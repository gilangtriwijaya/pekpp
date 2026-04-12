<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserUppController extends Controller
{
    public function index(Request $request)
    {
        $q = \App\Models\UserUpp::with(['user', 'upp', 'ditetapkanOleh'])->orderBy('id', 'desc');

        if ($request->filled('upp_id')) {
            $q->byUpp($request->input('upp_id'));
        }

        if ($request->filled('peran')) {
            $q->peran($request->input('peran'));
        }

        if ($request->filled('q')) {
            $search = $request->input('q');
            $q->whereHas('user', function ($sub) use ($search) {
                $sub->where('nama', 'like', "%{$search}%");
            });
        }

        $items = $q->paginate(15)->withQueryString();

        // The users table stores display name in `nama` column
        $users = \App\Models\User::select('id', 'nama')->orderBy('nama')->get();
        $upps = \App\Models\Upp::select('id', 'nama')->orderBy('nama')->get();

        return view('user_upp.index', compact('items', 'users', 'upps'));
    }

    public function create()
    {
        return view('user_upp.create');
    }

    public function store(Request $request)
    {
        \Log::info('UserUppController@store - Request:', $request->all());
        \Log::info('UserUppController@store - Headers:', [
            'Content-Type' => $request->header('Content-Type'),
            'Accept' => $request->header('Accept'),
            'wantsJson' => $request->wantsJson()
        ]);
        
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'upp_id' => 'required|integer|exists:upps,id',
            'peran' => 'required|string|in:admin_upp,admin_organisasi,verifikator,superadmin',
            'aktif' => 'sometimes|boolean',
        ]);
        
        // Get user to verify exists
        $user = DB::table('users')->where('id', $data['user_id'])->first();
        if (!$user) {
            \Log::warning('UserUppController@store - User not found:', ['user_id' => $data['user_id']]);
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 404);
        }
        
        // Use peran from form (no auto-mapping)
        $data['aktif'] = isset($data['aktif']) ? (bool)$data['aktif'] : true;
        $data['ditetapkan_oleh'] = auth()->id();
        $data['ditetapkan_pada'] = now();
        
        \Log::info('UserUppController@store - Processed data:', $data);
        
        try {
            $inserted = DB::table('user_upp')->insert(array_merge($data, ['created_at' => now(), 'updated_at' => now()]));
            \Log::info('UserUppController@store - Insert result:', ['inserted' => $inserted]);
            
            // Always return JSON for API/AJAX requests
            if ($request->wantsJson() || str_contains($request->header('Content-Type') ?? '', 'application/json')) {
                return response()->json(['success' => true, 'message' => 'Penugasan dibuat.']);
            }
            return redirect()->route('user_upp.index')->with('success', 'Penugasan dibuat.');
        } catch (\Exception $e) {
            \Log::error('UserUppController@store - Error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            // Always return JSON for API/AJAX requests
            if ($request->wantsJson() || str_contains($request->header('Content-Type') ?? '', 'application/json')) {
                return response()->json(['success' => false, 'message' => 'Gagal menyimpan penugasan: ' . $e->getMessage()], 500);
            }
            throw $e;
        }
    }

    public function edit($id)
    {
        $it = DB::table('user_upp')->where('id', $id)->first();
        if (! $it) abort(404);
        return view('user_upp.edit', compact('it'));
    }

    public function update(Request $request, $id)
    {
        \Log::info('UserUppController@update - Request for ID ' . $id . ':', $request->all());
        
        try {
            $data = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'upp_id' => 'required|integer|exists:upps,id',
                'peran' => 'required|string|in:admin_upp,admin_organisasi,verifikator,superadmin',
                'aktif' => 'sometimes|boolean',
            ]);
            
            // Get user to verify exists
            $user = DB::table('users')->where('id', $data['user_id'])->first();
            if (!$user) {
                \Log::warning('UserUppController@update - User not found:', ['user_id' => $data['user_id']]);
                if ($request->wantsJson() || str_contains($request->header('Content-Type') ?? '', 'application/json')) {
                    return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 404);
                }
                return redirect()->route('user_upp.index')->with('error', 'User tidak ditemukan');
            }
            
            // Use peran from form (no auto-mapping)
            $data['aktif'] = isset($data['aktif']) ? (bool)$data['aktif'] : true;
            
            \Log::info('UserUppController@update - Validated data:', $data);
            
            $updated = DB::table('user_upp')->where('id', $id)->update(array_merge($data, ['updated_at' => now()]));
            
            \Log::info('UserUppController@update - Update result:', ['rows_affected' => $updated]);
            
            // Always return JSON for API/AJAX requests
            if ($request->wantsJson() || str_contains($request->header('Content-Type') ?? '', 'application/json')) {
                return response()->json(['success' => true, 'message' => 'Penugasan diperbarui.'], 200);
            }
            return redirect()->route('user_upp.index')->with('success', 'Penugasan diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('UserUppController@update - Validation error:', $e->errors());
            if ($request->wantsJson() || str_contains($request->header('Content-Type') ?? '', 'application/json')) {
                return response()->json(['success' => false, 'errors' => $e->errors(), 'message' => 'Validasi gagal'], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('UserUppController@update - Error:', ['message' => $e->getMessage(), 'exception' => $e]);
            if ($request->wantsJson() || str_contains($request->header('Content-Type') ?? '', 'application/json')) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            throw $e;
        }
    }

    /**
     * Map role_sso to user_upp peran enum value
     * Reuses the same logic as PopulateUserUppFromLegacy command
     */
    private function mapRoleToPeran($role_sso)
    {
        $r = strtolower(trim((string)$role_sso));
        if ($r === '') return 'admin_upp';  // Default fallback

        if (str_contains($r, 'super')) return 'superadmin';
        if (str_contains($r, 'organis')) return 'admin_organisasi';
        if ($r === 'admin_opd' || $r === 'org_admin') return 'admin_upp';
        if (str_starts_with($r, 'admin')) return 'admin_upp';
        if (str_contains($r, 'verifik')) return 'verifikator';

        return 'admin_upp';  // Default fallback
    }

    public function destroy($id)
    {
        \Log::info('UserUppController@destroy - Deleting ID ' . $id);
        
        DB::table('user_upp')->where('id', $id)->delete();
        
        // Check if the request expects JSON (AJAX requests from JavaScript typically do)
        if (request()->wantsJson() || str_contains(request()->header('Content-Type') ?? '', 'application/json')) {
            return response()->json(['success' => true, 'message' => 'Penugasan dihapus.']);
        }
        return redirect()->route('user_upp.index')->with('success', 'Penugasan dihapus.');
    }
}
