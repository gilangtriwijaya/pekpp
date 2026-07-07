<?php

namespace App\Http\Controllers;

use App\Models\F03Token;
use App\Models\Upp;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class F03TokenController extends Controller
{
    protected $adminRoles = ['superadmin', 'admin_organisasi'];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (!$user->hasGlobalRole($this->adminRoles)) {
                // UPP user can only access their own token
                // This will be handled separately in permission check
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasGlobalRole($this->adminRoles)) {
            // Get active periodes for dropdown
            $periodes = Periode::where('is_aktif', true)->orderBy('tahun', 'desc')->get();
            $selectedPeriode = $request->get('periode_id', $periodes->first()?->id);

            // Get ALL UPPs with their token status for selected periode
            // LEFT JOIN to show UPPs even if they don't have a token yet
            $uppsWithStatusQuery = Upp::leftJoin(
                'f03_token',
                function ($join) use ($selectedPeriode) {
                    $join->on('upps.id', '=', 'f03_token.upp_id')
                        ->where('f03_token.periode_id', '=', $selectedPeriode);
                }
            )
            ->where('upps.aktif', true)
            ->select('upps.*', 'f03_token.id as token_id', 'f03_token.token', 'f03_token.aktif as token_aktif');

            $totalUpp = (clone $uppsWithStatusQuery)->count();
            $totalAktif = (clone $uppsWithStatusQuery)->where('f03_token.aktif', true)->count();
            $totalRevoke = (clone $uppsWithStatusQuery)->where('f03_token.aktif', false)->whereNotNull('f03_token.id')->count();

            $uppsWithStatus = (clone $uppsWithStatusQuery)->orderBy('upps.nama', 'asc')
            ->paginate(50);

        } else {
            // UPP user - show only their token
            abort(403);
        }

        return view('f03.token.index', compact('uppsWithStatus', 'periodes', 'selectedPeriode', 'totalUpp', 'totalAktif', 'totalRevoke'));

    }

    public function generateToken(Request $request)
    {
        $validated = $request->validate([
            'upp_id' => 'required|exists:upps,id',
            'periode_id' => 'required|exists:periode,id',
            'allow_multiple_responses' => 'nullable|boolean',
        ]);

        // Check if token already exists for this UPP + Periode (active or revoked)
        $existing = F03Token::where('upp_id', $validated['upp_id'])
            ->where('periode_id', $validated['periode_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'Token sudah ada untuk UPP dan Periode ini. Gunakan tombol Aktifkan jika ingin mengaktifkan kembali.',
                'status' => 'duplicate'
            ], 422);
        }

        // Generate unique token
        $token = Str::random(32);
        $validated['token'] = $token;
        $validated['aktif'] = true;
        $validated['allow_multiple_responses'] = filter_var($request->input('allow_multiple_responses', 0), FILTER_VALIDATE_BOOLEAN);

        $newToken = F03Token::create($validated);

        // Generate QR code immediately
        $newToken->generateQrCode();

        // Generate form URL
        $url = route('f03.public.form', ['token' => $token]);

        return response()->json([
            'success' => true,
            'message' => 'Token F03 berhasil dibuat',
            'token' => $newToken->load('upp'),
            'url' => $url
        ]);
    }

    public function generateAllTokens(Request $request)
    {
        $validated = $request->validate([
            'periode_id' => 'required|exists:periode,id',
            'allow_multiple_responses' => 'nullable|boolean',
        ]);

        $periodeId = $validated['periode_id'];
        $allowMultiple = filter_var($request->input('allow_multiple_responses', 0), FILTER_VALIDATE_BOOLEAN);
        
        // Log untuk debug
        \Log::info('generateAllTokens', [
            'periode_id' => $periodeId,
            'allow_multiple_responses_input' => $request->input('allow_multiple_responses'),
            'allow_multiple_responses_boolean' => $allowMultiple
        ]);
        
        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $errors = [];

        // Get all active UPPs
        $upps = Upp::where('aktif', true)->get();

        foreach ($upps as $upp) {
            try {
                // Check if token already exists for this UPP + Periode
                $existing = F03Token::where('upp_id', $upp->id)
                    ->where('periode_id', $periodeId)
                    ->first();

                if ($existing) {
                    // Jika setting berbeda, update
                    if ($existing->allow_multiple_responses != $allowMultiple) {
                        $existing->update(['allow_multiple_responses' => $allowMultiple]);
                        $updatedCount++;
                        \Log::info('Token updated', [
                            'token_id' => $existing->id,
                            'old_setting' => !$allowMultiple,
                            'new_setting' => $allowMultiple
                        ]);
                    } else {
                        // Setting sama, skip
                        $skippedCount++;
                    }
                    continue;
                }

                // Generate unique token
                $token = Str::random(32);

                $newTokenData = F03Token::create([
                    'upp_id' => $upp->id,
                    'periode_id' => $periodeId,
                    'token' => $token,
                    'aktif' => true,
                    'allow_multiple_responses' => $allowMultiple
                ]);

                // Generate QR code
                $newTokenData->generateQrCode();

                $createdCount++;
            } catch (\Exception $e) {
                $errors[] = "Gagal membuat token untuk {$upp->nama}: " . $e->getMessage();
            }
        }

        $message = "";
        if ($createdCount > 0) {
            $message .= "Berhasil membuat {$createdCount} token baru";
        }
        if ($updatedCount > 0) {
            if ($message) $message .= ", ";
            $message .= "Update {$updatedCount} token";
        }
        if ($skippedCount > 0 && ($createdCount > 0 || $updatedCount > 0)) {
            $message .= " ({$skippedCount} sudah ada)";
        }
        
        if (!$message) {
            $message = "Tidak ada perubahan ({$skippedCount} token tidak berubah)";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'created' => $createdCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'errors' => $errors
        ]);
    }

    public function updateSettings($id, Request $request)
    {
        $token = F03Token::findOrFail($id);
        
        $validated = $request->validate([
            'allow_multiple_responses' => 'required|boolean',
        ]);
        
        $token->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Pengaturan token berhasil diubah',
            'allow_multiple_responses' => $token->allow_multiple_responses
        ]);
    }

    public function updateGlobalSettings(Request $request)
    {
        $validated = $request->validate([
            'periode_id' => 'required|exists:periode,id',
            'allow_multiple_responses' => 'required|boolean',
        ]);

        $updatedCount = F03Token::where('periode_id', $validated['periode_id'])
            ->update(['allow_multiple_responses' => $validated['allow_multiple_responses']]);

        return response()->json([
            'success' => true,
            'message' => "Pengaturan berhasil diubah secara global. ({$updatedCount} token diperbarui)"
        ]);
    }

    public function revoke($id)
    {
        $token = F03Token::findOrFail($id);
        $token->update(['aktif' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Token berhasil dinonaktifkan'
        ]);
    }

    public function activate($id)
    {
        $token = F03Token::findOrFail($id);
        $token->update(['aktif' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Token berhasil diaktifkan kembali'
        ]);
    }

    public function show($id)
    {
        $token = F03Token::with(['upp', 'periode', 'pengisian'])->findOrFail($id);
        
        // Return JSON jika request dari AJAX
        if (request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'token' => $token
            ]);
        }
        
        // Return view untuk page biasa
        $totalResponses = $token->pengisian()->count();
        $uniqueResponses = $token->pengisian()->where('is_duplicate', false)->count();
        $duplicates = $token->pengisian()->where('is_duplicate', true)->count();

        return view('f03.token.show', compact('token', 'totalResponses', 'uniqueResponses', 'duplicates'));
    }


}
