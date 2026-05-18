<?php
$positionShort = static function (string $p): string {
    return match ($p) {
        'Portero', 'Portera' => 'POR',
        'Defensa'            => 'DEF',
        'Mediocampo'         => 'MED',
        'Delantero'          => 'DEL',
        default              => '—',
    };
};
$card = $card ?? [];
$dorsalLabel = isset($card['dorsal']) && $card['dorsal'] !== null
    ? str_pad((string) (int) $card['dorsal'], 2, '0', STR_PAD_LEFT)
    : '—';
$heightLabel = isset($card['height_cm']) && $card['height_cm'] !== null
    ? number_format(((int) $card['height_cm']) / 100, 2, '.', '') . 'm'
    : '—';
$avatarSrc = !empty($card['avatar']) ? asset($card['avatar']) : asset('images/default-avatar.svg');
$teamLabel = !empty($card['team']['name']) ? $card['team']['name'] : 'Sin equipo';
$posShort  = $positionShort((string) ($card['position'] ?? ''));
?>
<main class="fp-fade" style="max-width:1280px;margin:0 auto;padding:96px 24px 80px;">

    <div style="margin-bottom:36px;">
        <p style="color:#6b7280;font-size:13px;margin-bottom:4px;">Bienvenido de vuelta</p>
        <h1 style="font-size:30px;font-weight:900;margin:0;">Hola, <span class="fp-gradient-text"><?= e($user['name']) ?></span> 👋</h1>
    </div>

    <!-- Hero: carta FIFA + stats -->
    <section class="fp-hero-card" style="margin-bottom:36px;">
        <div class="fp-card-fifa-wrap">
            <article class="fp-card-fifa" aria-label="Carta de jugador">
                <div class="fp-card-fifa-shine" aria-hidden="true"></div>
                <header class="fp-card-fifa-head">
                    <div class="fp-card-fifa-rating">
                        <span class="fp-card-fifa-num"><?= e($dorsalLabel) ?></span>
                        <span class="fp-card-fifa-pos"><?= e($posShort) ?></span>
                    </div>
                    <div class="fp-card-fifa-team" title="<?= e($teamLabel) ?>">
                        <?php if (!empty($card['team'])): ?>
                            <span class="fp-card-fifa-team-badge"><?= e($card['team']['badge'] ?? '🛡️') ?></span>
                        <?php endif; ?>
                    </div>
                </header>
                <div class="fp-card-fifa-photo">
                    <img src="<?= e($avatarSrc) ?>" alt="<?= e($card['name'] ?? $user['name']) ?>" loading="lazy">
                </div>
                <div class="fp-card-fifa-name"><?= e(mb_strtoupper($card['name'] ?? $user['name'])) ?></div>
                <div class="fp-card-fifa-stats">
                    <div class="fp-card-fifa-stat"><b><?= (int) ($card['played']   ?? 0) ?></b><span>PAR</span></div>
                    <div class="fp-card-fifa-stat"><b><?= (int) ($card['goals']    ?? 0) ?></b><span>GOL</span></div>
                    <div class="fp-card-fifa-stat"><b><?= (int) ($card['assists']  ?? 0) ?></b><span>ASI</span></div>
                    <div class="fp-card-fifa-stat"><b><?= e($heightLabel) ?></b><span>ALT</span></div>
                    <div class="fp-card-fifa-stat"><b><?= e($posShort) ?></b><span>POS</span></div>
                    <div class="fp-card-fifa-stat"><b><?= e($dorsalLabel) ?></b><span>DOR</span></div>
                </div>
                <footer class="fp-card-fifa-foot">
                    <span class="fp-card-fifa-club"><?= e($teamLabel) ?></span>
                </footer>
            </article>
            <a href="<?= url('profile/edit') ?>" class="fp-btn fp-btn-ghost fp-card-fifa-edit">Editar mi carta →</a>
        </div>

        <div class="fp-hero-stats">
            <div class="fp-grid-4" style="gap:14px;">
                <?php foreach ($stats as $s): ?>
                    <div class="fp-glass" style="border-radius:18px;padding:20px;">
                        <div style="font-size:22px;margin-bottom:10px;"><?= e($s['i']) ?></div>
                        <div style="font-size:30px;font-weight:900;color:<?= e($s['c']) ?>;letter-spacing:-.02em;line-height:1;"><?= (int) $s['v'] ?></div>
                        <div style="font-size:11px;color:#6b7280;margin-top:6px;font-weight:500;"><?= e($s['l']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <div class="fp-grid-3">
        <!-- Mi equipo -->
        <div class="fp-glass" style="border-radius:18px;padding:24px;">
            <h2 style="font-size:16px;font-weight:900;margin-bottom:18px;">Mi equipo</h2>
            <?php if (!empty($team)): ?>
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:22px;">
                    <div class="fp-glass fp-glass-green" style="width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:22px;"><?= e($team['badge'] ?? '🛡️') ?></div>
                    <div>
                        <div style="font-weight:700;"><?= e($team['name']) ?></div>
                        <div style="font-size:13px;color:#9ca3af;">📍 <?= e($team['city']) ?></div>
                    </div>
                </div>
                <a href="<?= url('teams/show/' . (int) $team['id']) ?>" class="fp-btn fp-btn-ghost" style="width:100%;justify-content:center;padding:10px 0;font-size:13px;">Ver equipo →</a>
            <?php else: ?>
                <p style="font-size:13px;color:#9ca3af;margin:0 0 14px;">Aún no tienes equipo. ¡Únete a uno o crea el tuyo!</p>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <a href="<?= url('teams') ?>" class="fp-btn fp-btn-primary fp-btn-glow" style="width:100%;justify-content:center;padding:10px 0;font-size:13px;">Unirse a un equipo →</a>
                    <a href="<?= url('teams/create') ?>" class="fp-btn fp-btn-gold" style="width:100%;justify-content:center;padding:10px 0;font-size:13px;">+ Crear equipo</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Próximos partidos -->
        <div class="fp-glass" style="border-radius:18px;padding:24px;">
            <h2 style="font-size:16px;font-weight:900;margin-bottom:18px;">Próximos partidos</h2>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <?php foreach ($upcoming as $m): ?>
                    <div style="display:flex;align-items:center;gap:12px;padding:10px;border-radius:12px;">
                        <span style="width:8px;height:8px;border-radius:9999px;background:#4ade80;flex-shrink:0;"></span>
                        <div style="flex:1;">
                            <div style="font-size:12px;font-weight:600;"><?= e($m['home']) ?> vs <?= e($m['away']) ?></div>
                            <div style="font-size:11px;color:#6b7280;"><?= e($m['when']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Logros -->
        <div class="fp-glass" style="border-radius:18px;padding:24px;">
            <h2 style="font-size:16px;font-weight:900;margin-bottom:18px;">Logros recientes</h2>
            <?php if (empty($achievements)): ?>
                <p style="font-size:13px;color:#9ca3af;margin:0;">Aún sin logros. ¡Juega tu primer partido para empezar a sumar!</p>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <?php foreach ($achievements as $a): ?>
                        <div style="display:flex;align-items:center;gap:12px;padding:10px;border-radius:12px;background:rgba(255,255,255,.05);">
                            <span style="font-size:22px;"><?= e($a['i']) ?></span>
                            <div>
                                <div style="font-size:12px;font-weight:600;"><?= e($a['n']) ?></div>
                                <div style="font-size:11px;color:#6b7280;"><?= e($a['d']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</main>
