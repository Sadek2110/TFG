(function () {
    'use strict';

    // Auto-inject CSRF token into all POST forms that don't have one
    document.addEventListener('DOMContentLoaded', function () {
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.content : null;

        if (!token) return;

        document.querySelectorAll('form[method="POST"]').forEach(function (form) {
            if (form.querySelector('input[name="csrf_token"]')) return;

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'csrf_token';
            input.value = token;
            form.insertBefore(input, form.firstChild);
        });
    });

    // Flash message auto-dismiss (slide up + fade)
    const flashEl = document.getElementById('flash-msg');
    if (flashEl) {
        setTimeout(function () {
            flashEl.style.opacity = '0';
            flashEl.style.transform = 'translateX(-50%) translateY(-10px)';
            flashEl.style.transition = 'opacity .35s ease, transform .35s ease';
            setTimeout(function () { flashEl.remove(); }, 380);
        }, 3500);
    }

    // Generic submit-spinner for forms with data-loading-form attribute
    document.querySelectorAll('[data-loading-form]').forEach(function (form) {
        form.addEventListener('submit', function () {
            var btn  = form.querySelector('[data-submit-btn]');
            var txt  = btn && btn.querySelector('[data-btn-text]');
            var spin = btn && btn.querySelector('[data-spinner]');
            if (btn)  { btn.disabled = true; btn.style.opacity = '.75'; }
            if (txt)  txt.textContent = btn ? (btn.dataset.loadingText || 'Cargando…') : 'Cargando…';
            if (spin) spin.classList.remove('hidden');
        });
    });
})();
