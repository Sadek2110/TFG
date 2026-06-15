<section>
    <div class="barra-acciones">
        <div>
            <h1>
                <?= e($partido['nombre_local']) ?>
                vs
                <?= e($partido['nombre_visitante']) ?>
            </h1>
            <p class="campo__ayuda">
                <?= e(formatear_fecha($partido['fecha_partido'])) ?>
                <?php if (!empty($partido['nombre_campo'])): ?>
                    &middot; Campo: <?= e($partido['nombre_campo']) ?>
                <?php endif; ?>
                <?php if (!empty($partido['nombre_liga'])): ?>
                    &middot; Liga: <?= e($partido['nombre_liga']) ?>
                <?php endif; ?>
            </p>
        </div>
        <span class="etiqueta <?= $partido['estado'] === 'finalizado' ? 'etiqueta--exito' : '' ?>">
            <?= e(ucfirst($partido['estado'])) ?>
        </span>
    </div>

    <?php if ($partido['estado'] === 'finalizado'): ?>
        <p style="font-size:1.6rem; text-align:center;">
            <strong><?= (int) $partido['goles_local'] ?></strong>
            -
            <strong><?= (int) $partido['goles_visitante'] ?></strong>
        </p>
    <?php endif; ?>

    <?php if ($puedeResultado): ?>
        <h2>Registrar resultado</h2>
        <form method="post" action="<?= e(url('/partidos/' . $partido['id'] . '/resultado')) ?>" class="formulario" novalidate data-validar>
            <?= Csrf::campo() ?>
            <div class="campo">
                <label for="goles_local">Goles <?= e($partido['nombre_local']) ?></label>
                <input type="number" id="goles_local" name="goles_local" min="0" max="99" required
                       value="<?= e((string) ($partido['goles_local'] ?? '')) ?>"
                       aria-invalid="<?= isset($errores['goles_local']) ? 'true' : 'false' ?>">
                <?php if (isset($errores['goles_local'])): ?>
                    <span class="campo__error"><?= e($errores['goles_local']) ?></span>
                <?php endif; ?>
            </div>
            <div class="campo">
                <label for="goles_visitante">Goles <?= e($partido['nombre_visitante']) ?></label>
                <input type="number" id="goles_visitante" name="goles_visitante" min="0" max="99" required
                       value="<?= e((string) ($partido['goles_visitante'] ?? '')) ?>"
                       aria-invalid="<?= isset($errores['goles_visitante']) ? 'true' : 'false' ?>">
                <?php if (isset($errores['goles_visitante'])): ?>
                    <span class="campo__error"><?= e($errores['goles_visitante']) ?></span>
                <?php endif; ?>
            </div>
            <div class="formulario__acciones">
                <button type="submit" class="boton boton--principal">Guardar resultado</button>
            </div>
        </form>
    <?php endif; ?>

    <?php if ($puedeEliminar): ?>
        <form method="post" action="<?= e(url('/partidos/' . $partido['id'] . '/eliminar')) ?>"
              data-confirmar="¿Eliminar este partido?" style="margin-top:1rem;">
            <?= Csrf::campo() ?>
            <button type="submit" class="boton boton--peligro">Eliminar partido</button>
        </form>
    <?php endif; ?>
</section>
