<?php $pageTitle = 'Registrarse'; ?>
<?php require_once APP_PATH . '/views/partials/header.php'; ?>

<div class="min-h-screen flex items-center justify-center px-4 py-24 relative">
    <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 pointer-events-none"
         style="background:radial-gradient(ellipse,rgba(22,163,74,.1) 0%,transparent 70%);"></div>

    <div class="w-full max-w-lg relative">
        <div class="text-center mb-8 fade-up">
            <a href="<?= APP_URL ?>/" class="inline-flex items-center gap-2 font-black text-2xl">
                <span class="text-3xl">⚽</span>
                Fast<span class="text-green-400">Play</span>
            </a>
            <p class="text-gray-500 mt-2 text-sm">Crea tu perfil de jugador</p>
        </div>

        <div class="glass rounded-3xl p-8 fade-up-1">
            <h1 class="text-2xl font-black mb-7">Crear cuenta</h1>

            <form method="POST" action="<?= APP_URL ?>/register" class="space-y-4" novalidate id="registerForm" data-loading-form>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Nombre completo</label>
                        <input type="text" name="name" required autocomplete="name"
                               class="input-dark" placeholder="Tu nombre">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Edad</label>
                        <input type="number" name="age" min="16" max="60"
                               class="input-dark" placeholder="25">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                    <input type="email" name="email" required autocomplete="email"
                           class="input-dark" placeholder="tu@email.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Teléfono <span class="text-gray-600">(opcional)</span></label>
                    <input type="tel" name="phone" autocomplete="tel"
                           class="input-dark" placeholder="+34 600 000 000">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Posición</label>
                        <select name="position" class="input-dark">
                            <option value="">Selecciona…</option>
                            <?php foreach(['Portero','Defensa','Centrocampista','Delantero'] as $p): ?>
                            <option value="<?= strtolower($p) ?>"><?= $p ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Ciudad</label>
                        <input type="text" name="city" class="input-dark" placeholder="Madrid">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Contraseña</label>
                    <div class="relative">
                        <input type="password" name="password" id="pw1" required autocomplete="new-password"
                               class="input-dark pr-12" placeholder="Mínimo 8 caracteres">
                        <button type="button" onclick="togglePw('pw1')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors text-sm">👁</button>
                    </div>
                    <!-- Strength bar -->
                    <div class="mt-2 h-1 rounded-full bg-white/10 overflow-hidden">
                        <div id="strengthBar" class="h-full rounded-full transition-all duration-300" style="width:0%;background:#16a34a;"></div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Confirmar contraseña</label>
                    <div class="relative">
                        <input type="password" name="password_confirm" id="pw2" required autocomplete="new-password"
                               class="input-dark pr-12" placeholder="Repite la contraseña">
                        <button type="button" onclick="togglePw('pw2')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors text-sm">👁</button>
                    </div>
                </div>

                <div class="flex items-start gap-3 pt-2">
                    <input type="checkbox" name="accept_terms" id="terms" required
                           class="mt-1 w-4 h-4 rounded accent-green-500 cursor-pointer flex-shrink-0">
                    <label for="terms" class="text-sm text-gray-400 cursor-pointer leading-relaxed">
                        Acepto los <a href="#" class="text-green-400 hover:text-green-300">Términos de uso</a>
                        y la <a href="#" class="text-green-400 hover:text-green-300">Política de privacidad</a>
                    </label>
                </div>

                <button type="submit" data-submit-btn data-loading-text="Creando cuenta…" class="btn-primary w-full justify-center py-3.5 text-base glow-green mt-1">
                    <span data-btn-text>Crear mi cuenta gratis →</span>
                    <svg data-spinner class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-gray-500 mt-6 fade-up-2">
            ¿Ya tienes cuenta?
            <a href="<?= APP_URL ?>/login" class="text-green-400 font-semibold hover:text-green-300 transition-colors">Iniciar sesión</a>
        </p>
    </div>
</div>
<script>
function togglePw(id){const i=document.getElementById(id);i.type=i.type==='password'?'text':'password';}
document.getElementById('pw1')?.addEventListener('input',function(){
    const v=this.value;let s=0;
    if(v.length>=8)s+=33; if(/[A-Z]/.test(v))s+=33; if(/[0-9]/.test(v))s+=34;
    const bar=document.getElementById('strengthBar');
    bar.style.width=s+'%';
    bar.style.background=s<40?'#ef4444':s<80?'#f59e0b':'#16a34a';
});
</script>
</body></html>
