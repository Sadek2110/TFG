# FastPlay — Guía de presentación por asignatura

> **Proyecto:** FastPlay — *"Fútbol callejero, organizado."*
> Plataforma web (PHP 8 · MVC propio · MySQL · Apache) para gestionar ligas, equipos, partidos y campos de fútbol amateur en Ceuta.
> **Autor:** Sadek — 2º DAW · TFG

Este documento resume **qué enseñar a cada profesor**: las partes del proyecto donde su asignatura tiene más peso. Para cada una hay un *titular*, los *puntos fuertes* y los *ficheros concretos* que abrir durante la defensa.

---

## 1. Diseño de Interfaces — *La capa visual*

**Titular:** Design System propio "glass-and-neon" sin frameworks de CSS, coherente en toda la app.

### Puntos a resaltar
- **Identidad de marca con concepto:** cada pantalla es "un partido nocturno bajo focos" — fondo casi negro (`#060d09`), verde neón (`#16a34a`) para acciones, dorado (`#fbbf24`) **reservado solo a la Liga Pro**.
- **Sistema de variables CSS** en `:root` y componentes prefijados (`.btn-`, `.card-`, `.lg-`). Nada de Bootstrap/Tailwind: CSS escrito a mano.
- **Glassmorphism** real con `backdrop-filter: blur()` y superficies translúcidas reutilizables (`.glass`).
- **Tipografía Inter variable** cargada localmente (peso Black 900 para titulares).
- **Experiencia inmersiva:** landing con animación de scroll basada en vídeo (`hero.webm`) + paralaje y revelado progresivo de bloques.
- **Componentes reutilizables (partials):** navbar, footer, flash messages, toasts, loader, empty-states, cookie-banner, back-button → consistencia visual garantizada.
- **Iconografía SVG inline** (evita emojis que se rompen); marcadores de mapa dibujados a mano en SVG.
- **Diseño responsive** y layouts diferenciados (`main` para la app, `auth` para login/registro).

### Ficheros para enseñar
- [public/css/app.css](public/css/app.css) — Design System completo
- [public/css/scroll-anim.css](public/css/scroll-anim.css) — animaciones de scroll
- [app/views/layouts/main.php](app/views/layouts/main.php) y [app/views/layouts/auth.php](app/views/layouts/auth.php)
- [app/views/partials/](app/views/partials/) — biblioteca de componentes
- [app/views/home/index.php](app/views/home/index.php) — landing inmersiva

---

## 2. Desarrollo en el lado del Servidor — *El backend*

**Titular:** Arquitectura **MVC construida desde cero** (sin Laravel/Symfony), con Front Controller, router propio y capa de datos PDO.

### Puntos a resaltar
- **Patrón MVC clásico con Front Controller único:** todo entra por `public/index.php` → `Router` → `Controller` → `Model` → `Database`.
- **Router propio reflexivo** ([app/core/Router.php](app/core/Router.php)): mapea `/{controlador}/{acción}/{params}`, traduce **kebab-case de la URL a camelCase** del método, y usa `ReflectionMethod` para validar que la acción sea pública, no estática y no herede del controlador base (evita invocar métodos no permitidos).
- **Capa de datos con PDO** ([app/core/Database.php](app/core/Database.php)): singleton de conexión, helpers `one()/all()/value()/run()`, **prepared statements** siempre, soporte MySQL **y** PostgreSQL, y auto-migración de columnas.
- **Separación de responsabilidades por capas:** `controllers/` (orquestan), `models/` (SQL + reglas de negocio), `services/` (lógica externa: Stripe, Mail, Notificaciones, Solicitudes), `views/` (solo presentación).
- **Gestión de dependencias con Composer** ([composer.json](composer.json)): autoload por classmap, SDK de Stripe, PHPMailer, OAuth2-Google y PHPUnit en dev.
- **Carga de configuración por entorno** desde `.env` ([config/config.php](config/config.php)) con prioridad a variables del sistema.
- **Suite de tests con PHPUnit** ([tests/](tests/)): modelos, router, configuración y consistencia de datos, sobre una BD de test aislada.
- **Sistema de envío de emails transaccionales** (verificación, partidos, premium, solicitudes) con plantillas en `app/views/emails/`.

### Ficheros para enseñar
- [app/core/Router.php](app/core/Router.php) y [app/core/Controller.php](app/core/Controller.php)
- [app/core/Database.php](app/core/Database.php)
- [app/models/Usuario.php](app/models/Usuario.php) — modelo con reglas de negocio
- [app/services/](app/services/) — Stripe, Mail, Notificaciones
- [composer.json](composer.json) · [tests/](tests/) · [phpunit.xml](phpunit.xml)

---

## 3. Desarrollo en el lado del Cliente — *El JavaScript*

**Titular:** JavaScript **Vanilla, sin bundlers ni frameworks** — un archivo por *feature*, con manipulación del DOM, eventos, AJAX y JSON.

### Puntos a resaltar
- **Panel contextual AJAX** ([public/js/panel-contextual.js](public/js/panel-contextual.js)): `fetch` GET a `/api/contexto`, recibe JSON y reconstruye el DOM según el rol (admin/capitán/jugador/visitante). `async/await`, `response.ok`, `try/catch/finally`. **Es la pieza estrella.**
- **Parsing de JSON con `try/catch`** y reconstrucción del DOM sin `innerHTML` (`createElement`, `replaceChildren`, `DocumentFragment`).
- **Validación de formularios con regex** ([public/js/validacion.js](public/js/validacion.js)) en `blur`/`input`/`submit`, con feedback accesible, antes del envío al servidor.
- **Delegación de eventos** ([public/js/detalle-equipo.js](public/js/detalle-equipo.js)): filtrado y resaltado de la tabla de miembros con un solo listener.
- **Gestión de cookies y consentimiento** ([public/js/cookies.js](public/js/cookies.js)) — banner y persistencia de preferencias con API pública `window.FastplayCookies`.
- **Componentes interactivos propios:** carta tipo FIFA con tilt 3D (`carta-jugador.js`), animaciones de scroll con IntersectionObserver (`animaciones-scroll.js`), contador animado (`inicio.js`), tema claro/oscuro con localStorage (`tema.js`).
- **Arquitectura JS modular:** patrón IIFE + `'use strict'`, sin contaminar el scope global, un fichero por funcionalidad.

### Ficheros para enseñar
- [public/js/panel-contextual.js](public/js/panel-contextual.js) — **el más completo** (evento + AJAX + JSON + DOM + roles)
- [public/js/validacion.js](public/js/validacion.js)
- [public/js/cookies.js](public/js/cookies.js) · [public/js/tema.js](public/js/tema.js)
- [public/js/carta-jugador.js](public/js/carta-jugador.js) · [public/js/animaciones-scroll.js](public/js/animaciones-scroll.js) · [public/js/inicio.js](public/js/inicio.js) · [public/js/detalle-equipo.js](public/js/detalle-equipo.js)

---

## 4. Despliegue — *Puesta en producción*

**Titular:** App **contenedorizada con Docker** y desplegable en hosting real, con configuración por entorno y front controller de Apache.

### Puntos a resaltar
- **Dockerfile de producción** ([Dockerfile](Dockerfile)): imagen `php:8.2-apache`, extensiones `pdo_mysql`/`mbstring`, `mod_rewrite`, instalación de dependencias con `composer install --no-dev --optimize-autoloader`, y **document root movido a `public/`** por seguridad.
- **Configuración por variables de entorno** (12-factor): BD, Stripe, Mail SMTP, Google OAuth/Maps se inyectan vía `.env` o variables del contenedor/panel ([.env.example](.env.example)). El código nunca lleva credenciales.
- **Detección automática de `BASE_URL`** según el subdirectorio de instalación → funciona igual en XAMPP local, Docker o subcarpeta de hosting sin tocar código.
- **Reescritura de URLs con `.htaccess`** ([public/.htaccess](public/.htaccess)): front controller, bloqueo de archivos sensibles y `ServerSignature Off`.
- **Tres modos de despliegue documentados:** XAMPP local, Docker (`docker build` + `docker run -p 8080:80`), y servidor embebido de PHP (`php -S localhost:8000 router.php`).
- **Despliegue en EasyPanel** con script de diagnóstico propio ([scripts/diagnose-easypanel.php](scripts/diagnose-easypanel.php)) para verificar entorno en el servidor.
- **`.dockerignore` y `.gitignore`** para no subir secretos, BD ni `vendor/`.

### Ficheros para enseñar
- [Dockerfile](Dockerfile) · [.dockerignore](.dockerignore)
- [.env.example](.env.example) — contrato de configuración
- [public/.htaccess](public/.htaccess) y [.htaccess](.htaccess) raíz
- [scripts/diagnose-easypanel.php](scripts/diagnose-easypanel.php)

---

## 5. Seguridad — *Defensa en profundidad*

**Titular:** Seguridad aplicada en **capas** (sesiones, CSRF, XSS, SQLi, headers, aislamiento) — toda centralizada en [config/config.php](config/config.php).

### Puntos a resaltar
- **Protección CSRF** en todos los `POST`: token por sesión (`bin2hex(random_bytes(32))`), comparación con `hash_equals()` (anti timing-attack) y respuesta `419` si falla. Helpers `csrf_field()` / `verify_csrf()` / `require_csrf()`.
- **Sesiones endurecidas:** cookies `HttpOnly`, `Secure` (si HTTPS) y `SameSite=Lax`; `use_strict_mode` + `use_only_cookies`; `session_regenerate_id(true)` tras login (anti *session fixation*); cookie acotada a la subcarpeta y `save_path` propio.
- **Hashing de contraseñas** con `password_hash()` (bcrypt) y `password_verify()` — nunca en texto plano. El hash se elimina de la sesión al loguear.
- **Rate limiting** de intentos de login (anti fuerza bruta), con ventanas en UTC para casar con la BD.
- **SQL Injection = 0:** 100 % prepared statements vía PDO con `ATTR_EMULATE_PREPARES => false`; sin concatenar SQL nunca.
- **XSS:** escape sistemático con el helper `e()` (`htmlspecialchars` con `ENT_QUOTES`) en las vistas + **CSP restrictiva**.
- **Cabeceras de seguridad** desde un único origen (`security_headers()`): `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy`.
- **Aislamiento del document root:** solo `public/` es accesible; `app/`, `config/`, `storage/` bloqueadas por `.htaccess`. La carpeta `uploads/` **no permite ejecutar scripts**.
- **Autorización por roles** (visitante / jugador / capitán / admin): p.ej. crear/confirmar/finalizar partido exige ser capitán; no se puede degradar al último admin.
- **Validación de credenciales de terceros:** la clave de Stripe se valida por formato (`sk_test_`/`sk_live_`) antes de usarse ([app/services/StripeService.php](app/services/StripeService.php)).
- **Login con OAuth2 de Google** como alternativa segura a contraseña ([app/controllers/AuthController.php](app/controllers/AuthController.php)).

### Ficheros para enseñar
- [config/config.php](config/config.php) — **el fichero estrella**: sesiones, CSRF, headers, hashing helpers
- [app/core/Database.php](app/core/Database.php) — prepared statements
- [app/controllers/AuthController.php](app/controllers/AuthController.php) — login, rate limit, OAuth
- [public/.htaccess](public/.htaccess) y los `.htaccess` por carpeta — aislamiento

---

## Resumen de un vistazo

| Asignatura | Pieza estrella del proyecto | Fichero clave |
|---|---|---|
| **Diseño de Interfaces** | Design System "glass-and-neon" propio | [public/css/app.css](public/css/app.css) |
| **Servidor (Backend)** | MVC + router reflexivo + PDO, sin framework | [app/core/Router.php](app/core/Router.php) |
| **Cliente (JS)** | Mapa de campos con API externa, Vanilla JS | [public/js/campos-map.js](public/js/campos-map.js) |
| **Despliegue** | Docker + config por entorno + EasyPanel | [Dockerfile](Dockerfile) |
| **Seguridad** | CSRF + sesiones + CSP + PDO centralizados | [config/config.php](config/config.php) |

---


