# Revisión de diseño — FastPlay

> Revisión realizada el 2026-05-31 sobre el código (`home/index.php`, `scroll-anim.css`, `app.css`, partials) y la app en vivo en `fastplay.dksaa.com` (landing, login, registro, ligas, campos, términos).
>
> Cada punto incluye: **qué**, **dónde** (`archivo:línea`), **por qué importa** y **cómo arreglarlo**. Ordenado por prioridad.

---

## 🔴 Crítico — Coherencia y credibilidad

Estos no son "píxeles sueltos" sino incoherencias que un usuario/profesor nota enseguida.

### C1. La landing finge ser nacional, pero el producto es de Ceuta
La landing muestra ligas de **Madrid, Barcelona, Valencia, Sevilla** y "7 Ciudades", pero:
- El tagline del footer dice *"…jugadores, capitanes y equipos de **Ceuta**."* — [footer.php:29](app/views/partials/footer.php#L29)
- La página `/campos` es **100% de Ceuta** (Aiman Mohamed, Alfonso Murube, Hadú, Los Rosales…).
- Los términos legales hablan de "plataforma regional para Ceuta".

**Por qué importa:** el visitante ve "Liga Pro Madrid", se registra, y solo encuentra campos de Ceuta. Rompe la promesa de la home.

**Cómo arreglarlo (elige una línea narrativa y aplícala en todo):**
- **Opción A (recomendada, honesta):** posicionar la app como "el fútbol amateur de Ceuta". Cambiar las ligas demo de la home a ligas de Ceuta, el stat "7 Ciudades" → "X barrios/zonas", y mantener el tagline.
- **Opción B (nacional):** si la ambición es nacional, entonces los `/campos` y el tagline de Ceuta son los que hay que generalizar. Más trabajo y menos creíble a corto plazo.

### C2. Las cifras contradicen el copy
La sección Stats dice **"La comunidad crece — Miles de jugadores ya confían en FastPlay"** ([home/index.php:74-78](app/views/home/index.php#L74-L78)) pero el número real mostrado es **"18 Jugadores"** y **"4 Partidos"**.

**Por qué importa:** "Miles" junto a "18" destruye la credibilidad. Es el tipo de detalle que hace que la landing parezca un maqueta.

**Cómo arreglarlo:** suavizar el copy mientras las cifras sean bajas. Ej: *"La comunidad está despegando"* / *"Cada semana se suman nuevos jugadores"*. O quitar la sección de stats numéricos hasta tener volumen.

### C3. Asterisco sin nota al pie
El stat **"100% Gratis\*"** lleva un `*` que no tiene ninguna nota explicativa en toda la página.

**Por qué importa:** un asterisco huérfano sugiere "hay letra pequeña" y genera desconfianza, justo lo contrario de lo que busca.

**Cómo arreglarlo:** o quitar el `*`, o añadir una nota discreta (ej: *"\*La Liga Pro tiene coste por equipo"*) cerca del stat.

---

## 🟠 Bugs de código / cabos sueltos

### B1. La animación escalonada de las "features" no hace nada
En [home/index.php:115](app/views/home/index.php#L115) cada feature lleva `style="transition-delay: 240 + i*80 ms"`, pero `.scroll-feature-item` solo define `transition: transform .25s` ([scroll-anim.css:170](public/css/scroll-anim.css#L170)) y no tiene estado inicial `opacity:0`. Resultado: **el delay no produce ninguna entrada escalonada**; los 4 items aparecen a la vez (heredan el `--i:3` del bloque padre).

**Cómo arreglarlo:** o cablear la animación real…
```css
.scroll-feature-item { opacity: 0; transform: translateY(12px);
  transition: opacity .5s ease, transform .5s cubic-bezier(.22,1,.36,1); }
.scroll-section__inner.visible .scroll-feature-item { opacity: 1; transform: none; }
```
…o quitar el `transition-delay` inline para no dejar código muerto.

### B2. `.fp-btn-primary:hover` duplicado y en conflicto
Hay dos reglas: [app.css:187](public/css/app.css#L187) (glow verde) y [app.css:3965](public/css/app.css#L3965) (glow verde+oro). La segunda pisa el `box-shadow` de la primera. Funciona por orden de cascada, pero es frágil: quien edite la primera no verá efecto.

**Cómo arreglarlo:** fusionar ambas en una sola definición y borrar la duplicada.

---

## 🟡 Píxel / consistencia visual

### P1. Tamaños de texto inconsistentes y vía estilos inline
La descripción base es 16px ([scroll-anim.css:121](public/css/scroll-anim.css#L121)), pero:
- Hero la fuerza a **18px** inline ([home:47](app/views/home/index.php#L47))
- CTA final a **17px** inline ([home:201](app/views/home/index.php#L201))

Además el 18px del hero **no tiene override responsive**, así que en móvil sigue a 18px con `max-width:480px` y aprieta.

**Cómo arreglarlo:** crear clases `.scroll-desc--lg` / `.scroll-desc--cta` y darles tamaño + override en `@media (max-width:768px)`.

### P2. Exceso de estilos inline en `home/index.php`
Casi cada elemento lleva `style="..."` con valores en px (`font-size`, `max-width`, `padding`, `--i`…). No es un bug, pero contradice lo que presume el README ("animaciones extraídas a CSS, sin bloques inline críticos").

**Cómo arreglarlo:** mover los valores repetidos a clases utilitarias. Mantener solo `--i` inline (es índice de stagger, legítimo).

### P3. Badge "MÁS POPULAR" en `top:-10px`
[scroll-anim.css:376](public/css/scroll-anim.css#L376) — sobre una card con `padding-top:20px` y el icono justo debajo. Verificar en anchos intermedios que no roce el icono 🏆.

**Cómo arreglarlo:** subir el `padding-top` de `.scroll-pricing__option--pro` a ~28px o bajar el badge.

---

## 🔵 Accesibilidad y contraste

### A1. Texto gris demasiado pequeño / bajo contraste
- `.scroll-pricing__period` → `#6b7280` a **10px** ([scroll-anim.css:375](public/css/scroll-anim.css#L375)). Gris-500 a 10px sobre glass oscuro queda en el límite de WCAG AA.
- `.scroll-pricing__features li` → `#d1d5db` 12px (ok).
- "Elige tu nivel…" → `#9ca3af` 13px inline ([home:160](app/views/home/index.php#L160)).

**Cómo arreglarlo:** subir periodos a 11-12px y aclarar el gris a `#9ca3af`/`#cbd5e1`.

### A2. Falta requisitos de contraseña en el registro
La página `/registro` no muestra ninguna pista de requisitos de contraseña (longitud mínima, etc.). El usuario solo se entera al fallar el submit.

**Cómo arreglarlo:** añadir un texto de ayuda bajo el campo (ej: *"Mínimo 8 caracteres"*) y/o un medidor de fuerza.

---

## 🟢 Acabado / pendientes

### F1. Botones sociales placeholder
[footer.php:30-34](app/views/partials/footer.php#L30-L34) — los iconos `X / in / ig` son `<span>` con `title="Próximamente"`. En una landing pública dan sensación de inacabado.

**Cómo arreglarlo:** ocultarlos hasta tener los perfiles reales, o convertirlos en enlaces de verdad.

### F2. Footer "Cuenta" no se adapta a la sesión
[footer.php:9-14](app/views/partials/footer.php#L9-L14) — muestra siempre "Registrarse / Iniciar sesión", incluso con sesión iniciada.

**Cómo arreglarlo:** condicionar los enlaces según `current_user()` (logueado → "Cerrar sesión", etc.).

---

## Resumen de prioridades

| # | Tema | Severidad | Esfuerzo |
|---|------|-----------|----------|
| C1 | Ceuta vs ciudades nacionales | 🔴 Alta | Medio |
| C2 | "Miles" vs 18 jugadores | 🔴 Alta | Bajo |
| C3 | Asterisco huérfano | 🔴 Alta | Trivial |
| B1 | Animación features muerta | 🟠 Media | Bajo |
| B2 | `.fp-btn-primary:hover` duplicado | 🟠 Media | Bajo |
| P1 | Tamaños de texto inline inconsistentes | 🟡 Media | Bajo |
| P2 | Estilos inline en home | 🟡 Baja | Medio |
| P3 | Badge "MÁS POPULAR" solapamiento | 🟡 Baja | Trivial |
| A1 | Contraste de grises pequeños | 🔵 Media | Bajo |
| A2 | Requisitos de contraseña | 🔵 Baja | Bajo |
| F1 | Botones sociales placeholder | 🟢 Baja | Trivial |
| F2 | Footer "Cuenta" no condicionado | 🟢 Baja | Bajo |

**Recomendación de orden:** primero C2/C3 (triviales y de alto impacto en credibilidad), luego B1/B2 (cabos sueltos de código), después C1 (decisión de producto: Ceuta vs nacional), y el resto como pulido.
