<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="FastPlay — Plataforma de fútbol amateur. Crea equipos, organiza partidos y compite en ligas locales.">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<title><?= e($title ?? 'FastPlay') ?></title>
<link rel="icon" type="image/x-icon" href="<?= asset('favicon.ico') ?>">
<link rel="apple-touch-icon" href="<?= asset('apple-touch-icon.png') ?>">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<div class="fp-bg-glow"></div>
<?php $this->partial('flash', ['_flash' => $_flash ?? []]); ?>
<div id="app">
    <?= $content ?>
</div>
</body>
</html>