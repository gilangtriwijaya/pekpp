<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    /**
     * Only superadmin can impersonate other users
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()) {
                // Not authenticated
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
                }
                return redirect('/');
            }
            
            // Allow if:
            // 1. User is currently impersonating (session has impersonated_by) - for stop action
            // 2. User is superadmin (normal superadmin access)
            $isImpersonating = session()->has('impersonated_by');
            $isSuperadmin = auth()->user()->hasGlobalRole('superadmin');
            
            if (!$isImpersonating && !$isSuperadmin) {
                // Not superadmin and not impersonating
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk menggunakan fitur ini.'], 403);
                }
                return redirect('/')->with('error', 'Anda tidak memiliki izin untuk menggunakan fitur ini.');
            }
            
            return $next($request);
        });
    }

    /**
     * Get list of all active UPP users with their UPP information
     * Used for impersonate modal dropdown
     */
    public function getImpersonateUsers()
    {
        $users = User::with('userUpps.upp')
            ->where('aktif', 1)
            ->whereHas('userUpps', function($q) {
                $q->where('aktif', 1)
                  ->where('peran', '!=', 'superadmin');
            })
            ->orderBy('nama')
            ->get()
            ->map(function ($user) {
                $userUpp = $user->userUpps()->where('aktif', 1)->first();
                return [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'upp_id' => $userUpp?->upp_id,
                    'upp_nama' => $userUpp?->upp?->nama,
                    'peran' => $userUpp?->peran,
                ];
            })
            ->toArray();

        return response()->json([
            'success' => true,
            'users' => $users,
        ]);
    }

    /**
     * Start impersonating a user
     */
    public function startImpersonate(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', array_map(fn($errs) => implode(', ', $errs), $e->errors())),
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $targetUser = User::findOrFail($validated['user_id']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        // Validate: target user must have at least one active UPP role (not superadmin)
        if (!$targetUser->userUpps()->where('aktif', 1)->whereNotIn('peran', ['superadmin', 'admin_organisasi', 'admin_bagian_organisasi'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak memiliki role yang dapat di-impersonate.',
            ], 403);
        }

        // Get current authenticated user for tracking
        $currentUser = auth()->user();

        // Set impersonate session
        session()->put('impersonating_user_id', $targetUser->id);
        session()->put('impersonated_by', $currentUser->id);
        
        // Save to session immediately
        session()->save();

        // Log the impersonate action
        ActivityLog::record('IMPERSONATE_START', [
            'params' => [
                'impersonating_user_id' => $targetUser->id,
                'impersonating_user_name' => $targetUser->nama,
                'impersonating_upp_id' => $targetUser->userUpps()->where('aktif', 1)->first()?->upp_id,
                'impersonating_upp_name' => $targetUser->userUpps()->where('aktif', 1)->first()?->upp?->nama,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Berhasil melakukan impersonasi sebagai {$targetUser->nama}.",
            'user' => [
                'id' => $targetUser->id,
                'nama' => $targetUser->nama,
                'email' => $targetUser->email,
            ],
        ]);
    }

    /**
     * Stop impersonating and return to superadmin
     */
    public function stopImpersonate(Request $request)
    {
        $impersonatingUserId = session()->pull('impersonating_user_id');
        $impersonatedBy = session()->pull('impersonated_by');

        if ($impersonatingUserId && $impersonatedBy) {
            $impersonatingUser = User::find($impersonatingUserId);

            // Log the stop impersonate action
            ActivityLog::record('IMPERSONATE_STOP', [
                'params' => [
                    'impersonating_user_id' => $impersonatingUserId,
                    'impersonating_user_name' => $impersonatingUser?->nama,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kembali ke akun superadmin Anda.',
        ]);
    }

    /**
     * Check current impersonate status
     */
    public function checkImpersonateStatus()
    {
        $impersonatingUserId = session()->get('impersonating_user_id');

        if (!$impersonatingUserId) {
            return response()->json([
                'success' => true,
                'impersonating' => false,
            ]);
        }

        $user = User::find($impersonatingUserId);
        $userUpp = $user?->userUpps()->where('aktif', 1)->first();

        return response()->json([
            'success' => true,
            'impersonating' => true,
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'email' => $user->email,
                'upp_nama' => $userUpp?->upp?->nama,
            ],
        ]);
    }
}
