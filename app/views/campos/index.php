<main class="fp-fade fp-page">
    <div class="fp-page-head">
        <div>
            <p class="fp-eyebrow">Ceuta</p>
            <h1 class="fp-h1">Campos deportivos de Ceuta</h1>
        </div>
        <?php if (is_admin()): ?>
            <a href="<?= url('campos/create') ?>" class="fp-btn fp-btn-primary"><i class="bi bi-plus-lg"></i><span>Nuevo campo</span></a>
        <?php endif; ?>
    </div>

    <?php if (empty($fields)): ?>
        <?php $this->partial('empty-state', ['icon' => 'bi-geo-alt', 'title' => 'Sin campos disponibles', 'description' => 'Todavia no hay campos de Ceuta registrados.']); ?>
    <?php else: ?>
        <section class="fp-fields-layout">
            <div
                id="ceuta-map"
                class="fp-fields-map ceuta-map"
                data-map-provider="<?= !empty($googleMapsEnabled) ? 'google' : 'leaflet' ?>"
                data-fields='<?= e(json_encode($fields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>'
            ></div>
            <aside class="fp-fields-list">
                <?php foreach ($fields as $f): ?>
                    <?php
                        $desc = $f['description'] ?? '';
                        $isPublic = $desc !== '' && (str_contains($desc, 'Barriada') || str_contains($desc, 'Pública'));
                    ?>
                    <article class="fp-glass fp-field-card" data-field-card="<?= (int) $f['id'] ?>">
                        <div class="fp-field-img" style="background-image:url('<?= e(!empty($f['image']) ? asset($f['image']) : asset('images/hero-pitch.png')) ?>')"></div>
                        <div class="fp-field-card-body">
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                <?php if ($desc !== ''): ?>
                                    <span class="fp-field-tag<?= $isPublic ? ' fp-field-tag--public' : '' ?>"><?= e($desc) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($f['hourly_rate'])): ?>
                                    <span class="fp-field-rate"><?= number_format((float)$f['hourly_rate'], 0) ?>€/h</span>
                                <?php endif; ?>
                            </div>
                            <h3><?= e($f['name']) ?></h3>
                            <p><i class="bi bi-geo-alt"></i> <?= e($f['address'] ?? $f['city']) ?></p>
                            <div class="fp-field-meta">
                                <span><i class="bi bi-people"></i> <?= (int) $f['capacity'] ?> jugadores</span>
                                <span><i class="bi bi-circle-square"></i> <?= e($f['surface']) ?></span>
                            </div>
                            <div class="fp-actions-row">
                                <a href="<?= url('campos/show/' . (int) $f['id']) ?>" class="fp-btn fp-btn-primary">Detalles</a>
                                <?php if (!empty($f['maps_url'])): ?>
                                    <a href="<?= e($f['maps_url']) ?>" class="fp-btn fp-btn-ghost" target="_blank" rel="noopener"><i class="bi bi-map"></i> Maps</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </aside>
        </section>
    <?php endif; ?>
</main>
