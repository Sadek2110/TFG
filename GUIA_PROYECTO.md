# Guia completa de FastPlay

Esta guia explica como esta montado FastPlay, que funcionalidades tiene, como fluye una peticion por el codigo y donde tocar cuando quieras cambiar o ampliar algo.

## 1. Que es FastPlay

FastPlay es una aplicacion web en PHP para organizar futbol amateur:

- jugadores y usuarios
- equipos y capitanes
- ligas
- partidos
- campos
- chat
- perfil de usuario
- panel de administracion
- paginas legales

El proyecto esta hecho con PHP puro, sin framework externo. Usa una arquitectura MVC sencilla:

```text
Navegador
  -> public/index.php
  -> Router
  -> Controller
  -> Model
  -> Database
  -> View
  -> Layout
```

La base de datos principal es SQLite y se crea automaticamente en `storage/fastplay.sqlite`.

## 2. Stack del proyecto

| Parte | Tecnologia | Donde esta |
|---|---|---|
| Backend | PHP 8.x | `app/` |
| Base de datos | SQLite por defecto | `storage/fastplay.sqlite` |
| Acceso a datos | PDO | `app/core/Database.php` |
| Frontend | HTML, CSS y JS vanilla | `app/views/`, `public/css/`, `public/js/` |
| Servidor Apache | `.htaccess` | raiz, `public/`, carpetas protegidas |
| Tests | PHPUnit | `tests/`, `phpunit.xml` |
| SQL auxiliar | PostgreSQL/MySQL opcional | `database/` |

## 3. Estructura principal

```text
app/
  controllers/   Controladores: reciben peticiones y deciden que hacer.
  core/          Router, Controller base y Database.
  models/        Logica de negocio y consultas SQL.
  views/         Plantillas PHP/HTML.

config/
  config.php     Constantes, sesiones, seguridad, helpers globales.

public/
  index.php      Front controller.
  css/           Estilos.
  js/            JavaScript.
  images/        Imagenes.
  video/         Video de landing.

storage/
  fastplay.sqlite  Base de datos local.
  sessions/        Sesiones PHP locales.

tests/
  Pruebas PHPUnit.

database/
  Scripts SQL auxiliares para otros motores.
```

## 4. Como arranca la aplicacion

El punto de entrada real es `public/index.php`.

Ese archivo hace cuatro cosas importantes:

1. Carga `config/config.php`.
2. Carga las clases base: `Database`, `Router`, `Controller`.
3. Inicializa la base de datos llamando a `Database::pdo()`.
4. Despacha la URL con `Router::dispatch($url)`.

Cuando se usa el servidor embebido de PHP, entra antes por `router.php`, que sirve assets estaticos y manda el resto a `public/index.php`.

## 5. Configuracion global

Archivo principal: `config/config.php`.

Define:

- `APP_ROOT`: raiz del proyecto.
- `APP_PATH`: carpeta `app`.
- `STORAGE_PATH`: carpeta `storage`.
- `SESSIONS_PATH`: carpeta `storage/sessions`.
- `UPLOADS_PATH`: carpeta `uploads`.
- `APP_ENV`: entorno, por defecto `development`.
- `DB_DSN`: conexion SQLite.
- `BASE_URL`: ruta base detectada automaticamente.
- `ASSETS_URL`: ruta para assets.

Tambien contiene helpers globales:

- `e($value)`: escapa HTML para evitar XSS.
- `url($path)`: genera URLs internas.
- `asset($path)`: genera URLs a assets con version por `filemtime`.
- `redirect($path)`: redirige.
- `is_auth()`: indica si hay usuario logueado.
- `current_user()`: devuelve el usuario de sesion.
- `is_admin()`: comprueba si el usuario es admin.
- `login_user($user)`: guarda usuario en sesion.
- `logout_user()`: cierra sesion.
- `csrf_token()`, `csrf_field()`, `verify_csrf()`, `require_csrf()`.
- `flash()`, `flash_pull()`: mensajes temporales.
- `old()`, `flash_old()`, `old_clear()`: recordar datos de formularios.
- validadores: `v_required`, `v_email`, `v_min_len`, `v_int_range`.

## 6. Seguridad

FastPlay tiene varias defensas:

- Sesiones con cookie `HttpOnly`, `SameSite=Lax` y `Secure` si hay HTTPS.
- `session.use_strict_mode=1`.
- Sesiones guardadas en `storage/sessions/` para evitar problemas con `C:\xampp\tmp`.
- Regeneracion de ID tras login.
- CSRF obligatorio en formularios `POST`.
- Passwords con `password_hash()` y `password_verify()`.
- Rate limit para login.
- Consultas SQL preparadas con PDO.
- Escape de salida con `e()`.
- Cabeceras de seguridad:
  - `X-Frame-Options`
  - `X-Content-Type-Options`
  - `Referrer-Policy`
  - `Permissions-Policy`
  - `Content-Security-Policy`

## 7. Router

Archivo: `app/core/Router.php`.

El router convierte una URL en controlador, accion y parametros.

Ejemplos:

```text
/teams
  -> TeamsController@index

/teams/show/3
  -> TeamsController@show("3")

/matches/confirm/8
  -> MatchesController@confirm("8")
```

Reglas:

- Si no hay URL, usa `home/index`.
- El controlador sale del primer segmento.
- La accion sale del segundo segmento.
- El resto son parametros.
- Solo permite letras, numeros y guion bajo.
- No permite llamar metodos privados, protegidos, estaticos ni metodos heredados directamente de `Controller`.

Si no encuentra algo, renderiza 404.
Si hay excepcion, renderiza 500.

## 8. Controller base

Archivo: `app/core/Controller.php`.

Todos los controladores heredan de esta clase.

Metodos importantes:

- `view($view, $data, $layout)`: renderiza una vista dentro de un layout.
- `model($modelName)`: carga un modelo.
- `partial($name, $data)`: carga un partial.
- `requireAuth()`: exige login.
- `requireGuest()`: exige no estar logueado.
- `requireAdmin()`: exige admin.
- `requirePost()`: exige metodo POST y CSRF valido.
- `back($fallback)`: vuelve al referer si es seguro.

El metodo `view()` hace esto:

1. Busca `app/views/{view}.php`.
2. Busca `app/views/layouts/{layout}.php`.
3. Anade `_user`, `_flash`, `active` y `title` al array de datos.
4. Ejecuta la vista en buffer.
5. Mete el resultado en `$content`.
6. Renderiza el layout.
7. Limpia los datos antiguos de formulario.

## 9. Database

Archivo: `app/core/Database.php`.

Es un wrapper sobre PDO.

Metodos principales:

- `Database::pdo()`: crea o devuelve la conexion.
- `Database::run($sql, $params)`: prepara y ejecuta SQL.
- `Database::one($sql, $params)`: devuelve una fila.
- `Database::all($sql, $params)`: devuelve varias filas.
- `Database::value($sql, $params)`: devuelve un valor.
- `Database::insertId()`: ultimo ID insertado.

Cuando se abre la conexion, se ejecuta:

1. `PRAGMA foreign_keys = ON`.
2. `migrate($pdo)`.
3. `seed($pdo)` si toca.
4. `repairConsistency($pdo)`.

### Migraciones

La migracion crea todas las tablas si no existen:

- `users`
- `teams`
- `team_members`
- `leagues`
- `league_teams`
- `fields`
- `matches`
- `chat_rooms`
- `chat_messages`
- `achievements`
- `user_achievements`
- `login_attempts`

### Seeder

En `development`, si no hay usuarios, crea datos demo:

- admin
- jugador demo
- jugadores extra
- equipos
- ligas
- inscripciones
- campos
- partidos
- logros
- salas de chat
- mensajes iniciales

Credenciales demo:

```text
admin@fastplay.es / Admin1234!
demo@fastplay.es  / Demo1234!
```

En `production`, el seeder no crea datos demo.

### Reparacion de consistencia

`repairConsistency()` evita una incoherencia historica: partidos asociados a una liga donde alguno de los equipos no estaba inscrito en esa liga.

La reparacion hace `INSERT OR IGNORE` en `league_teams` para los equipos local y visitante de cada partido con `league_id`.

## 10. Modelos

Los modelos viven en `app/models/`.

### Usuario

Archivo: `app/models/Usuario.php`.

Gestiona:

- login
- rate limit
- registro
- busqueda de usuario
- actualizacion de perfil
- cambio de password
- estadisticas de dashboard
- logros
- listado admin
- cambio de rol
- eliminacion

Detalles importantes:

- El email se normaliza a minusculas.
- El login registra intentos en `login_attempts`.
- El rate limit bloquea tras 5 fallos recientes.
- Las ventanas temporales usan UTC con `gmdate()` para casar con SQLite.

### Equipo

Archivo: `app/models/Equipo.php`.

Gestiona:

- listado de equipos
- detalle de equipo
- miembros
- equipos de un usuario
- equipo principal del usuario
- creacion de equipo
- unirse
- salir
- eliminar
- bloqueos de eliminacion
- comprobar si alguien es capitan

Reglas:

- Al crear equipo, el capitan entra automaticamente como miembro.
- Un capitan no puede salir de su propio equipo.
- No se puede eliminar un equipo con partidos asociados.
- No se puede eliminar un equipo inscrito en una liga activa.

### Liga

Archivo: `app/models/Liga.php`.

Gestiona:

- listado
- detalle
- clasificacion
- comprobar inscripcion de equipo
- registrar equipo
- crear liga
- eliminar liga
- estadisticas de landing

Reglas:

- Solo ligas con estado `open` admiten inscripciones.
- Respeta `max_teams`.
- Evita inscribir dos veces el mismo equipo.
- Ordena clasificacion por puntos, diferencia de goles, goles a favor y nombre.

### Partido

Archivo: `app/models/Partido.php`.

Gestiona:

- listado
- detalle
- proximos partidos
- creacion
- cambio de estado
- eliminacion
- permisos de eliminacion
- normalizacion para tarjetas visuales

Estados:

- `pending`
- `confirmed`
- `cancelled`
- `finished`

Transiciones:

- `pending -> confirmed`
- `pending/confirmed -> cancelled`
- `confirmed -> finished`

Reglas:

- Equipo local y visitante deben ser distintos.
- La fecha debe ser futura al crear.
- Si se asocia a liga, ambos equipos deben estar inscritos.
- Solo admin o capitan de uno de los equipos puede gestionar el partido.

### Campo

Archivo: `app/models/Campo.php`.

Gestiona:

- listado
- detalle
- creacion
- eliminacion

Superficies validas:

- `cesped`
- `sintetico`
- `tierra`
- `cemento`

Nota: en la base y vistas aparecen textos con acentos, pero conceptualmente esas son las opciones.

### Chat

Archivo: `app/models/Chat.php`.

Gestiona:

- salas
- detalle de sala
- mensajes
- envio de mensajes
- creacion de salas
- permisos de acceso

Reglas:

- Admin puede acceder a todo.
- Salas `match_negotiation` solo para capitanes o admin.
- Mensajes vacios no se guardan.
- Mensajes maximo 800 caracteres.

## 11. Controladores

Los controladores viven en `app/controllers/`.

### HomeController

Rutas:

- `/`
- errores 404
- errores 500

Usa `Liga` para mostrar ligas destacadas y estadisticas de la landing.

### AuthController

Rutas:

- `/auth/login`
- `/auth/register`
- `/auth/logout`

Funcionalidades:

- Login con CSRF.
- Registro con validacion.
- Cierre de sesion solo por POST.
- Redirige al dashboard tras login/registro.

### DashboardController

Ruta:

- `/dashboard`

Requiere login.

Muestra:

- estadisticas del usuario
- logros
- proximos partidos
- equipo principal

### ProfileController

Rutas:

- `/profile`
- `/profile/edit`
- `/profile/password`

Requiere login.

Permite:

- ver perfil
- editar perfil
- cambiar password

### TeamsController

Rutas:

- `/teams`
- `/teams/show/{id}`
- `/teams/create`
- `/teams/join/{id}`
- `/teams/leave/{id}`
- `/teams/delete/{id}`

Permite:

- listar equipos
- ver equipo
- crear equipo
- unirse
- salir
- borrar si eres capitan o admin

### LeaguesController

Rutas:

- `/leagues`
- `/leagues/show/{id}`
- `/leagues/register/{id}`
- `/leagues/create`

Permite:

- listar ligas
- ver liga y clasificacion
- inscribir equipos si eres capitan o admin
- crear liga si eres admin

### MatchesController

Rutas:

- `/matches`
- `/matches/show/{id}`
- `/matches/create`
- `/matches/confirm/{id}`
- `/matches/cancel/{id}`
- `/matches/finish/{id}`
- `/matches/delete/{id}`

Permite:

- listar partidos
- ver partido
- crear partido
- confirmar
- cancelar
- finalizar con marcador
- eliminar

### CamposController

Rutas:

- `/campos`
- `/campos/show/{id}`
- `/campos/create`

Permite:

- listar campos
- ver campo
- crear campo si eres admin

### ChatController

Rutas:

- `/chat`
- `/chat/room/{id}`
- `/chat/send/{id}`
- `/chat/messages/{id}`
- `/chat/createRoom`

Permite:

- listar salas disponibles
- entrar en sala
- enviar mensaje
- obtener mensajes por JSON para polling
- crear salas si eres admin

### AdminController

Rutas:

- `/admin`
- `/admin/users`
- `/admin/setRole/{id}`
- `/admin/deleteUser/{id}`
- `/admin/teams`
- `/admin/leagues`
- `/admin/fields`
- `/admin/deleteLeague/{id}`
- `/admin/deleteField/{id}`

Permite:

- ver metricas del sistema
- gestionar usuarios
- cambiar roles
- borrar usuarios con protecciones
- ver equipos, ligas y campos
- borrar ligas/campos con protecciones

Protecciones:

- No puedes degradar al ultimo admin.
- No puedes eliminar al ultimo admin.
- No puedes eliminar tu propia cuenta desde admin.
- No puedes eliminar usuario que capitanea equipos.
- No puedes borrar liga/campo con partidos finalizados asociados.

### LegalController

Rutas:

- `/legal/terms`
- `/legal/privacy`
- `/legal/cookies`

Solo renderiza paginas legales.

## 12. Referencia tecnica de archivos

Esta seccion resume para que sirve cada bloque del proyecto y que archivos suelen tocarse juntos.

### Nucleo de ejecucion

| Archivo | Responsabilidad | Cuando tocarlo |
|---|---|---|
| `public/index.php` | Front controller real. Carga configuracion, clases base, inicializa BD y despacha la ruta. | Casi nunca. Solo si cambia el bootstrap global. |
| `router.php` | Router para `php -S`; sirve assets desde `public/` y reenvia rutas dinamicas. | Si cambias como se prueban rutas con servidor embebido. |
| `.htaccess` | Reescritura desde raiz hacia `public/`. | Si cambia el despliegue Apache. |
| `public/.htaccess` | Front controller para Apache dentro de `public/`. | Si cambias reglas de URL o acceso a assets. |
| `config/config.php` | Constantes, sesiones, headers, helpers, auth, CSRF, flash, old input y validadores. | Si cambia seguridad, entorno, helpers o rutas base. |

### Core MVC

| Archivo | Responsabilidad | Detalle clave |
|---|---|---|
| `app/core/Router.php` | Convierte URL en `Controller@action`. | Usa reflection para bloquear metodos no publicos, estaticos o heredados del controller base. |
| `app/core/Controller.php` | Base de controladores. | Renderiza vistas, carga modelos, protege rutas por auth/admin/post. |
| `app/core/Database.php` | PDO, migraciones, seed y reparaciones. | Crea tablas SQLite, datos demo y corrige incoherencias de liga/partidos. |

### Controladores

| Archivo | Modulo | Responsabilidad |
|---|---|---|
| `HomeController.php` | Home/errores | Landing, 404 y 500. |
| `AuthController.php` | Auth | Login, registro y logout. |
| `DashboardController.php` | Usuario privado | Panel inicial tras login. |
| `ProfileController.php` | Perfil | Ver/editar perfil y cambiar password. |
| `TeamsController.php` | Equipos | Listar, crear, ver, unirse, salir y borrar equipos. |
| `LeaguesController.php` | Ligas | Listar, crear y registrar equipos en ligas. |
| `MatchesController.php` | Partidos | Crear, ver, confirmar, cancelar, finalizar y borrar partidos. |
| `CamposController.php` | Campos | Listar, crear y ver campos. |
| `ChatController.php` | Chat | Salas, mensajes, envio y endpoint JSON de polling. |
| `AdminController.php` | Admin | Dashboard admin, usuarios, roles, equipos, ligas y campos. |
| `LegalController.php` | Legal | Terminos, privacidad y cookies. |

### Modelos

| Archivo | Tabla(s) principales | Responsabilidad |
|---|---|---|
| `Usuario.php` | `users`, `login_attempts`, `user_achievements` | Auth, registro, perfil, roles, stats, logros y rate limit. |
| `Equipo.php` | `teams`, `team_members` | Equipos, miembros, capitanes y bloqueos de borrado. |
| `Liga.php` | `leagues`, `league_teams` | Ligas, clasificaciones, inscripciones y stats de landing. |
| `Partido.php` | `matches` | Partidos, estados, permisos y normalizacion visual. |
| `Campo.php` | `fields` | Campos y validacion de superficies/capacidad/tarifa. |
| `Chat.php` | `chat_rooms`, `chat_messages` | Salas, permisos de acceso y mensajes. |

### Vistas

| Carpeta | Que contiene |
|---|---|
| `app/views/layouts/` | Layouts `main` y `auth`. |
| `app/views/partials/` | Navbar, footer y mensajes flash. |
| `app/views/home/` | Landing publica. |
| `app/views/auth/` | Login y registro. |
| `app/views/dashboard/` | Panel de usuario. |
| `app/views/profile/` | Perfil, edicion y password. |
| `app/views/teams/` | Listado, detalle y creacion de equipos. |
| `app/views/leagues/` | Listado, detalle/clasificacion y creacion de ligas. |
| `app/views/matches/` | Listado, detalle y creacion de partidos. |
| `app/views/campos/` | Listado, detalle y creacion de campos. |
| `app/views/chat/` | Listado de salas y sala concreta. |
| `app/views/admin/` | Panel admin y tablas administrativas. |
| `app/views/legal/` | Terminos, privacidad y cookies. |
| `app/views/errors/` | 403, 404 y 500. |

### Frontend publico

| Archivo/carpeta | Responsabilidad |
|---|---|
| `public/css/app.css` | Design system, layout general, botones, inputs, cards, tablas, estados. |
| `public/css/scroll-anim.css` | Estilos especificos de landing inmersiva. |
| `public/js/scroll-anim.js` | Control de scroll, video, progreso y secciones visibles. |
| `public/js/home-init.js` | Navbar al hacer scroll, contadores y activacion de landing. |
| `public/images/` | Logos, poster, imagenes auxiliares. |
| `public/video/hero.webm` | Video principal de landing. |

### Tests y tooling

| Archivo/carpeta | Responsabilidad |
|---|---|
| `composer.json` | Dependencias PHP y autoload de clases. |
| `composer.lock` | Versiones fijadas de dependencias. |
| `phpunit.xml` | Configuracion de PHPUnit. |
| `tests/bootstrap.php` | Entorno aislado de pruebas con SQLite de test. |
| `tests/` | Tests de helpers, router y modelos. |

### SQL auxiliar

| Archivo | Uso |
|---|---|
| `database/fastplay_postgres.sql` | Port opcional del esquema/seed para PostgreSQL. No es runtime activo. |
| `database/fastplay_mysql.sql` | Port opcional del esquema/seed para MySQL. No es runtime activo. |

## 13. Mapa tecnico de rutas y endpoints

El proyecto no tiene una API REST separada. Los endpoints son rutas web MVC. Algunas devuelven HTML, una devuelve JSON (`/chat/messages/{id}`), y la mayoria de acciones que modifican datos son `POST` con CSRF.

### Convencion del router

```text
/{controller}/{action}/{param1}/{param2}
```

Ejemplos:

```text
/teams/show/4
  controller = teams
  action = show
  params = ["4"]
  clase = TeamsController
  metodo = show("4")
```

Si la URL es `/teams`, el router asume:

```text
/teams/index
```

Si la URL es `/`, el router asume:

```text
/home/index
```

### Endpoints publicos

| Metodo | Ruta | Controlador | Respuesta | Permiso | Notas |
|---|---|---|---|---|---|
| `GET` | `/` | `HomeController@index` | HTML | Publico | Landing, ligas destacadas y stats. |
| `GET` | `/auth/login` | `AuthController@login` | HTML | Solo invitado | Formulario login. |
| `POST` | `/auth/login` | `AuthController@login` | Redirect/HTML | Solo invitado + CSRF | Login. Campos: `email`, `password`, `_csrf`. |
| `GET` | `/auth/register` | `AuthController@register` | HTML | Solo invitado | Formulario registro. |
| `POST` | `/auth/register` | `AuthController@register` | Redirect/HTML | Solo invitado + CSRF | Registro. Campos: `name`, `email`, `phone`, `age`, `city`, `position`, `password`, `password_confirm`, `_csrf`. |
| `GET` | `/leagues` | `LeaguesController@index` | HTML | Publico | Listado de ligas. |
| `GET` | `/leagues/show/{id}` | `LeaguesController@show` | HTML | Publico | Detalle y clasificacion. Si hay login, muestra equipos propios para inscribir. |
| `GET` | `/teams` | `TeamsController@index` | HTML | Publico | Listado de equipos. |
| `GET` | `/teams/show/{id}` | `TeamsController@show` | HTML | Publico | Detalle, miembros y botones segun usuario. |
| `GET` | `/matches` | `MatchesController@index` | HTML | Publico | Listado de partidos. |
| `GET` | `/matches/show/{id}` | `MatchesController@show` | HTML | Publico | Detalle; acciones solo si manager. |
| `GET` | `/campos` | `CamposController@index` | HTML | Publico | Listado de campos. |
| `GET` | `/campos/show/{id}` | `CamposController@show` | HTML | Publico | Detalle de campo. |
| `GET` | `/legal/terms` | `LegalController@terms` | HTML | Publico | Terminos. |
| `GET` | `/legal/privacy` | `LegalController@privacy` | HTML | Publico | Privacidad. |
| `GET` | `/legal/cookies` | `LegalController@cookies` | HTML | Publico | Cookies. |

### Endpoints autenticados

| Metodo | Ruta | Controlador | Respuesta | Permiso | Campos/params |
|---|---|---|---|---|---|
| `POST` | `/auth/logout` | `AuthController@logout` | Redirect | Login + CSRF | `_csrf`. |
| `GET` | `/dashboard` | `DashboardController@index` | HTML | Login | Muestra stats, logros, proximos partidos y equipo. |
| `GET` | `/profile` | `ProfileController@index` | HTML | Login | Perfil propio. |
| `GET` | `/profile/edit` | `ProfileController@edit` | HTML | Login | Formulario de perfil. |
| `POST` | `/profile/edit` | `ProfileController@edit` | Redirect/HTML | Login + CSRF | `name`, `age`, `phone`, `city`, `position`, `_csrf`. |
| `GET` | `/profile/password` | `ProfileController@password` | HTML | Login | Formulario password. |
| `POST` | `/profile/password` | `ProfileController@password` | Redirect/HTML | Login + CSRF | `current`, `new`, `confirm`, `_csrf`. |
| `GET` | `/teams/create` | `TeamsController@create` | HTML | Login | Formulario de equipo. |
| `POST` | `/teams/create` | `TeamsController@create` | Redirect/HTML | Login + CSRF | `name`, `city`, `badge`, `_csrf`. |
| `POST` | `/teams/join/{id}` | `TeamsController@join` | Redirect | Login + CSRF | `id` de equipo. |
| `POST` | `/teams/leave/{id}` | `TeamsController@leave` | Redirect | Login + CSRF | No permite salir si eres capitan. |
| `POST` | `/teams/delete/{id}` | `TeamsController@delete` | Redirect | Capitan o admin + CSRF | Bloquea si hay partidos o liga activa. |
| `POST` | `/leagues/register/{id}` | `LeaguesController@register` | Redirect | Capitan del equipo o admin + CSRF | `team_id`, `_csrf`. |
| `GET` | `/matches/create` | `MatchesController@create` | HTML | Login | Formulario partido. |
| `POST` | `/matches/create` | `MatchesController@create` | Redirect/HTML | Capitan de un equipo implicado o admin + CSRF | `home_team_id`, `away_team_id`, `field_id`, `league_id`, `scheduled_at`, `_csrf`. |
| `POST` | `/matches/confirm/{id}` | `MatchesController@confirm` | Redirect | Manager del partido o admin + CSRF | Cambia `pending` a `confirmed`. |
| `POST` | `/matches/cancel/{id}` | `MatchesController@cancel` | Redirect | Manager del partido o admin + CSRF | Cambia a `cancelled` si no esta terminado/cancelado. |
| `POST` | `/matches/finish/{id}` | `MatchesController@finish` | Redirect | Manager del partido o admin + CSRF | `home_score`, `away_score`, `_csrf`; requiere estado `confirmed`. |
| `POST` | `/matches/delete/{id}` | `MatchesController@delete` | Redirect | Manager del partido o admin + CSRF | Borra partido. |
| `GET` | `/chat` | `ChatController@index` | HTML | Login | Lista salas accesibles. |
| `GET` | `/chat/room/{id}` | `ChatController@room` | HTML | Login + permiso de sala | Sala y mensajes. |
| `POST` | `/chat/send/{id}` | `ChatController@send` | Redirect | Login + permiso de sala + CSRF | `body`, `_csrf`; max 800 chars. |
| `GET` | `/chat/messages/{id}` | `ChatController@messages` | JSON | Login + permiso de sala | Polling de mensajes. Devuelve `id`, `user_name`, `body`, `created_at`, `own`. |

### Endpoints admin

| Metodo | Ruta | Controlador | Respuesta | Permiso | Campos/params |
|---|---|---|---|---|---|
| `GET` | `/admin` | `AdminController@index` | HTML | Admin | Metricas y ultimos intentos login. |
| `GET` | `/admin/users` | `AdminController@users` | HTML | Admin | Tabla usuarios. |
| `POST` | `/admin/setRole/{id}` | `AdminController@setRole` | Redirect | Admin + CSRF | `role`; evita degradar ultimo admin. |
| `POST` | `/admin/deleteUser/{id}` | `AdminController@deleteUser` | Redirect | Admin + CSRF | Evita borrar propia cuenta, ultimo admin o capitan. |
| `GET` | `/admin/teams` | `AdminController@teams` | HTML | Admin | Tabla equipos. |
| `GET` | `/admin/leagues` | `AdminController@leagues` | HTML | Admin | Tabla ligas. |
| `GET` | `/admin/fields` | `AdminController@fields` | HTML | Admin | Tabla campos. |
| `POST` | `/admin/deleteLeague/{id}` | `AdminController@deleteLeague` | Redirect | Admin + CSRF | Bloquea si hay partidos finalizados. |
| `POST` | `/admin/deleteField/{id}` | `AdminController@deleteField` | Redirect | Admin + CSRF | Bloquea si hay partidos finalizados. |
| `GET` | `/leagues/create` | `LeaguesController@create` | HTML | Admin | Formulario liga. |
| `POST` | `/leagues/create` | `LeaguesController@create` | Redirect/HTML | Admin + CSRF | `name`, `city`, `pro`, `prize`, `start_date`, `end_date`, `max_teams`, `_csrf`. |
| `GET` | `/campos/create` | `CamposController@create` | HTML | Admin | Formulario campo. |
| `POST` | `/campos/create` | `CamposController@create` | Redirect/HTML | Admin + CSRF | `name`, `city`, `address`, `surface`, `capacity`, `hourly_rate`, `_csrf`. |
| `POST` | `/chat/createRoom` | `ChatController@createRoom` | Redirect | Admin + CSRF | `name`, `type`, `_csrf`. |

### Endpoints especiales de error

No se acceden normalmente como URL directa. Los usa el router:

| Caso | Metodo interno | Vista |
|---|---|---|
| Ruta inexistente | `Router::notFound()` | `app/views/errors/404.php` |
| Excepcion/fatal controlado | `Router::serverError($e)` | `app/views/errors/500.php` |
| Acceso no permitido | `Controller::requireAdmin()` | `app/views/errors/403.php` |

### Respuesta JSON de chat

`GET /chat/messages/{id}` devuelve una lista JSON parecida a:

```json
[
  {
    "id": 12,
    "user_name": "Jugador Demo",
    "body": "Buscamos rival este finde",
    "created_at": "18/05 19:30",
    "own": true
  }
]
```

Este endpoint es usado por `app/views/chat/room.php` para refrescar mensajes sin recargar toda la pagina.

### Reglas comunes de endpoints POST

Todo endpoint `POST` debe cumplir:

1. El formulario debe incluir `<?= csrf_field() ?>`.
2. El controlador debe llamar a `require_csrf()` o `$this->requirePost()`.
3. Si falla validacion, debe usar `flash()` para mensaje y normalmente `flash_old($_POST)`.
4. Si modifica datos correctamente, normalmente redirige con `redirect()`.

## 14. Vistas y layouts

Las vistas estan en `app/views/`.

Layouts:

- `layouts/main.php`: layout general.
- `layouts/auth.php`: layout para login y registro.

Partials:

- `partials/navbar.php`
- `partials/footer.php`
- `partials/flash.php`

Cada vista recibe variables desde el controlador. Ejemplo:

```php
$this->view('teams/show', [
    'team' => $team,
    'members' => $members,
]);
```

Entonces la vista puede usar `$team` y `$members`.

Importante:

- Escapar texto con `e()`.
- Usar `url()` para enlaces internos.
- Usar `asset()` para CSS, JS, imagenes y video.
- Usar `csrf_field()` en todo formulario POST.

## 15. Frontend

CSS:

- `public/css/app.css`: estilos globales y componentes.
- `public/css/scroll-anim.css`: landing inmersiva.

JS:

- `public/js/scroll-anim.js`: logica de scroll/video.
- `public/js/home-init.js`: inicializacion de landing, navbar y contadores.

Assets:

- `public/images/`
- `public/video/hero.webm`

La landing usa un video de fondo con scroll y secciones superpuestas.

## 16. Base de datos

Tablas principales:

### users

Usuarios registrados.

Campos clave:

- `id`
- `name`
- `email`
- `password_hash`
- `role`
- `phone`
- `age`
- `city`
- `position`
- `avatar`
- `created_at`

Roles:

- `player`
- `admin`

### teams

Equipos.

Campos clave:

- `id`
- `name`
- `city`
- `badge`
- `captain_id`

### team_members

Relacion N:M entre usuarios y equipos.

Clave primaria:

- `team_id`
- `user_id`

### leagues

Ligas.

Campos clave:

- `id`
- `name`
- `city`
- `pro`
- `prize`
- `start_date`
- `end_date`
- `max_teams`
- `status`

### league_teams

Equipos inscritos en ligas y clasificacion.

Campos:

- `points`
- `played`
- `won`
- `drawn`
- `lost`
- `gf`
- `ga`

### fields

Campos de juego.

Campos:

- `name`
- `city`
- `address`
- `surface`
- `capacity`
- `hourly_rate`

### matches

Partidos.

Campos:

- `home_team_id`
- `away_team_id`
- `league_id`
- `field_id`
- `scheduled_at`
- `status`
- `home_score`
- `away_score`
- `created_by`

### chat_rooms y chat_messages

Salas y mensajes.

### achievements y user_achievements

Logros disponibles y logros ganados por usuario.

### login_attempts

Intentos de login para rate limit.

## 17. Funcionalidades por tipo de usuario

### Visitante

Puede:

- ver landing
- ver ligas
- ver equipos
- ver partidos
- ver campos
- registrarse
- iniciar sesion

### Usuario logueado

Puede:

- entrar al dashboard
- editar perfil
- cambiar password
- crear equipo
- unirse a equipo
- salir de equipo si no es capitan
- crear partido si pertenece a un equipo
- usar chat

### Capitan

Puede:

- gestionar su equipo
- inscribir equipo en liga
- crear partidos como capitan
- confirmar/cancelar/finalizar partidos de su equipo
- entrar en sala de negociacion de partidos

### Admin

Puede:

- entrar al panel admin
- crear ligas
- crear campos
- cambiar roles
- eliminar usuarios con restricciones
- ver equipos, ligas y campos desde admin
- borrar ligas/campos con restricciones
- gestionar partidos
- acceder a todas las salas de chat

## 18. Tests

El proyecto usa PHPUnit.

Comandos:

```bash
composer install
vendor/bin/phpunit --configuration phpunit.xml
```

La suite usa:

- `tests/bootstrap.php`
- `storage/fastplay_test.sqlite`

No toca la base demo normal `storage/fastplay.sqlite`.

Tests actuales:

- helpers y configuracion
- router
- modelos:
  - Usuario
  - Equipo
  - Liga
  - Partido
  - Campo
  - Chat
- consistencia de base de datos

La ultima verificacion dio:

```text
OK (140 tests, 225 assertions)
```

## 19. Como crear una nueva pantalla

Ejemplo: crear una seccion `noticias`.

1. Crear controlador:

```text
app/controllers/NoticiasController.php
```

2. Crear clase:

```php
<?php

class NoticiasController extends Controller
{
    public function index(): void
    {
        $this->view('noticias/index', [
            'active' => 'noticias',
            'title' => 'Noticias - FastPlay',
        ]);
    }
}
```

3. Crear vista:

```text
app/views/noticias/index.php
```

4. Entrar por:

```text
/noticias
```

El router la resolvera como:

```text
NoticiasController@index
```

## 20. Como crear un nuevo modelo

Ejemplo: `Noticia`.

1. Crear:

```text
app/models/Noticia.php
```

2. Implementar:

```php
<?php

class Noticia
{
    public function all(): array
    {
        return Database::all('SELECT * FROM news ORDER BY created_at DESC');
    }
}
```

3. Usarlo desde un controlador:

```php
$noticia = $this->model('Noticia');
$items = $noticia->all();
```

## 21. Como crear una tabla nueva

El esquema SQLite vive dentro de `Database::migrate()`.

Pasos:

1. Anadir `CREATE TABLE IF NOT EXISTS ...`.
2. Crear indices necesarios.
3. Si necesita datos demo, anadirlos en `seed()`.
4. Si hay datos historicos que arreglar, crear una reparacion idempotente.
5. Crear tests.

Importante:

- Las migraciones deben poder ejecutarse muchas veces sin romper.
- Usa `CREATE TABLE IF NOT EXISTS`.
- Usa `CREATE INDEX IF NOT EXISTS`.
- Las reparaciones deben ser seguras e idempotentes.

## 22. Como crear un formulario seguro

Todo formulario POST debe tener:

```php
<form method="post" action="<?= url('ruta/accion') ?>">
    <?= csrf_field() ?>
    ...
</form>
```

Y el controlador debe validar:

```php
$this->requirePost();
```

o:

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
}
```

Usa `flash_old($_POST)` si quieres conservar datos tras error.

Usa `old('campo')` en la vista para rellenar valores anteriores.

## 23. Convenciones de codigo

- Controladores: `PascalCaseController`.
- Modelos: `PascalCase`.
- Metodos: `camelCase`.
- Vistas: carpetas en minusculas.
- Rutas: `controlador/accion/param`.
- SQL siempre con prepared statements.
- Texto HTML siempre escapado con `e()`.
- URLs internas con `url()`.
- Assets con `asset()`.
- No poner logica de negocio en vistas.
- No acceder directamente a `$_SESSION['user']` si puedes usar `current_user()`.

## 24. Archivos importantes

| Archivo | Para que sirve |
|---|---|
| `public/index.php` | Entrada principal web |
| `router.php` | Router para servidor embebido de PHP |
| `config/config.php` | Configuracion, sesiones, helpers y seguridad |
| `app/core/Router.php` | Despachador de rutas |
| `app/core/Controller.php` | Base de controladores |
| `app/core/Database.php` | Conexion, migraciones y seed |
| `app/models/Usuario.php` | Auth, perfil y usuarios |
| `app/models/Equipo.php` | Equipos y miembros |
| `app/models/Liga.php` | Ligas y clasificaciones |
| `app/models/Partido.php` | Partidos |
| `app/models/Campo.php` | Campos |
| `app/models/Chat.php` | Chat |
| `app/views/layouts/main.php` | Layout general |
| `public/css/app.css` | Estilo principal |
| `phpunit.xml` | Config de tests |
| `tests/bootstrap.php` | Entorno de pruebas |

## 25. Flujo ejemplo: login

1. Usuario entra a `/auth/login`.
2. Router llama `AuthController@login`.
3. Si es GET, renderiza `app/views/auth/login.php`.
4. Si es POST:
   - valida CSRF
   - carga `Usuario`
   - llama `Usuario::login()`
   - verifica password
   - registra intento
   - si va bien, llama `login_user()`
   - redirige a `/dashboard`
5. Dashboard carga stats y renderiza la vista privada.

## 26. Flujo ejemplo: crear partido

1. Usuario entra a `/matches/create`.
2. `MatchesController@create` exige login.
3. Carga equipos del usuario.
4. Carga todos los equipos, campos y ligas.
5. Si es POST:
   - valida CSRF
   - comprueba que el usuario sea admin o capitan de un equipo implicado
   - llama `Partido::create()`
6. El modelo valida:
   - local existe
   - visitante existe
   - equipos distintos
   - fecha futura
   - campo existente si se indica
   - liga existente si se indica
   - ambos equipos inscritos si hay liga
7. Inserta partido como `pending`.
8. Redirige a `/matches/show/{id}`.

## 27. Flujo ejemplo: inscribir equipo en liga

1. Usuario entra a `/leagues/show/{id}`.
2. Si esta logueado, se cargan sus equipos.
3. Envia POST a `/leagues/register/{id}`.
4. El controlador exige login y POST con CSRF.
5. Comprueba que el usuario sea capitan del equipo o admin.
6. `Liga::register()` valida:
   - liga existe
   - liga esta abierta
   - no esta llena
   - equipo no esta ya inscrito
7. Inserta en `league_teams`.

## 28. Flujo ejemplo: chat

1. Usuario entra a `/chat`.
2. `ChatController@index` exige login.
3. `Chat::rooms()` devuelve salas accesibles.
4. Usuario entra a `/chat/room/{id}`.
5. Se valida acceso.
6. La vista muestra mensajes.
7. El JS consulta `/chat/messages/{id}` periodicamente.
8. El formulario envia POST a `/chat/send/{id}`.

## 29. Errores comunes

### Error de sesiones en XAMPP

Sintoma:

```text
session_start(): Permission denied
Cannot modify header information
```

Solucion ya aplicada:

```php
ini_set('session.save_path', SESSIONS_PATH);
```

Las sesiones van a `storage/sessions/`.

### Login demo no funciona

Comprobar que la base local tiene hashes alineados con:

```text
admin@fastplay.es / Admin1234!
demo@fastplay.es  / Demo1234!
```

Si se borra `storage/fastplay.sqlite` en desarrollo, se regenera con el seeder.

### Partido de liga con equipos no inscritos

Solucion ya aplicada:

```php
Database::repairConsistency()
```

### Composer con rutas con espacios

En este entorno puede fallar `composer dump-autoload` con rutas que contienen espacios. La app no depende de Composer en runtime; Composer se usa para PHPUnit.

## 30. Que esta pendiente o seria una mejora futura

- Subida real de avatares y escudos.
- Sistema completo de invitaciones a equipos.
- Estadisticas avanzadas de jugador.
- Marcador en vivo real.
- PWA.
- Pagos para Liga Pro.
- WebSockets para chat/live scoring.
- Migracion real a PostgreSQL si el proyecto crece.
- Separar helpers de `config/config.php` si el archivo crece demasiado.
- Reducir `unsafe-inline` en CSP moviendo estilos/scripts inline a archivos.

## 31. Resumen mental rapido

Si quieres entender FastPlay rapido:

- `config/config.php` prepara el entorno.
- `public/index.php` arranca la app.
- `Router` decide que controlador ejecutar.
- El controlador valida permisos y recoge datos.
- El modelo consulta o modifica SQLite.
- La vista pinta HTML.
- El layout envuelve la vista.
- Los formularios POST usan CSRF.
- Los tests protegen la logica principal.

Ese es el corazon del proyecto.

