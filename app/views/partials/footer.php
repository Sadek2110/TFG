<footer class="mt-auto border-t border-white/5" style="background:#040a06; animation: fadeUp .7s .15s cubic-bezier(.22,1,.36,1) both;">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
            <!-- Brand -->
            <div class="md:col-span-1">
                <div class="flex items-center gap-2 font-black text-xl mb-4">
                    <span class="text-2xl">⚽</span>
                    <span>Fast<span class="text-green-400">Play</span></span>
                </div>
                <p class="text-gray-500 text-sm leading-relaxed mb-5">
                    Fútbol amateur organizado para todos, en cualquier lugar.
                </p>
                <div class="flex items-center gap-3">
                    <a href="#" class="w-9 h-9 glass rounded-full flex items-center justify-center text-gray-400 hover:text-white hover:border-green-500/40 hover:scale-110 hover:shadow-[0_0_12px_rgba(22,163,74,.3)] transition-all duration-200 text-sm">𝕏</a>
                    <a href="#" class="w-9 h-9 glass rounded-full flex items-center justify-center text-gray-400 hover:text-white hover:border-green-500/40 hover:scale-110 hover:shadow-[0_0_12px_rgba(22,163,74,.3)] transition-all duration-200 text-sm">in</a>
                    <a href="#" class="w-9 h-9 glass rounded-full flex items-center justify-center text-gray-400 hover:text-white hover:border-green-500/40 hover:scale-110 hover:shadow-[0_0_12px_rgba(22,163,74,.3)] transition-all duration-200 text-sm">ig</a>
                </div>
            </div>

            <!-- Plataforma -->
            <div>
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Plataforma</h4>
                <ul class="space-y-3">
                    <li><a href="<?= APP_URL ?>/teams"   class="text-sm text-gray-400 hover:text-white transition-colors">Equipos</a></li>
                    <li><a href="<?= APP_URL ?>/matches" class="text-sm text-gray-400 hover:text-white transition-colors">Partidos</a></li>
                    <li><a href="<?= APP_URL ?>/leagues" class="text-sm text-gray-400 hover:text-white transition-colors">Ligas</a></li>
                    <li><a href="<?= APP_URL ?>/chat"    class="text-sm text-gray-400 hover:text-white transition-colors">Chat</a></li>
                </ul>
            </div>

            <!-- Cuenta -->
            <div>
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Cuenta</h4>
                <ul class="space-y-3">
                    <li><a href="<?= APP_URL ?>/register" class="text-sm text-gray-400 hover:text-white transition-colors">Registrarse</a></li>
                    <li><a href="<?= APP_URL ?>/login"    class="text-sm text-gray-400 hover:text-white transition-colors">Iniciar sesión</a></li>
                    <?php if (!empty($_SESSION['user_id'])): ?>
                    <li><a href="<?= APP_URL ?>/dashboard" class="text-sm text-gray-400 hover:text-white transition-colors">Dashboard</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Legal -->
            <div>
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Legal</h4>
                <ul class="space-y-3">
                    <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Términos de uso</a></li>
                    <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Privacidad (GDPR)</a></li>
                    <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Cookies</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="border-t border-white/5 py-5">
        <p class="text-center text-gray-600 text-xs">
            &copy; <?= date('Y') ?> FastPlay — Todos los derechos reservados.
        </p>
    </div>
</footer>
</body>
</html>
