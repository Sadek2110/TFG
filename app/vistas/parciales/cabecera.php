<?php $usuario = Sesion::usuario(); ?>
<header class="cabecera">
    <div class="contenedor cabecera__interior">
        <a class="cabecera__marca" href="<?= e(url('/')) ?>">
            <span class="cabecera__logo">FP</span>
            FastPlay
        </a>

        <nav class="navegacion" aria-label="Navegación principal">
            <a href="<?= e(url('/equipos')) ?>"  class="<?= ruta_activa('/equipos') ?>">Equipos</a>
            <a href="<?= e(url('/partidos')) ?>" class="<?= ruta_activa('/partidos') ?>">Partidos</a>
            <a href="<?= e(url('/campos')) ?>"   class="<?= ruta_activa('/campos') ?>">Campos</a>
            <a href="<?= e(url('/ligas')) ?>"    class="<?= ruta_activa('/ligas') ?>">Ligas</a>
        </nav>

        <div class="cabecera__usuario">
            <?php if ($usuario): ?>
                <a href="<?= e(url('/perfil')) ?>" class="<?= ruta_activa('/perfil') ?>">
                    <?= e($usuario['nombre']) ?>
                </a>
                <?php if (Sesion::esAdministrador()): ?>
                    <a href="<?= e(url('/admin')) ?>" class="<?= ruta_activa('/admin') ?>">Admin</a>
                <?php endif; ?>
                <form method="post" action="<?= e(url('/cerrar-sesion')) ?>" class="cabecera__cerrar">
                    <?= Csrf::campo() ?>
                    <button type="submit" class="boton boton--enlace">Cerrar sesión</button>
                </form>
            <?php else: ?>
                <a href="<?= e(url('/iniciar-sesion')) ?>" class="<?= ruta_activa('/iniciar-sesion') ?>">Iniciar sesión</a>
                <a href="<?= e(url('/registro')) ?>" class="boton boton--principal">Registrarse</a>
            <?php endif; ?>
        </div>
    </div>
</header>
