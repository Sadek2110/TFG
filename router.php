<?php
// FastPlay · Built-in PHP server router
// Simula el comportamiento de public/.htaccess

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Servir archivos estáticos directamente desde public/
$publicFile = __DIR__ . '/public' . $uri;
if ($uri !== '/' && is_file($publicFile)) {
    return false;
}

// Si es un directorio existente, devolver false
if (is_dir($publicFile)) {
    return false;
}

// Redirigir /public/... a la ruta limpia
if (preg_match('#^/public/(.*)$#', $uri, $m)) {
    header('Location: /' . $m[1], true, 301);
    exit;
}

// Todo lo demás → front controller
$_GET['url'] = ltrim($uri ?? '/', '/');
require __DIR__ . '/public/index.php';
