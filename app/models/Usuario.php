<?php
// FastPlay · modelo Usuario (auth real con password_hash + rate-limit)

class Usuario
{
    /** Devuelve usuario público (sin hash) o null si las credenciales fallan. */
    public function login(string $email, string $password, string $ip = ''): ?array
    {
        if ($email === '' || $password === '') {
            return null;
        }
        if ($this->isRateLimited($email, $ip)) {
            return null;
        }
        $user = Database::one('SELECT * FROM users WHERE email = ?', [mb_strtolower($email)]);
        $ok = $user && password_verify($password, $user['password_hash']);
        $this->recordAttempt($email, $ip, $ok);
        if (!$ok) {
            return null;
        }
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            Database::run('UPDATE users SET password_hash = ? WHERE id = ?', [
                password_hash($password, PASSWORD_DEFAULT), $user['id'],
            ]);
        }
        unset($user['password_hash']);
        return $user;
    }

    /** Crea cuenta. Devuelve [user, errors]. */
    public function register(array $data): array
    {
        $errors = [];
        $name     = trim((string) ($data['name'] ?? ''));
        $email    = mb_strtolower(trim((string) ($data['email'] ?? '')));
        $phone    = trim((string) ($data['phone'] ?? ''));
        $age      = (int) ($data['age'] ?? 0);
        $city     = trim((string) ($data['city'] ?? ''));
        $position = trim((string) ($data['position'] ?? ''));
        $pass     = (string) ($data['password'] ?? '');
        $conf     = (string) ($data['password_confirm'] ?? '');

        if (!v_required($name) || mb_strlen($name) < 2)        $errors['name']  = 'Indica tu nombre completo.';
        if (!v_email($email))                                  $errors['email'] = 'Email no válido.';
        if ($age && !v_int_range($age, 14, 99))                $errors['age']   = 'La edad debe estar entre 14 y 99.';
        if ($phone !== '' && !preg_match('/^[+0-9 ()-]{6,20}$/', $phone)) {
            $errors['phone'] = 'Teléfono no válido.';
        }
        if (!v_min_len($pass, 8))                              $errors['password'] = 'Contraseña mínima de 8 caracteres.';
        if ($pass !== $conf)                                   $errors['password_confirm'] = 'Las contraseñas no coinciden.';

        if (!$errors) {
            $exists = Database::value('SELECT 1 FROM users WHERE email = ?', [$email]);
            if ($exists) {
                $errors['email'] = 'Ese email ya está registrado.';
            }
        }

        if ($errors) {
            return [null, $errors];
        }

        Database::run(
            "INSERT INTO users (name,email,phone,age,city,position,password_hash,role) VALUES (?,?,?,?,?,?,?,?)",
            [$name, $email, $phone ?: null, $age ?: null, $city ?: null, $position ?: null, password_hash($pass, PASSWORD_DEFAULT), 'player']
        );
        $user = Database::one('SELECT * FROM users WHERE id = ?', [Database::insertId()]);
        unset($user['password_hash']);
        return [$user, []];
    }

    public function find(int $id): ?array
    {
        $u = Database::one('SELECT id,name,email,phone,age,city,position,role,avatar,created_at FROM users WHERE id = ?', [$id]);
        return $u ?: null;
    }

    public function updateProfile(int $id, array $data): array
    {
        $errors = [];
        $name = trim((string) ($data['name'] ?? ''));
        $age  = (int) ($data['age'] ?? 0);
        $city = trim((string) ($data['city'] ?? ''));
        $position = trim((string) ($data['position'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));

        if (!v_required($name) || mb_strlen($name) < 2)              $errors['name'] = 'Indica tu nombre.';
        if ($age && !v_int_range($age, 14, 99))                      $errors['age']  = 'Edad inválida.';
        if ($phone !== '' && !preg_match('/^[+0-9 ()-]{6,20}$/', $phone)) $errors['phone'] = 'Teléfono inválido.';
        if ($position !== '' && !in_array($position, ['Portero','Portera','Defensa','Mediocampo','Delantero'], true)) {
            $errors['position'] = 'Posición no válida.';
        }
        if ($errors) return $errors;

        Database::run(
            'UPDATE users SET name=?, age=?, city=?, position=?, phone=? WHERE id=?',
            [$name, $age ?: null, $city ?: null, $position ?: null, $phone ?: null, $id]
        );
        return [];
    }

    public function changePassword(int $id, string $current, string $new, string $confirm): array
    {
        $errors = [];
        $row = Database::one('SELECT password_hash FROM users WHERE id = ?', [$id]);
        if (!$row || !password_verify($current, $row['password_hash'])) {
            $errors['current'] = 'La contraseña actual no es correcta.';
        }
        if (!v_min_len($new, 8))   $errors['new'] = 'Mínimo 8 caracteres.';
        if ($new !== $confirm)     $errors['confirm'] = 'Las contraseñas no coinciden.';
        if ($errors) return $errors;

        Database::run('UPDATE users SET password_hash=? WHERE id=?', [password_hash($new, PASSWORD_DEFAULT), $id]);
        return [];
    }

    public function dashboardStats(int $userId): array
    {
        $played = (int) Database::value(
            "SELECT COUNT(*) FROM matches m
             JOIN team_members tm ON tm.team_id IN (m.home_team_id, m.away_team_id)
             WHERE tm.user_id = ? AND m.status = 'finished'",
            [$userId]
        );
        $teams = (int) Database::value('SELECT COUNT(*) FROM team_members WHERE user_id=?', [$userId]);
        $captainOf = (int) Database::value('SELECT COUNT(*) FROM teams WHERE captain_id=?', [$userId]);
        $achievements = (int) Database::value('SELECT COUNT(*) FROM user_achievements WHERE user_id=?', [$userId]);
        return [
            ['i' => '⚽', 'v' => $played,       'l' => 'Partidos jugados', 'c' => '#4ade80'],
            ['i' => '👥', 'v' => $teams,        'l' => 'Equipos',          'c' => '#60a5fa'],
            ['i' => '🛡️', 'v' => $captainOf,    'l' => 'Como capitán',     'c' => '#fbbf24'],
            ['i' => '🏅', 'v' => $achievements, 'l' => 'Logros',           'c' => '#fde047'],
        ];
    }

    public function achievements(int $userId): array
    {
        return Database::all(
            'SELECT a.icon AS i, a.name AS n, a.description AS d, ua.earned_at
             FROM user_achievements ua
             JOIN achievements a ON a.id = ua.achievement_id
             WHERE ua.user_id = ?
             ORDER BY ua.earned_at DESC',
            [$userId]
        );
    }

    public function all(): array
    {
        return Database::all('SELECT id,name,email,role,city,position,created_at FROM users ORDER BY created_at DESC');
    }

    public function setRole(int $id, string $role): void
    {
        if (!in_array($role, ['player','admin'], true)) return;
        Database::run('UPDATE users SET role=? WHERE id=?', [$role, $id]);
    }

    public function delete(int $id): void
    {
        Database::run('DELETE FROM users WHERE id=?', [$id]);
    }

    // ===== Rate limit =====
    private function isRateLimited(string $email, string $ip): bool
    {
        $cutoff = gmdate('Y-m-d H:i:s', time() - 600); // SQLite datetime('now') is UTC.
        $fails = (int) Database::value(
            "SELECT COUNT(*) FROM login_attempts WHERE success = 0 AND attempted_at > ? AND (ip = ? OR email = ?)",
            [$cutoff, $ip, mb_strtolower($email)]
        );
        return $fails >= 5;
    }

    private function recordAttempt(string $email, string $ip, bool $success): void
    {
        Database::run('INSERT INTO login_attempts (ip,email,success) VALUES (?,?,?)', [
            $ip ?: '0.0.0.0', mb_strtolower($email), $success ? 1 : 0,
        ]);
        // limpieza ligera
        Database::run('DELETE FROM login_attempts WHERE attempted_at < ?', [gmdate('Y-m-d H:i:s', time() - 86400)]);
    }
}
