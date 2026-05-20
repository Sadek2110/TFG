<main class="fp-fade fp-page">
    <?php $this->partial('back-button', ['href' => url('teams')]); ?>
    <section class="fp-team-header fp-glass">
        <div class="fp-team-badge large"><?= e($team['badge'] ?? 'FP') ?></div>
        <div>
            <p class="fp-eyebrow">Equipo</p>
            <h1 class="fp-h1"><?= e($team['name']) ?></h1>
            <p class="fp-muted"><i class="bi bi-geo-alt"></i> <?= e($team['city']) ?> · <i class="bi bi-shield-check"></i> Capitán: <?= e($team['captain_name']) ?></p>
        </div>
        <div class="fp-actions-row">
            <?php if (is_auth() && !$isMember): ?>
                <form method="post" action="<?= url('team-join-request/create') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="team_id" value="<?= (int) $team['id'] ?>">
                    <button class="fp-btn fp-btn-primary">Solicitar unirse</button>
                </form>
            <?php endif; ?>
            <?php if ($isMember): ?>
                <a href="<?= url('chat/team/' . (int) $team['id']) ?>" class="fp-btn fp-btn-ghost"><i class="bi bi-chat-dots"></i><span>Chat interno</span></a>
            <?php endif; ?>
            <?php if (is_auth() && (int) $team['captain_id'] !== (int) current_user()['id'] && $isMember): ?>
                <form method="post" action="<?= url('teams/leave/' . (int) $team['id']) ?>" onsubmit="return confirm('¿Seguro que quieres dejar el equipo?');">
                    <?= csrf_field() ?>
                    <button class="fp-btn fp-btn-ghost">Dejar equipo</button>
                </form>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($pendingRequests)): ?>
        <section class="fp-glass fp-panel">
            <h2 class="fp-h2">Solicitudes pendientes</h2>
            <div class="fp-list">
                <?php foreach ($pendingRequests as $r): ?>
                    <div class="fp-list-item">
                        <i class="bi bi-person-plus"></i>
                        <span><strong><?= e($r['user_name']) ?></strong><small><?= e($r['user_email']) ?></small></span>
                        <form method="post" action="<?= url('team-join-request/accept/' . (int) $r['id']) ?>"><?= csrf_field() ?><button class="fp-btn fp-btn-primary">Aceptar</button></form>
                        <form method="post" action="<?= url('team-join-request/reject/' . (int) $r['id']) ?>"><?= csrf_field() ?><button class="fp-btn fp-btn-ghost">Rechazar</button></form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="fp-panel">
        <h2 class="fp-h2">Plantilla (<?= count($members) ?>)</h2>
        <?php if (empty($members)): ?>
            <?php $this->partial('empty-state', ['icon' => 'bi-people', 'title' => 'Sin jugadores', 'description' => 'Todavía no hay miembros en el equipo.']); ?>
        <?php else: ?>
            <div class="fp-grid-3">
                <?php foreach ($members as $m): ?>
                    <article class="fp-glass fp-member-card">
                        <?php if (!empty($m['avatar'])): ?>
                            <img src="<?= asset($m['avatar']) ?>" alt="<?= e($m['name']) ?>" class="fp-avatar-img">
                        <?php else: ?>
                            <span class="fp-avatar-initial"><?= e(mb_substr($m['name'], 0, 1)) ?></span>
                        <?php endif; ?>
                        <div>
                            <strong><?= e($m['name']) ?></strong>
                            <?php if ((int) $m['is_captain'] === 1): ?><small class="fp-gold-text">Capitán</small><?php endif; ?>
                            <small><?= e($m['position'] ?? 'Sin posición') ?> · <?= e($m['city'] ?? 'N/D') ?></small>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
