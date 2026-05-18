<div align="center">

# âš½ FastPlay â€” FÃºtbol Amateur Organizado

**_"FÃºtbol callejero, organizado."_**

Plataforma web para la gestiÃ³n de ligas, equipos y partidos de fÃºtbol amateur.

![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-3-003B57?logo=sqlite&logoColor=white)
![Apache](https://img.shields.io/badge/Apache-2.4-D22128?logo=apache&logoColor=white)
![Architecture](https://img.shields.io/badge/Arquitectura-MVC-success)
![Status](https://img.shields.io/badge/estado-v3-16a34a)
![License](https://img.shields.io/badge/licencia-uso%20acadÃ©mico-lightgrey)

</div>

---

> Guia ampliada del proyecto: [`GUIA_PROYECTO.md`](GUIA_PROYECTO.md)

---

## ðŸ“‘ Tabla de Contenidos

1. [DescripciÃ³n](#-descripciÃ³n)
2. [CaracterÃ­sticas Principales](#-caracterÃ­sticas-principales)
3. [Stack TecnolÃ³gico](#ï¸-stack-tecnolÃ³gico)
4. [Arquitectura](#ï¸-arquitectura)
5. [Estructura del Proyecto](#-estructura-del-proyecto)
6. [Modelo de Datos](#-modelo-de-datos)
7. [Roles y Permisos](#-roles-y-permisos)
8. [Mapa de Rutas](#-mapa-de-rutas)
9. [InstalaciÃ³n y ConfiguraciÃ³n](#ï¸-instalaciÃ³n-y-configuraciÃ³n)
10. [Credenciales Demo](#-credenciales-demo)
11. [Seguridad](#-seguridad)
12. [Identidad Visual y DiseÃ±o](#-identidad-visual-y-diseÃ±o)
13. [Convenciones de CÃ³digo](#-convenciones-de-cÃ³digo)
14. [Tests](#-tests)
15. [Roadmap](#ï¸-roadmap)
16. [Notas de VersiÃ³n](#-notas-de-versiÃ³n-v3)
17. [Licencia y CrÃ©ditos](#-licencia-y-crÃ©ditos)

---

## ðŸŽ¯ DescripciÃ³n

**FastPlay** es una aplicaciÃ³n web completa desarrollada en **PHP 8 (MVC)** que permite a jugadores y capitanes organizar partidos, inscribirse en ligas y gestionar sus estadÃ­sticas. EstÃ¡ pensada como un puente entre la pachanga improvisada de barrio y las ligas semi-profesionales: rÃ¡pida de usar, sin fricciÃ³n y con todas las herramientas de gestiÃ³n que un capitÃ¡n necesita.

La plataforma combina una experiencia inmersiva (animaciones de scroll, estÃ©tica _glass-and-neon-green_ inspirada en un estadio nocturno) con un nÃºcleo robusto: router propio, capa de datos PDO, sesiones endurecidas, protecciÃ³n CSRF y migraciones automÃ¡ticas en SQLite.

---

## ðŸš€ CaracterÃ­sticas Principales

### ðŸ† GestiÃ³n de Ligas
Dos niveles de competiciÃ³n claramente diferenciados:
- **Liga Pro** â€” _Tier_ premium con Ã¡rbitros, estadÃ­sticas completas, calendario oficial y premios en metÃ¡lico.
- **Liga Amistosa** â€” _Tier_ gratuito para pachangas, retos rÃ¡pidos y partidos negociados entre capitanes.

### ðŸ“… Partidos y Campos
- Reservas de campos por franja horaria.
- GestiÃ³n de convocatorias (titulares, suplentes, bajas).
- Resultados en vivo y estados de partido: **Pendiente**, **Confirmado**, **En Curso**, **Finalizado**.
- HistÃ³rico de partidos por equipo y por jugador.

### ðŸ‘¥ Equipos y Jugadores
- Perfiles pÃºblicos con avatar, biografÃ­a y estadÃ­sticas (goles, asistencias, tarjetas amarillas/rojas).
- Plantillas con rol (capitÃ¡n, titular, suplente).
- Sistema de invitaciones y solicitudes de ingreso.

### ðŸ’¬ Chat en Vivo
- Salas de chat por equipo y por liga.
- NegociaciÃ³n de partidos amistosos entre capitanes (chat 1:1).

### ðŸŽ–ï¸ Sistema de Logros
GamificaciÃ³n mediante medallas y trofeos por hitos alcanzados (primer gol, hat-trick, asistencia perfecta, ligas ganadas, etc.).

### ðŸ› ï¸ Panel de AdministraciÃ³n
GestiÃ³n total de la plataforma: usuarios, equipos, ligas, campos, sanciones y moderaciÃ³n de chats.

### âœ¨ Experiencia Inmersiva
AnimaciÃ³n de scroll basada en vÃ­deo en la _landing page_, con paralaje y revelado progresivo de bloques.

---

## ðŸ› ï¸ Stack TecnolÃ³gico

| Capa | TecnologÃ­a | Detalles |
|---|---|---|
| **Backend** | PHP 8.x | Arquitectura MVC personalizada, sin _framework_ externo |
| **Base de Datos** | SQLite 3 | Auto-migraciÃ³n + _seeding_ de datos demo |
| **Acceso a Datos** | PDO | _Prepared statements_, _bindings_ tipados |
| **Frontend** | Vanilla CSS 3 + JS | _Design System_ propio, sin dependencias |
| **TipografÃ­a** | Inter (variable) | Cargada localmente |
| **Servidor** | Apache 2.4 | `mod_rewrite` + `.htaccess` por carpeta |
| **Animaciones** | VÃ­deo + JS | Experiencia inmersiva en _scroll_ |

---

## ðŸ—ï¸ Arquitectura

FastPlay sigue un patrÃ³n **MVC clÃ¡sico** con un Ãºnico punto de entrada (_Front Controller_):

```
Browser â”€â”€â–º .htaccess â”€â”€â–º public/index.php â”€â”€â–º Router â”€â”€â–º Controller â”€â”€â–º Model â”€â”€â–º Database
                                                  â”‚            â”‚
                                                  â””â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”´â”€â”€â–º View (PHP + Layout + Partials)
```

- **`public/index.php`** es el Ãºnico archivo accesible desde la web. Todo el resto del proyecto vive fuera del _document root_ lÃ³gico (protegido por `.htaccess`).
- **`Router`** mapea verbos HTTP + rutas a `Controller@accion`.
- **`Controller`** orquesta la peticiÃ³n, valida CSRF y delega en los `Model`s.
- **`Model`** encapsula consultas SQL preparadas y reglas de negocio.
- **`View`** se renderiza dentro de un `layout` con `partials` reutilizables (header, footer, flash messages).

---

## ðŸ“‚ Estructura del Proyecto

```text
FastPlay_v3/
â”œâ”€â”€ app/                          # LÃ³gica de la aplicaciÃ³n (MVC)
â”‚   â”œâ”€â”€ .htaccess                 # Bloquea acceso directo
â”‚   â”œâ”€â”€ controllers/              # Controladores HTTP
â”‚   â”‚   â”œâ”€â”€ AdminController.php       # Panel de administraciÃ³n
â”‚   â”‚   â”œâ”€â”€ AuthController.php        # Login, registro, logout
â”‚   â”‚   â”œâ”€â”€ CamposController.php      # GestiÃ³n de campos
â”‚   â”‚   â”œâ”€â”€ ChatController.php        # MensajerÃ­a
â”‚   â”‚   â”œâ”€â”€ DashboardController.php   # Home autenticado
â”‚   â”‚   â”œâ”€â”€ HomeController.php        # Landing pÃºblica
â”‚   â”‚   â”œâ”€â”€ LeaguesController.php     # Ligas Pro / Amistosa
â”‚   â”‚   â”œâ”€â”€ LegalController.php       # Avisos legales y privacidad
â”‚   â”‚   â”œâ”€â”€ MatchesController.php     # Partidos
â”‚   â”‚   â”œâ”€â”€ ProfileController.php     # Perfil de usuario
â”‚   â”‚   â””â”€â”€ TeamsController.php       # Equipos y plantillas
â”‚   â”œâ”€â”€ core/                     # NÃºcleo del framework
â”‚   â”‚   â”œâ”€â”€ Controller.php            # Controlador base + render()
â”‚   â”‚   â”œâ”€â”€ Database.php              # PDO + migraciones + seeder
â”‚   â”‚   â””â”€â”€ Router.php                # Despachador HTTP
â”‚   â”œâ”€â”€ models/                   # Modelos de dominio
â”‚   â”‚   â”œâ”€â”€ Campo.php
â”‚   â”‚   â”œâ”€â”€ Chat.php
â”‚   â”‚   â”œâ”€â”€ Equipo.php
â”‚   â”‚   â”œâ”€â”€ Liga.php
â”‚   â”‚   â”œâ”€â”€ Partido.php
â”‚   â”‚   â””â”€â”€ Usuario.php
â”‚   â””â”€â”€ views/                    # Vistas (PHP puro)
â”‚       â”œâ”€â”€ layouts/                  # Layouts main / auth
â”‚       â”œâ”€â”€ partials/                 # navbar, flash, footer
â”‚       â”œâ”€â”€ home/                     # Landing
â”‚       â”œâ”€â”€ auth/                     # Login / registro
â”‚       â”œâ”€â”€ dashboard/                # Panel privado
â”‚       â”œâ”€â”€ teams/  leagues/  matches/  campos/  chat/  profile/  admin/  legal/  errors/
â”œâ”€â”€ config/
â”‚   â”œâ”€â”€ .htaccess                 # Bloquea acceso directo
â”‚   â””â”€â”€ config.php                # Constantes globales, BASE_URL, sesiÃ³n, CSP
â”œâ”€â”€ public/                       # ÃšNICO directorio expuesto a la web
â”‚   â”œâ”€â”€ .htaccess                 # Front controller + cabeceras
â”‚   â”œâ”€â”€ css/                      # app.css + scroll-anim.css
â”‚   â”œâ”€â”€ js/                       # scroll-anim.js
â”‚   â”œâ”€â”€ images/                   # Assets grÃ¡ficos (hero-pitch, etc.)
â”‚   â”œâ”€â”€ video/                    # VÃ­deos de la landing
â”‚   â””â”€â”€ index.php                 # Front Controller
├── database/                     # SQL auxiliar de referencia
│   └── fastplay_postgres.sql     # Esquema + seed opcional para PostgreSQL
â”œâ”€â”€ storage/                      # SQLite (ignorado por git)
│   ├── sessions/                 # Sesiones PHP locales (ignorado por git)
â”‚   â””â”€â”€ .htaccess
├── tests/                        # PHPUnit
â”œâ”€â”€ uploads/                      # Archivos subidos por usuarios (futuro)
â”‚   â””â”€â”€ .htaccess                 # Bloquea ejecuciÃ³n de scripts
â”œâ”€â”€ .htaccess                     # Reescritura raÃ­z â†’ public/
â”œâ”€â”€ .gitignore                    # Ignora SQLite, uploads de usuario, IDE
├── composer.json                 # Dependencias de desarrollo y autoload
├── phpunit.xml                   # Configuración de tests
â””â”€â”€ README.md                     # Este archivo
```

---

## ðŸ—„ï¸ Modelo de Datos

Entidades principales y sus relaciones:

| Entidad | DescripciÃ³n | Relaciones clave |
|---|---|---|
| **Usuario** | Jugador, capitÃ¡n o admin. Guarda credenciales, perfil y stats. | `1:N` con Equipo (como miembro), `1:N` con Partido (como participante) |
| **Equipo** | Plantilla con capitÃ¡n y miembros. | `N:1` con Usuario (capitÃ¡n), `N:M` con Liga |
| **Liga** | CompeticiÃ³n Pro o Amistosa. | `1:N` con Equipo (inscripciones), `1:N` con Partido |
| **Partido** | Encuentro entre dos equipos en un campo. | `N:1` con Liga, `N:1` con Campo, `N:M` con Usuario (convocatoria) |
| **Campo** | Recinto fÃ­sico reservable. | `1:N` con Partido |
| **Chat** | Mensajes y salas. | `N:1` con Usuario (autor), agrupados por sala |
| **Logro** | Medallas desbloqueadas por el jugador. | `N:1` con Usuario |

Las migraciones y el _seeding_ inicial se ejecutan automÃ¡ticamente en el primer arranque desde [`app/core/Database.php`](app/core/Database.php).

---

## ðŸ‘¤ Roles y Permisos

| Rol | Permisos |
|---|---|
| **Visitante** | Ver _landing_, ligas pÃºblicas, equipos y resultados finalizados. |
| **Jugador** | Todo lo anterior + perfil propio, unirse a equipo, chatear, ver convocatorias. |
| **CapitÃ¡n** | Todo lo anterior + crear/gestionar equipo, inscribir en ligas, aceptar retos, gestionar plantilla. |
| **Admin** | Acceso total: CRUD de usuarios, ligas, campos, equipos, moderaciÃ³n de chats y sanciones. |

---

## ðŸ—ºï¸ Mapa de Rutas

El router resuelve siempre `/{controlador}/{acciÃ³n}/{parametros...}`. La ruta raÃ­z `/` apunta a `HomeController@index`.

| MÃ©todo | Ruta | Controlador@acciÃ³n | DescripciÃ³n |
|---|---|---|---|
| `GET` | `/` | `HomeController@index` | Landing pÃºblica |
| `GET/POST` | `/auth/login` | `AuthController@login` | Inicio de sesiÃ³n |
| `GET/POST` | `/auth/register` | `AuthController@register` | Registro |
| `POST` | `/auth/logout` | `AuthController@logout` | Cierre de sesiÃ³n |
| `GET` | `/dashboard` | `DashboardController@index` | Panel del usuario |
| `GET` | `/leagues` | `LeaguesController@index` | Listado de ligas |
| `GET` | `/leagues/show/{id}` | `LeaguesController@show` | Detalle de liga |
| `POST` | `/leagues/register/{id}` | `LeaguesController@register` | Inscribir equipo en liga |
| `GET` | `/teams` | `TeamsController@index` | Listado de equipos |
| `GET/POST` | `/teams/create` | `TeamsController@create` | Crear equipo (capitÃ¡n) |
| `GET` | `/teams/show/{id}` | `TeamsController@show` | Detalle de equipo |
| `GET` | `/matches` | `MatchesController@index` | Listado de partidos |
| `GET` | `/matches/show/{id}` | `MatchesController@show` | Detalle de partido |
| `GET/POST` | `/matches/create` | `MatchesController@create` | Crear partido (capitÃ¡n) |
| `POST` | `/matches/confirm/{id}` | `MatchesController@confirm` | Confirmar partido |
| `POST` | `/matches/cancel/{id}` | `MatchesController@cancel` | Cancelar partido |
| `POST` | `/matches/finish/{id}` | `MatchesController@finish` | Finalizar partido |
| `GET` | `/campos` | `CamposController@index` | Listado de campos |
| `GET` | `/chat` | `ChatController@index` | Listado de salas |
| `GET/POST` | `/chat/room/{id}` | `ChatController@room` | Sala de chat |
| `GET` | `/profile` | `ProfileController@index` | Perfil propio |
| `GET/POST` | `/profile/edit` | `ProfileController@edit` | Editar perfil |
| `GET/POST` | `/profile/password` | `ProfileController@password` | Cambiar contraseÃ±a |
| `GET` | `/admin` | `AdminController@index` | Dashboard admin |
| `GET` | `/admin/users` | `AdminController@users` | GestiÃ³n de usuarios |
| `POST` | `/admin/setRole/{id}` | `AdminController@setRole` | Asignar rol |
| `POST` | `/admin/deleteUser/{id}` | `AdminController@deleteUser` | Eliminar usuario |
| `GET` | `/legal/terms` | `LegalController@terms` | TÃ©rminos |
| `GET` | `/legal/privacy` | `LegalController@privacy` | Privacidad |
| `GET` | `/legal/cookies` | `LegalController@cookies` | Cookies |

> Las rutas se resuelven en [`app/core/Router.php`](app/core/Router.php). El despachador valida `^[a-zA-Z0-9_]+$` en controlador y acciÃ³n, asÃ­ que las acciones multi-palabra usan `camelCase` en la URL (ej. `setRole`, `deleteUser`).

---

## âš™ï¸ InstalaciÃ³n y ConfiguraciÃ³n

### Requisitos

- **Apache 2.4+** con `mod_rewrite` habilitado.
- **PHP 8.0** o superior con las extensiones:
  - `pdo_sqlite`
  - `mbstring`
  - `fileinfo` (opcional, para futura validaciÃ³n de uploads)
- Permisos de escritura en `storage/` y `uploads/`.

### Despliegue local con XAMPP

```bash
# 1. Clonar dentro de htdocs
cd /c/xampp/htdocs/
git clone <repo-url> FastPlay_v3

# 2. Asegurar permisos en directorios escribibles (Linux/Mac)
chmod -R 775 FastPlay_v3/storage FastPlay_v3/uploads

# 3. Arrancar Apache desde el panel de XAMPP

# 4. Visitar
#    http://localhost/FastPlay_v3/
```

> La aplicaciÃ³n detecta automÃ¡ticamente la `BASE_URL` en funciÃ³n del subdirectorio donde estÃ© instalada â€” no necesita configuraciÃ³n manual.

### Despliegue con Docker

```bash
docker build -t fastplay .
docker run -p 8080:80 -e APP_ENV=production fastplay
```

### Servidor embebido de PHP (sin XAMPP)

```bash
php -S localhost:8000 router.php
```

### Primer arranque

En el primer acceso, [`app/core/Database.php`](app/core/Database.php):

1. Crea el archivo `storage/fastplay.sqlite` si no existe.
2. Ejecuta todas las migraciones (creaciÃ³n de tablas).
3. Inserta datos demo (_seeding_): usuarios, equipos, ligas y partidos de ejemplo.

Para resetear la base de datos basta con borrar el archivo `storage/fastplay.sqlite` y refrescar.

### PostgreSQL opcional

La aplicación funciona por defecto con SQLite. El archivo `database/fastplay_postgres.sql` es un script auxiliar para portar el esquema y los datos demo a PostgreSQL si se decide migrar más adelante; no se carga automáticamente por la app actual.

El script mantiene las mismas reglas de dominio que el código PHP: roles `player/admin`, estados de partido `pending/confirmed/cancelled/finished`, superficies admitidas y equipos inscritos de forma coherente con sus partidos de liga.

---

## ðŸ”‘ Credenciales Demo

| Rol | Email | ContraseÃ±a |
|---|---|---|
| **Admin** | `admin@fastplay.es` | `Admin1234!` |
| **Jugador** | `demo@fastplay.es` | `Demo1234!` |

> âš ï¸ El _seeder_ sÃ³lo siembra datos demo si `APP_ENV` â‰  `production`. En producciÃ³n, define `SetEnv APP_ENV production` (o variable de entorno equivalente) para garantizar que estas cuentas no se creen.

---

## ðŸ”’ Seguridad

FastPlay aplica una capa de seguridad defensiva en profundidad:

- **CSRF** â€” Token por sesiÃ³n validado en todos los formularios `POST`.
- **Sesiones endurecidas** â€” Cookies `HttpOnly`, `Secure` y `SameSite=Lax`; regeneraciÃ³n de ID tras login; almacenamiento local en `storage/sessions/` para evitar permisos rotos de XAMPP.
- **Hashing** â€” ContraseÃ±as con `password_hash()` (bcrypt) + verificaciÃ³n con `password_verify()`.
- **Rate limiting** â€” LimitaciÃ³n de intentos de login para prevenir _brute force_.
- **SQL Injection** â€” 100% _prepared statements_ vÃ­a PDO; sin concatenaciÃ³n de SQL.
- **XSS** â€” Escape sistemÃ¡tico con `htmlspecialchars()` en vistas + **CSP restrictiva**.
- **Headers de seguridad** â€” `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Content-Security-Policy`.
- **Aislamiento del _document root_** â€” Solo `public/` es accesible; `app/`, `config/`, `storage/` estÃ¡n bloqueadas por `.htaccess`. La carpeta `uploads/` estÃ¡ expuesta pero **no permite ejecutar scripts** (PHP/CGI/etc. se bloquean por `<FilesMatch>`).
- **Subida de archivos** â€” Pendiente de implementaciÃ³n. Cuando se implemente (avatars, logos), debe validar MIME real con `finfo`, extensiÃ³n por _whitelist_ y guardarse fuera de `public/` o con nombre regenerado.

> Las cabeceras de seguridad se envÃ­an desde [`config/config.php`](config/config.php) (funciÃ³n `security_headers()`). Las reglas de Apache viven en los `.htaccess` por carpeta.

---

## ðŸŽ¨ Identidad Visual y DiseÃ±o

### Concepto de Marca
FastPlay trata cada pantalla como un **partido nocturno bajo focos**. La identidad utiliza un fondo casi negro (`#060d09`), tarjetas de "cristal" translÃºcidas, un verde neÃ³n confiado (`#16a34a`) para acciones primarias y un dorado (`#fbbf24`) reservado **exclusivamente para la Liga Pro**.

### Paleta

| Color | Hex | Uso |
|---|---|---|
| â¬› Estadio | `#060d09` | Fondo principal |
| ðŸŸ¢ NeÃ³n | `#16a34a` | Acciones primarias, Ã©nfasis |
| ðŸŸ¡ Dorado | `#fbbf24` | Liga Pro, premios |
| â¬œ Glass | `rgba(255,255,255,0.04)` | Tarjetas y superficies |
| ðŸ”˜ Texto | `#e5e7eb` | Texto principal |

### TipografÃ­a y Tono
- **Fuente**: Inter (cargas variables, peso `Black 900` para titulares).
- **Voz**: Informal (_"TÃº"_), directa y deportiva. Los botones siempre terminan en ` â†’`.
- **IconografÃ­a**: Uso deliberado de **emojis** como iconografÃ­a de marca y **SVG inline** para utilidades funcionales.

### Superficies (Glassmorphism)

```css
.glass {
  background: rgba(255, 255, 255, 0.04);
  backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
}
```

---

## ðŸ“ Convenciones de CÃ³digo

- **PHP** â€” `PSR-12` flexible, _strict types_ donde aplica. Por simplicidad **no se usan namespaces**: las clases (`Controller`, `Router`, `Database`, modelos y controladores) viven en el espacio global y se cargan con `require_once`.
- **Nombres** â€” Controladores y modelos en `PascalCase`; mÃ©todos en `camelCase`; variables y vistas en `snake_case`.
- **Vistas** â€” Solo PHP + HTML; **nunca** lÃ³gica de negocio. Cualquier dato debe llegar pre-procesado desde el controlador.
- **CSS** â€” Variables CSS (`--neon`, `--glass-bg`...) en `:root`. Componentes prefijados (`.btn-`, `.card-`, `.lg-` para liga).
- **JS** â€” Vanilla, sin _bundlers_. Un archivo por _feature_ (`scroll-anim.js`).
- **Commits** â€” Mensajes en espaÃ±ol, modo imperativo (`Arregla`, `AÃ±ade`, `Refactoriza`).

---

## 🧪 Tests

El proyecto incluye una configuración básica de PHPUnit para proteger helpers, configuración y consistencia de datos.

```bash
composer install
vendor/bin/phpunit --configuration phpunit.xml
```

La suite usa `storage/fastplay_test.sqlite` y se reinicia desde `tests/bootstrap.php`; no toca la base demo `storage/fastplay.sqlite`.

---

## ðŸ›£ï¸ Roadmap

### âœ… v3 (actual)
- Refactor profundo del _router_.
- Endurecimiento de sesiones y CSP.
- ExtracciÃ³n de animaciones de scroll a archivos externos.
- AuditorÃ­a de seguridad en curso (ver [`arreglos.md`](arreglos.md)).

### ðŸ”œ v4 (planeado)
- [ ] API REST para cliente mÃ³vil.
- [ ] Notificaciones _push_ vÃ­a _service workers_.
- [ ] EstadÃ­sticas avanzadas (xG, mapas de calor).
- [ ] Sistema de pagos para Liga Pro (Stripe).
- [ ] _Live scoring_ vÃ­a WebSockets.
- [ ] PWA instalable.

---

## ðŸ“ Notas de VersiÃ³n (v3)

Esta versiÃ³n incluye:

- **RefactorizaciÃ³n profunda del sistema de rutas** â€” Router mÃ¡s expresivo y mantenible.
- **Correcciones crÃ­ticas en seguridad de sesiones** â€” RegeneraciÃ³n de ID, cookies endurecidas, _path_ acotado a la sub-instalaciÃ³n y `session.save_path` propio dentro de `storage/sessions/`.
- **ExtracciÃ³n de animaciones a archivos externos** â€” `scroll-anim.css`, `scroll-anim.js` y `home-init.js` viven en `public/`. Ya no quedan bloques `<style>`/`<script>` _inline_ crÃ­ticos en `home/index.php`.
- **AutorizaciÃ³n en partidos** â€” Crear, confirmar, cancelar y finalizar partidos exige ser capitÃ¡n de uno de los equipos (o admin).
- **Endurecimiento del panel admin** â€” No se puede eliminar ni degradar al Ãºltimo administrador.
- **Limpieza de assets** â€” La animaciÃ³n de scroll usa `public/video/hero.webm`; archivos huÃ©rfanos eliminados.
- **ReparaciÃ³n de consistencia de datos** â€” Los equipos que aparecen en partidos de liga quedan inscritos automÃ¡ticamente en esa liga.
- **Tests de regresiÃ³n** â€” PHPUnit cubre helpers, configuraciÃ³n, credenciales demo y coherencia de liga/partidos.
- **Rate limit corregido** â€” Las ventanas temporales usan UTC (`gmdate`) para casar con `datetime('now')` de SQLite.
- **`.gitignore` aÃ±adido** â€” Excluye SQLite, _journal_, logs, sesiones, cachÃ©s, `vendor/` y subidas de usuario.

> El historial completo de cambios vive en `git log`. Las correcciones detectadas durante auditorÃ­a se enumeran en [`arreglos.md`](arreglos.md).

---

## ðŸ“„ Licencia y CrÃ©ditos

**Proyecto acadÃ©mico** desarrollado como parte del trabajo de fin de ciclo / portfolio personal.

- **Autor**: [Sadek2110](https://github.com/Sadek2110)
- **TipografÃ­a**: [Inter](https://rsms.me/inter/) â€” Rasmus Andersson
- **InspiraciÃ³n visual**: EstÃ©tica _glass-and-neon_ de UIs deportivas modernas

> Si reutilizas el cÃ³digo, una menciÃ³n al repositorio original es bienvenida. âš½

---

<div align="center">

**FastPlay** â€” _FÃºtbol callejero, organizado_ â†’

</div>



