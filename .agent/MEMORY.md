# Memoria Técnica - Fastplay

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
