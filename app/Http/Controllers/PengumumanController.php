<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    /**
     * Set up auth check to only allow Admin Internal
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            $isGlobalUser = $user && $user->hasGlobalRole([
                'superadmin', 'admin_organisasi', 'admin_bagian_organisasi', 'org_admin', 'org-admin'
            ]);

            if (!$isGlobalUser) {
                abort(403, 'Akses ditolak. Hanya Admin Internal yang dapat mengelola pengumuman.');
            }

            return $next($request);
        });
    }

    /**
     * List all announcements
     */
    public function index()
    {
        $pengumuman = Pengumuman::with('createdBy')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('pengumuman.index', compact('pengumuman'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('pengumuman.create');
    }

    /**
     * Store new announcement
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'judul'        => 'required|string|max:255',
            'isi'          => 'required|string',
            'published_at' => 'nullable|date',
            'expired_at'   => 'nullable|date|after_or_equal:published_at',
        ]);

        $data['aktif'] = $request->has('aktif');
        $data['created_by'] = auth()->id();

        Pengumuman::create($data);

        return redirect()->route('pengumuman.index')
            ->with('success', 'Pengumuman berhasil dibuat.');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        return view('pengumuman.edit', compact('pengumuman'));
    }

    /**
     * Update announcement
     */
    public function update(Request $request, $id)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        $data = $request->validate([
            'judul'        => 'required|string|max:255',
            'isi'          => 'required|string',
            'published_at' => 'nullable|date',
            'expired_at'   => 'nullable|date|after_or_equal:published_at',
        ]);

        $data['aktif'] = $request->has('aktif');

        $pengumuman->update($data);

        return redirect()->route('pengumuman.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    /**
     * Delete announcement
     */
    public function destroy($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        $pengumuman->delete();

        return redirect()->route('pengumuman.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }
}
