---
name: framer-motion-react
description: Animaciones declarativas en React con Framer Motion — componentes, gestos y transiciones de página
type: skill
tags: [react, framer-motion, animacion, typescript, nextjs]
---

# Skill: Framer Motion para React

Animaciones de alto nivel en React/Next.js con Framer Motion.

## Instalación

```bash
npm install framer-motion
```

---

## Componentes básicos

```tsx
import { motion } from "framer-motion";

// Fade in simple
<motion.div
  initial={{ opacity: 0, y: 20 }}
  animate={{ opacity: 1, y: 0 }}
  transition={{ duration: 0.5, ease: "easeOut" }}
>
  Contenido
</motion.div>

// Animación al salir del DOM
<motion.div
  initial={{ opacity: 0 }}
  animate={{ opacity: 1 }}
  exit={{ opacity: 0, scale: 0.95 }}
>
  Contenido
</motion.div>
```

---

## Variantes — sistema de animación escalable

```tsx
const containerVariants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: {
      staggerChildren: 0.1,   // hijos se animan con retraso
      delayChildren: 0.2
    }
  }
};

const itemVariants = {
  hidden: { opacity: 0, y: 20 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.5 } }
};

function FeatureList() {
  return (
    <motion.ul variants={containerVariants} initial="hidden" animate="visible">
      {items.map(item => (
        <motion.li key={item.id} variants={itemVariants}>
          {item.text}
        </motion.li>
      ))}
    </motion.ul>
  );
}
```

---

## Animaciones al hacer scroll (whileInView)

```tsx
<motion.div
  initial={{ opacity: 0, y: 50 }}
  whileInView={{ opacity: 1, y: 0 }}
  viewport={{ once: true, amount: 0.3 }}
  transition={{ duration: 0.7, ease: "easeOut" }}
>
  Sección de contenido
</motion.div>
```

---

## Gestos interactivos

```tsx
<motion.button
  whileHover={{ scale: 1.05, backgroundColor: "#7c3aed" }}
  whileTap={{ scale: 0.97 }}
  transition={{ type: "spring", stiffness: 400, damping: 17 }}
  className="px-6 py-3 rounded-full bg-violet-600 text-white"
>
  Acción Principal
</motion.button>
```

---

## Transiciones de página (Next.js App Router)

```tsx
// app/layout.tsx
"use client";
import { AnimatePresence } from "framer-motion";
import { usePathname } from "next/navigation";

export default function RootLayout({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  return (
    <html>
      <body>
        <AnimatePresence mode="wait">
          <motion.div
            key={pathname}
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -10 }}
            transition={{ duration: 0.3 }}
          >
            {children}
          </motion.div>
        </AnimatePresence>
      </body>
    </html>
  );
}
```

---

## useAnimation — control programático

```tsx
import { motion, useAnimation } from "framer-motion";
import { useEffect } from "react";

function PulsingDot() {
  const controls = useAnimation();

  useEffect(() => {
    controls.start({
      scale: [1, 1.3, 1],
      transition: { repeat: Infinity, duration: 1.5, ease: "easeInOut" }
    });
  }, []);

  return <motion.div animate={controls} className="w-4 h-4 rounded-full bg-green-500" />;
}
```

---

## useScroll + useTransform — parallax

```tsx
import { motion, useScroll, useTransform } from "framer-motion";
import { useRef } from "react";

function ParallaxSection() {
  const ref = useRef(null);
  const { scrollYProgress } = useScroll({ target: ref, offset: ["start end", "end start"] });
  const y = useTransform(scrollYProgress, [0, 1], ["-20%", "20%"]);

  return (
    <div ref={ref} className="relative overflow-hidden h-screen">
      <motion.img
        src="/hero-bg.jpg"
        style={{ y }}
        className="absolute inset-0 w-full h-full object-cover scale-125"
      />
    </div>
  );
}
```

---

## Spring configs útiles

```ts
// Snappy
{ type: "spring", stiffness: 500, damping: 30 }

// Suave y natural
{ type: "spring", stiffness: 200, damping: 20 }

// Elástico
{ type: "spring", stiffness: 300, damping: 10, mass: 0.5 }
```
