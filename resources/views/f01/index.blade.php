@extends('layouts.app')

@section('title', 'Daftar Pengisian F01')
@section('page_title', 'Daftar Pengisian F01')

@section('content')
<x-panel>
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
    <div>
      <div style="font-weight:700">Daftar Pengisian F01</div>
      <div style="font-size:13px;color:var(--muted)">Periode / UPP yang dapat Anda akses</div>
    </div>
  </div>
  <form method="GET" class="mb-3" style="display:flex;gap:8px;align-items:end;flex-wrap:wrap">
    <div>
      <label>Periode</label>
      <select name="periode_id" class="form-control">
        <option value="">Semua</option>
        @foreach(($periodes ?? []) as $pd)
        <option value="{{ $pd->id }}" {{ request('periode_id') == $pd->id ? 'selected' : '' }}>{{ $pd->nama }} ({{ $pd->tahun }})</option>
        @endforeach
      </select>
    </div>
    <div>
      <label>UPP</label>
      <select name="upp_id" class="form-control">
        <option value="">Semua</option>
        @foreach(($uppOptions ?? []) as $u)
        <option value="{{ $u->id }}" {{ request('upp_id') == $u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label>Status</label>
      <select name="status" class="form-control">
        <option value="">Semua</option>
        <option value="draft" {{ request('status')=='draft' ? 'selected' : '' }}>Draft</option>
        <option value="final" {{ request('status')=='final' ? 'selected' : '' }}>Final</option>
      </select>
    </div>
    <div>
      <label>&nbsp;</label>
      <div style="display:flex;gap:6px">
        <button class="btn btn-primary">Filter</button>
        <a href="{{ route('f01.index') }}" class="btn btn-secondary">Reset</a>
      </div>
    </div>
  </form>

  <table class="table table-striped">
    <thead>
      <tr>
        <th>#</th>
        <th>Periode</th>
        <th>UPP</th>
        <th>Status</th>
        <th>Submitted At</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @forelse($pengisian as $p)
      <tr>
        <td>{{ $p->id }}</td>
        <td>{{ optional($p->periode)->nama ?? ($p->periode_label ?? '-') }}</td>
        <td>{{ optional($p->upp)->nama ?? '-' }}</td>
        <td>{{ $p->status }}</td>
        <td>{{ $p->submitted_at ? \Carbon\Carbon::parse($p->submitted_at)->toDayDateTimeString() : '-' }}</td>
        <td>
          <a href="{{ route('f01.show', $p->id) }}" class="btn btn-sm btn-outline-primary">Buka</a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="6">Tidak ada pengisian tersedia.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="flex justify-end">{{ $pengisian->links() }}</div>
</x-panel>
@endsection
