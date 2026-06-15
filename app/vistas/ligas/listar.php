<section>
    <div class="barra-acciones">
        <h1>Ligas</h1>
        <?php if (Sesion::esAdministrador()): ?>
            <a class="boton boton--principal" href="<?= e(url('/ligas/crear')) ?>">+ Nueva liga</a>
        <?php endif; ?>
    </div>

    <?php if (empty($ligas)): ?>
        <div class="estado-vacio">No hay ligas creadas todavía.</div>
    <?php else: ?>
        <ul class="lista-tarjetas">
            <?php foreach ($ligas as $l): ?>
                <li class="tarjeta">
                    <h2><a href="<?= e(url('/ligas/' . $l['id'])) ?>"><?= e($l['nombre']) ?></a></h2>
                    <p>
                        Temporada <?= e($l['temporada']) ?> &middot;
                        <?= (int) $l['total_equipos'] ?> equipos
                    </p>
                    <?php if (!empty($l['descripcion'])): ?>
                        <p class="campo__ayuda"><?= e($l['descripcion']) ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
