---
name: landing-animada
description: Crea una landing page animada con HTML, Tailwind CSS y GSAP desde cero
type: skill
tags: [html, tailwind, gsap, animacion, landing]
---

# Skill: Landing Page Animada

Crea una landing page animada y responsiva usando HTML5, Tailwind CSS y GSAP.

## Stack
- HTML5 semántico
- Tailwind CSS (via CDN o instalación local)
- GSAP + ScrollTrigger para animaciones
- Fuentes: Google Fonts (Inter, Syne, o similar)

## Estructura base

```html
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Landing Animada</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet" />
</head>
<body class="bg-black text-white font-[Inter] overflow-x-hidden">

  <!-- Hero Section -->
  <section id="hero" class="min-h-screen flex flex-col items-center justify-center text-center px-6">
    <h1 class="hero-title text-6xl font-extrabold leading-tight opacity-0">
      Tu Título <span class="text-violet-500">Aquí</span>
    </h1>
    <p class="hero-sub mt-4 text-xl text-gray-400 max-w-xl opacity-0">
      Subtítulo descriptivo que complementa el mensaje principal.
    </p>
    <a href="#" class="hero-cta mt-8 px-8 py-4 bg-violet-600 hover:bg-violet-500 rounded-full font-semibold transition opacity-0">
      Comenzar
    </a>
  </section>

  <!-- Features Section -->
  <section id="features" class="py-24 px-6 max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8">
    <!-- Feature cards aquí -->
  </section>

  <script>
    gsap.registerPlugin(ScrollTrigger);

    // Animación de entrada del hero
    const tl = gsap.timeline({ defaults: { ease: "power3.out", duration: 1 } });
    tl.to(".hero-title", { opacity: 1, y: 0, from: { y: 60 } })
      .to(".hero-sub",   { opacity: 1, y: 0 }, "-=0.6")
      .to(".hero-cta",   { opacity: 1, y: 0 }, "-=0.4");

    // Animaciones al scroll para features
    gsap.utils.toArray("#features > *").forEach((card, i) => {
      gsap.from(card, {
        scrollTrigger: { trigger: card, start: "top 85%" },
        opacity: 0,
        y: 50,
        duration: 0.8,
        delay: i * 0.15,
        ease: "power2.out"
      });
    });
  </script>
</body>
</html>
```

## Patrones de animación recomendados

| Efecto | GSAP snippet |
|---|---|
| Fade in desde abajo | `gsap.from(el, { opacity: 0, y: 60, duration: 1 })` |
| Stagger de lista | `gsap.from(items, { opacity: 0, stagger: 0.1 })` |
| Parallax suave | `gsap.to(bg, { y: -100, scrollTrigger: { scrub: true } })` |
| Texto letra a letra | Usa SplitText plugin (GSAP Club) |

## Checklist de accesibilidad
- [ ] Respetar `prefers-reduced-motion` con `matchMedia`
- [ ] Contraste de colores WCAG AA (ratio ≥ 4.5:1)
- [ ] Todos los elementos interactivos accesibles por teclado
- [ ] `alt` en todas las imágenes

```js
// Deshabilitar animaciones si el usuario lo prefiere
const mm = gsap.matchMedia();
mm.add("(prefers-reduced-motion: no-preference)", () => {
  // animaciones aquí
});
```
