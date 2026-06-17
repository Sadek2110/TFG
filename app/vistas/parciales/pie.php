<footer class="pie">
    <div class="contenedor pie__interior">
        <div class="pie__rejilla">
            <div class="pie__marca">
                <span class="pie__marca-titulo">
                    <img src="<?= e(url('/imagenes/logo.png')) ?>" alt="" width="32" height="32" decoding="async">
                    FastPlay
                </span>
                <p class="pie__lema">
                    Organiza tu fútbol amateur: equipos, partidos, campos y ligas
                    en un solo sitio, sin complicaciones.
                </p>
            </div>

            <nav class="pie__col" aria-label="Secciones">
                <h3>Explorar</h3>
                <ul class="pie__enlaces">
                    <li><a href="<?= e(url('/equipos')) ?>">Equipos</a></li>
                    <li><a href="<?= e(url('/partidos')) ?>">Partidos</a></li>
                    <li><a href="<?= e(url('/campos')) ?>">Campos</a></li>
                    <li><a href="<?= e(url('/ligas')) ?>">Ligas</a></li>
                </ul>
            </nav>

            <nav class="pie__col" aria-label="Tu cuenta">
                <h3>Cuenta</h3>
                <ul class="pie__enlaces">
                    <?php if (Sesion::usuario()): ?>
                        <li><a href="<?= e(url('/perfil')) ?>">Mi perfil</a></li>
                    <?php else: ?>
                        <li><a href="<?= e(url('/iniciar-sesion')) ?>">Iniciar sesión</a></li>
                        <li><a href="<?= e(url('/registro')) ?>">Registrarse</a></li>
                    <?php endif; ?>
                    <li><a href="<?= e(url('/')) ?>">Inicio</a></li>
                </ul>
            </nav>
        </div>

        <p class="pie__legal">
            <span>FastPlay &middot; TFG 2.º DAW &middot; <?= date('Y') ?></span>
            <span>Hecho por Sadek Ben Jouda Akil</span>
        </p>
    </div>
</footer>
