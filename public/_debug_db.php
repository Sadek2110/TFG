<?php
// FastPlay · script TEMPORAL de diagnóstico de conexión a BD.
// BORRAR este archivo en cuanto resuelvas el 500.
//
// Uso: https://<tu-dominio>/_debug_db.php?token=fastplay-debug-2026

declare(strict_types=1);

$EXPECTED_TOKEN = 'fastplay-debug-2026';
if (($_GET['token'] ?? '') !== $EXPECTED_TOKEN) {
    http_response_code(404);
    exit('Not found');
}

header('Content-Type: text/plain; charset=UTF-8');

function log_step(string $label, $value = null): void
{
    $line = '[debug_db] ' . $label;
    if ($value !== null) {
        $line .= ': ' . (is_scalar($value) ? (string) $value : print_r($value, true));
    }
    echo $line . PHP_EOL;
    error_log($line);
}

function mask(?string $v): string
{
    if ($v === null || $v === '') return '(vacío)';
    $len = strlen($v);
    return $len <= 4 ? str_repeat('*', $len) : substr($v, 0, 2) . str_repeat('*', $len - 4) . substr($v, -2);
}

// ---------------------------------------------------------------------------
// 1. Entorno PHP
// ---------------------------------------------------------------------------
log_step('PHP version', PHP_VERSION);
log_step('SAPI', PHP_SAPI);
log_step('pdo_mysql cargado', extension_loaded('pdo_mysql') ? 'SI' : 'NO');
log_step('pdo_sqlite cargado', extension_loaded('pdo_sqlite') ? 'SI' : 'NO');
log_step('Drivers PDO disponibles', implode(', ', PDO::getAvailableDrivers()));

echo PHP_EOL;

// ---------------------------------------------------------------------------
// 2. Variables de entorno (las que la app espera)
// ---------------------------------------------------------------------------
$envKeys = ['APP_ENV', 'DB_DRIVER', 'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'];
foreach ($envKeys as $k) {
    $raw = getenv($k);
    $val = $raw === false ? '(no definida)' : ($k === 'DB_PASS' ? mask($raw) : $raw);
    log_step("env $k", $val);
}

echo PHP_EOL;

// ---------------------------------------------------------------------------
// 3. Cargar config.php y ver qué constantes definió
// ---------------------------------------------------------------------------
try {
    require_once __DIR__ . '/../config/config.php';
    log_step('config.php cargado', 'OK');
    log_step('DB_DRIVER (constante)', defined('DB_DRIVER') ? DB_DRIVER : '(no definida)');
    log_step('DB_DSN (constante)', defined('DB_DSN') ? DB_DSN : '(no definida)');
    log_step('DB_USER (constante)', defined('DB_USER') ? (DB_USER ?? '(null)') : '(no definida)');
    log_step('DB_PASS (constante)', defined('DB_PASS') ? mask(DB_PASS) : '(no definida)');
} catch (Throwable $e) {
    log_step('ERROR cargando config.php', $e->getMessage());
    exit;
}

echo PHP_EOL;

// ---------------------------------------------------------------------------
// 4. Resolución DNS del host (solo MySQL/PostgreSQL)
// ---------------------------------------------------------------------------
$host = getenv('DB_HOST') ?: null;
if ($host && DB_DRIVER !== 'sqlite') {
    $ip = gethostbyname($host);
    log_step("DNS $host -> $ip", $ip === $host ? 'NO RESUELVE (host no encontrado)' : 'OK');
}

echo PHP_EOL;

// ---------------------------------------------------------------------------
// 5. Conexión PDO cruda (sin pasar por Database.php todavía)
// ---------------------------------------------------------------------------
try {
    log_step('Intentando new PDO()', '...');
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ]);
    log_step('PDO conectado', 'OK');
    log_step('Driver activo', $pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
    log_step('Versión servidor', $pdo->getAttribute(PDO::ATTR_SERVER_VERSION));
} catch (Throwable $e) {
    log_step('FALLO PDO', $e->getMessage());
    log_step('SQLSTATE', $e->getCode());
    exit;
}

echo PHP_EOL;

// ---------------------------------------------------------------------------
// 6. Query de sanidad
// ---------------------------------------------------------------------------
try {
    $row = $pdo->query('SELECT 1 AS ok')->fetch(PDO::FETCH_ASSOC);
    log_step('SELECT 1', json_encode($row));
} catch (Throwable $e) {
    log_step('FALLO SELECT 1', $e->getMessage());
    exit;
}

// ---------------------------------------------------------------------------
// 7. ¿Existe el esquema?
// ---------------------------------------------------------------------------
try {
    $tables = [];
    if (DB_DRIVER === 'mysql') {
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    } elseif (DB_DRIVER === 'pgsql') {
        $tables = $pdo->query(
            "SELECT tablename FROM pg_tables WHERE schemaname='public'"
        )->fetchAll(PDO::FETCH_COLUMN);
    } elseif (DB_DRIVER === 'sqlite') {
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    }
    log_step('Tablas encontradas (' . count($tables) . ')', implode(', ', $tables) ?: '(ninguna)');

    if (in_array('users', $tables, true)) {
        $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        log_step('COUNT(*) users', $count);
    } else {
        log_step('AVISO', 'tabla users no existe — falta cargar database/fastplay_mysql.sql');
    }
} catch (Throwable $e) {
    log_step('FALLO leyendo esquema', $e->getMessage());
}

echo PHP_EOL . '[debug_db] FIN' . PHP_EOL;
