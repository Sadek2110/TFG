# 🔧 FastPlay v3 — Arreglos pendientes

> Re-auditoría del proyecto al 2026-05-16. Compara el estado actual con la auditoría previa del 2026-05-10 (commit `3fc0df9`) y añade los fallos nuevos detectados.
>
> Leyenda: 🔴 crítico · 🟠 importante · 🟡 menor · 🔵 documentación / cosmético · ✅ resuelto desde la auditoría previa

---

## 0. Resumen ejecutivo

La mayor parte del Sprint 1 y Sprint 2 propuestos en la versión anterior **están aplicados**: autorización en partidos, guardas de admin, sesiones acotadas, `.gitignore`, índices SQL, `.htaccess` endurecidos, seed protegido por `APP_ENV`, frames movidos a `public/`, assets huérfanos eliminados.

Quedan abiertos algunos ítems del Sprint 2/3 originales y aparecen problemas nuevos serios — sobre todo dos focos que ahora dominan la deuda técnica: **(a)** el repositorio versiona ~250 MB de binarios (runtime de PHP para Windows y los 192 frames PNG) y **(b)** el chat y algunas acciones de partidos siguen sin validar pertenencia/estado.

---

## 1. Resueltos desde la auditoría previa

Solo se listan para cerrar la trazabilidad. No requieren acción.

| Ítem | Cambio que lo resuelve |
|---|---|
| ✅ 1.1 | `uploads/.htaccess` ahora desactiva `ExecCGI`, hace `RemoveHandler` para PHP/CGI/scripting y bloquea por `<FilesMatch>` |
| ✅ 1.2 | `app/`, `config/`, `storage/` con bloque dual Apache 2.4 + 2.2 (`mod_authz_core` con fallback) |
| ✅ 1.3 | `public/.htaccess` ya no duplica cabeceras; comentario explícito indica que el único origen es `security_headers()` en `config.php` |
| ✅ 2.1 / 2.2 / 2.5 | `uploads/image(2).png` y `uploads/video_landing_fastplay.mp4` eliminados; `uploads/` sólo conserva `.htaccess` |
| ✅ 2.3 | Frames movidos a `public/frames/` (`home/index.php:365` ya apunta a `asset('frames/frame_')`) |
| ✅ 3.1 | `home/scroll-animation.php` eliminado; sólo queda `home/index.php` como landing |
| ✅ 3.3 | `partials/tabs.php` eliminado; ya no lo cargan `layouts/main.php` ni `layouts/auth.php` |
| ✅ 3.4 | `home/index.php:27` oculta solo `.fp-footer` y `.fp-bg-glow`; navbar permanece visible |
| ✅ 3.5 | Stats vienen de `Liga::stats()` ([app/models/Liga.php:85-96](app/models/Liga.php#L85-L96)), no de números inventados |
| ✅ 4.1 | `MatchesController::canManageMatch()` valida capitanía en `confirm`/`cancel`/`finish` ([app/controllers/MatchesController.php:135-150](app/controllers/MatchesController.php#L135-L150)) |
| ✅ 4.2 | `create()` valida capitanía sobre `home_team_id` y `away_team_id` ([app/controllers/MatchesController.php:52-57](app/controllers/MatchesController.php#L52-L57)) |
| ✅ 4.3 | `Partido::create()` verifica que ambos equipos estén inscritos en la liga ([app/models/Partido.php:71-77](app/models/Partido.php#L71-L77)) |
| ✅ 4.4 | `AdminController::deleteUser` y `setRole` protegen al último admin ([app/controllers/AdminController.php:42-49](app/controllers/AdminController.php#L42-L49), [74-81](app/controllers/AdminController.php#L74-L81)) |
| ✅ 4.5 | `redirect` seguido de `return;` explícito en `LeaguesController::register` |
| ✅ 4.7 | `Usuario::dashboardStats()` ya devuelve métricas reales (partidos jugados, equipos, capitanías, logros) en lugar de ceros |
| ✅ 4.8 | `Partido::delete()` se invoca desde `MatchesController::delete`; `Chat::createRoom()` se usa desde `ChatController::createRoom` |
| ✅ 5.1 | `config.php:38` acota la cookie de sesión a `BASE_URL ?: '/'` |
| ✅ 5.4 | `mkdir()` con `RuntimeException` en lugar de `@` ([config/config.php:16-20](config/config.php#L16-L20)) |
| ✅ 6.2 | `Router` elimina el array `$blocked`; ahora confía en `ReflectionMethod::isPublic()` + `getDeclaringClass()` ([app/core/Router.php:44-50](app/core/Router.php#L44-L50)) |
| ✅ 6.3 | `public/index.php:10-21` registra `set_exception_handler` y `register_shutdown_function` |
| ✅ 7.1 / 7.6 / 7.7 | README sin referencias a `BUGS.md`, sin árbol obsoleto, sin SHAs de commits |
| ✅ 7.2 | Mapa de Rutas (`README.md:200-237`) coincide con el código real (`/auth/login`, `/leagues/show/{id}`, etc.) |
| ✅ 7.3 | `README.md:346` aclara explícitamente que **no se usan namespaces** |
| ✅ 7.4 | `README.md:305` reconoce que la subida de archivos está "pendiente de implementación"; ya no promete validación MIME |
| ✅ 7.5 | El árbol del README ya no promete un subsistema de logs inexistente |
| ✅ 8.1 | `.gitignore` creado: cubre SQLite, journal, IDE, sistema |
| ✅ 8.2 | `Database::seed()` protegido por `APP_ENV !== 'production'` ([app/core/Database.php:197-199](app/core/Database.php#L197-L199)) |
| ✅ 8.3 | Índices `idx_login_attempts_email` e `idx_login_attempts_ip` creados ([app/core/Database.php:188-189](app/core/Database.php#L188-L189)) |
| ✅ 8.7 | Seed sube las contraseñas demo a `Admin1234!` / `Demo1234!` |

---

## 2. Pendientes de la auditoría previa que siguen abiertos

🟠 **2.1 — Inline CSS/JS masivo en `home/index.php`** (heredado de 5.2 y 7.8)
- [home/index.php:2-149](app/views/home/index.php#L2-L149) contiene un bloque `<style>` de **148 líneas** con todas las clases `.scroll-*` específicas de la landing.
- [home/index.php:361-428](app/views/home/index.php#L361-L428) tiene un bloque `<script>` de 67 líneas con la inicialización del scroll.
- Mientras existan, la CSP debe seguir permitiendo `'unsafe-inline'` para `style-src` y `script-src` ([config.php:64-71](config/config.php#L64-L71)).
- **Fix:** mover las reglas `.scroll-*` a `public/css/scroll-anim.css` (ya existe) y el inicializador a `public/js/scroll-anim.js`. El path de frames puede llegar vía `<canvas data-frames-base="…">` para no depender de PHP en línea.

🟠 **2.2 — `<link rel="stylesheet">` cargado desde dentro de `<body>`**
- [home/index.php:1](app/views/home/index.php#L1) emite `<link rel="stylesheet" href="…/scroll-anim.css">` como **primera línea de la vista**, pero la vista se inyecta en [layouts/main.php:19](app/views/layouts/main.php#L19), debajo de `</head>` (línea 11). Resultado: la etiqueta `<link>` queda dentro de `<body>`, lo que causa FOUC y orden HTML inválido.
- **Fix:** exponer un slot `$head` en `main.php` (`<?= $head ?? '' ?>` antes de `</head>`) y que las vistas que necesiten CSS propio lo declaren ahí, o fusionar `scroll-anim.css` dentro de `app.css`.

🟠 **2.3 — `Usuario::register()` ignora `city` y `position`** (era Fallo 4.6)
- [app/models/Usuario.php:61-64](app/models/Usuario.php#L61-L64) hace `INSERT INTO users (name,email,phone,age,password_hash,role)` y omite las columnas `city` y `position`, aunque el esquema las define ([Database.php:62-63](app/core/Database.php#L62-L63)) y el README las anuncia como parte del perfil.
- **Fix:** o se piden en el formulario de alta y se persisten, o se quitan del esquema inicial y se admite que solo se rellenan vía `profile/edit`.

🟠 **2.4 — CSP sigue con `'unsafe-inline'` en `script-src` y `style-src`** (Fallo 5.2)
- Consecuencia directa de 2.1. Documentado en el propio `config.php:55-56`, pero sigue siendo una concesión real.

🟡 **2.5 — Footer con iconos de redes no clicables** (Fallo 3.6)
- [partials/footer.php:31-35](app/views/partials/footer.php#L31-L35) renderiza tres `<span aria-disabled="true">` (`𝕏`, `in`, `ig`) con estilo de botón pero sin enlace. Aunque tienen `aria-disabled` y `title="Próximamente"`, visualmente parecen interactivos.
- **Fix:** o convertir a `<a href>` reales cuando existan, o eliminarlos hasta que haya cuentas activas.

🟡 **2.6 — Chat sin auto-refresh** (Fallo 8.5)
- [chat/room.php:31-36](app/views/chat/room.php#L31-L36) sólo hace scroll inicial al final del feed; no hay polling ni WebSocket. El README sigue anunciando "chat en vivo" en línea 67.
- **Fix corto:** `fetch('/chat/messages/{id}?after={lastId}')` cada 5-10 s, devolviendo JSON. Si v3 va a ser final, etiquetar como "chat asíncrono" en el README.

🟡 **2.7 — Router rechaza guiones medios en URLs** (Fallo 6.1)
- [Router.php:15](app/core/Router.php#L15) sigue con `^[a-zA-Z0-9_]+$`. Ahora mismo no genera inconsistencias visibles (el método `setRole`/`deleteUser` usa camelCase, ver README:237), pero limita la expresividad futura. Es decisión consciente; documentado en el README.

🟡 **2.8 — `MatchesController::create` redirige si el usuario no tiene equipo** (Fallo 8.6)
- [MatchesController.php:41-45](app/controllers/MatchesController.php#L41-L45). Aún rompe el ciclo POST. Mejor render in-place con CTA "Crea un equipo →".

🟡 **2.9 — `config.php` sigue siendo un "todo en uno" de ~194 líneas** (Fallo 5.3)
- Refactor opcional; el código funciona. Solo se documenta para no perderlo del radar.

🟡 **2.10 — `Content-Type` UTF-8 dependiente del default de Apache** (Fallo 8.4)
- Conviene `header('Content-Type: text/html; charset=UTF-8')` explícito en `security_headers()` o en el layout principal, para no depender de la config del host.

🔵 **2.11 — Fallback `'mayo de 2026'` en `terms.php:16`** (heredado de Fallo 8.8)
- `LegalController::terms` ya inyecta `LEGAL_LAST_UPDATED = '2026-05-10'` ([LegalController.php:6,12](app/controllers/LegalController.php#L6)), por lo que el fallback `<?= e($lastUpdated ?? 'mayo de 2026') ?>` en [terms.php:16](app/views/legal/terms.php#L16) ya nunca se usa. Es código muerto: o se quita el `??` o se centraliza el valor.

---

## 3. Fallos nuevos detectados en esta auditoría

### 3.1 Repositorio / assets

🔴 **3.1.1 — `php/` (runtime de PHP para Windows) versionado en el repo**
- El directorio [`php/`](php/) contiene 82 archivos rastreados (`php.exe`, `php-cgi.exe`, `php8ts.dll`, `libcrypto-3-x64.dll`, `icudt71.dll`, `php.ini`, etc.) por un total de **~85 MB**.
- Un runtime no pertenece al repositorio: rompe la portabilidad (no funciona en Linux/Mac), expone una `php.ini` arbitraria del entorno del autor y multiplica el tamaño del clon.
- **Fix:**
  ```bash
  git rm -r --cached php/
  echo 'php/' >> .gitignore
  git commit -m "Quita runtime PHP del repositorio"
  ```
- Si se necesita reproducibilidad de entorno, usar el `Dockerfile` ya presente o documentar la versión de PHP requerida en el README. El historial seguirá pesando — ver 3.1.2.

🔴 **3.1.2 — El `.git/` pesa ~230 MB**
- Suma de los 85 MB del runtime + 169 MB de frames + churn. Cualquier clon nuevo descarga 230 MB. Si el proyecto va a ser público (portfolio), considerar `git filter-repo` o BFG **una vez** tras aplicar 3.1.1 y 3.1.3 para purgar los blobs históricos.
- ⚠️ Acción destructiva: hablarlo antes y avisar a cualquier colaborador. Si nadie ha clonado el repo todavía, ahora es el mejor momento.

🟠 **3.1.3 — 192 PNG (~169 MB) en `public/frames/` versionados**
- `git ls-files public/frames/ | wc -l → 192`; tamaño en disco 169 MB.
- Mismo problema que ya señalaba la auditoría 2.4 anterior (en `uploads/`), simplemente mudado de carpeta.
- **Fix recomendado:** re-codificar la secuencia a un único `.webm`/`.mp4` (`ffmpeg -framerate 30 -i frame_%04d.png -c:v libvpx-vp9 -b:v 0 -crf 32 hero.webm` típicamente <5 MB) y reemplazar el render por canvas por `<video autoplay muted playsinline>`. Si se mantiene el canvas, generar los frames en deploy a partir del vídeo y excluirlos del repo.

🟠 **3.1.4 — `php/php.ini` se distribuye con la app**
- Aunque sea solo de desarrollo, versionar el `.ini` del runtime mezcla configuración local con código fuente y puede filtrar rutas absolutas / extensiones cargadas que el host real no tiene. Eliminar junto con el resto de `php/`.

🔵 **3.1.5 — `.gitignore` con reglas muertas**
- [.gitignore:9-10](.gitignore#L9-L10) mantiene `!uploads/frames/` y `!uploads/frames/**`, pero esa carpeta ya no existe (los frames están en `public/frames/`). Líneas obsoletas que confunden.

🔵 **3.1.6 — Faltan `LICENSE`, `SECURITY.md` y `CHANGELOG.md`**
- El README cita "uso académico" pero no hay archivo `LICENSE`. Sin él, por defecto el código no tiene licencia explícita y nadie puede reutilizarlo legalmente.
- Tampoco hay `SECURITY.md` (canal para reportar vulnerabilidades) ni `CHANGELOG.md` (el README delega "el historial completo de cambios vive en git log", lo cual es razonable pero no sustituye un changelog narrado).

### 3.2 Seguridad y autorización

🟠 **3.2.1 — `ChatController::send` no valida pertenencia a la sala**
- [ChatController.php:35-46](app/controllers/ChatController.php#L35-L46) sólo exige `requireAuth()` y CSRF. Cualquier usuario autenticado puede escribir en cualquier `chat_rooms.id`, incluida `match_negotiation` (privada entre capitanes) o cualquier futura sala de equipo.
- **Fix:** introducir el concepto de "miembros de sala" (tabla `chat_room_members` o derivar dinámicamente desde `team_members`/`matches`) y verificar antes de insertar.

🟠 **3.2.2 — `MatchesController::cancel` y `finish` no validan transiciones de estado**
- [MatchesController.php:92-102](app/controllers/MatchesController.php#L92-L102): un capitán puede llamar `cancel` sobre un partido ya `finished`, perdiendo el marcador registrado. Y al revés: `finish` sobre un `cancelled` resucita el partido sin chequeo.
- **Fix:** en `Partido::setStatus` rechazar transiciones inválidas (de `finished`/`cancelled` no se puede salir; a `finished` solo desde `confirmed`).

🟠 **3.2.3 — `Partido::create` no verifica que `field_id` exista**
- [Partido.php:55-86](app/models/Partido.php#L55-L86) acepta cualquier `field_id` recibido por POST y lo inserta sin SELECT previo. La FK con `ON DELETE SET NULL` (Database.php:143) protege ante borrados, pero permite insertar IDs inventados (ej. `field_id=999`). Lo mismo aplicaba a `league_id` antes del chequeo de inscripción.
- **Fix:** `if ($field && !Database::value('SELECT 1 FROM fields WHERE id=?', [$field])) $errors[…]`.

🟠 **3.2.4 — `MatchesController::finish` acepta marcadores fuera de rango**
- [MatchesController.php:128-130](app/controllers/MatchesController.php#L128-L130) hace `(int) $_POST['home_score']` y solo aplica `max(0, …)`. Un POST con `home_score=99999` se guarda. Para datos no críticos no es urgente, pero conviene un tope (`min(99, …)`) y rechazar si los dos son 0 en un partido finalizado real.

🟠 **3.2.5 — Borrado de equipo destruye partidos pasados y standings**
- `teams.id` tiene `ON DELETE CASCADE` en `matches.home_team_id`, `matches.away_team_id`, `league_teams.team_id` ([Database.php:140-141, 114-115](app/core/Database.php#L140)). `TeamsController::delete` ([TeamsController.php:94-109](app/controllers/TeamsController.php#L94-L109)) permite al capitán borrar el equipo sin pasar por ningún chequeo: se llevará por delante todo el histórico de partidos jugados, la posición en cualquier liga activa y los datos de clasificación.
- **Fix:** soft-delete (`teams.archived_at`) o bloqueo si `EXISTS (SELECT 1 FROM matches WHERE home_team_id=? OR away_team_id=?)` o si el equipo está inscrito en una liga con `status='open'`/`'in_progress'`.

🟠 **3.2.6 — Borrar un usuario captain destruye su equipo (cascada)**
- `teams.captain_id` tiene `ON DELETE CASCADE` contra `users.id` ([Database.php:77](app/core/Database.php#L77)). Eliminar un usuario que sea capitán de varios equipos los borra todos, y por 3.2.5 borra también todos sus partidos. Si un admin elimina a un usuario problemático, se lleva por delante a equipos enteros.
- **Fix:** usar `ON DELETE RESTRICT` y obligar a transferir la capitanía antes de eliminar; o exponer un endpoint `teams/transferCaptaincy`.

🟡 **3.2.7 — `Equipo::leave` cierra al capitán sin ofrecer salida**
- [Equipo.php:88-96](app/models/Equipo.php#L88-L96) devuelve `false` si el usuario es capitán, y `TeamsController` le pide "transfiere la capitanía o elimina el equipo" — pero no existe ningún endpoint de transferencia de capitanía. UX bloqueada.
- **Fix:** añadir `Equipo::transferCaptaincy(int $teamId, int $fromUserId, int $toUserId)` y un POST en `TeamsController`.

🟡 **3.2.8 — Demo credentials públicas en el README** (aviso revisado)
- [README.md:286-287](README.md#L286-L287) sigue publicando `admin@fastplay.es / Admin1234!`. El seed está protegido por `APP_ENV !== 'production'`, así que en teoría es inofensivo, pero si alguien despliega sin definir `APP_ENV=production`, el atacante tiene admin instantáneamente. La nota del README ya advierte (línea 289); aceptable para portfolio, mantener el ojo puesto.

🟡 **3.2.9 — `public/.htaccess` no bloquea `composer.json`, `package.json`, `Dockerfile`**
- [public/.htaccess:14](public/.htaccess#L14) solo cubre `\.(env|md|log|sqlite|sqlite-journal|ini|sql)$`. Hoy no hay `composer.json` ni `package.json` en `public/` (y el rewrite raíz manda todo a `public/`, así que `Dockerfile` directo cae al 404), pero conviene ampliar la lista preventivamente: `…|json|lock|yml|yaml|dockerfile`.

### 3.3 README y documentación

🔵 **3.3.1 — `Dockerfile` presente pero sin instrucciones en README**
- Existe un [`Dockerfile`](Dockerfile) funcional (php:8.2-apache + pdo_sqlite + mod_rewrite), pero el README solo describe el flujo XAMPP. Añadir una sección "Despliegue con Docker":
  ```bash
  docker build -t fastplay .
  docker run -p 8080:80 -e APP_ENV=production fastplay
  ```

🔵 **3.3.2 — `router.php` para `php -S` no está documentado**
- [`router.php`](router.php) emula el comportamiento del `public/.htaccess` cuando se usa el servidor embebido. No se menciona en el README. Útil para devs sin XAMPP:
  ```bash
  php -S localhost:8000 router.php
  ```

🔵 **3.3.3 — `fileinfo` listado como requisito sin uso**
- [README.md:249](README.md#L249) pide la extensión `fileinfo` "para validar uploads", pero no hay flujo de uploads (el propio README lo reconoce en línea 305). Quitar o reetiquetar como "requerida cuando se implemente la subida de avatares".

🔵 **3.3.4 — `LegalController::cookies` y `privacy` no pasan `$lastUpdated`**
- [LegalController.php:17-31](app/controllers/LegalController.php#L17-L31) solo pasa la fecha a `terms`. Inconsistente: las tres páginas legales deberían publicar fecha de última actualización (es lo que una autoridad de protección de datos esperaría ver para GDPR/cookies).

---

## 4. Prioridad sugerida

1. **Sprint 1 — Seguridad / repo:** 3.1.1, 3.1.3, 3.2.1, 3.2.2, 3.2.5, 3.2.6.
2. **Sprint 2 — UX y limpieza visible:** 2.1, 2.2, 2.5, 2.6, 3.2.7, 3.3.1, 3.3.2.
3. **Sprint 3 — Coherencia y pulido:** 2.3, 2.8, 2.10, 2.11, 3.1.4, 3.1.5, 3.1.6, 3.2.3, 3.2.4, 3.2.8, 3.2.9, 3.3.3, 3.3.4.
4. **Backlog opcional / refactor:** 2.4, 2.7, 2.9, 3.1.2 (purga histórica, solo si el repo se hace público).

---

_Generado tras una re-auditoría manual de `/`, `/app`, `/config`, `/public`, `/storage`, `/uploads`, `/php`, `Dockerfile`, `router.php` y `README.md`. Para reproducir, comparar este documento con `git log -- arreglos.md` para ver el delta respecto a la versión anterior._
