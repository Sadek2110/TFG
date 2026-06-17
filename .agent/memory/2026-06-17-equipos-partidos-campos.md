# 2026-06-17 - Fichas de equipo, calendario de partidos y campos con foto

## Contexto
El usuario pidio ejecutar `Prompt.md`. El prompt solicitaba fichas de equipo,
union condicionada, herramientas de capitan, calendario en partidos, boton de
solicitud solo para capitanes y campos con foto/ubicacion.

## Cambios
- Rutas nuevas: `POST /equipos/{id}/unirse`, `POST /equipos/{id}/invitar` y
  `POST /equipos/{id}/actualizar-miembro`.
- `ControladorEquipos` bloquea crear otro equipo si el usuario ya capitanea uno,
  permite unirse solo a usuarios sin equipo, valida invitaciones y actualiza
  dorsal/posicion/titular por jugador.
- `ControladorPartidos` construye un calendario mensual server-side usando
  `?fecha=YYYY-MM-DD` y muestra el boton "Solicitar partido" solo a
  capitanes/admin.
- `ControladorCampos` y `Campo` aceptan una foto opcional.
- `BaseDeDatos` aplica migraciones ligeras aditivas para
  `miembros_equipo.titular` y `campos.foto`.
- Vistas y CSS actualizados para la ficha editable de equipo, calendario de
  partidos y tarjetas de campos con imagen y enlace a Google Maps.

## Verificacion
- `php tests/correr.php`: 17 OK / 0 KO.
- `php -l` en controladores, modelos y vistas tocadas: sin errores.
- Servidor PHP temporal: `/equipos`, `/equipos/1`, `/partidos`, `/campos`
  respondieron 200.
- Se regenero `base_datos/fastplay.sqlite` con `php base_datos/sembrar.php`.

## Notas
- La invitacion por correo no envia email real ni llama APIs externas; incorpora
  al usuario existente si la cuenta existe y esta libre.
- `git status` mostraba eliminaciones de documentos e imagenes no relacionadas;
  no se revirtieron.
