// Animaciones de la portada: contador de cifras.
//
// Qué se demuestra (DWEC):
//   - requestAnimationFrame para una animación fluida (mejor que setInterval).
//   - IntersectionObserver para arrancar el conteo solo cuando las cifras
//     entran en pantalla.
//   - dataset para leer el valor objetivo desde el HTML (data-objetivo).

(function () {
    'use strict';

    const contadores = document.querySelectorAll('[data-contador]');
    if (!contadores.length) return;

    const reduce = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function animar(el) {
        const objetivo = parseInt(el.dataset.objetivo, 10) || 0;
        if (reduce) {
            el.textContent = objetivo.toLocaleString('es-ES');
            return;
        }

        const duracion = 1400;
        const inicio = performance.now();

        function paso(ahora) {
            const t = Math.min((ahora - inicio) / duracion, 1);
            // easeOutCubic para que frene de forma natural al final.
            const eased = 1 - Math.pow(1 - t, 3);
            el.textContent = Math.round(objetivo * eased).toLocaleString('es-ES');
            if (t < 1) {
                requestAnimationFrame(paso);
            }
        }
        requestAnimationFrame(paso);
    }

    if (!('IntersectionObserver' in window)) {
        contadores.forEach(animar);
        return;
    }

    const observador = new IntersectionObserver(function (entradas, obs) {
        entradas.forEach(function (entrada) {
            if (entrada.isIntersecting) {
                animar(entrada.target);
                obs.unobserve(entrada.target);
            }
        });
    }, { threshold: 0.5 });

    contadores.forEach(function (el) { observador.observe(el); });
})();
