<main class="fp-fade fp-page" style="max-width:980px;">
    <p class="fp-eyebrow">Mi perfil</p>
    <h1 class="fp-h1">Hola, <span class="fp-gradient-text"><?= e($profile['name']) ?></span></h1>

    <div class="fp-grid-3" style="margin-top:28px;">
        <div class="fp-glass" style="border-radius:18px;padding:24px;grid-column:span 2;">
            <h2 class="fp-h2" style="margin-bottom:14px;">Datos del jugador</h2>
            <ul class="fp-detail">
                <li><span>Email</span><strong><?= e($profile['email']) ?></strong></li>
                <li><span>Teléfono</span><strong><?= e($profile['phone'] ?? '—') ?></strong></li>
                <li><span>Edad</span><strong><?= e((string) ($profile['age'] ?? '—')) ?></strong></li>
                <li><span>Ciudad</span><strong><?= e($profile['city'] ?? '—') ?></strong></li>
                <li><span>Posición</span><strong><?= e($profile['position'] ?? '—') ?></strong></li>
                <li><span>Rol</span><strong><?= e($profile['role'] ?? 'player') ?></strong></li>
                <li><span>Miembro desde</span><strong><?= e(date('d/m/Y', strtotime($profile['created_at']))) ?></strong></li>
            </ul>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <a href="<?= url('profile/edit') ?>" class="fp-btn fp-btn-primary">Editar perfil →</a>
                <a href="<?= url('profile/password') ?>" class="fp-btn fp-btn-ghost">Cambiar contraseña</a>
            </div>
        </div>

        <div class="fp-glass" style="border-radius:18px;padding:24px;">
            <h2 class="fp-h2" style="margin-bottom:14px;">Mis equipos</h2>
            <?php if (empty($teams)): ?>
                <p style="color:var(--fp-fg-muted);font-size:13px;">No estás en ningún equipo.</p>
                <a href="<?= url('teams/create') ?>" class="fp-btn fp-btn-primary" style="margin-top:10px;">Crear equipo →</a>
            <?php else: ?>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:10px;">
                    <?php foreach ($teams as $t): ?>
                        <li><a href="<?= url('teams/show/' . (int) $t['id']) ?>" style="display:flex;align-items:center;gap:10px;text-decoration:none;color:var(--fp-fg);font-size:13px;">
                    <span><?= e($t['badge'] ?? 'FP') ?></span>
                            <span><?= e($t['name']) ?> <small style="color:var(--fp-fg-subtle);">· <?= e($t['city']) ?></small></span>
                        </a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <section style="margin-top:28px;">
        <h2 class="fp-h2">Logros</h2>
        <?php if (empty($achievements)): ?>
            <div class="fp-empty">Aún no tienes logros. ¡Juega tu primer partido!</div>
        <?php else: ?>
            <div class="fp-grid-3">
                <?php foreach ($achievements as $a): ?>
                    <div class="fp-glass" style="border-radius:14px;padding:18px;display:flex;gap:14px;align-items:center;">
                        <span style="font-size:30px;"><?= e($a['i']) ?></span>
                        <div>
                            <div style="font-weight:700;font-size:14px;"><?= e($a['n']) ?></div>
                            <div style="font-size:12px;color:var(--fp-fg-subtle);"><?= e($a['d']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
