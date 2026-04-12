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
}

