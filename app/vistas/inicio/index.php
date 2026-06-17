<section class="hero revelar">
    <!-- Fondo multimedia: el vídeo se reproduce en bucle y silenciado; si el
         navegador no puede reproducirlo, queda el póster como imagen de fondo.
         Las "formas" son blobs de color con blur que dan profundidad al hero. -->
    <div class="hero__fondo" aria-hidden="true">
        <video class="hero__video" autoplay muted loop playsinline
               poster="<?= e(url('/imagenes/hero-poster.jpg')) ?>">
            <source src="<?= e(url('/video/hero.webm')) ?>" type="video/webm">
        </video>
        <span class="hero__velo"></span>
        <span class="hero__forma hero__forma--a"></span>
        <span class="hero__forma hero__forma--b"></span>
        <span class="hero__forma hero__forma--c"></span>
    </div>

    <div class="hero__contenido">
        <span class="hero__eyebrow" data-revelar>
            <span class="hero__eyebrow-punto" aria-hidden="true"></span>
            La plataforma del fútbol amateur
        </span>

        <h1 class="hero__titulo" data-revelar>
            Tu próximo partido
            <span class="hero__titulo-degradado">empieza&nbsp;aquí</span>
        </h1>

        <p class="hero__subtitulo" data-revelar>
            Crea tu equipo, organiza partidos, reserva campos y compite en ligas
            con una sola cuenta. Sin papeleo, sin aplicaciones raras.
        </p>

        <div class="hero__acciones" data-revelar>
            <a class="boton boton--principal boton--xl" href="<?= e(url('/equipos')) ?>">
                Crear mi equipo gratis
                <svg class="boton__icono" viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                    <path d="M4 10h11M11 5l5 5-5 5" fill="none" stroke="currentColor"
                          stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <a class="boton boton--contraste boton--xl" href="<?= e(url('/partidos')) ?>">
                Ver partidos
            </a>
        </div>

        <ul class="hero__chips" data-revelar aria-label="Qué incluye FastPlay">
            <li><span aria-hidden="true">✓</span> Sin coste de alta</li>
            <li><span aria-hidden="true">✓</span> Sin descargas</li>
            <li><span aria-hidden="true">✓</span> Hecho en España</li>
        </ul>
    </div>

    <a class="hero__indicador" href="#panel-ctx" aria-label="Descubrir más">
        <span class="hero__indicador-texto">Descubrir</span>
        <span class="hero__indicador-flecha" aria-hidden="true"></span>
    </a>
</section>

<!-- Panel contextual: lo rellena public/js/panel-contextual.js vía fetch al
     endpoint JSON, adaptándose al rol de quien mira la página. -->
<section id="panel-ctx" class="panel-ctx revelar"
         data-panel-contextual
         data-url="<?= e(url('/api/contexto')) ?>"
         aria-busy="false"
         aria-labelledby="panel-ctx-titulo">
    <div class="panel-ctx__barra">
        <h2 id="panel-ctx-titulo">Tu panel</h2>
        <button type="button" class="boton boton--enlace" data-accion="recargar">Actualizar</button>
    </div>
    <p class="campo__error" role="alert" data-zona="aviso" hidden></p>
    <div class="panel-ctx__cuerpo" data-zona="cuerpo" aria-live="polite">
        <p class="panel-ctx__cargando">Cargando tu panel…</p>
    </div>
</section>

<!-- Contadores: public/js/inicio.js anima los números al entrar en pantalla. -->
<section class="cifras revelar" aria-label="FastPlay en cifras">
    <div class="cifra">
        <span class="cifra__numero" data-contador data-objetivo="1200">0</span>
        <span class="cifra__texto">Jugadores</span>
    </div>
    <div class="cifra">
        <span class="cifra__numero" data-contador data-objetivo="180">0</span>
        <span class="cifra__texto">Equipos</span>
    </div>
    <div class="cifra">
        <span class="cifra__numero" data-contador data-objetivo="540">0</span>
        <span class="cifra__texto">Partidos jugados</span>
    </div>
    <div class="cifra">
        <span class="cifra__numero" data-contador data-objetivo="24">0</span>
        <span class="cifra__texto">Campos</span>
    </div>
</section>

<section class="caracteristicas revelar">
    <article class="tarjeta">
        <h2>Equipos</h2>
        <p>Crea tu equipo, gestiona miembros y mantén la información al día.</p>
    </article>
    <article class="tarjeta">
        <h2>Partidos</h2>
        <p>Programa encuentros, consulta el calendario y registra resultados.</p>
    </article>
    <article class="tarjeta">
        <h2>Campos</h2>
        <p>Encuentra los campos disponibles de tu ciudad y su superficie.</p>
    </article>
    <article class="tarjeta">
        <h2>Ligas</h2>
        <p>Sigue las clasificaciones y los próximos partidos de cada liga.</p>
    </article>
</section>

<!-- Comunidad: muestra a algunos jugadores reales de la plataforma. -->
<section class="comunidad revelar" aria-label="Jugadores de la comunidad">
    <h2 class="comunidad__titulo">Una comunidad que ya juega</h2>
    <p class="comunidad__lema">Cientos de jugadores organizan sus partidos cada semana con FastPlay.</p>
    <ul class="comunidad__lista">
        <?php
        $jugadores = [
            ['foto' => 'aiman-mohamed.webp',  'nombre' => 'Aiman Mohamed'],
            ['foto' => 'alfonso-murube.jpg',  'nombre' => 'Alfonso Murube'],
            ['foto' => 'emilio-cozar.jpg',    'nombre' => 'Emilio Cózar'],
            ['foto' => 'jose-benoliel.jpg',   'nombre' => 'José Benoliel'],
            ['foto' => 'jose-pirri.jpeg',     'nombre' => 'José Pirri'],
            ['foto' => 'tuhami-al-lal.webp',  'nombre' => 'Tuhami Al-Lal'],
        ];
        foreach ($jugadores as $j): ?>
            <li class="comunidad__jugador">
                <img class="comunidad__foto"
                     src="<?= e(url('/imagenes/jugadores/' . $j['foto'])) ?>"
                     alt="<?= e($j['nombre']) ?>" width="96" height="96"
                     loading="lazy" decoding="async">
                <span class="comunidad__nombre"><?= e($j['nombre']) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<!-- Carta tipo FIFA: public/js/carta-jugador.js le da efecto tilt 3D que
     sigue al puntero (evento pointermove). -->
<section class="seccion-carta revelar" aria-label="Demostración de carta de jugador">
    <h2 class="seccion-carta__titulo">Tu carta de jugador</h2>
    <div class="carta-fifa" data-carta tabindex="0" aria-label="Carta de jugador de ejemplo">
        <div class="carta-fifa__brillo" aria-hidden="true"></div>
        <div class="carta-fifa__contenido">
            <div class="carta-fifa__cabecera">
                <span class="carta-fifa__media">92</span>
                <span class="carta-fifa__posicion">DEL</span>
            </div>
            <img class="carta-fifa__foto"
                 src="<?= e(url('/imagenes/jugadores/jose-pirri.jpeg')) ?>"
                 alt="" width="160" height="160" loading="lazy" decoding="async">
            <span class="carta-fifa__nombre">FastPlayer</span>
            <ul class="carta-fifa__stats">
                <li><strong>RIT</strong> 90</li>
                <li><strong>TIR</strong> 93</li>
                <li><strong>PAS</strong> 81</li>
                <li><strong>REG</strong> 89</li>
            </ul>
        </div>
    </div>
    <p class="seccion-carta__pie">Mueve el ratón sobre la carta para ver el efecto 3D.</p>
</section>
