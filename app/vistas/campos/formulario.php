<section>
    <h1>Nuevo campo</h1>

    <form method="post" action="<?= e(url('/campos/crear')) ?>" class="formulario formulario--ancho" novalidate data-validar>
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
            <label for="ciudad">Ciudad</label>
            <input type="text" id="ciudad" name="ciudad" maxlength="80"
                   value="<?= viejo('ciudad') ?>">
        </div>

        <div class="campo">
            <label for="direccion">Dirección</label>
            <input type="text" id="direccion" name="direccion" maxlength="120"
                   value="<?= viejo('direccion') ?>">
        </div>

        <div class="campo">
            <label for="superficie">Superficie</label>
            <select id="superficie" name="superficie">
                <?php
                $opciones = ['', 'Hierba natural', 'Hierba artificial', 'Tierra', 'Cemento'];
                $seleccionada = viejo('superficie');
                foreach ($opciones as $op):
                ?>
                    <option value="<?= e($op) ?>" <?= $seleccionada === $op ? 'selected' : '' ?>>
                        <?= $op === '' ? '— Sin especificar —' : e($op) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="foto">Foto del campo</label>
            <input type="text" id="foto" name="foto" maxlength="255"
                   value="<?= viejo('foto') ?>"
                   placeholder="/imagenes/hero-poster.jpg">
            <span class="campo__ayuda">Puedes usar una ruta local o una URL completa. Si queda vacío se mostrará una imagen por defecto.</span>
        </div>

        <div class="formulario__acciones">
            <button type="submit" class="boton boton--principal">Guardar campo</button>
            <a href="<?= e(url('/campos')) ?>">Cancelar</a>
        </div>
    </form>
</section>
