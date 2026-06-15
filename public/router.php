<?php
// Router para el servidor embebido de PHP cuando se sirve desde public/.
// Uso: php -S localhost:8000 -t public public/router.php
//      (o bien: cd public && php -S localhost:8000 router.php)

declare(strict_types=1);

$ruta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$archivo = __DIR__ . $ruta;
if ($ruta !== '/' && is_file($archivo)) {
    return false;
}
require __DIR__ . '/index.php';
