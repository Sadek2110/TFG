<?php
$links = [
    ['id' => 'teams', 'label' => 'Equipos', 'url' => 'teams', 'icon' => 'bi-shield'],
    ['id' => 'matches', 'label' => 'Partidos', 'url' => 'matches', 'icon' => 'bi-calendar2-week'],
    ['id' => 'leagues', 'label' => 'Ligas', 'url' => 'leagues', 'icon' => 'bi-trophy'],
    ['id' => 'campos', 'label' => 'Campos', 'url' => 'campos', 'icon' => 'bi-geo-alt'],
];
$user = current_user();
$unread = 0;
if ($user) {
    try { $unread = (int) Database::value('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0', [(int) $user['id']]); } catch (Throwable $e) { $unread = 0; }
}
// Las páginas internas (equipos, partidos, ligas, campos) y el selector de tema
// solo aparecen cuando el usuario está autenticado Y no estamos en el landing.
// En el landing público (active === 'home') la navbar queda minimalista: logo +
// botones de entrar / registrarse.
$onLanding = (($active ?? '') === 'home');
$showInternalLinks = (bool) $user;
?>
<nav class="fp-navbar">
    <div class="fp-navbar-inner">
        <a href="<?= $user ? url('dashboard') : url('') ?>" class="fp-logo">
            <img src="<?= asset('images/logo.png') ?>" alt="" class="fp-logo-icon">
            <img src="<?= asset('images/logo-nombre.png') ?>" alt="FastPlay" class="fp-logo-word">
        </a>

        <button class="fp-nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-label="Abrir menu">
            <i class="bi bi-list"></i>
        </button>

        <div class="fp-nav-menu" data-nav-menu>
            <div class="fp-nav-links">
                <?php if ($showInternalLinks): ?>
                    <?php foreach ($links as $l): ?>
                        <a href="<?= url($l['url']) ?>" class="fp-nav-link <?= ($active ?? '') === $l['id'] ? 'active' : '' ?>">
                            <i class="bi <?= e($l['icon']) ?>"></i><span><?= e($l['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                    <a href="<?= url('dashboard') ?>" class="fp-nav-link <?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>"><i class="bi bi-house-door"></i><span>Inicio</span></a>
                    <?php if (is_admin()): ?>
                        <a href="<?= url('admin') ?>" class="fp-nav-link <?= ($active ?? '') === 'admin' ? 'active' : '' ?>"><i class="bi bi-sliders"></i><span>Admin</span></a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="fp-nav-actions">
                <?php if ($showInternalLinks): ?>
                    <button class="fp-icon-btn" type="button" data-theme-toggle aria-label="Cambiar tema"><i class="bi bi-moon"></i></button>
                <?php endif; ?>
                <?php if ($user): ?>
                    <a href="<?= url('notification') ?>" class="fp-icon-btn fp-notification-link <?= ($active ?? '') === 'notification' ? 'active' : '' ?>" aria-label="Notificaciones">
                        <i class="bi bi-bell"></i>
                        <span class="fp-badge" data-notification-badge <?= $unread > 0 ? '' : 'hidden' ?>><?= (int) $unread ?></span>
                    </a>
                    <a href="<?= url('profile/edit') ?>" class="fp-user-pill">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="<?= asset($user['avatar']) ?>" alt="">
                        <?php else: ?>
                            <span><?= e(mb_substr($user['name'], 0, 1)) ?></span>
                        <?php endif; ?>
                        <strong><?= e($user['name']) ?></strong>
                    </a>
                    <form method="post" action="<?= url('auth/logout') ?>" class="fp-logout-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="fp-logout">Salir</button>
                    </form>
                <?php else: ?>
                    <a href="<?= url('auth/login') ?>" class="fp-login">Entrar</a>
                    <a href="<?= url('auth/register') ?>" class="fp-btn fp-btn-primary">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
