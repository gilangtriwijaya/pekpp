{{-- ========================================
     PEKPPP Topbar Component - Complete Version
     Includes inline CSS and JavaScript
     ======================================== --}}

<style>
/* ======================================================
   TOPBAR STYLES - INLINE VERSION
   ====================================================== */

.pekppp-ui .pekppp-topbar {
    height: 64px;
    flex: 0 0 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 1px 0 rgba(2,6,23,0.04);
    position: relative;
    z-index: 40;
}

.pekppp-ui .topbar-left,
.pekppp-ui .topbar-right {
    display: flex;
    align-items: center;
    gap: 16px;
}

/* Sidebar Toggle Button */
.pekppp-ui .topbar-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    padding: 0;
    background: transparent;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    color: #64748b;
    transition: all 120ms ease;
}

.pekppp-ui .topbar-toggle:hover {
    background: rgba(2,6,23,0.04);
    border-color: #0f172a;
    color: #0f172a;
}

.pekppp-ui .topbar-toggle svg {
    width: 20px;
    height: 20px;
}

/* Page Title */
.pekppp-ui .topbar-title-wrapper {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.pekppp-ui .topbar-title {
    font-size: 16px;
    font-weight: 600;
    color: #0f172a;
    line-height: 1.2;
    margin: 0;
}

.pekppp-ui .topbar-subtitle {
    font-size: 12px;
    color: #64748b;
    line-height: 1.2;
    margin: 0;
}

/* Cache Clear Button */
.pekppp-ui .btn-cache-clear {
    padding: 8px 14px;
    font-size: 13px;
    font-weight: 500;
    background: transparent;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #0f172a;
    cursor: pointer;
    transition: all 120ms ease;
}

.pekppp-ui .btn-cache-clear:hover {
    background: rgba(2,6,23,0.04);
    border-color: #0f172a;
}

/* Profile Widget */
.pekppp-ui .profile-widget {
    position: relative;
}

.pekppp-ui .profile-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 6px 12px 6px 6px;
    border-radius: 8px;
    background: transparent;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 120ms ease;
}

.pekppp-ui .profile-btn:hover {
    background: rgba(2,6,23,0.04);
    border-color: #e2e8f0;
}

.pekppp-ui .profile-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: #ffffff;
    border-radius: 50%;
    font-size: 15px;
    font-weight: 700;
    flex-shrink: 0;
}

.pekppp-ui .profile-text {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
    min-width: 0;
}

.pekppp-ui .profile-text .name {
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
    line-height: 1.2;
    white-space: nowrap;
}

.pekppp-ui .profile-text .subtitle {
    font-size: 12px;
    font-weight: 400;
    color: #64748b;
    line-height: 1.2;
    white-space: nowrap;
}

.pekppp-ui .profile-chevron {
    width: 18px;
    height: 18px;
    color: #64748b;
    transition: transform 150ms ease, color 150ms ease;
    flex-shrink: 0;
    margin-left: 4px;
}

.pekppp-ui .profile-widget.open .profile-chevron {
    transform: rotate(180deg);
    color: #0f172a;
}

/* Profile Dropdown Menu */
.pekppp-ui .profile-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 8px);
    min-width: 280px;
    max-width: 320px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 
        0 10px 25px rgba(15, 23, 42, 0.1),
        0 4px 10px rgba(15, 23, 42, 0.06);
    opacity: 0;
    transform: translateY(-8px) scale(0.95);
    pointer-events: none;
    transition: opacity 180ms ease, transform 180ms ease;
    overflow: hidden;
    z-index: 50;
}

.pekppp-ui .profile-widget.open .profile-menu {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: auto;
}

/* Profile Menu Header */
.pekppp-ui .profile-menu-header {
    padding: 16px;
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.02), transparent);
    border-bottom: 1px solid #e2e8f0;
}

.pekppp-ui .profile-menu-name {
    font-size: 15px;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 4px;
    line-height: 1.3;
}

.pekppp-ui .profile-menu-email {
    font-size: 13px;
    color: #64748b;
    line-height: 1.3;
    word-break: break-word;
}

/* Impersonation Notice */
.pekppp-ui .profile-menu-notice {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: rgba(59, 130, 246, 0.08);
    border-bottom: 1px solid rgba(59, 130, 246, 0.15);
    font-size: 11px;
    font-weight: 600;
    color: #2563eb;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

.pekppp-ui .profile-menu-notice svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

/* Role Section */
.pekppp-ui .profile-menu-section {
    padding: 12px 16px;
}

.pekppp-ui .profile-menu-role {
    padding: 0;
}

.pekppp-ui .role-title {
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 4px;
    line-height: 1.3;
}

.pekppp-ui .role-subtitle {
    font-size: 12px;
    color: #64748b;
    line-height: 1.4;
}

/* Divider */
.pekppp-ui .profile-menu-divider {
    height: 1px;
    background: #e2e8f0;
    margin: 0;
}

/* Logout Button */
.pekppp-ui .profile-menu-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 12px 16px;
    background: transparent;
    border: none;
    text-align: left;
    font-size: 13px;
    font-weight: 500;
    color: #0f172a;
    cursor: pointer;
    transition: background 100ms ease, color 100ms ease;
}

.pekppp-ui .profile-menu-item svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.pekppp-ui .profile-menu-item:hover {
    background: rgba(2, 6, 23, 0.04);
}

.pekppp-ui .profile-menu-item.danger {
    color: #ef4444;
}

.pekppp-ui .profile-menu-item.danger:hover {
    background: rgba(239, 68, 68, 0.08);
    color: #dc2626;
}

/* Responsive */
@media (max-width: 768px) {
    .pekppp-ui .pekppp-topbar {
        padding: 0 16px;
    }
    
    .pekppp-ui .topbar-left,
    .pekppp-ui .topbar-right {
        gap: 12px;
    }
    
    .pekppp-ui .btn-cache-clear {
        display: none;
    }
    
    .pekppp-ui .profile-text {
        display: none;
    }
    
    .pekppp-ui .profile-chevron {
        display: none;
    }
    
    .pekppp-ui .profile-btn {
        padding: 6px;
    }
    
    .pekppp-ui .profile-menu {
        right: -8px;
        min-width: 260px;
    }
}

/* Focus States */
.pekppp-ui .topbar-toggle:focus,
.pekppp-ui .profile-btn:focus,
.pekppp-ui .btn-cache-clear:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

.pekppp-ui .profile-menu-item:focus {
    outline: none;
    background: rgba(2, 6, 23, 0.06);
}

/* Impersonate Modal Styles */
.pekppp-ui .impersonate-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 100;
}

.pekppp-ui .impersonate-modal-overlay.active {
    display: flex;
}

.pekppp-ui .impersonate-modal {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 20px 45px rgba(0, 0, 0, 0.15);
    width: 90%;
    max-width: 420px;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: slideUp 300ms ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.pekppp-ui .impersonate-modal-header {
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.pekppp-ui .impersonate-modal-title {
    font-size: 16px;
    font-weight: 600;
    color: #0f172a;
    margin: 0;
}

.pekppp-ui .impersonate-modal-close {
    background: transparent;
    border: none;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #64748b;
    border-radius: 6px;
    transition: all 120ms ease;
    padding: 0;
}

.pekppp-ui .impersonate-modal-close:hover {
    background: rgba(2, 6, 23, 0.08);
    color: #0f172a;
}

.pekppp-ui .impersonate-search {
    padding: 16px;
    border-bottom: 1px solid #e2e8f0;
}

.pekppp-ui .impersonate-search-input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    color: #0f172a;
    transition: all 120ms ease;
}

.pekppp-ui .impersonate-search-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.pekppp-ui .impersonate-search-input::placeholder {
    color: #cbd5e1;
}

.pekppp-ui .impersonate-list {
    flex: 1;
    overflow-y: auto;
    padding: 8px 0;
}

.pekppp-ui .impersonate-list-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: transparent;
    border: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
    transition: background 120ms ease;
    color: #0f172a;
    font-size: 14px;
    border-bottom: 1px solid #f1f5f9;
}

.pekppp-ui .impersonate-list-item:hover {
    background: rgba(2, 6, 23, 0.04);
}

.pekppp-ui .impersonate-list-item:active {
    background: rgba(2, 6, 23, 0.08);
}

.pekppp-ui .impersonate-list-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    flex-shrink: 0;
}

.pekppp-ui .impersonate-list-info {
    flex: 1;
    min-width: 0;
}

.pekppp-ui .impersonate-list-name {
    font-weight: 600;
    font-size: 14px;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pekppp-ui .impersonate-list-email {
    font-size: 12px;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pekppp-ui .impersonate-list-upp {
    font-size: 12px;
    color: #7c3aed;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pekppp-ui .impersonate-list-empty {
    padding: 32px 16px;
    text-align: center;
    color: #94a3b8;
    font-size: 13px;
}

.pekppp-ui .impersonate-modal-footer {
    padding: 12px 16px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 8px;
}

.pekppp-ui .impersonate-btn-cancel {
    flex: 1;
    padding: 10px 16px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #0f172a;
    cursor: pointer;
    transition: all 120ms ease;
}

.pekppp-ui .impersonate-btn-cancel:hover {
    background: #e2e8f0;
}

/* Scrollbar styling */
.pekppp-ui .impersonate-list::-webkit-scrollbar {
    width: 6px;
}

.pekppp-ui .impersonate-list::-webkit-scrollbar-track {
    background: transparent;
}

.pekppp-ui .impersonate-list::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.pekppp-ui .impersonate-list::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

<header class="pekppp-topbar">
    <div class="topbar-left">
        {{-- Sidebar toggle --}}
        <button type="button"
                class="topbar-toggle"
                data-sidebar-toggle
                aria-label="Toggle sidebar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" 
                      stroke-linejoin="round" 
                      d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        {{-- Page title --}}
        <div class="topbar-title-wrapper">
            <div class="topbar-title">
                @yield('page_title', trim($__env->yieldContent('title')))
            </div>
            @hasSection('page_subtitle')
                <div class="topbar-subtitle">
                    @yield('page_subtitle')
                </div>
            @endif
        </div>
    </div>

    <div class="topbar-right">

        {{-- Profile Widget --}}
        <div class="profile-widget" id="profileWidget">
            <button type="button"
                    class="profile-btn"
                    id="profileBtn"
                    onclick="toggleProfileDropdown(event)"
                    aria-haspopup="true"
                    aria-expanded="false">
                
                {{-- Avatar --}}
                <span class="profile-avatar">
                    {{ strtoupper(substr($user->nama ?? $user->name ?? 'U', 0, 1)) }}
                </span>

                {{-- Name & Role --}}
                <span class="profile-text">
                    <span class="name">{{ $user->nama ?? $user->name ?? 'User' }}</span>
                    <span class="subtitle">{{ $user_role_label ?? $user->role_sso ?? 'Profil' }}</span>
                </span>

                {{-- Dropdown chevron --}}
                <svg class="profile-chevron" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" 
                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" 
                          clip-rule="evenodd"/>
                </svg>
            </button>

            {{-- Dropdown Menu --}}
            <div class="profile-menu">
                {{-- User Info Header --}}
                <div class="profile-menu-header">
                    <div class="profile-menu-name">{{ $user->nama ?? $user->name }}</div>
                    <div class="profile-menu-email">{{ $user->email ?? ($user->username ?? '') . '@anambaskab.go.id' }}</div>
                </div>

                {{-- Impersonation Notice --}}
                @if(session('impersonated_by'))
                    <div class="profile-menu-notice">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span>IMPERSONASI · SETDA</span>
                    </div>
                @endif

                {{-- Role Info --}}
                <div class="profile-menu-section">
                    <div class="profile-menu-role">
                        <div class="role-title">{{ $user_role_label ?? $user->role_sso ?? 'User' }}</div>
                        <div class="role-subtitle">
                            {{ $user->unit_kerja ?? ($user->bidang ?? 'Unit Kerja') }} · {{ $user->jabatan ?? 'admin opd' }}
                        </div>
                    </div>
                </div>

                <div class="profile-menu-divider"></div>

                {{-- Impersonate Button (Superadmin only, when not impersonating) --}}
                @if(auth()->user()?->hasGlobalRole('superadmin') && !session()->has('impersonating_user_id'))
                    <button type="button" 
                            class="profile-menu-item"
                            id="btn-impersonate-user"
                            onclick="event.stopPropagation(); openImpersonateModal();">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path d="M11 3a1 1 0 10-2 0 1 1 0 012 0zM15.657 5.757a1 1 0 00-1.414-1.414l-1.623 1.623a6.002 6.002 0 01-5.647 0L7.757 4.343a1 1 0 00-1.414 1.414l1.623 1.623a6.002 6.002 0 010 5.647l-1.623 1.623a1 1 0 101.414 1.414l1.623-1.623a6.002 6.002 0 015.647 0l1.623 1.623a1 1 0 101.414-1.414l-1.623-1.623a6.002 6.002 0 010-5.647l1.623-1.623z"/>
                        </svg>
                        Impersonate User
                    </button>

                    <div class="profile-menu-divider"></div>
                @endif

                {{-- Stop Impersonate Button (visible when impersonating) --}}
                @if(session()->has('impersonating_user_id'))
                    <button type="button" 
                            class="profile-menu-item danger"
                            onclick="event.stopPropagation(); stopImpersonateUser();">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                        </svg>
                        Kembali ke Superadmin
                    </button>

                    <div class="profile-menu-divider"></div>
                @endif

                {{-- Logout Button --}}
                <form method="POST" action="{{ route('sso.logout') }}">
                    @csrf
                    <button type="submit" class="profile-menu-item danger">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

{{-- Impersonate Modal --}}
<div class="pekppp-ui impersonate-modal-overlay" id="impersonateModalOverlay" onclick="closeImpersonateModal(event)">
    <div class="impersonate-modal" onclick="event.stopPropagation()">
        {{-- Modal Header --}}
        <div class="impersonate-modal-header">
            <h2 class="impersonate-modal-title">Pilih User untuk Impersonate</h2>
            <button type="button" 
                    class="impersonate-modal-close"
                    onclick="event.stopPropagation(); closeImpersonateModal();"
                    aria-label="Tutup modal">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width: 20px; height: 20px;">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>

        {{-- Search Box --}}
        <div class="impersonate-search">
            <input type="text" 
                   class="impersonate-search-input"
                   id="impersonateSearchInput"
                   placeholder="Cari user, email, atau UPP..."
                   oninput="filterImpersonateUsers()">
        </div>

        {{-- Users List --}}
        <div class="impersonate-list" id="impersonateUsersList">
            <div class="impersonate-list-empty">
                <svg style="width: 32px; height: 32px; margin: 0 auto 8px; opacity: 0.5;" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                </svg>
                Memuat data user...
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="impersonate-modal-footer">
            <button type="button" 
                    class="impersonate-btn-cancel"
                    onclick="event.stopPropagation(); closeImpersonateModal();">
                Batalkan
            </button>
        </div>
    </div>
</div>

<script>
console.log('=== Topbar Script Started ===');

// Simple toggle function for profile dropdown
function toggleProfileDropdown(e) {
    console.log('%c CLICK EVENT FIRED ', 'background: #4CAF50; color: white; font-weight: bold;');
    
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    const widget = document.getElementById('profileWidget');
    console.log('profileWidget element:', widget);
    
    if (widget) {
        const wasOpen = widget.classList.contains('open');
        widget.classList.toggle('open');
        const isOpen = widget.classList.contains('open');
        
        console.log(`%c TOGGLE RESULT: ${wasOpen ? 'OPEN' : 'CLOSED'} -> ${isOpen ? 'OPEN' : 'CLOSED'}`, 
                   `background: ${isOpen ? '#4CAF50' : '#f44336'}; color: white; font-weight: bold;`);
        
        // Visual test: alert on toggle
        console.log('Classes on widget:', widget.className);
    } else {
        console.error('%c ❌ profileWidget NOT FOUND!', 'background: #f44336; color: white; font-weight: bold;');
        alert('DEBUG: profileWidget not found! Check browser console.');
    }
    
    return false;
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const widget = document.getElementById('profileWidget');
    if (widget && !widget.contains(e.target)) {
        if (widget.classList.contains('open')) {
            console.log('%c OUTSIDE CLICK: Closing dropdown', 'background: #2196F3; color: white;');
            widget.classList.remove('open');
        }
    }
}, true);

console.log('=== Topbar Script Setup Complete ===');

function openImpersonateModal(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    const overlay = document.getElementById('impersonateModalOverlay');
    if (overlay) {
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Close profile dropdown when modal opens
    const profileWidget = document.getElementById('profileWidget');
    if (profileWidget) {
        profileWidget.classList.remove('open');
    }
    
    // Load impersonate users
    loadImpersonateUsers();
}

function closeImpersonateModal(event) {
    // If event exists and target is not the overlay itself, don't close
    if (event) {
        event.preventDefault();
        event.stopPropagation();
        
        // Only close if clicking on overlay background (not on modal content)
        if (event.target.id !== 'impersonateModalOverlay') {
            return;
        }
    }
    
    const overlay = document.getElementById('impersonateModalOverlay');
    if (overlay) {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Clear search
    const searchInput = document.getElementById('impersonateSearchInput');
    if (searchInput) {
        searchInput.value = '';
    }
}

function loadImpersonateUsers() {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    console.log('Loading impersonate users...');
    
    fetch('{{ route("api.impersonate.users") }}', {
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Users list response status:', response.status);
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Users data received:', data);
        if (data.success && data.users) {
            renderImpersonateUserList(data.users);
        } else {
            console.error('Failed to load users:', data);
            const usersList = document.getElementById('impersonateUsersList');
            if (usersList) {
                usersList.innerHTML = '<div class="impersonate-list-empty">Gagal memuat daftar user</div>';
            }
        }
    })
    .catch(error => {
        console.error('Error loading users:', error);
        document.getElementById('impersonateUsersList').innerHTML = 
            '<div class="impersonate-list-empty">Gagal memuat data user: ' + error.message + '</div>';
    });
}

function renderImpersonateUserList(users) {
    const usersList = document.getElementById('impersonateUsersList');
    
    if (users.length === 0) {
        usersList.innerHTML = '<div class="impersonate-list-empty">Tidak ada user tersedia untuk impersonate</div>';
        return;
    }
    
    usersList.innerHTML = users.map(user => `
        <button type="button" 
                class="impersonate-list-item"
                onclick="startImpersonateUser(${user.id}, event)"
                data-user-id="${user.id}"
                data-user-name="${user.nama}"
                data-user-email="${user.email}"
                data-upp-name="${user.upp_nama || ''}">
            <div class="impersonate-list-avatar">
                ${user.nama.charAt(0).toUpperCase()}
            </div>
            <div class="impersonate-list-info">
                <div class="impersonate-list-name">${escapeHtml(user.nama)}</div>
                <div class="impersonate-list-email">${escapeHtml(user.email)}</div>
                ${user.upp_nama ? `<div class="impersonate-list-upp">${escapeHtml(user.upp_nama)}</div>` : ''}
            </div>
        </button>
    `).join('');
}

function filterImpersonateUsers() {
    const searchValue = document.getElementById('impersonateSearchInput').value.toLowerCase();
    const userItems = document.querySelectorAll('.impersonate-list-item');
    
    userItems.forEach(item => {
        const name = item.dataset.userName.toLowerCase();
        const email = item.dataset.userEmail.toLowerCase();
        const upp = item.dataset.uppName.toLowerCase();
        
        const matches = name.includes(searchValue) || email.includes(searchValue) || upp.includes(searchValue);
        item.style.display = matches ? 'flex' : 'none';
    });
}

function startImpersonateUser(userId, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    console.log('Starting impersonate for userId:', userId);
    
    fetch('{{ route("api.impersonate.start") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(response => {
        console.log('Response status:', response.status, response.statusText);
        
        // Check if response is valid JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            console.error('❌ Response is not JSON! Content-Type:', contentType);
            throw new Error('Response is not JSON');
        }
        
        return response.json().then(data => ({
            ok: response.ok,
            status: response.status,
            data: data
        }));
    })
    .then(({ ok, status, data }) => {
        console.log('Parsed response:', ok, status, data);
        
        if (!ok) {
            console.error('❌ API returned error status:', status);
            showNotification(data.message || 'Gagal melakukan impersonasi (Error ' + status + ')', 'error');
            return;
        }
        
        if (data.success) {
            console.log('✓ Impersonate successful');
            showNotification('Berhasil melakukan impersonasi sebagai ' + data.user.nama, 'success');
            
            // Close modal
            closeImpersonateModal();
            
            // Reload page after short delay to apply impersonation
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            console.error('❌ Success is false:', data.message);
            showNotification(data.message || 'Gagal melakukan impersonasi', 'error');
        }
    })
    .catch(error => {
        console.error('❌ Fetch error:', error);
        showNotification('Gagal melakukan impersonasi: ' + error.message, 'error');
    });
}

function stopImpersonateUser() {
    if (!confirm('Apakah Anda yakin ingin kembali ke akun superadmin?')) {
        return;
    }
    
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    console.log('Stopping impersonation...');
    
    fetch('{{ route("api.impersonate.stop") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Stop impersonate response status:', response.status, response.statusText);
        
        // Check if response is valid JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            console.error('❌ Response is not JSON! Content-Type:', contentType);
            throw new Error('Response is not JSON');
        }
        
        return response.json().then(data => ({
            ok: response.ok,
            status: response.status,
            data: data
        }));
    })
    .then(({ ok, status, data }) => {
        console.log('Parsed response:', ok, status, data);
        
        if (!ok) {
            console.error('❌ API returned error status:', status);
            showNotification(data.message || 'Gagal kembali ke superadmin (Error ' + status + ')', 'error');
            return;
        }
        
        if (data.success) {
            console.log('✓ Stop impersonate successful');
            showNotification('Kembali ke akun superadmin', 'success');
            
            // Close profile dropdown
            const profileWidget = document.querySelector('.profile-widget');
            profileWidget?.classList.remove('open');
            
            // Reload page
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            console.error('❌ Success is false:', data.message);
            showNotification(data.message || 'Gagal kembali ke superadmin', 'error');
        }
    })
    .catch(error => {
        console.error('❌ Fetch error:', error);
        showNotification('Gagal kembali ke superadmin: ' + error.message, 'error');
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showNotification(message, type = 'info') {
    // Just log for now - you might want to implement a toast notification system
    console.log(`[${type.toUpperCase()}] ${message}`);
    
    // Simple alert as fallback
    if (type === 'error') {
        alert(message);
    }
}
</script>