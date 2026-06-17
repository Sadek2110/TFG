<?php /** @var string $contenido */ ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo ?? 'FastPlay') ?></title>
    <link rel="icon" href="<?= e(url('/imagenes/icono-pag.ico')) ?>" sizes="any">
    <meta name="theme-color" content="#16a34a">
    <meta name="description" content="FastPlay: organiza tu fútbol amateur. Equipos, partidos, campos y ligas en un solo sitio.">
    <!-- Marca el documento como "con JavaScript" antes de pintar, para que las
         animaciones de revelado solo oculten contenido si el JS va a revelarlo.
         Sin JS, todo se ve igualmente. -->
    <script>document.documentElement.classList.add('js');</script>
    <link rel="stylesheet" href="<?= e(url('/css/estilos.css')) ?>">
</head>
<body>
    <?php require RUTA_VISTAS . '/parciales/cabecera.php'; ?>

    <main class="contenedor">
        <?php require RUTA_VISTAS . '/parciales/mensajes.php'; ?>
        <?= $contenido ?>
    </main>

    <?php require RUTA_VISTAS . '/parciales/pie.php'; ?>
    <?php require RUTA_VISTAS . '/parciales/banner-cookies.php'; ?>

    <!-- JavaScript del proyecto. Cada módulo es independiente (IIFE) y solo
         actúa si encuentra su elemento raíz, así que cargarlos en todas las
         páginas es seguro. -->
    <script src="<?= e(url('/js/principal.js')) ?>" defer></script>
    <script src="<?= e(url('/js/tema.js')) ?>" defer></script>
    <script src="<?= e(url('/js/cookies.js')) ?>" defer></script>
    <script src="<?= e(url('/js/validacion.js')) ?>" defer></script>
    <script src="<?= e(url('/js/animaciones-scroll.js')) ?>" defer></script>
    <script src="<?= e(url('/js/inicio.js')) ?>" defer></script>
    <script src="<?= e(url('/js/carta-jugador.js')) ?>" defer></script>
    <script src="<?= e(url('/js/panel-contextual.js')) ?>" defer></script>
    <script src="<?= e(url('/js/detalle-equipo.js')) ?>" defer></script>
</body>
</html>
