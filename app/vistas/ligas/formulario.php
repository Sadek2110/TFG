<section>
    <h1>Nueva liga</h1>

    <form method="post" action="<?= e(url('/ligas/crear')) ?>" class="formulario formulario--ancho" novalidate data-validar>
        <?= Csrf::campo() ?>

        <div class="campo">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" required maxlength="80"
                   value="<?= viejo('nombre') ?>"
                   aria-invalid="<?= isset($errores['nombre']) ? 'true' : 'false' ?>">
            <?php if (isset($errores['nombre'])): ?>
                <span class="campo__error"><?= e($errores['nombre']) ?></span>
            <?php endif; ?>
        </div>

        <div class="campo">
            <label for="temporada">Temporada</label>
            <input type="text" id="temporada" name="temporada" required maxlength="20"
                   placeholder="2025-2026"
                   value="<?= viejo('temporada') ?>"
                   aria-invalid="<?= isset($errores['temporada']) ? 'true' : 'false' ?>">
            <?php if (isset($errores['temporada'])): ?>
                <span class="campo__error"><?= e($errores['temporada']) ?></span>
            <?php endif; ?>
        </div>

        <div class="campo">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" maxlength="500"><?= viejo('descripcion') ?></textarea>
        </div>

        <div class="formulario__acciones">
            <button type="submit" class="boton boton--principal">Crear liga</button>
            <a href="<?= e(url('/ligas')) ?>">Cancelar</a>
        </div>
    </form>
</section>
