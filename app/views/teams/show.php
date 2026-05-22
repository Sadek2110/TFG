<?php
$posGroups = ['Portero' => [], 'Portera' => [], 'Defensa' => [], 'Mediocampo' => [], 'Delantero' => []];
$captain = null;
foreach ($members as $m) {
    if ((int) $m['is_captain'] === 1) $captain = $m;
    $pos = $m['position'] ?? 'Mediocampo';
    if (!isset($posGroups[$pos])) $pos = 'Mediocampo';
    $posGroups[$pos][] = $m;
}
$pitchRows = [
    ['label' => 'DEL', 'key' => 'Delantero', 'area' => 'attack'],
    ['label' => 'MED', 'key' => 'Mediocampo', 'area' => 'mid'],
    ['label' => 'DEF', 'key' => 'Defensa', 'area' => 'def'],
    ['label' => 'POR', 'key' => ['Portero', 'Portera'], 'area' => 'gk'],
];
$getAreaPlayers = static function(array $posGroups, $key): array {
    if (is_array($key)) {
        $out = [];
        foreach ($key as $k) $out = array_merge($out, $posGroups[$k] ?? []);
        return $out;
    }
    return $posGroups[$key] ?? [];
};
?>
<main class="fp-fade fp-page">
    <?php $this->partial('back-button', ['href' => url('teams')]); ?>

    <!-- Header -->
    <section class="fp-team-header fp-glass">
        <div class="fp-team-badge large"><?= e($team['badge'] ?? 'FP') ?></div>
        <div class="fp-team-header-info">
            <p class="fp-eyebrow">Equipo</p>
            <h1 class="fp-h1"><?= e($team['name']) ?></h1>
            <p class="fp-muted">
                <i class="bi bi-geo-alt"></i> <?= e($team['city']) ?> &nbsp;·&nbsp;
                <i class="bi bi-people"></i> <?= count($members) ?> jugador<?= count($members) !== 1 ? 'es' : '' ?> &nbsp;·&nbsp;
                <i class="bi bi-shield-check"></i> Capitán: <strong><?= e($team['captain_name']) ?></strong>
            </p>
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

    <!-- Plantilla + Campo -->
    <?php if (!empty($members)): ?>
    <section class="fp-team-pitch-layout">

        <!-- Lista de jugadores -->
        <div class="fp-team-roster">
            <h2 class="fp-h2">Plantilla (<?= count($members) ?>)</h2>
            <div class="fp-roster-list">
                <?php foreach ($members as $idx => $m): ?>
                    <button type="button"
                            class="fp-roster-item <?= (int) $m['is_captain'] === 1 ? 'is-captain' : '' ?>"
                            data-player-idx="<?= $idx ?>"
                            onclick="fpSelectPlayer(<?= $idx ?>)">
                        <?php if (!empty($m['avatar'])): ?>
                            <img src="<?= asset($m['avatar']) ?>" alt="<?= e($m['name']) ?>" class="fp-roster-avatar">
                        <?php else: ?>
                            <span class="fp-roster-avatar fp-roster-avatar--initial"><?= e(mb_substr($m['name'], 0, 1)) ?></span>
                        <?php endif; ?>
                        <div class="fp-roster-info">
                            <strong><?= e($m['name']) ?></strong>
                            <small><?= e($m['position'] ?? 'N/D') ?><?= (int) $m['is_captain'] === 1 ? ' · ⭐ Capitán' : '' ?></small>
                        </div>
                        <span class="fp-roster-pos-pill"><?= e($m['position'] ? mb_substr($m['position'], 0, 3) : 'N/D') ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Campo de fútbol -->
        <div class="fp-pitch-col">
            <div class="fp-pitch" id="fpPitch">
                <!-- Decoración del campo -->
                <div class="fp-pitch-deco" aria-hidden="true">
                    <div class="fp-pitch-half"></div>
                    <div class="fp-pitch-circle"></div>
                    <div class="fp-pitch-center-dot"></div>
                    <div class="fp-pitch-box fp-pitch-box--top"></div>
                    <div class="fp-pitch-box fp-pitch-box--bot"></div>
                    <div class="fp-pitch-goal fp-pitch-goal--top"></div>
                    <div class="fp-pitch-goal fp-pitch-goal--bot"></div>
                </div>

                <!-- Jugadores por zona -->
                <?php foreach ($pitchRows as $row): ?>
                    <?php $areaPlayers = $getAreaPlayers($posGroups, $row['key']); ?>
                    <div class="fp-pitch-row fp-pitch-row--<?= $row['area'] ?>">
                        <span class="fp-pitch-area-label"><?= $row['label'] ?></span>
                        <?php if (empty($areaPlayers)): ?>
                            <div class="fp-pitch-empty-slot"><i class="bi bi-person-dash"></i></div>
                        <?php else: ?>
                            <?php foreach ($areaPlayers as $pm): ?>
                                <?php
                                $pmIdx = array_search($pm, $members);
                                $isCapt = (int) $pm['is_captain'] === 1;
                                ?>
                                <button type="button"
                                        class="fp-pitch-player <?= $isCapt ? 'is-captain' : '' ?>"
                                        data-player-idx="<?= $pmIdx !== false ? $pmIdx : 0 ?>"
                                        title="<?= e($pm['name']) ?>"
                                        onclick="fpSelectPlayer(<?= $pmIdx !== false ? $pmIdx : 0 ?>)">
                                    <?php if (!empty($pm['avatar'])): ?>
                                        <img src="<?= asset($pm['avatar']) ?>" alt="<?= e($pm['name']) ?>">
                                    <?php else: ?>
                                        <span><?= e(mb_substr($pm['name'], 0, 1)) ?></span>
                                    <?php endif; ?>
                                    <?php if ($isCapt): ?><div class="fp-pitch-captain-star">⭐</div><?php endif; ?>
                                    <div class="fp-pitch-player-name"><?= e(explode(' ', $pm['name'])[0]) ?></div>
                                </button>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Carta del jugador seleccionado -->
            <div class="fp-player-detail" id="fpPlayerDetail" hidden>
                <button class="fp-player-detail-close" onclick="fpClosePlayer()" title="Cerrar"><i class="bi bi-x-lg"></i></button>
                <div class="fp-player-detail-inner" id="fpPlayerDetailInner"></div>
            </div>
        </div>
    </section>

    <!-- Datos JSON para el JS -->
    <script>
    window.fpTeamMembers = <?= json_encode(array_values($members), JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <?php else: ?>
        <section class="fp-panel">
            <?php $this->partial('empty-state', ['icon' => 'bi-people', 'title' => 'Sin jugadores', 'description' => 'Todavía no hay miembros en el equipo.']); ?>
        </section>
    <?php endif; ?>
</main>

<script>
function fpSelectPlayer(idx) {
    const members = window.fpTeamMembers || [];
    const m = members[idx];
    if (!m) return;

    // Highlight en roster y pitch
    document.querySelectorAll('.fp-roster-item, .fp-pitch-player').forEach(el => {
        el.classList.toggle('selected', parseInt(el.dataset.playerIdx, 10) === idx);
    });

    // Render card
    const avatarSrc = m.avatar ? (window.FP_BASE_URL + '/' + m.avatar) : '';
    const initial = (m.name || '?').charAt(0).toUpperCase();
    const pos = m.position || 'N/D';
    const city = m.city || 'N/D';
    const dorsal = m.dorsal !== null && m.dorsal !== undefined ? String(m.dorsal).padStart(2, '0') : 'N/D';
    const isCapt = parseInt(m.is_captain, 10) === 1;

    document.getElementById('fpPlayerDetailInner').innerHTML = `
        <div class="fp-player-card-mini">
            <div class="fp-player-card-avatar">
                ${avatarSrc
                    ? `<img src="${avatarSrc}" alt="${m.name}">`
                    : `<span class="fp-player-card-initial">${initial}</span>`}
            </div>
            <div class="fp-player-card-info">
                <h3>${m.name}${isCapt ? ' <span class="fp-gold-text">⭐ Capitán</span>' : ''}</h3>
                <div class="fp-player-card-stats">
                    <span><i class="bi bi-person-badge"></i> ${pos}</span>
                    <span><i class="bi bi-hash"></i> ${dorsal}</span>
                    <span><i class="bi bi-geo-alt"></i> ${city}</span>
                </div>
            </div>
        </div>`;

    const panel = document.getElementById('fpPlayerDetail');
    panel.hidden = false;
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
function fpClosePlayer() {
    document.getElementById('fpPlayerDetail').hidden = true;
    document.querySelectorAll('.fp-roster-item, .fp-pitch-player').forEach(el => el.classList.remove('selected'));
}
</script>
