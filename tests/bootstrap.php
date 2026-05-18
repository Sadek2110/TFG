<?php
// FastPlay · test bootstrap
// Define constants y funciones necesarias sin cargar config.php completo

// === Constantes ===
define('FASTPLAY_TESTING', true);
$_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
define('APP_NAME', 'FastPlay');
define('APP_ROOT', dirname(__DIR__));
define('APP_PATH', APP_ROOT . DIRECTORY_SEPARATOR . 'app');
define('STORAGE_PATH', APP_ROOT . DIRECTORY_SEPARATOR . 'storage');
define('SESSIONS_PATH', STORAGE_PATH . DIRECTORY_SEPARATOR . 'sessions');
define('UPLOADS_PATH', APP_ROOT . DIRECTORY_SEPARATOR . 'uploads');
define('APP_ENV', 'development');
define('DB_DSN', 'sqlite:' . STORAGE_PATH . DIRECTORY_SEPARATOR . 'fastplay_test.sqlite');
define('DB_USER', '');
define('DB_PASS', '');

$_SERVER['SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', rtrim($scriptDir, '/'));
define('ASSETS_URL', BASE_URL);

date_default_timezone_set('Europe/Madrid');
mb_internal_encoding('UTF-8');

// === Directorios ===
if (!is_dir(STORAGE_PATH)) {
    mkdir(STORAGE_PATH, 0775, true);
}
if (!is_dir(SESSIONS_PATH)) {
    mkdir(SESSIONS_PATH, 0775, true);
}
// Limpiar DB de test previa
$testDbFile = STORAGE_PATH . DIRECTORY_SEPARATOR . 'fastplay_test.sqlite';
if (file_exists($testDbFile)) {
    unlink($testDbFile);
}

// === Sesión ===
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.save_path', SESSIONS_PATH);
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    @session_start();
}

// === Helpers (mismos que config/config.php) ===
function security_headers(): void {}
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function url(string $path = ''): string {
    return BASE_URL . '/' . ltrim($path, '/');
}
function asset(string $path): string {
    $filePath = APP_ROOT . '/public/' . ltrim($path, '/');
    $v = is_file($filePath) ? '?v=' . filemtime($filePath) : '';
    return ASSETS_URL . '/' . ltrim($path, '/') . $v;
}
function redirect(string $path): void {
    throw new RuntimeException("redirect($path) - no permitido en tests");
}
function is_auth(): bool {
    return !empty($_SESSION['user']);
}
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}
function is_admin(): bool {
    return is_auth() && (current_user()['role'] ?? 'player') === 'admin';
}
function login_user(array $user): void {
    session_regenerate_id(true);
    unset($user['password_hash']);
    $_SESSION['user'] = $user;
    $_SESSION['_login_at'] = time();
}
function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}
function verify_csrf(): bool {
    $sent = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $stored = $_SESSION['_csrf'] ?? '';
    return $stored !== '' && is_string($sent) && hash_equals($stored, $sent);
}
function require_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verify_csrf()) {
        throw new RuntimeException('CSRF inválido');
    }
}
function flash(string $type, string $msg): void {
    $_SESSION['_flash'][] = ['type' => $type, 'msg' => $msg];
}
function flash_pull(): array {
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}
function old(string $key, $default = ''): string {
    return e((string) ($_SESSION['_old'][$key] ?? $default));
}
function flash_old(array $data): void {
    unset($data['password'], $data['password_confirm'], $data['_csrf']);
    $_SESSION['_old'] = $data;
}
function old_clear(): void {
    unset($_SESSION['_old']);
}
function v_required($v): bool { return is_string($v) ? trim($v) !== '' : !empty($v); }
function v_email($v): bool { return is_string($v) && filter_var($v, FILTER_VALIDATE_EMAIL) !== false; }
function v_min_len($v, int $n): bool { return is_string($v) && mb_strlen($v) >= $n; }
function v_int_range($v, int $min, int $max): bool { $i = (int) $v; return $i >= $min && $i <= $max; }

// === Cargar clases del proyecto ===
require_once APP_PATH . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Database.php';
require_once APP_PATH . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Controller.php';
require_once APP_PATH . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Router.php';

foreach (glob(APP_PATH . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . '*.php') as $file) {
    require_once $file;
}

// Algunos tests necesitan controladores
foreach (glob(APP_PATH . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . '*.php') as $file) {
    require_once $file;
}

// === Helpers para tests ===

/** Limpia todas las tablas de la BD y resetea la sesión */
function test_reset(): void
{
    $pdo = Database::pdo();
    $pdo->exec('PRAGMA foreign_keys = OFF');
    $tables = ['user_achievements','chat_messages','chat_rooms','matches','league_teams','leagues','team_members','teams','fields','achievements','login_attempts','users'];
    foreach ($tables as $t) {
        $pdo->exec("DELETE FROM \"$t\"");
    }
    $pdo->exec('PRAGMA foreign_keys = ON');
    $_SESSION = [];
    $_POST = [];
}

/** Crea un usuario demo y devuelve su array */
function test_create_user(string $name = 'Test User', string $email = 'test@test.com', string $password = 'password123', string $role = 'player'): array
{
    Database::run(
        "INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)",
        [$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]
    );
    $id = Database::insertId();
    return Database::one('SELECT id,name,email,role FROM users WHERE id=?', [$id]);
}

/** Crea un equipo demo */
function test_create_team(string $name = 'Test FC', string $city = 'Madrid', int $captainId = 1): array
{
    $captain = Database::value('SELECT 1 FROM users WHERE id=?', [$captainId]);
    if (!$captain) {
        Database::run(
            'INSERT INTO users (id,name,email,password_hash,role) VALUES (?,?,?,?,?)',
            [$captainId, 'Captain ' . $captainId, 'captain' . $captainId . '@test.com', password_hash('password123', PASSWORD_DEFAULT), 'player']
        );
    }
    Database::run('INSERT INTO teams (name,city,captain_id) VALUES (?,?,?)', [$name, $city, $captainId]);
    $id = Database::insertId();
    Database::run('INSERT INTO team_members (team_id,user_id) VALUES (?,?)', [$id, $captainId]);
    return Database::one('SELECT * FROM teams WHERE id=?', [$id]);
}

/** Crea una liga demo */
function test_create_league(string $name = 'Test Liga', string $city = 'Madrid', string $start = '2026-06-01', string $end = '2026-12-31'): array
{
    Database::run(
        "INSERT INTO leagues (name,city,start_date,end_date,status) VALUES (?,?,?,?,'open')",
        [$name, $city, $start, $end]
    );
    return Database::one('SELECT * FROM leagues WHERE id=?', [Database::insertId()]);
}

/** Crea un campo demo */
function test_create_field(string $name = 'Test Campo', string $city = 'Madrid'): array
{
    Database::run(
        'INSERT INTO fields (name,city,surface,capacity,hourly_rate) VALUES (?,?,?,?,?)',
        [$name, $city, 'césped', 22, 30.0]
    );
    return Database::one('SELECT * FROM fields WHERE id=?', [Database::insertId()]);
}
