<?php
// FastPlay · Built-in PHP server router
// Simula el comportamiento de public/.htaccess

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Servir archivos estáticos directamente desde public/
$publicFile = __DIR__ . '/public' . $uri;
if ($uri !== '/' && is_file($publicFile)) {
    $ext = strtolower(pathinfo($publicFile, PATHINFO_EXTENSION));
    $mimes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'webm' => 'video/webm',
        'mp4'  => 'video/mp4',
        'webp' => 'image/webp',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        'json' => 'application/json',
    ];
    header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
    readfile($publicFile);
    exit;
}

// Redirigir /public/... a la ruta limpia
if (preg_match('#^/public/(.*)$#', $uri, $m)) {
    header('Location: /' . $m[1], true, 301);
    exit;
}

// Todo lo demás → front controller
$_GET['url'] = ltrim($uri ?? '/', '/');
require __DIR__ . '/public/index.php';
