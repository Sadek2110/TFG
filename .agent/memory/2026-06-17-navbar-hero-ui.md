# 2026-06-17 - Ajuste UI del navbar y limpieza del hero

## Contexto
El usuario pidió usar la "skill de diseño" para mejorar el navbar porque se veía
todo muy pegado. En este entorno no existe una skill de diseño real, así que se
trabajó con buenas prácticas UI/UX y las convenciones existentes del proyecto.
Después pidió quitar el degradado de color del hero para acercarlo a la captura
`public/imagenes/capturas/01_inicio_escritorio.png`.

## Cambios
- `public/css/estilos.css`:
  - Navbar de escritorio con más separación entre marca, navegación y acciones.
  - Enlaces del navbar con `min-height` estable, más padding y estados hover más claros.
  - Menú móvil con filas de 48px, padding más generoso y acciones apiladas sin quedar pegadas.
  - Hero sin `background-image` de halos verde/dorado.
  - Overlay del hero cambiado a velo oscuro neutro, sin colorear la foto/vídeo.
  - Blobs decorativos `.hero__forma` ocultos.
  - `.hero__titulo-degradado` deja de usar gradiente animado y pasa a verde sólido de marca.

## Verificación
- `git diff --check -- public/css/estilos.css`: sin errores.
- Balance CSS: 246 llaves de apertura / 246 de cierre.
- `php tests/correr.php`: 16 OK / 0 KO.
- La app respondió 200 en servidor PHP local durante la comprobación.
  Edge/Chrome headless no pudieron generar capturas por error de GPU del entorno Windows.
