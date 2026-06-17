// Gestión de cookies y consentimiento.
//
// Qué se demuestra (DWEC):
//   - Lectura y escritura de cookies con document.cookie (prefijo fp_client_
//     para no chocar con las cookies del servidor como FASTPLAYSID).
//   - Banner de aceptar/rechazar con persistencia de la elección.
//   - API pública reutilizable en window.FastplayCookies para que cualquier
//     otro script guarde preferencias respetando el consentimiento.

(function () {
    'use strict';

    const PREFIJO = 'fp_client_';
    const CLAVE_CONSENTIMIENTO = 'consentimiento';

    function escribir(nombre, valor, dias) {
        let cookie = PREFIJO + nombre + '=' + encodeURIComponent(valor) +
            '; path=/; SameSite=Lax';
        if (typeof dias === 'number') {
            const expira = new Date(Date.now() + dias * 864e5);
            cookie += '; expires=' + expira.toUTCString();
        }
        if (location.protocol === 'https:') {
            cookie += '; Secure';
        }
        document.cookie = cookie;
    }

    function leer(nombre) {
        const buscado = PREFIJO + nombre + '=';
        const partes = document.cookie.split('; ');
        for (const parte of partes) {
            if (parte.indexOf(buscado) === 0) {
                return decodeURIComponent(parte.substring(buscado.length));
            }
        }
        return null;
    }

    function borrar(nombre) {
        escribir(nombre, '', -1);
    }

    // API pública: otros scripts pueden guardar preferencias, pero solo
    // deberían hacerlo si hay consentimiento.
    const API = {
        set: escribir,
        get: leer,
        remove: borrar,
        consentimiento: function () {
            return leer(CLAVE_CONSENTIMIENTO); // 'aceptado' | 'rechazado' | null
        },
        hayConsentimiento: function () {
            return leer(CLAVE_CONSENTIMIENTO) === 'aceptado';
        },
    };
    window.FastplayCookies = API;

    // --- Banner de consentimiento ---
    const banner = document.querySelector('[data-banner-cookies]');
    if (!banner) return;

    function ocultarBanner() {
        banner.hidden = true;
    }

    function decidir(valor) {
        escribir(CLAVE_CONSENTIMIENTO, valor, 180);
        ocultarBanner();
        document.dispatchEvent(new CustomEvent('fastplay:consentimiento', { detail: valor }));
    }

    // Solo mostramos el banner si aún no hay una decisión guardada.
    if (API.consentimiento() === null) {
        banner.hidden = false;
    }

    const aceptar = banner.querySelector('[data-accion="aceptar"]');
    const rechazar = banner.querySelector('[data-accion="rechazar"]');
    if (aceptar)  aceptar.addEventListener('click', function () { decidir('aceptado'); });
    if (rechazar) rechazar.addEventListener('click', function () { decidir('rechazado'); });
})();
