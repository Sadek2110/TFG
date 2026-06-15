// Mejoras de UX comunes a todas las páginas.
// Por ahora: confirmación en formularios con data-confirmar.

(function () {
    'use strict';

    document.addEventListener('submit', function (evento) {
        const formulario = evento.target;
        if (!(formulario instanceof HTMLFormElement)) return;
        const mensaje = formulario.dataset.confirmar;
        if (mensaje && !window.confirm(mensaje)) {
            evento.preventDefault();
        }
    });
})();
