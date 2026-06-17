// Revelado progresivo de secciones al hacer scroll.
//
// Qué se demuestra (DWEC):
//   - IntersectionObserver para reaccionar cuando un elemento entra en el
//     viewport, sin escuchar el evento scroll (más eficiente).
//   - Respeto de prefers-reduced-motion: si el usuario pide menos animación,
//     se muestra todo de golpe sin transiciones.

(function () {
    'use strict';

    const objetivos = document.querySelectorAll('.revelar');
    if (!objetivos.length) return;

    const reduce = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Sin IntersectionObserver (o con animación reducida) revelamos todo ya.
    if (reduce || !('IntersectionObserver' in window)) {
        objetivos.forEach(function (el) { el.classList.add('revelar--visible'); });
        return;
    }

    const observador = new IntersectionObserver(function (entradas, obs) {
        entradas.forEach(function (entrada) {
            if (entrada.isIntersecting) {
                entrada.target.classList.add('revelar--visible');
                obs.unobserve(entrada.target); // Se revela una sola vez.
            }
        });
    }, { threshold: 0.15 });

    objetivos.forEach(function (el) { observador.observe(el); });
})();
