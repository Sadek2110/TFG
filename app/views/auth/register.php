<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:96px 24px;position:relative;">
    <div style="position:absolute;top:33%;left:50%;transform:translate(-50%,-50%);width:384px;height:384px;background:radial-gradient(ellipse,rgba(22,163,74,.10) 0%,transparent 70%);pointer-events:none;"></div>

    <div style="width:100%;max-width:520px;position:relative;">
        <div style="text-align:center;margin-bottom:32px;">
            <a href="<?= url('') ?>" class="fp-logo" style="justify-content:center;">
                <img src="<?= asset('images/logo.png') ?>" alt="" class="fp-logo-icon" style="height:30px;">
                <img src="<?= asset('images/logo_palabra.png') ?>" alt="FastPlay" class="fp-logo-word" style="height:22px;">
            </a>
            <p style="color:#6b7280;margin-top:8px;font-size:13px;">Crea tu perfil de jugador</p>
        </div>

        <div class="fp-glass" style="border-radius:24px;padding:32px;">
            <h1 style="font-size:24px;font-weight:900;margin-bottom:28px;">Crear cuenta</h1>

            <?php if (!empty($errors)): ?>
                <div class="fp-alert fp-alert-err">Revisa los datos del formulario.</div>
            <?php endif; ?>

            <form method="post" action="<?= url('auth/register') ?>" style="display:flex;flex-direction:column;gap:18px;">
                <?= csrf_field() ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <label style="display:block;">
                        <span class="fp-label">Nombre completo</span>
                        <input name="name" type="text" placeholder="Tu nombre" class="fp-input" value="<?= old('name') ?>" required>
                        <?php if (!empty($errors['name'])): ?><small class="fp-err"><?= e($errors['name']) ?></small><?php endif; ?>
                    </label>
                    <label style="display:block;">
                        <span class="fp-label">Edad</span>
                        <input name="age" type="number" placeholder="25" class="fp-input" value="<?= old('age') ?>" min="14" max="99">
                        <?php if (!empty($errors['age'])): ?><small class="fp-err"><?= e($errors['age']) ?></small><?php endif; ?>
                    </label>
                </div>
                <label style="display:block;">
                    <span class="fp-label">Email</span>
                    <input name="email" type="email" placeholder="tu@email.com" class="fp-input" value="<?= old('email') ?>" required autocomplete="email">
                    <?php if (!empty($errors['email'])): ?><small class="fp-err"><?= e($errors['email']) ?></small><?php endif; ?>
                </label>
                <label style="display:block;">
                    <span class="fp-label">Teléfono (opcional)</span>
                    <input name="phone" type="tel" placeholder="+34 600 000 000" class="fp-input" value="<?= old('phone') ?>">
                    <?php if (!empty($errors['phone'])): ?><small class="fp-err"><?= e($errors['phone']) ?></small><?php endif; ?>
                </label>
                <label style="display:block;">
                    <span class="fp-label">Contraseña</span>
                    <input name="password" type="password" placeholder="Mínimo 8 caracteres" class="fp-input" minlength="8" required autocomplete="new-password">
                    <?php if (!empty($errors['password'])): ?><small class="fp-err"><?= e($errors['password']) ?></small><?php endif; ?>
                </label>
                <label style="display:block;">
                    <span class="fp-label">Confirmar contraseña</span>
                    <input name="password_confirm" type="password" placeholder="Repite la contraseña" class="fp-input" minlength="8" required autocomplete="new-password">
                    <?php if (!empty($errors['password_confirm'])): ?><small class="fp-err"><?= e($errors['password_confirm']) ?></small><?php endif; ?>
                </label>
                <button type="submit" class="fp-btn fp-btn-primary fp-btn-glow" style="width:100%;justify-content:center;padding:14px 0;font-size:15px;margin-top:4px;">Crear mi cuenta gratis →</button>
            </form>
        </div>

        <p style="text-align:center;font-size:13px;color:#6b7280;margin-top:22px;">
            ¿Ya tienes cuenta?
            <a href="<?= url('auth/login') ?>" style="color:#4ade80;font-weight:600;text-decoration:none;">Iniciar sesión</a>
        </p>
    </div>
</div>