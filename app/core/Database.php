<?php
// FastPlay · wrapper PDO con auto-init de esquema (SQLite)

class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        self::$pdo = $pdo;
        // El auto-init de esquema y los seeds están escritos en sintaxis SQLite
        // (AUTOINCREMENT, PRAGMA, datetime('now'), INSERT OR IGNORE). Para MySQL
        // o PostgreSQL el esquema se carga vía database/fastplay_{mysql,postgres}.sql.
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $pdo->exec('PRAGMA foreign_keys = ON;');
            self::migrate($pdo);
        } else {
            // Las instalaciones MySQL/PostgreSQL no ejecutan la migración SQLite,
            // pero las columnas añadidas a posteriori sí deben aparecer también allí.
            self::ensureRuntimeColumns($pdo, $driver);
        }
        return $pdo;
    }

    /** Añade en caliente las columnas de la carta-jugador en MySQL/PostgreSQL si faltan. */
    private static function ensureRuntimeColumns(PDO $pdo, string $driver): void
    {
        try {
            if ($driver === 'mysql') {
                $existing = [];
                $rows = $pdo->query(
                    "SELECT COLUMN_NAME FROM information_schema.COLUMNS
                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'"
                );
                foreach ($rows as $r) { $existing[$r['COLUMN_NAME']] = true; }
                if (!isset($existing['dorsal']))    $pdo->exec("ALTER TABLE users ADD COLUMN dorsal SMALLINT NULL");
                if (!isset($existing['height_cm'])) $pdo->exec("ALTER TABLE users ADD COLUMN height_cm SMALLINT NULL");
                if (!isset($existing['goals']))     $pdo->exec("ALTER TABLE users ADD COLUMN goals INT NOT NULL DEFAULT 0");
                if (!isset($existing['assists']))   $pdo->exec("ALTER TABLE users ADD COLUMN assists INT NOT NULL DEFAULT 0");
                if (!isset($existing['is_premium'])) $pdo->exec("ALTER TABLE users ADD COLUMN is_premium TINYINT(1) NOT NULL DEFAULT 0");
                if (!isset($existing['current_team_id'])) $pdo->exec("ALTER TABLE users ADD COLUMN current_team_id BIGINT UNSIGNED NULL");

                $teamCols = [];
                $rows = $pdo->query(
                    "SELECT COLUMN_NAME FROM information_schema.COLUMNS
                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'teams'"
                );
                foreach ($rows as $r) { $teamCols[$r['COLUMN_NAME']] = true; }
                if (!isset($teamCols['shield'])) $pdo->exec("ALTER TABLE teams ADD COLUMN shield TEXT NULL");

                $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
                    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    user_id BIGINT UNSIGNED NOT NULL,
                    type VARCHAR(80) NOT NULL,
                    message TEXT NOT NULL,
                    is_read TINYINT(1) NOT NULL DEFAULT 0,
                    action_url TEXT,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $pdo->exec("CREATE INDEX IF NOT EXISTS idx_notifications_user_read ON notifications(user_id, is_read, created_at)");

                $pdo->exec("CREATE TABLE IF NOT EXISTS team_join_requests (
                    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    team_id BIGINT UNSIGNED NOT NULL,
                    user_id BIGINT UNSIGNED NOT NULL,
                    captain_id BIGINT UNSIGNED NOT NULL,
                    status ENUM('pending','accepted','rejected','cancelled') NOT NULL DEFAULT 'pending',
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    CONSTRAINT fk_tjr_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
                    CONSTRAINT fk_tjr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    CONSTRAINT fk_tjr_captain FOREIGN KEY (captain_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $pdo->exec("CREATE INDEX IF NOT EXISTS idx_tjr_team_user_status ON team_join_requests(team_id, user_id, status)");

                $pdo->exec("CREATE TABLE IF NOT EXISTS match_requests (
                    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    requesting_team_id BIGINT UNSIGNED NOT NULL,
                    requested_team_id BIGINT UNSIGNED NOT NULL,
                    requesting_captain_id BIGINT UNSIGNED NOT NULL,
                    requested_captain_id BIGINT UNSIGNED NOT NULL,
                    status ENUM('pending','accepted','accepted_final','rejected','cancelled') NOT NULL DEFAULT 'pending',
                    proposed_date DATE NULL,
                    proposed_time TIME NULL,
                    location VARCHAR(200) NULL,
                    requesting_confirmed BOOLEAN NOT NULL DEFAULT FALSE,
                    requested_confirmed BOOLEAN NOT NULL DEFAULT FALSE,
                    match_id BIGINT UNSIGNED NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    CONSTRAINT fk_mr_requesting_team FOREIGN KEY (requesting_team_id) REFERENCES teams(id) ON DELETE CASCADE,
                    CONSTRAINT fk_mr_requested_team FOREIGN KEY (requested_team_id) REFERENCES teams(id) ON DELETE CASCADE,
                    CONSTRAINT fk_mr_requesting_captain FOREIGN KEY (requesting_captain_id) REFERENCES users(id) ON DELETE CASCADE,
                    CONSTRAINT fk_mr_requested_captain FOREIGN KEY (requested_captain_id) REFERENCES users(id) ON DELETE CASCADE,
                    CONSTRAINT fk_mr_match FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $pdo->exec("CREATE INDEX IF NOT EXISTS idx_mr_teams_status ON match_requests(requesting_team_id, requested_team_id, status)");

                $pdo->exec("CREATE TABLE IF NOT EXISTS subscriptions (
                    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    user_id BIGINT UNSIGNED NOT NULL,
                    provider VARCHAR(40) NOT NULL DEFAULT 'stripe',
                    provider_customer_id VARCHAR(190),
                    provider_subscription_id VARCHAR(190),
                    status ENUM('active','cancelled','pending','expired') NOT NULL DEFAULT 'pending',
                    starts_at DATETIME,
                    ends_at DATETIME,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    CONSTRAINT fk_subscriptions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $pdo->exec("CREATE INDEX IF NOT EXISTS idx_subscriptions_user_status ON subscriptions(user_id, status)");

            } elseif ($driver === 'pgsql') {
                $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS dorsal SMALLINT");
                $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS height_cm SMALLINT");
                $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS goals INTEGER NOT NULL DEFAULT 0");
                $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS assists INTEGER NOT NULL DEFAULT 0");
                $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS is_premium BOOLEAN NOT NULL DEFAULT FALSE");
                $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS current_team_id BIGINT");
                $pdo->exec("ALTER TABLE teams ADD COLUMN IF NOT EXISTS shield TEXT");

                $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
                    id BIGSERIAL PRIMARY KEY,
                    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                    type VARCHAR(80) NOT NULL,
                    message TEXT NOT NULL,
                    is_read BOOLEAN NOT NULL DEFAULT FALSE,
                    action_url TEXT,
                    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
                )");
                $pdo->exec("CREATE INDEX IF NOT EXISTS idx_notifications_user_read ON notifications(user_id, is_read, created_at)");

                $pdo->exec("CREATE TABLE IF NOT EXISTS team_join_requests (
                    id BIGSERIAL PRIMARY KEY,
                    team_id BIGINT NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
                    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                    captain_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','accepted','rejected','cancelled')),
                    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
                )");
                $pdo->exec("CREATE INDEX IF NOT EXISTS idx_tjr_team_user_status ON team_join_requests(team_id, user_id, status)");

                $pdo->exec("CREATE TABLE IF NOT EXISTS match_requests (
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
                )");
                $pdo->exec("CREATE INDEX IF NOT EXISTS idx_mr_teams_status ON match_requests(requesting_team_id, requested_team_id, status)");

                $pdo->exec("CREATE TABLE IF NOT EXISTS subscriptions (
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
                )");
                $pdo->exec("CREATE INDEX IF NOT EXISTS idx_subscriptions_user_status ON subscriptions(user_id, status)");
            }
        } catch (Throwable $e) {
            // No bloqueamos el arranque si el usuario de BD no tiene permisos DDL;
            // los queries que dependen de estas columnas fallarán con un error claro.
            error_log('[FastPlay] ensureRuntimeColumns: ' . $e->getMessage());
        }
    }

    public static function run(string $sql, array $params = []): PDOStatement
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st;
    }

    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    public static function value(string $sql, array $params = [])
    {
        $st = self::run($sql, $params);
        $row = $st->fetch(PDO::FETCH_NUM);
        return $row === false ? null : $row[0];
    }

    public static function insertId(): int
    {
        return (int) self::pdo()->lastInsertId();
    }

    private static function migrate(PDO $pdo): void
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            phone TEXT,
            age INTEGER,
            city TEXT,
            position TEXT,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'player',
            avatar TEXT,
            current_team_id INTEGER,
            is_premium INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS teams (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            city TEXT NOT NULL,
            badge TEXT DEFAULT '🛡️',
            shield TEXT,
            captain_id INTEGER NOT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (captain_id) REFERENCES users(id) ON DELETE RESTRICT
        )");

        // Migración: si la tabla existía con ON DELETE CASCADE, recrear con RESTRICT
        $needsMigrate = false;
        foreach ($pdo->query("PRAGMA foreign_key_list('teams')") as $fk) {
            if ($fk['table'] === 'users' && $fk['from'] === 'captain_id' && $fk['on_delete'] === 'CASCADE') {
                $needsMigrate = true;
                break;
            }
        }
        if ($needsMigrate) {
            $pdo->exec('PRAGMA foreign_keys = OFF');
            $pdo->exec('BEGIN TRANSACTION');
            $pdo->exec("CREATE TABLE teams_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                city TEXT NOT NULL,
                badge TEXT DEFAULT '🛡️',
                shield TEXT,
                captain_id INTEGER NOT NULL,
                created_at TEXT NOT NULL DEFAULT (datetime('now')),
                FOREIGN KEY (captain_id) REFERENCES users(id) ON DELETE RESTRICT
            )");
            $pdo->exec("INSERT INTO teams_new (id,name,city,badge,captain_id,created_at) SELECT id,name,city,badge,captain_id,created_at FROM teams");
            $pdo->exec("DROP TABLE teams");
            $pdo->exec("ALTER TABLE teams_new RENAME TO teams");
            $pdo->exec('COMMIT');
            $pdo->exec('PRAGMA foreign_keys = ON');
        }

        // Columnas adicionales para la carta-jugador estilo FIFA del dashboard.
        // Se añaden de forma idempotente: sólo se crean si no existen ya.
        $userCols = [];
        foreach ($pdo->query("PRAGMA table_info('users')") as $col) {
            $userCols[$col['name']] = true;
        }
        if (!isset($userCols['dorsal'])) {
            $pdo->exec("ALTER TABLE users ADD COLUMN dorsal INTEGER");
        }
        if (!isset($userCols['height_cm'])) {
            $pdo->exec("ALTER TABLE users ADD COLUMN height_cm INTEGER");
        }
        if (!isset($userCols['goals'])) {
            $pdo->exec("ALTER TABLE users ADD COLUMN goals INTEGER NOT NULL DEFAULT 0");
        }
        if (!isset($userCols['assists'])) {
            $pdo->exec("ALTER TABLE users ADD COLUMN assists INTEGER NOT NULL DEFAULT 0");
        }
        if (!isset($userCols['is_premium'])) {
            $pdo->exec("ALTER TABLE users ADD COLUMN is_premium INTEGER NOT NULL DEFAULT 0");
        }
        if (!isset($userCols['current_team_id'])) {
            $pdo->exec("ALTER TABLE users ADD COLUMN current_team_id INTEGER");
        }

        $teamCols = [];
        foreach ($pdo->query("PRAGMA table_info('teams')") as $col) {
            $teamCols[$col['name']] = true;
        }
        if (!isset($teamCols['shield'])) {
            $pdo->exec("ALTER TABLE teams ADD COLUMN shield TEXT");
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS team_members (
            team_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            role TEXT NOT NULL DEFAULT 'player',
            joined_at TEXT NOT NULL DEFAULT (datetime('now')),
            PRIMARY KEY (team_id, user_id),
            FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS leagues (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            city TEXT NOT NULL,
            pro INTEGER NOT NULL DEFAULT 0,
            prize REAL,
            start_date TEXT NOT NULL,
            end_date TEXT NOT NULL,
            max_teams INTEGER NOT NULL DEFAULT 12,
            status TEXT NOT NULL DEFAULT 'open',
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS league_teams (
            league_id INTEGER NOT NULL,
            team_id INTEGER NOT NULL,
            points INTEGER NOT NULL DEFAULT 0,
            played INTEGER NOT NULL DEFAULT 0,
            won INTEGER NOT NULL DEFAULT 0,
            drawn INTEGER NOT NULL DEFAULT 0,
            lost INTEGER NOT NULL DEFAULT 0,
            gf INTEGER NOT NULL DEFAULT 0,
            ga INTEGER NOT NULL DEFAULT 0,
            registered_at TEXT NOT NULL DEFAULT (datetime('now')),
            PRIMARY KEY (league_id, team_id),
            FOREIGN KEY (league_id) REFERENCES leagues(id) ON DELETE CASCADE,
            FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS fields (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            city TEXT NOT NULL,
            address TEXT,
            surface TEXT NOT NULL DEFAULT 'césped',
            capacity INTEGER NOT NULL DEFAULT 22,
            hourly_rate REAL NOT NULL DEFAULT 0,
            latitude REAL,
            longitude REAL,
            maps_url TEXT,
            image TEXT,
            description TEXT
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS matches (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            home_team_id INTEGER NOT NULL,
            away_team_id INTEGER NOT NULL,
            league_id INTEGER,
            field_id INTEGER,
            scheduled_at TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'pending',
            local_captain_id INTEGER,
            visitor_captain_id INTEGER,
            match_time TEXT,
            location TEXT,
            home_score INTEGER,
            away_score INTEGER,
            created_by INTEGER NOT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (home_team_id) REFERENCES teams(id) ON DELETE CASCADE,
            FOREIGN KEY (away_team_id) REFERENCES teams(id) ON DELETE CASCADE,
            FOREIGN KEY (league_id) REFERENCES leagues(id) ON DELETE SET NULL,
            FOREIGN KEY (field_id) REFERENCES fields(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS chat_rooms (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type TEXT NOT NULL DEFAULT 'group',
            team_id INTEGER,
            match_request_id INTEGER,
            name TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS chat_messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            room_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            body TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (room_id) REFERENCES chat_rooms(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");

        $teamMemberCols = [];
        foreach ($pdo->query("PRAGMA table_info('team_members')") as $col) { $teamMemberCols[$col['name']] = true; }
        if (!isset($teamMemberCols['role'])) $pdo->exec("ALTER TABLE team_members ADD COLUMN role TEXT NOT NULL DEFAULT 'player'");

        $fieldCols = [];
        foreach ($pdo->query("PRAGMA table_info('fields')") as $col) { $fieldCols[$col['name']] = true; }
        foreach (['latitude' => 'REAL', 'longitude' => 'REAL', 'maps_url' => 'TEXT', 'image' => 'TEXT', 'description' => 'TEXT'] as $name => $type) {
            if (!isset($fieldCols[$name])) $pdo->exec("ALTER TABLE fields ADD COLUMN {$name} {$type}");
        }

        $matchCols = [];
        foreach ($pdo->query("PRAGMA table_info('matches')") as $col) { $matchCols[$col['name']] = true; }
        foreach (['local_captain_id' => 'INTEGER', 'visitor_captain_id' => 'INTEGER', 'match_time' => 'TEXT', 'location' => 'TEXT'] as $name => $type) {
            if (!isset($matchCols[$name])) $pdo->exec("ALTER TABLE matches ADD COLUMN {$name} {$type}");
        }

        $roomCols = [];
        foreach ($pdo->query("PRAGMA table_info('chat_rooms')") as $col) { $roomCols[$col['name']] = true; }
        foreach (['team_id' => 'INTEGER', 'match_request_id' => 'INTEGER'] as $name => $type) {
            if (!isset($roomCols[$name])) $pdo->exec("ALTER TABLE chat_rooms ADD COLUMN {$name} {$type}");
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            type TEXT NOT NULL,
            message TEXT NOT NULL,
            is_read INTEGER NOT NULL DEFAULT 0,
            action_url TEXT,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_notifications_user_read ON notifications(user_id, is_read, created_at)');

        $pdo->exec("CREATE TABLE IF NOT EXISTS team_join_requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            team_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            captain_id INTEGER NOT NULL,
            status TEXT NOT NULL DEFAULT 'pending',
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (captain_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_tjr_team_user_status ON team_join_requests(team_id, user_id, status)');

        $pdo->exec("CREATE TABLE IF NOT EXISTS match_requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            requesting_team_id INTEGER NOT NULL,
            requested_team_id INTEGER NOT NULL,
            requesting_captain_id INTEGER NOT NULL,
            requested_captain_id INTEGER NOT NULL,
            status TEXT NOT NULL DEFAULT 'pending',
            proposed_date TEXT,
            proposed_time TEXT,
            location TEXT,
            requesting_confirmed INTEGER NOT NULL DEFAULT 0,
            requested_confirmed INTEGER NOT NULL DEFAULT 0,
            match_id INTEGER,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (requesting_team_id) REFERENCES teams(id) ON DELETE CASCADE,
            FOREIGN KEY (requested_team_id) REFERENCES teams(id) ON DELETE CASCADE,
            FOREIGN KEY (requesting_captain_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (requested_captain_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE SET NULL
        )");
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_mr_teams_status ON match_requests(requesting_team_id, requested_team_id, status)');

        $pdo->exec("CREATE TABLE IF NOT EXISTS subscriptions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            provider TEXT NOT NULL DEFAULT 'stripe',
            provider_customer_id TEXT,
            provider_subscription_id TEXT,
            status TEXT NOT NULL DEFAULT 'pending',
            starts_at TEXT,
            ends_at TEXT,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_subscriptions_user_status ON subscriptions(user_id, status)');

        $pdo->exec("CREATE TABLE IF NOT EXISTS achievements (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            description TEXT NOT NULL,
            icon TEXT NOT NULL DEFAULT '🏅'
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS user_achievements (
            user_id INTEGER NOT NULL,
            achievement_id INTEGER NOT NULL,
            earned_at TEXT NOT NULL DEFAULT (datetime('now')),
            PRIMARY KEY (user_id, achievement_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip TEXT NOT NULL,
            email TEXT NOT NULL,
            success INTEGER NOT NULL DEFAULT 0,
            attempted_at TEXT NOT NULL DEFAULT (datetime('now'))
        )");
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_login_attempts_email ON login_attempts(email, attempted_at)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_login_attempts_ip    ON login_attempts(ip,    attempted_at)');

        self::seed($pdo);
        self::ensureCeutaData($pdo);
        self::repairConsistency($pdo);
    }

    private static function ensureCeutaData(PDO $pdo): void
    {
        $knownUsers = [
            'admin@fastplay.es',
            'demo@fastplay.es',
            'lucia@fastplay.es',
            'marc@fastplay.es',
            'ana@fastplay.es',
            'ivan@fastplay.es',
            'paula@fastplay.es',
            'hugo@fastplay.es',
        ];
        $stUser = $pdo->prepare('UPDATE users SET city = ? WHERE email = ?');
        foreach ($knownUsers as $email) {
            $stUser->execute(['Ceuta', $email]);
        }

        $teamRenames = [
            'Madrid Real C.F.' => 'Murube United',
            'Barça Amateurs' => 'Benoliel FC',
            'Atlético Centro' => 'Ceuta Centro Atletico',
            'Sevilla Street' => 'Principe Deportivo',
            'Valencia Calle' => 'La Marina FC',
            'Bilbao Norte' => 'Otero Norte',
            'Zaragoza FC' => 'Hacho FC',
            'Málaga Costa' => 'Ribera Sur',
        ];
        $stTeam = $pdo->prepare('UPDATE teams SET name = ?, city = ? WHERE name = ?');
        foreach ($teamRenames as $old => $new) {
            $stTeam->execute([$new, 'Ceuta', $old]);
        }
        $pdo->exec("UPDATE teams SET city = 'Ceuta' WHERE name IN ('Murube United','Benoliel FC','Ceuta Centro Atletico','Principe Deportivo','La Marina FC','Otero Norte','Hacho FC','Ribera Sur')");

        $leagueRenames = [
            'Liga Pro Madrid 25/26' => 'Liga Pro Ceuta 25/26',
            'Liga Pro Barcelona 25/26' => 'Copa Local Ceuta 25/26',
            'Liga Amistosa Valencia' => 'Liga Amistosa Ceuta',
            'Liga Amistosa Sevilla' => 'Torneo Barrios de Ceuta',
        ];
        $stLeague = $pdo->prepare('UPDATE leagues SET name = ?, city = ? WHERE name = ?');
        foreach ($leagueRenames as $old => $new) {
            $stLeague->execute([$new, 'Ceuta', $old]);
        }
        $pdo->exec("UPDATE leagues SET city = 'Ceuta' WHERE name IN ('Liga Pro Ceuta 25/26','Copa Local Ceuta 25/26','Liga Amistosa Ceuta','Torneo Barrios de Ceuta')");

        $fields = [
            ['Estadio Municipal Alfonso Murube', 'Ceuta', 'Calle Juan de Juanes, Ceuta', 'cesped', 22, 0, 35.8883, -5.3162, 'https://www.google.com/maps/search/?api=1&query=Estadio+Municipal+Alfonso+Murube+Ceuta', 'Estadio principal para futbol local y competicion en Ceuta.'],
            ['Campo Jose Martinez Pirri', 'Ceuta', 'Ceuta', 'sintetico', 22, 0, 35.8890, -5.3070, 'https://www.google.com/maps/search/?api=1&query=Campo+Jose+Martinez+Pirri+Ceuta', 'Instalacion deportiva para entrenamientos y partidos locales.'],
            ['Campo Federativo Jose Benoliel', 'Ceuta', 'Avenida de Africa, Ceuta', 'sintetico', 22, 0, 35.8898, -5.3262, 'https://www.google.com/maps/search/?api=1&query=Campo+Federativo+Jose+Benoliel+Ceuta', 'Campo federativo de futbol en Ceuta.'],
            ['Campo de futbol del Principe', 'Ceuta', 'Barriada Principe Alfonso, Ceuta', 'sintetico', 22, 0, 35.8746, -5.3268, 'https://www.google.com/maps/search/?api=1&query=Campo+de+futbol+del+Principe+Ceuta', 'Campo de barrio para futbol base y encuentros locales.'],
            ['Complejo Deportivo Diaz-Flor', 'Ceuta', 'Avenida de Otero, Ceuta', 'cesped', 22, 0, 35.8871, -5.3073, 'https://www.google.com/maps/search/?api=1&query=Complejo+Deportivo+Diaz+Flor+Ceuta', 'Complejo deportivo municipal en Ceuta.'],
        ];
        $fieldRenames = [
            'La Cantera' => $fields[0],
            'Pista 4' => $fields[1],
            'Polideportivo Sur' => $fields[2],
            'Camp Nou Petit' => $fields[3],
            'Sevilla Sur' => $fields[4],
        ];
        $updateField = $pdo->prepare('UPDATE fields SET name=?, city=?, address=?, surface=?, capacity=?, hourly_rate=?, latitude=?, longitude=?, maps_url=?, description=? WHERE name=?');
        foreach ($fieldRenames as $old => $field) {
            $updateField->execute([...$field, $old]);
        }

        $exists = $pdo->prepare('SELECT 1 FROM fields WHERE name = ? AND city = ?');
        $insert = $pdo->prepare('INSERT INTO fields (name,city,address,surface,capacity,hourly_rate,latitude,longitude,maps_url,description) VALUES (?,?,?,?,?,?,?,?,?,?)');
        foreach ($fields as $field) {
            $exists->execute([$field[0], $field[1]]);
            if (!$exists->fetchColumn()) {
                $insert->execute($field);
            }
        }
    }

    private static function repairConsistency(PDO $pdo): void
    {
        // Las versiones anteriores podían dejar partidos de liga con equipos no inscritos.
        // Mantener esta reparación idempotente evita clasificaciones incoherentes.
        $pdo->exec("
            INSERT OR IGNORE INTO league_teams (league_id, team_id)
            SELECT league_id, home_team_id
            FROM matches
            WHERE league_id IS NOT NULL
        ");
        $pdo->exec("
            INSERT OR IGNORE INTO league_teams (league_id, team_id)
            SELECT league_id, away_team_id
            FROM matches
            WHERE league_id IS NOT NULL
        ");
    }

    private static function seed(PDO $pdo): void
    {
        // Nunca seedeamos datos demo en producción.
        if (defined('APP_ENV') && APP_ENV === 'production') {
            return;
        }
        $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        if ($count > 0) {
            return;
        }

        // Todos los usuarios, equipos, ligas y campos demo están localizados en Ceuta.
        $st = $pdo->prepare("INSERT INTO users (name,email,phone,age,city,position,password_hash,role,dorsal,height_cm,goals,assists) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $st->execute(['Sadek Admin', 'admin@fastplay.es', '+34600000000', 28, 'Ceuta', 'Mediocampo', password_hash('Admin1234!', PASSWORD_DEFAULT), 'admin', 10, 178, 4, 12]);
        $adminId = (int) $pdo->lastInsertId();
        $st->execute(['Jugador Demo', 'demo@fastplay.es', '+34611111111', 24, 'Ceuta', 'Delantero', password_hash('Demo1234!', PASSWORD_DEFAULT), 'player', 9, 182, 15, 8]);
        $demoId = (int) $pdo->lastInsertId();

        $players = [
            ['Lucía Pérez', 'lucia@fastplay.es', 22, 'Ceuta', 'Portera',     1, 170, 0,  3],
            ['Marc Costa',  'marc@fastplay.es',  27, 'Ceuta', 'Defensa',     4, 184, 2,  5],
            ['Ana Ruiz',    'ana@fastplay.es',   26, 'Ceuta', 'Mediocampo',  8, 168, 6,  9],
            ['Iván Soto',   'ivan@fastplay.es',  25, 'Ceuta', 'Delantero',  11, 180, 18, 6],
            ['Paula Gil',   'paula@fastplay.es', 23, 'Ceuta', 'Defensa',     3, 173, 1,  4],
            ['Hugo Marín',  'hugo@fastplay.es',  29, 'Ceuta', 'Mediocampo',  6, 177, 5,  7],
        ];
        $playerIds = [];
        foreach ($players as $p) {
            $st->execute([$p[0], $p[1], '+34600' . random_int(100000, 999999), $p[2], $p[3], $p[4], password_hash('Demo1234!', PASSWORD_DEFAULT), 'player', $p[5], $p[6], $p[7], $p[8]]);
            $playerIds[] = (int) $pdo->lastInsertId();
        }

        // Equipos — todos de Ceuta, nombres reales de barrios y zonas.
        $teams = [
            ['Murube United',          'Ceuta', $demoId],
            ['Benoliel FC',            'Ceuta', $playerIds[0]],
            ['Ceuta Centro Atletico',  'Ceuta', $adminId],
            ['Principe Deportivo',     'Ceuta', $playerIds[2]],
            ['La Marina FC',           'Ceuta', $playerIds[1]],
            ['Otero Norte',            'Ceuta', $playerIds[3]],
            ['Hacho FC',               'Ceuta', $playerIds[4]],
            ['Ribera Sur',             'Ceuta', $playerIds[5]],
        ];
        $teamIds = [];
        $stT = $pdo->prepare("INSERT INTO teams (name,city,captain_id) VALUES (?,?,?)");
        $stTM = $pdo->prepare("INSERT INTO team_members (team_id,user_id) VALUES (?,?)");
        foreach ($teams as $t) {
            $stT->execute([$t[0], $t[1], $t[2]]);
            $tid = (int) $pdo->lastInsertId();
            $teamIds[] = $tid;
            $stTM->execute([$tid, $t[2]]);
        }
        // demo se une también a un par
        $stTM->execute([$teamIds[2], $demoId]);

        // Ligas — todas en Ceuta.
        $leagues = [
            ['Liga Pro Ceuta 25/26',       'Ceuta', 1, 1500.00, '2026-03-01', '2026-06-30'],
            ['Copa Local Ceuta 25/26',     'Ceuta', 1, 1500.00, '2026-03-01', '2026-06-30'],
            ['Liga Amistosa Ceuta',        'Ceuta', 0, null,    '2026-03-01', '2026-06-30'],
            ['Torneo Barrios de Ceuta',    'Ceuta', 0, null,    '2026-03-01', '2026-06-30'],
        ];
        $stL = $pdo->prepare("INSERT INTO leagues (name,city,pro,prize,start_date,end_date) VALUES (?,?,?,?,?,?)");
        $leagueIds = [];
        foreach ($leagues as $l) {
            $stL->execute($l);
            $leagueIds[] = (int) $pdo->lastInsertId();
        }
        // Inscribimos equipos en cada liga (asegurando consistencia con los partidos)
        $stLT = $pdo->prepare("INSERT INTO league_teams (league_id,team_id,points,played,won,drawn,lost,gf,ga) VALUES (?,?,?,?,?,?,?,?,?)");
        $stLT->execute([$leagueIds[0], $teamIds[0], 9,  4, 3, 0, 1, 11, 5]);  // Murube United en Liga Pro Ceuta
        $stLT->execute([$leagueIds[0], $teamIds[2], 7,  4, 2, 1, 1, 8,  6]);  // Ceuta Centro Atletico en Liga Pro Ceuta
        $stLT->execute([$leagueIds[0], $teamIds[1], 3,  3, 1, 0, 2, 4,  7]);  // Benoliel FC en Liga Pro Ceuta
        $stLT->execute([$leagueIds[0], $teamIds[3], 1,  3, 0, 1, 2, 3,  8]);  // Principe Deportivo en Liga Pro Ceuta
        $stLT->execute([$leagueIds[1], $teamIds[1], 6,  3, 2, 0, 1, 6,  3]);  // Benoliel FC en Copa Local
        $stLT->execute([$leagueIds[2], $teamIds[4], 4,  3, 1, 1, 1, 4,  4]);  // La Marina FC en Liga Amistosa
        $stLT->execute([$leagueIds[2], $teamIds[0], 2,  3, 0, 2, 1, 2,  5]);  // Murube United en Liga Amistosa
        $stLT->execute([$leagueIds[3], $teamIds[3], 8,  4, 2, 2, 0, 7,  3]);  // Principe Deportivo en Torneo Barrios

        // Campos — todos en Ceuta, con coordenadas reales para Google Maps / Leaflet.
        $fields = [
            ['Estadio Municipal Alfonso Murube', 'Ceuta', 'Calle Juan de Juanes, Ceuta',     'césped',    22, 0.00,  35.8883, -5.3162, 'https://www.google.com/maps/search/?api=1&query=Estadio+Municipal+Alfonso+Murube+Ceuta', 'Estadio principal para futbol local y competicion en Ceuta.'],
            ['Campo Jose Martinez Pirri',        'Ceuta', 'Ceuta',                            'sintético', 22, 0.00,  35.8890, -5.3070, 'https://www.google.com/maps/search/?api=1&query=Campo+Jose+Martinez+Pirri+Ceuta', 'Instalacion deportiva para entrenamientos y partidos locales.'],
            ['Campo Federativo Jose Benoliel',   'Ceuta', 'Avenida de Africa, Ceuta',         'sintético', 22, 0.00,  35.8898, -5.3262, 'https://www.google.com/maps/search/?api=1&query=Campo+Federativo+Jose+Benoliel+Ceuta', 'Campo federativo de futbol en Ceuta.'],
            ['Campo de futbol del Principe',     'Ceuta', 'Barriada Principe Alfonso, Ceuta', 'sintético', 22, 0.00,  35.8746, -5.3268, 'https://www.google.com/maps/search/?api=1&query=Campo+de+futbol+del+Principe+Ceuta', 'Campo de barrio para futbol base y encuentros locales.'],
            ['Complejo Deportivo Diaz-Flor',     'Ceuta', 'Avenida de Otero, Ceuta',          'césped',    22, 0.00,  35.8871, -5.3073, 'https://www.google.com/maps/search/?api=1&query=Complejo+Deportivo+Diaz+Flor+Ceuta', 'Complejo deportivo municipal en Ceuta.'],
            ['Polideportivo La Libertad',        'Ceuta', 'Avenida de Lisboa, Ceuta',         'sintético', 14, 0.00,  35.8844, -5.3441, 'https://www.google.com/maps/search/?api=1&query=Polideportivo+La+Libertad+Ceuta', 'Instalacion polideportiva para entrenamientos y partidos.'],
        ];
        $stF = $pdo->prepare("INSERT INTO fields (name,city,address,surface,capacity,hourly_rate,latitude,longitude,maps_url,description) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $fieldIds = [];
        foreach ($fields as $f) {
            $stF->execute($f);
            $fieldIds[] = (int) $pdo->lastInsertId();
        }
        // Partidos
        $stM = $pdo->prepare("INSERT INTO matches (home_team_id,away_team_id,league_id,field_id,scheduled_at,status,home_score,away_score,created_by) VALUES (?,?,?,?,?,?,?,?,?)");
        $stM->execute([$teamIds[0], $teamIds[1], $leagueIds[0], $fieldIds[0], '2026-06-12 19:30:00', 'confirmed', null, null, $adminId]);
        $stM->execute([$teamIds[2], $teamIds[3], $leagueIds[0], $fieldIds[1], '2026-06-15 21:00:00', 'finished',  3,    2,   $adminId]);
        $stM->execute([$teamIds[4], $teamIds[0], $leagueIds[2], $fieldIds[2], '2026-06-22 20:00:00', 'pending',   null, null, $adminId]);
        $stM->execute([$teamIds[1], $teamIds[5], null,           $fieldIds[3], '2026-07-02 18:00:00', 'confirmed', null, null, $adminId]);

        // Logros
        $achievements = [
            ['first_goal', 'Primer gol',   'Marca tu primer gol oficial.',     '🎖️'],
            ['hat_trick',  'Hat-trick',    '3 goles en un solo partido.',      '🏅'],
            ['captain',    'Capitán',      'Crea y dirige un equipo.',         '🛡️'],
            ['veteran',    'Veterano',     'Juega 10 partidos.',               '🎯'],
            ['mvp',        'MVP',          'Mejor jugador en una jornada.',    '🏆'],
        ];
        $stA = $pdo->prepare("INSERT INTO achievements (code,name,description,icon) VALUES (?,?,?,?)");
        $aids = [];
        foreach ($achievements as $a) {
            $stA->execute($a);
            $aids[] = (int) $pdo->lastInsertId();
        }
        $stUA = $pdo->prepare("INSERT INTO user_achievements (user_id,achievement_id) VALUES (?,?)");
        $stUA->execute([$demoId, $aids[0]]);
        $stUA->execute([$demoId, $aids[2]]);

        // Salas de chat
        $stR = $pdo->prepare("INSERT INTO chat_rooms (type,name) VALUES (?,?)");
        $stR->execute(['general', 'Lobby general']);
        $generalRoom = (int) $pdo->lastInsertId();
        $stR->execute(['match_negotiation', 'Capitanes — partidos amistosos']);
        $captainsRoom = (int) $pdo->lastInsertId();

        $stMsg = $pdo->prepare("INSERT INTO chat_messages (room_id,user_id,body) VALUES (?,?,?)");
        $stMsg->execute([$generalRoom, $adminId, '¡Bienvenidos a FastPlay Ceuta! Por aquí coordinamos cualquier duda.']);
        $stMsg->execute([$generalRoom, $demoId,  '¿Alguien para un 7v7 este finde en el Murube?']);
        $stMsg->execute([$captainsRoom, $playerIds[0], 'Buscamos rival amistoso este sábado, Benoliel FC disponibles.']);
    }
}
