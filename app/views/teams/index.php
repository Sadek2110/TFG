<main class="fp-fade fp-page">
    <div class="fp-page-head">
        <div>
            <p class="fp-eyebrow">Directorio</p>
            <h1 class="fp-h1">Equipos</h1>
        </div>
        <?php if (is_auth()): ?>
            <a href="<?= url('teams/create') ?>" class="fp-btn fp-btn-primary fp-btn-glow">+ Crear equipo</a>
        <?php endif; ?>
    </div>

    <?php if (empty($teams)): ?>
        <div class="fp-empty">⚽ Aún no hay equipos. ¡Sé el primero en crear uno!</div>
    <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <?php foreach ($teams as $t): ?>
                <a href="<?= url('teams/show/' . (int) $t['id']) ?>" class="fp-glass fp-match-row fp-card-link" style="text-decoration:none;color:#fff;">
                    <div class="fp-glass fp-glass-green" style="width:64px;height:64px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0;">
                        <?= e($t['badge'] ?? '🛡️') ?>
                    </div>
                    <div style="width:1px;height:44px;background:rgba(255,255,255,.10);"></div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:18px;font-weight:900;letter-spacing:-.01em;"><?= e($t['name']) ?></div>
                        <div style="font-size:12px;color:#9ca3af;margin-top:4px;">📍 <?= e($t['city']) ?></div>
                    </div>
                    <div style="min-width:180px;text-align:right;">
                        <div style="font-size:11px;color:#6b7280;">🛡️ Capitán: <span style="color:#d1d5db;font-weight:600;"><?= e($t['captain_name']) ?></span></div>
                        <div style="margin-top:8px;">
                            <span class="fp-status fp-status-confirmed"><?= (int) $t['players'] ?> jugadores</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
