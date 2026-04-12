<?php

namespace App\Http\Controllers;

use App\Models\F03Token;
use App\Models\F03Pengisian;
use App\Models\F03Jawaban;
use App\Models\F03Indikator;
use App\Models\F03ResponseDemographic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class F03PublicController extends Controller
{
    /**
     * Display the public questionnaire form
     */
    public function show($token)
    {
        $tokenRecord = F03Token::where('token', $token)
            ->with(['upp', 'periode'])
            ->firstOrFail();

        // Check if token is active
        if (!$tokenRecord->aktif) {
            return view('f03.public.error', ['message' => 'Token tidak aktif']);
        }

        // Check if expired
        if ($tokenRecord->isExpired()) {
            return view('f03.public.error', ['message' => 'Token sudah kadaluarsa']);
        }

        // Check if periode is accepting input
        if ($tokenRecord->periode && $tokenRecord->periode->status_pengisian !== 'open') {
            $statusMessage = $tokenRecord->periode->status_pengisian === 'locked' 
                ? 'Periode ini sedang dikunci dan tidak menerima respons baru. Hubungi administrator jika ada pertanyaan.'
                : 'Periode ini telah ditutup dan tidak menerima respons baru. Hubungi administrator jika ada pertanyaan.';
            return view('f03.public.error', ['message' => $statusMessage]);
        }

        // Check if user already responded (anti-duplicate check)
        $identifier = $this->generateResponseIdentifier();
        $existing = F03Pengisian::where('f03_token_id', $tokenRecord->id)
            ->where('response_identifier', $identifier)
            ->first();

        // Debug log
        \Log::info('F03 Form Show', [
            'token_id' => $tokenRecord->id,
            'allow_multiple_responses' => $tokenRecord->allow_multiple_responses,
            'existing_response' => $existing ? 'yes' : 'no'
        ]);

        if ($existing && !$tokenRecord->allow_multiple_responses) {
            return view('f03.public.error', ['message' => 'Anda sudah memberikan respons sebelumnya. Responden tidak dapat mengisi ulang.']);
        }

        // Load aspeks with indikators
        $aspeks = $tokenRecord->periode->f03Aspeks()
            ->where('aktif', true)
            ->with(['indikator' => function($query) {
                $query->where('aktif', true)->orderBy('urutan');
            }])
            ->orderBy('urutan')
            ->get();

        $token = $tokenRecord;
        return view('f03.public.form', compact('token', 'aspeks'));
    }

    /**
     * Submit questionnaire responses
     */
    public function submit(Request $request, $token)
    {
        $tokenRecord = F03Token::where('token', $token)->firstOrFail();

        // Validate token
        if (!$tokenRecord->aktif || $tokenRecord->isExpired()) {
            return response()->json(['error' => 'Token tidak valid'], 422);
        }

        // Check if periode is accepting input
        if ($tokenRecord->periode && $tokenRecord->periode->status_pengisian !== 'open') {
            return response()->json(['error' => 'Periode ini tidak menerima respons baru'], 422);
        }

        // Validate demographic data
        $demographicRules = [
            'gender' => 'required|string|in:M,F,O,Prefer Not to Say',
            'age' => 'required|integer|min:18|max:100',
            'last_education' => 'required|string',
            'occupation' => 'required|string'
        ];

        // Validate responses based on tipe_jawaban
        $rules = $demographicRules;
        $indikators = F03Indikator::where('periode_id', $tokenRecord->periode_id)
            ->where('aktif', true)
            ->get();

        foreach ($indikators as $ind) {
            $tipeJawaban = $ind->tipe_jawaban;
            
            if ($tipeJawaban == 'checkbox') {
                $rules['responses.' . $ind->id] = 'required|array|min:1';
                $rules['responses.' . $ind->id . '.*'] = 'string';
            } else if (in_array($tipeJawaban, ['text', 'textarea'])) {
                $rules['responses.' . $ind->id] = 'required|string|max:5000';
            } else if (in_array($tipeJawaban, ['likert_5', 'rating'])) {
                $rules['responses.' . $ind->id] = 'required|integer|min:1|max:5';
            } else if ($tipeJawaban == 'likert_4') {
                $rules['responses.' . $ind->id] = 'required|integer|min:1|max:4';
            } else {
                // radio, dropdown, multiple_choice, etc
                $rules['responses.' . $ind->id] = 'required|string';
            }
        }

        try {
            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validasi gagal. Pastikan semua data terisi.', 'errors' => $e->errors()], 422);
        }

        // Check anti-duplicate
        $identifier = $this->generateResponseIdentifier();
        $isDuplicate = F03Pengisian::where('f03_token_id', $tokenRecord->id)
            ->where('response_identifier', $identifier)
            ->exists();

        if ($isDuplicate && !$tokenRecord->allow_multiple_responses) {
            return response()->json(['error' => 'Anda sudah memberikan respons sebelumnya. Responden tidak dapat mengisi ulang.'], 422);
        }

        try {
            // Create pengisian record
            $pengisian = F03Pengisian::create([
                'upp_id' => $tokenRecord->upp_id,
                'periode_id' => $tokenRecord->periode_id,
                'f03_token_id' => $tokenRecord->id,
                'response_identifier' => $identifier,
                'ip_address_hashed' => Hash::make($request->ip()),
                'browser_fingerprint' => $this->generateBrowserFingerprint($request),
                'response_date' => now(),
                'is_duplicate' => $isDuplicate
            ]);

            // Save demographic data
            F03ResponseDemographic::create([
                'f03_pengisian_id' => $pengisian->id,
                'gender' => $validated['gender'],
                'age' => $validated['age'],
                'last_education' => $validated['last_education'],
                'occupation' => $validated['occupation']
            ]);

            // Save individual responses
            foreach ($validated['responses'] as $indikatorId => $response) {
                // Convert to numeric score if needed
                $score = null;
                $responseText = null;

                if (is_array($response)) {
                    // For checkboxes, store as JSON
                    $responseText = json_encode($response);
                } else {
                    // Try to convert to number for likert scales
                    if (is_numeric($response)) {
                        $score = (int)$response;
                    } else {
                        $responseText = strval($response);
                    }
                }

                F03Jawaban::create([
                    'f03_pengisian_id' => $pengisian->id,
                    'f03_indikator_id' => $indikatorId,
                    'score' => $score,
                    'catatan' => $request->input("catatan.$indikatorId") ?? null,
                    'response_text' => $responseText
                ]);
            }

            return response()->json([
                'message' => 'Terima kasih! Respons Anda berhasil disimpan.',
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menyimpan respons: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate response identifier based on IP + Browser Fingerprint
     */
    private function generateResponseIdentifier()
    {
        $request = request();
        
        $fingerprint = $this->generateBrowserFingerprint($request);
        $ip = $request->ip();

        // Combine IP + fingerprint and hash
        $identifier = hash('sha256', $ip . '::' . $fingerprint);

        return $identifier;
    }

    /**
     * Generate browser fingerprint
     */
    private function generateBrowserFingerprint($request)
    {
        // Can be expanded with more details
        $userAgent = $request->header('User-Agent', '');
        $acceptLanguage = $request->header('Accept-Language', '');
        
        return hash('sha256', $userAgent . '::' . $acceptLanguage);
    }
}
