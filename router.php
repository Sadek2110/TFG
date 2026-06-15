<?php
// Router para el servidor embebido cuando se sirve desde la raíz del proyecto.
// Sirve manualmente los archivos estáticos de public/ y delega el resto al
// front controller.
// Uso: php -S localhost:8000 router.php

declare(strict_types=1);

$ruta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$archivo = __DIR__ . '/public' . $ruta;

if ($ruta !== '/' && is_file($archivo)) {
    $tipos = [
        'css' => 'text/css',
        'js'  => 'application/javascript',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg'=> 'image/jpeg',
        'gif' => 'image/gif',
        'ico' => 'image/x-icon',
        'webp'=> 'image/webp',
        'json'=> 'application/json',
        'txt' => 'text/plain',
    ];
    $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
    if (isset($tipos[$extension])) {
        header('Content-Type: ' . $tipos[$extension]);
    }
    readfile($archivo);
    return true;
}

require __DIR__ . '/public/index.php';
