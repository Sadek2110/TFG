// Revelado progresivo de secciones al hacer scroll y en cascada al cargar.
//
// Qué se demuestra (DWEC):
//   - IntersectionObserver para reaccionar cuando un elemento entra en el
//     viewport, sin escuchar el evento scroll (más eficiente).
//   - Respeto de prefers-reduced-motion: si el usuario pide menos animación,
//     se muestra todo de golpe sin transiciones.
//   - data-revelar: hijos directos con animación escalonada al cargar
//     (se les aplica un --revelar-delay en función de su posición).

(function () {
    'use strict';

    const reduce = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // 1) data-revelar: cascada al cargar la página (sin esperar al scroll).
    //    Se aplica a cualquier elemento con [data-revelar] que esté dentro de
    //    un .revelar (típicamente el hero, que está visible desde el inicio).
    //    Calculamos un delay en función de la posición del elemento entre sus
    //    hermanos dentro del mismo contenedor para crear el efecto cascada.
    const contenedores = document.querySelectorAll('.revelar');
    contenedores.forEach(function (padre) {
        const hijos = padre.querySelectorAll('[data-revelar]');
        hijos.forEach(function (hijo, i) {
            // Si el autor fija un delay explícito, lo respetamos; si no,
            // usamos 120 ms × índice con un tope para no eternizar la entrada.
            const delay = hijo.dataset.revelarDelay !== undefined
                ? parseInt(hijo.dataset.revelarDelay, 10) || 0
                : Math.min(i, 6) * 120;
            hijo.style.transitionDelay = delay + 'ms';
        });
    });

    const enCascada = document.querySelectorAll('.revelar [data-revelar]');
    if (enCascada.length) {
        if (reduce || !('IntersectionObserver' in window)) {
            enCascada.forEach(function (el) { el.classList.add('revelar--visible'); });
        } else {
            const obsCascada = new IntersectionObserver(function (entradas, obs) {
                entradas.forEach(function (entrada) {
                    if (entrada.isIntersecting) {
                        entrada.target.classList.add('revelar--visible');
                        obs.unobserve(entrada.target);
                    }
                });
            }, { threshold: 0.05 });
            enCascada.forEach(function (el) { obsCascada.observe(el); });
        }
    }

    // 2) .revelar clásico: aparece al entrar en pantalla. Excluimos el hero
    //    (su contenido ya se anima con data-revelar y el section en sí se ve
    //    desde el primer momento, no queremos que parpadee al cargar).
    const secciones = document.querySelectorAll('.revelar:not(.hero)');
    if (!secciones.length) return;

    if (reduce || !('IntersectionObserver' in window)) {
        secciones.forEach(function (el) { el.classList.add('revelar--visible'); });
        return;
    }

    const observador = new IntersectionObserver(function (entradas, obs) {
        entradas.forEach(function (entrada) {
            if (entrada.isIntersecting) {
                entrada.target.classList.add('revelar--visible');
                obs.unobserve(entrada.target);
            }
        });
    }, { threshold: 0.15 });

    secciones.forEach(function (el) { observador.observe(el); });
})();
