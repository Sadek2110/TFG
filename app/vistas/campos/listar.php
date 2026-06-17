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
        <ul class="lista-tarjetas lista-campos">
            <?php foreach ($campos as $c): ?>
                <?php
                $foto = $c['foto'] ?? '';
                $foto = $foto !== '' ? $foto : '/imagenes/hero-poster.jpg';
                $srcFoto = str_starts_with($foto, 'http://') || str_starts_with($foto, 'https://') ? $foto : url($foto);
                $ubicacion = trim((string) (($c['direccion'] ?? '') . ' ' . ($c['ciudad'] ?? '')));
                $maps = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($ubicacion !== '' ? $ubicacion : (string) $c['nombre']);
                ?>
                <li class="tarjeta campo-card">
                    <img class="campo-card__foto" src="<?= e($srcFoto) ?>" alt="Foto de <?= e($c['nombre']) ?>" loading="lazy">
                    <div class="campo-card__contenido">
                        <h2><?= e($c['nombre']) ?></h2>
                        <p>
                            <?php if (!empty($c['ciudad'])): ?><?= e($c['ciudad']) ?><?php endif; ?>
                            <?php if (!empty($c['superficie'])): ?>
                                <span class="etiqueta"><?= e($c['superficie']) ?></span>
                            <?php endif; ?>
                        </p>
                        <?php if (!empty($ubicacion)): ?>
                            <a class="campo-card__mapa" href="<?= e($maps) ?>" target="_blank" rel="noopener">
                                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                                <?= e($ubicacion) ?>
                            </a>
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
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
