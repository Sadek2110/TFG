<?php /** @var array|null $equipo */ ?>
<section>
    <h1><?= $equipo ? 'Editar equipo' : 'Crear equipo' ?></h1>

    <form method="post" action="<?= e($accion) ?>" class="formulario formulario--ancho" novalidate data-validar>
        <?= Csrf::campo() ?>

        <div class="campo">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" required maxlength="80"
                   value="<?= viejo('nombre', $equipo['nombre'] ?? '') ?>"
                   aria-invalid="<?= isset($errores['nombre']) ? 'true' : 'false' ?>">
            <?php if (isset($errores['nombre'])): ?>
                <span class="campo__error"><?= e($errores['nombre']) ?></span>
            <?php endif; ?>
        </div>

        <div class="campo">
            <label for="ciudad">Ciudad</label>
            <input type="text" id="ciudad" name="ciudad" maxlength="80"
                   value="<?= viejo('ciudad', $equipo['ciudad'] ?? '') ?>"
                   aria-invalid="<?= isset($errores['ciudad']) ? 'true' : 'false' ?>">
            <?php if (isset($errores['ciudad'])): ?>
                <span class="campo__error"><?= e($errores['ciudad']) ?></span>
            <?php endif; ?>
        </div>

        <div class="campo">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" maxlength="500"><?= viejo('descripcion', $equipo['descripcion'] ?? '') ?></textarea>
            <span class="campo__ayuda">Opcional. Máximo 500 caracteres.</span>
            <?php if (isset($errores['descripcion'])): ?>
                <span class="campo__error"><?= e($errores['descripcion']) ?></span>
            <?php endif; ?>
        </div>

        <div class="formulario__acciones">
            <button type="submit" class="boton boton--principal">
                <?= $equipo ? 'Guardar cambios' : 'Crear equipo' ?>
            </button>
            <a href="<?= e($equipo ? url('/equipos/' . $equipo['id']) : url('/equipos')) ?>">Cancelar</a>
        </div>
    </form>
</section>
