<?php
$cols = [
    ['h' => 'Plataforma', 'items' => [
        ['l' => 'Equipos',  'u' => 'teams'],
        ['l' => 'Partidos', 'u' => 'matches'],
        ['l' => 'Ligas',    'u' => 'leagues'],
        ['l' => 'Campos',   'u' => 'campos'],
        ['l' => 'Chat',     'u' => 'chat'],
    ]],
    ['h' => 'Cuenta', 'items' => [
        ['l' => 'Registrarse',    'u' => 'auth/register'],
        ['l' => 'Iniciar sesión', 'u' => 'auth/login'],
        ['l' => 'Mi panel',       'u' => 'dashboard'],
        ['l' => 'Mi perfil',      'u' => 'profile'],
    ]],
    ['h' => 'Legal', 'items' => [
        ['l' => 'Términos de uso',     'u' => 'legal/terms'],
        ['l' => 'Privacidad (GDPR)',   'u' => 'legal/privacy'],
        ['l' => 'Cookies',             'u' => 'legal/cookies'],
    ]],
];
?>
<footer class="fp-footer">
    <div class="fp-footer-inner">
        <div>
            <a href="<?= url('') ?>" class="fp-logo" style="margin-bottom:16px;">
                <img src="<?= asset('images/logo.png') ?>" alt="FastPlay">
            </a>
            <p style="color:#6b7280;font-size:13px;line-height:1.55;margin:16px 0 18px;">Fútbol amateur organizado para todos, en cualquier lugar.</p>
            <div style="display:flex;gap:10px;" aria-label="Redes sociales (próximamente)">
                <?php foreach (['𝕏', 'in', 'ig'] as $s): ?>
                    <span aria-disabled="true" title="Próximamente" style="width:34px;height:34px;border-radius:9999px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);display:inline-flex;align-items:center;justify-content:center;color:#9ca3af;font-size:13px;"><?= e($s) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php foreach ($cols as $col): ?>
            <div>
                <h4 style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.22em;margin-bottom:16px;"><?= e($col['h']) ?></h4>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:12px;">
                    <?php foreach ($col['items'] as $item): ?>
                        <li><a href="<?= url($item['u']) ?>" style="font-size:13px;color:#9ca3af;text-decoration:none;"><?= e($item['l']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="fp-footer-bottom">© <?= date('Y') ?> FastPlay — Todos los derechos reservados.</div>
</footer>