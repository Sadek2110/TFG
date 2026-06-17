# 2026-06-18 - Espaciado visual y limpieza del hero

## Contexto
El usuario reporto que las vistas tenian muy poco espacio entre secciones y
componentes. Tambien pidio quitar el texto "Descubrir" del hero y recortar un
poco el video por debajo porque aparecia la marca de agua de Veo.

## Cambios
- `app/vistas/inicio/index.php`: eliminado el enlace/indicador `hero__indicador`
  con el texto "Descubrir".
- `public/css/estilos.css`:
  - Nuevos tokens `--e20`, `--espacio-pagina-arriba`,
    `--espacio-pagina-abajo` y `--espacio-secciones`.
  - Mayor margen entre secciones y entre titulos, tablas, formularios y listas.
  - Mas `gap` y `padding` en tarjetas, listas, formularios, panel contextual,
    cifras, calendario, detalle de equipo y tarjetas de campos.
  - Ajuste responsive para que en movil los paddings vuelvan a valores
    razonables.
  - El video del hero se ancla arriba con `height: 112%` y
    `object-position: center top`, recortando la zona inferior sin modificar el
    archivo `.webm`.

## Verificacion
- `php -l app/vistas/inicio/index.php`: sin errores.
- `git diff --check -- app/vistas/inicio/index.php public/css/estilos.css`: sin
  errores de whitespace (solo avisos CRLF habituales de Git).
- `php tests/correr.php`: 17 OK / 0 KO.
- Servidor PHP temporal con job de PowerShell: `/` respondio 200.
- El navegador integrado no pudo abrirse por restriccion del sandbox de Windows
  (`CreateProcessAsUserW failed: 5`), asi que no se genero captura visual.
