<section>
    <h1>Mi perfil</h1>
    <p>
        <span class="etiqueta">
            <?= $usuario['rol'] === 'administrador' ? 'Administrador' : 'Jugador' ?>
        </span>
        Miembro desde <?= e(formatear_fecha($usuario['fecha_creacion'], 'd/m/Y')) ?>
    </p>

    <form method="post" action="<?= e(url('/perfil')) ?>" class="formulario formulario--ancho" novalidate data-validar>
        <?= Csrf::campo() ?>

        <div class="campo">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" required maxlength="80"
                   value="<?= viejo('nombre', $usuario['nombre']) ?>"
                   aria-invalid="<?= isset($errores['nombre']) ? 'true' : 'false' ?>">
            <?php if (isset($errores['nombre'])): ?>
                <span class="campo__error"><?= e($errores['nombre']) ?></span>
            <?php endif; ?>
        </div>

        <div class="campo">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required maxlength="120"
                   value="<?= viejo('email', $usuario['email']) ?>"
                   aria-invalid="<?= isset($errores['email']) ? 'true' : 'false' ?>">
            <?php if (isset($errores['email'])): ?>
                <span class="campo__error"><?= e($errores['email']) ?></span>
            <?php endif; ?>
        </div>

        <fieldset class="campo">
            <legend>Cambiar contraseña</legend>
            <span class="campo__ayuda">Déjalo en blanco para no cambiarla.</span>

            <label for="contrasena">Nueva contraseña</label>
            <input type="password" id="contrasena" name="contrasena" minlength="8"
                   aria-invalid="<?= isset($errores['contrasena']) ? 'true' : 'false' ?>">
            <?php if (isset($errores['contrasena'])): ?>
                <span class="campo__error"><?= e($errores['contrasena']) ?></span>
            <?php endif; ?>

            <label for="contrasena2">Confirmar nueva contraseña</label>
            <input type="password" id="contrasena2" name="contrasena2" minlength="8"
                   aria-invalid="<?= isset($errores['contrasena2']) ? 'true' : 'false' ?>">
            <?php if (isset($errores['contrasena2'])): ?>
                <span class="campo__error"><?= e($errores['contrasena2']) ?></span>
            <?php endif; ?>
        </fieldset>

        <div class="formulario__acciones">
            <button type="submit" class="boton boton--principal">Guardar cambios</button>
        </div>
    </form>
</section>
