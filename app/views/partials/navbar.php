<?php
$links = [
    ['id' => 'teams',   'label' => 'Equipos',  'url' => 'teams'],
    ['id' => 'matches', 'label' => 'Partidos', 'url' => 'matches'],
    ['id' => 'leagues', 'label' => 'Ligas',    'url' => 'leagues'],
    ['id' => 'campos',  'label' => 'Campos',   'url' => 'campos'],
];
$user = current_user();
?>
<nav class="fp-navbar">
    <div class="fp-navbar-inner">
        <a href="<?= url('') ?>" class="fp-logo">
            <img src="<?= asset('images/logo.png') ?>" alt="" class="fp-logo-icon">
            <img src="<?= asset('images/logo_palabra.png') ?>" alt="FastPlay" class="fp-logo-word">
        </a>
        <div class="fp-nav-links">
            <?php foreach ($links as $l): ?>
                <a href="<?= url($l['url']) ?>" class="fp-nav-link <?= ($active ?? '') === $l['id'] ? 'active' : '' ?>"><?= e($l['label']) ?></a>
            <?php endforeach; ?>
            <?php if ($user): ?>
                <a href="<?= url('chat') ?>" class="fp-nav-link <?= ($active ?? '') === 'chat' ? 'active' : '' ?>">Chat</a>
                <?php if (is_admin()): ?>
                    <a href="<?= url('admin') ?>" class="fp-nav-link <?= ($active ?? '') === 'admin' ? 'active' : '' ?>">Admin</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <?php if ($user): ?>
                <a href="<?= url('dashboard') ?>" class="fp-glass" style="border-radius:9999px;padding:5px 14px 5px 6px;display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:500;text-decoration:none;color:#fff;">
                    <span style="width:26px;height:26px;border-radius:9999px;background:#16a34a;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;"><?= e(mb_substr($user['name'], 0, 1)) ?></span>
                    <?= e($user['name']) ?>
                </a>
                <form method="post" action="<?= url('auth/logout') ?>" style="margin:0;">
                    <?= csrf_field() ?>
                    <button type="submit" style="background:none;border:0;color:#9ca3af;font-size:13px;cursor:pointer;padding:0;font-family:inherit;">Salir</button>
                </form>
            <?php else: ?>
                <a href="<?= url('auth/login') ?>" style="color:#9ca3af;font-size:14px;font-weight:500;text-decoration:none;">Entrar</a>
                <a href="<?= url('auth/register') ?>" style="background:#16a34a;color:#fff;font-weight:700;padding:10px 20px;border-radius:9999px;font-size:13px;text-decoration:none;">Registrarse</a>
            <?php endif; ?>
        </div>
    </div>
</nav>