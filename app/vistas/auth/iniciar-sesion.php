<section>
    <h1>Iniciar sesión</h1>

    <form method="post" action="<?= e(url('/iniciar-sesion')) ?>" class="formulario" novalidate data-validar>
        <?= Csrf::campo() ?>

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
            <input type="password" id="contrasena" name="contrasena" required
                   aria-invalid="<?= isset($errores['contrasena']) ? 'true' : 'false' ?>">
            <?php if (isset($errores['contrasena'])): ?>
                <span class="campo__error"><?= e($errores['contrasena']) ?></span>
            <?php endif; ?>
        </div>

        <div class="formulario__acciones">
            <button type="submit" class="boton boton--principal">Entrar</button>
            <a href="<?= e(url('/registro')) ?>">¿No tienes cuenta? Regístrate</a>
        </div>
    </form>
</section>
