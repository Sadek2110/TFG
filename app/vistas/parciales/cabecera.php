<?php $usuario = Sesion::usuario(); ?>
<header class="cabecera">
    <div class="contenedor cabecera__interior">
        <a class="cabecera__marca" href="<?= e(url('/')) ?>">
            <img class="cabecera__logo" src="<?= e(url('/imagenes/logo.png')) ?>"
                 alt="" width="40" height="40" decoding="async">
            <img class="cabecera__marca-img" src="<?= e(url('/imagenes/logo-nombre.png')) ?>"
                 alt="FastPlay" width="120" height="38" decoding="async">
        </a>

        <button type="button" class="cabecera__hamburger" aria-label="Abrir menú"
                aria-expanded="false" data-menu-toggle>
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="cabecera__menu" data-menu>
            <nav class="navegacion" aria-label="Navegación principal">
                <a href="<?= e(url('/equipos')) ?>"  class="<?= ruta_activa('/equipos') ?>">Equipos</a>
                <a href="<?= e(url('/partidos')) ?>" class="<?= ruta_activa('/partidos') ?>">Partidos</a>
                <a href="<?= e(url('/campos')) ?>"   class="<?= ruta_activa('/campos') ?>">Campos</a>
                <a href="<?= e(url('/ligas')) ?>"    class="<?= ruta_activa('/ligas') ?>">Ligas</a>
            </nav>

            <div class="cabecera__usuario">
                <button type="button" class="boton boton--enlace cabecera__tema"
                        data-toggle-tema aria-pressed="false" aria-label="Cambiar a tema oscuro">
                    <i class="fa-solid fa-moon"></i> Oscuro
                </button>
                <?php if ($usuario): ?>
                    <a href="<?= e(url('/perfil')) ?>" class="cabecera__perfil <?= ruta_activa('/perfil') ?>">
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                        Perfil
                    </a>
                    <?php if (Sesion::esAdministrador()): ?>
                        <a href="<?= e(url('/admin')) ?>" class="<?= ruta_activa('/admin') ?>">Admin</a>
                    <?php endif; ?>
                    <form method="post" action="<?= e(url('/cerrar-sesion')) ?>" class="cabecera__cerrar">
                        <?= Csrf::campo() ?>
                        <button type="submit" class="boton boton--enlace">
                            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                            Cerrar sesión
                        </button>
                    </form>
                <?php else: ?>
                    <a href="<?= e(url('/iniciar-sesion')) ?>" class="<?= ruta_activa('/iniciar-sesion') ?>">Iniciar sesión</a>
                    <a href="<?= e(url('/registro')) ?>" class="boton boton--principal">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
