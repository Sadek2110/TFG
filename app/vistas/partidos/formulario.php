<section>
    <h1>Crear partido</h1>

    <form method="post" action="<?= e(url('/partidos/crear')) ?>" class="formulario formulario--ancho" novalidate data-validar>
        <?= Csrf::campo() ?>

        <div class="campo">
            <label for="id_equipo_local">Equipo local</label>
            <select id="id_equipo_local" name="id_equipo_local" required
                    aria-invalid="<?= isset($errores['id_equipo_local']) ? 'true' : 'false' ?>">
                <option value="">-- Selecciona --</option>
                <?php foreach ($equipos as $eq): ?>
                    <option value="<?= (int) $eq['id'] ?>" <?= viejo('id_equipo_local') === (string) $eq['id'] ? 'selected' : '' ?>>
                        <?= e($eq['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errores['id_equipo_local'])): ?>
                <span class="campo__error"><?= e($errores['id_equipo_local']) ?></span>
            <?php endif; ?>
        </div>

        <div class="campo">
            <label for="id_equipo_visitante">Equipo visitante</label>
            <select id="id_equipo_visitante" name="id_equipo_visitante" required
                    aria-invalid="<?= isset($errores['id_equipo_visitante']) ? 'true' : 'false' ?>">
                <option value="">-- Selecciona --</option>
                <?php foreach ($equipos as $eq): ?>
                    <option value="<?= (int) $eq['id'] ?>" <?= viejo('id_equipo_visitante') === (string) $eq['id'] ? 'selected' : '' ?>>
                        <?= e($eq['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errores['id_equipo_visitante'])): ?>
                <span class="campo__error"><?= e($errores['id_equipo_visitante']) ?></span>
            <?php endif; ?>
        </div>

        <div class="campo">
            <label for="fecha_partido">Fecha y hora</label>
            <input type="datetime-local" id="fecha_partido" name="fecha_partido" required
                   value="<?= viejo('fecha_partido') ?>"
                   aria-invalid="<?= isset($errores['fecha_partido']) ? 'true' : 'false' ?>">
            <?php if (isset($errores['fecha_partido'])): ?>
                <span class="campo__error"><?= e($errores['fecha_partido']) ?></span>
            <?php endif; ?>
        </div>

        <div class="campo">
            <label for="id_campo">Campo (opcional)</label>
            <select id="id_campo" name="id_campo">
                <option value="">— Sin asignar —</option>
                <?php foreach ($campos as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= viejo('id_campo') === (string) $c['id'] ? 'selected' : '' ?>>
                        <?= e($c['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="id_liga">Liga (opcional)</label>
            <select id="id_liga" name="id_liga">
                <option value="">— Sin liga —</option>
                <?php foreach ($ligas as $l): ?>
                    <option value="<?= (int) $l['id'] ?>" <?= viejo('id_liga') === (string) $l['id'] ? 'selected' : '' ?>>
                        <?= e($l['nombre']) ?> (<?= e($l['temporada']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="formulario__acciones">
            <button type="submit" class="boton boton--principal">Programar partido</button>
            <a href="<?= e(url('/partidos')) ?>">Cancelar</a>
        </div>
    </form>
</section>
