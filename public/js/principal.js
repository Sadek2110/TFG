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
