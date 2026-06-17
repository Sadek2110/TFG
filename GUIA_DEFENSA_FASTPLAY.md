# Guia rapida para defender FastPlay

Esta guia esta basada en el codigo real de esta entrega. Usala para explicar con seguridad lo que hay implementado, sin mezclarlo con versiones antiguas de la documentacion.

## 1. Idea del proyecto

**Frase de apertura:**

FastPlay es una plataforma web para organizar futbol amateur: usuarios, equipos, miembros, partidos, campos y ligas con clasificacion. La he planteado como un MVP completo, facil de desplegar y facil de mantener.

**Problema que resuelve:**

Organizar partidos por chats es caotico: falta centralizar equipos, horarios, campos, resultados y clasificaciones. FastPlay unifica ese flujo.

**Roles:**

- Visitante: puede ver contenido publico, registrarse e iniciar sesion.
- Jugador: puede gestionar su perfil y crear o unirse a equipos.
- Capitan: no es un rol fijo de la tabla; se deduce cuando un usuario administra un equipo.
- Administrador: gestiona campos, ligas y panel general.

**Archivos a abrir:**

- `README.md`
- `config/rutas.php`
- `base_datos/esquema.sql`

## 2. Arquitectura backend / DWES

**Frase clave:**

No he usado Laravel ni Symfony. He construido un MVC propio, pequeno y legible, para controlar todo el flujo de una peticion.

**Flujo de una peticion:**

`public/index.php` carga configuracion y nucleo, crea el enrutador, carga `config/rutas.php` y despacha la peticion al controlador adecuado.

Flujo mental:

`Navegador -> public/index.php -> Enrutador -> Controlador -> Modelo -> BaseDeDatos -> Vista -> Layout`

**Piezas importantes:**

- `public/index.php`: front controller unico.
- `config/rutas.php`: tabla explicita de rutas GET/POST.
- `app/nucleo/Enrutador.php`: convierte patrones como `/equipos/{id}` en rutas reales.
- `app/nucleo/Controlador.php`: renderiza vistas, redirige, devuelve JSON y exige autenticacion/admin/POST.
- `app/nucleo/BaseDeDatos.php`: conexion PDO SQLite y helpers `ejecutar`, `uno`, `todos`, `valor`.

**Que decir si te preguntan por MVC:**

Los controladores coordinan la peticion, los modelos contienen las consultas SQL y reglas de datos, y las vistas solo pintan HTML. Asi evito mezclar logica de negocio con presentacion.

## 3. Base de datos

**Frase clave:**

La base de datos esta normalizada y usa claves foraneas reales en SQLite, activadas con `PRAGMA foreign_keys = ON`.

**Tablas principales:**

- `usuarios`: cuenta, email unico, hash de contrasena y rol.
- `equipos`: datos del equipo y capitan.
- `miembros_equipo`: relacion muchos-a-muchos entre usuarios y equipos.
- `partidos`: equipos local/visitante, campo, liga, goles y estado.
- `campos`: instalaciones.
- `ligas` y `ligas_equipos`: competiciones y equipos inscritos.

**Restricciones defendibles:**

- Un email no se puede repetir.
- Un usuario no puede duplicarse dentro del mismo equipo.
- Un partido no puede tener el mismo equipo como local y visitante.
- Las claves foraneas mantienen la coherencia entre tablas.

**Archivo estrella:**

- `base_datos/esquema.sql`

## 4. Logica de negocio

### Usuarios y autenticacion

**Archivos:**

- `app/controladores/ControladorAuth.php`
- `app/modelos/Usuario.php`
- `app/nucleo/Sesion.php`

**Que decir:**

En el registro valido los campos, compruebo si el email ya existe y guardo la contrasena con `password_hash`. En el login uso `password_verify`. Al iniciar sesion regenero el ID de sesion para evitar fijacion de sesion.

### Equipos

**Archivos:**

- `app/controladores/ControladorEquipos.php`
- `app/modelos/Equipo.php`
- `app/modelos/MiembroEquipo.php`

**Que decir:**

Cuando un usuario crea un equipo, queda como capitan y se anade automaticamente como miembro. Para editar, eliminar o gestionar miembros, el servidor comprueba que sea capitan o administrador.

### Partidos

**Archivos:**

- `app/controladores/ControladorPartidos.php`
- `app/modelos/Partido.php`

**Que decir:**

Solo un capitan implicado o un administrador puede crear/gestionar resultados. Al registrar el resultado, el partido pasa a estado `finalizado`.

### Ligas

**Archivos:**

- `app/controladores/ControladorLigas.php`
- `app/modelos/Liga.php`

**Que decir:**

La clasificacion no esta escrita a mano en una tabla; se calcula desde los partidos finalizados. La consulta cuenta jugados, ganados, empatados, perdidos, goles y puntos, y ordena por puntos, diferencia de goles, goles a favor y nombre.

## 5. Seguridad

**Frase clave:**

La seguridad esta aplicada en capas: sesiones seguras, CSRF, validacion, hash de contrasenas, consultas preparadas y escape de salida.

**Puntos concretos:**

- Contrasenas con `password_hash` y `password_verify`.
- Sesion con cookie `HttpOnly`, `SameSite=Lax` y `Secure` si hay HTTPS.
- `session_regenerate_id(true)` al iniciar sesion.
- CSRF en formularios POST mediante token por sesion y `hash_equals`.
- PDO con consultas preparadas en todos los modelos.
- Escape HTML con `e()` / `escapar()` para prevenir XSS.
- Acciones destructivas solo por POST.
- Permisos en servidor: las vistas ocultan botones, pero el controlador vuelve a comprobar permisos.

**Archivos estrella:**

- `app/nucleo/Csrf.php`
- `app/nucleo/Sesion.php`
- `app/nucleo/helpers.php`
- `app/nucleo/BaseDeDatos.php`
- `app/controladores/ControladorEquipos.php`
- `app/controladores/ControladorPartidos.php`

## 6. Cliente / DWEC

**Frase clave:**

El JavaScript esta hecho en vanilla JS, sin frameworks ni bundler, separado por funcionalidades y con mejora progresiva.

**Pieza estrella: panel contextual AJAX**

Archivos:

- `public/js/panel-contextual.js`
- `app/controladores/ControladorApi.php`
- `app/vistas/inicio/index.php`

**Que ocurre:**

El panel de la portada tiene un `data-url` apuntando a `/api/contexto`. El JS hace `fetch`, recibe JSON y reconstruye el DOM segun el rol: visitante, jugador, capitan o administrador.

**Conceptos que demuestra:**

- Evento `click` para recargar.
- Carga inicial automatica.
- `fetch` con cabecera `Accept: application/json`.
- `async/await`.
- `try/catch/finally`.
- Comprobacion de `response.ok`.
- JSON.
- `Map` para roles.
- `Set` para evitar acciones duplicadas.
- DOM seguro con `createElement`, `textContent` y `replaceChildren`, evitando `innerHTML`.
- Accesibilidad con `aria-busy` y `aria-live`.

**Otras piezas DWEC:**

- `public/js/validacion.js`: regex, eventos `submit`, `blur`, `input`, `preventDefault`, `aria-invalid`.
- `public/js/cookies.js`: consentimiento y API `window.FastplayCookies`.
- `public/js/tema.js`: tema claro/oscuro con `localStorage` y `prefers-color-scheme`.
- `public/js/detalle-equipo.js`: delegacion de eventos, filtrado de miembros y `dataset`.
- `public/js/inicio.js`: contadores animados.
- `public/js/carta-jugador.js`: efecto 3D con eventos de puntero.

## 7. Diseno de interfaces / DIW

**Frase clave:**

El diseno se ha hecho sin Bootstrap ni Tailwind, con un sistema visual propio basado en variables CSS, componentes reutilizables y responsive.

**Puntos a resaltar:**

- Variables CSS en `:root`.
- Paleta de estadio nocturno: fondo oscuro, verde como accion principal y acentos de contraste.
- Hero con video de fondo y poster fallback.
- Componentes reutilizables: botones, tarjetas, formularios, mensajes flash, cabecera, pie y banner de cookies.
- Responsive con media queries.
- Accesibilidad: enlace "Saltar al contenido", etiquetas, `aria-*`, estados de foco y contenido visible aunque JS falle.

**Archivos a abrir:**

- `public/css/estilos.css`
- `app/vistas/layout.php`
- `app/vistas/inicio/index.php`
- `app/vistas/parciales/cabecera.php`
- `app/vistas/parciales/banner-cookies.php`

## 8. Despliegue

**Frase clave:**

El proyecto se puede ejecutar de forma local con el servidor embebido de PHP, con Apache/XAMPP o en Docker.

**Archivos:**

- `Dockerfile`
- `.htaccess`
- `public/.htaccess`
- `router.php`
- `public/router.php`

**Que decir:**

El Dockerfile usa `php:8.1-apache`, instala PDO SQLite, activa `mod_rewrite`, mueve el document root a `public/` y define rutas de base de datos y sesiones por variables de entorno. Asi evito exponer carpetas internas como `app`, `config` o `base_datos`.

## 9. Pruebas

**Archivo:**

- `tests/correr.php`

**Resultado comprobado:**

`16 OK / 0 KO`

**Que cubren:**

- Creacion y busqueda de usuarios.
- Email duplicado.
- Login correcto e incorrecto.
- Creacion de equipos.
- Capitan correcto e incorrecto.
- Anadir y quitar miembros.
- Validador.
- Clasificacion de liga con un partido finalizado.

**Nota honesta:**

Son pruebas minimas, no una suite completa. Sirven para verificar los modelos principales y la clasificacion antes de la defensa.

## 10. Demo recomendada en 7 minutos

1. Portada: explicar problema, hero, panel contextual y contadores.
2. Abrir `config/rutas.php`: ensenar que hay rutas claras por funcionalidad.
3. Registro/login: explicar validacion, hash y sesion.
4. Equipos: crear/editar equipo y anadir miembro por email.
5. Partidos: crear partido y registrar resultado.
6. Liga: ensenar clasificacion calculada.
7. Admin: campos/ligas/panel.
8. Cerrar con seguridad y pruebas.

## 11. Preguntas tipicas y respuestas

**Por que PHP puro y no Laravel?**

Porque el objetivo era demostrar fundamentos: rutas, controladores, modelos, vistas, sesiones, validacion y PDO. Un framework habria ocultado parte del aprendizaje.

**Por que SQLite?**

Porque para un MVP academico es portable, no requiere servidor de base de datos y permite ejecutar la demo facilmente. A futuro se podria migrar a MySQL o PostgreSQL manteniendo la idea de PDO.

**El JavaScript sustituye la validacion del servidor?**

No. El JS mejora la experiencia dando feedback inmediato, pero la validacion importante se repite en PHP, porque el cliente se puede manipular o desactivar.

**Como evitas SQL Injection?**

No concateno datos de usuario en SQL. Uso consultas preparadas con PDO y parametros.

**Como evitas XSS?**

Escapo la salida en las vistas con `e()`, que usa `htmlspecialchars`. En JS uso `textContent` y construccion del DOM en vez de `innerHTML`.

**Como evitas CSRF?**

Los formularios POST incluyen un token de sesion. El servidor lo valida con `hash_equals` antes de ejecutar la accion.

**Que parte es mas compleja tecnicamente?**

La clasificacion de ligas, porque se calcula a partir de partidos finalizados con una consulta SQL agregada, y el panel contextual, porque conecta backend JSON con DOM dinamico.

## 12. Cosas que NO conviene decir

- No digas que usa Laravel, Symfony, Bootstrap o Tailwind.
- No digas que hay Stripe, OAuth, PHPMailer o Composer en esta version.
- No digas MySQL/PostgreSQL como implementacion actual: esta entrega usa SQLite con PDO.
- No digas que el rol capitan esta guardado como rol de usuario: se deduce por los equipos que administra.
- No digas que las pruebas son exhaustivas: son pruebas minimas de modelos.

## 13. Resumen final para memorizar

FastPlay es un MVP de gestion de futbol amateur hecho con PHP 8, MVC propio, SQLite mediante PDO, vistas PHP, CSS propio y JavaScript vanilla. La parte fuerte de servidor es el flujo Front Controller -> Router -> Controller -> Model -> View. La parte fuerte de cliente es el panel contextual AJAX y la validacion accesible. La seguridad se apoya en sesiones endurecidas, CSRF, hash de contrasenas, consultas preparadas y escape de salida. La parte de datos mas interesante es la clasificacion de ligas calculada desde partidos finalizados.
