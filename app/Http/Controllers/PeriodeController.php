<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePeriodeRequest;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PeriodeController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (! $user->hasGlobalRole(['superadmin', 'admin_organisasi'])) {
                abort(403);
            }
            return $next($request);
        })->only(['create','store','edit','destroy']);
        
        // Only superadmin can toggle is_aktif
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            
            // Check if update action and is_aktif is being changed
            if ($request->isMethod('post') && $request->path() !== route('admin.periode.store', [], false)) {
                if ($request->has('is_aktif')) {
                    if (! $user->hasGlobalRole('superadmin')) {
                        abort(403, 'Hanya superadmin yang dapat mengatur periode aktif');
                    }
                }
            }
            return $next($request);
        })->only(['update']);
    }

    public function index()
    {
        $periodes = Periode::orderBy('tahun','desc')->paginate(20);
        return view('periode.index', compact('periodes'));
    }

    public function create()
    {
        return view('periode.create');
    }

    public function store(StorePeriodeRequest $request)
    {
        $data = $request->validated();
        
        // Auto-generate kode if not provided
        if (empty($data['kode'])) {
            $data['kode'] = 'PEKPPP-' . $data['tahun'] . '-' . strtoupper(uniqid());
        }
        
        $data['is_aktif'] = $request->has('is_aktif') ? (bool)$request->input('is_aktif') : false;
        
        // Only superadmin can set is_aktif
        $user = $request->user();
        
        if ($data['is_aktif'] && $user->hasGlobalRole('superadmin')) {
            // Deactivate all other periods if setting this one to active
            Periode::where('is_aktif', 1)->update(['is_aktif' => false]);
        } elseif ($data['is_aktif'] && !$user->hasGlobalRole('superadmin')) {
            // Prevent non-superadmin from creating active periode
            $data['is_aktif'] = false;
        }
        
        Periode::create($data);
        return redirect()->route('admin.periode.index')->with('success', 'Periode dibuat.');
    }

    public function edit(Periode $periode)
    {
        return view('periode.edit', compact('periode'));
    }

    public function update(StorePeriodeRequest $request, Periode $periode)
    {
        $data = $request->validated();
        
        // Auto-generate kode if not provided
        if (empty($data['kode'])) {
            $data['kode'] = $periode->kode ?? ('PEKPPP-' . $data['tahun'] . '-' . strtoupper(uniqid()));
        }
        
        $data['is_aktif'] = $request->has('is_aktif') ? (bool)$request->input('is_aktif') : false;
        
        // Only superadmin can set is_aktif
        $user = $request->user();
        
        if ($data['is_aktif'] && $user->hasGlobalRole('superadmin')) {
            // Deactivate all other periods if setting this one to active
            Periode::where('id', '!=', $periode->id)->update(['is_aktif' => false]);
        } elseif (!$data['is_aktif'] && !$user->hasGlobalRole('superadmin')) {
            // Prevent non-superadmin from changing is_aktif
            $data['is_aktif'] = $periode->is_aktif;
        }
        
        $periode->update($data);
        return redirect()->route('admin.periode.index')->with('success', 'Periode berhasil diperbarui.');
    }

    public function destroy(Periode $periode)
    {
        // Check all related data
        $hasF01 = \App\Models\F01Pengisian::where('periode_id', $periode->id)->exists();
        $hasAspek = \App\Models\Aspek::where('periode_id', $periode->id)->exists();
        $hasF03Token = \App\Models\F03Token::where('periode_id', $periode->id)->exists();
        
        if ($hasF01 || $hasAspek || $hasF03Token) {
            $errorMessages = [];
            if ($hasF01) $errorMessages[] = 'Data Pengisian F01';
            if ($hasAspek) $errorMessages[] = 'Aspek';
            if ($hasF03Token) $errorMessages[] = 'Token F03';
            
            $message = 'Tidak dapat menghapus periode karena masih terkait dengan: ' . implode(', ', $errorMessages) . '. Silakan hapus data terkait terlebih dahulu.';
            
            if (request()->expectsJson()) {
                return response()->json(['error' => $message], 422);
            }
            
            return redirect()->back()->withErrors(['related' => $message]);
        }

        $periode->delete();
        
        if (request()->expectsJson()) {
            return response()->json(['message' => 'Periode berhasil dihapus.']);
        }
        
        return redirect()->route('admin.periode.index')->with('success', 'Periode berhasil dihapus.');
    }

    /**
     * Toggle is_aktif status (AJAX endpoint - superadmin only)
     */
    public function toggleAktif(Request $request, Periode $periode)
    {
        $user = $request->user();
        
        // Only superadmin can toggle
        if (!$user->hasGlobalRole('superadmin')) {
            return response()->json(['error' => 'Hanya superadmin yang dapat mengatur periode aktif'], 403);
        }
        
        $newStatus = !$periode->is_aktif;
        
        if ($newStatus) {
            // Deactivate all other periods
            Periode::where('id', '!=', $periode->id)->update(['is_aktif' => false]);
        }
        
        $periode->update(['is_aktif' => $newStatus]);
        
        return response()->json([
            'success' => true,
            'message' => $newStatus ? 'Periode berhasil diaktifkan' : 'Periode berhasil dinonaktifkan',
            'is_aktif' => $periode->is_aktif
        ]);
    }

    /**
     * Get instrument tree from a source period (AJAX endpoint)
     */
    public function getInstrumenTree(Request $request, Periode $periode)
    {
        $sumber_id = $request->query('sumber');
        if (!$sumber_id) {
            return response()->json(['error' => 'Parameter sumber_periode_id diperlukan'], 400);
        }

        $sumberPeriode = Periode::findOrFail($sumber_id);

        $aspek = \App\Models\Aspek::where('periode_id', $sumber_id)
            ->with(['indikator' => function ($q) {
                $q->orderBy('urutan', 'asc');
            }, 'indikator.pertanyaan' => function ($q) {
                $q->orderBy('urutan', 'asc');
            }])
            ->orderBy('urutan', 'asc')
            ->get();

        // Transform for frontend
        $f01_tree = $aspek->map(function ($a) {
            return [
                'id' => $a->id,
                'kode' => $a->kode,
                'nama' => $a->nama,
                'indikator' => $a->indikator->map(function ($i) {
                    return [
                        'id' => $i->id,
                        'kode' => $i->kode,
                        'nama' => $i->nama,
                        'pertanyaan' => $i->pertanyaan->map(function ($p) {
                            return [
                                'id' => $p->id,
                                'kode' => $p->kode,
                                'label' => $p->label,
                                'parent_pertanyaan_id' => $p->parent_pertanyaan_id
                            ];
                        })->values()
                    ];
                })->values()
            ];
        })->values();

        $f03Aspek = \App\Models\F03Aspek::where('periode_id', $sumber_id)
            ->with(['indikator' => function ($q) {
                $q->orderBy('urutan', 'asc');
            }])
            ->orderBy('urutan', 'asc')
            ->get();

        $f03_tree = $f03Aspek->map(function ($a) {
            return [
                'id' => $a->id,
                'kode' => $a->kode,
                'nama' => $a->nama,
                'indikator' => $a->indikator->map(function ($i) {
                    return [
                        'id' => $i->id,
                        'kode' => $i->kode,
                        'pertanyaan' => $i->pertanyaan
                    ];
                })->values()
            ];
        })->values();

        return response()->json([
            'f01' => $f01_tree,
            'f03' => $f03_tree
        ]);
    }

    /**
     * Execute copy instrument from a source period to the target period (AJAX endpoint)
     */
    public function salinInstrumen(Request $request, Periode $periode)
    {
        $request->validate([
            'sumber_periode_id' => 'required|exists:periode,id',
            'mode' => 'required|in:skip,overwrite',
            'aspek_ids' => 'array',
            'indikator_ids' => 'array',
            'pertanyaan_ids' => 'array',
            'f03_aspek_ids' => 'array',
            'f03_indikator_ids' => 'array',
            'copy_f02_skor' => 'boolean',
        ]);

        $sumber_id = $request->input('sumber_periode_id');
        $mode = $request->input('mode');
        $aspek_ids = $request->input('aspek_ids', []);
        $indikator_ids = $request->input('indikator_ids', []);
        $pertanyaan_ids = $request->input('pertanyaan_ids', []);
        $f03_aspek_ids = $request->input('f03_aspek_ids', []);
        $f03_indikator_ids = $request->input('f03_indikator_ids', []);
        $copy_f02_skor = $request->input('copy_f02_skor', false);

        $summary = [
            'aspek_disalin' => 0,
            'aspek_dilewati' => 0,
            'indikator_disalin' => 0,
            'indikator_dilewati' => 0,
            'pertanyaan_disalin' => 0,
            'pertanyaan_dilewati' => 0,
            'f03_aspek_disalin' => 0,
            'f03_aspek_dilewati' => 0,
            'f03_indikator_disalin' => 0,
            'f03_indikator_dilewati' => 0,
            'f02_skor_disalin' => 0,
            'f02_skor_dilewati' => 0,
            'warning' => []
        ];

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // 1. Process Aspek
            $sumberAspeks = \App\Models\Aspek::whereIn('id', $aspek_ids)->get();
            foreach ($sumberAspeks as $sumberAspek) {
                $existingAspek = \App\Models\Aspek::where('periode_id', $periode->id)
                    ->where('kode', $sumberAspek->kode)
                    ->first();

                if (!$existingAspek) {
                    $newAspek = $sumberAspek->replicate();
                    $newAspek->periode_id = $periode->id;
                    $newAspek->save();
                    $summary['aspek_disalin']++;
                } else {
                    if ($mode === 'overwrite') {
                        $updateData = $sumberAspek->toArray();
                        unset($updateData['id'], $updateData['periode_id'], $updateData['created_at'], $updateData['updated_at']);
                        $existingAspek->update($updateData);
                        $summary['aspek_disalin']++;
                    } else {
                        $summary['aspek_dilewati']++;
                    }
                }
            }

            // 2. Process Indikator
            $sumberIndikators = \App\Models\Indikator::with('aspek')->whereIn('id', $indikator_ids)->get();
            foreach ($sumberIndikators as $sumberIndikator) {
                // Find parent Aspek in target by code
                $targetAspek = \App\Models\Aspek::where('periode_id', $periode->id)
                    ->where('kode', $sumberIndikator->aspek->kode)
                    ->first();

                if (!$targetAspek) {
                    continue; // Should not happen if parent was selected, but skip if missing
                }

                $existingIndikator = \App\Models\Indikator::where('aspek_id', $targetAspek->id)
                    ->where('kode', $sumberIndikator->kode)
                    ->first();

                $targetInd = null;
                if (!$existingIndikator) {
                    $newIndikator = $sumberIndikator->replicate();
                    $newIndikator->aspek_id = $targetAspek->id;
                    $newIndikator->save();
                    $summary['indikator_disalin']++;
                    $targetInd = $newIndikator;
                } else {
                    if ($mode === 'overwrite') {
                        $updateData = $sumberIndikator->toArray();
                        unset($updateData['id'], $updateData['aspek_id'], $updateData['created_at'], $updateData['updated_at']);
                        $existingIndikator->update($updateData);
                        $summary['indikator_disalin']++;
                    } else {
                        $summary['indikator_dilewati']++;
                    }
                    $targetInd = $existingIndikator;
                }

                // Process F02 Skor if requested
                if ($copy_f02_skor) {
                    $sumberSkor = \App\Models\F02Skor::where('indikator_id', $sumberIndikator->id)
                        ->where('periode_id', $sumber_id)
                        ->first();
                        
                    if ($sumberSkor) {
                        $existingSkor = \App\Models\F02Skor::where('indikator_id', $targetInd->id)
                            ->where('periode_id', $periode->id)
                            ->first();
                            
                        if (!$existingSkor) {
                            $newSkor = $sumberSkor->replicate();
                            $newSkor->indikator_id = $targetInd->id;
                            $newSkor->periode_id = $periode->id;
                            $newSkor->save();
                            $summary['f02_skor_disalin']++;
                        } else {
                            if ($mode === 'overwrite') {
                                $updateData = $sumberSkor->toArray();
                                unset($updateData['id'], $updateData['indikator_id'], $updateData['periode_id'], $updateData['created_at'], $updateData['updated_at']);
                                $existingSkor->update($updateData);
                                $summary['f02_skor_disalin']++;
                            } else {
                                $summary['f02_skor_dilewati']++;
                            }
                        }
                    }
                }
            }

            // 3. Process Pertanyaan
            $sumberPertanyaans = \App\Models\Pertanyaan::with('indikator.aspek')->whereIn('id', $pertanyaan_ids)->get();
            
            // Map old ID to new model for conditional parents later
            $pertanyaanMap = []; 

            // First pass: non-conditional questions
            $nonConditionals = $sumberPertanyaans->whereNull('parent_pertanyaan_id');
            foreach ($nonConditionals as $sp) {
                // Find parent Indikator in target
                $targetAspek = \App\Models\Aspek::where('periode_id', $periode->id)
                    ->where('kode', $sp->indikator->aspek->kode)
                    ->first();

                if (!$targetAspek) continue;

                $targetIndikator = \App\Models\Indikator::where('aspek_id', $targetAspek->id)
                    ->where('kode', $sp->indikator->kode)
                    ->first();

                if (!$targetIndikator) continue;

                // Also check if already exists in this indicator
                $existingP = \App\Models\Pertanyaan::where('indikator_id', $targetIndikator->id)
                    ->where('kode', $sp->kode)
                    ->first();

                $targetP = null;
                if (!$existingP) {
                    $newP = $sp->replicate();
                    $newP->indikator_id = $targetIndikator->id;
                    $newP->save();
                    $summary['pertanyaan_disalin']++;
                    $targetP = $newP;
                } else {
                    if ($mode === 'overwrite') {
                        $updateData = $sp->toArray();
                        unset($updateData['id'], $updateData['indikator_id'], $updateData['parent_pertanyaan_id'], $updateData['created_at'], $updateData['updated_at']);
                        $existingP->update($updateData);
                        $summary['pertanyaan_disalin']++;
                    } else {
                        $summary['pertanyaan_dilewati']++;
                    }
                    $targetP = $existingP;
                }
                
                $pertanyaanMap[$sp->id] = $targetP;
            }

            // Second pass: conditional questions
            $conditionals = $sumberPertanyaans->whereNotNull('parent_pertanyaan_id');
            foreach ($conditionals as $sp) {
                $targetAspek = \App\Models\Aspek::where('periode_id', $periode->id)
                    ->where('kode', $sp->indikator->aspek->kode)
                    ->first();

                if (!$targetAspek) continue;

                $targetIndikator = \App\Models\Indikator::where('aspek_id', $targetAspek->id)
                    ->where('kode', $sp->indikator->kode)
                    ->first();

                if (!$targetIndikator) continue;

                // Find parent question in target
                $sumberParent = \App\Models\Pertanyaan::find($sp->parent_pertanyaan_id);
                if (!$sumberParent) continue;

                $targetParent = \App\Models\Pertanyaan::where('indikator_id', $targetIndikator->id)
                    ->where('kode', $sumberParent->kode)
                    ->first();

                if (!$targetParent) {
                    $summary['warning'][] = "Pertanyaan kondisional " . $sp->kode . " dilewati karena induk " . $sumberParent->kode . " tidak ditemukan.";
                    $summary['pertanyaan_dilewati']++;
                    continue;
                }

                $existingP = \App\Models\Pertanyaan::where('indikator_id', $targetIndikator->id)
                    ->where('kode', $sp->kode)
                    ->first();

                if (!$existingP) {
                    $newP = $sp->replicate();
                    $newP->indikator_id = $targetIndikator->id;
                    $newP->parent_pertanyaan_id = $targetParent->id;
                    $newP->save();
                    $summary['pertanyaan_disalin']++;
                } else {
                    if ($mode === 'overwrite') {
                        $updateData = $sp->toArray();
                        unset($updateData['id'], $updateData['indikator_id'], $updateData['parent_pertanyaan_id'], $updateData['created_at'], $updateData['updated_at']);
                        
                        $existingP->parent_pertanyaan_id = $targetParent->id; // Ensure link is correct
                        $existingP->update($updateData);
                        $summary['pertanyaan_disalin']++;
                    } else {
                        $summary['pertanyaan_dilewati']++;
                    }
                }
            }

            // 4. Process F03 Aspek
            $sumberF03Aspeks = \App\Models\F03Aspek::whereIn('id', $f03_aspek_ids)->get();
            foreach ($sumberF03Aspeks as $sumberAspek) {
                $existingAspek = \App\Models\F03Aspek::where('periode_id', $periode->id)
                    ->where('kode', $sumberAspek->kode)
                    ->first();

                if (!$existingAspek) {
                    $newAspek = $sumberAspek->replicate();
                    $newAspek->periode_id = $periode->id;
                    $newAspek->save();
                    $summary['f03_aspek_disalin']++;
                } else {
                    if ($mode === 'overwrite') {
                        $updateData = $sumberAspek->toArray();
                        unset($updateData['id'], $updateData['periode_id'], $updateData['created_at'], $updateData['updated_at']);
                        $existingAspek->update($updateData);
                        $summary['f03_aspek_disalin']++;
                    } else {
                        $summary['f03_aspek_dilewati']++;
                    }
                }
            }

            // 5. Process F03 Indikator
            $sumberF03Indikators = \App\Models\F03Indikator::with('aspek')->whereIn('id', $f03_indikator_ids)->get();
            foreach ($sumberF03Indikators as $sumberIndikator) {
                // Find parent Aspek in target by code
                $targetAspek = \App\Models\F03Aspek::where('periode_id', $periode->id)
                    ->where('kode', $sumberIndikator->aspek->kode)
                    ->first();

                if (!$targetAspek) {
                    continue; // Skip if missing parent
                }

                $existingIndikator = \App\Models\F03Indikator::where('f03_aspek_id', $targetAspek->id)
                    ->where('kode', $sumberIndikator->kode)
                    ->first();

                if (!$existingIndikator) {
                    $newIndikator = $sumberIndikator->replicate();
                    $newIndikator->f03_aspek_id = $targetAspek->id;
                    $newIndikator->periode_id = $periode->id;
                    $newIndikator->save();
                    $summary['f03_indikator_disalin']++;
                } else {
                    if ($mode === 'overwrite') {
                        $updateData = $sumberIndikator->toArray();
                        unset($updateData['id'], $updateData['f03_aspek_id'], $updateData['periode_id'], $updateData['created_at'], $updateData['updated_at']);
                        $existingIndikator->update($updateData);
                        $summary['f03_indikator_disalin']++;
                    } else {
                        $summary['f03_indikator_dilewati']++;
                    }
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'success' => true,
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'error' => 'Gagal menyalin instrumen: ' . $e->getMessage()
            ], 500);
        }
    }
}
