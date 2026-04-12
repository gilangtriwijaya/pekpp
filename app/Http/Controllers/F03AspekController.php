<?php

namespace App\Http\Controllers;

use App\Models\F03Aspek;
use App\Models\Periode;
use Illuminate\Http\Request;

class F03AspekController extends Controller
{
    protected $adminRoles = ['superadmin', 'admin_organisasi'];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (!$user->hasGlobalRole($this->adminRoles)) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = F03Aspek::with('periode');

        for ($i = 1; $i <= 3; $i++) {
            $column = $request->query("sort$i");
            $direction = $request->query("dir$i", 'asc');
            
            if ($column && in_array($column, ['kode', 'nama', 'bobot', 'aktif'])) {
                $query->orderBy($column, $direction === 'desc' ? 'desc' : 'asc');
            }
        }

        if (!$request->query('sort1')) {
            $query->orderBy('kode', 'asc');
        }

        $aspeks = $query->paginate(50);
        $periodes = Periode::orderBy('tahun', 'desc')->orderBy('nama', 'asc')->get();
        
        return view('f03.aspek.index', compact('aspeks', 'periodes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'periode_id' => 'required|exists:periode,id',
            'kode' => 'nullable|string|max:50',
            'nama' => 'required|string|max:255',
            'bobot' => 'nullable|numeric|min:0|max:100',
            'keterangan' => 'nullable|string',
            'aktif' => 'boolean',
        ]);

        if (empty($validated['kode'])) {
            $lastAspek = F03Aspek::where('periode_id', $validated['periode_id'])->latest('id')->first();
            $number = ($lastAspek ? intval(substr($lastAspek->kode, -1)) : 0) + 1;
            $validated['kode'] = 'FA' . $number;
        }

        $maxUrutan = F03Aspek::where('periode_id', $validated['periode_id'])->max('urutan') ?? 0;
        $validated['urutan'] = $maxUrutan + 1;
        $validated['aktif'] = $request->has('aktif');
        $validated['bobot'] = $validated['bobot'] ?? 0;

        F03Aspek::create($validated);

        return response()->json(['message' => 'Aspek F03 berhasil dibuat']);
    }

    public function update(Request $request, $id)
    {
        $aspek = F03Aspek::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'bobot' => 'nullable|numeric|min:0|max:100',
            'keterangan' => 'nullable|string',
            'aktif' => 'boolean',
        ]);

        $validated['aktif'] = $request->has('aktif');
        $validated['bobot'] = $validated['bobot'] ?? 0;
        $aspek->update($validated);

        return response()->json(['message' => 'Aspek F03 berhasil diperbarui']);
    }

    public function destroy($id)
    {
        $aspek = F03Aspek::findOrFail($id);
        $aspek->delete();

        return response()->json(['message' => 'Aspek F03 berhasil dihapus']);
    }
}
