---
name: FastPlay tiene tres dialectos del mismo schema
description: Schema base SQLite (Database.php) más volcados equivalentes en PostgreSQL y MySQL en database/
type: project
---

FastPlay mantiene **tres versiones del mismo schema** que deben permanecer sincronizadas:

1. **SQLite** — `app/core/Database.php` (auto-migración + seed en PHP, es la BD viva del proyecto)
2. **PostgreSQL** — `database/fastplay_postgres.sql` (volcado portable con ENUMs, CITEXT, pgcrypto, triggers)
3. **MySQL** — `database/fastplay_mysql.sql` (volcado portable con ENUMs inline, utf8mb4, bcrypt pre-calculado)

**Why:** El usuario pidió tener portabilidad entre BDs sin atarse al SQLite del runtime PHP. El seed de datos demo (admin/demo + 6 jugadores + 8 equipos + 4 ligas + 5 campos + 4 partidos) es **idéntico** en los tres dialectos.

**How to apply:**
- Si se modifica el schema en `Database.php`, actualizar también los dos `.sql` para mantener paridad.
- En MySQL no se puede usar `pgcrypto`/`crypt()` para bcrypt — los hashes del seed están **pre-calculados** con `password_hash($pwd, PASSWORD_BCRYPT, ['cost'=>10])` y verificados con `password_verify()`. Si se cambia un password en el seed, regenerar el hash con PHP.
- En MySQL `updated_at` usa `ON UPDATE CURRENT_TIMESTAMP` nativo (no hace falta trigger como en Postgres).
- Las CHECK constraints requieren MySQL 8.0.16+ (en 5.7/8.0.15 se parsean pero se ignoran silenciosamente).
- El view `v_upcoming_matches` filtra solo por `('pending','confirmed')` — el SQL Postgres original incluía `'in_progress'` pero ese valor no existe en el ENUM `match_status`.

**Passwords seed (dev only):**
- `admin@fastplay.es` → `Admin1234!`
- todos los demás (`demo@…`, `lucia@…`, etc.) → `Demo1234!`
