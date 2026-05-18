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
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $pdo->exec('PRAGMA foreign_keys = ON;');
            self::migrate($pdo);
        }
        return $pdo;
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
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS teams (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            city TEXT NOT NULL,
            badge TEXT DEFAULT '🛡️',
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
                captain_id INTEGER NOT NULL,
                created_at TEXT NOT NULL DEFAULT (datetime('now')),
                FOREIGN KEY (captain_id) REFERENCES users(id) ON DELETE RESTRICT
            )");
            $pdo->exec("INSERT INTO teams_new SELECT * FROM teams");
            $pdo->exec("DROP TABLE teams");
            $pdo->exec("ALTER TABLE teams_new RENAME TO teams");
            $pdo->exec('COMMIT');
            $pdo->exec('PRAGMA foreign_keys = ON');
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS team_members (
            team_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
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
            hourly_rate REAL NOT NULL DEFAULT 0
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS matches (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            home_team_id INTEGER NOT NULL,
            away_team_id INTEGER NOT NULL,
            league_id INTEGER,
            field_id INTEGER,
            scheduled_at TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'pending',
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
        self::repairConsistency($pdo);
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

        // Admin + jugador demo (sólo en dev por el guard de APP_ENV)
        $st = $pdo->prepare("INSERT INTO users (name,email,phone,age,city,position,password_hash,role) VALUES (?,?,?,?,?,?,?,?)");
        $st->execute(['Sadek Admin', 'admin@fastplay.es', '+34600000000', 28, 'Madrid', 'Mediocampo', password_hash('Admin1234!', PASSWORD_DEFAULT), 'admin']);
        $adminId = (int) $pdo->lastInsertId();
        $st->execute(['Jugador Demo', 'demo@fastplay.es', '+34611111111', 24, 'Madrid', 'Delantero', password_hash('Demo1234!', PASSWORD_DEFAULT), 'player']);
        $demoId = (int) $pdo->lastInsertId();

        $players = [
            ['Lucía Pérez', 'lucia@fastplay.es', 22, 'Barcelona', 'Portera'],
            ['Marc Costa',  'marc@fastplay.es',  27, 'Valencia',  'Defensa'],
            ['Ana Ruiz',    'ana@fastplay.es',   26, 'Sevilla',   'Mediocampo'],
            ['Iván Soto',   'ivan@fastplay.es',  25, 'Bilbao',    'Delantero'],
            ['Paula Gil',   'paula@fastplay.es', 23, 'Zaragoza',  'Defensa'],
            ['Hugo Marín',  'hugo@fastplay.es',  29, 'Málaga',    'Mediocampo'],
        ];
        $playerIds = [];
        foreach ($players as $p) {
            $st->execute([$p[0], $p[1], '+34600' . random_int(100000, 999999), $p[2], $p[3], $p[4], password_hash('Demo1234!', PASSWORD_DEFAULT), 'player']);
            $playerIds[] = (int) $pdo->lastInsertId();
        }

        // Equipos
        $teams = [
            ['Madrid Real C.F.', 'Madrid',    $demoId],
            ['Barça Amateurs',   'Barcelona', $playerIds[0]],
            ['Atlético Centro',  'Madrid',    $adminId],
            ['Sevilla Street',   'Sevilla',   $playerIds[2]],
            ['Valencia Calle',   'Valencia',  $playerIds[1]],
            ['Bilbao Norte',     'Bilbao',    $playerIds[3]],
            ['Zaragoza FC',      'Zaragoza',  $playerIds[4]],
            ['Málaga Costa',     'Málaga',    $playerIds[5]],
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

        // Ligas
        $leagues = [
            ['Liga Pro Madrid 25/26',     'Madrid',    1, 1500.00, '2026-03-01', '2026-06-30'],
            ['Liga Pro Barcelona 25/26',  'Barcelona', 1, 1500.00, '2026-03-01', '2026-06-30'],
            ['Liga Amistosa Valencia',    'Valencia',  0, null,    '2026-03-01', '2026-06-30'],
            ['Liga Amistosa Sevilla',     'Sevilla',   0, null,    '2026-03-01', '2026-06-30'],
        ];
        $stL = $pdo->prepare("INSERT INTO leagues (name,city,pro,prize,start_date,end_date) VALUES (?,?,?,?,?,?)");
        $leagueIds = [];
        foreach ($leagues as $l) {
            $stL->execute($l);
            $leagueIds[] = (int) $pdo->lastInsertId();
        }
        // Inscribimos equipos en cada liga (asegurando consistencia con los partidos)
        $stLT = $pdo->prepare("INSERT INTO league_teams (league_id,team_id,points,played,won,drawn,lost,gf,ga) VALUES (?,?,?,?,?,?,?,?,?)");
        $stLT->execute([$leagueIds[0], $teamIds[0], 9,  4, 3, 0, 1, 11, 5]);  // Madrid Real en Liga Madrid
        $stLT->execute([$leagueIds[0], $teamIds[2], 7,  4, 2, 1, 1, 8,  6]);  // Atlético Centro en Liga Madrid
        $stLT->execute([$leagueIds[0], $teamIds[1], 3,  3, 1, 0, 2, 4,  7]);  // Barça Amateurs en Liga Madrid (para el partido vs Madrid Real)
        $stLT->execute([$leagueIds[0], $teamIds[3], 1,  3, 0, 1, 2, 3,  8]);  // Sevilla Street en Liga Madrid (para el partido vs Atlético Centro)
        $stLT->execute([$leagueIds[1], $teamIds[1], 6,  3, 2, 0, 1, 6,  3]);  // Barça Amateurs en Liga Barcelona
        $stLT->execute([$leagueIds[2], $teamIds[4], 4,  3, 1, 1, 1, 4,  4]);  // Valencia Calle en Liga Valencia
        $stLT->execute([$leagueIds[2], $teamIds[0], 2,  3, 0, 2, 1, 2,  5]);  // Madrid Real en Liga Valencia (para el partido vs Valencia Calle)
        $stLT->execute([$leagueIds[3], $teamIds[3], 8,  4, 2, 2, 0, 7,  3]);  // Sevilla Street en Liga Sevilla

        // Campos
        $fields = [
            ['La Cantera',         'Madrid',    'Av. de las Glorietas 12', 'césped',     22, 35.00],
            ['Pista 4',            'Madrid',    'Polideportivo Centro',    'sintético',  14, 22.00],
            ['Polideportivo Sur',  'Valencia',  'C/ del Mar, 3',           'césped',     22, 30.00],
            ['Camp Nou Petit',     'Barcelona', 'C/ de Sants, 88',         'césped',     22, 40.00],
            ['Sevilla Sur',        'Sevilla',   'Av. Heliópolis 21',       'tierra',     14, 18.00],
        ];
        $stF = $pdo->prepare("INSERT INTO fields (name,city,address,surface,capacity,hourly_rate) VALUES (?,?,?,?,?,?)");
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
        $stMsg->execute([$generalRoom, $adminId, '¡Bienvenidos a FastPlay! Por aquí coordinamos cualquier duda.']);
        $stMsg->execute([$generalRoom, $demoId,  '¿Alguien para un 7v7 este finde en Madrid?']);
        $stMsg->execute([$captainsRoom, $playerIds[0], 'Buscamos rival amistoso este sábado, Barça Amateurs disponibles.']);
    }
}
