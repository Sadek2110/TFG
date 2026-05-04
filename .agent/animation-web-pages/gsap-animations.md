---
name: gsap-animations
description: Patrones y recetas de animación con GSAP 3 y ScrollTrigger
type: skill
tags: [gsap, animacion, scroll, timeline, javascript]
---

# Skill: GSAP Animations

Referencia rápida de patrones GSAP 3 para proyectos web profesionales.

## Setup

```bash
# NPM
npm install gsap

# CDN
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
```

```js
import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
gsap.registerPlugin(ScrollTrigger);
```

---

## Timelines

```js
// Secuencia de animaciones coordinadas
const tl = gsap.timeline({ defaults: { duration: 0.8, ease: "power2.out" } });

tl.from(".nav",    { y: -80, opacity: 0 })
  .from(".title",  { x: -60, opacity: 0 }, "-=0.4")   // overlap 0.4s
  .from(".text",   { y:  40, opacity: 0 }, "+=0.2")   // delay 0.2s
  .from(".button", { scale: 0.8, opacity: 0 });
```

---

## ScrollTrigger — Animaciones al hacer scroll

```js
// Fade in al entrar en viewport
gsap.from(".card", {
  scrollTrigger: {
    trigger: ".card",
    start: "top 80%",      // cuando el top del elemento llega al 80% del viewport
    end: "bottom 20%",
    toggleActions: "play none none reverse"
  },
  opacity: 0,
  y: 60,
  duration: 1
});

// Animación scrub (vinculada al scroll)
gsap.to(".parallax-bg", {
  yPercent: -30,
  ease: "none",
  scrollTrigger: {
    trigger: ".section",
    start: "top bottom",
    end: "bottom top",
    scrub: 1   // suavidad del scrub en segundos
  }
});

// Pin (fijar elemento durante el scroll)
gsap.to(".progress-bar", {
  scaleX: 1,
  transformOrigin: "left center",
  ease: "none",
  scrollTrigger: {
    trigger: "body",
    start: "top top",
    end: "bottom bottom",
    scrub: true
  }
});
```

---

## Stagger — Animar grupos de elementos

```js
// Aparición escalonada de tarjetas
gsap.from(".feature-card", {
  scrollTrigger: { trigger: ".features", start: "top 70%" },
  opacity: 0,
  y: 40,
  duration: 0.6,
  stagger: {
    amount: 0.8,     // tiempo total repartido entre todos
    from: "start",   // "start" | "end" | "center" | "random"
    grid: "auto"     // útil para grids 2D
  }
});
```

---

## Easings más usados

| Nombre | Efecto |
|---|---|
| `power2.out` | Desaceleración natural — uso general |
| `power3.inOut` | Arranque y frenado suave |
| `elastic.out(1, 0.3)` | Rebote elástico |
| `back.out(1.7)` | Ligero overshoot |
| `expo.out` | Desaceleración rápida y pronunciada |
| `none` / `linear` | Velocidad constante — para scrub |

---

## Hover interactivo

```js
document.querySelectorAll(".btn").forEach(btn => {
  btn.addEventListener("mouseenter", () =>
    gsap.to(btn, { scale: 1.05, duration: 0.25, ease: "power2.out" })
  );
  btn.addEventListener("mouseleave", () =>
    gsap.to(btn, { scale: 1, duration: 0.25, ease: "power2.in" })
  );
});
```

---

## Texto animado (sin plugin)

```js
// Animar cada palabra
const words = document.querySelector(".title").textContent.split(" ");
document.querySelector(".title").innerHTML = words
  .map(w => `<span class="word" style="display:inline-block">${w}&nbsp;</span>`)
  .join("");

gsap.from(".word", {
  opacity: 0,
  y: 30,
  rotationX: -90,
  stagger: 0.07,
  duration: 0.6,
  ease: "back.out(1.5)"
});
```

---

## Prefers-reduced-motion

```js
const mm = gsap.matchMedia();

mm.add("(prefers-reduced-motion: no-preference)", () => {
  // todas tus animaciones aquí
  gsap.from(".hero", { opacity: 0, y: 60, duration: 1 });
});

mm.add("(prefers-reduced-motion: reduce)", () => {
  // versión sin movimiento
  gsap.set(".hero", { opacity: 1 });
});
```
