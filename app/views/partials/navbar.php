<?php
$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = parse_url(APP_URL, PHP_URL_PATH);
$path = '/' . ltrim(substr($uri, strlen($base)), '/');
function isActive(string $route, string $path): string {
    return str_starts_with($path, $route) ? 'active' : '';
}
?>
<nav class="fixed top-0 left-0 right-0 z-50 h-16 flex items-center"
     style="background:rgba(6,13,9,0.85);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-bottom:1px solid rgba(255,255,255,0.06);">
    <div class="max-w-7xl mx-auto w-full px-6 flex items-center justify-between">

        <!-- Logo -->
        <a href="<?= APP_URL ?>/" class="flex items-center gap-2 font-black text-xl tracking-tight select-none">
            <span class="text-2xl leading-none">⚽</span>
            <span>Fast<span class="text-green-400">Play</span></span>
        </a>

        <!-- Desktop nav -->
        <div class="hidden md:flex items-center gap-7">
            <a href="<?= APP_URL ?>/teams"   class="nav-link <?= isActive('/teams',   $path) ?>">Equipos</a>
            <a href="<?= APP_URL ?>/matches" class="nav-link <?= isActive('/matches', $path) ?>">Partidos</a>
            <a href="<?= APP_URL ?>/leagues" class="nav-link <?= isActive('/leagues', $path) ?>">Ligas</a>
            <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="<?= APP_URL ?>/chat"    class="nav-link <?= isActive('/chat',    $path) ?>">Chat</a>
            <?php endif; ?>
            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
            <a href="<?= APP_URL ?>/admin"   class="nav-link <?= isActive('/admin',   $path) ?> text-yellow-400">Admin</a>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            <?php if (!empty($_SESSION['user_id'])): ?>
            <!-- User menu -->
            <div class="relative" id="userMenu">
                <button onclick="toggleUserMenu()"
                        class="flex items-center gap-2 glass rounded-full pl-3 pr-4 py-1.5 hover:bg-white/10 transition-colors text-sm font-medium">
                    <img src="<?= APP_URL ?>/public/images/uploads/profiles/<?= htmlspecialchars($_SESSION['user_photo'] ?? 'default.png') ?>"
                         onerror="this.src='<?= APP_URL ?>/public/images/default-avatar.svg'"
                         class="w-7 h-7 rounded-full object-cover" alt="Avatar">
                    <span class="hidden sm:block"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></span>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="userDropdown" class="dropdown-panel absolute right-0 top-full mt-2 w-48 glass rounded-2xl overflow-hidden shadow-2xl py-1">
                    <a href="<?= APP_URL ?>/dashboard" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-300 hover:text-white hover:bg-white/10 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>
                        Dashboard
                    </a>
                    <a href="<?= APP_URL ?>/profile" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-300 hover:text-white hover:bg-white/10 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                        Mi perfil
                    </a>
                    <hr class="border-white/10 my-1">
                    <form method="POST" action="<?= APP_URL ?>/logout" class="inline w-full">
                        <button type="submit" class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-colors w-full">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/></svg>
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <a href="<?= APP_URL ?>/login" class="text-sm text-gray-400 hover:text-white font-medium px-4 py-2 transition-colors hidden sm:block">Entrar</a>
            <a href="<?= APP_URL ?>/register"
               class="text-sm bg-green-600 hover:bg-green-500 text-white font-bold px-5 py-2.5 rounded-full transition-all duration-200 hover:shadow-[0_0_20px_rgba(22,163,74,.4)]">
                Registrarse
            </a>
            <?php endif; ?>

            <!-- Hamburger -->
            <button id="hamburger" onclick="toggleNav()"
                    class="md:hidden flex flex-col gap-1.5 p-2 rounded-lg hover:bg-white/10 transition-colors" aria-label="Menú">
                <span class="block w-5 h-0.5 bg-white transition-transform" id="ham1"></span>
                <span class="block w-5 h-0.5 bg-white transition-opacity"   id="ham2"></span>
                <span class="block w-5 h-0.5 bg-white transition-transform" id="ham3"></span>
            </button>
        </div>
    </div>

    <!-- Mobile menu -->
    <div id="mobileMenu" class="absolute top-16 left-0 right-0 glass border-t border-white/10">
        <a href="<?= APP_URL ?>/teams"   class="nav-link py-2 border-b border-white/5">Equipos</a>
        <a href="<?= APP_URL ?>/matches" class="nav-link py-2 border-b border-white/5">Partidos</a>
        <a href="<?= APP_URL ?>/leagues" class="nav-link py-2 border-b border-white/5">Ligas</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
        <a href="<?= APP_URL ?>/chat"      class="nav-link py-2 border-b border-white/5">Chat</a>
        <a href="<?= APP_URL ?>/dashboard" class="nav-link py-2 border-b border-white/5">Dashboard</a>
        <form method="POST" action="<?= APP_URL ?>/logout" class="w-full">
            <button type="submit" class="text-red-400 py-2 text-sm font-medium hover:text-red-300 transition-colors w-full text-left">Cerrar sesión</button>
        </form>
        <?php else: ?>
        <a href="<?= APP_URL ?>/login"    class="nav-link py-2">Entrar</a>
        <a href="<?= APP_URL ?>/register" class="btn-primary text-center mt-2">Registrarse gratis</a>
        <?php endif; ?>
    </div>
</nav>
<script>
function toggleNav(){
    const m  = document.getElementById('mobileMenu');
    const h1 = document.getElementById('ham1');
    const h2 = document.getElementById('ham2');
    const h3 = document.getElementById('ham3');
    const open = m.classList.toggle('open');
    h1.style.transform = open ? 'translateY(8px) rotate(45deg)'   : '';
    h2.style.opacity   = open ? '0' : '1';
    h3.style.transform = open ? 'translateY(-8px) rotate(-45deg)' : '';
}
function toggleUserMenu(){
    document.getElementById('userDropdown').classList.toggle('open');
}
document.addEventListener('click', e => {
    const m = document.getElementById('userMenu');
    if(m && !m.contains(e.target)) document.getElementById('userDropdown')?.classList.remove('open');
});
// Scroll-aware navbar: deepen background only on state change
(function(){
    const nav = document.querySelector('nav');
    if (!nav) return;
    let solid = false;
    window.addEventListener('scroll', () => {
        const isSolid = window.scrollY > 24;
        if (isSolid === solid) return;
        solid = isSolid;
        nav.style.background = solid ? 'rgba(6,13,9,0.97)' : 'rgba(6,13,9,0.85)';
        nav.style.boxShadow  = solid ? '0 1px 24px rgba(0,0,0,.4)' : 'none';
    }, { passive: true });
})();
</script>
