<section>
    <div class="barra-acciones">
        <h1>Equipos</h1>
        <?php if (Sesion::autenticado()): ?>
            <a class="boton boton--principal" href="<?= e(url('/equipos/crear')) ?>">+ Nuevo equipo</a>
        <?php endif; ?>
    </div>

    <?php if (empty($equipos)): ?>
        <div class="estado-vacio">
            <p>Aún no hay equipos. ¡Crea el primero!</p>
            <?php if (!Sesion::autenticado()): ?>
                <p><a class="boton boton--principal" href="<?= e(url('/registro')) ?>">Crear cuenta</a></p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <ul class="lista-tarjetas">
            <?php foreach ($equipos as $equipo): ?>
                <li class="tarjeta">
                    <h2><a href="<?= e(url('/equipos/' . $equipo['id'])) ?>"><?= e($equipo['nombre']) ?></a></h2>
                    <?php if (!empty($equipo['ciudad'])): ?>
                        <p><?= e($equipo['ciudad']) ?></p>
                    <?php endif; ?>
                    <p class="campo__ayuda">
                        Capitán: <?= e($equipo['nombre_capitan']) ?> &middot;
                        <?= (int) $equipo['total_miembros'] ?> miembros
                    </p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
