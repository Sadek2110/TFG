<?php $pageTitle = 'Iniciar sesión'; ?>
<?php require_once APP_PATH . '/views/partials/header.php'; ?>

<div class="min-h-screen flex items-center justify-center px-4 pt-16 relative">
    <!-- Background glow -->
    <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 pointer-events-none"
         style="background:radial-gradient(ellipse,rgba(22,163,74,.1) 0%,transparent 70%);"></div>

    <div class="w-full max-w-md relative">
        <!-- Logo -->
        <div class="text-center mb-8 fade-up">
            <a href="<?= APP_URL ?>/" class="inline-flex items-center gap-2 font-black text-2xl">
                <span class="text-3xl">⚽</span>
                Fast<span class="text-green-400">Play</span>
            </a>
            <p class="text-gray-500 mt-2 text-sm">Bienvenido de vuelta</p>
        </div>

        <div class="glass rounded-3xl p-8 fade-up-1">
            <h1 class="text-2xl font-black mb-7">Iniciar sesión</h1>

            <form method="POST" action="<?= APP_URL ?>/login" class="space-y-5" novalidate id="loginForm" data-loading-form>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Email o teléfono</label>
                    <input type="text" name="credential" required autocomplete="username"
                           class="input-dark" placeholder="tu@email.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Contraseña</label>
                    <div class="relative">
                        <input type="password" name="password" id="pw" required autocomplete="current-password"
                               class="input-dark pr-12" placeholder="••••••••">
                        <button type="button" onclick="togglePw()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors text-sm">
                            👁
                        </button>
                    </div>
                </div>
                <button type="submit" data-submit-btn data-loading-text="Entrando…" class="btn-primary w-full justify-center py-3.5 text-base glow-green mt-2">
                    <span data-btn-text>Entrar →</span>
                    <svg data-spinner class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </button>
            </form>

            <div class="relative my-7">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-white/10"></div></div>
                <div class="relative text-center"><span class="bg-[#0d1810] px-4 text-xs text-gray-600">o continúa con</span></div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <button class="btn-ghost py-3 text-sm justify-center opacity-50 cursor-not-allowed" disabled>Google (pronto)</button>
                <button class="btn-ghost py-3 text-sm justify-center opacity-50 cursor-not-allowed" disabled>GitHub (pronto)</button>
            </div>
        </div>

        <p class="text-center text-sm text-gray-500 mt-6 fade-up-2">
            ¿No tienes cuenta?
            <a href="<?= APP_URL ?>/register" class="text-green-400 font-semibold hover:text-green-300 transition-colors">Regístrate gratis</a>
        </p>
    </div>
</div>
<script>function togglePw(){const i=document.getElementById('pw');i.type=i.type==='password'?'text':'password';}</script>
</body></html>
