<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:96px 24px;position:relative;">
    <div style="position:absolute;top:33%;left:50%;transform:translate(-50%,-50%);width:384px;height:384px;background:radial-gradient(ellipse,rgba(22,163,74,.10) 0%,transparent 70%);pointer-events:none;"></div>

    <div style="width:100%;max-width:420px;position:relative;">
        <div style="text-align:center;margin-bottom:32px;">
            <a href="<?= url('') ?>" class="fp-logo" style="justify-content:center;font-size:26px;">
                <img src="<?= asset('images/logo.png') ?>" alt="" class="fp-logo-icon">
                <img src="<?= asset('images/logo-nombre.png') ?>" alt="FastPlay" class="fp-logo-word">
            </a>
            <p style="color:#6b7280;margin-top:8px;font-size:13px;">Bienvenido de vuelta</p>
        </div>

        <div class="fp-glass" style="border-radius:24px;padding:32px;">
            <h1 style="font-size:24px;font-weight:900;margin-bottom:28px;">Iniciar sesión</h1>

            <?php if (!empty($errors['_'])): ?>
                <div class="fp-alert fp-alert-err"><?= e($errors['_']) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= url('auth/login') ?>" style="display:flex;flex-direction:column;gap:18px;">
                <?= csrf_field() ?>
                <label style="display:block;">
                    <span style="display:block;font-size:13px;color:#d1d5db;margin-bottom:8px;font-weight:500;">Email</span>
                    <input name="email" type="email" placeholder="tu@email.com" class="fp-input" value="<?= old('email') ?>" required autocomplete="email">
                </label>
                <label style="display:block;">
                    <span style="display:block;font-size:13px;color:#d1d5db;margin-bottom:8px;font-weight:500;">Contraseña</span>
                    <input name="password" type="password" placeholder="••••••••" class="fp-input" required autocomplete="current-password">
                </label>
                <button type="submit" class="fp-btn fp-btn-primary fp-btn-glow" style="width:100%;justify-content:center;padding:14px 0;font-size:15px;margin-top:4px;">Entrar →</button>
            </form>

            <div style="margin-top: 16px; display: flex; flex-direction: column; gap: 12px;">
                <div style="display:flex; align-items:center; color:#6b7280; font-size:12px; gap:8px;">
                    <hr style="flex:1; border:0; border-top:1px solid rgba(255,255,255,0.1);">
                    <span>O</span>
                    <hr style="flex:1; border:0; border-top:1px solid rgba(255,255,255,0.1);">
                </div>
                <a href="<?= url('auth/google') ?>" class="fp-btn fp-btn-ghost" style="width:100%; justify-content:center; padding:12px 0;">
                    <i class="bi bi-google" style="color:#ef4444;"></i> Continuar con Google
                </a>
            </div>


        </div>

        <p style="text-align:center;font-size:13px;color:#6b7280;margin-top:22px;">
            ¿No tienes cuenta?
            <a href="<?= url('auth/register') ?>" style="color:#4ade80;font-weight:600;text-decoration:none;">Regístrate gratis</a>
        </p>
    </div>
</div>