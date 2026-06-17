// Carta de jugador tipo FIFA con efecto "tilt" 3D.
//
// Qué se demuestra (DWEC):
//   - Eventos de puntero: pointermove para seguir el cursor y pointerleave
//     para volver al reposo (funciona con ratón y con táctil).
//   - Cálculo de la posición relativa con getBoundingClientRect.
//   - Escritura de estilos en línea (transform, custom properties) para el
//     brillo que sigue al puntero.
//   - Respeto de prefers-reduced-motion.

(function () {
    'use strict';

    const cartas = document.querySelectorAll('[data-carta]');
    if (!cartas.length) return;

    const reduce = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) return;

    const INCLINACION = 12; // grados máximos de giro

    cartas.forEach(function (carta) {
        const brillo = carta.querySelector('.carta-fifa__brillo');

        function mover(evento) {
            const r = carta.getBoundingClientRect();
            const x = (evento.clientX - r.left) / r.width;  // 0..1
            const y = (evento.clientY - r.top) / r.height;  // 0..1
            const giroY = (x - 0.5) * 2 * INCLINACION;
            const giroX = (0.5 - y) * 2 * INCLINACION;

            carta.style.transform =
                'perspective(700px) rotateX(' + giroX.toFixed(2) + 'deg) rotateY(' +
                giroY.toFixed(2) + 'deg) scale(1.04)';

            if (brillo) {
                brillo.style.setProperty('--x', (x * 100).toFixed(1) + '%');
                brillo.style.setProperty('--y', (y * 100).toFixed(1) + '%');
                brillo.style.opacity = '1';
            }
        }

        function reposo() {
            carta.style.transform = '';
            if (brillo) brillo.style.opacity = '0';
        }

        carta.addEventListener('pointermove', mover);
        carta.addEventListener('pointerleave', reposo);
        carta.addEventListener('blur', reposo);
    });
})();
