<?php
// FastPlay · configuración base + seguridad

define('APP_NAME', 'FastPlay');
define('APP_ROOT', dirname(__DIR__));
define('APP_PATH', APP_ROOT . DIRECTORY_SEPARATOR . 'app');
define('STORAGE_PATH', APP_ROOT . DIRECTORY_SEPARATOR . 'storage');
define('UPLOADS_PATH', APP_ROOT . DIRECTORY_SEPARATOR . 'uploads');

// Entorno de ejecución: 'development' o 'production'.
// Sobrescribible vía variable de entorno APP_ENV (Apache SetEnv / sistema).
if (!defined('APP_ENV')) {
    define('APP_ENV', getenv('APP_ENV') ?: 'development');
}

if (!is_dir(STORAGE_PATH)) {
    if (!mkdir(STORAGE_PATH, 0775, true) && !is_dir(STORAGE_PATH)) {
        throw new RuntimeException('No se pudo crear el directorio de almacenamiento: ' . STORAGE_PATH);
    }
}

// SQLite — sin necesidad de configurar MySQL
define('DB_DSN', 'sqlite:' . STORAGE_PATH . DIRECTORY_SEPARATOR . 'fastplay.sqlite');

// BASE_URL detecta automáticamente la ruta de instalación bajo XAMPP
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
define('BASE_URL', rtrim($scriptDir, '/'));
define('ASSETS_URL', BASE_URL);

date_default_timezone_set('Europe/Madrid');
mb_internal_encoding('UTF-8');

// ===== Sesión endurecida =====
if (session_status() === PHP_SESSION_NONE) {
    $secureCookie = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    // Acotamos la cookie a la subcarpeta de instalación para no chocar con
    // otras apps que vivan en el mismo host (típico en XAMPP).
    $cookiePath = BASE_URL !== '' ? BASE_URL . '/' : '/';
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => $cookiePath,
        'domain'   => '',
        'secure'   => $secureCookie,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    session_name('FPSESSID');
    session_start();
}

// ===== Cabeceras de seguridad =====
// Único origen de cabeceras: NO duplicar en .htaccess (mod_headers).
// CSP: 'unsafe-inline' es necesario hoy porque varias vistas (home, dashboard)
// llevan <style>/<script> inline. Pendiente migrarlas a archivos para retirarlo.
function security_headers(): void
{
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header(
        "Content-Security-Policy: default-src 'self'; "
        . "img-src 'self' data:; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
        . "font-src 'self' https://fonts.gstatic.com; "
        . "script-src 'self' 'unsafe-inline'; "
        . "connect-src 'self'; "
        . "frame-ancestors 'self'"
    );
}
security_headers();

// ===== Helpers básicos =====
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $filePath = APP_ROOT . '/public/' . ltrim($path, '/');
    $v = is_file($filePath) ? '?v=' . filemtime($filePath) : '';
    return ASSETS_URL . '/' . ltrim($path, '/') . $v;
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

// ===== Auth helpers =====
function is_auth(): bool
{
    return !empty($_SESSION['user']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    return is_auth() && (current_user()['role'] ?? 'player') === 'admin';
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    unset($user['password_hash']);
    $_SESSION['user'] = $user;
    $_SESSION['_login_at'] = time();
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

// ===== CSRF =====
function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): bool
{
    $sent = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $stored = $_SESSION['_csrf'] ?? '';
    return $stored !== '' && is_string($sent) && hash_equals($stored, $sent);
}

function require_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verify_csrf()) {
        http_response_code(419);
        die('Token CSRF inválido. Recarga la página e inténtalo otra vez.');
    }
}

// ===== Flash messages =====
function flash(string $type, string $msg): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'msg' => $msg];
}

function flash_pull(): array
{
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

// ===== Old input (re-fill forms tras error) =====
function old(string $key, $default = ''): string
{
    return e((string) ($_SESSION['_old'][$key] ?? $default));
}

function flash_old(array $data): void
{
    unset($data['password'], $data['password_confirm'], $data['_csrf']);
    $_SESSION['_old'] = $data;
}

function old_clear(): void
{
    unset($_SESSION['_old']);
}

// ===== Validación =====
function v_required($v): bool { return is_string($v) ? trim($v) !== '' : !empty($v); }
function v_email($v): bool { return is_string($v) && filter_var($v, FILTER_VALIDATE_EMAIL) !== false; }
function v_min_len($v, int $n): bool { return is_string($v) && mb_strlen($v) >= $n; }
function v_int_range($v, int $min, int $max): bool { $i = (int) $v; return $i >= $min && $i <= $max; }