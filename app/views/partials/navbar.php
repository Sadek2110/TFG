<?php
$user = current_user();
$activePage = $active ?? '';
$isLanding = $activePage === 'home';
$primaryLinks = [
    ['ids' => ['home', 'dashboard'], 'label' => 'Inicio', 'url' => $user ? 'dashboard' : '', 'icon' => 'bi-house-door'],
    ['ids' => ['teams'], 'label' => 'Equipos', 'url' => 'teams', 'icon' => 'bi-shield'],
    ['ids' => ['matches'], 'label' => 'Partidos', 'url' => 'matches', 'icon' => 'bi-broadcast'],
    ['ids' => ['leagues'], 'label' => 'Ligas', 'url' => 'leagues', 'icon' => 'bi-trophy'],
    ['ids' => ['campos'], 'label' => 'Campos', 'url' => 'campos', 'icon' => 'bi-geo-alt'],
];
$legacyLinks = [
    ['id' => 'teams', 'label' => 'Equipos', 'url' => 'teams', 'icon' => 'bi-shield'],
    ['id' => 'matches', 'label' => 'Partidos', 'url' => 'matches', 'icon' => 'bi-calendar2-week'],
    ['id' => 'leagues', 'label' => 'Ligas', 'url' => 'leagues', 'icon' => 'bi-trophy'],
    ['id' => 'campos', 'label' => 'Campos', 'url' => 'campos', 'icon' => 'bi-geo-alt'],
];
$legacyIconColors = [
    'teams'   => '#4ade80',
    'matches' => '#f59e0b',
    'leagues' => '#a855f7',
    'campos'  => '#f472b6',
];
$unread = 0;
if ($user) {
    try { $unread = (int) Database::value('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0', [(int) $user['id']]); } catch (Throwable $e) { $unread = 0; }
}
?>
<nav class="<?= $isLanding ? 'fp-sidebar' : 'fp-sidebar fp-floating-nav' ?>" id="fpSidebar" aria-label="Navegacion principal">
    <button class="fp-sidebar-toggle" type="button" data-nav-toggle aria-expanded="false" aria-label="Abrir menú">
        <i class="bi bi-list"></i>
    </button>

    <div class="fp-sidebar-logo">
        <a href="<?= $user ? url('dashboard') : url('') ?>">
            <img src="<?= asset('images/logo.png') ?>" alt="" class="fp-sidebar-logo-img">
            <img src="<?= asset('images/logo-nombre.png') ?>" alt="FastPlay" class="fp-sidebar-logo-word">
        </a>
    </div>

    <div class="fp-sidebar-nav" data-nav-menu>
        <?php if ($isLanding): ?>
            <?php if ($user): ?>
                <a href="<?= url('dashboard') ?>" class="fp-sidebar-link <?= $activePage === 'dashboard' ? 'active' : '' ?>" title="Inicio">
                    <i class="bi bi-house-door" style="color:#60a5fa;"></i>
                    <span>Inicio</span>
                </a>
                <?php foreach ($legacyLinks as $l): ?>
                    <a href="<?= url($l['url']) ?>" class="fp-sidebar-link <?= $activePage === $l['id'] ? 'active' : '' ?>" title="<?= e($l['label']) ?>">
                        <i class="bi <?= e($l['icon']) ?>" style="color:<?= $legacyIconColors[$l['id']] ?? 'var(--fp-fg)' ?>;"></i>
                        <span><?= e($l['label']) ?></span>
                    </a>
                <?php endforeach; ?>
                <?php if (is_admin()): ?>
                    <a href="<?= url('admin') ?>" class="fp-sidebar-link <?= $activePage === 'admin' ? 'active' : '' ?>" title="Admin">
                        <i class="bi bi-sliders" style="color:#fb923c;"></i>
                        <span>Admin</span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <?php foreach ($primaryLinks as $link): ?>
                <a href="<?= url($link['url']) ?>" class="fp-sidebar-link <?= in_array($activePage, $link['ids'], true) ? 'active' : '' ?>" title="<?= e($link['label']) ?>">
                    <i class="bi <?= e($link['icon']) ?>"></i>
                    <span><?= e($link['label']) ?></span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="fp-sidebar-bottom">
        <?php if ($user): ?>
            <button class="fp-sidebar-link" type="button" data-theme-toggle aria-label="Cambiar tema" title="Cambiar tema">
                <i class="bi bi-moon"></i>
                <span>Tema</span>
            </button>
            <a href="<?= url('notification') ?>" class="fp-sidebar-link <?= $activePage === 'notification' ? 'active' : '' ?>" title="Notificaciones">
                <i class="bi bi-bell"></i>
                <span>Avisos</span>
                <span class="fp-badge" data-notification-badge <?= $unread > 0 ? '' : 'hidden' ?>><?= (int) $unread ?></span>
            </a>
            <a href="<?= url('profile/edit') ?>" class="fp-sidebar-link fp-sidebar-user" title="Mi perfil" aria-label="Mi perfil">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="<?= asset($user['avatar']) ?>" alt="" class="fp-sidebar-avatar">
                <?php else: ?>
                    <span class="fp-sidebar-avatar fp-sidebar-avatar--initial"><?= e(mb_substr($user['name'], 0, 1)) ?></span>
                <?php endif; ?>
            </a>
            <form method="post" action="<?= url('auth/logout') ?>" class="fp-logout-form">
                <?= csrf_field() ?>
                <button type="submit" class="fp-sidebar-link fp-sidebar-link--danger" title="Salir">
                    <i class="bi bi-box-arrow-left"></i>
                    <span>Salir</span>
                </button>
            </form>
            <?php if (!$isLanding && is_admin()): ?>
                <a href="<?= url('admin') ?>" class="fp-sidebar-link <?= $activePage === 'admin' ? 'active' : '' ?>" title="Admin">
                    <i class="bi bi-sliders"></i>
                    <span>Admin</span>
                </a>
            <?php endif; ?>
        <?php else: ?>
            <a href="<?= url('auth/login') ?>" class="fp-sidebar-link" title="Entrar">
                <i class="bi bi-box-arrow-in-right"></i>
                <span>Entrar</span>
            </a>
            <a href="<?= url('auth/register') ?>" class="fp-btn fp-btn-primary fp-sidebar-cta">Registrarse</a>
        <?php endif; ?>
    </div>
</nav>
