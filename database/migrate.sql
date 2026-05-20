-- =============================================================================
-- FastPlay · Migración MySQL de producción
-- Ejecutar sobre la base de datos existente (es seguro re-ejecutar).
-- Compatible con MySQL 8.0+
-- =============================================================================

-- Cambia 'fastplay' por el nombre real de tu base de datos si es distinto.
-- USE fastplay;

-- -----------------------------------------------------------------------------
-- Procedimiento helper: añade columna solo si no existe
-- -----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS _fp_add_col;
DELIMITER $$
CREATE PROCEDURE _fp_add_col(IN tbl VARCHAR(64), IN col VARCHAR(64), IN def TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = tbl
          AND COLUMN_NAME  = col
    ) THEN
        SET @_q = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN ', def);
        PREPARE _s FROM @_q; EXECUTE _s; DEALLOCATE PREPARE _s;
    END IF;
END$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- 1. COLUMNAS NUEVAS EN TABLAS EXISTENTES
-- -----------------------------------------------------------------------------

-- users
CALL _fp_add_col('users', 'is_premium',      'is_premium TINYINT(1) NOT NULL DEFAULT 0');
CALL _fp_add_col('users', 'google_id',       'google_id VARCHAR(255) NULL');
CALL _fp_add_col('users', 'current_team_id', 'current_team_id BIGINT UNSIGNED NULL');
CALL _fp_add_col('users', 'dorsal',          'dorsal SMALLINT NULL');
CALL _fp_add_col('users', 'height_cm',       'height_cm SMALLINT NULL');
CALL _fp_add_col('users', 'goals',           'goals INT NOT NULL DEFAULT 0');
CALL _fp_add_col('users', 'assists',         'assists INT NOT NULL DEFAULT 0');

-- teams
CALL _fp_add_col('teams', 'shield', 'shield TEXT NULL');

-- team_members
CALL _fp_add_col('team_members', 'role', "role ENUM('captain','player') NOT NULL DEFAULT 'player'");

-- fields
CALL _fp_add_col('fields', 'latitude',    'latitude DECIMAL(10,7) NULL');
CALL _fp_add_col('fields', 'longitude',   'longitude DECIMAL(10,7) NULL');
CALL _fp_add_col('fields', 'maps_url',    'maps_url TEXT NULL');
CALL _fp_add_col('fields', 'image',       'image TEXT NULL');
CALL _fp_add_col('fields', 'description', 'description TEXT NULL');

-- matches
CALL _fp_add_col('matches', 'local_captain_id',   'local_captain_id BIGINT UNSIGNED NULL');
CALL _fp_add_col('matches', 'visitor_captain_id', 'visitor_captain_id BIGINT UNSIGNED NULL');
CALL _fp_add_col('matches', 'match_time',         'match_time TIME NULL');
CALL _fp_add_col('matches', 'location',           'location VARCHAR(200) NULL');

-- chat_rooms
CALL _fp_add_col('chat_rooms', 'team_id',          'team_id BIGINT UNSIGNED NULL');
CALL _fp_add_col('chat_rooms', 'match_request_id', 'match_request_id BIGINT UNSIGNED NULL');

DROP PROCEDURE IF EXISTS _fp_add_col;

-- -----------------------------------------------------------------------------
-- 2. TABLAS NUEVAS
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS notifications (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id    BIGINT UNSIGNED NOT NULL,
    type       VARCHAR(80)     NOT NULL,
    message    TEXT            NOT NULL,
    is_read    TINYINT(1)      NOT NULL DEFAULT 0,
    action_url TEXT,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_notifications_user_read (user_id, is_read, created_at),
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS team_join_requests (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    team_id    BIGINT UNSIGNED NOT NULL,
    user_id    BIGINT UNSIGNED NOT NULL,
    captain_id BIGINT UNSIGNED NOT NULL,
    status     ENUM('pending','accepted','rejected','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_tjr_team_user_status (team_id, user_id, status),
    CONSTRAINT fk_tjr_team    FOREIGN KEY (team_id)    REFERENCES teams(id) ON DELETE CASCADE,
    CONSTRAINT fk_tjr_user    FOREIGN KEY (user_id)    REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_tjr_captain FOREIGN KEY (captain_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS match_requests (
    id                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    requesting_team_id    BIGINT UNSIGNED NOT NULL,
    requested_team_id     BIGINT UNSIGNED NOT NULL,
    requesting_captain_id BIGINT UNSIGNED NOT NULL,
    requested_captain_id  BIGINT UNSIGNED NOT NULL,
    status                ENUM('pending','accepted','accepted_final','rejected','cancelled') NOT NULL DEFAULT 'pending',
    proposed_date         DATE NULL,
    proposed_time         TIME NULL,
    location              VARCHAR(200) NULL,
    requesting_confirmed  BOOLEAN NOT NULL DEFAULT FALSE,
    requested_confirmed   BOOLEAN NOT NULL DEFAULT FALSE,
    match_id              BIGINT UNSIGNED NULL,
    created_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_mr_teams_status (requesting_team_id, requested_team_id, status),
    CONSTRAINT fk_mr_req_team  FOREIGN KEY (requesting_team_id)    REFERENCES teams(id) ON DELETE CASCADE,
    CONSTRAINT fk_mr_res_team  FOREIGN KEY (requested_team_id)     REFERENCES teams(id) ON DELETE CASCADE,
    CONSTRAINT fk_mr_req_cap   FOREIGN KEY (requesting_captain_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_mr_res_cap   FOREIGN KEY (requested_captain_id)  REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_mr_match     FOREIGN KEY (match_id)              REFERENCES matches(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscriptions (
    id                       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id                  BIGINT UNSIGNED NOT NULL,
    provider                 VARCHAR(40)     NOT NULL DEFAULT 'stripe',
    provider_customer_id     VARCHAR(190),
    provider_subscription_id VARCHAR(190),
    status                   ENUM('active','cancelled','pending','expired') NOT NULL DEFAULT 'pending',
    starts_at                DATETIME,
    ends_at                  DATETIME,
    created_at               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_subscriptions_user_status (user_id, status),
    CONSTRAINT fk_sub_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. CAMPOS DE CEUTA (INSERT IGNORE = no falla si ya existen)
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO fields (name, city, address, surface, capacity, hourly_rate, latitude, longitude, maps_url, description) VALUES
  ('Campo Federativo Jose Benoliel', 'Ceuta', 'Avenida de Africa, Ceuta',   'sintético', 22, 0, 35.8898, -5.3262, 'https://www.google.com/maps/search/?api=1&query=Campo+Federativo+Jose+Benoliel+Ceuta',  'Campo federativo de fútbol en Ceuta.'),
  ('Polideportivo La Libertad',      'Ceuta', 'Avenida de Lisboa, Ceuta',   'sintético', 14, 0, 35.8844, -5.3441, 'https://www.google.com/maps/search/?api=1&query=Polideportivo+La+Libertad+Ceuta',       'Instalación polideportiva para entrenamientos y partidos.'),
  ('Complejo Deportivo Diaz-Flor',   'Ceuta', 'Avenida de Otero, Ceuta',    'césped',    22, 0, 35.8871, -5.3073, 'https://www.google.com/maps/search/?api=1&query=Complejo+Deportivo+Diaz+Flor+Ceuta',   'Complejo deportivo municipal en Ceuta.');
