<link rel="stylesheet" href="<?= asset('css/scroll-anim.css') ?>">
<style>
html { scroll-behavior: auto !important; }

/* ====== Navbar fusionado con el fondo ====== */
.fp-navbar {
  z-index: 60 !important;
  background: transparent !important;
  backdrop-filter: none !important;
  -webkit-backdrop-filter: none !important;
  border-bottom: 0 !important;
  box-shadow: none !important;
}
.fp-navbar::before {
  content: '';
  position: absolute; inset: 0; pointer-events: none;
  background: linear-gradient(180deg, rgba(6,13,9,.82) 0%, rgba(6,13,9,.55) 55%, rgba(6,13,9,0) 100%);
  backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
  -webkit-mask-image: linear-gradient(180deg, #000 0%, #000 45%, transparent 100%);
          mask-image: linear-gradient(180deg, #000 0%, #000 45%, transparent 100%);
  z-index: -1; transition: background .4s, backdrop-filter .4s;
}
.fp-navbar.scrolled::before {
  background: linear-gradient(180deg, rgba(6,13,9,.92) 0%, rgba(6,13,9,.70) 55%, rgba(6,13,9,0) 100%);
  backdrop-filter: blur(22px); -webkit-backdrop-filter: blur(22px);
}
.fp-footer, .fp-bg-glow { display: none !important; }

/* ====== Leagues ====== */
.scroll-league-list {
  display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px;
}
.scroll-league-item {
  display: flex; align-items: center; gap: 14px;
  padding: 12px 0; text-decoration: none; color: #fff;
  border-bottom: 1px solid rgba(255,255,255,.06);
  transition: padding .2s, border-color .2s;
}
.scroll-league-item:hover {
  border-bottom-color: rgba(34,197,94,.35); padding-left: 8px;
}
.scroll-league-item:focus-visible {
  outline: 2px solid #4ade80; outline-offset: 2px; border-radius: 6px;
}
.scroll-league-item__icon { font-size: 22px; flex-shrink: 0; }
.scroll-league-item__info { flex: 1; min-width: 0; }
.scroll-league-item__name {
  font-weight: 800; font-size: 15px; margin-bottom: 4px;
  text-shadow: 0 1px 8px rgba(0,0,0,.5);
  display: inline-flex; align-items: center; gap: 8px; flex-wrap: wrap;
}
.scroll-league-item__meta {
  font-size: 11.5px; color: #a8b3bb; display: flex; gap: 12px; flex-wrap: wrap;
  text-shadow: 0 1px 6px rgba(0,0,0,.55);
}
.scroll-league-item__arrow {
  color: #4ade80; font-weight: 700; font-size: 16px; flex-shrink: 0;
  opacity: 0; transform: translateX(-8px); transition: all .2s;
}
.scroll-league-item:hover .scroll-league-item__arrow { opacity: 1; transform: translateX(0); }
.scroll-league-item__prize {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 3px 10px; border-radius: 9999px;
  font-size: 11px; color: #fbbf24; font-weight: 600; white-space: nowrap;
  background: rgba(245,158,11,.10); border: 1px solid rgba(245,158,11,.16);
  backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
}

/* ====== League status pill ====== */
.scroll-league-status {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 2px 8px; border-radius: 9999px;
  font-size: 10px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
}
.scroll-league-status__dot { width: 6px; height: 6px; border-radius: 50%; }
.scroll-league-status--live {
  color: #4ade80; background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.3);
}
.scroll-league-status--live .scroll-league-status__dot {
  background: #4ade80; box-shadow: 0 0 8px #4ade80;
  animation: fp-pulse 1.6s ease-in-out infinite;
}
.scroll-league-status--open {
  color: #fbbf24; background: rgba(245,158,11,.12); border: 1px solid rgba(245,158,11,.3);
}
.scroll-league-status--open .scroll-league-status__dot { background: #fbbf24; }
.scroll-league-status--ended {
  color: #9ca3af; background: rgba(156,163,175,.10); border: 1px solid rgba(156,163,175,.22);
}
.scroll-league-status--ended .scroll-league-status__dot { background: #6b7280; }

/* ====== Pricing mini-cards ====== */
.scroll-pricing { display: flex; gap: 12px; }
.scroll-pricing__option {
  flex: 1; padding: 20px 18px; border-radius: 16px;
  border: 1px solid rgba(255,255,255,.06); background: rgba(255,255,255,.02);
  backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
  text-align: center; position: relative;
  transition: border-color .25s, background .25s, transform .25s, box-shadow .25s;
}
.scroll-pricing__option:hover { border-color: rgba(34,197,94,.25); transform: translateY(-3px); }
.scroll-pricing__option--pro {
  border-color: rgba(34,197,94,.28); background: rgba(22,163,74,.06);
  box-shadow: 0 0 0 1px rgba(34,197,94,.12), 0 12px 40px rgba(22,163,74,.12);
}
.scroll-pricing__option--pro:hover {
  border-color: rgba(34,197,94,.5);
  box-shadow: 0 0 0 1px rgba(34,197,94,.2), 0 16px 48px rgba(22,163,74,.22);
}
.scroll-pricing__icon { font-size: 26px; margin-bottom: 6px; }
.scroll-pricing__name { font-weight: 800; font-size: 14px; margin-bottom: 8px; text-shadow: 0 1px 8px rgba(0,0,0,.5); }
.scroll-pricing__price {
  font-size: 32px; font-weight: 900; color: #4ade80; line-height: 1;
  text-shadow: 0 0 16px rgba(34,197,94,.2); font-variant-numeric: tabular-nums;
}
.scroll-pricing__price--free { color: #fff; }
.scroll-pricing__period { font-size: 10px; color: #6b7280; margin-top: 4px; letter-spacing: .04em; }
.scroll-pricing__badge { position: absolute; top: -10px; left: 50%; transform: translateX(-50%); display: inline-block; }
.scroll-pricing__features {
  list-style: none; padding: 14px 0 0; margin: 14px 0 0;
  border-top: 1px solid rgba(255,255,255,.06);
  display: flex; flex-direction: column; gap: 8px; text-align: left;
}
.scroll-pricing__features li {
  display: flex; align-items: flex-start; gap: 8px;
  font-size: 12px; color: #d1d5db; line-height: 1.4;
}
.scroll-pricing__features li::before {
  content: "✓"; color: #4ade80; font-weight: 900; flex-shrink: 0; font-size: 13px; line-height: 1.3;
}

/* ====== Responsive — page-specific ====== */
@media (max-width: 980px) {
  .scroll-anim__sides { background: linear-gradient(90deg, rgba(6,13,9,.45) 0%, rgba(6,13,9,.08) 15%, transparent 100%); }
}
@media (max-width: 768px) {
  .scroll-pricing { flex-direction: column; }
}

/* ====== Reduced motion — page-specific ====== */
@media (prefers-reduced-motion: reduce) {
  .scroll-league-item,
  .scroll-pricing__option,
  .scroll-league-status--live .scroll-league-status__dot {
    animation: none !important; transition: none !important;
    transform: none !important; opacity: 1 !important;
  }
}
</style>

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

<div class="scroll-anim">

  <div class="scroll-progress" id="scrollProgress"></div>

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
<script>
(function() {
  'use strict';

  var navbar = document.querySelector('.fp-navbar');
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function parseStat(raw) {
    var s = String(raw).trim();
    var m = s.match(/^([^\d\-,\.]*)([\-]?[\d\.,]+)([^\d]*)$/);
    if (!m) return null;
    var prefix = m[1] || '';
    var numRaw = m[2] || '';
    var suffix = m[3] || '';
    var n;
    if (numRaw.indexOf(',') !== -1) {
      n = parseFloat(numRaw.replace(/\./g, '').replace(',', '.'));
    } else {
      n = parseFloat(numRaw.replace(/\./g, ''));
    }
    if (!isFinite(n)) return null;
    var hasComma = numRaw.indexOf(',') !== -1;
    var decimals = hasComma ? (numRaw.split(',')[1] || '').length : 0;
    return { num: n, prefix: prefix, suffix: suffix, decimals: decimals };
  }

  function formatStat(value, parsed) {
    var fixed = value.toFixed(parsed.decimals);
    if (parsed.decimals > 0) fixed = fixed.replace('.', ',');
    if (parsed.decimals === 0 && /^[\-]?\d+$/.test(fixed) && Math.abs(value) >= 1000 && !parsed.suffix) {
      fixed = Number(fixed).toLocaleString('es-ES');
    }
    return parsed.prefix + fixed + parsed.suffix;
  }

  function animateNumber(el) {
    if (el.dataset.animated === '1') return;
    var parsed = parseStat(el.dataset.value || el.textContent);
    if (!parsed) return;
    el.dataset.animated = '1';
    if (reduceMotion) { el.textContent = formatStat(parsed.num, parsed); return; }
    var duration = 1200, start = performance.now();
    function tick(now) {
      var t = Math.min((now - start) / duration, 1);
      var eased = 1 - Math.pow(1 - t, 3);
      el.textContent = formatStat(parsed.num * eased, parsed);
      if (t < 1) requestAnimationFrame(tick);
      else el.textContent = formatStat(parsed.num, parsed);
    }
    requestAnimationFrame(tick);
  }

  FastPlayScrollAnim.init({
    onScroll: function(scrollTop) {
      if (navbar) {
        if (scrollTop > 60) navbar.classList.add('scrolled');
        else navbar.classList.remove('scrolled');
      }
    },
    onReveal: function(target) {
      var nums = target.querySelectorAll('.scroll-stat__num[data-value]');
      if (nums.length) nums.forEach(animateNumber);
    }
  });
})();
</script>
