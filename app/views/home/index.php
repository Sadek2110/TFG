<?php $pageTitle = 'Inicio'; ?>
<?php require_once APP_PATH . '/views/partials/header.php'; ?>
<?php require_once APP_PATH . '/views/partials/navbar.php'; ?>

<style>
.scroll-section {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40%;
    opacity: 0;
    pointer-events: none;
    z-index: 20;
    will-change: opacity, transform;
}
.scroll-section.active { pointer-events: auto; }
@keyframes scrollDown {
    0%   { transform: translateY(-100%); }
    100% { transform: translateY(200%);  }
}
/* Extend glass transition to include lift + shadow for cards in scroll sections */
.scroll-section .glass,
.scroll-section .glass-green {
    transition: background 0.25s ease, border-color 0.25s ease,
                transform 0.22s cubic-bezier(.22,1,.36,1), box-shadow 0.25s ease;
}
.scroll-section .glass:hover,
.scroll-section .glass-green:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(0,0,0,.35);
}
</style>

<!-- Main container for the scroll height -->
<div id="main-scroll-container" style="height: 400vh; position: relative;">
    
    <!-- Sticky container that holds the video and UI overlays -->
    <div class="sticky top-0 w-full h-screen overflow-hidden bg-black">
        
        <!-- Glowing background behind the ball -->
        <div class="absolute right-[10%] top-1/2 -translate-y-1/2 w-[40vw] h-[40vw] bg-green-600/20 rounded-full blur-[120px] pointer-events-none z-0"></div>

        <!-- Canvas Background for Image Sequence -->
        <canvas id="bg-canvas" class="absolute right-[5%] top-1/2 -translate-y-1/2 w-[55vw] h-[80vh] opacity-100 z-10" style="-webkit-mask-image: radial-gradient(ellipse at center, black 40%, transparent 75%); mask-image: radial-gradient(ellipse at center, black 40%, transparent 75%);"></canvas>
        
        <!-- Particles Canvas -->
        <canvas id="particles-canvas" class="absolute inset-0 w-full h-full pointer-events-none z-10"></canvas>
        
        <!-- Removed gradients to keep the background clean -->

        <!-- Content that stays fixed but animates based on scroll -->
        <div class="relative w-full h-full max-w-[1400px] mx-auto px-8 pointer-events-none z-20">
            
            <!-- ================= PHASE 1: Hero (Left) ================= -->
            <div class="scroll-section" id="section-0" style="left: 0; margin-left: 2rem;">
                <div class="glass rounded-3xl p-10 bg-black/50 shadow-2xl backdrop-blur-xl border-white/10 pointer-events-auto">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-green-500/30 bg-green-500/20 text-green-400 text-sm font-semibold mb-8">
                        <span class="w-2 h-2 bg-green-400 rounded-full pulse-dot"></span>
                        Liga Pro Temporada 2026 — ¡Inscripciones abiertas!
                    </div>
                    <h1 class="text-6xl lg:text-7xl font-black leading-none tracking-tight mb-6 text-white drop-shadow-lg">
                        ¿Te apetece<br>
                        <span class="gradient-text">jugar?</span>
                    </h1>
                    <p class="text-xl text-gray-200 max-w-lg mb-10 leading-relaxed drop-shadow-md">
                        FastPlay conecta jugadores, organiza partidos en campos reales y lleva el fútbol amateur al siguiente nivel. 
                        <strong class="text-white">En cualquier lugar. Para todos.</strong>
                    </p>
                    <div class="flex flex-col sm:flex-row items-start gap-4">
                        <?php if (!empty($_SESSION['user_id'])): ?>
                            <a href="<?= APP_URL ?>/dashboard" class="btn-primary text-lg px-8 py-3.5 glow-green shadow-lg">
                                Mi dashboard →
                            </a>
                        <?php else: ?>
                            <a href="<?= APP_URL ?>/register" class="btn-primary text-lg px-8 py-3.5 glow-green shadow-lg">
                                Empieza gratis →
                            </a>
                            <a href="<?= APP_URL ?>/leagues" class="btn-ghost text-lg px-8 py-3.5 shadow-lg bg-black/40 hover:bg-black/60">
                                Ver ligas
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ================= PHASE 2: Stats & Matches (Left) ================= -->
            <div class="scroll-section" id="section-1" style="left: 0; margin-left: 2rem;">
                <div class="glass rounded-3xl p-10 bg-black/50 shadow-2xl backdrop-blur-xl border-white/10 flex flex-col items-start text-left pointer-events-auto">
                    <h2 class="text-4xl lg:text-5xl font-black mb-8">La comunidad <span class="gradient-text">crece</span></h2>
                    
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 gap-4 mb-10 pointer-events-auto">
                        <div class="glass rounded-2xl p-5 text-center w-40">
                            <div class="text-4xl font-black text-white">12K+</div>
                            <div class="text-xs text-gray-500 font-medium uppercase mt-1">Jugadores</div>
                        </div>
                        <div class="glass rounded-2xl p-5 text-center w-40">
                            <div class="text-4xl font-black text-white">3.4K</div>
                            <div class="text-xs text-gray-500 font-medium uppercase mt-1">Partidos</div>
                        </div>
                        <div class="glass rounded-2xl p-5 text-center w-40">
                            <div class="text-4xl font-black text-white">48</div>
                            <div class="text-xs text-gray-500 font-medium uppercase mt-1">Ciudades</div>
                        </div>
                        <div class="glass rounded-2xl p-5 text-center w-40 bg-green-500/5 border-green-500/20">
                            <div class="text-4xl font-black text-green-400">100%</div>
                            <div class="text-xs text-green-500 font-medium uppercase mt-1">Gratis*</div>
                        </div>
                    </div>

                    <?php if (!empty($upcomingMatches)): ?>
                        <div class="w-full max-w-sm pointer-events-auto text-left">
                            <p class="text-green-400 font-semibold text-xs uppercase tracking-widest mb-3">En Vivo: Próximos Partidos</p>
                            <div class="space-y-3">
                                <?php foreach (array_slice($upcomingMatches, 0, 2) as $m): ?>
                                <a href="<?= APP_URL ?>/matches/<?= $m['id'] ?>" class="glass rounded-xl p-4 flex flex-col hover:bg-white/5 transition block">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[10px] px-2 py-0.5 rounded bg-green-500/10 text-green-400 border border-green-500/20">Confirmado</span>
                                        <span class="text-[10px] text-gray-400"><?= date('d/m H:i', strtotime($m['match_date'])) ?></span>
                                    </div>
                                    <div class="flex justify-between items-center gap-2">
                                        <span class="font-bold text-sm truncate w-[40%]"><?= htmlspecialchars($m['home_team_name']) ?></span>
                                        <span class="text-xs font-black text-gray-500">VS</span>
                                        <span class="font-bold text-sm truncate w-[40%] text-right"><?= htmlspecialchars($m['away_team_name']) ?></span>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ================= PHASE 3: Features (Left) ================= -->
            <div class="scroll-section" id="section-2" style="left: 0; margin-left: 2rem;">
                <div class="glass rounded-3xl p-10 bg-black/50 shadow-2xl backdrop-blur-xl border-white/10 pointer-events-auto">
                    <h2 class="text-4xl lg:text-5xl font-black mb-8 drop-shadow-md">Todo lo que <span class="gradient-text">necesitas</span></h2>
                    <div class="space-y-4 max-w-md">
                    
                    <div class="glass-green rounded-2xl p-6 flex gap-4 items-start group hover:bg-green-500/10 transition">
                        <div class="text-4xl">⚽</div>
                        <div>
                            <h3 class="text-lg font-black mb-1">Crea y gestiona tu equipo</h3>
                            <p class="text-gray-400 text-sm">Arma tu plantilla, define la alineación y reta a otros equipos en el chat.</p>
                        </div>
                    </div>

                    <div class="glass rounded-2xl p-6 flex gap-4 items-start hover:bg-white/5 transition">
                        <div class="text-4xl">🏟️</div>
                        <div>
                            <h3 class="text-lg font-black mb-1">Campos reales certificados</h3>
                            <p class="text-gray-400 text-sm">Reserva con un clic. Calendario integrado y notificaciones.</p>
                        </div>
                    </div>

                    <div class="glass rounded-2xl p-6 flex gap-4 items-start hover:bg-white/5 transition relative overflow-hidden">
                        <div class="absolute top-3 right-3 px-2 py-0.5 rounded text-[10px] font-black bg-gradient-to-r from-yellow-400 to-amber-500 text-black">PRO</div>
                        <div class="text-4xl">🏆</div>
                        <div>
                            <h3 class="text-lg font-black mb-1">Liga Pro</h3>
                            <p class="text-gray-400 text-sm">Árbitro oficial, estadísticas completas y premios reales.</p>
                        </div>
                    </div>

                    <div class="glass rounded-2xl p-6 flex gap-4 items-start hover:bg-white/5 transition">
                        <div class="text-4xl">🎯</div>
                        <div>
                            <h3 class="text-lg font-black mb-1">Matchmaking inteligente</h3>
                            <p class="text-gray-400 text-sm">Emparejamiento automático por nivel, posición y localidad.</p>
                        </div>
                    </div>

                    </div>
                </div>
            </div>

            <!-- ================= PHASE 4: Leagues (Left) ================= -->
            <div class="scroll-section" id="section-3" style="left: 0; margin-left: 2rem;">
                <div class="glass rounded-3xl p-10 bg-black/50 shadow-2xl backdrop-blur-xl border-white/10 flex flex-col items-start text-left pointer-events-auto">
                    <p class="text-green-400 font-semibold text-sm uppercase tracking-widest mb-2">Compite</p>
                    <h2 class="text-4xl lg:text-5xl font-black mb-8 drop-shadow-md">Ligas <span class="gradient-text">activas</span></h2>
                    
                    <div class="space-y-4 max-w-sm w-full">
                        <?php if (!empty($activeLeagues)): ?>
                            <?php foreach (array_slice($activeLeagues, 0, 4) as $l): ?>
                            <a href="<?= APP_URL ?>/leagues/<?= $l['id'] ?>" class="glass rounded-2xl p-5 flex flex-col text-left hover:bg-white/5 hover:border-green-500/30 transition duration-300 <?= $l['type'] === 'pro' ? 'border-yellow-500/20' : '' ?>">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-black text-lg"><?= htmlspecialchars($l['name']) ?></h3>
                                    <?php if ($l['type'] === 'pro'): ?>
                                        <span class="text-[10px] px-2 py-1 rounded bg-yellow-500/20 text-yellow-400 font-bold">LIGA PRO</span>
                                    <?php else: ?>
                                        <span class="text-[10px] px-2 py-1 rounded bg-white/10 text-gray-300">Amistosa</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-gray-400 mb-3">📍 <?= htmlspecialchars($l['city']) ?></p>
                                <div class="text-[10px] text-gray-500 flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                    <?= date('d/m/Y', strtotime($l['start_date'])) ?> — <?= date('d/m/Y', strtotime($l['end_date'])) ?>
                                </div>
                            </a>
                            <?php endforeach; ?>
                            
                            <a href="<?= APP_URL ?>/leagues" class="btn-ghost w-full justify-center mt-2 py-3 text-sm">Ver todas las ligas →</a>
                        <?php else: ?>
                            <div class="glass rounded-2xl p-8 text-center">
                                <span class="text-3xl mb-3 block">⏳</span>
                                <p class="text-gray-400">Próximamente nuevas ligas.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ================= PHASE 5: Pricing / CTA ================= -->
            <div class="scroll-section flex flex-col gap-6" id="section-4" style="left: 0; margin-left: 2rem;">
                
                <!-- Free Plan -->
                <div class="glass rounded-3xl p-8 w-full pointer-events-auto bg-black/50 shadow-2xl backdrop-blur-xl border-white/10 hover:bg-black/70 transition">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="text-4xl">🤝</div>
                        <div>
                            <h3 class="text-xl font-black">Liga Amistosa</h3>
                            <p class="text-gray-400 text-xs">Para jugar sin compromisos</p>
                        </div>
                    </div>
                    
                    <div class="mb-8">
                        <span class="text-5xl font-black">Gratis</span>
                    </div>
                    
                    <ul class="space-y-3 mb-10">
                        <li class="flex items-center gap-3 text-sm text-gray-300"><span class="text-green-400">✓</span> Perfil de jugador completo</li>
                        <li class="flex items-center gap-3 text-sm text-gray-300"><span class="text-green-400">✓</span> Búsqueda de equipos</li>
                        <li class="flex items-center gap-3 text-sm text-gray-300"><span class="text-green-400">✓</span> Partidos sin árbitro</li>
                        <li class="flex items-center gap-3 text-sm text-gray-300"><span class="text-green-400">✓</span> Acceso a campos</li>
                    </ul>
                    <a href="<?= APP_URL ?>/register" class="btn-ghost w-full justify-center py-3">Empezar gratis</a>
                </div>

                <!-- Pro Plan -->
                <div class="glass-green rounded-3xl p-8 w-full pointer-events-auto bg-green-950/50 shadow-2xl backdrop-blur-xl border-green-500/40 glow-sm relative hover:shadow-[0_0_30px_rgba(22,163,74,0.3)] hover:bg-green-900/60 transition">
                    <div class="absolute top-4 right-4 px-3 py-1 rounded text-[10px] font-black bg-gradient-to-r from-yellow-400 to-amber-500 text-black">MÁS POPULAR</div>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="text-4xl">🏆</div>
                        <div>
                            <h3 class="text-xl font-black">Liga Pro</h3>
                            <p class="text-gray-400 text-xs">Para los que quieren competir</p>
                        </div>
                    </div>
                    
                    <div class="mb-8">
                        <span class="text-5xl font-black text-green-400">20€</span>
                        <span class="text-gray-400 text-sm">/temporada por eq.</span>
                    </div>
                    
                    <ul class="space-y-3 mb-10">
                        <li class="flex items-center gap-3 text-sm text-gray-200"><span class="bg-green-500 w-4 h-4 flex items-center justify-center rounded-full text-white text-[10px] font-bold">✓</span> Todo lo de Amistosa</li>
                        <li class="flex items-center gap-3 text-sm text-gray-200"><span class="bg-green-500 w-4 h-4 flex items-center justify-center rounded-full text-white text-[10px] font-bold">✓</span> Árbitro oficial</li>
                        <li class="flex items-center gap-3 text-sm text-gray-200"><span class="bg-green-500 w-4 h-4 flex items-center justify-center rounded-full text-white text-[10px] font-bold">✓</span> Estadísticas completas</li>
                        <li class="flex items-center gap-3 text-sm text-gray-200"><span class="bg-green-500 w-4 h-4 flex items-center justify-center rounded-full text-white text-[10px] font-bold">✓</span> Premios económicos reales</li>
                    </ul>
                    <a href="<?= APP_URL ?>/register" class="btn-primary w-full justify-center py-3 glow-green">Inscribir mi equipo →</a>
                </div>

            </div>
            
        </div>

        <!-- Scroll Indicator (Visible at top only) -->
        <div id="scroll-indicator" class="absolute bottom-10 flex flex-col items-center gap-3 z-30"
             style="left:50%; transform:translateX(-50%); transition: opacity .5s ease, transform .5s ease;">
            <span class="text-[10px] font-bold text-white/60 uppercase tracking-[0.3em]">Scroll</span>
            <div class="w-px h-12 bg-white/20 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1/2 bg-green-400 animate-[scrollDown_1.5s_ease-in-out_infinite]"></div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('bg-canvas');
    const ctx = canvas.getContext('2d');
    const scrollContainer = document.getElementById('main-scroll-container');
    const scrollIndicator = document.getElementById('scroll-indicator');
    
    // The sections to animate
    const sections = [
        document.getElementById('section-0'),
        document.getElementById('section-1'),
        document.getElementById('section-2'),
        document.getElementById('section-3'),
        document.getElementById('section-4')
    ];

    // Image Sequence Preloading & Canvas setup
    const frameCount = 56;
    const images = [];
    const currentFrame = index => `<?= APP_URL ?>/assets/ezgif-492d760405175bba-png-split/ezgif-frame-${index.toString().padStart(3, '0')}.png`;

    for (let i = 1; i <= frameCount; i++) {
        const img = new Image();
        img.src = currentFrame(i);
        images.push(img);
    }

    let lastRenderedIndex = -1;
    const renderFrame = (frameIndex) => {
        if (frameIndex === lastRenderedIndex) return;
        if (!images[frameIndex]) return;
        lastRenderedIndex = frameIndex;
        const img = images[frameIndex];

        const draw = () => {
            const canvasRatio = canvas.width / canvas.height;
            const imgRatio = img.width / img.height;
            let drawWidth, drawHeight, offsetX, offsetY;

            if (canvasRatio > imgRatio) {
                drawHeight = canvas.height;
                drawWidth = canvas.height * imgRatio;
            } else {
                drawWidth = canvas.width;
                drawHeight = canvas.width / imgRatio;
            }
            offsetX = (canvas.width - drawWidth) / 2;
            offsetY = (canvas.height - drawHeight) / 2;

            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, offsetX, offsetY, drawWidth, drawHeight);
        };

        if (!img.complete) {
            img.onload = () => { if (lastRenderedIndex === frameIndex) draw(); };
        } else {
            draw();
        }
    };

    // Particles Engine
    const pCanvas = document.getElementById('particles-canvas');
    const pCtx = pCanvas.getContext('2d');
    let particles = [];
    
    const createParticles = () => {
        particles = [];
        const count = window.innerWidth < 768 ? 35 : 80;
        for (let i = 0; i < count; i++) {
            const isSpark = Math.random() > 0.65;
            particles.push({
                x: Math.random() * window.innerWidth,
                y: Math.random() * window.innerHeight,
                w: isSpark ? (Math.random() * 2 + 1) : (Math.random() * 3 + 2),
                h: isSpark ? (Math.random() * 2 + 1) : (Math.random() * 9 + 5),
                vx: (Math.random() - 0.5) * 1.2,
                vy: (Math.random() - 0.5) * 1.2 - 0.35,
                angle: Math.random() * Math.PI * 2,
                spin: (Math.random() - 0.5) * 0.08,
                alpha: Math.random() * 0.35 + 0.08,
                color: ['#16a34a','#4ade80','#22c55e','#86efac'][Math.floor(Math.random()*4)],
                shape: isSpark ? 'circle' : 'rect'
            });
        }
    };

    const drawParticles = () => {
        if(!pCanvas.width) return requestAnimationFrame(drawParticles);
        pCtx.clearRect(0, 0, pCanvas.width, pCanvas.height);
        
        particles.forEach(p => {
            p.x += p.vx;
            p.y += p.vy;
            p.angle += p.spin;

            if (p.x < -20) p.x = pCanvas.width + 20;
            if (p.x > pCanvas.width + 20) p.x = -20;
            if (p.y < -20) p.y = pCanvas.height + 20;
            if (p.y > pCanvas.height + 20) p.y = -20;

            pCtx.save();
            pCtx.translate(p.x, p.y);
            pCtx.rotate(p.angle);
            pCtx.fillStyle = p.color;
            pCtx.globalAlpha = p.alpha;
            if (p.shape === 'circle') {
                pCtx.beginPath();
                pCtx.arc(0, 0, p.w, 0, Math.PI * 2);
                pCtx.fill();
            } else {
                pCtx.fillRect(-p.w/2, -p.h/2, p.w, p.h);
            }
            pCtx.restore();
        });
        requestAnimationFrame(drawParticles);
    };
    drawParticles();

    const resizeCanvas = () => {
        // Set actual pixel dimensions to match display dimensions
        canvas.width = canvas.clientWidth;
        canvas.height = canvas.clientHeight;
        pCanvas.width = canvas.clientWidth;
        pCanvas.height = canvas.clientHeight;
        
        createParticles();

        if (lastRenderedIndex !== -1) {
            renderFrame(lastRenderedIndex);
        } else {
            renderFrame(0);
        }
    };
    
    window.addEventListener('resize', resizeCanvas);
    // Let browser calculate CSS size first, then adjust canvas pixels
    setTimeout(resizeCanvas, 0);

    let animationFrameId = null;

    // Define scroll boundaries for each section
    // Total scroll progress goes from 0.0 to 1.0
    const breakpoints = [
        { start: 0.00, end: 0.15 }, // Section 0: Hero
        { start: 0.20, end: 0.35 }, // Section 1: Stats
        { start: 0.40, end: 0.55 }, // Section 2: Features
        { start: 0.60, end: 0.75 }, // Section 3: Leagues
        { start: 0.80, end: 1.00 }  // Section 4: Pricing
    ];

    const easeInOut = window.utils.easeInOut;
    let indicatorVisible = true;

    const onScroll = () => {
        const scrollY = window.scrollY;
        const containerTop = scrollContainer.offsetTop;
        const containerHeight = scrollContainer.scrollHeight - window.innerHeight;

        let progress = (scrollY - containerTop) / containerHeight;
        if (progress < 0) progress = 0;
        if (progress > 1) progress = 1;

        renderFrame(Math.min(frameCount - 1, Math.floor(progress * frameCount)));

        const shouldShow = progress <= 0.025;
        if (shouldShow !== indicatorVisible) {
            indicatorVisible = shouldShow;
            scrollIndicator.style.opacity = shouldShow ? '1' : '0';
            scrollIndicator.style.transform = shouldShow ? 'translateX(-50%) translateY(0)' : 'translateX(-50%) translateY(8px)';
            scrollIndicator.style.pointerEvents = shouldShow ? '' : 'none';
        }

        sections.forEach((sec, index) => {
            const bp = breakpoints[index];
            const fadeZone = 0.05;
            const fadeStart = bp.start - fadeZone;
            const fadeEnd   = bp.end   + fadeZone;

            if (progress > fadeStart && progress < fadeEnd) {
                sec.classList.add('active');

                let rawRatio = 0;
                let yDir = 0;

                if (progress < bp.start) {
                    rawRatio = (bp.start - progress) / fadeZone;
                    yDir = 1;
                } else if (progress > bp.end) {
                    rawRatio = (progress - bp.end) / fadeZone;
                    yDir = -1;
                }

                const smooth  = easeInOut(Math.min(1, rawRatio));
                const opacity = 1 - smooth;

                if (opacity < 0.005) {
                    sec.classList.remove('active');
                    sec.style.opacity   = '0';
                    sec.style.transform = 'translateY(-50%)';
                    return;
                }

                sec.style.opacity   = opacity;
                sec.style.transform = `translateY(calc(-50% + ${yDir * smooth * 36}px))`;
            } else {
                sec.classList.remove('active');
                sec.style.opacity   = '0';
                sec.style.transform = 'translateY(-50%)';
            }
        });
    };

    window.addEventListener('scroll', () => {
        if (!animationFrameId) {
            animationFrameId = requestAnimationFrame(() => {
                onScroll();
                animationFrameId = null;
            });
        }
    }, { passive: true });

    // Run once on load to set initial state
    onScroll();
});
</script>

<?php require_once APP_PATH . '/views/partials/footer.php'; ?>
