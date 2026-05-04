---
name: css-animations
description: Animaciones CSS puras con @keyframes, transitions y custom properties — sin dependencias
type: skill
tags: [css, animacion, keyframes, transitions, tailwind]
---

# Skill: CSS Animations

Animaciones nativas en CSS — rápidas, sin dependencias, con aceleración por GPU.

---

## Transitions — hover y estados

```css
/* Transición suave para hover */
.btn {
  background: #7c3aed;
  transform: translateY(0);
  box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
  transition:
    background 0.25s ease,
    transform 0.25s ease,
    box-shadow 0.25s ease;
}

.btn:hover {
  background: #6d28d9;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(124, 58, 237, 0.5);
}

.btn:active {
  transform: translateY(0);
}
```

---

## @keyframes — animaciones continuas

```css
/* Fade in desde abajo */
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(30px); }
  to   { opacity: 1; transform: translateY(0); }
}

.hero-title {
  animation: fadeInUp 0.8s ease-out both;
}

/* Delay escalonado para listas */
.card:nth-child(1) { animation-delay: 0s; }
.card:nth-child(2) { animation-delay: 0.1s; }
.card:nth-child(3) { animation-delay: 0.2s; }


/* Pulso suave */
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0.5; }
}

.loading { animation: pulse 2s ease-in-out infinite; }


/* Rotación continua */
@keyframes spin {
  to { transform: rotate(360deg); }
}

.spinner { animation: spin 1s linear infinite; }


/* Float / levitar */
@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50%       { transform: translateY(-12px); }
}

.floating-card { animation: float 3s ease-in-out infinite; }


/* Gradiente animado en texto */
@keyframes gradientShift {
  0%   { background-position: 0% 50%; }
  50%  { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

.gradient-text {
  background: linear-gradient(90deg, #7c3aed, #ec4899, #f59e0b, #7c3aed);
  background-size: 300% 300%;
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  animation: gradientShift 4s ease infinite;
}
```

---

## CSS Custom Properties para temas animados

```css
:root {
  --color-primary: #7c3aed;
  --color-accent:  #ec4899;
  --radius:        12px;
  --shadow:        0 4px 20px rgba(0,0,0,0.3);
  --transition:    0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.card {
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  transition: transform var(--transition), box-shadow var(--transition);
}

.card:hover {
  transform: translateY(-4px) scale(1.01);
  box-shadow: 0 12px 40px rgba(0,0,0,0.4);
}
```

---

## Reveal al scroll (solo CSS con Intersection Observer)

```css
.reveal {
  opacity: 0;
  transform: translateY(40px);
  transition: opacity 0.7s ease, transform 0.7s ease;
}

.reveal.is-visible {
  opacity: 1;
  transform: translateY(0);
}
```

```js
const observer = new IntersectionObserver(
  entries => entries.forEach(e => e.isIntersecting && e.target.classList.add("is-visible")),
  { threshold: 0.15 }
);
document.querySelectorAll(".reveal").forEach(el => observer.observe(el));
```

---

## Glassmorphism

```css
.glass {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
}
```

---

## Cursor personalizado animado

```css
*, *::before, *::after { cursor: none; }

.cursor {
  width: 12px; height: 12px;
  background: #7c3aed;
  border-radius: 50%;
  position: fixed;
  pointer-events: none;
  transition: transform 0.15s ease, width 0.2s ease, height 0.2s ease;
  z-index: 9999;
}

.cursor.hovering {
  width: 36px; height: 36px;
  background: rgba(124, 58, 237, 0.3);
  border: 2px solid #7c3aed;
}
```

```js
const cursor = document.querySelector(".cursor");
document.addEventListener("mousemove", e => {
  cursor.style.left = e.clientX - cursor.offsetWidth / 2 + "px";
  cursor.style.top  = e.clientY - cursor.offsetHeight / 2 + "px";
});
document.querySelectorAll("a, button").forEach(el => {
  el.addEventListener("mouseenter", () => cursor.classList.add("hovering"));
  el.addEventListener("mouseleave", () => cursor.classList.remove("hovering"));
});
```

---

## Prefers-reduced-motion

```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```
