<tr id="row-{{ $it->id }}" data-id="{{ $it->id }}" data-user-id="{{ $it->user_id }}" data-upp-id="{{ $it->upp_id }}" data-peran="{{ $it->peran }}" data-aktif="{{ $it->aktif }}" data-user-data="{{ json_encode($it->user) }}" data-upp-data="{{ json_encode($it->upp) }}">
    <td>
        <div class="user_upp-user-cell">
            <span class="user_upp-user-name">{{ optional($it->user)->nama ?? optional($it->user)->name ?? 'User #' . $it->user_id }}</span>
            <span class="user_upp-user-email">{{ $it->user->email ?? '-' }}</span>
        </div>
    </td>
    <td>{{ $it->upp->nama ?? '-' }}</td>
    <td>
        <span class="user_upp-badge user_upp-badge-peran">{{ $it->peran }}</span>
    </td>
    <td>
        @if($it->aktif)
            <span class="user_upp-badge user_upp-badge-active">
                <span class="user_upp-badge-dot"></span>
                Aktif
            </span>
        @else
            <span class="user_upp-badge user_upp-badge-inactive">
                <span class="user_upp-badge-dot"></span>
                Nonaktif
            </span>
        @endif
    </td>
    <td style="text-align: center;">{{ optional($it->ditetapkanOleh)->nama ?? optional($it->ditetapkanOleh)->name ?? '-' }}</td>
    <td style="text-align: center; font-size: 13px; color: #64748b;">
        @if($it->ditetapkan_pada)
            {{ \Carbon\Carbon::parse($it->ditetapkan_pada)->format('d/m/Y H:i') }}
        @elseif($it->created_at)
            {{ \Carbon\Carbon::parse($it->created_at)->format('d/m/Y H:i') }}
        @else
            -
        @endif
    </td>
    <td style="text-align: center;">
        <div class="user_upp-actions-cell">
            <button type="button" class="user_upp-btn-icon" onclick="AdminUserUpp.openEditModal({{ $it->id }})" title="Edit">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
            </button>
            <button type="button" class="user_upp-btn-icon btn-danger" onclick="AdminUserUpp.openDeleteModal({{ $it->id }}, '{{ optional($it->user)->nama ?? 'User' }}')" title="Hapus">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    <line x1="10" y1="11" x2="10" y2="17"/>
                    <line x1="14" y1="11" x2="14" y2="17"/>
                </svg>
            </button>
        </div>
    </td>
</tr>
