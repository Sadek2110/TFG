# Memoria Técnica - Fastplay

## Comparación con el Proyecto Real
- El proyecto real de referencia está en `C:\Users\sadek\Desktop\Proyectos personales\Fastplay`.
- La refactorización actual es un MVP en español, sin Composer ni dependencias externas, y no tiene paridad funcional completa con el proyecto real.
- Módulos del proyecto real no presentes en esta refactorización: dashboard, chat, notificaciones, solicitudes de partido, solicitudes de unión a equipo, premium/suscripciones/pagos Stripe, correo SMTP/PHPMailer, OAuth Google, verificación de email, login rate limiting, páginas legales, logros, avatar/subidas y administración avanzada.
- Si el objetivo pasa a ser paridad funcional con el proyecto real, conviene migrar por capas: esquema/modelos, servicios, controladores/rutas, vistas/JS y pruebas.

## Arquitectura del Proyecto
Fastplay es una aplicación web escrita en PHP 8.1+ con un MVC propio minimalista, usando SQLite como base de datos y Apache como servidor web.

### Rutas Clave
- **Controlador frontal:** `public/index.php`
- **Configuración:** `config/configuracion.php`
- **Rutas de la aplicación:** `config/rutas.php`
- **Base de datos:** `base_datos/fastplay.sqlite` (generada automáticamente en base_datos/ o en la ruta de almacenamiento configurada)

### Decisiones de Diseño Recientes
- **Despliegue en Producción (Easypanel/Docker):**
  - Se modificaron las constantes de configuración `RUTA_BD` y `RUTA_SESIONES` en `config/configuracion.php` para admitir rutas dinámicas usando variables de entorno (`FASTPLAY_BD_PATH` y `FASTPLAY_SESIONES_PATH`).
  - Se añadió un `Dockerfile` en el directorio raíz del proyecto que configura Apache, PHP 8.1, `pdo_sqlite` y los permisos del directorio `/var/www/html/almacenamiento` para almacenar sesiones y la base de datos de manera persistente.
  - La persistencia en producción se logra mediante un único volumen persistente montado en `/var/www/html/almacenamiento`.
- **Landing a pantalla completa (2026-06-17):** El hero de la portada se reescribió
  en `app/vistas/inicio/index.php` + `public/css/estilos.css` para ocupar 100dvh
  full-bleed. Se apoya en `public/js/hero.js` (cabecera transparente sobre el hero
  que se vuelve sólida al scrollear) y en `public/js/animaciones-scroll.js`
  (cascada de hijos con `data-revelar` al cargar). Sin dependencias externas,
  CSS vanilla. Ver `.agent/memory/2026-06-17-landing-rediseno.md` para detalle.
- **Navbar y hero más limpios (2026-06-17):** El usuario pidió más aire en el
  navbar y un hero similar a la captura `public/imagenes/capturas/01_inicio_escritorio.png`.
  En `public/css/estilos.css` se separaron mejor marca, navegación y acciones; en
  móvil el desplegable usa filas más amplias. El hero ya no usa halos/blobs de
  color ni texto con degradado animado; mantiene la imagen/vídeo con velo oscuro
  neutro y acento verde sólido en la segunda línea del título.
- **Skill "diseño":** En este entorno no existe ninguna skill de diseño (solo
  `customize-opencode`, que es para configurar opencode). Si el usuario la pide,
  trabajar con buenas prácticas de UI/UX y avisarle.

## Actualización 2026-06-17 - Equipos, partidos y campos
- `/equipos/{id}` es ahora una ficha funcional: un usuario libre puede unirse,
  un usuario que ya pertenece a un equipo no ve la acción, y el capitán/admin
  puede invitar por correo, actualizar dorsal/posición, marcar titulares y echar
  miembros.
- Un capitán que ya tiene equipo se redirige a su ficha en vez de crear otro.
- `miembros_equipo` incorpora `titular INTEGER NOT NULL DEFAULT 0`; `BaseDeDatos`
  añade esta columna con migración ligera en SQLite existentes.
- `/partidos` tiene calendario mensual server-side con día actual, días con
  partido y días ya jugados. El botón destacado "Solicitar partido" solo aparece
  para capitanes/admin.
- `/campos` muestra tarjetas con foto, ubicación enlazada a Google Maps y
  superficie. `campos` incorpora `foto TEXT` con migración ligera.
- La invitación por correo no envía email real ni llama APIs externas; en este
  MVP incorpora al usuario existente si el correo corresponde a una cuenta libre.

## Actualización 2026-06-18 - Fix de despliegue SQLite
- Se corrigió un fallo de producción donde todos los endpoints podían caer en
  el `catch` global con "Ha ocurrido un error inesperado". Causa probable:
  volumen persistente con `fastplay.sqlite` existente pero vacío/sin esquema, o
  permisos del volumen montado en runtime.
- `BaseDeDatos::conexion()` ya no decide aplicar esquema solo por `file_exists`;
  ahora comprueba si existe la tabla `usuarios`. Si no existe, aplica
  `base_datos/esquema.sql` y después ejecuta las migraciones ligeras.
- `Dockerfile` ahora ejecuta al arrancar `mkdir -p` y `chown -R www-data:www-data`
  sobre `/var/www/html/almacenamiento` antes de `apache2-foreground`, porque los
  volúmenes montados pueden sobrescribir permisos definidos durante el build.

## Actualización 2026-06-18 - Espaciado visual y hero del landing
- Se amplió el ritmo vertical global en `public/css/estilos.css` con tokens de
  espacio de página y separación entre secciones para evitar vistas pegadas.
- Tarjetas, listas, formularios, panel contextual, cifras, calendario, detalle
  de equipo y tarjetas de campos usan gaps/paddings más amplios, con reducción
  responsive en móvil.
- En `app/vistas/inicio/index.php` se eliminó el indicador "Descubrir" del hero.
- El vídeo del hero se recorta por CSS anclándolo arriba y aumentando su altura
  al 112%, de forma que se oculta visualmente la zona inferior donde aparecía la
  marca de agua sin regenerar `public/video/hero.webm`.
