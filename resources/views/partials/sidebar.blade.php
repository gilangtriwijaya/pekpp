<aside class="pekppp-sidebar" aria-label="Primary navigation">
  <style>
    .pekppp-sidebar .nav-icon,
    .pekppp-sidebar .accordion-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 2rem;
      height: 2rem;
      border-radius: 0.75rem;
      background: linear-gradient(180deg, #ffffff, #f8fafc);
      color: #64748b;
      flex: 0 0 auto;
      border: 1px solid rgba(148, 163, 184, 0.22);
      box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
      opacity: 0.96;
    }
    .pekppp-sidebar .nav-icon svg,
    .pekppp-sidebar .accordion-icon svg {
      width: 1rem;
      height: 1rem;
      stroke: currentColor;
      fill: none;
      stroke-width: 1.9;
      stroke-linecap: round;
      stroke-linejoin: round;
    }
    .pekppp-sidebar .nav-item {
      gap: 0.75rem;
    }
    .pekppp-sidebar .nav-section-title {
      gap: 0.75rem;
    }
    .pekppp-sidebar .accordion-content {
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
    }
    /* Hide scrollbar inline */
    .pekppp-sidebar .sidebar-nav::-webkit-scrollbar {
      display: none;
    }
    .pekppp-sidebar .sidebar-nav {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }
  </style>
  <div class="sidebar-inner">

    @php
      $sidebarIcon = function (string $name) {
        $icons = [
          'dashboard' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 13h6V4H4v9Zm10 7h6V10h-6v10ZM4 20h6v-5H4v5Zm10-13h6V4h-6v3Z"/></svg>',
          'analytics' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19h16"/><path d="M7 17V9"/><path d="M12 17V5"/><path d="M17 17v-7"/></svg>',
          'trend' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19h16"/><path d="M5 15l5-5 4 4 5-7"/><path d="m14 7 5 0v5"/></svg>',
          'history' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v6h6"/><path d="M12 7v5l3 2"/></svg>',
          'users' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
          'calendar' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 2v4"/><path d="M16 2v4"/><path d="M3 10h18"/><path d="M5 4h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/></svg>',
          'file' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7z"/><path d="M14 2v5h5"/><path d="M9 13h6"/><path d="M9 17h6"/></svg>',
          'layers' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5"/><path d="m3 17 9 5 9-5"/></svg>',
          'question' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9.1 9a3 3 0 1 1 5.82 1c0 2-3 2-3 4"/><path d="M12 17h.01"/><circle cx="12" cy="12" r="9"/></svg>',
          'target' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="7"/><circle cx="12" cy="12" r="3"/><path d="M12 2v4"/><path d="M22 12h-4"/><path d="M12 22v-4"/><path d="M2 12h4"/></svg>',
          'score' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 2.9 6.3 6.9.6-5.2 4.6 1.6 6.8L12 16.9 5.8 20.3l1.6-6.8L2.2 8.9l6.9-.6L12 2Z"/></svg>',
          'search' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21 21-4.3-4.3"/><circle cx="11" cy="11" r="7"/></svg>',
          'lock' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 11V8a5 5 0 0 1 10 0v3"/><rect x="5" y="11" width="14" height="10" rx="2"/></svg>',
          'chart' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19h16"/><path d="M7 16V8"/><path d="M12 16V5"/><path d="M17 16v-3"/></svg>',
          'settings' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 15.5A3.5 3.5 0 1 0 12 8.5a3.5 3.5 0 0 0 0 7Z"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 0 1-1.41 3.41 2 2 0 0 1-1.42-.59l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.34 1.87v.08a2 2 0 0 1-2.32 0v-.08A1.7 1.7 0 0 0 10 20a1.7 1.7 0 0 0-1-.6 1.7 1.7 0 0 0-1 .6l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.7 1.7 0 0 0 5.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.87-.34h-.08a2 2 0 0 1 0-2.32h.08A1.7 1.7 0 0 0 5 10a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-.6-1l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.7 1.7 0 0 0 9 5.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .34-1.87v-.08a2 2 0 0 1 2.32 0v.08A1.7 1.7 0 0 0 14 5.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 1 .6l.06.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.7 1.7 0 0 0 18.4 10c.27.31.48.67.6 1 .11.35.11.73 0 1.08a1.7 1.7 0 0 0-1 .92Z"/></svg>',
          'default' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 6v12"/><path d="M6 12h12"/></svg>',
        ];

        return $icons[$name] ?? $icons['default'];
      };
    @endphp

    {{-- Brand / top area with redesigned logo --}}
    <div class="sidebar-brand">
      <a href="{{ url('/dashboard') }}" class="brand-link">
        <div class="brand-logo-wrapper">
          <img src="{{ asset('images/logo-pemda.png') }}" alt="Logo Pemda" class="brand-logo-badge">
        </div>
        <div class="brand-text">
          <div class="title">LAYANI Mandiri</div>
          <div class="subtitle">Kab. Kepulauan Anambas</div>
        </div>
      </a>
      <button data-sidebar-toggle aria-label="Toggle sidebar" class="sidebar-collapse-btn" title="Sembunyikan sidebar">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 18L18 12 6 6v12z" fill="currentColor"/></svg>
      </button>
    </div>

    {{-- Navigation --}}
    @php
      $current = trim(request()->path(), '/');
      $isActive = fn($path) => \Illuminate\Support\Str::startsWith($current, trim($path, '/'));

      // Determine which sections should be expanded based on current path
      $isF01Active = $isActive('admin/f01') || $isActive('f01');
      $isF02Active = $isActive('admin/f02') || $isActive('f02');
      $isF03Active = $isActive('admin/f03') || $isActive('f03');
      $isPeriodeActive = $isActive('admin/periode');
      $isUppActive = $isActive('user-upp');

      // Determine user role(s)
      $user = Auth::user();
      $userUpps = $user ? $user->getUserUpps() : collect();

      // Helper to check if user has a specific peran (case-insensitive, must be aktif)
      // Supports multiple role name variations
      $hasPeran = function($peranChecks) use ($userUpps) {
        // Normalize: accept string or array
        $peranList = is_array($peranChecks) ? $peranChecks : [$peranChecks];
        $peranList = array_map(function($p) {
          return \Illuminate\Support\Str::lower(trim((string)$p));
        }, $peranList);

        return $userUpps->contains(function($u) use ($peranList) {
          $uPeran = \Illuminate\Support\Str::lower(trim((string)$u->peran));
          return in_array($uPeran, $peranList) && (bool)$u->aktif;
        });
      };

      // Determine user's primary role tier (highest precedence first)
      // Support multiple role name variations
      // First check global roles (role_sso), then UPP-level roles
      $isSuperadmin = $user ? $user->hasGlobalRole('superadmin') : false;
      $isAdminOrganisasi = $user ? $user->hasGlobalRole(['admin_organisasi', 'org_admin', 'org-admin', 'admin_bagian_organisasi']) : false;
      $isAdminUpp = $hasPeran(['admin_upp', 'admin-upp', 'admin_uppp']);
      $isVerifikator = $hasPeran(['verifikator', 'validator']);

      // DEBUG: Log detected roles
      \Log::info('Sidebar role detection', [
        'user' => $user?->nama ?? 'Unknown',
        'userUpps' => $userUpps->pluck('peran')->toArray(),
        'isSuperadmin' => $isSuperadmin,
        'isAdminOrganisasi' => $isAdminOrganisasi,
        'isAdminUpp' => $isAdminUpp,
        'isVerifikator' => $isVerifikator,
      ]);
    @endphp

    <nav class="sidebar-nav" id="sidebarNav">

      {{-- MAIN MENU - Always visible --}}
      <div class="nav-section">
        <a href="{{ url('/dashboard') }}" class="nav-item {{ $isActive('dashboard') ? 'active' : '' }}" title="Dashboard">
          <span class="nav-icon">{!! $sidebarIcon('dashboard') !!}</span>
          <span class="label">Dashboard</span>
        </a>

        {{-- Activity Log: Superadmin only --}}
        @if($isSuperadmin)
          <a href="{{ url('/activity-logs') }}" class="nav-item {{ $isActive('activity-logs') ? 'active' : '' }}" title="Activity Logs">
            <span class="nav-icon">{!! $sidebarIcon('history') !!}</span>
            <span class="label">Activity Log</span>
          </a>
        @endif
      </div>

      {{-- ANALISIS & LAPORAN - Superadmin & Admin Organisasi --}}
      @if($isSuperadmin || $isAdminOrganisasi)
        <div class="nav-section">
          <a href="{{ url('/analytics') }}" class="nav-item {{ $isActive('analytics') ? 'active' : '' }}" title="Analisis & Laporan Komprehensif">
            <span class="nav-icon">{!! $sidebarIcon('trend') !!}</span>
            <span class="label">Analisis & Laporan</span>
          </a>
        </div>
      @endif

      {{-- UPP MANAGEMENT - Superadmin only --}}
      @if($isSuperadmin)
        <div class="nav-section">
          <a href="{{ url('/user-upp') }}" class="nav-item {{ $isActive('user-upp') ? 'active' : '' }}" title="Mapping User ke UPP">
            <span class="nav-icon">{!! $sidebarIcon('users') !!}</span>
            <span class="label">Penugasan User UPP</span>
          </a>
        </div>
      @endif

      {{-- PERIODE MANAGEMENT - Superadmin & Admin Organisasi --}}
      @if($isSuperadmin || $isAdminOrganisasi)
        <div class="nav-section">
          <a href="{{ url('/admin/periode') }}" class="nav-item {{ $isActive('admin/periode') ? 'active' : '' }}" title="Kelola Periode">
            <span class="nav-icon">{!! $sidebarIcon('calendar') !!}</span>
            <span class="label">Periode</span>
          </a>
        </div>
      @endif

      {{-- F01 PENGISIAN (USER ASSESSMENT) - Hide for global admin_organisasi users --}}
      @if(!$isAdminOrganisasi || $isAdminUpp)
      <div class="nav-section">
        <a href="{{ url('/f01') }}" class="nav-item {{ $isActive('f01') ? 'active' : '' }}" title="Isi Form Penilaian F01">
          <span class="nav-icon">{!! $sidebarIcon('file') !!}</span>
          <span class="label">F01</span>
        </a>
      </div>
      @endif

      {{-- F01 SETUP MANAGEMENT - Superadmin & Admin Organisasi --}}
      @if($isSuperadmin || $isAdminOrganisasi)
        <div class="nav-section accordion-section {{ $isF01Active ? 'expanded' : '' }}" data-section="f01">
          <button class="nav-section-title accordion-toggle" type="button" aria-expanded="{{ $isF01Active ? 'true' : 'false' }}">
            <span class="accordion-content"><span class="accordion-icon">{!! $sidebarIcon('layers') !!}</span> <span>Setup F01</span></span>
            <svg class="accordion-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </button>
          <div class="accordion-items">
            <a href="{{ url('/admin/f01/aspek') }}" class="nav-item {{ $isActive('admin/f01/aspek') ? 'active' : '' }}" title="Kelola Aspek">
              <span class="nav-icon">{!! $sidebarIcon('layers') !!}</span>
              <span class="label">Aspek</span>
            </a>

            <a href="{{ url('/admin/f01/indikator') }}" class="nav-item {{ $isActive('admin/f01/indikator') ? 'active' : '' }}" title="Kelola Indikator">
              <span class="nav-icon">{!! $sidebarIcon('target') !!}</span>
              <span class="label">Indikator</span>
            </a>

            <a href="{{ url('/admin/f01/pertanyaan') }}" class="nav-item {{ $isActive('admin/f01/pertanyaan') ? 'active' : '' }}" title="Kelola Pertanyaan">
              <span class="nav-icon">{!! $sidebarIcon('question') !!}</span>
              <span class="label">Pertanyaan</span>
            </a>
          </div>
        </div>
      @endif

      {{-- F02 MANAGEMENT - Superadmin, Admin Organisasi, & Verifikator --}}
      @if($isSuperadmin || $isAdminOrganisasi || $isVerifikator)
        <div class="nav-section accordion-section {{ $isF02Active ? 'expanded' : '' }}" data-section="f02">
          <button class="nav-section-title accordion-toggle" type="button" aria-expanded="{{ $isF02Active ? 'true' : 'false' }}">
            <span class="accordion-content"><span class="accordion-icon">{!! $sidebarIcon('score') !!}</span> <span>F02 Validasi</span></span>
            <svg class="accordion-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </button>
          <div class="accordion-items">
            {{-- F02 Pengelolaan Skor: Superadmin & Admin Organisasi --}}
            @if($isSuperadmin || $isAdminOrganisasi)
              <a href="{{ url('/f02-skor') }}" class="nav-item {{ $isActive('f02-skor') ? 'active' : '' }}" title="Kelola Narasi Skor Indikator F02">
                <span class="nav-icon">{!! $sidebarIcon('file') !!}</span>
                <span class="label">Pengelolaan Skor</span>
              </a>
            @endif

            {{-- F02 Validasi Penilaian: Superadmin, Admin Organisasi, & Verifikator --}}
            <a href="{{ url('/f02') }}" class="nav-item {{ $isActive('f02') ? 'active' : '' }}" title="Validasi Pengisian F01">
              <span class="nav-icon">{!! $sidebarIcon('search') !!}</span>
              <span class="label">Validasi Penilaian</span>
            </a>
          </div>
        </div>
      @endif

      {{-- F03 MANAGEMENT --}}
      @if($isAdminUpp)
        {{-- F03 Dashboard for Admin UPP Users --}}
        <div class="nav-section">
          <a href="{{ url('/f03/dashboard') }}" class="nav-item {{ $isActive('f03/dashboard') ? 'active' : '' }}" title="Dashboard F03 - Responden & Data">
            <span class="nav-icon">{!! $sidebarIcon('chart') !!}</span>
            <span class="label">F03</span>
          </a>
        </div>
      @elseif($isSuperadmin || $isAdminOrganisasi || $isVerifikator)
        {{-- F03 Setup Management for Admin Organisasi & Superadmin --}}
        <div class="nav-section accordion-section {{ $isF03Active ? 'expanded' : '' }}" data-section="f03">
          <button class="nav-section-title accordion-toggle" type="button" aria-expanded="{{ $isF03Active ? 'true' : 'false' }}">
            <span class="accordion-content"><span class="accordion-icon">{!! $sidebarIcon('chart') !!}</span> <span>Setup F03</span></span>
            <svg class="accordion-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </button>
          <div class="accordion-items">
            <a href="{{ url('/admin/f03/aspek') }}" class="nav-item {{ $isActive('admin/f03/aspek') ? 'active' : '' }}" title="Kelola Aspek F03">
              <span class="nav-icon">{!! $sidebarIcon('layers') !!}</span>
              <span class="label">Aspek F03</span>
            </a>

            <a href="{{ url('/admin/f03/indikator') }}" class="nav-item {{ $isActive('admin/f03/indikator') ? 'active' : '' }}" title="Kelola Indikator F03">
              <span class="nav-icon">{!! $sidebarIcon('target') !!}</span>
              <span class="label">Indikator F03</span>
            </a>

            <a href="{{ url('/admin/f03/token') }}" class="nav-item {{ $isActive('admin/f03/token') ? 'active' : '' }}" title="Manajemen Token">
              <span class="nav-icon">{!! $sidebarIcon('lock') !!}</span>
              <span class="label">Token & URL</span>
            </a>

            <a href="{{ url('/admin/f03/dashboard') }}" class="nav-item {{ $isActive('admin/f03/dashboard') ? 'active' : '' }}" title="Admin Dashboard F03">
              <span class="nav-icon">{!! $sidebarIcon('analytics') !!}</span>
              <span class="label">Dashboard Admin F03</span>
            </a>
          </div>
        </div>
      @endif

    </nav>

  </div>

  {{-- Script for accordion and scroll persistence --}}
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const sidebarNav = document.getElementById('sidebarNav');

      // Restore scroll position on page load
      const scrollPos = localStorage.getItem('sidebarScrollPos');
      if (scrollPos) {
        sidebarNav.scrollTop = parseInt(scrollPos);
      }

      // Save scroll position as user scrolls
      sidebarNav.addEventListener('scroll', function() {
        localStorage.setItem('sidebarScrollPos', sidebarNav.scrollTop);
      });

      // Accordion functionality
      const accordionToggles = document.querySelectorAll('.accordion-toggle');
      accordionToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
          e.preventDefault();
          const section = toggle.closest('.accordion-section');
          const isExpanded = section.classList.contains('expanded');

          // Toggle expanded state
          if (isExpanded) {
            section.classList.remove('expanded');
            toggle.setAttribute('aria-expanded', 'false');
          } else {
            section.classList.add('expanded');
            toggle.setAttribute('aria-expanded', 'true');
          }

          // Save accordion state to localStorage
          const sectionName = section.dataset.section;
          localStorage.setItem('sidebarAccordion_' + sectionName, !isExpanded);
        });
      });
    });
  </script>
</aside>
