# 2026-06-18 - Fix de despliegue SQLite

## Contexto
El usuario reporto que tras desplegar aparecia "Ha ocurrido un error inesperado.
Intentelo mas tarde." en todos los endpoints. Ese mensaje viene del `catch`
global de `public/index.php`, asi que se busco una excepcion comun a rutas.

## Causa probable
- `BaseDeDatos` aplicaba `esquema.sql` solo si el archivo SQLite no existia.
  En produccion, un volumen persistente puede contener un `fastplay.sqlite`
  existente pero vacio o sin tablas, lo que hacia que la app intentara migrar
  tablas inexistentes.
- En Docker, los permisos aplicados durante el build no siempre sirven para un
  volumen montado en runtime. Si el volumen de `almacenamiento` queda sin
  permisos para `www-data`, SQLite no puede abrir/crear la BD.

## Cambios
- `app/nucleo/BaseDeDatos.php`:
  - Crea siempre el directorio padre de `RUTA_BD` si falta.
  - Comprueba la existencia real de la tabla `usuarios`; si no existe, aplica
    el esquema aunque el archivo SQLite ya exista.
  - Ejecuta despues las migraciones ligeras (`titular`, `foto`).
- `Dockerfile`:
  - Añade `CMD` que crea `almacenamiento/sesiones`, ajusta propiedad a
    `www-data` y arranca Apache.

## Verificacion
- Reproducido localmente con SQLite existente pero vacia; tras el fix `/equipos`
  renderiza correctamente.
- `php tests/correr.php`: 17 OK / 0 KO.
- HTTP temporal: `/`, `/equipos`, `/partidos`, `/campos`, `/ligas` respondieron
  200.
