---
name: ux-ui-design
description: Principios UX/UI, design tokens, microinteracciones y sistemas de diseño para interfaces modernas
type: skill
tags: [ux, ui, design-system, figma, accesibilidad, microinteracciones]
---

# Skill: UX/UI Design

Principios, patrones y herramientas para diseñar interfaces efectivas y elegantes.

---

## Design Tokens — base de todo sistema de diseño

```css
:root {
  /* Colores */
  --color-primary-50:  #f5f3ff;
  --color-primary-500: #7c3aed;
  --color-primary-600: #6d28d9;
  --color-primary-700: #5b21b6;
  --color-neutral-900: #0f0f0f;
  --color-neutral-800: #1a1a1a;
  --color-neutral-600: #525252;
  --color-neutral-200: #e5e5e5;

  /* Tipografía */
  --font-sans:   'Inter', system-ui, sans-serif;
  --font-display:'Syne', sans-serif;
  --text-xs:     0.75rem;
  --text-sm:     0.875rem;
  --text-base:   1rem;
  --text-lg:     1.125rem;
  --text-xl:     1.25rem;
  --text-2xl:    1.5rem;
  --text-4xl:    2.25rem;
  --text-6xl:    3.75rem;
  --leading-tight:  1.25;
  --leading-normal: 1.5;

  /* Espaciado (base 4px) */
  --space-1:  0.25rem;
  --space-2:  0.5rem;
  --space-4:  1rem;
  --space-6:  1.5rem;
  --space-8:  2rem;
  --space-12: 3rem;
  --space-16: 4rem;
  --space-24: 6rem;

  /* Bordes */
  --radius-sm: 6px;
  --radius-md: 12px;
  --radius-lg: 20px;
  --radius-full: 9999px;

  /* Sombras */
  --shadow-sm:  0 1px 3px rgba(0,0,0,0.12);
  --shadow-md:  0 4px 16px rgba(0,0,0,0.2);
  --shadow-lg:  0 12px 40px rgba(0,0,0,0.35);
  --shadow-glow: 0 0 30px rgba(124, 58, 237, 0.4);

  /* Duración de animaciones */
  --duration-fast:   150ms;
  --duration-normal: 300ms;
  --duration-slow:   600ms;
  --ease-out:        cubic-bezier(0.4, 0, 0.2, 1);
  --ease-spring:     cubic-bezier(0.34, 1.56, 0.64, 1);
}
```

---

## Microinteracciones esenciales

### Botón con feedback

```html
<button class="btn-primary" aria-label="Enviar formulario">
  <span class="btn-text">Enviar</span>
  <span class="btn-icon">→</span>
</button>
```

```css
.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-3, 0.75rem) var(--space-6);
  background: var(--color-primary-500);
  color: white;
  border: none;
  border-radius: var(--radius-full);
  font-weight: 600;
  cursor: pointer;
  transition: all var(--duration-normal) var(--ease-out);
  position: relative;
  overflow: hidden;
}

/* Ripple effect */
.btn-primary::after {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(255,255,255,0.15);
  opacity: 0;
  transition: opacity var(--duration-fast);
}

.btn-primary:active::after { opacity: 1; }
.btn-primary:hover { background: var(--color-primary-600); transform: translateY(-1px); }
.btn-primary:active { transform: translateY(0); }
.btn-primary:focus-visible { outline: 2px solid var(--color-primary-500); outline-offset: 3px; }
```

### Input con label animado (floating label)

```html
<div class="field">
  <input type="text" id="name" placeholder=" " class="field-input" />
  <label for="name" class="field-label">Nombre completo</label>
</div>
```

```css
.field { position: relative; }

.field-input {
  width: 100%;
  padding: 1.25rem 1rem 0.5rem;
  border: 1px solid var(--color-neutral-200);
  border-radius: var(--radius-md);
  background: transparent;
  font-size: var(--text-base);
  transition: border-color var(--duration-normal);
}

.field-label {
  position: absolute;
  left: 1rem;
  top: 1rem;
  font-size: var(--text-base);
  color: var(--color-neutral-600);
  pointer-events: none;
  transition: all var(--duration-normal) var(--ease-out);
}

.field-input:focus,
.field-input:not(:placeholder-shown) {
  border-color: var(--color-primary-500);
  outline: none;
}

.field-input:focus ~ .field-label,
.field-input:not(:placeholder-shown) ~ .field-label {
  top: 0.35rem;
  font-size: var(--text-xs);
  color: var(--color-primary-500);
}
```

---

## Principios de Motion Design

| Principio | Regla práctica |
|---|---|
| **Duración** | UI corta 100–200ms, transiciones 250–400ms, entradas 400–700ms |
| **Easing** | Entradas: `ease-out`. Salidas: `ease-in`. Continuos: `ease-in-out` |
| **Propósito** | Cada animación debe guiar atención o confirmar acción |
| **Jerarquía** | Elementos primarios se animan antes; secundarios con stagger |
| **Sutileza** | Amplitudes pequeñas (10–40px). Menos es más |

---

## Tipografía para interfaces

```css
/* Escala tipográfica fluida (sin media queries) */
.heading-xl {
  font-size: clamp(2.5rem, 5vw + 1rem, 5rem);
  line-height: var(--leading-tight);
  font-weight: 800;
  letter-spacing: -0.02em;
}

.heading-lg {
  font-size: clamp(1.75rem, 3vw + 0.5rem, 3rem);
  line-height: 1.2;
  font-weight: 700;
}

.body-text {
  font-size: clamp(1rem, 1.5vw, 1.125rem);
  line-height: var(--leading-normal);
  color: var(--color-neutral-600);
  max-width: 65ch;
}
```

---

## Accesibilidad — checklist

- [ ] **Contraste** mínimo 4.5:1 para texto normal, 3:1 para texto grande
- [ ] **Focus visible** en todos los elementos interactivos (`focus-visible`)
- [ ] **Tamaño de toque** mínimo 44×44px en móvil
- [ ] **`aria-label`** en iconos sin texto
- [ ] **`prefers-reduced-motion`** respetado
- [ ] **`prefers-color-scheme`** dark mode implementado
- [ ] Orden de tab lógico y sin trampas
- [ ] Roles ARIA correctos (`role="dialog"`, `aria-expanded`, etc.)

---

## Layout responsivo moderno

```css
/* Grid adaptativo sin media queries */
.auto-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(280px, 100%), 1fr));
  gap: var(--space-6);
}

/* Contenedor fluido */
.container {
  width: min(1200px, 100% - var(--space-8) * 2);
  margin-inline: auto;
}

/* Stack vertical consistente */
.stack > * + * { margin-top: var(--space-6); }
```
