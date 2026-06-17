# Dónde encontrar cada cosa en mi código — Proyecto Final DWEC

> Hola profesor, aquí te dejo un mapa con **lo más relevante** de Desarrollo en el lado del Cliente. Para cada conocimiento que evalúas pongo el archivo donde mejor se ve, y debajo algún archivo de apoyo si quieres más ejemplos. Todos los ficheros JS están en `public/js/` y son **JavaScript vanilla** (sin librerías), cada uno envuelto en una IIFE con `'use strict'`.

| Conocimiento DWEC | Dónde verlo (ejemplo principal) |
|---|---|
| **AJAX (fetch) + JSON** | `panel-contextual.js` — Pieza central: pulso un botón → evento → `fetch` GET a `/api/contexto` (endpoint PHP `ControladorApi::contexto`) → recibo JSON → transformo el DOM según el rol del usuario (admin, capitán, jugador, visitante). Usa `async/await`, `response.ok`, `throw` ante error HTTP y `finally`. |
| **Manipulación del DOM** | `panel-contextual.js` — `createElement`, `replaceChildren`, `dataset`, `classList`, `DocumentFragment` y atributos ARIA, **sin `innerHTML`** (evita inyección). *Apoyo: `detalle-equipo.js` (filtra y resalta filas de la tabla de miembros).* |
| **Eventos** | `validacion.js` — `blur`, `input` y `submit` con `preventDefault`. *Apoyo: delegación de `click` en `detalle-equipo.js`, `pointermove` en `carta-jugador.js`.* |
| **Validación con expresiones regulares** | `validacion.js` — Catálogo de regex (email, contraseña, nombre, ciudad, dorsal, teléfono) que valida en tiempo real y muestra mensajes accesibles (`aria-invalid`, `aria-describedby`). Complementa a la validación PHP, que sigue siendo la autoritativa. |
| **Cookies** | `cookies.js` — Banner aceptar/rechazar, cookies con prefijo `fp_client_` y API pública `window.FastplayCookies` reutilizable desde cualquier script. |
| **Manejo de errores (try/catch)** | `panel-contextual.js` (fallo de red en el `fetch`) y `validacion.js` (compilar un `pattern` inválido). *Apoyo: `tema.js` con `localStorage` bloqueado.* |

**Extras hechos con JS propio (sin librerías):** `animaciones-scroll.js` (revelado de secciones con `IntersectionObserver`), `carta-jugador.js` (efecto tilt 3D que sigue al cursor con `pointermove`) e `inicio.js` (contador animado del landing con `requestAnimationFrame`). También `tema.js` (tema claro/oscuro persistido en `localStorage`).

## Endpoint que da soporte a la parte AJAX

`GET /api/contexto` → [app/controladores/ControladorApi.php](../../app/controladores/ControladorApi.php). Devuelve un JSON con el resumen del sitio adaptado al rol de quien pregunta. Es la fuente de datos de `panel-contextual.js`.
