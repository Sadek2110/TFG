<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>FastPlay</title>
    <meta name="description" content="FastPlay — Fútbol amateur organizado para todos, en cualquier lugar.">
    <?php if (isset($_SESSION['csrf_token'])): ?>
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <?php endif; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        fp: {
                            bg:   '#060d09',
                            card: '#0d1810',
                        }
                    },
                    backgroundImage: {
                        'grid-dark': "linear-gradient(rgba(255,255,255,0.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.03) 1px,transparent 1px)",
                    },
                    backgroundSize: {
                        'grid': '60px 60px',
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;0,14..32,900;1,14..32,400&display=swap" rel="stylesheet">
    <style>
        html { scroll-behavior: smooth; }
        * { font-family: 'Inter', system-ui, sans-serif; box-sizing: border-box; }

        /* ── Gradients ── */
        .gradient-text {
            background: linear-gradient(135deg, #4ade80 0%, #16a34a 60%, #facc15 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .gradient-text-gold {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ── Glass surfaces ── */
        .glass {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.08);
            transition: background 0.25s ease, border-color 0.25s ease;
        }
        .glass-green {
            background: rgba(22,163,74,0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(22,163,74,0.2);
            transition: background 0.25s ease, border-color 0.25s ease;
        }
        .glow-green  { box-shadow: 0 0 40px rgba(22,163,74,0.35); }
        .glow-sm     { box-shadow: 0 0 20px rgba(22,163,74,0.2);  }

        /* ── Keyframes ── */
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(22px); }
            to   { opacity:1; transform:translateY(0);    }
        }
        @keyframes pulse-dot {
            0%,100% { opacity:1; transform:scale(1);   }
            50%      { opacity:.45; transform:scale(1.5); }
        }
        @keyframes float {
            0%,100% { transform: translateY(0px); }
            50%     { transform: translateY(-7px); }
        }
        @keyframes shimmer {
            from { background-position: -200% center; }
            to   { background-position:  200% center; }
        }
        @keyframes slideDown {
            from { opacity:0; transform:translate(-50%,-16px); }
            to   { opacity:1; transform:translate(-50%,0);     }
        }
        @keyframes scaleIn {
            from { opacity:0; transform:scale(.94) translateY(-6px); }
            to   { opacity:1; transform:scale(1)   translateY(0);    }
        }

        /* ── Entrance animations ── */
        .fade-up   { animation: fadeUp .6s cubic-bezier(.22,1,.36,1) both; }
        .fade-up-1 { animation: fadeUp .6s .08s cubic-bezier(.22,1,.36,1) both; }
        .fade-up-2 { animation: fadeUp .6s .16s cubic-bezier(.22,1,.36,1) both; }
        .fade-up-3 { animation: fadeUp .6s .24s cubic-bezier(.22,1,.36,1) both; }
        .fade-up-4 { animation: fadeUp .6s .32s cubic-bezier(.22,1,.36,1) both; }
        .fade-up-5 { animation: fadeUp .6s .40s cubic-bezier(.22,1,.36,1) both; }
        .pulse-dot { animation: pulse-dot 2.2s cubic-bezier(.45,.05,.55,.95) infinite; }
        .float     { animation: float 4s ease-in-out infinite; }
        .float-slow{ animation: float 6s ease-in-out infinite; }
        .skeleton  {
            background: linear-gradient(90deg, rgba(255,255,255,.04) 25%, rgba(255,255,255,.09) 50%, rgba(255,255,255,.04) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.6s ease-in-out infinite;
            border-radius: .5rem;
        }

        /* ── Buttons ── */
        .btn-primary {
            display:inline-flex; align-items:center; gap:.5rem;
            background:#16a34a; color:#fff; font-weight:700;
            padding:.85rem 2rem; border-radius:9999px;
            transition: background .2s ease, transform .18s cubic-bezier(.22,1,.36,1), box-shadow .25s ease;
            user-select: none;
        }
        .btn-primary:hover {
            background:#15803d;
            transform:translateY(-2px) scale(1.02);
            box-shadow:0 0 36px rgba(22,163,74,.45), 0 4px 16px rgba(0,0,0,.35);
        }
        .btn-primary:active { transform:translateY(0) scale(.97); box-shadow:none; }

        .btn-ghost {
            display:inline-flex; align-items:center; gap:.5rem;
            background:rgba(255,255,255,.06); color:#fff; font-weight:600;
            padding:.85rem 2rem; border-radius:9999px; border:1px solid rgba(255,255,255,.12);
            transition: background .2s ease, transform .18s cubic-bezier(.22,1,.36,1), border-color .2s ease;
            user-select: none;
        }
        .btn-ghost:hover {
            background:rgba(255,255,255,.11);
            border-color:rgba(255,255,255,.22);
            transform:translateY(-2px) scale(1.01);
        }
        .btn-ghost:active { transform:translateY(0) scale(.97); }

        /* ── Inputs ── */
        .input-dark {
            width:100%; background:rgba(255,255,255,.05); color:#fff;
            border:1px solid rgba(255,255,255,.1); border-radius:.75rem;
            padding:.85rem 1.1rem; font-size:.95rem;
            transition: border-color .2s ease, box-shadow .25s ease, background .2s ease, transform .15s ease;
            outline:none;
        }
        .input-dark::placeholder { color: rgba(255,255,255,.3); }
        .input-dark:hover { background:rgba(255,255,255,.07); border-color:rgba(255,255,255,.18); }
        .input-dark:focus {
            border-color:#16a34a;
            box-shadow:0 0 0 3px rgba(22,163,74,.18);
            background:rgba(255,255,255,.07);
        }

        /* ── Navigation links ── */
        .nav-link {
            color:rgba(255,255,255,.55); font-size:.875rem; font-weight:500;
            transition: color .2s ease; position:relative; padding-bottom:2px;
        }
        .nav-link:hover, .nav-link.active { color:#fff; }
        .nav-link::after {
            content:''; position:absolute; bottom:0; left:0; right:0;
            height:2px; background:#16a34a; border-radius:9999px;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .28s cubic-bezier(.22,1,.36,1);
        }
        .nav-link.active::after { transform: scaleX(1); }

        /* ── Flash notifications ── */
        #flash-msg { animation: slideDown .45s cubic-bezier(.22,1,.36,1) both; }

        /* ── Dropdown / popup ── */
        .dropdown-panel {
            opacity:0;
            transform: scale(.94) translateY(-8px);
            transform-origin: top right;
            pointer-events:none;
            transition: opacity .2s ease, transform .25s cubic-bezier(.22,1,.36,1);
        }
        .dropdown-panel.open {
            opacity:1;
            transform: scale(1) translateY(0);
            pointer-events:auto;
        }

        /* ── Mobile menu ── */
        #mobileMenu {
            display:flex; flex-direction:column; gap:.75rem;
            max-height:0; overflow:hidden; opacity:0;
            padding:0 1.5rem;
            transition: max-height .38s cubic-bezier(.22,1,.36,1), opacity .25s ease, padding .3s ease;
        }
        #mobileMenu.open {
            max-height:420px; opacity:1;
            padding:1rem 1.5rem;
        }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar       { width:5px; }
        ::-webkit-scrollbar-track { background:#060d09; }
        ::-webkit-scrollbar-thumb { background:linear-gradient(to bottom,#16a34a,#0d4a22); border-radius:4px; }
        ::-webkit-scrollbar-thumb:hover { background:linear-gradient(to bottom,#22c55e,#16a34a); }
    </style>
</head>
<body class="bg-[#060d09] text-white min-h-screen flex flex-col antialiased">
<script>const APP_URL = '<?= APP_URL ?>';</script>
<script src="<?= APP_URL ?>/public/js/app.js"></script>
<script src="<?= APP_URL ?>/public/js/forms.js"></script>
<?php
// Flash message helper
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<?php if ($flash): ?>
<div id="flash-msg" class="fixed top-20 left-1/2 z-[100] px-5 py-3 rounded-2xl font-semibold text-sm shadow-2xl flex items-center gap-2.5 whitespace-nowrap
    <?= $flash['type'] === 'success' ? 'bg-green-600 text-white' : ($flash['type'] === 'error' ? 'bg-red-600 text-white' : 'bg-yellow-500 text-black') ?>"
    style="transform:translateX(-50%);">
    <span><?= $flash['type'] === 'success' ? '✓' : ($flash['type'] === 'error' ? '✕' : '⚠') ?></span>
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>
