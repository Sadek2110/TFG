# FastPlay — Explicación técnica por asignatura

> **Proyecto:** FastPlay — *"Fútbol callejero, organizado."*
> Plataforma web en **PHP 8.2 · MVC propio · MySQL/PostgreSQL · Apache** para gestionar ligas, equipos, partidos y campos de fútbol amateur en Ceuta.
> **Autor:** Sadek — 2º DAW · TFG

Este documento explica, **asignatura por asignatura**, las *funciones reales*, las *buenas prácticas* y los *detalles de implementación* tal como están en el código. Para cada apartado se indica el fichero exacto y, cuando aporta, el método o la línea concreta.

---

## Índice

1. [Diseño de Interfaces](#1-diseño-de-interfaces)
2. [Desarrollo en el lado del Servidor](#2-desarrollo-en-el-lado-del-servidor)
3. [Desarrollo en el lado del Cliente (DWEC)](#3-desarrollo-en-el-lado-del-cliente-dwec)
4. [Despliegue](#4-despliegue)
5. [Seguridad](#5-seguridad)
6. [Tabla resumen](#tabla-resumen)

---

## 1. Diseño de Interfaces

**Titular:** Design System propio *"glass-and-neon"* escrito a mano, sin Bootstrap ni Tailwind, coherente en toda la app.

### Concepto de marca
- Cada pantalla simula *"un partido nocturno bajo focos"*: fondo casi negro (`#060d09`), **verde neón** (`#16a34a` / `#4ade80`) para acciones, y **dorado** (`#fbbf24`) reservado en exclusiva a la **Liga Pro**.
- La identidad es consistente porque todo nace de **variables CSS en `:root`** y de componentes prefijados (`.btn-`, `.card-`, `.lg-`, `.scroll-`).

### Técnicas y prácticas concretas
- **Glassmorphism real:** superficies translúcidas con `backdrop-filter: blur(18px) saturate(125%)` y su prefijo `-webkit-`. Ver `.scroll-section__inner` en [public/css/scroll-anim.css](../public/css/scroll-anim.css#L64-L73).
- **Gradientes en capas** para simular focos de estadio: `radial-gradient` superpuestos + `linear-gradient` de base ([scroll-anim.css:20-27](../public/css/scroll-anim.css#L20-L27)).
- **Tipografía fluida** con `clamp()` para que los titulares escalen con el viewport sin media queries: `font-size: clamp(36px, 5.5vw, 56px)` ([scroll-anim.css:114-119](../public/css/scroll-anim.css#L114-L119)).
- **Tipografía Inter variable** cargada localmente (peso Black 900 para titulares).
- **Diseño responsive** con breakpoints en cascada (`980px`, `768px`, `480px`) que reorganizan secciones, ocultan los *scroll-dots* y apilan las tarjetas de precios.
- **Accesibilidad:**
  - `@media (prefers-reduced-motion: reduce)` desactiva todas las animaciones y transiciones ([scroll-anim.css:269-278](../public/css/scroll-anim.css#L269-L278)).
  - `:focus-visible` con `outline` verde en botones y enlaces de liga.
- **Layouts diferenciados:** `main` para la app y `auth` para login/registro ([app/views/layouts/](../app/views/layouts/)).
- **Componentes reutilizables (partials):** navbar, footer, flash messages, toasts, loader, empty-states, cookie-banner, back-button → consistencia garantizada ([app/views/partials/](../app/views/partials/)).
- **Iconografía SVG inline** en lugar de emojis (no se rompen entre sistemas); los marcadores del mapa son SVG dibujado a mano.
- **Cache-busting de assets:** el helper `asset()` añade `?v=<filemtime>` a CSS/JS para forzar recarga al cambiar el fichero ([config/config.php:155-160](../config/config.php#L155-L160)).

### Ficheros para enseñar
- [public/css/app.css](../public/css/app.css) — Design System completo.
- [public/css/scroll-anim.css](../public/css/scroll-anim.css) — animaciones de scroll de la landing.
- [app/views/layouts/main.php](../app/views/layouts/main.php) y [app/views/layouts/auth.php](../app/views/layouts/auth.php).
- [app/views/partials/](../app/views/partials/) — biblioteca de componentes.
- [app/views/home/index.php](../app/views/home/index.php) — landing inmersiva.

---

## 2. Desarrollo en el lado del Servidor

**Titular:** Arquitectura **MVC construida desde cero** (sin Laravel/Symfony), con Front Controller único, router reflexivo y capa de datos PDO.

### Flujo de una petición
`public/index.php` (Front Controller) → `Router::dispatch()` → `Controller` → `Model` → `Database` → `View`.

### El Router propio — [app/core/Router.php](../app/core/Router.php)
- Parsea la URL `/{controlador}/{acción}/{params}` con `parse_url` + `explode`.
- **Traduce kebab-case → camelCase:** `resend-verification` en la URL invoca `resendVerification()` ([Router.php:22-24](../app/core/Router.php#L22-L24)).
- **Validación con regex** del slug y la acción antes de tocar el sistema de ficheros ([Router.php:26](../app/core/Router.php#L26)).
- **Reflexión defensiva:** usa `ReflectionMethod` para invocar la acción **solo si** es pública, no estática y **no está declarada en el `Controller` base** — así nadie puede llamar a helpers internos vía URL ([Router.php:55-69](../app/core/Router.php#L55-L69)).
- **Conteo de parámetros:** compara `getNumberOfRequiredParameters()` con los segmentos recibidos; si faltan, responde 404 en vez de petar.
- **Manejo de errores:** captura cualquier `Throwable` y delega en `serverError()` (HTTP 500 + `error_log`), y `notFound()` (HTTP 404) renderiza una vista propia.
- Caso especial cableado: `auth/google/callback` → acción `googleCallback`.

### Capa de datos — [app/core/Database.php](../app/core/Database.php)
- **Singleton de conexión PDO** (`private static ?PDO $pdo`), reutilizado en toda la app ([Database.php:8-36](../app/core/Database.php#L8)).
- **Atributos seguros por defecto:** `ERRMODE_EXCEPTION`, `FETCH_ASSOC` y **`ATTR_EMULATE_PREPARES => false`** (prepared statements reales en el motor).
- **Soporta MySQL y PostgreSQL** según `DB_DRIVER`; el DSN se construye en [config/config.php:57-74](../config/config.php#L57-L74).
- **Helpers de consulta** que encapsulan el patrón prepare/execute:
  - `run($sql, $params)` → `PDOStatement`.
  - `one()` → una fila o `null`.
  - `all()` → todas las filas.
  - `value()` → un escalar (primera columna).
  - `insertId()` → último id autoincremental.
- **Auto-migración suave:** al conectar, comprueba columnas (`email_verified`, `verification_token`) y las añade con `ALTER TABLE` si faltan, silenciando el error si ya existen ([Database.php:19-33](../app/core/Database.php#L19-L33)).
- `repairConsistency()` repara integridad referencial entre `matches` y `league_teams`.

### Separación de responsabilidades
- `app/controllers/` — orquestan la petición (23 controladores: Auth, Dashboard, Teams, Matches, Leagues, Payment, Subscription, Chat, Notification…).
- `app/models/` — SQL + reglas de negocio (Usuario, Equipo, Liga, Partido, Campo, Chat, Subscription, Notification…).
- `app/services/` — lógica externa aislada: `StripeService`, `MailService`, `NotificationService`, `MatchRequestService`, `TeamJoinService`.
- `app/views/` — solo presentación.

### Dependencias y configuración
- **Composer** ([composer.json](../composer.json)): `stripe/stripe-php`, `league/oauth2-google`, `phpmailer/phpmailer`, y `phpunit/phpunit` en dev. Autoload por **classmap** sobre `app/core`, `app/models`, `app/controllers`.
- **Carga de `.env` propia** (12-factor) en [config/config.php:14-36](../config/config.php#L14-L36): lee `KEY=VALUE`, ignora comentarios y, clave, **da prioridad a las variables del sistema** (`getenv($key) !== false` → no sobreescribe).
- **Emails transaccionales** con PHPMailer ([app/services/MailService.php](../app/services/MailService.php)) y plantillas en `app/views/emails/` (bienvenida/verificación, partidos, premium, solicitudes).

### Integración de Stripe — [app/services/StripeService.php](../app/services/StripeService.php)
- `createCheckoutSession()` crea una suscripción mensual de 5,00 € (`unit_amount: 500`, `interval: month`) y devuelve la `checkout_url`.
- Usa `client_reference_id` para enlazar la sesión de Stripe con el usuario.

### Tests con PHPUnit — [tests/](../tests/)
- `RouterTest`, `ConfigTest`, `ProductionConfigurationTest`, `EmailVerificationTest`, `DatabaseConsistencyTest` y tests de modelos en `tests/Models/`, todos sobre una BD de test aislada ([phpunit.xml](../phpunit.xml), [tests/bootstrap.php](../tests/bootstrap.php)).

### Ficheros para enseñar
- [app/core/Router.php](../app/core/Router.php) y [app/core/Controller.php](../app/core/Controller.php).
- [app/core/Database.php](../app/core/Database.php).
- [app/models/Usuario.php](../app/models/Usuario.php) — modelo con reglas de negocio.
- [app/services/](../app/services/) · [composer.json](../composer.json) · [tests/](../tests/) · [phpunit.xml](../phpunit.xml).

---

## 3. Desarrollo en el lado del Cliente (DWEC)

**Titular:** JavaScript **Vanilla, sin bundlers ni frameworks** — un fichero por *feature*, patrón **IIFE + `'use strict'`** para no contaminar el scope global.

### Pieza central de la rúbrica: panel contextual AJAX — [public/js/dwec-context-panel.js](../public/js/dwec-context-panel.js)
Demuestra de un tirón casi todos los criterios DWEC:
- **Evento → AJAX → JSON → DOM:** un `click` en el botón dispara un `fetch` GET a `/dashboard/context`; el servidor responde JSON y el panel se re-renderiza según el **rol** del usuario (admin / capitán / jugador / visitante).
- **`async/await`** con comprobación `response.ok`, `throw` ante HTTP de error, `catch` que muestra mensaje accesible y `finally` que restaura el estado del botón ([dwec-context-panel.js:76-104](../public/js/dwec-context-panel.js#L76-L104)).
- **Estructuras de datos:** `Map` para diccionarios de roles y acciones, `Set` para deduplicar chips.
- **Manipulación del DOM sin `innerHTML`:** `createElement`, `replaceChildren`, `textContent`, `dataset`, `classList.toggle`.
- **ARIA dinámico:** `aria-label`, `aria-busy`, `aria-hidden`, `hidden` para reflejar el estado real.
- **Internacionalización ligera:** `toLocaleTimeString('es-ES', …)` para la hora de "última actualización".

### API / librería externa: mapa de campos — [public/js/campos-map.js](../public/js/campos-map.js)
- Pinta los campos de Ceuta con **Leaflet (OpenStreetMap)** y conmuta a **Google Maps** de forma transparente si existe `GOOGLE_MAPS_API_KEY`.
- **Parsing JSON con `try/catch`** leyendo el atributo `data-fields` del HTML y filtrando coordenadas válidas ([campos-map.js:47-55](../public/js/campos-map.js#L47-L55)).
- **Marcadores SVG propios** (pin con mini-campo y balón), popups e **sincronización mapa ↔ tarjetas** (`is-selected`).

### Resto del catálogo DWEC
| Conocimiento | Fichero | Detalle |
|---|---|---|
| **Validación con regex + eventos** | [public/js/form-validation.js](../public/js/form-validation.js) | Catálogo de expresiones regulares (email, contraseña, nombre, ciudad, dorsal…) validando en `blur`/`input`/`submit` con `preventDefault`; feedback accesible (`aria-invalid`, `aria-describedby`). Es complemento de la validación PHP, que sigue siendo la autoritativa. |
| **Cookies + consentimiento** | [public/js/cookie-consent.js](../public/js/cookie-consent.js) | Banner aceptar/rechazar, cookies con prefijo `fp_client_` y API pública `window.FastplayCookies`. |
| **Chat en vivo (polling)** | [public/js/chat-room.js](../public/js/chat-room.js) | POST/GET con sondeo cada 8 s; pinta mensajes con `textContent` + `DocumentFragment` (sin `innerHTML` → anti-inyección). |
| **Eventos avanzados / tilt 3D** | [public/js/fifa-card.js](../public/js/fifa-card.js) | Carta tipo FIFA con efecto *tilt* que sigue al cursor vía `pointermove`. |
| **`IntersectionObserver`** | [public/js/scroll-anim.js](../public/js/scroll-anim.js) | Revelado progresivo de secciones al hacer scroll. |
| **`localStorage`** | [public/js/theme.js](../public/js/theme.js) | Tema claro/oscuro persistente con `try/catch`. |
| **Contador animado** | [public/js/home-init.js](../public/js/home-init.js) | Animación de cifras del landing. |
| **Delegación de eventos** | [public/js/team-detail.js](../public/js/team-detail.js) | `click` delegado, `dataset`, paneles. |

### Buenas prácticas transversales
- **Un fichero por funcionalidad**, todos envueltos en IIFE con `'use strict'`.
- **Sin dependencias salvo Leaflet/Google Maps** donde aportan valor real.
- **Defensa ante el DOM ausente:** cada módulo comprueba que su raíz existe (`if (!panel) return;`) antes de actuar.

### Ficheros para enseñar
- [public/js/dwec-context-panel.js](../public/js/dwec-context-panel.js) — **la pieza estrella** (evento + AJAX + JSON + DOM + roles).
- [public/js/campos-map.js](../public/js/campos-map.js) — API externa + JSON.
- [public/js/form-validation.js](../public/js/form-validation.js) · [public/js/chat-room.js](../public/js/chat-room.js) · [public/js/cookie-consent.js](../public/js/cookie-consent.js).

---

## 4. Despliegue

**Titular:** App **contenedorizada con Docker**, configurable por entorno (12-factor) y desplegable en hosting real sin tocar código.

### Dockerfile — [Dockerfile](../Dockerfile)
- Base **`php:8.2-apache`**.
- Instala extensiones **`pdo`, `pdo_mysql`, `mbstring`** y activa **`mod_rewrite`** (`a2enmod rewrite`).
- Instala dependencias optimizadas para producción: `composer install --no-dev --optimize-autoloader --no-interaction`.
- **Document root movido a `public/`** por seguridad: reescribe los `*.conf` de Apache con `sed` ([Dockerfile:24-25](../Dockerfile#L24-L25)).
- Crea y da permisos (`www-data`, `775`) a `storage/`, `uploads/` y `public/uploads/avatars`.
- Fija `APP_ENV=production` como variable de entorno.

### Configuración por entorno
- Todas las credenciales (BD, Stripe, SMTP, Google OAuth/Maps) se inyectan vía `.env` o variables del contenedor/panel — **el código nunca lleva secretos** ([.env.example](../.env.example) es el contrato).
- La carga de `.env` da prioridad a las variables del sistema, lo que permite que Apache `SetEnv` o el panel de hosting manden sobre el fichero.

### Portabilidad
- **`BASE_URL` autodetectada** desde `dirname($_SERVER['SCRIPT_NAME'])` ([config/config.php:78-81](../config/config.php#L78-L81)) → funciona igual en **XAMPP local**, **Docker** o en una **subcarpeta** de hosting.
- **`.htaccess`** ([public/.htaccess](../public/.htaccess)): front controller con `RewriteRule`, sirve assets físicos sin reescribir, **bloquea ficheros sensibles** (`.env`, `.md`, `.sql`, `.log`, `.lock`…) y desactiva `ServerSignature`.

### Modos de despliegue documentados
1. **XAMPP local.**
2. **Docker:** `docker build` + `docker run -p 8080:80`.
3. **Servidor embebido de PHP:** `php -S localhost:8000 router.php`.
4. **EasyPanel** con script de diagnóstico propio ([scripts/diagnose-easypanel.php](../scripts/diagnose-easypanel.php)) para verificar el entorno del servidor.

- **`.dockerignore` / `.gitignore`** evitan subir secretos, BD y `vendor/`.

### Ficheros para enseñar
- [Dockerfile](../Dockerfile) · [.dockerignore](../.dockerignore).
- [.env.example](../.env.example) — contrato de configuración.
- [public/.htaccess](../public/.htaccess) y el `.htaccess` raíz.
- [scripts/diagnose-easypanel.php](../scripts/diagnose-easypanel.php).

---

## 5. Seguridad

**Titular:** **Defensa en profundidad** centralizada en [config/config.php](../config/config.php) — sesiones, CSRF, XSS, SQLi, cabeceras y aislamiento.

### CSRF — [config/config.php:202-229](../config/config.php#L202-L229)
- Token por sesión generado con `bin2hex(random_bytes(32))`.
- Comparación con **`hash_equals()`** (resistente a *timing attacks*).
- Helpers `csrf_token()` / `csrf_field()` / `verify_csrf()` / `require_csrf()`.
- `require_csrf()` responde **HTTP 419** y aborta si un `POST` no trae token válido; acepta el token por `$_POST['_csrf']` o por cabecera `X-CSRF-Token` (para AJAX).

### Sesiones endurecidas — [config/config.php:86-105](../config/config.php#L86-L105)
- Cookies **`HttpOnly`**, **`Secure`** (solo si HTTPS) y **`SameSite=Lax`**.
- `session.use_strict_mode` + `session.use_only_cookies`.
- **`save_path` propio** (`storage/sessions`) y nombre de cookie `FPSESSID`.
- Cookie **acotada a la subcarpeta** de instalación para no colisionar con otras apps del host.
- **`session_regenerate_id(true)` tras el login** (anti *session fixation*) en `login_user()` ([config/config.php:184-190](../config/config.php#L184-L190)).

### Contraseñas y autenticación — [app/models/Usuario.php](../app/models/Usuario.php)
- **`password_hash()` (bcrypt)** al registrar y **`password_verify()`** al loguear; nunca texto plano.
- El `password_hash` se **elimina de la sesión** al iniciar (`unset($user['password_hash'])`).
- **Rate limiting anti fuerza bruta** sobre la tabla `login_attempts`:
  - `isRateLimited()` cuenta intentos fallidos en una ventana de **600 s** ([Usuario.php:335-343](../app/models/Usuario.php#L335)).
  - **Ventanas en UTC** (`gmdate`) para casar con `datetime('now')` de la BD.
  - `recordAttempt()` registra cada intento y purga los de más de 24 h.
- **Login OAuth2 con Google** como alternativa segura, con verificación del parámetro `state` ([app/controllers/AuthController.php](../app/controllers/AuthController.php) — `google()` / `googleCallback()`).

### SQL Injection = 0
- **100 % prepared statements** vía PDO con `ATTR_EMULATE_PREPARES => false`; jamás se concatena SQL con datos de usuario.

### XSS
- Helper **`e()`** = `htmlspecialchars` con `ENT_QUOTES | ENT_SUBSTITUTE` en todas las vistas ([config/config.php:131-134](../config/config.php#L131-L134)).
- En el cliente, los mensajes del chat usan `textContent` + `DocumentFragment` en vez de `innerHTML`.

### Cabeceras de seguridad — `security_headers()` ([config/config.php:111-128](../config/config.php#L111-L128))
- **Único origen** de cabeceras (no se duplican en `.htaccess`).
- **CSP restrictiva** con allowlists explícitas para mapas (Google/OSM) y CDNs concretos.
- `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` que desactiva geolocalización/micro/cámara.
- *Nota honesta del código:* `'unsafe-inline'` sigue presente porque varias vistas llevan `<style>`/`<script>` inline; está marcado como pendiente de retirar.

### Aislamiento y autorización
- Solo `public/` es accesible; `app/`, `config/` y `storage/` quedan fuera del document root y bloqueadas por `.htaccess`. La carpeta `uploads/` no ejecuta scripts.
- **Autorización por roles** (visitante / jugador / capitán / admin): p. ej. crear/confirmar/finalizar partido exige ser capitán, y no se puede degradar al último admin.
- **Validación de credenciales de terceros:** la clave de Stripe se valida por **formato** (`/^sk_(test|live)_[A-Za-z0-9_]+$/`) y se rechaza el placeholder `sk_test_123` antes de usarse ([app/services/StripeService.php:11-20](../app/services/StripeService.php#L11-L20)).

### Ficheros para enseñar
- [config/config.php](../config/config.php) — **el fichero estrella**: sesiones, CSRF, headers, hashing helpers.
- [app/core/Database.php](../app/core/Database.php) — prepared statements.
- [app/models/Usuario.php](../app/models/Usuario.php) — hashing + rate limit.
- [app/controllers/AuthController.php](../app/controllers/AuthController.php) — login, OAuth.
- [public/.htaccess](../public/.htaccess) — aislamiento.

---

## Tabla resumen

| Asignatura | Pieza estrella | Fichero clave | Práctica destacada |
|---|---|---|---|
| **Diseño de Interfaces** | Design System "glass-and-neon" | [public/css/app.css](../public/css/app.css) | Glassmorphism, `clamp()`, `prefers-reduced-motion` |
| **Servidor (Backend)** | MVC + router reflexivo + PDO | [app/core/Router.php](../app/core/Router.php) | `ReflectionMethod`, kebab→camelCase, helpers PDO |
| **Cliente (DWEC)** | Panel contextual AJAX por roles | [public/js/dwec-context-panel.js](../public/js/dwec-context-panel.js) | `fetch` + `async/await` + DOM sin `innerHTML` |
| **Despliegue** | Docker + 12-factor + EasyPanel | [Dockerfile](../Dockerfile) | Document root en `public/`, `BASE_URL` autodetectada |
| **Seguridad** | CSRF + sesiones + CSP + PDO | [config/config.php](../config/config.php) | `hash_equals`, `random_bytes`, rate limit UTC |
