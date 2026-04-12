<aside class="pekppp-sidebar" aria-label="Primary navigation">
  <style>
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
          <span class="icon">🏠</span>
          <span class="label">Dashboard</span>
        </a>

        {{-- Activity Log: Superadmin only --}}
        @if($isSuperadmin)
          <a href="{{ url('/activity-logs') }}" class="nav-item {{ $isActive('activity-logs') ? 'active' : '' }}" title="Activity Logs">
            <span class="icon">📊</span>
            <span class="label">Activity Log</span>
          </a>
        @endif
      </div>

      {{-- UPP MANAGEMENT - Superadmin only --}}
      @if($isSuperadmin)
        <div class="nav-section">
          <a href="{{ url('/user-upp') }}" class="nav-item {{ $isActive('user-upp') ? 'active' : '' }}" title="Mapping User ke UPP">
            <span class="icon">👥</span>
            <span class="label">Penugasan User UPP</span>
          </a>
        </div>
      @endif

      {{-- PERIODE MANAGEMENT - Superadmin & Admin Organisasi --}}
      @if($isSuperadmin || $isAdminOrganisasi)
        <div class="nav-section">
          <a href="{{ url('/admin/periode') }}" class="nav-item {{ $isActive('admin/periode') ? 'active' : '' }}" title="Kelola Periode">
            <span class="icon">📅</span>
            <span class="label">Periode</span>
          </a>
        </div>
      @endif

      {{-- F01 PENGISIAN (USER ASSESSMENT) - Hide for global admin_organisasi users --}}
      @if(!$isAdminOrganisasi || $isAdminUpp)
      <div class="nav-section">
        <a href="{{ url('/f01') }}" class="nav-item {{ $isActive('f01') ? 'active' : '' }}" title="Isi Form Penilaian F01">
          <span class="icon">📝</span>
          <span class="label">F01</span>
        </a>
      </div>
      @endif

      {{-- F01 SETUP MANAGEMENT - Superadmin & Admin Organisasi --}}
      @if($isSuperadmin || $isAdminOrganisasi)
        <div class="nav-section accordion-section {{ $isF01Active ? 'expanded' : '' }}" data-section="f01">
          <button class="nav-section-title accordion-toggle" type="button" aria-expanded="{{ $isF01Active ? 'true' : 'false' }}">
            <span class="accordion-content">📋 Setup F01</span>
            <svg class="accordion-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </button>
          <div class="accordion-items">
            <a href="{{ url('/admin/f01/aspek') }}" class="nav-item {{ $isActive('admin/f01/aspek') ? 'active' : '' }}" title="Kelola Aspek">
              <span class="icon">🎯</span>
              <span class="label">Aspek</span>
            </a>

            <a href="{{ url('/admin/f01/indikator') }}" class="nav-item {{ $isActive('admin/f01/indikator') ? 'active' : '' }}" title="Kelola Indikator">
              <span class="icon">📍</span>
              <span class="label">Indikator</span>
            </a>

            <a href="{{ url('/admin/f01/pertanyaan') }}" class="nav-item {{ $isActive('admin/f01/pertanyaan') ? 'active' : '' }}" title="Kelola Pertanyaan">
              <span class="icon">❓</span>
              <span class="label">Pertanyaan</span>
            </a>
          </div>
        </div>
      @endif

      {{-- F02 MANAGEMENT - Superadmin, Admin Organisasi, & Verifikator --}}
      @if($isSuperadmin || $isAdminOrganisasi || $isVerifikator)
        <div class="nav-section accordion-section {{ $isF02Active ? 'expanded' : '' }}" data-section="f02">
          <button class="nav-section-title accordion-toggle" type="button" aria-expanded="{{ $isF02Active ? 'true' : 'false' }}">
            <span class="accordion-content">⭐ F02 Validasi</span>
            <svg class="accordion-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </button>
          <div class="accordion-items">
            {{-- F02 Pengelolaan Skor: Superadmin & Admin Organisasi --}}
            @if($isSuperadmin || $isAdminOrganisasi)
              <a href="{{ url('/f02-skor') }}" class="nav-item {{ $isActive('f02-skor') ? 'active' : '' }}" title="Kelola Narasi Skor Indikator F02">
                <span class="icon">📝</span>
                <span class="label">Pengelolaan Skor</span>
              </a>
            @endif

            {{-- F02 Validasi Penilaian: Superadmin, Admin Organisasi, & Verifikator --}}
            <a href="{{ url('/f02') }}" class="nav-item {{ $isActive('f02') ? 'active' : '' }}" title="Validasi Pengisian F01">
              <span class="icon">🔍</span>
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
            <span class="icon">📊</span>
            <span class="label">F03</span>
          </a>
        </div>
      @elseif($isSuperadmin || $isAdminOrganisasi || $isVerifikator)
        {{-- F03 Setup Management for Admin Organisasi & Superadmin --}}
        <div class="nav-section accordion-section {{ $isF03Active ? 'expanded' : '' }}" data-section="f03">
          <button class="nav-section-title accordion-toggle" type="button" aria-expanded="{{ $isF03Active ? 'true' : 'false' }}">
            <span class="accordion-content">📊 Setup F03</span>
            <svg class="accordion-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </button>
          <div class="accordion-items">
            <a href="{{ url('/admin/f03/aspek') }}" class="nav-item {{ $isActive('admin/f03/aspek') ? 'active' : '' }}" title="Kelola Aspek F03">
              <span class="icon">🎯</span>
              <span class="label">Aspek F03</span>
            </a>

            <a href="{{ url('/admin/f03/indikator') }}" class="nav-item {{ $isActive('admin/f03/indikator') ? 'active' : '' }}" title="Kelola Indikator F03">
              <span class="icon">📍</span>
              <span class="label">Indikator F03</span>
            </a>

            <a href="{{ url('/admin/f03/token') }}" class="nav-item {{ $isActive('admin/f03/token') ? 'active' : '' }}" title="Manajemen Token">
              <span class="icon">🔐</span>
              <span class="label">Token & URL</span>
            </a>

            <a href="{{ url('/admin/f03/dashboard') }}" class="nav-item {{ $isActive('admin/f03/dashboard') ? 'active' : '' }}" title="Admin Dashboard F03">
              <span class="icon">📈</span>
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
