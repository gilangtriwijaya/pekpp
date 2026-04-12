{{-- Impersonate Idle Timeout Component --}}
{{-- Auto-returns to superadmin after 30 minutes of inactivity when impersonating --}}

@if(session()->has('impersonating_user_id'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Only run idle timeout if impersonating
    const isImpersonating = {{ session()->has('impersonating_user_id') ? 'true' : 'false' }};
    
    if (!isImpersonating) {
        return;
    }
    
    const IDLE_TIMEOUT_MS = 30 * 60 * 1000; // 30 minutes
    const WARNING_TIME_MS = 5 * 60 * 1000;  // Warning at 5 minutes before timeout
    let idleTimer = null;
    let warningTimer = null;
    let isWarning = false;
    
    function resetIdleTimer() {
        // Clear existing timers
        clearTimeout(idleTimer);
        clearTimeout(warningTimer);
        isWarning = false;
        
        // Remove warning modal if active
        const warningModal = document.getElementById('impersonateWarningModal');
        if (warningModal) {
            warningModal.style.display = 'none';
        }
        
        // Set warning timer (5 minutes before timeout)
        warningTimer = setTimeout(function() {
            isWarning = true;
            showIdleWarning();
        }, IDLE_TIMEOUT_MS - WARNING_TIME_MS);
        
        // Set logout timer
        idleTimer = setTimeout(function() {
            autoReturnToSuperadmin();
        }, IDLE_TIMEOUT_MS);
    }
    
    function showIdleWarning() {
        if (isWarning) {
            // Create warning modal
            if (!document.getElementById('impersonateWarningModal')) {
                const warningHtml = `
                <div id="impersonateWarningModal" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0, 0, 0, 0.6);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 999;
                ">
                    <div style="
                        background: white;
                        border-radius: 12px;
                        padding: 24px;
                        max-width: 400px;
                        box-shadow: 0 20px 45px rgba(0,0,0,0.2);
                        text-align: center;
                    ">
                        <svg style="
                            width: 48px;
                            height: 48px;
                            margin: 0 auto 16px;
                            color: #f59e0b;
                        " viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <h3 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 600; color: #0f172a;">
                            Sesi Impersonasi Akan Berakhir
                        </h3>
                        <p style="margin: 0 0 16px 0; font-size: 14px; color: #64748b; line-height: 1.5;">
                            Anda akan otomatis kembali ke akun superadmin dalam 5 menit karena tidak ada aktivitas.
                        </p>
                        <button onclick="document.dispatchEvent(new Event('userActivity'))" style="
                            padding: 10px 16px;
                            background: #3b82f6;
                            color: white;
                            border: none;
                            border-radius: 8px;
                            font-size: 14px;
                            font-weight: 500;
                            cursor: pointer;
                            width: 100%;
                            transition: background 120ms;
                        " onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
                            Lanjutkan Sesi
                        </button>
                    </div>
                </div>
                `;
                document.body.insertAdjacentHTML('beforeend', warningHtml);
            } else {
                document.getElementById('impersonateWarningModal').style.display = 'flex';
            }
        }
    }
    
    function autoReturnToSuperadmin() {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        
        fetch('{{ route("api.impersonate.stop") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Redirect to root page
            window.location.href = '/';
        })
        .catch(error => {
            console.error('Error auto-returning to superadmin:', error);
            // Force redirect anyway
            window.location.href = '/';
        });
    }
    
    // Track user activity
    const activityEvents = ['mousedown', 'keydown', 'scroll', 'touchstart', 'click'];
    
    activityEvents.forEach(event => {
        document.addEventListener(event, function() {
            if (!isWarning) {
                resetIdleTimer();
            }
        }, true);
    });
    
    // Listen for manual "continue session" event
    document.addEventListener('userActivity', function() {
        resetIdleTimer();
    });
    
    // Start the idle timer
    resetIdleTimer();
});
</script>
@endif
