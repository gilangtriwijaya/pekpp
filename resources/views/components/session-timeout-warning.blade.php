{{-- Session Timeout Warning Based on Idle Time --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sessionLifetimeSeconds = {{ config('session.lifetime') }} * 60; // 120 minutes in seconds
        const warningBeforeExpirySeconds = 15 * 60; // Warn 15 minutes before
        const warningTriggerSeconds = sessionLifetimeSeconds - warningBeforeExpirySeconds; // 105 minutes = 6300 seconds
        
        let lastActivityTime = Math.floor(Date.now() / 1000); // Current time in seconds
        let warningShown = false;
        let idleCheckInterval = null;
        let countdownInterval = null;
        
        // Function to check idle time and show warning if needed
        function checkIdleTime() {
            const currentTime = Math.floor(Date.now() / 1000);
            const idleSeconds = currentTime - lastActivityTime;
            
            // If idle time exceeds warning trigger time, show warning
            if (idleSeconds >= warningTriggerSeconds && !warningShown) {
                showSessionWarning(Math.max(0, sessionLifetimeSeconds - idleSeconds));
            }
            
            // If idle time exceeds session lifetime, logout
            if (idleSeconds >= sessionLifetimeSeconds) {
                clearInterval(idleCheckInterval);
                clearInterval(countdownInterval);
                logoutDueToTimeout();
            }
        }
        
        function showSessionWarning(remainingSeconds) {
            if (warningShown) return;
            warningShown = true;
            
            // Create modal backdrop
            const backdrop = document.createElement('div');
            backdrop.id = 'session-warning-backdrop';
            backdrop.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
            `;
            
            // Create modal
            const modal = document.createElement('div');
            modal.style.cssText = `
                background: white;
                border-radius: 12px;
                padding: 30px;
                max-width: 450px;
                box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
                text-align: center;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            `;
            
            modal.innerHTML = `
                <div style="font-size: 48px; margin-bottom: 16px;">⏰</div>
                <h2 style="font-size: 22px; font-weight: 700; color: #1f2937; margin-bottom: 10px;">
                    Sesi Akan Berakhir Segera
                </h2>
                <p style="color: #6b7280; font-size: 15px; margin-bottom: 24px;">
                    Anda tidak aktif selama beberapa saat. Sesi akan berakhir dalam <span id="countdown-timer" style="font-weight: 700; color: #ef4444;">15:00</span>
                </p>
                <p style="color: #6b7280; font-size: 14px; margin-bottom: 24px;">
                    Klik "Tetap Aktif" untuk melanjutkan sesi Anda.
                </p>
                <div style="display: flex; gap: 12px;">
                    <button onclick="extendSession()" style="
                        flex: 1;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        border: none;
                        padding: 12px 24px;
                        border-radius: 8px;
                        font-size: 15px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: transform 0.2s;
                    " onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                        ✓ Tetap Aktif
                    </button>
                    <button onclick="logoutNow()" style="
                        flex: 1;
                        background: #f3f4f6;
                        color: #374151;
                        border: 1px solid #d1d5db;
                        padding: 12px 24px;
                        border-radius: 8px;
                        font-size: 15px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.2s;
                    " onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                        🚪 Logout
                    </button>
                </div>
            `;
            
            backdrop.appendChild(modal);
            document.body.appendChild(backdrop);
            
            // Start countdown timer
            let remainingSecondsCount = Math.max(0, sessionLifetimeSeconds - (Math.floor(Date.now() / 1000) - lastActivityTime));
            
            countdownInterval = setInterval(function() {
                const currentTime = Math.floor(Date.now() / 1000);
                const currentIdleSeconds = currentTime - lastActivityTime;
                const secondsUntilLogout = Math.max(0, sessionLifetimeSeconds - currentIdleSeconds);
                
                const minutes = Math.floor(secondsUntilLogout / 60);
                const seconds = secondsUntilLogout % 60;
                
                const timerEl = document.getElementById('countdown-timer');
                if (timerEl) {
                    timerEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                }
                
                // If time's up, logout
                if (secondsUntilLogout <= 0) {
                    clearInterval(countdownInterval);
                    logoutDueToTimeout();
                }
            }, 1000);
        }
        
        function logoutDueToTimeout() {
            // Make a simple request to trigger server-side logout via middleware
            window.location.href = window.location.href;
        }
        
        window.extendSession = function() {
            // Clear the modal
            const backdrop = document.getElementById('session-warning-backdrop');
            if (backdrop) backdrop.remove();
            
            if (countdownInterval) clearInterval(countdownInterval);
            warningShown = false;
            
            // Reset last activity time to now
            lastActivityTime = Math.floor(Date.now() / 1000);
            
            // Continue checking
            if (!idleCheckInterval) {
                idleCheckInterval = setInterval(checkIdleTime, 1000);
            }
        };
        
        window.logoutNow = function() {
            // Submit logout form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("sso.logout") }}';
            
            const token = document.querySelector('meta[name="csrf-token"]');
            if (token) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_token';
                input.value = token.getAttribute('content');
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
        };
        
        // Track user activity to reset idle timer
        function resetIdleTimer() {
            lastActivityTime = Math.floor(Date.now() / 1000);
            
            // If warning is shown, close it and reset
            if (warningShown) {
                const backdrop = document.getElementById('session-warning-backdrop');
                if (backdrop) backdrop.remove();
                
                if (countdownInterval) clearInterval(countdownInterval);
                warningShown = false;
            }
        }
        
        document.addEventListener('click', resetIdleTimer);
        document.addEventListener('keydown', resetIdleTimer);
        document.addEventListener('mousemove', resetIdleTimer);
        document.addEventListener('scroll', resetIdleTimer);
        document.addEventListener('touchstart', resetIdleTimer);
        
        // Start checking idle time every second
        idleCheckInterval = setInterval(checkIdleTime, 1000);
    });
</script>
