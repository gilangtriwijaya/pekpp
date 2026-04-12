<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIndikatorRequest;
use App\Models\Indikator;
use App\Models\Aspek;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use PDOException;

class F01IndikatorController extends Controller
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

    public function index(Request $request)
    {
        $query = Indikator::with('aspek');
        
        if ($request->filled('aspek_id')) {
            $query->where('aspek_id', $request->input('aspek_id'));
        }
        
        // Apply multi-column sorting (up to 3 levels)
        for ($i = 1; $i <= 3; $i++) {
            $column = $request->query("sort$i");
            $direction = $request->query("dir$i", 'asc');
            
            if ($column && in_array($column, ['kode', 'nama', 'aspek_id', 'bobot', 'aktif'])) {
                $query->orderBy($column, $direction === 'desc' ? 'desc' : 'asc');
            }
        }

        // Default sort if no sort specified
        if (!$request->query('sort1')) {
            $query->orderBy('aspek_id', 'asc')->orderBy('kode', 'asc');
        }

        $indikator = $query->paginate(50);
        $aspeks = Aspek::orderBy('urutan', 'asc')->get();
        
        return view('f01.indikator.index', compact('indikator', 'aspeks'));
    }

    public function store(StoreIndikatorRequest $request)
    {
        try {
            $data = $request->validated();
            
            // Auto-generate kode if not provided
            if (empty($data['kode'])) {
                $aspekId = $data['aspek_id'];
                $lastIndikator = Indikator::where('aspek_id', $aspekId)->latest('id')->first();
                $number = ($lastIndikator ? intval(substr($lastIndikator->kode, -1)) : 0) + 1;
                $data['kode'] = 'I' . $number;
            }
            
            // Calculate urutan for this aspek - never null
            $maxUrutan = Indikator::where('aspek_id', $data['aspek_id'])->max('urutan') ?? 0;
            $data['urutan'] = $maxUrutan + 1;
            
            // Set default bobot if not provided - never null
            if (empty($data['bobot'])) {
                $data['bobot'] = 0;
            }
            
            $data['aktif'] = $request->has('aktif') ? (bool)$request->input('aktif') : true;
            
            $indikator = Indikator::create($data);
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Indikator berhasil dibuat.', 'data' => $indikator]);
            }
            
            return redirect()->route('admin.f01.indikator.index')->with('success', 'Indikator dibuat.');
        } catch (QueryException $e) {
            $errorMsg = $this->getCleanDatabaseErrorMessage($e, 'Indikator');
            if ($request->expectsJson()) {
                return response()->json(['error' => $errorMsg], 400);
            }
            return redirect()->back()->withErrors(['error' => $errorMsg]);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Terjadi kesalahan saat menyimpan data'], 400);
            }
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data']);
        }
    }

    public function show(Indikator $indikator)
    {
        $indikator->load('aspek');
        if (request()->expectsJson()) {
            return response()->json(['data' => $indikator]);
        }
        return view('f01.indikator.show', compact('indikator'));
    }

    public function update(StoreIndikatorRequest $request, Indikator $indikator)
    {
        try {
            $data = $request->validated();
            
            // Auto-generate kode if not provided
            if (empty($data['kode'])) {
                $aspekId = $data['aspek_id'];
                $lastIndikator = Indikator::where('aspek_id', $aspekId)->where('id', '!=', $indikator->id)->latest('id')->first();
                $number = ($lastIndikator ? intval(substr($lastIndikator->kode, -1)) : 0) + 1;
                $data['kode'] = 'I' . $number;
            }
            
            // Auto-generate urutan if not provided - never null
            if (empty($data['urutan'])) {
                $maxUrutan = Indikator::where('aspek_id', $data['aspek_id'])->where('id', '!=', $indikator->id)->max('urutan') ?? 0;
                $data['urutan'] = $maxUrutan + 1;
            }
            
            // Set default bobot if not provided - never null
            if (empty($data['bobot'])) {
                $data['bobot'] = 0;
            }
            
            $data['aktif'] = $request->has('aktif') ? (bool)$request->input('aktif') : true;
            
            $indikator->update($data);
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Indikator berhasil diperbarui.', 'data' => $indikator]);
            }
            
            return redirect()->route('admin.f01.indikator.index')->with('success', 'Indikator diperbarui.');
        } catch (QueryException $e) {
            $errorMsg = $this->getCleanDatabaseErrorMessage($e, 'Indikator');
            if ($request->expectsJson()) {
                return response()->json(['error' => $errorMsg], 400);
            }
            return redirect()->back()->withErrors(['error' => $errorMsg]);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Terjadi kesalahan saat memperbarui data'], 400);
            }
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui data']);
        }
    }
    
    /**
     * Mengkonversi database error exception menjadi pesan yang user-friendly
     */
    private function getCleanDatabaseErrorMessage(QueryException $e, $model = 'Data')
    {
        $message = $e->getMessage();
        
        // Check for duplicate entry / unique constraint
        if (strpos($message, 'Duplicate entry') !== false || strpos($message, 'UNIQUE constraint failed') !== false) {
            if (strpos($message, 'kode') !== false) {
                return "Kode $model sudah ada, gunakan kode yang berbeda";
            }
            if (strpos($message, 'nama') !== false) {
                return "Nama $model sudah ada, gunakan nama yang berbeda";
            }
            return "Data sudah ada di sistem, gunakan data yang berbeda";
        }
        
        // Check for foreign key constraint
        if (strpos($message, 'Foreign key constraint') !== false || strpos($message, 'FOREIGN KEY constraint failed') !== false) {
            if (strpos($message, 'aspek_id') !== false) {
                return "Tidak dapat mengubah Aspek karena sudah digunakan oleh Indikator lain";
            }
            if (strpos($message, 'indikator_id') !== false) {
                return "Tidak dapat mengubah Indikator karena sudah digunakan oleh Pertanyaan lain";
            }
            return "Gagal menyimpan karena terhubung dengan data lain";
        }
        
        // Default message
        return "Terjadi kesalahan saat menyimpan data";
    }

    public function destroy(Indikator $indikator)
    {
        if ($indikator->pertanyaan()->exists()) {
            $message = 'Indikator tidak dapat dihapus karena masih memiliki pertanyaan.';
            if (request()->expectsJson()) {
                return response()->json(['error' => $message], 422);
            }
            return redirect()->back()->withErrors(['delete' => $message]);
        }
        
        $indikator->delete();
        
        if (request()->expectsJson()) {
            return response()->json(['message' => 'Indikator berhasil dihapus.']);
        }
        
        return redirect()->route('admin.f01.indikator.index')->with('success', 'Indikator dihapus.');
    }

    public function reorder(Request $request)
    {
        $order = $request->validate(['order' => 'required|array'])['order'];
        
        foreach ($order as $index => $id) {
            Indikator::where('id', $id)->update(['urutan' => $index + 1]);
        }
        
        return response()->json(['message' => 'Urutan indikator berhasil diperbarui.']);
    }

}

