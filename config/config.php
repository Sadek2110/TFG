<?php

define('APP_NAME',    'FastPlay');
define('APP_URL',     'http://localhost/Proyectos/FastPlay');
define('APP_VERSION', '1.0.0');

define('DB_HOST',    getenv('DB_HOST') ?: 'localhost');
define('DB_PORT',    getenv('DB_PORT') ?: '3306');
define('DB_NAME',    getenv('DB_NAME') ?: 'fastplay');
define('DB_USER',    getenv('DB_USER') ?: 'root');
define('DB_PASS',    getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

define('SESSION_LIFETIME', 3600);
define('HASH_ALGO',        PASSWORD_BCRYPT);
define('HASH_COST',        12);

define('UPLOAD_PATH',     __DIR__ . '/../public/images/uploads');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);

define('CAPTAIN_FEE',    4.99);
define('LEAGUE_PRO_FEE', 20.00);

if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}
