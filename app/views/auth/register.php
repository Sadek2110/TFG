<div class="fp-auth-container">
    <div class="fp-auth-bg-glow"></div>

    <div class="fp-auth-wrapper fp-auth-wrapper-wide">
        <div class="fp-auth-header">
            <a href="<?= url('') ?>" class="fp-logo">
                <img src="<?= asset('images/logo.png') ?>" alt="" class="fp-logo-icon">
                <img src="<?= asset('images/logo-nombre.png') ?>" alt="FastPlay" class="fp-logo-word">
            </a>
            <p>Crea tu perfil de jugador</p>
        </div>

        <div class="fp-glass fp-auth-card fp-green-glow">
            <h1 class="fp-auth-title">Crear cuenta</h1>

            <?php if (!empty($errors)): ?>
                <div class="fp-alert fp-alert-err">Revisa los datos del formulario.</div>
            <?php endif; ?>

            <form method="post" action="<?= url('auth/register') ?>" class="fp-form" data-fp-validate novalidate>
                <?= csrf_field() ?>

                <div class="fp-grid-2">
                    <div>
                        <span class="fp-label">Nombre completo</span>
                        <div class="fp-input-icon-group">
                            <i class="bi bi-person"></i>
                            <input name="name" type="text" placeholder="Tu nombre" class="fp-input" value="<?= old('name') ?>" required
                                   data-fp-validate-field data-fp-rule="name">
                        </div>
                        <?php if (!empty($errors['name'])): ?><small class="fp-err"><?= e($errors['name']) ?></small><?php endif; ?>
                    </div>

                    <div>
                        <span class="fp-label">Edad</span>
                        <div class="fp-input-icon-group">
                            <i class="bi bi-hash"></i>
                            <input name="age" type="number" placeholder="25" class="fp-input" value="<?= old('age') ?>" min="14" max="99">
                        </div>
                        <?php if (!empty($errors['age'])): ?><small class="fp-err"><?= e($errors['age']) ?></small><?php endif; ?>
                    </div>
                </div>

                <div class="fp-grid-2">
                    <div>
                        <span class="fp-label">Email</span>
                        <div class="fp-input-icon-group">
                            <i class="bi bi-envelope"></i>
                            <input name="email" type="email" placeholder="tu@email.com" class="fp-input" value="<?= old('email') ?>" required autocomplete="email"
                                   data-fp-validate-field data-fp-rule="email">
                        </div>
                        <?php if (!empty($errors['email'])): ?><small class="fp-err"><?= e($errors['email']) ?></small><?php endif; ?>
                    </div>

                    <div>
                        <span class="fp-label">Teléfono (opcional)</span>
                        <div class="fp-input-icon-group">
                            <i class="bi bi-telephone"></i>
                            <input name="phone" type="tel" placeholder="+34 600 000 000" class="fp-input" value="<?= old('phone') ?>">
                        </div>
                        <?php if (!empty($errors['phone'])): ?><small class="fp-err"><?= e($errors['phone']) ?></small><?php endif; ?>
                    </div>
                </div>

                <div class="fp-grid-2">
                    <div>
                        <span class="fp-label">Contraseña</span>
                        <div class="fp-input-icon-group">
                            <i class="bi bi-lock"></i>
                            <input name="password" type="password" placeholder="Mínimo 8 caracteres" class="fp-input" minlength="8" required autocomplete="new-password"
                                   data-fp-validate-field data-fp-rule="password-strong">
                        </div>
                        <small class="fp-help-text" style="color:var(--fp-fg-muted);font-size:11px;display:block;margin-top:4px;opacity:0.8;">Mínimo 8 caracteres, incluyendo letras y números.</small>
                        <?php if (!empty($errors['password'])): ?><small class="fp-err"><?= e($errors['password']) ?></small><?php endif; ?>
                    </div>

                    <div>
                        <span class="fp-label">Confirmar contraseña</span>
                        <div class="fp-input-icon-group">
                            <i class="bi bi-lock-fill"></i>
                            <input name="password_confirm" type="password" placeholder="Repite la contraseña" class="fp-input" minlength="8" required autocomplete="new-password"
                                   data-fp-validate-field data-fp-rule="password-basic"
                                   data-fp-error="Debes repetir la contrasena (minimo 6 caracteres).">
                        </div>
                        <?php if (!empty($errors['password_confirm'])): ?><small class="fp-err"><?= e($errors['password_confirm']) ?></small><?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="fp-btn fp-btn-primary fp-btn-glow fp-auth-google-btn">Crear mi cuenta gratis →</button>
            </form>

            <div class="fp-auth-divider">
                <hr>
                <span>O</span>
                <hr>
            </div>

            <a href="<?= url('auth/google') ?>" class="fp-btn fp-btn-ghost fp-auth-google-btn">
                <i class="bi bi-google"></i> Registrarse con Google
            </a>
        </div>

        <p class="fp-auth-footer">
            ¿Ya tienes cuenta?
            <a href="<?= url('auth/login') ?>">Iniciar sesión</a>
        </p>
    </div>
</div>