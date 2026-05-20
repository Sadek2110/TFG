<main class="fp-fade fp-page fp-medium-container">
    <?php $this->partial('back-button', ['href' => url('campos')]); ?>
    <p class="fp-eyebrow">Campo</p>
    <h1 class="fp-h1"><?= e($field['name']) ?></h1>
    <p class="fp-muted"><i class="bi bi-geo-alt"></i> <?= e($field['city']) ?></p>

    <div class="fp-glass fp-panel">
        <ul class="fp-detail">
            <li><span>Dirección</span><strong><?= e($field['address'] ?? 'N/D') ?></strong></li>
            <li><span>Superficie</span><strong><?= e($field['surface']) ?></strong></li>
            <li><span>Capacidad</span><strong><?= (int) $field['capacity'] ?> jugadores</strong></li>
            <li><span>Tarifa</span><strong><?= number_format((float) $field['hourly_rate'], 2, ',', '.') ?> EUR/hora</strong></li>
        </ul>
        <div class="fp-actions-row">
            <a href="<?= url('matches/create') ?>" class="fp-btn fp-btn-primary">Solicitar partido aquí</a>
            <?php if (!empty($field['maps_url'])): ?><a href="<?= e($field['maps_url']) ?>" target="_blank" rel="noopener" class="fp-btn fp-btn-ghost">Google Maps</a><?php endif; ?>
        </div>
    </div>
</main>
