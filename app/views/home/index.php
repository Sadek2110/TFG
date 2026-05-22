

<?php
// === Status server-side por liga (live / open / ended) ===
$today = date('Y-m-d');
foreach ($leagues as &$_l) {
    $s = $_l['start_date'] ?? '';
    $e = $_l['end_date']   ?? '';
    if ($e && $today > $e)         $_l['_status'] = ['k' => 'ended', 'label' => 'Finalizada'];
    elseif ($s && $today >= $s)    $_l['_status'] = ['k' => 'live',  'label' => 'En curso'];
    else                           $_l['_status'] = ['k' => 'open',  'label' => 'Inscripción abierta'];
}
unset($_l);
?>

<script>
  document.documentElement.classList.add('fp-landing-page');
  document.body.classList.add('fp-landing-page');
  document.documentElement.setAttribute('data-theme', 'dark');
</script>

<div class="scroll-anim">


  <div class="scroll-anim__canvas-wrap">
    <video id="heroVideo" muted playsinline preload="auto" poster="<?= asset('images/hero-poster.jpg') ?>">
      <source src="<?= asset('video/hero.webm') ?>" type="video/webm">
    </video>
  </div>

  <div class="scroll-anim__vignette"></div>
  <div class="scroll-anim__sides"></div>

  <div class="scroll-anim__content">

    <!-- ========== SECTION 0: HERO ========== -->
    <section class="scroll-section scroll-section--hero scroll-section--left" id="section-0">
      <div class="scroll-section__inner visible">
        <div class="scroll-stagger">
          <div class="scroll-eyebrow" style="--i:0">
            <span class="fp-pulse-dot" style="width:8px;height:8px;border-radius:50%;background:#4ade80;display:inline-block;"></span>
            Temporada 2026 — Inscripciones abiertas
          </div>
          <h1 class="scroll-title" style="--i:1">
            ¿Te apetece<br><span class="fp-gradient-text">jugar?</span>
          </h1>
          <p class="scroll-desc" style="--i:2;font-size:18px;max-width:480px;">
            FastPlay conecta jugadores, organiza partidos en campos reales y lleva el fútbol amateur al siguiente nivel.
            <strong style="color:#fff;">En cualquier lugar. Para todos.</strong>
          </p>
          <div class="scroll-cta-row" style="--i:3">
            <a href="<?= url('auth/register') ?>" class="fp-btn fp-btn-primary fp-btn-glow" style="padding:16px 32px;font-size:16px;">Empieza gratis →</a>
            <a href="<?= url('leagues') ?>" class="fp-btn fp-btn-ghost" style="padding:16px 32px;font-size:16px;">Ver ligas</a>
          </div>
          <div class="scroll-trust" style="--i:4" aria-label="Garantías">
            <span class="scroll-trust__item"><span class="scroll-trust__check">✓</span> Sin tarjeta</span>
            <span class="scroll-trust__item"><span class="scroll-trust__check">✓</span> Listo en 2 min</span>
            <span class="scroll-trust__item"><span class="scroll-trust__check">✓</span> 100% gratis</span>
          </div>
        </div>
      </div>
      <div class="scroll-hint" aria-hidden="true">
        <span>Scroll para explorar</span>
        <div class="scroll-hint__arrow"></div>
      </div>
    </section>

    <!-- ========== SECTION 1: STATS ========== -->
    <section class="scroll-section scroll-section--right" id="section-1">
      <div class="scroll-section__inner scroll-section__inner--wide">
        <div class="scroll-stagger">
          <div class="scroll-eyebrow" style="--i:0">📊 Comunidad</div>
          <h2 class="scroll-title scroll-title--sm" style="--i:1">
            La comunidad <span class="fp-gradient-text">crece</span>
          </h2>
          <p class="scroll-desc" style="--i:2;max-width:480px;">
            Miles de jugadores ya confían en FastPlay para organizar sus partidos cada semana.
          </p>
          <div class="scroll-stats" style="--i:3" id="statsRow">
            <?php foreach ($stats as $i => $s): ?>
              <div class="scroll-stat">
                <span
                  class="scroll-stat__num"
                  data-value="<?= e($s['v']) ?>"
                  style="color:<?= !empty($s['green']) ? '#4ade80' : '#fff' ?>"
                ><?= e($s['v']) ?></span>
                <span class="scroll-stat__label" style="color:<?= !empty($s['green']) ? '#4ade80' : '#c8d0d6' ?>"><?= e($s['l']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>

    <!-- ========== SECTION 2: FEATURES ========== -->
    <section class="scroll-section scroll-section--left" id="section-2">
      <div class="scroll-section__inner scroll-section__inner--wide">
        <div class="scroll-stagger">
          <div class="scroll-eyebrow" style="--i:0">⚡ Funciones</div>
          <h2 class="scroll-title scroll-title--sm" style="--i:1">
            Todo lo que <span class="fp-gradient-text">necesitas</span>
          </h2>
          <p class="scroll-desc" style="--i:2;max-width:480px;">
            Herramientas diseñadas para que organices, compitas y disfrutes del fútbol sin complicaciones.
          </p>
          <div class="scroll-features" style="--i:3">
            <?php
            $features = [
                ['icon' => '⚽', 'title' => 'Gestión de equipos', 'desc' => 'Crea tu equipo, gestiona la plantilla y define alineaciones fácilmente.'],
                ['icon' => '🏟️', 'title' => 'Campos reales',     'desc' => 'Reserva con un clic. Calendario integrado y notificaciones automáticas.'],
                ['icon' => '🎯', 'title' => 'Matchmaking inteligente', 'desc' => 'Emparejamiento automático por nivel, posición y localidad.'],
                ['icon' => '📊', 'title' => 'Estadísticas Pro',  'desc' => 'Datos detallados de cada partido: goles, asistencias, MVP y más.'],
            ];
            foreach ($features as $i => $f): ?>
              <div class="scroll-feature-item" style="transition-delay: <?= 240 + $i * 80 ?>ms;">
                <span class="scroll-feature-item__icon" aria-hidden="true"><?= $f['icon'] ?></span>
                <div>
                  <h3 class="scroll-feature-item__title"><?= e($f['title']) ?></h3>
                  <p class="scroll-feature-item__desc"><?= e($f['desc']) ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>

    <!-- ========== SECTION 3: LIGAS + PRICING ========== -->
    <section class="scroll-section scroll-section--right" id="section-3">
      <div class="scroll-section__inner scroll-section__inner--wide">
        <div class="scroll-stagger">
          <div class="scroll-eyebrow scroll-eyebrow--gold" style="--i:0">🏆 Compite</div>
          <h2 class="scroll-title scroll-title--sm" style="--i:1">
            Ligas <span class="fp-gradient-text">activas</span>
          </h2>
          <div class="scroll-league-list" style="--i:2">
            <?php foreach ($leagues as $l): $st = $l['_status']; ?>
              <a href="<?= url('leagues') ?>" class="scroll-league-item">
                <span class="scroll-league-item__icon" aria-hidden="true"><?= $l['pro'] ? '🏆' : '⚽' ?></span>
                <span class="scroll-league-item__info">
                  <div class="scroll-league-item__name">
                    <?= e($l['name']) ?>
                    <span class="scroll-league-status scroll-league-status--<?= e($st['k']) ?>">
                      <span class="scroll-league-status__dot"></span><?= e($st['label']) ?>
                    </span>
                  </div>
                  <div class="scroll-league-item__meta">
                    <span>📍 <?= e($l['city']) ?></span>
                    <span>📅 <?= e($l['start']) ?> – <?= e($l['end']) ?></span>
                  </div>
                </span>
                <?php if (!empty($l['prize'])): ?>
                  <span class="scroll-league-item__prize">💰 <?= number_format($l['prize'], 0, ',', '.') ?>€</span>
                <?php endif; ?>
                <span class="scroll-league-item__arrow" aria-hidden="true">→</span>
              </a>
            <?php endforeach; ?>
          </div>

          <p style="--i:3;font-size:13px;color:#9ca3af;margin:0 0 14px;">Elige tu nivel de competición:</p>
          <div class="scroll-pricing" style="--i:4">
            <div class="scroll-pricing__option">
              <div class="scroll-pricing__icon">🤝</div>
              <div class="scroll-pricing__name">Liga Amistosa</div>
              <div class="scroll-pricing__price scroll-pricing__price--free">Gratis</div>
              <div class="scroll-pricing__period">Sin compromisos</div>
              <ul class="scroll-pricing__features">
                <li>Partidos entre amigos</li>
                <li>Reserva de campo incluida</li>
                <li>Estadísticas básicas</li>
              </ul>
            </div>
            <div class="scroll-pricing__option scroll-pricing__option--pro">
              <div class="scroll-pricing__badge">
                <span class="fp-pro-badge" style="font-size:10px;">MÁS POPULAR</span>
              </div>
              <div class="scroll-pricing__icon">🏆</div>
              <div class="scroll-pricing__name">Liga Pro</div>
              <div class="scroll-pricing__price">20€</div>
              <div class="scroll-pricing__period">/temporada · equipo</div>
              <ul class="scroll-pricing__features">
                <li>Liga oficial con clasificación</li>
                <li>Premios y reconocimientos</li>
                <li>Estadísticas avanzadas + MVP</li>
                <li>Soporte prioritario</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ========== SECTION 4: CTA ========== -->
    <section class="scroll-section scroll-section--center" id="section-4">
      <div class="scroll-section__inner">
        <div class="scroll-stagger" style="text-align:center;">
          <div class="scroll-eyebrow" style="--i:0;margin-left:auto;margin-right:auto;background:rgba(34,197,94,.14);border-color:rgba(34,197,94,.28);">🚀 Únete ahora</div>
          <h2 class="scroll-title" style="--i:1;font-size:clamp(32px,6vw,56px);text-align:center;">
            ¿Listo para <span class="fp-gradient-text">jugar?</span>
          </h2>
          <p class="scroll-desc" style="--i:2;max-width:460px;margin-left:auto;margin-right:auto;text-align:center;font-size:17px;">
            Regístrate gratis, crea tu equipo y empieza a competir hoy mismo. El fútbol amateur te espera.
          </p>
          <div class="scroll-cta-row" style="--i:3;justify-content:center;">
            <a href="<?= url('auth/register') ?>" class="fp-btn fp-btn-primary fp-btn-glow" style="padding:18px 42px;font-size:17px;border-radius:14px;">Crear cuenta gratis →</a>
          </div>
          <div class="scroll-trust" style="--i:4;justify-content:center;" aria-label="Garantías">
            <span class="scroll-trust__item"><span class="scroll-trust__check">✓</span> Sin tarjeta</span>
            <span class="scroll-trust__item"><span class="scroll-trust__check">✓</span> Cancela cuando quieras</span>
            <span class="scroll-trust__item"><span class="scroll-trust__check">✓</span> Soporte en español</span>
          </div>
        </div>
      </div>
    </section>

  </div>
</div>

<script src="<?= asset('js/scroll-anim.js') ?>"></script>
<script src="<?= asset('js/home-init.js') ?>"></script>
