---
name: threejs-3d-web
description: Gráficos 3D en el navegador con Three.js y React Three Fiber — escenas, materiales y efectos
type: skill
tags: [threejs, 3d, webgl, react-three-fiber, r3f, animacion]
---

# Skill: Three.js & React Three Fiber

Gráficos 3D en el navegador para fondos interactivos, portfolios y experiencias inmersivas.

---

## Three.js puro — escena básica

```html
<script type="importmap">
  {"imports": {"three": "https://cdn.jsdelivr.net/npm/three@0.165.0/build/three.module.js"}}
</script>
<script type="module">
import * as THREE from 'three';

// Escena, cámara, renderer
const scene    = new THREE.Scene();
const camera   = new THREE.PerspectiveCamera(75, innerWidth / innerHeight, 0.1, 1000);
const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });

renderer.setSize(innerWidth, innerHeight);
renderer.setPixelRatio(Math.min(devicePixelRatio, 2));
document.body.appendChild(renderer.domElement);
camera.position.z = 5;

// Malla básica
const geometry = new THREE.IcosahedronGeometry(1.5, 4);
const material  = new THREE.MeshStandardMaterial({
  color: 0x7c3aed,
  wireframe: false,
  roughness: 0.3,
  metalness: 0.8
});
const mesh = new THREE.Mesh(geometry, material);
scene.add(mesh);

// Luz
scene.add(new THREE.AmbientLight(0xffffff, 0.5));
const dirLight = new THREE.DirectionalLight(0xffffff, 2);
dirLight.position.set(5, 5, 5);
scene.add(dirLight);

// Loop de animación
function animate() {
  requestAnimationFrame(animate);
  mesh.rotation.x += 0.003;
  mesh.rotation.y += 0.005;
  renderer.render(scene, camera);
}
animate();

// Responsive
window.addEventListener('resize', () => {
  camera.aspect = innerWidth / innerHeight;
  camera.updateProjectionMatrix();
  renderer.setSize(innerWidth, innerHeight);
});
</script>
```

---

## React Three Fiber (R3F) — setup

```bash
npm install three @react-three/fiber @react-three/drei
```

```tsx
// app/page.tsx
import { Canvas } from "@react-three/fiber";
import { Scene } from "@/components/Scene";

export default function Home() {
  return (
    <main className="w-full h-screen bg-black">
      <Canvas camera={{ position: [0, 0, 5], fov: 75 }}>
        <Scene />
      </Canvas>
    </main>
  );
}
```

```tsx
// components/Scene.tsx
import { useRef } from "react";
import { useFrame } from "@react-three/fiber";
import { Environment, Float } from "@react-three/drei";
import type { Mesh } from "three";

export function Scene() {
  const meshRef = useRef<Mesh>(null);

  useFrame((_, delta) => {
    if (meshRef.current) {
      meshRef.current.rotation.y += delta * 0.5;
    }
  });

  return (
    <>
      <Environment preset="city" />
      <ambientLight intensity={0.5} />
      <directionalLight position={[5, 5, 5]} intensity={2} />

      <Float speed={2} rotationIntensity={0.5} floatIntensity={1}>
        <mesh ref={meshRef}>
          <icosahedronGeometry args={[1.5, 4]} />
          <meshStandardMaterial
            color="#7c3aed"
            roughness={0.2}
            metalness={0.9}
          />
        </mesh>
      </Float>
    </>
  );
}
```

---

## Partículas / campo de estrellas

```tsx
import { useMemo, useRef } from "react";
import { useFrame } from "@react-three/fiber";
import * as THREE from "three";

export function Stars({ count = 3000 }) {
  const positions = useMemo(() => {
    const arr = new Float32Array(count * 3);
    for (let i = 0; i < count; i++) {
      arr[i * 3]     = (Math.random() - 0.5) * 100;
      arr[i * 3 + 1] = (Math.random() - 0.5) * 100;
      arr[i * 3 + 2] = (Math.random() - 0.5) * 100;
    }
    return arr;
  }, [count]);

  const ref = useRef<THREE.Points>(null);
  useFrame((_, delta) => {
    if (ref.current) ref.current.rotation.y += delta * 0.02;
  });

  return (
    <points ref={ref}>
      <bufferGeometry>
        <bufferAttribute attach="attributes-position" args={[positions, 3]} />
      </bufferGeometry>
      <pointsMaterial size={0.08} color="#ffffff" transparent opacity={0.7} />
    </points>
  );
}
```

---

## Post-procesado con @react-three/postprocessing

```bash
npm install @react-three/postprocessing
```

```tsx
import { EffectComposer, Bloom, ChromaticAberration } from "@react-three/postprocessing";

// Dentro del Canvas:
<EffectComposer>
  <Bloom
    luminanceThreshold={0.6}
    luminanceSmoothing={0.9}
    intensity={1.5}
  />
  <ChromaticAberration offset={[0.002, 0.002]} />
</EffectComposer>
```

---

## Shader personalizado — gradiente animado

```tsx
import { shaderMaterial } from "@react-three/drei";
import { extend, useFrame } from "@react-three/fiber";
import { useRef } from "react";

const GradientMaterial = shaderMaterial(
  { uTime: 0 },
  // vertex shader
  `varying vec2 vUv;
   void main() { vUv = uv; gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0); }`,
  // fragment shader
  `uniform float uTime;
   varying vec2 vUv;
   void main() {
     vec3 a = vec3(0.49, 0.23, 0.93);  // violeta
     vec3 b = vec3(0.93, 0.28, 0.60);  // rosa
     float t = (sin(vUv.x * 3.14 + uTime) + 1.0) * 0.5;
     gl_FragColor = vec4(mix(a, b, t), 1.0);
   }`
);

extend({ GradientMaterial });

export function GradientSphere() {
  const matRef = useRef<any>(null);
  useFrame(({ clock }) => {
    if (matRef.current) matRef.current.uTime = clock.elapsedTime;
  });

  return (
    <mesh>
      <sphereGeometry args={[2, 64, 64]} />
      {/* @ts-ignore */}
      <gradientMaterial ref={matRef} />
    </mesh>
  );
}
```

---

## Performance tips

| Problema | Solución |
|---|---|
| FPS bajo | Reducir `pixelRatio` a `Math.min(dpr, 2)` |
| Muchos draw calls | Usar `instancedMesh` para objetos repetidos |
| Geometrías pesadas | `geometry.dispose()` al desmontar |
| Texturas grandes | Comprimir con `ktx2` + `KTX2Loader` |
| Móvil | Detectar con `gl.getParameter` y bajar calidad |
