@extends('layouts.app')

@section('title','Activity Logs')
@section('page_title','Activity Logs')

@section('content')
<style>
  .activity-table thead th { background: #f8fafc; border-bottom:1px solid #eef2f7; }
  .activity-table td, .activity-table th { padding: 12px; }
  .detail-button { color:#0ea5a4; text-decoration:none; cursor:pointer; background:transparent; border:0; padding:0 }
  .modal-backdrop{position:fixed;left:0;top:0;right:0;bottom:0;background:rgba(0,0,0,0.35);display:none;align-items:center;justify-content:center;z-index:9999}
  .modal-box{background:#fff;padding:18px;border-radius:10px;max-width:900px;width:96%;max-height:80vh;overflow:auto}
</style>

<x-panel>
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
    <div style="font-size:20px;font-weight:700">Activity Logs</div>
    <div>
      <a href="{{ route('activity-logs.index') }}" class="reset-btn" style="padding:8px 12px;background:#eef2f7;border-radius:8px;color:var(--accent);text-decoration:none;display:inline-block">Reset</a>
    </div>
  </div>

  <div class="filters" style="margin-bottom:12px">
    <form id="filterForm" method="GET" action="{{ route('activity-logs.index') }}" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <input name="q" value="{{ $q ?? '' }}" placeholder="Search action, route, path or IP" style="border:1px solid #e6eef8;padding:8px 10px;border-radius:8px;min-width:220px">
      <select name="user" style="border:1px solid #e6eef8;padding:8px 10px;border-radius:8px;background:#fff;min-width:200px">
        <option value="">All users</option>
        @if(!empty($users) && $users->count())
          @foreach(($users ?? []) as $u)
            <option value="{{ $u->id }}" {{ (string)($userFilter ?? '') === (string)$u->id ? 'selected' : '' }}>{{ $u->nama ?? $u->name ?? 'User#'.$u->id }}</option>
          @endforeach
        @endif
      </select>
      <input name="action" value="{{ $actionFilter ?? '' }}" placeholder="Action contains" style="border:1px solid #e6eef8;padding:8px 10px;border-radius:8px;min-width:180px">
      <label style="font-size:12px;color:var(--muted);margin-right:6px">From</label>
      <input type="date" name="start" value="{{ $start ?? '' }}" style="border:1px solid #e6eef8;padding:8px 10px;border-radius:8px">
      <label style="font-size:12px;color:var(--muted);margin-right:6px">To</label>
      <input type="date" name="end" value="{{ $end ?? '' }}" style="border:1px solid #e6eef8;padding:8px 10px;border-radius:8px">

      <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
        <a href="{{ route('activity-logs.index') }}" class="reset-btn" style="padding:8px 12px;background:#eef2f7;border-radius:8px;color:var(--accent);text-decoration:none;display:inline-block">Reset</a>
        <button type="submit" style="background:#0ea5a4;color:#fff;padding:8px 12px;border-radius:8px;border:0">Search</button>
      </div>
    </form>
  </div>

  @if($logs->count() === 0)
    <div style="padding:40px;text-align:center;color:var(--muted)">Tidak ada aktivitas yang tercatat.</div>
  @else
    <div style="overflow:auto">
      <table class="table-modern activity-table" style="width:100%;border-collapse:collapse;font-size:14px">
        <thead style="text-align:left">
          <tr>
            <th>When (WIB)</th>
            <th>User</th>
            <th>Action</th>
            <th>Route / Path</th>
            <th>IP</th>
            <th>Details</th>
          </tr>
        </thead>
        <tbody>
          @foreach(($logs ?? []) as $log)
          <tr style="border-bottom:1px solid #f1f5f9">
            <td>{{ $log->created_at->format('Y-m-d H:i:s') }} WIB</td>
            <td>{{ optional($log->user)->nama ?? optional($log->user)->name ?? ($log->user_id ? 'User#'.$log->user_id : '-') }}</td>
            <td>{{ $log->action }}</td>
            <td>{{ $log->route ?? $log->path }}</td>
            <td>{{ $log->ip }}</td>
            <td><button class="detail-button" data-id="{{ $log->id }}">Detail</button></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-3">{{ $logs->links() }}</div>
  @endif

</x-panel>

<!-- Modal markup -->
<div id="logDetailModal" class="modal-backdrop" role="dialog" aria-hidden="true">
  <div class="modal-box" role="document">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
      <h3 id="modalTitle" style="margin:0;font-size:18px">Activity Detail</h3>
      <button id="modalClose" style="background:transparent;border:0;font-size:18px;cursor:pointer">✕</button>
    </div>
    <div id="modalBody">
      <div style="color:var(--muted);margin-bottom:12px">Recorded at: <span id="m_created_at"></span></div>

      <div style="display:grid;grid-template-columns:160px 1fr;gap:12px;align-items:start;font-size:14px">
        <div style="font-weight:700;color:var(--accent)">User</div>
        <div id="m_user"></div>

        <div style="font-weight:700;color:var(--accent)">Action</div>
        <div id="m_action"></div>

        <div style="font-weight:700;color:var(--accent)">Route</div>
        <div id="m_route"></div>

        <div style="font-weight:700;color:var(--accent)">Method / Path</div>
        <div id="m_method_path"></div>

        <div style="font-weight:700;color:var(--accent)">IP</div>
        <div id="m_ip"></div>

        <div style="font-weight:700;color:var(--accent)">User agent</div>
        <div style="color:var(--muted)"><pre id="m_user_agent" style="margin:0;background:#f8fafc;padding:8px;border-radius:6px"></pre></div>

        <div style="font-weight:700;color:var(--accent)">Params</div>
        <div><pre id="m_params" style="margin:0;background:#f8fafc;padding:8px;border-radius:6px;overflow:auto"></pre></div>
      </div>
    </div>
  </div>
</div>

<script>
  (function(){
    function qs(selector, el){ return (el||document).querySelector(selector); }
    function qsa(selector, el){ return Array.from((el||document).querySelectorAll(selector)); }

    // Modal handlers
    const modal = qs('#logDetailModal');
    const close = qs('#modalClose');

    function openModal(){ modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false'); }
    function closeModal(){ modal.style.display = 'none'; modal.setAttribute('aria-hidden','true'); }

    close.addEventListener('click', closeModal);
    modal.addEventListener('click', function(e){ if(e.target === modal) closeModal(); });

    qsa('.detail-button').forEach(function(btn){
      btn.addEventListener('click', function(){
        const id = this.getAttribute('data-id');
        fetch("{{ url('/activity-logs') }}" + '/' + id, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then(r=>{
            if(!r.ok) return r.text().then(t=>Promise.reject(new Error('HTTP '+r.status+': '+t)));
            return r.json().catch(err=> r.text().then(t=>({ raw: t })) );
          })
          .then(data=>{
            if(data.raw){
              qs('#m_created_at').textContent = '';
              qs('#m_user').textContent = '-';
              qs('#m_action').textContent = '-';
              qs('#m_route').textContent = '-';
              qs('#m_method_path').textContent = '';
              qs('#m_ip').textContent = '';
              qs('#m_user_agent').textContent = '';
              qs('#m_params').textContent = data.raw;
              openModal();
              return;
            }

            qs('#m_created_at').textContent = data.created_at || '';
            qs('#m_user').textContent = data.user || '-';
            qs('#m_action').textContent = data.action || '';
            qs('#m_route').textContent = data.route || '-';
            qs('#m_method_path').textContent = (data.method || '') + ' ' + (data.path || '');
            qs('#m_ip').textContent = data.ip || '';
            qs('#m_user_agent').textContent = data.user_agent || '';
            try{ qs('#m_params').textContent = JSON.stringify(data.params || {}, null, 2); }catch(e){ qs('#m_params').textContent = String(data.params || ''); }
            openModal();
          }).catch(err=>{
            alert('Gagal memuat detail: ' + err.message);
            console.error(err);
          });
      });
    });

    // Filter form validation (start <= end)
    const filterForm = qs('#filterForm');
    if(filterForm){
      filterForm.addEventListener('submit', function(e){
        const start = qs('input[name="start"]', filterForm).value;
        const end = qs('input[name="end"]', filterForm).value;
        if(start && end){
          if(new Date(start) > new Date(end)){
            e.preventDefault();
            alert('Range tanggal tidak valid: "From" tidak boleh lebih besar dari "To".');
            return false;
          }
        }
      });
    }
  })();
</script>

@endsection
