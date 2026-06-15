<?php /** @var string $contenido */ ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo ?? 'FastPlay') ?></title>
    <link rel="stylesheet" href="<?= e(url('/css/estilos.css')) ?>">
</head>
<body>
    <?php require RUTA_VISTAS . '/parciales/cabecera.php'; ?>

    <main class="contenedor">
        <?php require RUTA_VISTAS . '/parciales/mensajes.php'; ?>
        <?= $contenido ?>
    </main>

    <?php require RUTA_VISTAS . '/parciales/pie.php'; ?>

    <script src="<?= e(url('/js/principal.js')) ?>" defer></script>
    <script src="<?= e(url('/js/validacion.js')) ?>" defer></script>
</body>
</html>
