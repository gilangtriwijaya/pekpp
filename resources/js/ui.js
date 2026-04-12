document.addEventListener('DOMContentLoaded', () => {

    // ============================================
    // SIDEBAR TOGGLE
    // ============================================
    const sidebarToggles = document.querySelectorAll('[data-sidebar-toggle]');
    const sidebar = document.querySelector('.pekppp-sidebar');

    if (sidebarToggles.length && sidebar) {
        const key = 'pekppp:sidebarCollapsed';
        
        // Load saved state
        try {
            const val = localStorage.getItem(key);
            if (val === '1') sidebar.classList.add('collapsed');
        } catch (e) {
            console.warn('localStorage not available');
        }

        // Sync aria-expanded across all toggle buttons
        const setAria = (isCollapsed) => {
            sidebarToggles.forEach(btn => {
                try { 
                    btn.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true'); 
                } catch (e) {}
            });
        };

        setAria(sidebar.classList.contains('collapsed'));

        // Toggle on click
        sidebarToggles.forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const collapsed = sidebar.classList.toggle('collapsed');
                setAria(collapsed);
                try { 
                    localStorage.setItem(key, collapsed ? '1' : '0'); 
                } catch (e) {}
            });
        });
    }


    // ============================================
    // PROFILE DROPDOWN (FIXED - Single handler)
    // ============================================
    const profileWidget = document.querySelector('.profile-widget');
    const profileBtn = document.querySelector('[data-profile-trigger]');
    const profileMenu = document.querySelector('.profile-menu');

    if (profileWidget && profileBtn && profileMenu) {
        
        // Open dropdown
        const open = () => {
            profileWidget.classList.add('open');
            profileBtn.setAttribute('aria-expanded', 'true');
            
            // Focus first focusable element in menu
            const focusable = profileMenu.querySelector('button, a, [tabindex]:not([tabindex="-1"])');
            if (focusable) {
                setTimeout(() => focusable.focus(), 50);
            }
        };

        // Close dropdown
        const close = () => {
            profileWidget.classList.remove('open');
            profileBtn.setAttribute('aria-expanded', 'false');
        };

        // Toggle on button click
        profileBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            if (profileWidget.classList.contains('open')) {
                close();
            } else {
                open();
            }
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!profileWidget.contains(e.target)) {
                close();
            }
        });

        // Close on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && profileWidget.classList.contains('open')) {
                close();
                profileBtn.focus(); // Return focus to trigger button
            }
        });
    }


    // ============================================
    // CONFIRM DIALOGS (data-confirm attribute)
    // ============================================
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-confirm]');
        if (!btn) return;

        const message = btn.getAttribute('data-confirm') || 'Are you sure?';

        if (!window.confirm(message)) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        return true;
    });

});