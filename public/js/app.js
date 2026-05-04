(function () {
    'use strict';

    window.api = {
        get: async function (url, params = {}) {
            const query = new URLSearchParams(params).toString();
            const fullUrl = query ? `${APP_URL}${url}?${query}` : `${APP_URL}${url}`;
            const res = await fetch(fullUrl);
            return res.json();
        },

        post: async function (url, data = {}) {
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = tokenMeta ? tokenMeta.content : '';

            const res = await fetch(`${APP_URL}${url}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': csrfToken
                },
                body: new URLSearchParams({ ...data, csrf_token: csrfToken })
            });
            return res.json();
        },

        delete: async function (url, data = {}) {
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = tokenMeta ? tokenMeta.content : '';

            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('csrf_token', csrfToken);
            for (const [key, val] of Object.entries(data)) {
                formData.append(key, val);
            }
            const res = await fetch(`${APP_URL}${url}`, { method: 'POST', body: formData });
            return res.json();
        }
    };

    window.utils = {
        escapeHtml: function (text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        formatDate: function (dateStr) {
            return new Date(dateStr).toLocaleDateString('es-ES', {
                day: '2-digit', month: 'short', year: 'numeric'
            });
        },

        formatTime: function (dateStr) {
            return new Date(dateStr).toLocaleTimeString('es-ES', {
                hour: '2-digit', minute: '2-digit'
            });
        },

        debounce: function (fn, delay = 300) {
            let timer;
            return function (...args) {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), delay);
            };
        },

        easeInOut: function (t) {
            return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
        }
    };
})();
