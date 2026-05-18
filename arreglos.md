# Arreglos pendientes

Auditoría de limpieza del proyecto — 2026-05-18.

---

## Alta prioridad

### 1. Referencias muertas a `arreglos.md` en `README.md`

El archivo `arreglos.md` fue eliminado pero el README aún lo referencia en dos sitios:
- **Línea 404**: `ver [arreglos.md](arreglos.md)` dentro de la sección Roadmap v3.
- **Línea 431**: `se enumeran en [arreglos.md](arreglos.md)` dentro de Notas de Versión.

**Acción**: Eliminar ambas referencias o redirigir a este mismo archivo.

### 2. Referencia a `arreglos.md` en `.dockerignore`

- **Línea 4**: `arreglos.md` — el archivo no existía; ahora existe de nuevo. Si se quiere excluir del contenedor, mantener. Si no, eliminar la línea.

**Acción**: Decidir si `arreglos.md` debe ir en la imagen Docker o no.

### 3. `storage/fastplay.sqlite` trackeado por git

El `.gitignore` tiene la regla `storage/*.sqlite` pero el archivo fue commiteado antes de añadir la regla. Sigue apareciendo en el historial y se trackea en cada cambio.

**Acción**:
```
git rm --cached "storage/fastplay.sqlite"
```

### 4. `.claude/settings.local.json` trackeado por git

Contiene rutas locales de máquina (`C:\xampp\htdocs\...`) y no está protegido por `.gitignore`. El directorio `.claude/` no aparece en `.gitignore`.

**Acción**:
- Añadir `.claude/` a `.gitignore`.
- Ejecutar `git rm --cached ".claude/settings.local.json"`.

---

## Media prioridad

### 5. Dos directorios de memoria del agente

- `memory/` — contiene `2026-05-18.md` y `project_db_dialects.md` (archivos reales de sesión).
- `.agent/memory/` — solo contiene `.gitkeep` (vacío).

`AGENTS.md` instruye escribir resúmenes en `.agent/memory/YYYY-MM-DD.md`, pero el archivo real fue escrito en `memory/`.

**Acción**: Consolidar en una sola ubicación:
- Opción A: Mover `memory/*` a `.agent/memory/` y eliminar `memory/`.
- Opción B: Mover `.agent/memory/.gitkeep` a `memory/`, eliminar `.agent/memory/` y actualizar `AGENTS.md`.

### 6. Dos archivos `MEMORY.md`

- `MEMORY.md` (raíz) — 1 línea, puntero del sistema Engram.
- `.agent/MEMORY.md` — 35 líneas, template de memoria a largo plazo del agente.

**Acción**: Renombrar o consolidar. Sugerencia: mantener `.agent/MEMORY.md` como template del agente y renombrar el de raíz a `ENGRAM.md` o similar para evitar confusión.

### 7. Directorio `public/frames-webp/` vacío

0 archivos. Ya está en `.gitignore`. No sirve para nada.

**Acción**: Eliminar el directorio.

---

## Baja prioridad

### 8. CSP con `unsafe-inline`

`config/config.php` incluye `'unsafe-inline'` en `style-src` y `script-src`. El propio comentario en el código reconoce que es temporal mientras se externalizan estilos y scripts inline.

**Acción**: Mover todos los `<style>` y `on*` handlers a archivos externos, luego eliminar `unsafe-inline` del CSP.

---

## Resumen rápido de comandos

```bash
# Alta prioridad
git rm --cached "storage/fastplay.sqlite"
git rm --cached ".claude/settings.local.json"

# Añadir al .gitignore (línea nueva)
# .claude/

# Eliminar directorio vacío
rm -r "public/frames-webp/"
```
