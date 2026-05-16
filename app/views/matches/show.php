<main class="fp-fade fp-page" style="max-width:920px;">
    <p class="fp-eyebrow">Partido</p>
    <h1 class="fp-h1"><?= e($match['home_name']) ?> <span style="color:#6b7280;">vs</span> <?= e($match['away_name']) ?></h1>

    <div class="fp-glass" style="border-radius:18px;padding:28px;margin-top:20px;">
        <div style="display:flex;align-items:center;justify-content:center;gap:24px;flex-wrap:wrap;">
            <div style="text-align:center;flex:1;min-width:160px;">
                <div style="font-size:18px;font-weight:900;"><?= e($match['home_name']) ?></div>
                <div style="font-size:11px;color:#6b7280;">LOCAL</div>
            </div>
            <div style="font-size:48px;font-weight:900;line-height:1;letter-spacing:-.02em;">
                <?= e($match['s']) ?>
            </div>
            <div style="text-align:center;flex:1;min-width:160px;">
                <div style="font-size:18px;font-weight:900;"><?= e($match['away_name']) ?></div>
                <div style="font-size:11px;color:#6b7280;">VISITANTE</div>
            </div>
        </div>

        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-top:24px;font-size:13px;color:#9ca3af;">
            <div>📅 <?= e(date('d/m/Y H:i', strtotime($match['scheduled_at']))) ?></div>
            <div>🏟️ <?= e($match['field_name'] ?? 'Campo a confirmar') ?></div>
            <?php if (!empty($match['league_name'])): ?>
                <div>🏆 <?= e($match['league_name']) ?></div>
            <?php endif; ?>
            <span class="fp-status fp-status-<?= e($match['st']) ?>"><?= e($match['lbl']) ?></span>
        </div>
    </div>

    <?php if (is_auth() && $match['st'] !== 'finished' && $match['st'] !== 'cancelled'): ?>
        <section style="margin-top:24px;display:flex;gap:10px;flex-wrap:wrap;">
            <?php if ($match['st'] === 'pending'): ?>
                <form method="post" action="<?= url('matches/confirm/' . (int) $match['id']) ?>" style="margin:0;">
                    <?= csrf_field() ?>
                    <button class="fp-btn fp-btn-primary fp-btn-glow">Confirmar partido →</button>
                </form>
            <?php endif; ?>
            <form method="post" action="<?= url('matches/cancel/' . (int) $match['id']) ?>" style="margin:0;" onsubmit="return confirm('¿Cancelar el partido?');">
                <?= csrf_field() ?>
                <button class="fp-btn fp-btn-ghost" style="color:#f87171;">Cancelar</button>
            </form>
            <?php if ($match['st'] === 'confirmed'): ?>
            <details class="fp-glass" style="border-radius:14px;padding:14px 16px;">
                <summary style="cursor:pointer;font-weight:600;font-size:13px;">Cerrar resultado</summary>
                <form method="post" action="<?= url('matches/finish/' . (int) $match['id']) ?>" style="display:flex;gap:10px;align-items:center;margin-top:12px;">
                    <?= csrf_field() ?>
                    <input type="number" name="home_score" min="0" max="99" placeholder="Local" class="fp-input" style="width:100px;">
                    <span style="color:#6b7280;">–</span>
                    <input type="number" name="away_score" min="0" max="99" placeholder="Visit." class="fp-input" style="width:100px;">
                    <button class="fp-btn fp-btn-primary">Finalizar →</button>
                </form>
            </details>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>
