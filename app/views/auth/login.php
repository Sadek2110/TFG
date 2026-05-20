<div class="fp-auth-container">
    <div class="fp-auth-bg-glow"></div>

    <div class="fp-auth-wrapper">
        <div class="fp-auth-header">
            <a href="<?= url('') ?>" class="fp-logo">
                <img src="<?= asset('images/logo.png') ?>" alt="" class="fp-logo-icon">
                <img src="<?= asset('images/logo-nombre.png') ?>" alt="FastPlay" class="fp-logo-word">
            </a>
            <p>Bienvenido de vuelta</p>
        </div>

        <div class="fp-glass fp-auth-card fp-green-glow">
            <h1 class="fp-auth-title">Iniciar sesión</h1>

            <?php if (!empty($errors['_'])): ?>
                <div class="fp-alert fp-alert-err"><?= e($errors['_']) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= url('auth/login') ?>" class="fp-form">
                <?= csrf_field() ?>
                <div>
                    <span class="fp-label">Email</span>
                    <div class="fp-input-icon-group">
                        <i class="bi bi-envelope"></i>
                        <input name="email" type="email" placeholder="tu@email.com" class="fp-input" value="<?= old('email') ?>" required autocomplete="email">
                    </div>
                </div>
                <div>
                    <span class="fp-label">Contraseña</span>
                    <div class="fp-input-icon-group">
                        <i class="bi bi-lock"></i>
                        <input name="password" type="password" placeholder="••••••••" class="fp-input" required autocomplete="current-password">
                    </div>
                </div>
                <button type="submit" class="fp-btn fp-btn-primary fp-btn-glow fp-auth-google-btn">Entrar →</button>
            </form>

            <div class="fp-auth-divider">
                <hr>
                <span>O</span>
                <hr>
            </div>

            <a href="<?= url('auth/google') ?>" class="fp-btn fp-btn-ghost fp-auth-google-btn">
                <i class="bi bi-google"></i> Continuar con Google
            </a>
        </div>

        <p class="fp-auth-footer">
            ¿No tienes cuenta?
            <a href="<?= url('auth/register') ?>">Regístrate gratis</a>
        </p>
    </div>
</div>