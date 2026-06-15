<?php $mensajes = Sesion::consumirFlash(); ?>
<?php if (!empty($mensajes)): ?>
    <div class="mensajes" role="status" aria-live="polite">
        <?php foreach ($mensajes as $mensaje): ?>
            <p class="mensaje mensaje--<?= e($mensaje['tipo']) ?>">
                <?= e($mensaje['mensaje']) ?>
            </p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
