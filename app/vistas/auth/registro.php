<section>
    <h1>Crear cuenta</h1>
    <p>Únete a FastPlay para gestionar tu equipo y participar en partidos.</p>

    <form method="post" action="<?= e(url('/registro')) ?>" class="formulario" novalidate data-validar>
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
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required maxlength="120"
                   value="<?= viejo('email') ?>"
                   aria-invalid="<?= isset($errores['email']) ? 'true' : 'false' ?>">
            <?php if (isset($errores['email'])): ?>
                <span class="campo__error"><?= e($errores['email']) ?></span>
            <?php endif; ?>
        </div>

        <div class="campo">
            <label for="contrasena">Contraseña</label>
            <input type="password" id="contrasena" name="contrasena" required minlength="8"
                   aria-invalid="<?= isset($errores['contrasena']) ? 'true' : 'false' ?>">
            <span class="campo__ayuda">Mínimo 8 caracteres.</span>
            <?php if (isset($errores['contrasena'])): ?>
                <span class="campo__error"><?= e($errores['contrasena']) ?></span>
            <?php endif; ?>
        </div>

        <div class="campo">
            <label for="contrasena2">Repite la contraseña</label>
            <input type="password" id="contrasena2" name="contrasena2" required minlength="8"
                   aria-invalid="<?= isset($errores['contrasena2']) ? 'true' : 'false' ?>">
            <?php if (isset($errores['contrasena2'])): ?>
                <span class="campo__error"><?= e($errores['contrasena2']) ?></span>
            <?php endif; ?>
        </div>

        <div class="formulario__acciones">
            <button type="submit" class="boton boton--principal">Crear cuenta</button>
            <a href="<?= e(url('/iniciar-sesion')) ?>">¿Ya tienes cuenta? Inicia sesión</a>
        </div>
    </form>
</section>
