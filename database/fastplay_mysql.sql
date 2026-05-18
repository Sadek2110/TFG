-- ============================================================================
-- FastPlay - Esquema completo MySQL + datos de prueba
-- ----------------------------------------------------------------------------
-- Equivalente del schema PostgreSQL (fastplay_postgres.sql) portado a MySQL
-- con tipos nativos, ENUMs, CHECK constraints, indices y triggers.
-- Compatible con MySQL 8.0.16+ (las CHECK constraints requieren esa version).
--
-- Uso rapido:
--   mysql -u root -p < fastplay_mysql.sql
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 0. CREACION DE LA BASE DE DATOS
-- ----------------------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS fastplay
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE fastplay;

SET NAMES utf8mb4;
SET time_zone = '+02:00';   -- CEST (Europe/Madrid)

-- ----------------------------------------------------------------------------
-- 1. LIMPIEZA (orden inverso por dependencias)
-- ----------------------------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;

DROP VIEW  IF EXISTS v_upcoming_matches;
DROP VIEW  IF EXISTS v_league_standings;

DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS user_achievements;
DROP TABLE IF EXISTS achievements;
DROP TABLE IF EXISTS chat_messages;
DROP TABLE IF EXISTS chat_rooms;
DROP TABLE IF EXISTS matches;
DROP TABLE IF EXISTS fields;
DROP TABLE IF EXISTS league_teams;
DROP TABLE IF EXISTS leagues;
DROP TABLE IF EXISTS team_members;
DROP TABLE IF EXISTS teams;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- 2. TABLAS
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 2.1 USERS
-- ----------------------------------------------------------------------------
CREATE TABLE users (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(120)    NOT NULL,
    email           VARCHAR(190)    NOT NULL,
    phone           VARCHAR(20),
    age             SMALLINT,
    city            VARCHAR(80),
    position        VARCHAR(40),
    password_hash   VARCHAR(255)    NOT NULL,
    role            ENUM('player','admin') NOT NULL DEFAULT 'player',
    avatar          TEXT,
    bio             TEXT,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                            ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_users_email UNIQUE (email),
    CONSTRAINT chk_users_age  CHECK (age IS NULL OR (age BETWEEN 14 AND 99))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_users_city ON users(city);
CREATE INDEX idx_users_role ON users(role);

-- ----------------------------------------------------------------------------
-- 2.2 TEAMS  (1 capitan -> N equipos; el equipo NO se borra al borrar capitan)
-- ----------------------------------------------------------------------------
CREATE TABLE teams (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120)    NOT NULL,
    city        VARCHAR(80)     NOT NULL,
    badge       VARCHAR(10)     DEFAULT '🛡️',
    captain_id  BIGINT UNSIGNED NOT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_teams_captain FOREIGN KEY (captain_id)
        REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT uq_teams_name_city UNIQUE (name, city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_teams_captain ON teams(captain_id);
CREATE INDEX idx_teams_city    ON teams(city);

-- ----------------------------------------------------------------------------
-- 2.3 TEAM_MEMBERS  (N:M users <-> teams)
-- ----------------------------------------------------------------------------
CREATE TABLE team_members (
    team_id    BIGINT UNSIGNED NOT NULL,
    user_id    BIGINT UNSIGNED NOT NULL,
    joined_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (team_id, user_id),
    CONSTRAINT fk_tm_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    CONSTRAINT fk_tm_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_team_members_user ON team_members(user_id);

-- ----------------------------------------------------------------------------
-- 2.4 LEAGUES
-- ----------------------------------------------------------------------------
CREATE TABLE leagues (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150)    NOT NULL,
    city        VARCHAR(80)     NOT NULL,
    pro         BOOLEAN         NOT NULL DEFAULT FALSE,
    prize       DECIMAL(10,2),
    start_date  DATE            NOT NULL,
    end_date    DATE            NOT NULL,
    max_teams   INT             NOT NULL DEFAULT 12,
    status      ENUM('open','in_progress') NOT NULL DEFAULT 'open',
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_leagues_prize     CHECK (prize IS NULL OR prize >= 0),
    CONSTRAINT chk_leagues_max_teams CHECK (max_teams BETWEEN 2 AND 64),
    CONSTRAINT chk_leagues_dates     CHECK (end_date >= start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_leagues_city   ON leagues(city);
CREATE INDEX idx_leagues_status ON leagues(status);
CREATE INDEX idx_leagues_pro    ON leagues(pro);

-- ----------------------------------------------------------------------------
-- 2.5 LEAGUE_TEAMS  (clasificacion)
-- ----------------------------------------------------------------------------
CREATE TABLE league_teams (
    league_id      BIGINT UNSIGNED NOT NULL,
    team_id        BIGINT UNSIGNED NOT NULL,
    points         INT             NOT NULL DEFAULT 0,
    played         INT             NOT NULL DEFAULT 0,
    won            INT             NOT NULL DEFAULT 0,
    drawn          INT             NOT NULL DEFAULT 0,
    lost           INT             NOT NULL DEFAULT 0,
    gf             INT             NOT NULL DEFAULT 0,
    ga             INT             NOT NULL DEFAULT 0,
    registered_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (league_id, team_id),
    CONSTRAINT fk_lt_league FOREIGN KEY (league_id) REFERENCES leagues(id) ON DELETE CASCADE,
    CONSTRAINT fk_lt_team   FOREIGN KEY (team_id)   REFERENCES teams(id)   ON DELETE CASCADE,
    CONSTRAINT chk_lt_points CHECK (points >= 0),
    CONSTRAINT chk_lt_played CHECK (played >= 0),
    CONSTRAINT chk_lt_won    CHECK (won    >= 0),
    CONSTRAINT chk_lt_drawn  CHECK (drawn  >= 0),
    CONSTRAINT chk_lt_lost   CHECK (lost   >= 0),
    CONSTRAINT chk_lt_gf     CHECK (gf     >= 0),
    CONSTRAINT chk_lt_ga     CHECK (ga     >= 0),
    CONSTRAINT chk_lt_sum    CHECK (won + drawn + lost = played)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_lt_team ON league_teams(team_id);

-- ----------------------------------------------------------------------------
-- 2.6 FIELDS
-- ----------------------------------------------------------------------------
CREATE TABLE fields (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(120)    NOT NULL,
    city         VARCHAR(80)     NOT NULL,
    address      VARCHAR(200),
    surface      ENUM('césped','sintético','tierra','cemento') NOT NULL DEFAULT 'césped',
    capacity     INT             NOT NULL DEFAULT 22,
    hourly_rate  DECIMAL(8,2)    NOT NULL DEFAULT 0,
    CONSTRAINT chk_fields_capacity CHECK (capacity > 0),
    CONSTRAINT chk_fields_rate     CHECK (hourly_rate >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_fields_city ON fields(city);

-- ----------------------------------------------------------------------------
-- 2.7 MATCHES
-- ----------------------------------------------------------------------------
CREATE TABLE matches (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    home_team_id  BIGINT UNSIGNED NOT NULL,
    away_team_id  BIGINT UNSIGNED NOT NULL,
    league_id     BIGINT UNSIGNED,
    field_id      BIGINT UNSIGNED,
    scheduled_at  DATETIME        NOT NULL,
    status        ENUM('pending','confirmed','cancelled','finished') NOT NULL DEFAULT 'pending',
    home_score    SMALLINT,
    away_score    SMALLINT,
    created_by    BIGINT UNSIGNED NOT NULL,
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_match_home    FOREIGN KEY (home_team_id) REFERENCES teams(id)   ON DELETE CASCADE,
    CONSTRAINT fk_match_away    FOREIGN KEY (away_team_id) REFERENCES teams(id)   ON DELETE CASCADE,
    CONSTRAINT fk_match_league  FOREIGN KEY (league_id)    REFERENCES leagues(id) ON DELETE SET NULL,
    CONSTRAINT fk_match_field   FOREIGN KEY (field_id)     REFERENCES fields(id)  ON DELETE SET NULL,
    CONSTRAINT fk_match_creator FOREIGN KEY (created_by)   REFERENCES users(id)   ON DELETE CASCADE,
    CONSTRAINT chk_match_teams  CHECK (home_team_id <> away_team_id),
    CONSTRAINT chk_match_scores CHECK (home_score IS NULL OR home_score >= 0),
    CONSTRAINT chk_match_scores2 CHECK (away_score IS NULL OR away_score >= 0),
    CONSTRAINT chk_match_finished CHECK (
        status <> 'finished'
        OR (home_score IS NOT NULL AND away_score IS NOT NULL)
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_matches_status    ON matches(status);
CREATE INDEX idx_matches_scheduled ON matches(scheduled_at);
CREATE INDEX idx_matches_league    ON matches(league_id);
CREATE INDEX idx_matches_field     ON matches(field_id);
CREATE INDEX idx_matches_home      ON matches(home_team_id);
CREATE INDEX idx_matches_away      ON matches(away_team_id);

-- ----------------------------------------------------------------------------
-- 2.8 CHAT (rooms + messages)
-- ----------------------------------------------------------------------------
CREATE TABLE chat_rooms (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    type        ENUM('group','general','team','league','match_negotiation','direct')
                NOT NULL DEFAULT 'group',
    name        VARCHAR(150)    NOT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE chat_messages (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    room_id     BIGINT UNSIGNED NOT NULL,
    user_id     BIGINT UNSIGNED NOT NULL,
    body        TEXT            NOT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_msg_room FOREIGN KEY (room_id) REFERENCES chat_rooms(id) ON DELETE CASCADE,
    CONSTRAINT fk_msg_user FOREIGN KEY (user_id) REFERENCES users(id)      ON DELETE CASCADE,
    CONSTRAINT chk_msg_body CHECK (CHAR_LENGTH(TRIM(body)) > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_messages_room_time ON chat_messages(room_id, created_at);
CREATE INDEX idx_messages_user      ON chat_messages(user_id);

-- ----------------------------------------------------------------------------
-- 2.9 ACHIEVEMENTS (catalogo + asignaciones)
-- ----------------------------------------------------------------------------
CREATE TABLE achievements (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code         VARCHAR(50)     NOT NULL,
    name         VARCHAR(100)    NOT NULL,
    description  TEXT            NOT NULL,
    icon         VARCHAR(10)     NOT NULL DEFAULT '🏅',
    CONSTRAINT uq_achievements_code UNIQUE (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_achievements (
    user_id         BIGINT UNSIGNED NOT NULL,
    achievement_id  BIGINT UNSIGNED NOT NULL,
    earned_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, achievement_id),
    CONSTRAINT fk_ua_user FOREIGN KEY (user_id)        REFERENCES users(id)        ON DELETE CASCADE,
    CONSTRAINT fk_ua_ach  FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_user_achievements_ach ON user_achievements(achievement_id);

-- ----------------------------------------------------------------------------
-- 2.10 LOGIN_ATTEMPTS  (rate-limiting de logins)
-- ----------------------------------------------------------------------------
CREATE TABLE login_attempts (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ip            VARCHAR(45)     NOT NULL,    -- IPv4 o IPv6
    email         VARCHAR(190)    NOT NULL,
    success       BOOLEAN         NOT NULL DEFAULT FALSE,
    attempted_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_login_attempts_email ON login_attempts(email, attempted_at);
CREATE INDEX idx_login_attempts_ip    ON login_attempts(ip,    attempted_at);

-- ----------------------------------------------------------------------------
-- 3. TRIGGERS  (updated_at en users ya esta cubierto por ON UPDATE CURRENT_TIMESTAMP)
-- ----------------------------------------------------------------------------
-- En MySQL no se necesita trigger explicito: la columna users.updated_at usa
-- "ON UPDATE CURRENT_TIMESTAMP" que se actualiza en cada UPDATE automaticamente.

-- ============================================================================
-- 4. SEED DATA  (equivalente del seeder PHP de Database.php)
-- ============================================================================
-- Los password_hash son bcrypt $2y$10$... pre-calculados con
-- password_hash($pwd, PASSWORD_BCRYPT, ['cost' => 10]) y verifican OK con
-- password_verify() de PHP.
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 4.1 USERS
-- ----------------------------------------------------------------------------
INSERT INTO users (id, name, email, phone, age, city, position, password_hash, role) VALUES
  (1, 'Sadek Admin',  'admin@fastplay.es', '+34600000000', 28, 'Madrid',    'Mediocampo', '$2y$10$ah8oxFHzLqh5/RfMQXFf0.XjtUAPQ85hDWDXYXNMgH5pPq3l.50xi', 'admin'),
  (2, 'Jugador Demo', 'demo@fastplay.es',  '+34611111111', 24, 'Madrid',    'Delantero',  '$2y$10$D5keJqVGvaO8YuGTIubIlO6SVNb/Ad3xIAVHGTMumkj0Jl.7Y8HOy', 'player'),
  (3, 'Lucía Pérez',  'lucia@fastplay.es', '+34600123456', 22, 'Barcelona', 'Portera',    '$2y$10$D5keJqVGvaO8YuGTIubIlO6SVNb/Ad3xIAVHGTMumkj0Jl.7Y8HOy', 'player'),
  (4, 'Marc Costa',   'marc@fastplay.es',  '+34600234567', 27, 'Valencia',  'Defensa',    '$2y$10$D5keJqVGvaO8YuGTIubIlO6SVNb/Ad3xIAVHGTMumkj0Jl.7Y8HOy', 'player'),
  (5, 'Ana Ruiz',     'ana@fastplay.es',   '+34600345678', 26, 'Sevilla',   'Mediocampo', '$2y$10$D5keJqVGvaO8YuGTIubIlO6SVNb/Ad3xIAVHGTMumkj0Jl.7Y8HOy', 'player'),
  (6, 'Iván Soto',    'ivan@fastplay.es',  '+34600456789', 25, 'Bilbao',    'Delantero',  '$2y$10$D5keJqVGvaO8YuGTIubIlO6SVNb/Ad3xIAVHGTMumkj0Jl.7Y8HOy', 'player'),
  (7, 'Paula Gil',    'paula@fastplay.es', '+34600567890', 23, 'Zaragoza',  'Defensa',    '$2y$10$D5keJqVGvaO8YuGTIubIlO6SVNb/Ad3xIAVHGTMumkj0Jl.7Y8HOy', 'player'),
  (8, 'Hugo Marín',   'hugo@fastplay.es',  '+34600678901', 29, 'Málaga',    'Mediocampo', '$2y$10$D5keJqVGvaO8YuGTIubIlO6SVNb/Ad3xIAVHGTMumkj0Jl.7Y8HOy', 'player');

ALTER TABLE users AUTO_INCREMENT = 9;

-- ----------------------------------------------------------------------------
-- 4.2 TEAMS
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

ALTER TABLE teams AUTO_INCREMENT = 9;

-- ----------------------------------------------------------------------------
-- 4.3 TEAM_MEMBERS
-- ----------------------------------------------------------------------------
INSERT INTO team_members (team_id, user_id) VALUES
  (1, 2), (2, 3), (3, 1), (4, 5),
  (5, 4), (6, 6), (7, 7), (8, 8),
  (3, 2);   -- demo tambien juega en Atletico Centro

-- ----------------------------------------------------------------------------
-- 4.4 LEAGUES
-- ----------------------------------------------------------------------------
INSERT INTO leagues (id, name, city, pro, prize, start_date, end_date) VALUES
  (1, 'Liga Pro Madrid 25/26',    'Madrid',    TRUE,  1500.00, '2026-03-01', '2026-06-30'),
  (2, 'Liga Pro Barcelona 25/26', 'Barcelona', TRUE,  1500.00, '2026-03-01', '2026-06-30'),
  (3, 'Liga Amistosa Valencia',   'Valencia',  FALSE, NULL,    '2026-03-01', '2026-06-30'),
  (4, 'Liga Amistosa Sevilla',    'Sevilla',   FALSE, NULL,    '2026-03-01', '2026-06-30');

ALTER TABLE leagues AUTO_INCREMENT = 5;

-- ----------------------------------------------------------------------------
-- 4.5 LEAGUE_TEAMS  (clasificacion sembrada coherente con played = w+d+l)
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
-- 4.6 FIELDS
-- ----------------------------------------------------------------------------
INSERT INTO fields (id, name, city, address, surface, capacity, hourly_rate) VALUES
  (1, 'La Cantera',        'Madrid',    'Av. de las Glorietas 12', 'césped',    22, 35.00),
  (2, 'Pista 4',           'Madrid',    'Polideportivo Centro',    'sintético', 14, 22.00),
  (3, 'Polideportivo Sur', 'Valencia',  'C/ del Mar, 3',           'césped',    22, 30.00),
  (4, 'Camp Nou Petit',    'Barcelona', 'C/ de Sants, 88',         'césped',    22, 40.00),
  (5, 'Sevilla Sur',       'Sevilla',   'Av. Heliópolis 21',       'tierra',    14, 18.00);

ALTER TABLE fields AUTO_INCREMENT = 6;

-- ----------------------------------------------------------------------------
-- 4.7 MATCHES  (horas en zona CEST configurada al inicio: time_zone='+02:00')
-- ----------------------------------------------------------------------------
INSERT INTO matches (id, home_team_id, away_team_id, league_id, field_id, scheduled_at, status, home_score, away_score, created_by) VALUES
  (1, 1, 2, 1,    1, '2026-06-12 19:30:00', 'confirmed', NULL, NULL, 1),
  (2, 3, 4, 1,    2, '2026-06-15 21:00:00', 'finished',  3,    2,    1),
  (3, 5, 1, 3,    3, '2026-06-22 20:00:00', 'pending',   NULL, NULL, 1),
  (4, 2, 6, NULL, 4, '2026-07-02 18:00:00', 'confirmed', NULL, NULL, 1);

ALTER TABLE matches AUTO_INCREMENT = 5;

-- ----------------------------------------------------------------------------
-- 4.8 ACHIEVEMENTS
-- ----------------------------------------------------------------------------
INSERT INTO achievements (id, code, name, description, icon) VALUES
  (1, 'first_goal', 'Primer gol', 'Marca tu primer gol oficial.',  '🎖️'),
  (2, 'hat_trick',  'Hat-trick',  '3 goles en un solo partido.',   '🏅'),
  (3, 'captain',    'Capitán',    'Crea y dirige un equipo.',      '🛡️'),
  (4, 'veteran',    'Veterano',   'Juega 10 partidos.',            '🎯'),
  (5, 'mvp',        'MVP',        'Mejor jugador en una jornada.', '🏆');

ALTER TABLE achievements AUTO_INCREMENT = 6;

INSERT INTO user_achievements (user_id, achievement_id) VALUES
  (2, 1),
  (2, 3);

-- ----------------------------------------------------------------------------
-- 4.9 CHAT
-- ----------------------------------------------------------------------------
INSERT INTO chat_rooms (id, type, name) VALUES
  (1, 'general',           'Lobby general'),
  (2, 'match_negotiation', 'Capitanes — partidos amistosos');

ALTER TABLE chat_rooms AUTO_INCREMENT = 3;

INSERT INTO chat_messages (room_id, user_id, body) VALUES
  (1, 1, '¡Bienvenidos a FastPlay! Por aquí coordinamos cualquier duda.'),
  (1, 2, '¿Alguien para un 7v7 este finde en Madrid?'),
  (2, 3, 'Buscamos rival amistoso este sábado, Barça Amateurs disponibles.');

-- ----------------------------------------------------------------------------
-- 4.10 LOGIN_ATTEMPTS  (muestra para rate-limiter)
-- ----------------------------------------------------------------------------
INSERT INTO login_attempts (ip, email, success) VALUES
  ('192.168.1.10', 'admin@fastplay.es', TRUE),
  ('192.168.1.11', 'demo@fastplay.es',  TRUE),
  ('203.0.113.45', 'demo@fastplay.es',  FALSE),
  ('203.0.113.45', 'demo@fastplay.es',  FALSE);

-- ============================================================================
-- 5. VISTAS UTILES
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
WHERE   m.status IN ('pending', 'confirmed')
ORDER BY m.scheduled_at;
