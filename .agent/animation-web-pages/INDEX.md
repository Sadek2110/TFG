# Skills — Páginas Web Animadas & UX/UI

Colección de referencias y recetas listas para usar en proyectos de diseño web animado.

---

## Skills disponibles

| Archivo | Descripción |
|---|---|
| [landing-animada.md](landing-animada.md) | Landing page completa con HTML + Tailwind + GSAP, estructura base y checklist de accesibilidad |
| [gsap-animations.md](gsap-animations.md) | Timelines, ScrollTrigger, stagger, hover interactivo y prefers-reduced-motion |
| [framer-motion-react.md](framer-motion-react.md) | Animaciones declarativas en React: variantes, whileInView, gestos, parallax, transiciones de página |
| [css-animations.md](css-animations.md) | @keyframes, transitions, glassmorphism, cursor animado, reveal al scroll — sin dependencias |
| [ux-ui-design.md](ux-ui-design.md) | Design tokens, microinteracciones, floating labels, principios de motion, tipografía fluida |
| [threejs-3d-web.md](threejs-3d-web.md) | Three.js puro, React Three Fiber, partículas, post-procesado, shaders GLSL |

---

## Cuándo usar cada stack

```
¿Proyecto React/Next.js?
  └─ Sí → Framer Motion + shadcn/ui
  └─ No → HTML/Tailwind + GSAP

¿Necesita 3D o WebGL?
  └─ React → React Three Fiber (@react-three/fiber)
  └─ Vanilla → Three.js

¿Solo efectos hover/reveal simples?
  └─ CSS puro (css-animations.md) — sin JS, máxima performance

¿Landing page desde cero?
  └─ landing-animada.md — plantilla lista
```

---

## Recursos externos

- [GSAP Docs](https://gsap.com/docs/v3/)
- [Framer Motion Docs](https://www.framer.com/motion/)
- [Three.js Docs](https://threejs.org/docs/)
- [React Three Fiber](https://docs.pmnd.rs/react-three-fiber)
- [Easings.net](https://easings.net/) — visualizador de curvas de animación
- [cubic-bezier.com](https://cubic-bezier.com/) — editor de easing personalizado
