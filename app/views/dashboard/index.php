<?php
$positionShort = static function (string $p): string {
    return match ($p) {
        'Portero', 'Portera' => 'POR',
        'Defensa' => 'DEF',
        'Mediocampo' => 'MED',
        'Delantero' => 'DEL',
        default => 'N/D',
    };
};
$card = $card ?? [];
$dorsalLabel = isset($card['dorsal']) && $card['dorsal'] !== null ? str_pad((string) (int) $card['dorsal'], 2, '0', STR_PAD_LEFT) : 'N/D';
$heightLabel = isset($card['height_cm']) && $card['height_cm'] !== null ? number_format(((int) $card['height_cm']) / 100, 2, '.', '') . 'm' : 'N/D';
$avatarSrc = !empty($card['avatar']) ? asset($card['avatar']) : asset('images/default-avatar.svg');
$teamLabel = !empty($card['team']['name']) ? $card['team']['name'] : 'Sin equipo';
$posShort = $positionShort((string) ($card['position'] ?? ''));
$isCaptain = !empty($team) && (int) ($team['captain_id'] ?? 0) === (int) $user['id'];
?>
<main class="fp-fade fp-page">
    <div class="fp-page-head">
        <div>
            <p class="fp-eyebrow">Inicio</p>
            <h1 class="fp-h1">Hola, <span class="fp-name-glow"><?= e($user['name']) ?></span></h1>
        </div>
        <a href="<?= url('profile/edit') ?>" class="fp-btn fp-btn-ghost"><i class="bi bi-person-gear"></i><span>Editar perfil</span></a>
    </div>

    <section class="fp-hero-card">
        <div class="fp-card-fifa-wrap">
            <a href="<?= url('profile/edit') ?>" class="fp-card-fifa-link" title="Editar perfil">
                <article class="fp-card-fifa <?= !empty($isPremium) ? 'premium' : '' ?>" aria-label="Carta de jugador">
                    <div class="fp-card-fifa-shine" aria-hidden="true"></div>
                    <header class="fp-card-fifa-head">
                        <div class="fp-card-fifa-rating">
                            <span class="fp-card-fifa-num"><?= e($dorsalLabel) ?></span>
                            <span class="fp-card-fifa-pos"><?= e($posShort) ?></span>
                        </div>
                        <i class="bi bi-shield-fill-check fp-card-fifa-icon"></i>
                    </header>
                    <div class="fp-card-fifa-photo"><img src="<?= e($avatarSrc) ?>" alt="<?= e($card['name'] ?? $user['name']) ?>" loading="lazy"></div>
                    <div class="fp-card-fifa-name"><?= e(mb_strtoupper($card['name'] ?? $user['name'])) ?></div>
                    <div class="fp-card-fifa-stats">
                        <div class="fp-card-fifa-stat"><i class="bi bi-calendar2-check"></i><b><?= (int) ($card['played'] ?? 0) ?></b><span>PJ</span></div>
                        <div class="fp-card-fifa-stat"><i class="bi bi-bullseye"></i><b><?= (int) ($card['goals'] ?? 0) ?></b><span>GOL</span></div>
                        <div class="fp-card-fifa-stat"><i class="bi bi-crosshair"></i><b><?= (int) ($card['assists'] ?? 0) ?></b><span>ASI</span></div>
                        <div class="fp-card-fifa-stat"><i class="bi bi-rulers"></i><b><?= e($heightLabel) ?></b><span>ALT</span></div>
                    </div>
                    <footer class="fp-card-fifa-foot"><span class="fp-card-fifa-club"><?= e($teamLabel) ?></span></footer>
                </article>
            </a>
            <small class="fp-card-edit-hint"><i class="bi bi-pencil"></i> Click para editar perfil</small>
        </div>

        <div class="fp-hero-stats">
            <div class="fp-grid-3">
                <?php foreach ($stats as $s): ?>
                    <div class="fp-glass fp-stat-card">
                        <i class="bi <?= e($s['i']) ?>" style="color:<?= e($s['c']) ?>"></i>
                        <strong><?= (int) $s['v'] ?></strong>
                        <span><?= e($s['l']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <div class="fp-dashboard-bottom">
        <!-- Left column: info panels -->
        <div class="fp-dashboard-left">

            <?php if (!empty($team)): ?>
                <section class="fp-glass fp-panel fp-panel--team">
                    <div class="fp-panel-head">
                        <h2 class="fp-h2"><i class="bi bi-shield-fill" style="color:#4ade80"></i> Mi equipo</h2>
                        <a href="<?= url('teams/show/' . (int) $team['id']) ?>" class="fp-btn fp-btn-ghost fp-btn-sm">Ver equipo</a>
                    </div>
                    <div class="fp-team-info-row">
                        <div class="fp-team-badge large"><?= e($team['badge'] ?? 'FP') ?></div>
                        <div class="fp-team-info-text">
                            <strong class="fp-team-info-name"><?= e($team['name']) ?></strong>
                            <span class="fp-team-info-city"><i class="bi bi-geo-alt"></i> <?= e($team['city']) ?></span>
                            <?php if ($isCaptain): ?>
                                <span class="fp-captain-tag"><i class="bi bi-star-fill"></i> Capitán</span>
                            <?php endif; ?>
                        </div>
                        <a href="<?= url('chat/team/' . (int) $team['id']) ?>" class="fp-btn fp-btn-ghost fp-btn-sm"><i class="bi bi-chat-dots"></i><span>Chat</span></a>
                    </div>
                </section>
            <?php else: ?>
                <section class="fp-glass fp-panel">
                    <h2 class="fp-h2"><i class="bi bi-shield-plus" style="color:#4ade80"></i> Mi equipo</h2>
                    <?php $this->partial('empty-state', ['icon' => 'bi-shield-plus', 'title' => 'Sin equipo', 'description' => 'Únete o crea un equipo para competir.', 'ctaUrl' => 'teams', 'ctaLabel' => 'Ver equipos']); ?>
                </section>
            <?php endif; ?>

            <section class="fp-glass fp-panel">
                <div class="fp-panel-head">
                    <h2 class="fp-h2"><i class="bi bi-calendar2-event" style="color:#60a5fa"></i> Próximos partidos</h2>
                    <a href="<?= url('matches') ?>" class="fp-btn fp-btn-ghost fp-btn-sm">Ver todos</a>
                </div>
                <?php if (empty($upcoming)): ?>
                    <?php $this->partial('empty-state', ['icon' => 'bi-calendar-x', 'title' => 'Sin partidos programados', 'description' => 'Tu equipo aún no tiene partidos.']); ?>
                <?php else: ?>
                    <div class="fp-list">
                        <?php foreach ($upcoming as $m): ?>
                            <a href="<?= url('matches/show/' . (int) $m['id']) ?>" class="fp-list-item fp-list-item--match">
                                <div class="fp-list-match-date">
                                    <strong><?= e($m['when'] ?? '') ?></strong>
                                </div>
                                <div class="fp-list-match-teams">
                                    <span><?= e($m['home']) ?></span>
                                    <b>vs</b>
                                    <span><?= e($m['away']) ?></span>
                                </div>
                                <i class="bi bi-chevron-right" style="color:var(--fp-fg-muted)"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <?php if ($isCaptain): ?>
                <section class="fp-captain-section">
                    <div class="fp-captain-section-inner">
                        <div class="fp-captain-icon"><i class="bi bi-star-fill"></i></div>
                        <div>
                            <h3 class="fp-captain-title">Eres capitán de <?= e($team['name']) ?></h3>
                            <p class="fp-captain-desc">Gestiona tu equipo, acepta solicitudes y organiza partidos.</p>
                        </div>
                    </div>
                    <div class="fp-captain-actions">
                        <a href="<?= url('teams/show/' . (int) $team['id']) ?>" class="fp-btn fp-btn-ghost fp-btn-sm"><i class="bi bi-people"></i> Gestionar equipo</a>
                    </div>
                </section>
            <?php endif; ?>

            <section class="fp-find-match-cta">
                <div class="fp-find-match-inner">
                    <i class="bi bi-broadcast fp-find-match-icon"></i>
                    <div>
                        <h3 class="fp-find-match-title">¿Buscas partido?</h3>
                        <p class="fp-find-match-desc">Solicita un partido en los campos de Ceuta.</p>
                    </div>
                </div>
                <a href="<?= url('matches/create') ?>" class="fp-btn fp-btn-primary fp-btn-glow">
                    <i class="bi bi-send"></i> Solicitar partido
                </a>
            </section>

        </div>

        <!-- Right column: notifications -->
        <div class="fp-dashboard-right">
            <section class="fp-glass fp-panel fp-panel--notifications">
                <div class="fp-panel-head">
                    <h2 class="fp-h2"><i class="bi bi-bell-fill" style="color:#fbbf24"></i> Notificaciones</h2>
                    <a href="<?= url('notification') ?>" class="fp-btn fp-btn-ghost fp-btn-sm">Ver todas</a>
                </div>
                <?php if (empty($notifications)): ?>
                    <?php $this->partial('empty-state', ['icon' => 'bi-bell', 'title' => 'Sin notificaciones', 'description' => 'Aquí verás solicitudes y avisos.']); ?>
                <?php else: ?>
                    <div class="fp-notif-list">
                        <?php foreach ($notifications as $n): ?>
                            <a href="<?= !empty($n['action_url']) ? url($n['action_url']) : url('notification') ?>" class="fp-notif-item <?= (int) $n['is_read'] === 0 ? 'unread' : '' ?>">
                                <div class="fp-notif-icon-wrap">
                                    <i class="bi <?= (int) $n['is_read'] === 0 ? 'bi-bell-fill' : 'bi-bell' ?>"></i>
                                </div>
                                <div class="fp-notif-body">
                                    <span class="fp-notif-msg"><?= e($n['message']) ?></span>
                                    <small class="fp-notif-time"><i class="bi bi-clock"></i> <?= e(date('d/m/Y H:i', strtotime($n['created_at']))) ?></small>
                                </div>
                                <?php if ((int) $n['is_read'] === 0): ?><div class="fp-notif-dot"></div><?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>
