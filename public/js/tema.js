// Tema claro/oscuro persistente.
//
// Qué se demuestra (DWEC):
//   - localStorage para recordar la preferencia entre visitas.
//   - try/catch alrededor de localStorage: en modo privado o con el
//     almacenamiento bloqueado, acceder lanza; lo capturamos para que la web
//     siga funcionando (solo se pierde la persistencia).
//   - Respeto de prefers-color-scheme como valor por defecto.

(function () {
    'use strict';

    const CLAVE = 'fp_tema';
    const raiz = document.documentElement;

    function guardar(valor) {
        try {
            localStorage.setItem(CLAVE, valor);
        } catch (error) {
            // Almacenamiento no disponible: seguimos sin persistir.
            console.warn('No se pudo guardar el tema:', error.message);
        }
    }

    function recuperar() {
        try {
            return localStorage.getItem(CLAVE);
        } catch (error) {
            return null;
        }
    }

    function temaPorDefecto() {
        const prefiereOscuro = window.matchMedia &&
            window.matchMedia('(prefers-color-scheme: dark)').matches;
        return prefiereOscuro ? 'oscuro' : 'claro';
    }

    function aplicar(tema) {
        raiz.dataset.tema = tema;
        const boton = document.querySelector('[data-toggle-tema]');
        if (boton) {
            const esOscuro = tema === 'oscuro';
            boton.setAttribute('aria-pressed', String(esOscuro));
            const icono = boton.querySelector('i');
            if (icono) {
                icono.className = esOscuro ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
            }
            boton.innerHTML = (esOscuro ? '<i class="fa-solid fa-sun"></i> Claro' : '<i class="fa-solid fa-moon"></i> Oscuro');
            boton.setAttribute('aria-label', esOscuro ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro');
        }
    }

    // Estado inicial: preferencia guardada o, si no hay, la del sistema.
    aplicar(recuperar() || temaPorDefecto());

    document.addEventListener('click', function (evento) {
        const boton = evento.target.closest('[data-toggle-tema]');
        if (!boton) return;
        const nuevo = raiz.dataset.tema === 'oscuro' ? 'claro' : 'oscuro';
        aplicar(nuevo);
        guardar(nuevo);
    });
})();
