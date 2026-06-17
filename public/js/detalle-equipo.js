// Interacciones del detalle de equipo.
//
// Qué se demuestra (DWEC):
//   - Delegación de eventos: un único listener en el contenedor atiende los
//     clics e inputs de todas las filas, en lugar de uno por fila.
//   - dataset para leer datos asociados a cada fila (data-nombre).
//   - Filtrado en vivo de la tabla de miembros y resaltado de fila.

(function () {
    'use strict';

    const zona = document.querySelector('[data-tabla-miembros]');
    if (!zona) return;

    const buscador = zona.querySelector('[data-buscar-miembro]');
    const contador = zona.querySelector('[data-contador-visibles]');
    const filas = zona.querySelectorAll('tbody tr[data-nombre]');

    function normalizar(texto) {
        return (texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, ''); // quita acentos
    }

    function filtrar(termino) {
        const t = normalizar(termino);
        let visibles = 0;
        filas.forEach(function (fila) {
            const coincide = normalizar(fila.dataset.nombre).includes(t);
            fila.hidden = !coincide;
            if (coincide) visibles++;
        });
        if (contador) {
            contador.textContent = visibles + (visibles === 1 ? ' miembro' : ' miembros');
        }
    }

    // Delegación: un solo listener de input para el buscador.
    if (buscador) {
        zona.addEventListener('input', function (evento) {
            if (evento.target === buscador) {
                filtrar(buscador.value);
            }
        });
    }

    // Delegación: clic en cualquier fila para resaltarla.
    zona.addEventListener('click', function (evento) {
        const fila = evento.target.closest('tr[data-nombre]');
        if (!fila || !zona.contains(fila)) return;
        // No interferir con los botones/enlaces de la fila.
        if (evento.target.closest('a, button')) return;
        fila.classList.toggle('fila--activa');
    });
})();
