# Dónde encontrar cada cosa en mi código — Proyecto Final DWEC

> Hola profesor, aquí te dejo un mapa con **lo más relevante** de Desarrollo en el lado del Cliente. Para cada conocimiento que evalúas pongo el archivo donde mejor se ve, y debajo algún archivo de apoyo si quieres más ejemplos.

| Conocimiento DWEC | Dónde verlo (ejemplo principal) |
|---|---|
| **AJAX (fetch) + JSON** | `dwec-context-panel.js` — Pieza central: pulso un botón → evento → `fetch` GET a un endpoint PHP → recibo JSON → transformo el DOM según el rol del usuario (admin, capitán, jugador, visitante). Usa `async/await`, `response.ok` y `finally`. *Apoyo: `chat-room.js` (POST/GET con polling cada 8s).* |
| **Manipulación del DOM** | `dwec-context-panel.js` y `team-detail.js` — `createElement`, `replaceChildren`, `dataset`, `classList`, atributos ARIA. `chat-room.js` pinta mensajes con `textContent` + `DocumentFragment` (sin `innerHTML`, evita inyección). |
| **Eventos** | `form-validation.js` — `blur`, `input` y `submit` con `preventDefault`. *Apoyo: delegación de `click` en `team-detail.js`, `pointermove` en `fifa-card.js`.* |
| **Validación con expresiones regulares** | `form-validation.js` — Catálogo de regex (email, contraseña, nombre, ciudad, dorsal…) que valida en tiempo real y muestra mensajes accesibles (`aria-invalid`, `aria-describedby`). Complementa a la validación PHP, que sigue siendo la autoritativa. |
| **Cookies** | `cookie-consent.js` — Banner aceptar/rechazar, cookies con prefijo `fp_client_` y API pública `window.FastplayCookies` reutilizable desde cualquier script. |
| **Manejo de errores (try/catch)** | `dwec-context-panel.js` (fallo de red en el `fetch`) y `form-validation.js` (parseo de reglas). *Apoyo: `theme.js` con `localStorage`.* |

**Extras hechos con JS propio (sin librerías):** `scroll-anim.js` (animaciones con `IntersectionObserver`), `fifa-card.js` (efecto tilt 3D que sigue al cursor) y `home-init.js` (contador animado del landing).
