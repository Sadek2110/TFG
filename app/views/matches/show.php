<main class="fp-fade fp-page fp-large-container">
    <?php $this->partial('back-button', ['href' => url('matches')]); ?>
    <p class="fp-eyebrow">Partido</p>
    <h1 class="fp-h1"><?= e($match['home_name']) ?> <span class="fp-muted">vs</span> <?= e($match['away_name']) ?></h1>

    <section class="fp-premium-scoreboard">
        <div class="fp-scoreboard-team home">
            <span class="fp-scoreboard-team-name"><?= e($match['home_name']) ?></span>
            <small class="fp-eyebrow">Local</small>
        </div>
        <div class="fp-scoreboard-center">
            <span class="fp-scoreboard-score-badge"><?= e($match['s']) ?></span>
        </div>
        <div class="fp-scoreboard-team away">
            <span class="fp-scoreboard-team-name"><?= e($match['away_name']) ?></span>
            <small class="fp-eyebrow">Visitante</small>
        </div>
    </section>

    <section class="fp-glass fp-panel">
        <div class="fp-actions-row fp-match-details" style="justify-content: center;">
            <span><i class="bi bi-calendar2-event"></i> <?= e(date('d/m/Y H:i', strtotime($match['scheduled_at']))) ?></span>
            <span><i class="bi bi-geo-alt"></i> <?= e($match['location'] ?? $match['field_name'] ?? 'Campo a confirmar') ?></span>
            <?php if (!empty($match['league_name'])): ?><span><i class="bi bi-trophy"></i> <?= e($match['league_name']) ?></span><?php endif; ?>
            <span class="fp-status fp-status-<?= e($match['st']) ?>"><?= e($match['lbl']) ?></span>
        </div>
    </section>

    <?php if ($isManager && $match['st'] !== 'finished' && $match['st'] !== 'cancelled'): ?>
        <section class="fp-actions-row" style="margin-top: 24px;">
            <?php if ($match['st'] === 'pending'): ?>
                <form method="post" action="<?= url('matches/confirm/' . (int) $match['id']) ?>"><?= csrf_field() ?><button class="fp-btn fp-btn-primary">Confirmar partido</button></form>
            <?php endif; ?>
            <form method="post" action="<?= url('matches/cancel/' . (int) $match['id']) ?>" onsubmit="return confirm('¿Cancelar el partido?');"><?= csrf_field() ?><button class="fp-btn fp-btn-ghost">Cancelar</button></form>
        </section>
        <?php if ($match['st'] === 'confirmed'): ?>
            <section class="fp-glass fp-panel">
                <h2 class="fp-h2">Cerrar resultado</h2>
                <form method="post" action="<?= url('matches/finish/' . (int) $match['id']) ?>" class="fp-actions-row">
                     <?= csrf_field() ?>
                     <input type="number" name="home_score" min="0" max="99" placeholder="Local" class="fp-input fp-input-score">
                     <input type="number" name="away_score" min="0" max="99" placeholder="Visitante" class="fp-input fp-input-score">
                     <button class="fp-btn fp-btn-primary">Finalizar</button>
                </form>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</main>
