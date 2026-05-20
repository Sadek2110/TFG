<main class="fp-fade fp-page fp-large-container">
    <p class="fp-eyebrow">Premium</p>
    <h1 class="fp-h1">Suscripción FastPlay Premium</h1>

    <section class="fp-glass fp-panel fp-premium-panel <?= !$isPremium ? 'fp-premium-glow' : 'fp-green-glow' ?>">
        <div>
            <h2><?= $isPremium ? 'Premium activo' : 'Crea y gestiona tu equipo' ?></h2>
            <p class="fp-muted">Premium habilita la creación de equipos y deja preparada la plataforma para funciones avanzadas.</p>
            <ul class="fp-check-list">
                <li><i class="bi bi-check2"></i> Crear equipo</li>
                <li><i class="bi bi-check2"></i> Ser capitán</li>
                <li><i class="bi bi-check2"></i> Acceso a futuras funciones premium</li>
            </ul>
        </div>
        <?php if (!$isPremium): ?>
            <form method="post" action="<?= url('subscription/checkout') ?>">
                <?= csrf_field() ?>
                <button class="fp-btn fp-btn-primary fp-btn-glow"><i class="bi bi-credit-card"></i><span>Mejorar a Premium</span></button>
            </form>
        <?php else: ?>
            <span class="fp-status fp-status-confirmed">Activo</span>
        <?php endif; ?>
    </section>
</main>
