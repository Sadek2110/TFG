// Mejoras de UX comunes a todas las páginas:
//   - Confirmación en formularios con data-confirmar.
//   - Menú hamburguesa en móvil.

(function () {
    'use strict';

    // -------------------------------------------------------------------------
    // Confirmación en formularios con data-confirmar
    // -------------------------------------------------------------------------
    document.addEventListener('submit', function (evento) {
        const formulario = evento.target;
        if (!(formulario instanceof HTMLFormElement)) return;
        const mensaje = formulario.dataset.confirmar;
        if (mensaje && !window.confirm(mensaje)) {
            evento.preventDefault();
        }
    });

    // -------------------------------------------------------------------------
    // Tarjetas y filas clicables con data-tarjeta-url
    // -------------------------------------------------------------------------
    document.addEventListener('click', function (evento) {
        const tarjeta = evento.target.closest('[data-tarjeta-url]');
        if (!tarjeta) return;
        if (evento.target.closest('a, button, input, select, textarea, label, form')) return;

        const destino = tarjeta.dataset.tarjetaUrl;
        if (!destino) return;

        if (tarjeta.dataset.tarjetaTarget === '_blank') {
            window.open(destino, '_blank', 'noopener');
            return;
        }
        window.location.href = destino;
    });

    // -------------------------------------------------------------------------
    // Menú hamburguesa en móvil
    // -------------------------------------------------------------------------
    const toggle = document.querySelector('[data-menu-toggle]');
    const menu   = document.querySelector('[data-menu]');

    if (toggle && menu) {
        toggle.addEventListener('click', function () {
            const abierto = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!abierto));
            menu.classList.toggle('menu--abierto', !abierto);
        });

        // Cerrar al pulsar un enlace del menú
        menu.addEventListener('click', function (e) {
            if (e.target.tagName === 'A') {
                toggle.setAttribute('aria-expanded', 'false');
                menu.classList.remove('menu--abierto');
            }
        });
    }
})();
