# 2026-06-17 - Rediseño de la landing (hero a pantalla completa)

## Contexto
El usuario pidió "una landing en condiciones" con:
- Hero a pantalla completa que "resalte más".
- Diseño "más moderno".
- Enlaces sin subrayado visible o con efecto de aparecer gradualmente.
- Pidió usar la "skill de diseño": NO EXISTE en este entorno. Solo está `customize-opencode`
  (es para configurar opencode, no para diseño). Se le avisó y se procedió igualmente
  con el rediseño usando buenas prácticas de UI/UX.

## Cambios Realizados

### 1. `app/vistas/inicio/index.php`
Hero reescrito con:
- Eyebrow "La plataforma del fútbol amateur" con punto pulsante.
- H1 grande "Tu próximo partido / empieza aquí" (la segunda parte con degradado animado).
- Subtítulo.
- CTAs grandes (boton--xl): "Crear mi equipo gratis" (con flecha SVG) y "Ver partidos".
- Chips de confianza: "Sin coste de alta", "Sin descargas", "Hecho en España".
- Indicador "Descubrir ↓" al final con animación tipo "scroll mouse".
- 3 blobs decorativos (`hero__forma--a/b/c`) sobre el vídeo.
- `id="panel-ctx"` añadido a la sección panel-ctx para que el indicador haga scroll suave.

### 2. `public/css/estilos.css`
- `.hero` ahora ocupa 100dvh/100vh, full-width (escape del contenedor con
  `width:100vw; margin-left: calc(50% - 50vw); margin-top: calc(-1 * var(--e8))`).
- Nuevas clases: `.hero__eyebrow`, `.hero__titulo`, `.hero__titulo-degradado`,
  `.hero__subtitulo`, `.hero__acciones`, `.hero__chips`, `.hero__indicador`,
  `.hero__forma` con sus modificadores.
- Botón XL: `.boton--xl` (54px min-height) e icono opcional `.boton__icono` (se
  desplaza 3px al hacer hover).
- Cabecera: `.cabecera--transparente` (transparente sobre el hero, texto blanco
  plano) y `.cabecera--solida` (con fondo). Se aplica desde `public/js/hero.js`.
- Reglas para `.js [data-revelar]` (cascada al cargar) y `.js .hero.hero.revelar`
  (el hero no se oculta, su contenido se anima vía data-revelar).
- Pseudo-elemento `.hero::before` con grid sutil de líneas (efecto textura).
- Animaciones: `hero-flotar` (blobs), `hero-pulso` (punto del eyebrow),
  `hero-degradado` (gradiente animado del título), `hero-raton` (indicador).
- `.boton--enlace` ahora usa el mismo patrón de subrayado fade-in que `a`.
- Responsive: en ≤720px la cabecera vuelve a ser sólida siempre (legibilidad
  móvil). En ≤860px el hero reduce tipografía y padding.
- `prefers-reduced-motion`: oculta blobs y vídeo (solo queda el póster).

### 3. `public/js/animaciones-scroll.js`
Reescrito para soportar dos modos:
- Cascada al cargar: cualquier `[data-revelar]` dentro de un `.revelar` se
  observa con IntersectionObserver (threshold 0.05) y se le aplica un
  `transition-delay` de 120ms × índice (con tope de 6).
- Reveal al scroll: las secciones `.revelar:not(.hero)` siguen apareciendo
  al entrar en el viewport (threshold 0.15).
El hero queda fuera del observer para que no parpadee al cargar.

### 4. `public/js/hero.js` (nuevo)
IIFE que:
- Sale silenciosamente si no hay `.cabecera` o `.hero` (seguro cargarlo en todas
  las páginas).
- Aplica `cabecera--transparente` / `cabecera--solida` según la posición del hero
  en el viewport (usando `getBoundingClientRect` con `requestAnimationFrame` para
  no spamear el evento scroll).

### 5. `app/vistas/layout.php`
- Carga `public/js/hero.js` después de `animaciones-scroll.js`.

## Verificación
- Pruebas: `16 OK / 0 KO` (no se rompió nada en los modelos).
- CSS balanceado (243 llaves abrir / 243 cerrar).
- JS balanceado.
- Página servida correctamente (200 en /, /equipos, /partidos, /iniciar-sesion, /registro).
- Assets: CSS, JS, imagen del póster y vídeo (12.4 MB) servidos con 200.
- Captura con Edge headless confirma que el hero se ve correctamente en modo claro:
  navbar verde translúcido, título/subtítulo/CTAs/chips legibles, indicador "DESCUBRIR" al final.

## Fix de contraste (2026-06-17, segunda iteración)
El usuario reportó que el navbar no se veía en la parte alta del hero en modo claro.
El problema era doble:

1. **Bug de z-index en el hero:** el velo oscuro estaba en `.hero__fondo` con
   `z-index: -1` y el `<video>` sin z-index, por lo que el vídeo se pintaba
   *encima* del velo. Resultado: el póster claro del campo de fútbol se veía
   sin oscurecer y las letras blancas desaparecían sobre el cielo claro.
   **Fix:** se reescribieron los z-index explícitos:
   `hero__fondo:0, hero__video:1, hero__velo:2, hero__forma:3, hero__contenido:4, hero__indicador:5`.
   Además, el `.hero` tiene ahora `background-color: #061b13` con dos
   `radial-gradient` sutiles como red de seguridad, por si el póster/vídeo/velo
   fallan: el fondo nunca será claro.

2. **Cabecera invisible sobre el hero:** en lugar de hacer la cabecera totalmente
   transparente, ahora `.cabecera--transparente` lleva un fondo verde translúcido
   (`color-mix(in srgb, var(--color-principal-oscuro) 78%, transparent)`) con
   `backdrop-filter: saturate(150%) blur(12px)`. El logo y los enlaces se leen
   siempre y, de paso, la marca se refuerza con un toque de color en la parte
   superior. Se eliminó el override móvil que forzaba la cabecera sólida en
   ≤720px (ya no hace falta con el tinte verde).

Texto del hero reforzado con `text-shadow` doble (`0 2px 6px rgba(0,0,0,0.55), 0 8px 28px rgba(0,0,0,0.45)`)
para que el blanco se lea incluso si el velo se aclara puntualmente.

## Aprendizajes
- `min-height: 100vh; min-height: 100dvh;` (cascada) funciona en navegadores
  antiguos y modernos, mejor que `@supports` para este caso.
- Para escapar del contenedor sin tocar `layout.php`: `width: 100vw; margin-left: calc(50% - 50vw);`.
- `text-wrap: balance` (Chrome 114+, Firefox 121+) y `color-mix` (Chrome 111+):
  los navegadores antiguos los ignoran sin romper nada.
- `text-decoration: underline` con `text-underline-offset` no permite animar la
  entrada; el patrón con `background-image + background-size` (que ya usaba el
  proyecto) sí lo permite. Lo extendimos a `.boton--enlace`.
