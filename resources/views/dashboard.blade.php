@extends('layouts.app')

@section('title','PEKPP — Dashboard')

@section('content')
  <x-panel>
    <h1>Pemantauan dan Evaluasi Kinerja Penyelenggaraan Pelayanan Publik</h1>
    <p class="muted">Anda berhasil mengakses aplikasi melalui SSO.</p>

    @php
      $sso = session('sso.user');
      $role = null;
      if (is_array($sso) && !empty($sso['app_role'])) {
        $role = $sso['app_role'];
      } elseif (!empty($user->role_sso)) {
        $role = $user->role_sso;
      } else {
        // fallback: first assigned user_upp peran
        try { $first = $user->getUserUpps()->first(); if ($first) $role = $first->peran; } catch (\Throwable $e) { }
      }

      $level = null;
      if (is_array($sso)) {
        $isLocked = isset($sso['is_opd_locked']) ? (bool)$sso['is_opd_locked'] : false;
        $byApp = $sso['allowed_opd_ids_by_app'] ?? null;
        $allowed = null;
        if (is_array($byApp) && array_key_exists('pekpp', $byApp)) {
          $allowed = $byApp['pekpp'];
        } elseif (array_key_exists('allowed_opd_ids', $sso)) {
          $allowed = $sso['allowed_opd_ids'];
        }

        if (!$isLocked) {
          $level = 'Global';
        } elseif (is_array($allowed)) {
          if (count($allowed) === 1) {
            $opds = $sso['opds'] ?? [];
            $found = null;
            foreach ($opds as $o) {
              if (isset($o['id']) && (int)$o['id'] === (int)$allowed[0]) {
                $found = $o['name'] ?? null; break;
              }
            }
            $level = $found ?? ('OPD: ' . $allowed[0]);
          } else {
            $level = 'Multiple OPD (' . count($allowed) . ')';
          }
        } else {
          $level = 'Restricted';
        }
      } else {
        $level = !empty($user->sso_user_id) ? 'SSO' : 'Local';
      }
    @endphp

    <div class="user-info">
      <div class="user-info-left">
        <img src="{{ $user->email ? 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($user->email))).'?s=128&d=identicon' : asset('images/default-avatar.svg') }}" alt="avatar" style="width:96px;height:96px;border-radius:8px;object-fit:cover">
      </div>
      <div class="user-info-right">
        <dl>
          <dt>Nama</dt><dd>{{ $user->nama ?? $user->name ?? '(tidak tersedia)' }}</dd>
          <dt>Email</dt><dd>{{ $user->email ?? '(tidak tersedia)' }}</dd>
          <dt>SSO User ID</dt><dd>{{ $user->sso_user_id ?? '(belum)' }}</dd>
          <dt>Role</dt><dd>{{ $role ?? '(belum)' }}</dd>
          <dt>Level</dt><dd>{{ $level }}</dd>
        </dl>
      </div>
    </div>

    <p class="mt-4 text-sm text-gray-500">Jika informasi role belum muncul, jalankan mirror SSO: <code>php artisan sso:mirror-users --chunk=200</code></p>

  </x-panel>
@endsection