<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserUpp;

class UppController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        // if there are any UserUpp rows, show authorizations; otherwise fall back to show UPPs
        $userUppCount = UserUpp::count();
        if ($userUppCount > 0) {
            $query = UserUpp::with(['user', 'upp.opd', 'upp.opdUnit']);

            if ($q !== '') {
                $query->where(function ($qb) use ($q) {
                    $qb->whereHas('user', function ($u) use ($q) {
                        $u->where('nama', 'like', "%{$q}%")->orWhere('nip', 'like', "%{$q}%");
                    })
                    ->orWhereHas('upp', function ($u2) use ($q) {
                        $u2->where('nama', 'like', "%{$q}%")->orWhere('kode', 'like', "%{$q}%")->orWhere('jenis', 'like', "%{$q}%");
                    })
                    ->orWhere('peran', 'like', "%{$q}%");
                });
            }

            $query->orderBy('id', 'desc');
            $perPage = (int) $request->get('per_page', 25);
            $list = $query->paginate(max(1, min(200, $perPage)))->withQueryString();

            return view('upps.index', ['list' => $list, 'q' => $q, 'mode' => 'user_upp']);
        }

        // fallback: list upps directly (no user mappings)
        $uppQuery = \App\Models\Upp::with(['opd', 'opdUnit']);
        if ($q !== '') {
            $uppQuery->where(function ($u) use ($q) {
                $u->where('nama', 'like', "%{$q}%")->orWhere('kode', 'like', "%{$q}%")->orWhere('jenis', 'like', "%{$q}%");
            });
        }
        $uppQuery->orderBy('id', 'desc');
        $perPage = (int) $request->get('per_page', 25);
        $upps = $uppQuery->paginate(max(1, min(200, $perPage)))->withQueryString();

        return view('upps.index', ['list' => $upps, 'q' => $q, 'mode' => 'upp']);
    }
}
