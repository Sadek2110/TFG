<section>
    <div class="barra-acciones">
        <h1>Campos</h1>
        <?php if (Sesion::esAdministrador()): ?>
            <a class="boton boton--principal" href="<?= e(url('/campos/crear')) ?>">+ Nuevo campo</a>
        <?php endif; ?>
    </div>

    <?php if (empty($campos)): ?>
        <div class="estado-vacio">Todavía no se han registrado campos.</div>
    <?php else: ?>
        <ul class="lista-tarjetas">
            <?php foreach ($campos as $c): ?>
                <li class="tarjeta">
                    <h2><?= e($c['nombre']) ?></h2>
                    <p>
                        <?php if (!empty($c['ciudad'])): ?><?= e($c['ciudad']) ?><?php endif; ?>
                        <?php if (!empty($c['superficie'])): ?>
                            &middot; <span class="etiqueta"><?= e($c['superficie']) ?></span>
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($c['direccion'])): ?>
                        <p class="campo__ayuda"><?= e($c['direccion']) ?></p>
                    <?php endif; ?>
                    <?php if (Sesion::esAdministrador()): ?>
                        <div class="tarjeta__pie">
                            <form method="post" action="<?= e(url('/campos/' . $c['id'] . '/eliminar')) ?>"
                                  data-confirmar="¿Eliminar este campo?">
                                <?= Csrf::campo() ?>
                                <button type="submit" class="boton boton--enlace">Eliminar</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
