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

        $token = bin2hex(random_bytes(32));
        Database::run(
            "INSERT INTO users (name,email,phone,age,city,position,password_hash,role,verification_token,email_verified) VALUES (?,?,?,?,?,?,?,?,?,?)",
            [$name, $email, $phone ?: null, $age ?: null, $city ?: null, $position ?: null, password_hash($pass, PASSWORD_DEFAULT), 'player', $token, 0]
        );
        $user = Database::one('SELECT * FROM users WHERE id = ?', [Database::insertId()]);
        unset($user['password_hash']);
        return [$user, []];
    }

    public function find(int $id): ?array
    {
        $u = Database::one(
            'SELECT id,name,email,phone,age,city,position,role,avatar,dorsal,height_cm,goals,assists,is_premium,current_team_id,email_verified,verification_token,created_at
             FROM users WHERE id = ?',
            [$id]
        );
        return $u ?: null;
    }

    public static function isPremium(int $userId): bool
    {
        $active = Database::value("SELECT 1 FROM subscriptions WHERE user_id=? AND status='active' LIMIT 1", [$userId]);
        if ($active) {
            return true;
        }
        return (bool) Database::value('SELECT 1 FROM users WHERE id=? AND is_premium=1', [$userId]);
    }

    public function updateProfile(int $id, array $data): array
    {
        $errors = [];
        $name = trim((string) ($data['name'] ?? ''));
        $age  = (int) ($data['age'] ?? 0);
        $city = trim((string) ($data['city'] ?? ''));
        $position = trim((string) ($data['position'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));

        $hasDorsal  = array_key_exists('dorsal', $data)    && trim((string) $data['dorsal']) !== '';
        $hasHeight  = array_key_exists('height_cm', $data) && trim((string) $data['height_cm']) !== '';
        $hasGoals   = array_key_exists('goals', $data)     && trim((string) $data['goals']) !== '';
        $hasAssists = array_key_exists('assists', $data)   && trim((string) $data['assists']) !== '';
        $dorsal    = $hasDorsal  ? (int) $data['dorsal']    : null;
        $height_cm = $hasHeight  ? (int) $data['height_cm'] : null;
        $goals     = $hasGoals   ? (int) $data['goals']     : 0;
        $assists   = $hasAssists ? (int) $data['assists']   : 0;

        if (!v_required($name) || mb_strlen($name) < 2)              $errors['name'] = 'Indica tu nombre.';
        if ($age && !v_int_range($age, 14, 99))                      $errors['age']  = 'Edad inválida.';
        if ($phone !== '' && !preg_match('/^[+0-9 ()-]{6,20}$/', $phone)) $errors['phone'] = 'Teléfono inválido.';
        if ($position !== '' && !in_array($position, ['Portero','Portera','Defensa','Mediocampo','Delantero'], true)) {
            $errors['position'] = 'Posición no válida.';
        }
        if ($hasDorsal  && !v_int_range($dorsal,    1,  99))  $errors['dorsal']    = 'Dorsal entre 1 y 99.';
        if ($hasHeight  && !v_int_range($height_cm, 140, 220)) $errors['height_cm'] = 'Altura entre 140 y 220 cm.';
        if ($hasGoals   && !v_int_range($goals,     0,  999)) $errors['goals']     = 'Goles entre 0 y 999.';
        if ($hasAssists && !v_int_range($assists,   0,  999)) $errors['assists']   = 'Asistencias entre 0 y 999.';
        if ($errors) return $errors;

        Database::run(
            'UPDATE users SET name=?, age=?, city=?, position=?, phone=?, dorsal=?, height_cm=?, goals=?, assists=? WHERE id=?',
            [$name, $age ?: null, $city ?: null, $position ?: null, $phone ?: null, $dorsal, $height_cm, $goals, $assists, $id]
        );
        return [];
    }

    /**
     * Procesa un upload de avatar. Devuelve [ruta_relativa_o_null, errors].
     * - Acepta JPG/PNG/WEBP hasta 2 MB.
     * - Guarda bajo public/uploads/avatars/u{id}_{time}.{ext}.
     * - Borra el avatar anterior del usuario si existía y era nuestro.
     */
    public function updateAvatar(int $id, array $file): array
    {
        $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err === UPLOAD_ERR_NO_FILE) {
            return [null, []];
        }
        if ($err !== UPLOAD_ERR_OK) {
            return [null, ['avatar' => 'No se pudo subir la imagen (código ' . $err . ').']];
        }
        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > 2 * 1024 * 1024) {
            return [null, ['avatar' => 'La imagen debe pesar menos de 2 MB.']];
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return [null, ['avatar' => 'Fichero temporal no válido.']];
        }
        $info = @getimagesize($tmp);
        if (!$info) {
            return [null, ['avatar' => 'El archivo no parece una imagen válida.']];
        }
        $extByMime = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];
        $mime = (string) ($info['mime'] ?? '');
        if (!isset($extByMime[$mime])) {
            return [null, ['avatar' => 'Formato no soportado. Usa JPG, PNG o WEBP.']];
        }
        $ext = $extByMime[$mime];

        $publicDir = APP_ROOT . '/public/uploads/avatars';
        if (!is_dir($publicDir)) {
            // @ silencia el warning si el usuario PHP no tiene permiso de escritura
            // sobre public/uploads (típico en producción). El error se reporta abajo.
            @mkdir($publicDir, 0775, true);
        }
        if (!is_dir($publicDir) || !is_writable($publicDir)) {
            return [null, ['avatar' => 'No se pudo preparar el directorio de subidas. Pide al admin que cree "public/uploads/avatars" con permisos de escritura.']];
        }
        $filename = 'u' . $id . '_' . time() . '.' . $ext;
        $dest = $publicDir . '/' . $filename;
        if (!@move_uploaded_file($tmp, $dest)) {
            return [null, ['avatar' => 'No se pudo guardar la imagen.']];
        }
        @chmod($dest, 0644);

        $relative = 'uploads/avatars/' . $filename;

        // Borrar avatar anterior del mismo usuario si era nuestro
        $previous = (string) (Database::value('SELECT avatar FROM users WHERE id = ?', [$id]) ?? '');
        if ($previous !== '' && str_starts_with($previous, 'uploads/avatars/')) {
            $prevPath = APP_ROOT . '/public/' . $previous;
            if (is_file($prevPath)) {
                @unlink($prevPath);
            }
        }
        Database::run('UPDATE users SET avatar = ? WHERE id = ?', [$relative, $id]);
        return [$relative, []];
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
        $notifications = (int) Database::value('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0', [$userId]);
        return [
            ['i' => 'bi-calendar2-check', 'v' => $played,    'l' => 'Partidos jugados', 'c' => '#4ade80'],
            ['i' => 'bi-people',          'v' => $teams,     'l' => 'Equipos',          'c' => '#60a5fa'],
            ['i' => 'bi-shield-check',    'v' => $captainOf, 'l' => 'Como capitán',     'c' => '#fbbf24'],
            ['i' => 'bi-bell',            'v' => $notifications, 'l' => 'Notificaciones', 'c' => '#38bdf8'],
        ];
    }

    /**
     * Datos compactos para la carta estilo FIFA del dashboard:
     * nombre, foto, posición, dorsal, altura, partidos, goles, asistencias y equipo principal.
     */
    public function playerCard(int $userId): array
    {
        $u = Database::one(
            'SELECT id,name,avatar,position,dorsal,height_cm,goals,assists FROM users WHERE id = ?',
            [$userId]
        );
        if (!$u) return [];
        $played = (int) Database::value(
            "SELECT COUNT(*) FROM matches m
             JOIN team_members tm ON tm.team_id IN (m.home_team_id, m.away_team_id)
             WHERE tm.user_id = ? AND m.status = 'finished'",
            [$userId]
        );
        $team = Database::one(
            "SELECT t.id, t.name, t.badge, t.city
             FROM teams t
             JOIN team_members tm ON tm.team_id = t.id
             WHERE tm.user_id = ?
             ORDER BY t.name LIMIT 1",
            [$userId]
        );
        return [
            'name'      => (string) $u['name'],
            'avatar'    => $u['avatar'] ?? null,
            'position'  => (string) ($u['position'] ?? ''),
            'dorsal'    => isset($u['dorsal']) && $u['dorsal'] !== null ? (int) $u['dorsal'] : null,
            'height_cm' => isset($u['height_cm']) && $u['height_cm'] !== null ? (int) $u['height_cm'] : null,
            'goals'     => (int) ($u['goals']   ?? 0),
            'assists'   => (int) ($u['assists'] ?? 0),
            'played'    => $played,
            'team'      => $team ?: null,
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

    /**
     * Crea un nuevo administrador desde el panel de admin.
     * Devuelve [user, errors].
     */
    public function registerAdmin(array $data): array
    {
        $errors = [];
        $name  = trim((string) ($data['name'] ?? ''));
        $email = mb_strtolower(trim((string) ($data['email'] ?? '')));
        $pass  = (string) ($data['password'] ?? '');
        $conf  = (string) ($data['password_confirm'] ?? '');

        if (!v_required($name) || mb_strlen($name) < 2)  $errors['name']  = 'Indica el nombre completo.';
        if (!v_email($email))                            $errors['email'] = 'Email no válido.';
        if (!v_min_len($pass, 8))                        $errors['password'] = 'Contraseña mínima de 8 caracteres.';
        if ($pass !== $conf)                             $errors['password_confirm'] = 'Las contraseñas no coinciden.';

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
            "INSERT INTO users (name, email, password_hash, role, email_verified) VALUES (?, ?, ?, 'admin', 1)",
            [$name, $email, password_hash($pass, PASSWORD_DEFAULT)]
        );
        $user = Database::one('SELECT * FROM users WHERE id = ?', [Database::insertId()]);
        unset($user['password_hash']);
        return [$user, []];
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

    public function registerOrLoginWithGoogle(array $googleData): array
    {
        $email = mb_strtolower(trim((string) $googleData['email']));
        $googleId = (string) $googleData['id'];
        $name = (string) $googleData['name'];
        $avatar = (string) ($googleData['avatar'] ?? '');

        $user = Database::one('SELECT * FROM users WHERE google_id = ?', [$googleId]);
        if ($user) {
            unset($user['password_hash']);
            return [$user, [], false];
        }

        $user = Database::one('SELECT * FROM users WHERE email = ?', [$email]);
        if ($user) {
            Database::run('UPDATE users SET google_id = ?, avatar = COALESCE(avatar, ?), email_verified = 1, verification_token = NULL WHERE id = ?', [$googleId, $avatar, $user['id']]);
            $user = Database::one('SELECT * FROM users WHERE id = ?', [$user['id']]);
            unset($user['password_hash']);
            return [$user, [], false];
        }

        $randomPass = bin2hex(random_bytes(16));
        try {
            Database::run(
                "INSERT INTO users (name,email,password_hash,role,google_id,avatar,email_verified,verification_token) VALUES (?,?,?,?,?,?,?,?)",
                [$name, $email, password_hash($randomPass, PASSWORD_DEFAULT), 'player', $googleId, $avatar, 1, null]
            );
        } catch (\Exception $e) {
            error_log('Google login error: ' . $e->getMessage());
            return [null, ['email' => 'Error creando el usuario con Google (revisa la base de datos).'], false];
        }
        $user = Database::one('SELECT * FROM users WHERE id = ?', [Database::insertId()]);
        unset($user['password_hash']);
        return [$user, [], true];
    }
}
