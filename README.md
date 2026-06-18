# FastPlay

Plataforma web sencilla para organizar futbol amateur: usuarios, equipos,
partidos, campos y ligas con clasificacion.

Trabajo de fin de grado de 2.o de DAW. Reescritura completa del proyecto,
priorizando codigo legible y mantenible sobre cualquier arquitectura compleja.

## Stack

- PHP 8.1+ con MVC propio (sin Laravel ni Symfony).
- SQLite via PDO con consultas preparadas.
- Vistas PHP + HTML5 semantico, CSS y JavaScript vanilla (sin Bootstrap ni
  Tailwind, sin bundler).
- Apache (o el servidor embebido `php -S`).

## Estructura

```
Fastplay/
|-- app/
|   |-- nucleo/          # BaseDeDatos, Enrutador, Sesion, Csrf, Validador, Controlador, helpers
|   |-- controladores/   # ControladorInicio, ControladorAuth, ControladorPerfil,
|   |                    # ControladorEquipos, ControladorPartidos, ControladorCampos,
|   |                    # ControladorLigas, ControladorAdmin
|   |-- modelos/         # Usuario, Equipo, MiembroEquipo, Partido, Campo, Liga
|   `-- vistas/          # Vistas PHP y parciales (cabecera, pie, mensajes)
|-- almacenamiento/
|   `-- sesiones/        # Archivos de sesion PHP (excluidos de Git)
|-- base_datos/
|   |-- esquema.sql      # CREATE TABLE de todas las tablas
|   |-- sembrar.php      # Script de datos de demostracion
|   `-- fastplay.sqlite  # Generado al primer arranque (excluido de Git)
|-- config/
|   |-- configuracion.php # Rutas, sesion, entorno, url_base()
|   `-- rutas.php         # Tabla de rutas HTTP -> controlador/accion
|-- public/
|   |-- index.php        # Front controller
|   |-- router.php       # Router para `php -S ... -t public`
|   |-- .htaccess        # Reescritura para Apache
|   |-- css/estilos.css  # Hoja de estilos propia
|   `-- js/              # JS vanilla: panel-contextual (AJAX), validacion,
|                        # cookies, tema, animaciones-scroll, carta-jugador,
|                        # inicio, detalle-equipo, principal
|-- tests/correr.php     # Pruebas minimas de los modelos
|-- router.php           # Router para `php -S` desde la raiz del proyecto
```

## Instalacion y arranque

Solo se necesita PHP 8.1 o superior con la extension `pdo_sqlite` activada
(viene de serie en XAMPP y en la mayoria de distribuciones).

1. Clonar el repositorio y entrar en `Fastplay/`.
2. (Opcional) Cargar datos de demostracion:

   ```bash
   php base_datos/sembrar.php
   ```

3. Arrancar el servidor embebido:

   ```bash
   php -S localhost:8000 router.php
   ```

   Tambien funciona desde `public/`:

   ```bash
   cd public
   php -S localhost:8000 router.php
   ```

4. Abrir `http://localhost:8000` en el navegador.

Para Apache (XAMPP), basta con colocar el proyecto en `htdocs/` y entrar a
`http://localhost/Fastplay/public/`. Los `.htaccess` de la raiz y de
`public/` se encargan de la reescritura.

La base de datos se crea automaticamente la primera vez que se accede a una
pagina que la necesita; si no se ha sembrado, estara vacia.

## Usuarios de prueba (tras ejecutar `sembrar.php`)

| Email                 | Contrasena   | Rol            |
|-----------------------|--------------|----------------|
| admin@fastplay.test   | admin1234    | administrador  |
| ana@fastplay.test     | jugador1     | capitana       |
| bob@fastplay.test     | jugador1     | capitan        |
| eva@fastplay.test     | jugador1     | capitana       |
| luis@fastplay.test    | jugador1     | jugador        |

## Funcionalidades principales

- Pagina de inicio publica.
- Registro, inicio y cierre de sesion. Edicion del perfil propio.
- Listado y detalle de **equipos**. Creacion y edicion por el capitan,
  gestion de miembros (anadir por correo, quitar).
- Listado y detalle de **partidos**. Creacion por capitanes, registro de
  resultado por los dos capitanes o por un administrador.
- Listado de **campos**. Alta y baja por administrador.
- Listado y detalle de **ligas**, con clasificacion calculada a partir de
  los partidos finalizados (3 puntos victoria, 1 empate, 0 derrota).
  Inscripcion de equipos por administrador.
- **Panel de administracion** con resumen del sitio.
- Estados vacios, mensajes flash (exito/error/aviso) y confirmaciones antes
  de acciones destructivas.
- **Parte de cliente (JavaScript vanilla):** panel contextual por rol via
  AJAX (`fetch` a `/api/contexto` -> JSON -> DOM), validacion de formularios
  con expresiones regulares, consentimiento de cookies, tema claro/oscuro
  con `localStorage`, animaciones de scroll (`IntersectionObserver`), carta
  con efecto 3D y contadores animados. Detalle en `context/rubrica/`.

## Seguridad implementada

- Consultas preparadas con PDO (`ATTR_EMULATE_PREPARES = false`) en todos
  los accesos a datos.
- Contrasenas hasheadas con `password_hash()` (bcrypt) y verificadas con
  `password_verify()`.
- Token CSRF por sesion en todos los formularios `POST`, validado con
  `hash_equals()`. Las acciones que modifican datos solo aceptan `POST`.
- Cookies de sesion con `HttpOnly`, `SameSite=Lax`, y `Secure` cuando hay
  HTTPS. Regeneracion del id de sesion en cada login.
- Escape sistematico de la salida con el helper `e()` basado en
  `htmlspecialchars()`.
- Comprobacion de autenticacion, rol y propiedad del recurso en el servidor
  (las vistas solo ocultan controles, nunca actuan como guardia).
- Tabla `usuarios` con constraint sobre el rol.

## Pruebas

```bash
php tests/correr.php
```

Salida esperada: 16 OK / 0 KO.

## Convenciones

- Codigo, identificadores y textos en espanol; se mantienen en ingles las
  palabras reservadas y APIs estandar.
- Una clase por archivo, nombres descriptivos (`ControladorEquipos`,
  `BaseDeDatos`, `MiembroEquipo`).
- Vistas centradas en presentar datos; controladores orquestan; modelos
  contienen el SQL.
- Sin dependencias externas: no hay `composer.json`.

## Limitaciones intencionales

Para mantener el alcance al de un MVP defendible en 2.o DAW se han excluido
expresamente: pagos, OAuth, envio real de correos, mapas externos, chat en
tiempo real, animaciones avanzadas, WebSockets, colas, microservicios,
compatibilidad simultanea con varios motores de base de datos.
