<main class="fp-fade fp-page fp-small-container">
    <p class="fp-eyebrow">Admin · Campos</p>
    <h1 class="fp-h1">Registrar campo</h1>

    <div class="fp-glass fp-panel">
        <form method="post" action="<?= url('campos/create') ?>" class="fp-profile-form">
            <?= csrf_field() ?>
            <label>
                <span class="fp-label">Nombre</span>
                <input name="name" class="fp-input" value="<?= old('name') ?>" required>
                <?php if (!empty($errors['name'])): ?><small class="fp-err"><?= e($errors['name']) ?></small><?php endif; ?>
            </label>
            <div class="fp-grid-2">
                <label>
                    <span class="fp-label">Ciudad</span>
                    <input name="city" class="fp-input" value="<?= old('city') ?>" required>
                    <?php if (!empty($errors['city'])): ?><small class="fp-err"><?= e($errors['city']) ?></small><?php endif; ?>
                </label>
                <label>
                    <span class="fp-label">Capacidad</span>
                    <input type="number" name="capacity" class="fp-input" value="<?= old('capacity', 22) ?>" min="4" max="50" required>
                    <?php if (!empty($errors['capacity'])): ?><small class="fp-err"><?= e($errors['capacity']) ?></small><?php endif; ?>
                </label>
            </div>
            <label>
                <span class="fp-label">Dirección</span>
                <input name="address" class="fp-input" value="<?= old('address') ?>">
                <?php if (!empty($errors['address'])): ?><small class="fp-err"><?= e($errors['address']) ?></small><?php endif; ?>
            </label>
            <div class="fp-grid-2">
                <label>
                    <span class="fp-label">Superficie</span>
                    <select name="surface" class="fp-input">
                        <?php $cur = old('surface', 'césped'); foreach (['césped','sintético','tierra','cemento'] as $s): ?>
                            <option value="<?= e($s) ?>" <?= $cur === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['surface'])): ?><small class="fp-err"><?= e($errors['surface']) ?></small><?php endif; ?>
                </label>
                <label>
                    <span class="fp-label">Tarifa €/hora</span>
                    <input type="number" step="0.01" name="hourly_rate" class="fp-input" value="<?= old('hourly_rate', 25) ?>">
                    <?php if (!empty($errors['hourly_rate'])): ?><small class="fp-err"><?= e($errors['hourly_rate']) ?></small><?php endif; ?>
                </label>
            </div>
            <button class="fp-btn fp-btn-primary fp-btn-glow">Crear campo →</button>
        </form>
    </div>
</main>
