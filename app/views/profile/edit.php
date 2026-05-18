<main class="fp-fade fp-page" style="max-width:680px;">
    <p class="fp-eyebrow">Editar</p>
    <h1 class="fp-h1">Mi perfil</h1>

    <div class="fp-glass" style="border-radius:18px;padding:28px;margin-top:24px;">
        <form method="post" action="<?= url('profile/edit') ?>" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:18px;">
            <?= csrf_field() ?>
            <div style="display:flex;align-items:center;gap:18px;">
                <div style="width:84px;height:84px;border-radius:50%;overflow:hidden;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                    <img src="<?= asset(!empty($profile['avatar']) ? $profile['avatar'] : 'images/default-avatar.svg') ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                </div>
                <label style="flex:1;">
                    <span class="fp-label">Foto de perfil</span>
                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="fp-input" style="padding:8px;">
                    <small style="color:#6b7280;font-size:11px;">JPG, PNG o WEBP. Máximo 2 MB.</small>
                    <?php if (!empty($errors['avatar'])): ?><small class="fp-err"><?= e($errors['avatar']) ?></small><?php endif; ?>
                </label>
            </div>
            <label>
                <span class="fp-label">Nombre</span>
                <input name="name" class="fp-input" value="<?= old('name', $profile['name']) ?>" required minlength="2">
                <?php if (!empty($errors['name'])): ?><small class="fp-err"><?= e($errors['name']) ?></small><?php endif; ?>
            </label>
            <div class="fp-grid-2" style="gap:14px;">
                <label>
                    <span class="fp-label">Edad</span>
                    <input type="number" name="age" class="fp-input" value="<?= old('age', $profile['age'] ?? '') ?>" min="14" max="99">
                    <?php if (!empty($errors['age'])): ?><small class="fp-err"><?= e($errors['age']) ?></small><?php endif; ?>
                </label>
                <label>
                    <span class="fp-label">Teléfono</span>
                    <input name="phone" class="fp-input" value="<?= old('phone', $profile['phone'] ?? '') ?>" placeholder="+34 600 000 000">
                    <?php if (!empty($errors['phone'])): ?><small class="fp-err"><?= e($errors['phone']) ?></small><?php endif; ?>
                </label>
            </div>
            <div class="fp-grid-2" style="gap:14px;">
                <label>
                    <span class="fp-label">Ciudad</span>
                    <input name="city" class="fp-input" value="<?= old('city', $profile['city'] ?? '') ?>">
                </label>
                <label>
                    <span class="fp-label">Posición</span>
                    <select name="position" class="fp-input">
                        <?php $cur = old('position', $profile['position'] ?? ''); foreach (['','Portero','Portera','Defensa','Mediocampo','Delantero'] as $p): ?>
                            <option value="<?= e($p) ?>" <?= $cur === $p ? 'selected' : '' ?>><?= e($p === '' ? '— sin definir —' : $p) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['position'])): ?><small class="fp-err"><?= e($errors['position']) ?></small><?php endif; ?>
                </label>
            </div>
            <div class="fp-grid-2" style="gap:14px;">
                <label>
                    <span class="fp-label">Dorsal</span>
                    <input type="number" name="dorsal" class="fp-input" min="1" max="99" value="<?= old('dorsal', $profile['dorsal'] ?? '') ?>" placeholder="1-99">
                    <?php if (!empty($errors['dorsal'])): ?><small class="fp-err"><?= e($errors['dorsal']) ?></small><?php endif; ?>
                </label>
                <label>
                    <span class="fp-label">Altura (cm)</span>
                    <input type="number" name="height_cm" class="fp-input" min="140" max="220" value="<?= old('height_cm', $profile['height_cm'] ?? '') ?>" placeholder="140-220">
                    <?php if (!empty($errors['height_cm'])): ?><small class="fp-err"><?= e($errors['height_cm']) ?></small><?php endif; ?>
                </label>
            </div>
            <div class="fp-grid-2" style="gap:14px;">
                <label>
                    <span class="fp-label">Goles</span>
                    <input type="number" name="goals" class="fp-input" min="0" max="999" value="<?= old('goals', $profile['goals'] ?? 0) ?>">
                    <?php if (!empty($errors['goals'])): ?><small class="fp-err"><?= e($errors['goals']) ?></small><?php endif; ?>
                </label>
                <label>
                    <span class="fp-label">Asistencias</span>
                    <input type="number" name="assists" class="fp-input" min="0" max="999" value="<?= old('assists', $profile['assists'] ?? 0) ?>">
                    <?php if (!empty($errors['assists'])): ?><small class="fp-err"><?= e($errors['assists']) ?></small><?php endif; ?>
                </label>
            </div>
            <div style="display:flex;gap:10px;">
                <button class="fp-btn fp-btn-primary fp-btn-glow">Guardar cambios →</button>
                <a class="fp-btn fp-btn-ghost" href="<?= url('profile') ?>">Cancelar</a>
            </div>
        </form>
    </div>
</main>