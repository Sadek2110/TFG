<main class="fp-fade fp-page">
    <div class="fp-page-head">
        <div>
            <p class="fp-eyebrow">Calendario de Ceuta</p>
            <h1 class="fp-h1">Partidos</h1>
        </div>
        <?php if (is_auth()): ?>
            <a href="<?= url('matches/create') ?>" class="fp-btn fp-btn-gold"><i class="bi bi-send"></i><span>Solicitar partido</span></a>
        <?php endif; ?>
    </div>

    <section class="matches-layout">
        <div class="matches-list-col">
            <?php if (empty($matches)): ?>
                <?php $this->partial('empty-state', ['icon' => 'bi-calendar-x', 'title' => 'No hay partidos programados', 'description' => 'Los partidos de Ceuta aparecerán cuando ambos capitanes confirmen una solicitud.']); ?>
            <?php else: ?>
                <div class="fp-matches-list">
                    <?php foreach ($matches as $i => $m): ?>
                        <a href="<?= url('matches/show/' . (int) $m['id']) ?>"
                           class="fp-match-card fp-card-link"
                           style="animation-delay: <?= $i * 60 ?>ms">
                            <div class="fp-match-card-date fp-match-card-date--<?= e($m['st']) ?>">
                                <strong class="fp-match-day"><?= e($m['d']) ?></strong>
                                <span class="fp-match-month"><?= e($m['m']) ?></span>
                                <small class="fp-match-time"><?= e($m['t']) ?></small>
                            </div>
                            <div class="fp-match-card-center">
                                <div class="fp-match-card-teams">
                                    <div class="fp-match-team-wrap">
                                        <div class="fp-match-team-badge"><?= e(mb_substr($m['h'], 0, 2)) ?></div>
                                        <span class="fp-match-team-name"><?= e($m['h']) ?></span>
                                    </div>
                                    <div class="fp-match-score-badge fp-match-score-badge--<?= e($m['st']) ?>">
                                        <?= e($m['s']) ?>
                                    </div>
                                    <div class="fp-match-team-wrap fp-match-team-wrap--away">
                                        <span class="fp-match-team-name"><?= e($m['a']) ?></span>
                                        <div class="fp-match-team-badge fp-match-team-badge--away"><?= e(mb_substr($m['a'], 0, 2)) ?></div>
                                    </div>
                                </div>
                                <div class="fp-match-card-meta">
                                    <span class="fp-match-field"><i class="bi bi-geo-alt"></i> <?= e($m['f']) ?></span>
                                    <span class="fp-status fp-status-<?= e($m['st']) ?>"><?= e($m['lbl']) ?></span>
                                </div>
                            </div>
                            <i class="bi bi-chevron-right fp-match-card-arrow"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <aside class="matches-calendar" data-calendar-matches='<?= e(json_encode($calendarMatches, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>'>
            <div class="calendar-head">
                <button class="fp-icon-btn" type="button" data-calendar-prev aria-label="Mes anterior"><i class="bi bi-chevron-left"></i></button>
                <strong data-calendar-title></strong>
                <button class="fp-icon-btn" type="button" data-calendar-next aria-label="Mes siguiente"><i class="bi bi-chevron-right"></i></button>
            </div>
            <div class="calendar-weekdays">
                <span>L</span><span>M</span><span>X</span><span>J</span><span>V</span><span>S</span><span>D</span>
            </div>
            <div class="calendar-grid" data-calendar-grid></div>
            <div class="calendar-day-panel">
                <h3 data-calendar-day-title>Partidos del dia</h3>
                <div data-calendar-day-list></div>
            </div>
        </aside>
    </section>
</main>
