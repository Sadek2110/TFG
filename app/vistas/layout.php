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
    <!-- Marca JS y aplica el tema antes de cargar CSS para evitar el flash claro
         cuando el usuario tiene guardado el modo oscuro. -->
    <script>
        (function () {
            var raiz = document.documentElement;
            raiz.classList.add('js');
            try {
                var tema = localStorage.getItem('fp_tema');
                if (!tema) {
                    tema = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
                        ? 'oscuro'
                        : 'claro';
                }
                raiz.dataset.tema = tema;
            } catch (error) {
                raiz.dataset.tema = 'claro';
            }
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= e(url('/css/estilos.css')) ?>">
</head>
<body>
    <a class="salto-contenido" href="#contenido">Saltar al contenido</a>

    <?php require RUTA_VISTAS . '/parciales/cabecera.php'; ?>

    <main id="contenido" class="contenedor">
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
    <script src="<?= e(url('/js/hero.js')) ?>" defer></script>
    <script src="<?= e(url('/js/inicio.js')) ?>" defer></script>
    <script src="<?= e(url('/js/carta-jugador.js')) ?>" defer></script>
    <script src="<?= e(url('/js/panel-contextual.js')) ?>" defer></script>
    <script src="<?= e(url('/js/detalle-equipo.js')) ?>" defer></script>
</body>
</html>
