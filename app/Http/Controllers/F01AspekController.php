<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAspekRequest;
use App\Http\Requests\UpdateAspekRequest;
use App\Models\Aspek;
use App\Models\Periode;
use App\Models\F01Pengisian;
use Illuminate\Http\Request;

class F01AspekController extends Controller
{
    protected $adminRoles = ['superadmin', 'admin_organisasi', 'admin_bagian_organisasi'];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (! $user->hasGlobalRole($this->adminRoles)) {
                abort(403);
            }
            return $next($request);
        });
    }

    protected function structureLocked(): bool
    {
        return F01Pengisian::where('status', 'final')->exists();
    }

    public function index(Request $request)
    {
        $query = Aspek::with('periode')->withCount('indikator');

        // Apply multi-column sorting (up to 3 levels)
        for ($i = 1; $i <= 3; $i++) {
            $column = $request->query("sort$i");
            $direction = $request->query("dir$i", 'asc');
            
            if ($column && in_array($column, ['kode', 'nama', 'domain', 'periode', 'aktif'])) {
                $query->orderBy($column, $direction === 'desc' ? 'desc' : 'asc');
            }
        }

        // Default sort if no sort specified
        if (!$request->query('sort1')) {
            $query->orderBy('kode', 'asc');
        }

        $aspeks = $query->paginate(50);
        $periodes = Periode::orderBy('tahun', 'desc')->orderBy('nama', 'asc')->get();
        $locked = $this->structureLocked();
        return view('f01.aspek.index', compact('aspeks', 'periodes', 'locked'));
    }

    public function store(StoreAspekRequest $request)
    {
        if ($this->structureLocked()) {
            $message = 'Perubahan struktur tidak diperbolehkan saat pengisian sudah difinalisasi.';
            if ($request->expectsJson()) {
                return response()->json(['error' => $message], 422);
            }
            return redirect()->back()->withErrors(['structure' => $message]);
        }
        $data = $request->validated();
        
        // Auto-generate kode if not provided or empty
        if (empty($data['kode'])) {
            $periodeId = $data['periode_id'];
            $lastAspek = Aspek::where('periode_id', $periodeId)->latest('id')->first();
            $number = ($lastAspek ? intval(substr($lastAspek->kode, -1)) : 0) + 1;
            $data['kode'] = 'A' . $number;
        }
        
        // Calculate urutan for this periode
        $maxUrutan = Aspek::where('periode_id', $data['periode_id'])->max('urutan') ?? 0;
        $data['urutan'] = $maxUrutan + 1;
        
        $data['aktif'] = $request->has('aktif') ? (bool)$request->input('aktif') : true;
        $aspek = Aspek::create($data);
        
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Aspek berhasil dibuat.', 'data' => $aspek]);
        }
        return redirect()->route('admin.f01.aspek.index')->with('success', 'Aspek dibuat.');
    }

    public function update(UpdateAspekRequest $request, Aspek $aspek)
    {
        if ($this->structureLocked()) {
            $message = 'Perubahan struktur tidak diperbolehkan saat pengisian sudah difinalisasi.';
            if ($request->expectsJson()) {
                return response()->json(['error' => $message], 422);
            }
            return redirect()->back()->withErrors(['structure' => $message]);
        }
        $data = $request->validated();
        $aspek->update($data);
        
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Aspek berhasil diperbarui.', 'data' => $aspek]);
        }
        return redirect()->route('admin.f01.aspek.index')->with('success', 'Aspek diperbarui.');
    }

    public function destroy(Aspek $aspek)
    {
        if ($this->structureLocked()) {
            $message = 'Perubahan struktur tidak diperbolehkan saat pengisian sudah difinalisasi.';
            if (request()->expectsJson()) {
                return response()->json(['error' => $message], 422);
            }
            return redirect()->back()->withErrors(['structure' => $message]);
        }

        // Check if aspek has related indikator
        $hasIndikator = $aspek->indikator()->exists();

        if ($hasIndikator) {
            $message = 'Tidak dapat menghapus aspek karena masih terkait dengan indikator. Silakan hapus indikator terlebih dahulu.';
            if (request()->expectsJson()) {
                return response()->json(['error' => $message], 422);
            }
            return redirect()->back()->withErrors(['related' => $message]);
        }

        $aspek->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Aspek berhasil dihapus.']);
        }

        return redirect()->route('admin.f01.aspek.index')->with('success', 'Aspek berhasil dihapus.');
    }

    public function toggleActive(Aspek $aspek)
    {
        if ($this->structureLocked()) {
            return redirect()->back()->withErrors(['structure' => 'Perubahan struktur tidak diperbolehkan saat pengisian sudah difinalisasi.']);
        }
        // Optional: prevent deactivating if related final F01 exists - skipped by default
        $aspek->aktif = ! (bool)$aspek->aktif;
        $aspek->save();
        return redirect()->route('admin.f01.aspek.index')->with('success', 'Status aspek diperbarui.');
    }

    public function reorder(Request $request)
    {
        if ($this->structureLocked()) {
            return response()->json(['error' => 'Perubahan struktur tidak diperbolehkan saat pengisian sudah difinalisasi.'], 422);
        }
        $order = $request->input('order');
        if (! is_array($order)) {
            return response()->json(['error' => 'Order harus array id.'], 422);
        }
        // Validate unique ids
        $ids = array_map('intval', $order);
        if (count($ids) !== count(array_unique($ids))) {
            return response()->json(['error' => 'Order mengandung duplikat.'], 422);
        }
        // Update in transaction
        \DB::transaction(function () use ($ids) {
            foreach ($ids as $i => $id) {
                Aspek::where('id', $id)->update(['urutan' => $i + 1]);
            }
        });
        return response()->json(['success' => true]);
    }
}
