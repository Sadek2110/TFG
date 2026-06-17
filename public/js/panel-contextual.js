// Panel contextual — pieza central de la parte de cliente (DWEC).
//
// Reúne casi todos los criterios de la asignatura en un solo flujo:
//   evento (click) -> petición AJAX (fetch GET) -> respuesta JSON ->
//   reconstrucción del DOM según el ROL que devuelve el servidor.
//
// Detalles que se demuestran aquí:
//   - async/await con comprobación de response.ok y throw ante error HTTP.
//   - try/catch/finally: el catch muestra un mensaje accesible y el finally
//     restaura siempre el botón, haya ido bien o mal.
//   - Estructuras de datos: Map (rol -> etiqueta/icono) y Set (deduplicar).
//   - DOM sin innerHTML: createElement, replaceChildren, textContent,
//     dataset y classList (evita inyección de HTML).
//   - ARIA dinámico: aria-busy mientras carga y región aria-live para avisos.
//   - i18n ligera: toLocaleTimeString('es-ES') para la última actualización.

(function () {
    'use strict';

    const panel = document.querySelector('[data-panel-contextual]');
    if (!panel) return; // Defensa: el módulo solo actúa si su raíz existe.

    const endpoint = panel.dataset.url;
    const boton    = panel.querySelector('[data-accion="recargar"]');
    const cuerpo   = panel.querySelector('[data-zona="cuerpo"]');
    const aviso    = panel.querySelector('[data-zona="aviso"]');

    // Diccionario rol -> presentación. Un Map deja claro que es una tabla
    // de consulta y no un objeto cualquiera.
    const ROLES = new Map([
        ['administrador', { etiqueta: 'Administrador', insignia: 'rol--admin' }],
        ['capitan',       { etiqueta: 'Capitán',       insignia: 'rol--capitan' }],
        ['jugador',       { etiqueta: 'Jugador',       insignia: 'rol--jugador' }],
        ['visitante',     { etiqueta: 'Visitante',     insignia: 'rol--visitante' }],
    ]);

    function crear(etiqueta, clase, texto) {
        const nodo = document.createElement(etiqueta);
        if (clase) nodo.className = clase;
        if (texto !== undefined) nodo.textContent = texto;
        return nodo;
    }

    function pintar(datos) {
        const rol = ROLES.get(datos.rol) || ROLES.get('visitante');
        const fragmento = document.createDocumentFragment();

        // Cabecera: rol + saludo personalizado.
        const cabecera = crear('div', 'panel-ctx__cabecera');
        const insignia = crear('span', 'panel-ctx__rol ' + rol.insignia, rol.etiqueta);
        insignia.dataset.rol = datos.rol;
        cabecera.appendChild(insignia);
        if (datos.nombre) {
            cabecera.appendChild(crear('span', 'panel-ctx__hola', 'Hola, ' + datos.nombre));
        }
        fragmento.appendChild(cabecera);

        fragmento.appendChild(crear('h3', 'panel-ctx__titulo', datos.titulo));
        fragmento.appendChild(crear('p', 'panel-ctx__mensaje', datos.mensaje));

        // Resumen numérico.
        if (Array.isArray(datos.resumen) && datos.resumen.length) {
            const lista = crear('ul', 'panel-ctx__resumen');
            datos.resumen.forEach(function (item) {
                const li = crear('li', 'panel-ctx__dato');
                li.appendChild(crear('strong', null, String(item.valor)));
                li.appendChild(crear('span', null, item.etiqueta));
                lista.appendChild(li);
            });
            fragmento.appendChild(lista);
        }

        // Acciones. Usamos un Set para no repetir un mismo enlace si el
        // servidor devolviera duplicados.
        if (Array.isArray(datos.acciones) && datos.acciones.length) {
            const vistas = new Set();
            const acciones = crear('div', 'panel-ctx__acciones');
            datos.acciones.forEach(function (accion) {
                if (vistas.has(accion.url)) return;
                vistas.add(accion.url);
                const enlace = crear('a', 'boton boton--secundario boton--pequeno', accion.texto);
                enlace.href = accion.url;
                acciones.appendChild(enlace);
            });
            fragmento.appendChild(acciones);
        }

        // Marca de tiempo localizada.
        if (datos.hora_servidor) {
            const hora = new Date(datos.hora_servidor).toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });
            fragmento.appendChild(crear('p', 'panel-ctx__hora', 'Actualizado a las ' + hora));
        }

        cuerpo.replaceChildren(fragmento);
    }

    function mostrarAviso(texto) {
        aviso.textContent = texto;
        aviso.hidden = texto === '';
    }

    async function cargar() {
        panel.setAttribute('aria-busy', 'true');
        if (boton) boton.disabled = true;
        mostrarAviso('');

        try {
            const respuesta = await fetch(endpoint, {
                headers: { Accept: 'application/json' },
            });
            if (!respuesta.ok) {
                throw new Error('El servidor respondió ' + respuesta.status);
            }
            const datos = await respuesta.json();
            pintar(datos);
        } catch (error) {
            mostrarAviso('No se pudo cargar tu panel (' + error.message + '). Inténtalo de nuevo.');
        } finally {
            panel.setAttribute('aria-busy', 'false');
            if (boton) boton.disabled = false;
        }
    }

    if (boton) {
        boton.addEventListener('click', cargar);
    }

    // Carga inicial automática.
    cargar();
})();
