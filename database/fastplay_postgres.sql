-- ============================================================================
-- FastPlay - Esquema completo PostgreSQL + datos de prueba
-- ----------------------------------------------------------------------------
-- Equivalente del schema SQLite original (app/core/Database.php) portado a
-- PostgreSQL con tipos nativos, ENUMs, CHECK constraints, indices y triggers.
-- Compatible con PostgreSQL 13+.
--
-- Uso rapido:
--   psql -U postgres -f fastplay_postgres.sql
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 0. CREACION DE LA BASE DE DATOS
-- ----------------------------------------------------------------------------
-- Ejecutar este bloque conectado a la BD `postgres` (o cualquier otra distinta
-- a `fastplay`). Una vez creada, reconectar a `fastplay` para el resto.
--
--   CREATE DATABASE fastplay
--       WITH ENCODING   = 'UTF8'
--            LC_COLLATE = 'es_ES.UTF-8'
--            LC_CTYPE   = 'es_ES.UTF-8'
--            TEMPLATE   = template0;
--   \c fastplay
-- ----------------------------------------------------------------------------

BEGIN;

-- Extensiones
CREATE EXTENSION IF NOT EXISTS citext;     -- emails case-insensitive
CREATE EXTENSION IF NOT EXISTS pgcrypto;   -- bcrypt para password_hash

-- ----------------------------------------------------------------------------
-- 1. LIMPIEZA (orden inverso por dependencias)
-- ----------------------------------------------------------------------------
DROP VIEW  IF EXISTS v_upcoming_matches CASCADE;
DROP VIEW  IF EXISTS v_league_standings CASCADE;

DROP TABLE IF EXISTS login_attempts     CASCADE;
DROP TABLE IF EXISTS user_achievements  CASCADE;
DROP TABLE IF EXISTS achievements       CASCADE;
DROP TABLE IF EXISTS chat_messages      CASCADE;
DROP TABLE IF EXISTS chat_rooms         CASCADE;
DROP TABLE IF EXISTS matches            CASCADE;
DROP TABLE IF EXISTS fields             CASCADE;
DROP TABLE IF EXISTS league_teams       CASCADE;
DROP TABLE IF EXISTS leagues            CASCADE;
DROP TABLE IF EXISTS team_members       CASCADE;
DROP TABLE IF EXISTS teams              CASCADE;
DROP TABLE IF EXISTS users              CASCADE;

DROP TYPE  IF EXISTS chat_room_type;
DROP TYPE  IF EXISTS field_surface;
DROP TYPE  IF EXISTS league_status;
DROP TYPE  IF EXISTS match_status;
DROP TYPE  IF EXISTS user_role;

-- ----------------------------------------------------------------------------
-- 2. TIPOS ENUMERADOS
-- ----------------------------------------------------------------------------
CREATE TYPE user_role      AS ENUM ('player', 'admin');
CREATE TYPE match_status   AS ENUM ('pending', 'confirmed', 'cancelled', 'finished');
CREATE TYPE league_status  AS ENUM ('open', 'in_progress');
CREATE TYPE field_surface  AS ENUM ('césped', 'sintético', 'tierra', 'cemento');
CREATE TYPE chat_room_type AS ENUM ('group', 'general', 'team', 'league', 'match_negotiation', 'direct');

-- ============================================================================
-- 3. TABLAS
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 3.1 USERS
-- ----------------------------------------------------------------------------
CREATE TABLE users (
    id              BIGSERIAL    PRIMARY KEY,
    name            VARCHAR(120) NOT NULL,
    email           CITEXT       NOT NULL UNIQUE,
    phone           VARCHAR(20),
    age             SMALLINT     CHECK (age IS NULL OR (age BETWEEN 14 AND 99)),
    city            VARCHAR(80),
    position        VARCHAR(40),
    password_hash   VARCHAR(255) NOT NULL,
    role            user_role    NOT NULL DEFAULT 'player',
    avatar          TEXT,
    bio             TEXT,
    dorsal          SMALLINT,
    height_cm       SMALLINT,
    goals           INTEGER      NOT NULL DEFAULT 0,
    assists         INTEGER      NOT NULL DEFAULT 0,
    created_at      TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);
CREATE INDEX idx_users_city ON users(city);
CREATE INDEX idx_users_role ON users(role);

-- ----------------------------------------------------------------------------
-- 3.2 TEAMS  (1 capitan -> N equipos; el equipo NO se borra al borrar capitan)
-- ----------------------------------------------------------------------------
CREATE TABLE teams (
    id          BIGSERIAL    PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    city        VARCHAR(80)  NOT NULL,
    badge       VARCHAR(10)  DEFAULT '🛡️',
    captain_id  BIGINT       NOT NULL,
    created_at  TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    CONSTRAINT fk_teams_captain FOREIGN KEY (captain_id)
        REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT uq_teams_name_city UNIQUE (name, city)
);
CREATE INDEX idx_teams_captain ON teams(captain_id);
CREATE INDEX idx_teams_city    ON teams(city);

-- ----------------------------------------------------------------------------
-- 3.3 TEAM_MEMBERS  (N:M users <-> teams)
-- ----------------------------------------------------------------------------
CREATE TABLE team_members (
    team_id    BIGINT      NOT NULL,
    user_id    BIGINT      NOT NULL,
    joined_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    PRIMARY KEY (team_id, user_id),
    CONSTRAINT fk_tm_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    CONSTRAINT fk_tm_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE INDEX idx_team_members_user ON team_members(user_id);

-- ----------------------------------------------------------------------------
-- 3.4 LEAGUES
-- ----------------------------------------------------------------------------
CREATE TABLE leagues (
    id          BIGSERIAL     PRIMARY KEY,
    name        VARCHAR(150)  NOT NULL,
    city        VARCHAR(80)   NOT NULL,
    pro         BOOLEAN       NOT NULL DEFAULT FALSE,
    prize       NUMERIC(10,2) CHECK (prize IS NULL OR prize >= 0),
    start_date  DATE          NOT NULL,
    end_date    DATE          NOT NULL,
    max_teams   INTEGER       NOT NULL DEFAULT 12 CHECK (max_teams BETWEEN 2 AND 64),
    status      league_status NOT NULL DEFAULT 'open',
    created_at  TIMESTAMPTZ   NOT NULL DEFAULT NOW(),
    CHECK (end_date >= start_date)
);
CREATE INDEX idx_leagues_city   ON leagues(city);
CREATE INDEX idx_leagues_status ON leagues(status);
CREATE INDEX idx_leagues_pro    ON leagues(pro);

-- ----------------------------------------------------------------------------
-- 3.5 LEAGUE_TEAMS  (clasificacion)
-- ----------------------------------------------------------------------------
CREATE TABLE league_teams (
    league_id      BIGINT      NOT NULL,
    team_id        BIGINT      NOT NULL,
    points         INTEGER     NOT NULL DEFAULT 0 CHECK (points  >= 0),
    played         INTEGER     NOT NULL DEFAULT 0 CHECK (played  >= 0),
    won            INTEGER     NOT NULL DEFAULT 0 CHECK (won     >= 0),
    drawn          INTEGER     NOT NULL DEFAULT 0 CHECK (drawn   >= 0),
    lost           INTEGER     NOT NULL DEFAULT 0 CHECK (lost    >= 0),
    gf             INTEGER     NOT NULL DEFAULT 0 CHECK (gf      >= 0),
    ga             INTEGER     NOT NULL DEFAULT 0 CHECK (ga      >= 0),
    registered_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    PRIMARY KEY (league_id, team_id),
    CONSTRAINT fk_lt_league FOREIGN KEY (league_id) REFERENCES leagues(id) ON DELETE CASCADE,
    CONSTRAINT fk_lt_team   FOREIGN KEY (team_id)   REFERENCES teams(id)   ON DELETE CASCADE,
    CHECK (won + drawn + lost = played)
);
CREATE INDEX idx_lt_team ON league_teams(team_id);

-- ----------------------------------------------------------------------------
-- 3.6 FIELDS
-- ----------------------------------------------------------------------------
CREATE TABLE fields (
    id           BIGSERIAL     PRIMARY KEY,
    name         VARCHAR(120)  NOT NULL,
    city         VARCHAR(80)   NOT NULL,
    address      VARCHAR(200),
    surface      field_surface NOT NULL DEFAULT 'césped',
    capacity     INTEGER       NOT NULL DEFAULT 22 CHECK (capacity > 0),
    hourly_rate  NUMERIC(8,2)  NOT NULL DEFAULT 0  CHECK (hourly_rate >= 0)
);
CREATE INDEX idx_fields_city ON fields(city);

-- ----------------------------------------------------------------------------
-- 3.7 MATCHES
-- ----------------------------------------------------------------------------
CREATE TABLE matches (
    id            BIGSERIAL    PRIMARY KEY,
    home_team_id  BIGINT       NOT NULL,
    away_team_id  BIGINT       NOT NULL,
    league_id     BIGINT,
    field_id      BIGINT,
    scheduled_at  TIMESTAMPTZ  NOT NULL,
    status        match_status NOT NULL DEFAULT 'pending',
    home_score    SMALLINT     CHECK (home_score IS NULL OR home_score >= 0),
    away_score    SMALLINT     CHECK (away_score IS NULL OR away_score >= 0),
    created_by    BIGINT       NOT NULL,
    created_at    TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    CONSTRAINT fk_match_home    FOREIGN KEY (home_team_id) REFERENCES teams(id)   ON DELETE CASCADE,
    CONSTRAINT fk_match_away    FOREIGN KEY (away_team_id) REFERENCES teams(id)   ON DELETE CASCADE,
    CONSTRAINT fk_match_league  FOREIGN KEY (league_id)    REFERENCES leagues(id) ON DELETE SET NULL,
    CONSTRAINT fk_match_field   FOREIGN KEY (field_id)     REFERENCES fields(id)  ON DELETE SET NULL,
    CONSTRAINT fk_match_creator FOREIGN KEY (created_by)   REFERENCES users(id)   ON DELETE CASCADE,
    CHECK (home_team_id <> away_team_id),
    CHECK (
        status <> 'finished'
        OR (home_score IS NOT NULL AND away_score IS NOT NULL)
    )
);
CREATE INDEX idx_matches_status    ON matches(status);
CREATE INDEX idx_matches_scheduled ON matches(scheduled_at);
CREATE INDEX idx_matches_league    ON matches(league_id);
CREATE INDEX idx_matches_field     ON matches(field_id);
CREATE INDEX idx_matches_home      ON matches(home_team_id);
CREATE INDEX idx_matches_away      ON matches(away_team_id);

-- ----------------------------------------------------------------------------
-- 3.8 CHAT (rooms + messages)
-- ----------------------------------------------------------------------------
CREATE TABLE chat_rooms (
    id          BIGSERIAL      PRIMARY KEY,
    type        chat_room_type NOT NULL DEFAULT 'group',
    name        VARCHAR(150)   NOT NULL,
    created_at  TIMESTAMPTZ    NOT NULL DEFAULT NOW()
);

CREATE TABLE chat_messages (
    id          BIGSERIAL   PRIMARY KEY,
    room_id     BIGINT      NOT NULL,
    user_id     BIGINT      NOT NULL,
    body        TEXT        NOT NULL CHECK (length(trim(body)) > 0),
    created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT fk_msg_room FOREIGN KEY (room_id) REFERENCES chat_rooms(id) ON DELETE CASCADE,
    CONSTRAINT fk_msg_user FOREIGN KEY (user_id) REFERENCES users(id)      ON DELETE CASCADE
);
CREATE INDEX idx_messages_room_time ON chat_messages(room_id, created_at);
CREATE INDEX idx_messages_user      ON chat_messages(user_id);

-- ----------------------------------------------------------------------------
-- 3.9 ACHIEVEMENTS (catalogo + asignaciones)
-- ----------------------------------------------------------------------------
CREATE TABLE achievements (
    id           BIGSERIAL    PRIMARY KEY,
    code         VARCHAR(50)  NOT NULL UNIQUE,
    name         VARCHAR(100) NOT NULL,
    description  TEXT         NOT NULL,
    icon         VARCHAR(10)  NOT NULL DEFAULT '🏅'
);

CREATE TABLE user_achievements (
    user_id         BIGINT      NOT NULL,
    achievement_id  BIGINT      NOT NULL,
    earned_at       TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    PRIMARY KEY (user_id, achievement_id),
    CONSTRAINT fk_ua_user FOREIGN KEY (user_id)        REFERENCES users(id)        ON DELETE CASCADE,
    CONSTRAINT fk_ua_ach  FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE
);
CREATE INDEX idx_user_achievements_ach ON user_achievements(achievement_id);

-- ----------------------------------------------------------------------------
-- 3.10 LOGIN_ATTEMPTS  (rate-limiting de logins)
-- ----------------------------------------------------------------------------
CREATE TABLE login_attempts (
    id            BIGSERIAL   PRIMARY KEY,
    ip            INET        NOT NULL,
    email         CITEXT      NOT NULL,
    success       BOOLEAN     NOT NULL DEFAULT FALSE,
    attempted_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX idx_login_attempts_email ON login_attempts(email, attempted_at);
CREATE INDEX idx_login_attempts_ip    ON login_attempts(ip,    attempted_at);

-- ----------------------------------------------------------------------------
-- 4. TRIGGER: refresca users.updated_at en cada UPDATE
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION set_updated_at() RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_users_updated
    BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

COMMIT;

-- ============================================================================
-- 5. SEED DATA  (equivalente del seeder PHP de Database.php)
-- ============================================================================

BEGIN;

-- ----------------------------------------------------------------------------
-- 5.1 USERS  (passwords bcrypt via pgcrypto, compatibles con PHP password_verify)
-- ----------------------------------------------------------------------------
INSERT INTO users (id, name, email, phone, age, city, position, password_hash, role) VALUES
  (1, 'Sadek Admin',  'admin@fastplay.es', '+34600000000', 28, 'Madrid',    'Mediocampo', crypt('Admin1234!', gen_salt('bf', 10)), 'admin'),
  (2, 'Jugador Demo', 'demo@fastplay.es',  '+34611111111', 24, 'Madrid',    'Delantero',  crypt('Demo1234!',  gen_salt('bf', 10)), 'player'),
  (3, 'Lucía Pérez',  'lucia@fastplay.es', '+34600123456', 22, 'Barcelona', 'Portera',    crypt('Demo1234!',  gen_salt('bf', 10)), 'player'),
  (4, 'Marc Costa',   'marc@fastplay.es',  '+34600234567', 27, 'Valencia',  'Defensa',    crypt('Demo1234!',  gen_salt('bf', 10)), 'player'),
  (5, 'Ana Ruiz',     'ana@fastplay.es',   '+34600345678', 26, 'Sevilla',   'Mediocampo', crypt('Demo1234!',  gen_salt('bf', 10)), 'player'),
  (6, 'Iván Soto',    'ivan@fastplay.es',  '+34600456789', 25, 'Bilbao',    'Delantero',  crypt('Demo1234!',  gen_salt('bf', 10)), 'player'),
  (7, 'Paula Gil',    'paula@fastplay.es', '+34600567890', 23, 'Zaragoza',  'Defensa',    crypt('Demo1234!',  gen_salt('bf', 10)), 'player'),
  (8, 'Hugo Marín',   'hugo@fastplay.es',  '+34600678901', 29, 'Málaga',    'Mediocampo', crypt('Demo1234!',  gen_salt('bf', 10)), 'player');

SELECT setval(pg_get_serial_sequence('users', 'id'), (SELECT MAX(id) FROM users));

-- ----------------------------------------------------------------------------
-- 5.2 TEAMS
-- ----------------------------------------------------------------------------
INSERT INTO teams (id, name, city, captain_id) VALUES
  (1, 'Madrid Real C.F.', 'Madrid',    2),
  (2, 'Barça Amateurs',   'Barcelona', 3),
  (3, 'Atlético Centro',  'Madrid',    1),
  (4, 'Sevilla Street',   'Sevilla',   5),
  (5, 'Valencia Calle',   'Valencia',  4),
  (6, 'Bilbao Norte',     'Bilbao',    6),
  (7, 'Zaragoza FC',      'Zaragoza',  7),
  (8, 'Málaga Costa',     'Málaga',    8);

SELECT setval(pg_get_serial_sequence('teams', 'id'), (SELECT MAX(id) FROM teams));

-- ----------------------------------------------------------------------------
-- 5.3 TEAM_MEMBERS
-- ----------------------------------------------------------------------------
INSERT INTO team_members (team_id, user_id) VALUES
  (1, 2), (2, 3), (3, 1), (4, 5),
  (5, 4), (6, 6), (7, 7), (8, 8),
  (3, 2);   -- demo tambien juega en Atletico Centro

-- ----------------------------------------------------------------------------
-- 5.4 LEAGUES
-- ----------------------------------------------------------------------------
INSERT INTO leagues (id, name, city, pro, prize, start_date, end_date) VALUES
  (1, 'Liga Pro Madrid 25/26',    'Madrid',    TRUE,  1500.00, '2026-03-01', '2026-06-30'),
  (2, 'Liga Pro Barcelona 25/26', 'Barcelona', TRUE,  1500.00, '2026-03-01', '2026-06-30'),
  (3, 'Liga Amistosa Valencia',   'Valencia',  FALSE, NULL,    '2026-03-01', '2026-06-30'),
  (4, 'Liga Amistosa Sevilla',    'Sevilla',   FALSE, NULL,    '2026-03-01', '2026-06-30');

SELECT setval(pg_get_serial_sequence('leagues', 'id'), (SELECT MAX(id) FROM leagues));

-- ----------------------------------------------------------------------------
-- 5.5 LEAGUE_TEAMS  (clasificacion sembrada coherente con played = w+d+l)
-- ----------------------------------------------------------------------------
INSERT INTO league_teams (league_id, team_id, points, played, won, drawn, lost, gf, ga) VALUES
  (1, 1, 9, 4, 3, 0, 1, 11, 5),
  (1, 3, 7, 4, 2, 1, 1,  8, 6),
  (1, 2, 3, 3, 1, 0, 2,  4, 7),
  (1, 4, 1, 3, 0, 1, 2,  3, 8),
  (2, 2, 6, 3, 2, 0, 1,  6, 3),
  (3, 5, 4, 3, 1, 1, 1,  4, 4),
  (3, 1, 2, 3, 0, 2, 1,  2, 5),
  (4, 4, 8, 4, 2, 2, 0,  7, 3);

-- ----------------------------------------------------------------------------
-- 5.6 FIELDS
-- ----------------------------------------------------------------------------
INSERT INTO fields (id, name, city, address, surface, capacity, hourly_rate) VALUES
  (1, 'La Cantera',        'Madrid',    'Av. de las Glorietas 12', 'césped',    22, 35.00),
  (2, 'Pista 4',            'Madrid',    'Polideportivo Centro',    'sintético', 14, 22.00),
  (3, 'Polideportivo Sur', 'Valencia',  'C/ del Mar, 3',           'césped',    22, 30.00),
  (4, 'Camp Nou Petit',    'Barcelona', 'C/ de Sants, 88',         'césped',    22, 40.00),
  (5, 'Sevilla Sur',       'Sevilla',   'Av. Heliópolis 21',       'tierra',    14, 18.00);

SELECT setval(pg_get_serial_sequence('fields', 'id'), (SELECT MAX(id) FROM fields));

-- ----------------------------------------------------------------------------
-- 5.7 MATCHES  (zonas horarias en CEST '+02')
-- ----------------------------------------------------------------------------
INSERT INTO matches (id, home_team_id, away_team_id, league_id, field_id, scheduled_at, status, home_score, away_score, created_by) VALUES
  (1, 1, 2, 1,    1, '2026-06-12 19:30:00+02', 'confirmed', NULL, NULL, 1),
  (2, 3, 4, 1,    2, '2026-06-15 21:00:00+02', 'finished',  3,    2,    1),
  (3, 5, 1, 3,    3, '2026-06-22 20:00:00+02', 'pending',   NULL, NULL, 1),
  (4, 2, 6, NULL, 4, '2026-07-02 18:00:00+02', 'confirmed', NULL, NULL, 1);

SELECT setval(pg_get_serial_sequence('matches', 'id'), (SELECT MAX(id) FROM matches));

-- ----------------------------------------------------------------------------
-- 5.8 ACHIEVEMENTS
-- ----------------------------------------------------------------------------
INSERT INTO achievements (id, code, name, description, icon) VALUES
  (1, 'first_goal', 'Primer gol', 'Marca tu primer gol oficial.',  '🎖️'),
  (2, 'hat_trick',  'Hat-trick',  '3 goles en un solo partido.',   '🏅'),
  (3, 'captain',    'Capitán',    'Crea y dirige un equipo.',      '🛡️'),
  (4, 'veteran',    'Veterano',   'Juega 10 partidos.',            '🎯'),
  (5, 'mvp',        'MVP',        'Mejor jugador en una jornada.', '🏆');

SELECT setval(pg_get_serial_sequence('achievements', 'id'), (SELECT MAX(id) FROM achievements));

INSERT INTO user_achievements (user_id, achievement_id) VALUES
  (2, 1),
  (2, 3);

-- ----------------------------------------------------------------------------
-- 5.9 CHAT
-- ----------------------------------------------------------------------------
INSERT INTO chat_rooms (id, type, name) VALUES
  (1, 'general',           'Lobby general'),
  (2, 'match_negotiation', 'Capitanes — partidos amistosos');

SELECT setval(pg_get_serial_sequence('chat_rooms', 'id'), (SELECT MAX(id) FROM chat_rooms));

INSERT INTO chat_messages (room_id, user_id, body) VALUES
  (1, 1, '¡Bienvenidos a FastPlay! Por aquí coordinamos cualquier duda.'),
  (1, 2, '¿Alguien para un 7v7 este finde en Madrid?'),
  (2, 3, 'Buscamos rival amistoso este sábado, Barça Amateurs disponibles.');

-- ----------------------------------------------------------------------------
-- 5.10 LOGIN_ATTEMPTS  (muestra para rate-limiter)
-- ----------------------------------------------------------------------------
INSERT INTO login_attempts (ip, email, success) VALUES
  ('192.168.1.10', 'admin@fastplay.es', TRUE),
  ('192.168.1.11', 'demo@fastplay.es',  TRUE),
  ('203.0.113.45', 'demo@fastplay.es',  FALSE),
  ('203.0.113.45', 'demo@fastplay.es',  FALSE);

COMMIT;

-- ============================================================================
-- 6. VISTAS UTILES
-- ============================================================================

-- Clasificacion ordenada por liga
CREATE OR REPLACE VIEW v_league_standings AS
SELECT  l.id   AS league_id,
        l.name AS league_name,
        t.id   AS team_id,
        t.name AS team_name,
        lt.points,
        lt.played, lt.won, lt.drawn, lt.lost,
        lt.gf, lt.ga,
        (lt.gf - lt.ga) AS goal_diff
FROM    league_teams lt
JOIN    leagues l ON l.id = lt.league_id
JOIN    teams   t ON t.id = lt.team_id
ORDER BY lt.league_id, lt.points DESC, (lt.gf - lt.ga) DESC, lt.gf DESC;

-- Partidos proximos / en curso
CREATE OR REPLACE VIEW v_upcoming_matches AS
SELECT  m.id, m.scheduled_at, m.status,
        h.name AS home_team,
        a.name AS away_team,
        f.name AS field,
        l.name AS league
FROM    matches m
JOIN    teams h ON h.id = m.home_team_id
JOIN    teams a ON a.id = m.away_team_id
LEFT JOIN fields  f ON f.id = m.field_id
LEFT JOIN leagues l ON l.id = m.league_id
WHERE   m.status IN ('pending', 'confirmed', 'in_progress')
ORDER BY m.scheduled_at;

-- ----------------------------------------------------------------------------
-- 7. EXTENSIONES FUNCIONALES 2026-05-19 (notificaciones, solicitudes, premium)
-- ----------------------------------------------------------------------------
ALTER TABLE users ADD COLUMN is_premium BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE users ADD COLUMN google_id VARCHAR(255) UNIQUE;
ALTER TABLE users ADD COLUMN current_team_id BIGINT;
ALTER TABLE teams ADD COLUMN shield TEXT;
ALTER TABLE team_members ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'player';
ALTER TABLE fields
  ADD COLUMN latitude NUMERIC(10,7),
  ADD COLUMN longitude NUMERIC(10,7),
  ADD COLUMN maps_url TEXT,
  ADD COLUMN image TEXT,
  ADD COLUMN description TEXT;
ALTER TABLE matches
  ADD COLUMN local_captain_id BIGINT,
  ADD COLUMN visitor_captain_id BIGINT,
  ADD COLUMN match_time TIME,
  ADD COLUMN location VARCHAR(200);
ALTER TABLE chat_rooms
  ADD COLUMN team_id BIGINT,
  ADD COLUMN match_request_id BIGINT;

CREATE TABLE notifications (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type VARCHAR(80) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    action_url TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read, created_at);

CREATE TABLE team_join_requests (
    id BIGSERIAL PRIMARY KEY,
    team_id BIGINT NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    captain_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','accepted','rejected','cancelled')),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX idx_tjr_team_user_status ON team_join_requests(team_id, user_id, status);

CREATE TABLE match_requests (
    id BIGSERIAL PRIMARY KEY,
    requesting_team_id BIGINT NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
    requested_team_id BIGINT NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
    requesting_captain_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    requested_captain_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','accepted','accepted_final','rejected','cancelled')),
    proposed_date DATE,
    proposed_time TIME,
    location VARCHAR(200),
    requesting_confirmed BOOLEAN NOT NULL DEFAULT FALSE,
    requested_confirmed BOOLEAN NOT NULL DEFAULT FALSE,
    match_id BIGINT REFERENCES matches(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX idx_mr_teams_status ON match_requests(requesting_team_id, requested_team_id, status);

CREATE TABLE subscriptions (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    provider VARCHAR(40) NOT NULL DEFAULT 'stripe',
    provider_customer_id VARCHAR(190),
    provider_subscription_id VARCHAR(190),
    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('active','cancelled','pending','expired')),
    starts_at TIMESTAMPTZ,
    ends_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX idx_subscriptions_user_status ON subscriptions(user_id, status);

INSERT INTO fields (name, city, address, surface, capacity, hourly_rate, latitude, longitude, maps_url, description) VALUES
  ('Campo Federativo Jose Benoliel', 'Ceuta', 'Avenida de Africa, Ceuta', 'sintÃ©tico', 22, 0, 35.8898000, -5.3262000, 'https://www.google.com/maps/search/?api=1&query=Campo+Federativo+Jose+Benoliel+Ceuta', 'Campo federativo de futbol en Ceuta.'),
  ('Polideportivo La Libertad', 'Ceuta', 'Avenida de Lisboa, Ceuta', 'sintÃ©tico', 14, 0, 35.8844000, -5.3441000, 'https://www.google.com/maps/search/?api=1&query=Polideportivo+La+Libertad+Ceuta', 'Instalacion polideportiva para entrenamientos y partidos.'),
  ('Complejo Deportivo Diaz-Flor', 'Ceuta', 'Avenida de Otero, Ceuta', 'cÃ©sped', 22, 0, 35.8871000, -5.3073000, 'https://www.google.com/maps/search/?api=1&query=Complejo+Deportivo+Diaz+Flor+Ceuta', 'Complejo deportivo municipal en Ceuta.');
