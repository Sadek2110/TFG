<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presentación Técnica de Fastplay - Fútbol Callejero Organizado</title>
    <!-- Google Fonts: Outfit (modern, athletic typography) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons for modern icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --bg-dark: #060d09;
            --bg-card: rgba(14, 26, 19, 0.55);
            --bg-card-hover: rgba(22, 42, 30, 0.7);
            --neon: #16a34a;
            --neon-glow: rgba(22, 163, 74, 0.4);
            --neon-light: #22c55e;
            --gold: #fbbf24;
            --gold-glow: rgba(251, 191, 36, 0.35);
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
            --border-glass: rgba(255, 255, 255, 0.08);
            --border-active: rgba(22, 163, 74, 0.6);
            --font-main: 'Outfit', sans-serif;
            --transition-smooth: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-dark);
            color: var(--text-light);
            overflow: hidden;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(22, 163, 74, 0.06) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(251, 191, 36, 0.04) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, #0c1810 0%, #060d09 100%);
            background-attachment: fixed;
        }

        /* Top Progress Bar */
        .progress-container {
            width: 100%;
            height: 5px;
            background: rgba(255, 255, 255, 0.05);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .progress-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--neon) 0%, var(--gold) 100%);
            box-shadow: 0 0 10px var(--neon-glow);
            transition: width 0.3s ease;
        }

        /* Navigation Header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            position: fixed;
            top: 5px;
            left: 0;
            right: 0;
            z-index: 900;
            backdrop-filter: blur(10px);
            background: rgba(6, 13, 9, 0.6);
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-circle {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--neon) 0%, #0f766e 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 15px var(--neon-glow);
            overflow: hidden;
            padding: 5px;
        }

        .logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 0 3px rgba(0,0,0,0.4));
        }

        .logo-circle i {
            color: white;
            font-size: 1.2rem;
        }

        .home-btn {
            color: var(--neon-light) !important;
            border-color: rgba(22, 163, 74, 0.35) !important;
            text-decoration: none;
        }

        .home-btn:hover {
            background: rgba(22, 163, 74, 0.15) !important;
            border-color: var(--neon) !important;
        }

        .logo-text {
            font-weight: 900;
            font-size: 1.5rem;
            letter-spacing: 1px;
            background: linear-gradient(to right, #ffffff, #c2f5d3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .project-tag {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            background: rgba(22, 163, 74, 0.15);
            border: 1px solid var(--neon);
            padding: 4px 10px;
            border-radius: 20px;
            color: var(--neon-light);
            font-weight: 600;
        }

        /* Slide Container */
        .slides-wrapper {
            flex: 1;
            position: relative;
            width: 100%;
            height: 100%;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            visibility: hidden;
            transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.6s ease, visibility 0.6s ease;
            transform: scale(0.95) translateY(20px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 90px 60px 80px 60px;
            z-index: 1;
        }

        .slide.active {
            opacity: 1;
            visibility: visible;
            transform: scale(1) translateY(0);
            z-index: 10;
        }

        .slide.prev {
            transform: scale(0.95) translateY(-30px);
            opacity: 0;
        }

        /* Slide Grid Layouts */
        .slide-grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 40px;
            width: 100%;
            max-width: 1280px;
            align-items: center;
        }

        .slide-grid.reverse {
            grid-template-columns: 0.85fr 1.15fr;
        }

        .slide-full {
            width: 100%;
            max-width: 1280px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Typography */
        .eyebrow {
            color: var(--neon-light);
            font-size: 0.9rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 4px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .eyebrow::after {
            content: '';
            display: inline-block;
            width: 40px;
            height: 2px;
            background: var(--neon);
        }

        .eyebrow.gold {
            color: var(--gold);
        }
        .eyebrow.gold::after {
            background: var(--gold);
        }

        h1.slide-title {
            font-size: 3.2rem;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 15px;
            letter-spacing: -1px;
            color: var(--text-light);
        }

        h1.slide-title span {
            background: linear-gradient(135deg, var(--neon-light) 0%, var(--gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        h2.slide-subtitle {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 12px;
            line-height: 1.2;
        }

        h2.slide-subtitle span {
            color: var(--neon-light);
        }

        .slide-desc {
            font-size: 1.05rem;
            line-height: 1.5;
            color: var(--text-muted);
            margin-bottom: 25px;
        }

        /* Glass Cards */
        .glass-panel {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-glass);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            position: relative;
            overflow: hidden;
            transition: var(--transition-smooth);
            transform: translate3d(0,0,0);
            backface-visibility: hidden;
        }

        .glass-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--neon) 0%, transparent 100%);
            opacity: 0.7;
        }

        .glass-panel:hover {
            border-color: rgba(22, 163, 74, 0.25);
            box-shadow: 0 20px 50px rgba(22, 163, 74, 0.1);
            background: var(--bg-card-hover);
        }

        .glass-panel.gold::before {
            background: linear-gradient(90deg, var(--gold) 0%, transparent 100%);
        }

        .glass-panel.gold:hover {
            border-color: rgba(251, 191, 36, 0.25);
            box-shadow: 0 20px 50px rgba(251, 191, 36, 0.08);
        }

        /* Visual Showcase / Images */
        .visual-showcase {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            max-height: 400px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .visual-showcase img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 12s ease;
        }

        .slide.active .visual-showcase img {
            transform: scale(1.08);
        }

        .visual-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(6, 13, 9, 0.85) 0%, transparent 60%);
            display: flex;
            align-items: flex-end;
            padding: 20px;
            pointer-events: none;
        }

        .visual-caption {
            font-size: 0.85rem;
            color: white;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.8);
        }

        .visual-caption i {
            color: var(--neon-light);
        }

        /* Feature grid */
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .feature-box {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .feature-icon-wrapper {
            background: rgba(22, 163, 74, 0.1);
            border: 1px solid rgba(22, 163, 74, 0.2);
            color: var(--neon-light);
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.15rem;
            box-shadow: 0 0 10px rgba(22, 163, 74, 0.1);
        }

        .feature-box.gold .feature-icon-wrapper {
            background: rgba(251, 191, 36, 0.1);
            border: 1px solid rgba(251, 191, 36, 0.2);
            color: var(--gold);
            box-shadow: 0 0 10px rgba(251, 191, 36, 0.1);
        }

        .feature-box h4 {
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 4px;
            color: white;
        }

        .feature-box p {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.4;
        }

        /* Tech Stack Badges */
        .tech-categories {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 10px;
        }

        .tech-category-card {
            background: rgba(255, 255, 255, 0.015);
            border: 1px solid rgba(255, 255, 255, 0.04);
            border-radius: 12px;
            padding: 15px;
        }

        .tech-category-card h3 {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--neon-light);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tech-category-card.gold h3 {
            color: var(--gold);
        }

        .badge-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .tech-badge {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 0.8rem;
            color: white;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition-smooth);
        }

        .tech-badge:hover {
            background: rgba(22, 163, 74, 0.08);
            border-color: rgba(22, 163, 74, 0.3);
            transform: translateY(-2px);
        }

        .tech-badge i {
            color: var(--neon-light);
        }

        .tech-badge.gold:hover {
            background: rgba(251, 191, 36, 0.08);
            border-color: rgba(251, 191, 36, 0.3);
        }

        .tech-badge.gold i {
            color: var(--gold);
        }

        /* Database Entities Map */
        .db-entities-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            width: 100%;
        }

        .db-entity-card {
            background: rgba(255,255,255,0.015);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 15px;
            text-align: left;
            transition: var(--transition-smooth);
        }

        .db-entity-card:hover {
            border-color: rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.03);
            transform: translateY(-2px);
        }

        .db-entity-card h4 {
            font-size: 0.95rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            font-family: monospace;
        }

        .db-entity-card h4 i {
            color: var(--neon-light);
        }

        .db-entity-card p {
            font-size: 0.78rem;
            color: var(--text-muted);
            line-height: 1.4;
        }

        /* Folder Explorer (Interactive) */
        .explorer-container {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 20px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            height: 380px;
            overflow: hidden;
        }

        .explorer-tree {
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            padding: 15px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .tree-item {
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--text-muted);
            transition: var(--transition-smooth);
        }

        .tree-item:hover {
            background: rgba(255, 255, 255, 0.02);
            color: white;
        }

        .tree-item.active {
            background: rgba(22, 163, 74, 0.08);
            border: 1px solid rgba(22, 163, 74, 0.15);
            color: var(--neon-light);
            font-weight: 600;
        }

        .tree-item .folder-name {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tree-item i.folder-icon {
            color: var(--neon);
        }

        .tree-item i.chevron {
            font-size: 0.8rem;
            opacity: 0.5;
            transition: var(--transition-smooth);
        }

        .tree-item.active i.chevron {
            transform: translateX(3px);
            opacity: 1;
        }

        .explorer-content {
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: rgba(6, 13, 9, 0.2);
        }

        .explorer-detail-header {
            margin-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 8px;
        }

        .explorer-detail-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .explorer-detail-title span {
            font-size: 0.7rem;
            text-transform: uppercase;
            background: rgba(22, 163, 74, 0.1);
            color: var(--neon-light);
            border: 1px solid rgba(22, 163, 74, 0.2);
            padding: 2px 6px;
            border-radius: 4px;
        }

        .explorer-detail-desc {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.4;
            margin-bottom: 15px;
        }

        .explorer-files-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: white;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .explorer-files-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .file-pill {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 0.8rem;
            font-family: monospace;
            color: #d1d5db;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .file-pill i {
            color: #38bdf8;
            font-size: 0.9rem;
        }

        .file-pill i.php-icon {
            color: #777bb4;
        }

        /* Controls Panel */
        .controls-panel {
            position: fixed;
            bottom: 25px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(14, 26, 19, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass);
            padding: 8px 25px;
            border-radius: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            z-index: 950;
            transition: var(--transition-smooth);
        }

        .controls-panel:hover {
            border-color: rgba(22, 163, 74, 0.25);
            box-shadow: 0 10px 35px rgba(22, 163, 74, 0.1);
        }

        .nav-btn {
            background: transparent;
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.1rem;
            transition: var(--transition-smooth);
        }

        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            color: var(--neon-light);
        }

        .nav-btn:active {
            transform: scale(0.9);
        }

        .slide-dots {
            display: flex;
            gap: 8px;
        }

        .dot {
            width: 8px;
            height: 8px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            cursor: pointer;
            transition: var(--transition-smooth);
        }

        .dot:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        .dot.active {
            background: var(--neon);
            width: 24px;
            border-radius: 4px;
            box-shadow: 0 0 8px var(--neon-glow);
        }

        .keyboard-tip {
            font-size: 0.75rem;
            color: var(--text-muted);
            border-left: 1px solid rgba(255,255,255,0.1);
            padding-left: 15px;
            margin-left: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .keyboard-tip kbd {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 4px;
            padding: 1px 5px;
            font-size: 0.65rem;
            font-family: inherit;
            color: white;
        }

        /* Extra Styling and Layouts for specific slides */
        .intro-title-wrapper {
            margin-bottom: 25px;
        }

        .stats-highlight-row {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.05);
            padding-top: 15px;
        }

        .stat-highlight {
            flex: 1;
        }

        .stat-val {
            font-size: 1.8rem;
            font-weight: 900;
            color: white;
            line-height: 1;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stat-val span {
            color: var(--neon-light);
        }

        .stat-val.gold span {
            color: var(--gold);
        }

        .stat-lbl {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* Custom slide layouts */
        .cover-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            max-width: 800px;
        }

        .cover-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(22, 163, 74, 0.08);
            border: 1px solid rgba(22, 163, 74, 0.25);
            padding: 6px 14px;
            border-radius: 30px;
            color: var(--neon-light);
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(22, 163, 74, 0.05);
        }

        /* Quality cards */
        .quality-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            width: 100%;
        }

        .quality-card {
            background: rgba(255, 255, 255, 0.01);
            border: 1px solid rgba(255, 255, 255, 0.04);
            border-radius: 16px;
            padding: 20px;
            text-align: left;
            transition: var(--transition-smooth);
        }

        .quality-card:hover {
            background: rgba(255, 255, 255, 0.02);
            border-color: rgba(255, 255, 255, 0.08);
            transform: translateY(-4px);
        }

        .quality-icon {
            font-size: 1.6rem;
            color: var(--neon-light);
            margin-bottom: 12px;
            display: inline-block;
        }

        .quality-card.gold .quality-icon {
            color: var(--gold);
        }

        .quality-card h3 {
            font-size: 1rem;
            font-weight: 700;
            color: white;
            margin-bottom: 8px;
        }

        .quality-card p {
            font-size: 0.8rem;
            color: var(--text-muted);
            line-height: 1.4;
        }

        /* Animations */
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 15px var(--neon-glow); }
            50% { transform: scale(1.03); box-shadow: 0 0 25px rgba(22, 163, 74, 0.45); }
            100% { transform: scale(1); box-shadow: 0 0 15px var(--neon-glow); }
        }

        .pulse-element {
            animation: pulse 3s infinite ease-in-out;
        }

        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
            100% { transform: translateY(0px); }
        }

        .floating {
            animation: floating 4s infinite ease-in-out;
        }

        /* Staggered content reveal when a slide becomes active */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(35px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        .slide.active .eyebrow,
        .slide.active .cover-badge { animation: fadeUp 0.6s 0.08s both; }

        .slide.active h1.slide-title,
        .slide.active h2.slide-subtitle { animation: fadeUp 0.65s 0.18s both; }

        .slide.active .slide-desc { animation: fadeUp 0.65s 0.28s both; }

        .slide.active .glass-panel,
        .slide.active .visual-showcase { animation: fadeUp 0.7s 0.36s both; }

        .slide.active .feature-grid,
        .slide.active .badge-list,
        .slide.active .quality-grid,
        .slide.active .db-entities-grid,
        .slide.active .tech-categories,
        .slide.active .explorer-container,
        .slide.active .impact-grid { animation: fadeUp 0.75s 0.46s both; }

        .slide.active .stats-highlight-row { animation: fadeIn 0.9s 0.6s both; }

        /* Animated gradient sweep on titles */
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        h1.slide-title span {
            background-size: 200% auto;
            animation: gradientShift 6s ease infinite;
        }

        /* Directional exit (set by JS) */
        .slide.exit-left {
            transform: scale(0.92) translateX(-60px);
            opacity: 0;
        }
        .slide.exit-right {
            transform: scale(0.92) translateX(60px);
            opacity: 0;
        }

        /* Impact / animated counters grid */
        .impact-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            width: 100%;
            max-width: 1100px;
            margin-top: 10px;
        }

        .impact-card {
            background: rgba(255, 255, 255, 0.015);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 18px;
            padding: 28px 20px;
            text-align: center;
            transition: var(--transition-smooth);
            position: relative;
            overflow: hidden;
        }

        .impact-card::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0;
            width: 100%; height: 3px;
            background: linear-gradient(90deg, var(--neon) 0%, var(--gold) 100%);
            opacity: 0;
            transition: var(--transition-smooth);
        }

        .impact-card:hover {
            transform: translateY(-6px);
            border-color: rgba(22, 163, 74, 0.3);
            background: rgba(22, 163, 74, 0.04);
        }

        .impact-card:hover::after { opacity: 1; }

        .impact-icon {
            font-size: 1.8rem;
            color: var(--neon-light);
            margin-bottom: 12px;
        }

        .impact-card.gold .impact-icon { color: var(--gold); }

        .impact-number {
            font-size: 2.8rem;
            font-weight: 900;
            line-height: 1;
            color: white;
            margin-bottom: 8px;
            letter-spacing: -1px;
        }

        .impact-number .suffix { color: var(--neon-light); }
        .impact-card.gold .impact-number .suffix { color: var(--gold); }

        .impact-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 500;
        }

        /* Gamification / social cards */
        .gamification-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
            width: 100%;
        }

        .game-card {
            display: flex;
            gap: 16px;
            align-items: flex-start;
            background: rgba(255, 255, 255, 0.015);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 20px;
            transition: var(--transition-smooth);
        }

        .game-card:hover {
            transform: translateY(-4px);
            border-color: rgba(22, 163, 74, 0.25);
            background: rgba(22, 163, 74, 0.03);
        }

        .game-card .game-icon {
            flex-shrink: 0;
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: rgba(22, 163, 74, 0.1);
            border: 1px solid rgba(22, 163, 74, 0.2);
            color: var(--neon-light);
        }

        .game-card.gold .game-icon {
            background: rgba(251, 191, 36, 0.1);
            border-color: rgba(251, 191, 36, 0.2);
            color: var(--gold);
        }

        .game-card h4 {
            font-size: 1.05rem;
            font-weight: 700;
            color: white;
            margin-bottom: 6px;
        }

        .game-card p {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.45;
        }

        /* Closing slide */
        .closing-logo {
            width: 130px;
            height: 130px;
            border-radius: 30px;
            background: linear-gradient(135deg, var(--neon) 0%, #0f766e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 45px var(--neon-glow);
            margin-bottom: 25px;
            padding: 22px;
        }

        .closing-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .closing-title {
            font-size: 4rem;
            font-weight: 900;
            letter-spacing: -2px;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #ffffff 0%, #c2f5d3 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .closing-cta {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--neon);
            color: white;
            font-weight: 700;
            font-size: 1.05rem;
            padding: 14px 32px;
            border-radius: 14px;
            text-decoration: none;
            box-shadow: 0 8px 25px var(--neon-glow);
            transition: var(--transition-smooth);
            margin-top: 30px;
            pointer-events: auto;
        }

        .closing-cta:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 35px rgba(22, 163, 74, 0.5);
        }

        .closing-cta.secondary {
            background: transparent;
            border: 1px solid var(--border-glass);
            color: var(--text-light);
            box-shadow: none;
            margin-left: 12px;
        }

        .closing-cta.secondary:hover {
            background: rgba(255,255,255,0.05);
            box-shadow: none;
        }

        /* Footer info */
        footer.slide-footer {
            position: fixed;
            bottom: 25px;
            left: 40px;
            right: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 900;
            pointer-events: none;
        }

        .author-info {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .author-info a {
            color: var(--neon-light);
            text-decoration: none;
            pointer-events: auto;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .author-info a:hover {
            text-decoration: underline;
        }

        .slide-counter {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 600;
            font-family: monospace;
            background: rgba(255, 255, 255, 0.03);
            padding: 4px 10px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Fullscreen button */
        .fs-btn {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition-smooth);
            pointer-events: auto;
        }

        .fs-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.15);
        }

        /* Responsive adaptations */
        @media (max-width: 992px) {
            .slide-grid {
                grid-template-columns: 1fr;
                gap: 20px;
                max-width: 100%;
            }
            .slide-grid.reverse {
                grid-template-columns: 1fr;
            }
            .visual-showcase {
                max-height: 220px;
            }
            h1.slide-title {
                font-size: 2.2rem;
            }
            h2.slide-subtitle {
                font-size: 1.5rem;
            }
            .slide {
                padding: 80px 20px 80px 20px;
                overflow-y: auto;
            }
            .keyboard-tip {
                display: none;
            }
            .tech-categories {
                grid-template-columns: 1fr;
            }
            .explorer-container {
                grid-template-columns: 1fr;
                height: auto;
            }
            .explorer-tree {
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
                height: 150px;
            }
            .quality-grid {
                grid-template-columns: 1fr;
            }
            .db-entities-grid {
                grid-template-columns: 1fr;
            }
            .impact-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .gamification-grid {
                grid-template-columns: 1fr;
            }
            .closing-title {
                font-size: 2.6rem;
            }
            .home-btn span,
            #autoplayBtn {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- Progress Indicator Bar -->
    <div class="progress-container">
        <div class="progress-bar" id="progressBar"></div>
    </div>

    <!-- Top Sticky Header -->
    <header>
        <div class="logo-container">
            <div class="logo-circle floating">
                <img src="<?= asset('images/logo.png') ?>" alt="FastPlay logo">
            </div>
            <div class="logo-text">FastPlay</div>
            <span class="project-tag">v3.0.0</span>
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="<?= url('') ?>" class="fs-btn home-btn" title="Volver a la página principal">
                <i class="bi bi-house-door-fill"></i> Volver al Inicio
            </a>
            <button class="fs-btn" id="autoplayBtn" onclick="toggleAutoplay()">
                <i class="bi bi-play-circle-fill"></i> Autoplay
            </button>
            <button class="fs-btn" onclick="toggleFullscreen()">
                <i class="bi bi-fullscreen"></i> Pantalla Completa
            </button>
        </div>
    </header>

    <!-- Main Slides Wrapper -->
    <div class="slides-wrapper" id="slidesWrapper">

        <!-- Slide 1: Portada (Hero) -->
        <div class="slide active" id="slide-1">
            <div class="slide-grid">
                <div class="intro-left">
                    <div class="cover-badge">
                        <i class="bi bi-lightning-charge-fill"></i> Arquitectura Web · Entorno Académico
                    </div>
                    <h1 class="slide-title">Futbol Amateur<br><span>Organizado.</span></h1>
                    <h2 class="slide-subtitle" style="font-weight: 500; font-size: 1.4rem; color: var(--text-muted); margin-bottom: 20px;">
                        "El puente definitivo entre la pachanga improvisada de barrio y las ligas organizadas."
                    </h2>
                    <div class="glass-panel" style="padding: 25px;">
                        <p style="font-size: 0.95rem; line-height: 1.5; color: #d1d5db;">
                            <strong>Fastplay</strong> es una aplicación web interactiva desarrollada para capitanes y jugadores de fútbol amateur en <strong>Ceuta</strong>. Permite estructurar equipos, organizar partidos amistosos, inscribirse en ligas, y llevar un registro pormenorizado de las estadísticas del plantel eliminando la fricción tradicional.
                        </p>
                        <div class="stats-highlight-row" style="margin-top: 15px; padding-top: 15px;">
                            <div class="stat-highlight">
                                <div class="stat-val">Ceuta</div>
                                <div class="stat-lbl">Foco Geográfico</div>
                            </div>
                            <div class="stat-highlight">
                                <div class="stat-val">231</div>
                                <div class="stat-lbl">Tests PHPUnit</div>
                            </div>
                            <div class="stat-highlight">
                                <div class="stat-val">100%</div>
                                <div class="stat-lbl">Vanilla Stack</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="intro-right" style="display: flex; align-items: center; justify-content: center;">
                    <div class="visual-showcase pulse-element" style="max-height: 380px; width: 100%; border: 1px solid rgba(22, 163, 74, 0.25);">
                        <img src="<?= asset('images/presentacion_hero.png') ?>" alt="Fastplay Football Stadium">
                        <div class="visual-overlay">
                            <div class="visual-caption">
                                <i class="bi bi-geo-alt-fill"></i> Estadio digitalizado de Fastplay
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 2: La Idea & Foco Regional -->
        <div class="slide" id="slide-2">
            <div class="slide-grid reverse">
                <div class="showcase-side">
                    <div class="visual-showcase" style="max-height: 380px;">
                        <img src="<?= asset('images/campos/emilio-cozar.jpg') ?>" alt="Campo de Ceuta">
                        <div class="visual-overlay">
                            <div class="visual-caption">
                                <i class="bi bi-compass-fill"></i> Campos reales integrados en la base de datos de Ceuta
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-side">
                    <div class="eyebrow">Concepto & Foco</div>
                    <h2 class="slide-subtitle">La Idea: <span>Resolver el caos.</span></h2>
                    <p class="slide-desc" style="margin-bottom: 15px;">
                        Tradicionalmente, las pachangas de fin de semana se coordinan a través de cadenas interminables de WhatsApp y hojas de cálculo. Fastplay centraliza este proceso y eleva el nivel de juego amateur en la Ciudad Autónoma de Ceuta.
                    </p>
                    
                    <div class="feature-grid">
                        <div class="feature-box">
                            <div class="feature-icon-wrapper">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div>
                                <h4>Liga Amistosa (Free)</h4>
                                <p>Inscripción gratuita. Permite negociar partidos en directo entre capitanes de equipo, pactando la fecha, el campo, y las reglas del encuentro.</p>
                            </div>
                        </div>
                        
                        <div class="feature-box gold">
                            <div class="feature-icon-wrapper">
                                <i class="bi bi-trophy-fill"></i>
                            </div>
                            <div>
                                <h4>Liga Pro (Premium)</h4>
                                <p>Modelo de suscripción integrado con Stripe. Acceso a calendario oficial estructurado por la administración, árbitros colegiados y premios físicos.</p>
                            </div>
                        </div>
                        
                        <div class="feature-box">
                            <div class="feature-icon-wrapper">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div>
                                <h4>Estadios Locales</h4>
                                <p>Carga de los principales campos de Ceuta (Alfonso Murube, Emilio Cózar, Benoliel, Pirri, etc.) con sus correspondientes localizaciones en mapas interactivos.</p>
                            </div>
                        </div>

                        <div class="feature-box">
                            <div class="feature-icon-wrapper">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div>
                                <h4>Convocatorias</h4>
                                <p>Control del plantel de juego: capitanes definen titulares y suplentes, mientras los jugadores confirman su disponibilidad directamente.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 3: Stack Tecnológico - Backend y Seguridad -->
        <div class="slide" id="slide-3">
            <div class="slide-grid">
                <div class="content-side">
                    <div class="eyebrow">Arquitectura Lógica</div>
                    <h2 class="slide-subtitle">Backend robusto, <span>sin dependencias.</span></h2>
                    <p class="slide-desc" style="margin-bottom: 15px;">
                        Para demostrar dominio en el desarrollo de aplicaciones web complejas, Fastplay evita el uso de frameworks pesados (como Laravel o Symfony), construyendo un núcleo a medida centrado en la seguridad defensiva.
                    </p>
                    
                    <div class="glass-panel" style="padding: 20px; margin-bottom: 15px;">
                        <h4 style="color: var(--neon-light); font-size: 0.95rem; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px;">
                            <i class="bi bi-shield-fill-lock"></i> Directivas de Seguridad Defensiva
                        </h4>
                        <ul style="list-style: none; display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 0.8rem; color: #cbd5e1;">
                            <li><i class="bi bi-shield-fill-check" style="color:var(--neon-light);"></i> <strong>Protección CSRF:</strong> Tokens firmados por sesión en POSTs.</li>
                            <li><i class="bi bi-shield-fill-check" style="color:var(--neon-light);"></i> <strong>CSP Restrictiva:</strong> Cabeceras para prevenir ataques XSS.</li>
                            <li><i class="bi bi-shield-fill-check" style="color:var(--neon-light);"></i> <strong>Rate Limit:</strong> Previene ataques de fuerza bruta en Auth.</li>
                            <li><i class="bi bi-shield-fill-check" style="color:var(--neon-light);"></i> <strong>Sesiones Aisladas:</strong> Guardadas en local, cookies HTTPOnly.</li>
                            <li><i class="bi bi-shield-fill-check" style="color:var(--neon-light);"></i> <strong>Preparación SQL:</strong> Inyecciones imposibilitadas vía PDO.</li>
                            <li><i class="bi bi-shield-fill-check" style="color:var(--neon-light);"></i> <strong>Bcrypt Hashing:</strong> Encriptación irreversible de contraseñas.</li>
                        </ul>
                    </div>

                    <div class="badge-list">
                        <span class="tech-badge"><i class="bi bi-check-circle-fill"></i> PHP 8.x nativo (MVC)</span>
                        <span class="tech-badge"><i class="bi bi-check-circle-fill"></i> Router extensible con alias</span>
                        <span class="tech-badge"><i class="bi bi-check-circle-fill"></i> Capa de datos PDO abstracta</span>
                        <span class="tech-badge"><i class="bi bi-check-circle-fill"></i> Google OAuth 2.0 integrado</span>
                        <span class="tech-badge"><i class="bi bi-check-circle-fill"></i> PHPMailer & Fallback Local</span>
                        <span class="tech-badge"><i class="bi bi-check-circle-fill"></i> Stripe Subscriptions SDK</span>
                    </div>
                </div>
                <div class="showcase-side" style="display: flex; align-items: center; justify-content: center;">
                    <div class="visual-showcase" style="max-height: 380px; border: 1px solid rgba(22, 163, 74, 0.25);">
                        <img src="<?= asset('images/presentacion_stack.png') ?>" alt="Fastplay Stack Técnico">
                        <div class="visual-overlay">
                            <div class="visual-caption">
                                <i class="bi bi-layers-half"></i> Estructura lógica y flujo de datos
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 4: Stack Tecnológico - Frontend e Interfaces -->
        <div class="slide" id="slide-4">
            <div class="slide-grid reverse">
                <div class="showcase-side" style="display: flex; align-items: center; justify-content: center;">
                    <div class="visual-showcase" style="max-height: 380px; box-shadow: 0 15px 35px rgba(251, 191, 36, 0.15); border: 1px solid rgba(251, 191, 36, 0.25);">
                        <img src="<?= asset('images/presentacion_fifa_card.png') ?>" alt="Fastplay Fifa Card">
                        <div class="visual-overlay">
                            <div class="visual-caption">
                                <i class="bi bi-cpu-fill"></i> Interfaz del perfil de jugador en el Dashboard
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-side">
                    <div class="eyebrow gold">Diseño & Frontend (DIW)</div>
                    <h2 class="slide-subtitle">Experiencia inmersiva: <span>Estadio nocturno.</span></h2>
                    <p class="slide-desc" style="margin-bottom: 15px;">
                        El diseño de interfaz emula la atmósfera de un estadio iluminado artificialmente. La paleta combina fondos mate profundos, desenfoques en cascada (glassmorphism), acentos de neón deportivo y dorado Pro.
                    </p>

                    <div class="feature-grid" style="grid-template-columns: 1fr; gap: 12px; margin-bottom: 15px;">
                        <div class="feature-box" style="gap: 10px;">
                            <div class="feature-icon-wrapper" style="width: 32px; height: 32px; font-size: 0.95rem;"><i class="bi bi-droplet-fill"></i></div>
                            <div>
                                <h4 style="font-size: 0.95rem; margin-bottom: 2px;">Vanilla CSS3 Custom Properties</h4>
                                <p style="font-size: 0.8rem;">Estilos centralizados mediante variables nativas en <code>:root</code>, permitiendo overrides ágiles para temas claros/oscuros.</p>
                            </div>
                        </div>
                        <div class="feature-box" style="gap: 10px;">
                            <div class="feature-icon-wrapper" style="width: 32px; height: 32px; font-size: 0.95rem;"><i class="bi bi-phone-fill"></i></div>
                            <div>
                                <h4 style="font-size: 0.95rem; margin-bottom: 2px;">Cápsula Flotante Glassmorphism</h4>
                                <p style="font-size: 0.8rem;">Estructura de navegación minimalista (floating nav capsule) adaptada de forma elástica a layouts móviles mediante flexbox y grid.</p>
                            </div>
                        </div>
                        <div class="feature-box" style="gap: 10px;">
                            <div class="feature-icon-wrapper" style="width: 32px; height: 32px; font-size: 0.95rem;"><i class="bi bi-cursor-fill"></i></div>
                            <div>
                                <h4 style="font-size: 0.95rem; margin-bottom: 2px;">Scroll-Triggered Animations</h4>
                                <p style="font-size: 0.8rem;">Revelado progresivo y dinámico de elementos a medida que el usuario hace scroll, gestionado con JavaScript nativo.</p>
                            </div>
                        </div>
                    </div>

                    <div class="badge-list">
                        <span class="tech-badge"><i class="bi bi-star-fill"></i> Carga variable tipográfica (Inter/Outfit)</span>
                        <span class="tech-badge"><i class="bi bi-star-fill"></i> Bootstrap Icons integrados</span>
                        <span class="tech-badge"><i class="bi bi-star-fill"></i> Caching dinámico mediante <code>asset()</code></span>
                        <span class="tech-badge gold"><i class="bi bi-star-fill"></i> FIFA-Card de Stats interactiva</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 5: Base de Datos y Modelo de Datos -->
        <div class="slide" id="slide-5">
            <div class="slide-full">
                <div class="eyebrow">Capa de Persistencia</div>
                <h2 class="slide-subtitle" style="margin-bottom: 10px;">Modelo de datos y <span>Relaciones</span></h2>
                <p class="slide-desc" style="max-width: 900px; margin-bottom: 20px;">
                    Fastplay soporta dos motores de base de datos de manera agnóstica: **SQLite 3** para el desarrollo ágil local con auto-migración y seeder integrado, y **PostgreSQL** para despliegues de producción sobre Docker/EasyPanel.
                </p>

                <!-- Entities list -->
                <div class="db-entities-grid" style="max-width: 1100px;">
                    <div class="db-entity-card">
                        <h4><i class="bi bi-person-fill"></i> Usuario</h4>
                        <p>Almacena credenciales, flags de verificación de correo (`email_verified`), tokens hash de activación, rol de acceso (`admin/player`), estadísticas y perfil general del jugador local.</p>
                    </div>
                    <div class="db-entity-card">
                        <h4><i class="bi bi-people-fill"></i> Equipo</h4>
                        <p>Gestionado por un Capitán. Controla la plantilla de jugadores, cuotas, solicitudes de unión entrantes (`team-join-request`) y registros en competiciones.</p>
                    </div>
                    <div class="db-entity-card">
                        <h4><i class="bi bi-trophy-fill"></i> Liga</h4>
                        <p>Competiciones divididas en Pro (dorado premium, inscripciones de pago) y Amistosas (gratuita). Registra históricos y tablas clasificatorias generadas al vuelo.</p>
                    </div>
                    <div class="db-entity-card">
                        <h4><i class="bi bi-calendar-check-fill"></i> Partido</h4>
                        <p>Encuentros deportivos vinculados a una liga y un Campo. Controla estados (`Pendiente`, `Confirmado`, `En Curso`, `Finalizado`) y la convocatoria del plantel.</p>
                    </div>
                    <div class="db-entity-card">
                        <h4><i class="bi bi-geo-alt-fill"></i> Campo</h4>
                        <p>Recintos deportivos físicos reservados por fecha y franja horaria. Almacena las coordenadas geográficas de los campos de Ceuta.</p>
                    </div>
                    <div class="db-entity-card">
                        <h4><i class="bi bi-chat-dots-fill"></i> Chat & Logro</h4>
                        <p>Salas por equipo y canales de negociación 1:1 entre capitanes para pactar retos amistosos. Sistema de gamificación con medallas desbloqueadas por hitos.</p>
                    </div>
                </div>

                <div class="glass-panel" style="margin-top: 15px; padding: 15px 25px; width: 100%; max-width: 1100px; text-align: left; display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 0.85rem; color: #cbd5e1; max-width: 80%;">
                        <strong style="color:var(--neon-light);">Auto-Migraciones:</strong> En el primer arranque, la aplicación detecta si el archivo SQLite existe; si no, ejecuta de forma idempotente las consultas de definición de esquemas e inyecta los datos semilla (seeding) con jugadores y partidos realistas de Ceuta.
                    </div>
                    <span class="tech-badge" style="background: rgba(251, 191, 36, 0.1); border-color: var(--gold); color: var(--gold); font-weight: 600;">
                        <i class="bi bi-arrow-repeat"></i> Idempotente
                    </span>
                </div>
            </div>
        </div>

        <!-- Slide 6: Estructura del Proyecto -->
        <div class="slide" id="slide-6">
            <div class="slide-full">
                <div class="eyebrow">Arquitectura de Directorios</div>
                <h2 class="slide-subtitle" style="margin-bottom: 10px;">Estructura de <span>Carpetas Clave</span></h2>
                <p class="slide-desc" style="max-width: 800px; margin-bottom: 15px;">
                    El proyecto sigue una distribución MVC estructurada y segura, aislando todo el código lógico fuera de la raíz pública accesible desde la web. Explora los directorios haciendo clic en ellos:
                </p>

                <!-- Interactive Folder Explorer -->
                <div class="explorer-container" style="width: 100%; max-width: 1100px;">
                    <div class="explorer-tree" id="explorerTree">
                        <!-- Items populated by JS -->
                    </div>
                    <div class="explorer-content" id="explorerContent">
                        <!-- Content populated dynamically by JS -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 7: Frontend Avanzado & DWEC (Soluciones Técnicas) -->
        <div class="slide" id="slide-7">
            <div class="slide-full">
                <div class="eyebrow">Desarrollo Avanzado (DWEC/DIW)</div>
                <h2 class="slide-subtitle" style="margin-bottom: 10px;">Resolución de Problemas <span>e Innovación</span></h2>
                <p class="slide-desc" style="max-width: 800px; margin-bottom: 25px;">
                    Nuestras iteraciones frontend y backend resuelven problemas del mundo real relacionados con compatibilidad gráfica en navegadores modernos y experiencia de usuario.
                </p>

                <div class="quality-grid" style="max-width: 1100px;">
                    <div class="quality-card">
                        <div class="quality-icon"><i class="bi bi-gpu-card"></i></div>
                        <h3>GPU Composition (Ghost Shadows)</h3>
                        <p>Corregimos el bug de texturas fantasmas de Chromium. El uso de <code>backdrop-filter</code> con desplazamientos tridimensionales desalineaba la sombra de los paneles. Lo solucionamos forzando contextos de composición de GPU independientes con <code>translate3d(0, 0, 0)</code>.</p>
                    </div>

                    <div class="quality-card gold">
                        <div class="quality-icon"><i class="bi bi-layout-split"></i></div>
                        <h3>Ceuta Fields Split-Layout</h3>
                        <p>Rediseñamos la vista de campos de fútbol locales. Ubica un mapa interactivo flotante y sticky en la izquierda (45%) mientras el usuario explora las tarjetas de campos en la derecha (55%). Convierte a modo apilado automáticamente en pantallas táctiles móviles.</p>
                    </div>

                    <div class="quality-card">
                        <div class="quality-icon"><i class="bi bi-shield-lock-fill"></i></div>
                        <h3>Mecanismo de Doble-Opt-In</h3>
                        <p>Diferenciamos el registro: OAuth de Google (correo verificado externamente) genera una cuenta activa de inmediato. El registro normal requiere activación por correo y genera notificaciones internas y alertas en el panel de control del usuario.</p>
                    </div>
                </div>

                <div class="glass-panel" style="margin-top: 25px; padding: 15px 30px; width: 100%; max-width: 1100px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="text-align: left;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: var(--neon-light); text-transform: uppercase; letter-spacing: 1px;">Desarrollo JS</span>
                        <h4 style="color: white; font-size: 1rem; margin-top: 2px;">Eventos DOM interactivos, AJAX para chats de equipo y validaciones Regex.</h4>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <span class="tech-badge" style="background: rgba(22, 163, 74, 0.15); border-color: var(--neon); color: var(--neon-light); font-weight: 700;">
                            <i class="bi bi-braces"></i> JS VANILLA
                        </span>
                        <span class="tech-badge" style="background: rgba(251, 191, 36, 0.15); border-color: var(--gold); color: var(--gold); font-weight: 700;">
                            <i class="bi bi-phone-vibrate"></i> RESPONSIVO
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 8: Gamificación y Componente Social -->
        <div class="slide" id="slide-gamification">
            <div class="slide-full">
                <div class="eyebrow gold">Engagement & Retención</div>
                <h2 class="slide-subtitle" style="margin-bottom: 10px;">Gamificación y <span>Comunidad viva</span></h2>
                <p class="slide-desc" style="max-width: 850px; margin-bottom: 25px;">
                    Fastplay no es solo un gestor: es una comunidad. Combinamos mecánicas de juego, comunicación en tiempo real y retos entre capitanes para mantener a los jugadores enganchados temporada tras temporada.
                </p>

                <div class="gamification-grid" style="max-width: 1100px;">
                    <div class="game-card">
                        <div class="game-icon"><i class="bi bi-award-fill"></i></div>
                        <div>
                            <h4>Logros & Medallas</h4>
                            <p>Sistema de hitos desbloqueables: primer gol, racha de victorias, veteranía o fichajes. Cada logro otorga insignias visibles en el perfil del jugador.</p>
                        </div>
                    </div>
                    <div class="game-card gold">
                        <div class="game-icon"><i class="bi bi-bar-chart-line-fill"></i></div>
                        <div>
                            <h4>Carta FIFA Dinámica</h4>
                            <p>Cada jugador posee una carta de estadísticas estilo FIFA que evoluciona con su rendimiento real: partidos jugados, goles, asistencias y valoración media.</p>
                        </div>
                    </div>
                    <div class="game-card">
                        <div class="game-icon"><i class="bi bi-chat-dots-fill"></i></div>
                        <div>
                            <h4>Chat de Equipo (AJAX)</h4>
                            <p>Salas de mensajería en tiempo real por equipo, gestionadas con peticiones asíncronas JavaScript para coordinar alineaciones sin recargar la página.</p>
                        </div>
                    </div>
                    <div class="game-card gold">
                        <div class="game-icon"><i class="bi bi-controller"></i></div>
                        <div>
                            <h4>Retos entre Capitanes</h4>
                            <p>Canal de negociación 1:1 donde los capitanes pactan amistosos en directo: fecha, campo y reglas. El reto aceptado genera el partido automáticamente.</p>
                        </div>
                    </div>
                </div>

                <div class="glass-panel gold" style="margin-top: 20px; padding: 15px 28px; width: 100%; max-width: 1100px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 0.88rem; color: #e2e8f0; text-align: left;">
                        <strong style="color: var(--gold);">Notificaciones internas:</strong> solicitudes de unión, confirmaciones de partido y logros desbloqueados llegan al panel del usuario en tiempo real.
                    </div>
                    <span class="tech-badge gold" style="background: rgba(251, 191, 36, 0.12); border-color: var(--gold); color: var(--gold); font-weight: 700;">
                        <i class="bi bi-bell-fill"></i> Tiempo Real
                    </span>
                </div>
            </div>
        </div>

        <!-- Slide 9: Métricas de Impacto (contadores animados) -->
        <div class="slide" id="slide-impact">
            <div class="slide-full">
                <div class="eyebrow">El Proyecto en Cifras</div>
                <h2 class="slide-subtitle" style="margin-bottom: 10px;">Magnitud y <span>Robustez técnica</span></h2>
                <p class="slide-desc" style="max-width: 800px; margin-bottom: 30px;">
                    Un vistazo cuantitativo al esfuerzo de ingeniería detrás de Fastplay: cobertura de pruebas, entidades de dominio y arquitectura construida íntegramente a medida.
                </p>

                <div class="impact-grid">
                    <div class="impact-card">
                        <div class="impact-icon"><i class="bi bi-shield-fill-check"></i></div>
                        <div class="impact-number"><span class="counter" data-target="231">0</span></div>
                        <div class="impact-label">Tests PHPUnit</div>
                    </div>
                    <div class="impact-card gold">
                        <div class="impact-icon"><i class="bi bi-diagram-3-fill"></i></div>
                        <div class="impact-number"><span class="counter" data-target="12">0</span></div>
                        <div class="impact-label">Entidades de Dominio</div>
                    </div>
                    <div class="impact-card">
                        <div class="impact-icon"><i class="bi bi-geo-alt-fill"></i></div>
                        <div class="impact-number"><span class="counter" data-target="6">0</span></div>
                        <div class="impact-label">Campos de Ceuta</div>
                    </div>
                    <div class="impact-card gold">
                        <div class="impact-icon"><i class="bi bi-percent"></i></div>
                        <div class="impact-number"><span class="counter" data-target="100">0</span><span class="suffix">%</span></div>
                        <div class="impact-label">Stack Vanilla</div>
                    </div>
                    <div class="impact-card">
                        <div class="impact-icon"><i class="bi bi-controller"></i></div>
                        <div class="impact-number"><span class="counter" data-target="2">0</span></div>
                        <div class="impact-label">Modelos de Liga</div>
                    </div>
                    <div class="impact-card gold">
                        <div class="impact-icon"><i class="bi bi-credit-card-2-front-fill"></i></div>
                        <div class="impact-number"><span class="counter" data-target="1">0</span></div>
                        <div class="impact-label">Pasarela Stripe</div>
                    </div>
                    <div class="impact-card">
                        <div class="impact-icon"><i class="bi bi-database-fill-check"></i></div>
                        <div class="impact-number"><span class="counter" data-target="2">0</span></div>
                        <div class="impact-label">Motores BD (SQLite/PG)</div>
                    </div>
                    <div class="impact-card gold">
                        <div class="impact-icon"><i class="bi bi-box-seam-fill"></i></div>
                        <div class="impact-number"><span class="counter" data-target="0">0</span></div>
                        <div class="impact-label">Frameworks Pesados</div>
                    </div>
                </div>

                <div class="glass-panel" style="margin-top: 25px; padding: 15px 28px; width: 100%; max-width: 1100px; text-align: center;">
                    <p style="font-size: 0.88rem; color: #cbd5e1;">
                        <strong style="color: var(--neon-light);">Cero dependencias de framework:</strong> cada capa &mdash; router, ORM ligero, autenticación, seguridad y vistas &mdash; fue diseñada y verificada manualmente para demostrar dominio completo del stack web.
                    </p>
                </div>
            </div>
        </div>

        <!-- Slide 10: Estabilidad, Coherencia y Conclusiones -->
        <div class="slide" id="slide-8">
            <div class="slide-grid">
                <div class="content-side">
                    <div class="eyebrow gold">Calidad del Código & Roadmap</div>
                    <h2 class="slide-subtitle">Código limpio y <span>100% verificado.</span></h2>
                    <p class="slide-desc" style="margin-bottom: 20px;">
                        La estabilidad de Fastplay está respaldada por una amplia suite de pruebas unitarias locales automatizadas que verifican la lógica de negocio antes de integraciones y despliegues en producción.
                    </p>

                    <div class="glass-panel gold" style="padding: 25px; margin-bottom: 25px;">
                        <h4 style="color: var(--gold); font-size: 1rem; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px;">
                            <i class="bi bi-list-task"></i> Próximos pasos (Roadmap v4)
                        </h4>
                        <ul style="list-style: none; display: flex; flex-direction: column; gap: 8px; font-size: 0.85rem; color: #e2e8f0;">
                            <li><i class="bi bi-arrow-right-short" style="color:var(--gold);"></i> <strong>API Rest:</strong> Implementación de servicios JSON para clientes de aplicaciones nativas móviles.</li>
                            <li><i class="bi bi-arrow-right-short" style="color:var(--gold);"></i> <strong>WebSockets:</strong> Resultados y estadísticas en vivo para partidos Pro en juego.</li>
                            <li><i class="bi bi-arrow-right-short" style="color:var(--gold);"></i> <strong>PWA:</strong> Aplicación web progresiva instalable fuera del navegador con notificaciones push.</li>
                        </ul>
                    </div>

                    <div style="display: flex; gap: 15px;">
                        <span class="tech-badge" style="background: rgba(22, 163, 74, 0.12); border-color: var(--neon); color: var(--neon-light); font-weight: 700; padding: 8px 16px;">
                            <i class="bi bi-shield-fill-check"></i> 231 TESTS PASADOS
                        </span>
                        <span class="tech-badge" style="background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.15); color: white; font-weight: 700; padding: 8px 16px;">
                            <i class="bi bi-check2-all"></i> PHPUNIT COMPLETO
                        </span>
                    </div>
                </div>
                <div class="showcase-side" style="display: flex; flex-direction: column; gap: 20px;">
                    <div class="glass-panel" style="background: rgba(14, 26, 19, 0.2); border-color: rgba(22, 163, 74, 0.15); text-align: center;">
                        <div style="font-size: 3rem; color: var(--neon-light); margin-bottom: 10px;"><i class="bi bi-check-circle-fill"></i></div>
                        <h3 style="color: white; font-size: 1.25rem; font-weight: 800; margin-bottom: 5px;">Producción Estable</h3>
                        <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.4;">
                            Configuraciones analizadas con scripts de diagnóstico locales en EasyPanel garantizando cargas seguras y consistentes de las variables de Stripe, SMTP y Google callback.
                        </p>
                    </div>

                    <a href="<?= url('') ?>" class="fs-btn" style="text-align: center; justify-content: center; font-weight: 700; background: var(--neon); color: white; border: none; padding: 12px; font-size: 1rem; box-shadow: 0 5px 15px var(--neon-glow); pointer-events: auto; display: flex; align-items: center; gap: 8px; border-radius: 12px;">
                        Volver a la Aplicación Principal <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Slide 11: Cierre / Gracias -->
        <div class="slide" id="slide-closing">
            <div class="slide-full">
                <div class="closing-logo floating">
                    <img src="<?= asset('images/logo.png') ?>" alt="FastPlay logo">
                </div>
                <div class="cover-badge">
                    <i class="bi bi-check-circle-fill"></i> Presentación completada
                </div>
                <h1 class="closing-title">FastPlay</h1>
                <h2 class="slide-subtitle" style="font-weight: 500; font-size: 1.3rem; color: var(--text-muted); max-width: 650px; margin-bottom: 5px;">
                    Donde la pachanga de barrio en Ceuta se convierte en competición organizada.
                </h2>
                <p class="slide-desc" style="max-width: 600px; margin-top: 15px;">
                    Gracias por recorrer la arquitectura técnica del proyecto. Una aplicación web completa, segura y sin frameworks, construida desde cero.
                </p>

                <div style="display: flex; flex-wrap: wrap; justify-content: center; align-items: center;">
                    <a href="<?= url('') ?>" class="closing-cta">
                        <i class="bi bi-house-door-fill"></i> Volver al Inicio
                    </a>
                    <a href="https://github.com/Sadek2110" target="_blank" class="closing-cta secondary">
                        <i class="bi bi-github"></i> Ver en GitHub
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- Presentation Footer info -->
    <footer class="slide-footer">
        <div class="author-info">
            FastPlay - Presentación Técnica &nbsp;|&nbsp; Desarrollado por <a href="https://github.com/Sadek2110" target="_blank"><i class="bi bi-github"></i> Sadek2110</a>
        </div>
        <div class="slide-counter" id="slideCounter">
            01 / 08
        </div>
    </footer>

    <!-- Bottom Controls Panel -->
    <div class="controls-panel">
        <button class="nav-btn" onclick="prevSlide()" title="Anterior Slide (Flecha Izquierda)">
            <i class="bi bi-chevron-left"></i>
        </button>
        <div class="slide-dots" id="slideDots">
            <!-- Dots populated dynamically by JS -->
        </div>
        <button class="nav-btn" onclick="nextSlide()" title="Siguiente Slide (Flecha Derecha / Espacio)">
            <i class="bi bi-chevron-right"></i>
        </button>
        <div class="keyboard-tip">
            <span>Navega con</span> <kbd>←</kbd> <kbd>→</kbd> <span>o</span> <kbd>Espacio</kbd>
        </div>
    </div>

    <!-- Interactive script -->
    <script>
        // Slides data
        let currentSlideIdx = 0;
        const slides = document.querySelectorAll('.slide');
        const progressBar = document.getElementById('progressBar');
        const slideCounter = document.getElementById('slideCounter');
        const slideDotsContainer = document.getElementById('slideDots');
        
        let autoplayInterval = null;
        let isAutoplayActive = false;

        // Folders data for Slide 6 Interactive Explorer
        const folderStructure = [
            {
                name: "app/core/",
                icon: "bi-cpu-fill",
                type: "Núcleo del Framework",
                desc: "Es el motor interno de la aplicación. Se encarga de las tareas de bajo nivel como el despacho de rutas y las conexiones PDO base.",
                files: [
                    { name: "Router.php", desc: "Despacha peticiones HTTP, parsea controladores en kebab-case y gestiona los alias de URLs en español." },
                    { name: "Database.php", desc: "Conexión PDO, creador automático de tablas SQLite y base de datos semilla (seeds) del dominio." },
                    { name: "Controller.php", desc: "Clase abstracta de la que heredan los controladores. Gestiona autorizaciones de roles, renderizados, CSRF y variables de sesión." }
                ]
            },
            {
                name: "app/controllers/",
                icon: "bi-folder-symlink-fill",
                type: "Controladores (C)",
                desc: "Orquestan el flujo de datos. Capturan las peticiones del usuario, validan seguridad (CORS, CSRF, Token), interactúan con los Modelos y eligen la Vista a mostrar.",
                files: [
                    { name: "AuthController.php", desc: "Gestiona Login, Registro estándar, OAuth con Google y envío de tokens." },
                    { name: "TeamsController.php", desc: "Creación de equipos, invitación a jugadores y flujo de unión con capitán." },
                    { name: "MatchesController.php", desc: "Creación, confirmación, cancelación y finalización de partidos." },
                    { name: "DashboardController.php", desc: "Carga la pantalla privada agregando datos de notificaciones, equipos y perfiles." },
                    { name: "PresentationController.php", desc: "Maneja la carga y el renderizado sin layout de esta presentación técnica interactiva." }
                ]
            },
            {
                name: "app/models/",
                icon: "bi-database-fill-gear",
                type: "Modelos de Dominio (M)",
                desc: "Contienen las reglas de negocio y las consultas SQL preparadas seguras a través de PDO. Representan las entidades de la base de datos.",
                files: [
                    { name: "Usuario.php", desc: "Estadísticas del jugador, hashes de contraseña, registro Google e histórico de participación." },
                    { name: "Equipo.php", desc: "Control de plantillas, capitanes, validación de cuotas premium y listado de miembros." },
                    { name: "Liga.php", desc: "Clasificaciones de las ligas, distinción Pro/Amistosa y recuento de partidos jugados." },
                    { name: "Partido.php", desc: "Gestión de convocados (titulares/suplentes), horarios, campos y marcadores." }
                ]
            },
            {
                name: "app/views/",
                icon: "bi-layout-text-window-reverse",
                type: "Vistas en HTML/PHP (V)",
                desc: "Vistas puras que renderizan los datos. No contienen lógica de negocio compleja, solo bucles de pintado e inyecciones seguras escapadas con htmlspecialchars.",
                files: [
                    { name: "layouts/main.php", desc: "Plantilla envolvente base con los scripts, navbar flotante y estructura general." },
                    { name: "home/index.php", desc: "Landing page del sitio con video hero en bucle y estadísticas de Ceuta." },
                    { name: "dashboard/index.php", desc: "Panel de usuario con la carta de estadísticas de estilo FIFA." },
                    { name: "campos/index.php", desc: "Pantalla dividida con el mapa interactivo de Leaflet y listado de tarjetas." }
                ]
            },
            {
                name: "public/",
                icon: "bi-globe",
                type: "Carpeta Pública Web",
                desc: "El único directorio expuesto a Internet en el servidor web. Incrementa la seguridad al aislar todo el código lógico del acceso HTTP directo.",
                files: [
                    { name: "index.php", desc: "Front Controller. Recibe todas las peticiones del sitio y las desvía al Router." },
                    { name: "css/app.css", desc: "Hojas de estilo del sistema de diseño (Palette, Typography, Glassmorphic cards)." },
                    { name: "js/theme.js", desc: "Gestión del contraste y modo claro/oscuro persistido en localStorage." },
                    { name: "video/hero.webm", desc: "Video corporativo en segundo plano de la landing page." }
                ]
            },
            {
                name: "tests/",
                icon: "bi-shield-check-fill",
                type: "Pruebas Unitarias",
                desc: "Conjunto de tests para automatizar el control de regresión y calidad mediante PHPUnit, operando sobre una base de datos SQLite de test aislada.",
                files: [
                    { name: "EmailVerificationTest.php", desc: "Valida flujos de verificación de correo y tokens OAuth de Google." },
                    { name: "ProductionConfigurationTest.php", desc: "Asegura integridad de dependencias de Composer y variables críticas de Stripe." }
                ]
            }
        ];

        // Initialize dots and dynamic explorer
        function initPresentation() {
            // Generate dots
            slideDotsContainer.innerHTML = '';
            slides.forEach((_, idx) => {
                const dot = document.createElement('div');
                dot.classList.add('dot');
                if (idx === 0) dot.classList.add('active');
                dot.addEventListener('click', () => goToSlide(idx));
                slideDotsContainer.appendChild(dot);
            });

            // Initialize explorer
            initExplorer();
            
            // Update progress
            updateProgress();
        }

        // Initialize Explorer Tree
        function initExplorer() {
            const treeContainer = document.getElementById('explorerTree');
            treeContainer.innerHTML = '';
            
            folderStructure.forEach((folder, idx) => {
                const item = document.createElement('div');
                item.className = `tree-item ${idx === 0 ? 'active' : ''}`;
                item.innerHTML = `
                    <span class="folder-name">
                        <i class="bi ${folder.icon} folder-icon"></i>
                        ${folder.name}
                    </span>
                    <i class="bi bi-chevron-right chevron"></i>
                `;
                item.onclick = () => selectFolder(idx, item);
                treeContainer.appendChild(item);
            });

            // Select first folder by default
            renderFolderDetails(0);
        }

        // Switch active folder in explorer
        function selectFolder(idx, element) {
            document.querySelectorAll('.tree-item').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
            renderFolderDetails(idx);
        }

        // Render selected folder details
        function renderFolderDetails(idx) {
            const folder = folderStructure[idx];
            const contentContainer = document.getElementById('explorerContent');
            
            let filesHtml = '';
            folder.files.forEach(file => {
                const isPhp = file.name.endsWith('.php');
                const fileIcon = isPhp ? 'bi-filetype-php php-icon' : 'bi-file-code-fill';
                filesHtml += `
                    <div class="file-pill">
                        <i class="bi ${fileIcon}"></i>
                        <div style="display: flex; flex-direction: column; text-align:left;">
                            <span style="font-weight: 600; color: white;">${file.name}</span>
                            <span style="font-size: 0.72rem; color: var(--text-muted); font-family: var(--font-main);">${file.desc}</span>
                        </div>
                    </div>
                `;
            });

            contentContainer.innerHTML = `
                <div>
                    <div class="explorer-detail-header">
                        <div class="explorer-detail-title">
                            ${folder.name} <span>${folder.type}</span>
                        </div>
                    </div>
                    <p class="explorer-detail-desc">${folder.desc}</p>
                </div>
                <div>
                    <div class="explorer-files-title">Componentes y archivos representativos:</div>
                    <div class="explorer-files-list">
                        ${filesHtml}
                    </div>
                </div>
            `;
        }

        // Slide navigation
        function updateProgress() {
            const percent = (currentSlideIdx / (slides.length - 1)) * 100;
            progressBar.style.width = `${percent}%`;
            slideCounter.textContent = `${String(currentSlideIdx + 1).padStart(2, '0')} / ${String(slides.length).padStart(2, '0')}`;
            
            // Update dots
            const dots = document.querySelectorAll('.dot');
            dots.forEach((dot, idx) => {
                if (idx === currentSlideIdx) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }

        function goToSlide(idx) {
            if (idx < 0 || idx >= slides.length) return;

            const goingForward = idx > currentSlideIdx;

            // Apply directional exit classes for a smooth horizontal transition
            slides.forEach((slide, sIdx) => {
                slide.classList.remove('active', 'prev', 'exit-left', 'exit-right');
                if (sIdx !== idx) {
                    if (sIdx < idx) {
                        slide.classList.add('exit-left');
                    } else {
                        slide.classList.add('exit-right');
                    }
                }
            });

            currentSlideIdx = idx;
            const activeSlide = slides[currentSlideIdx];
            activeSlide.classList.add('active');

            // Trigger animated counters if this slide contains any
            const counters = activeSlide.querySelectorAll('.counter');
            if (counters.length) animateCounters(counters);

            updateProgress();
        }

        // Animated number counters (count-up)
        function animateCounters(counters) {
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target'), 10) || 0;
                const duration = 1200;
                const startTime = performance.now();

                function tick(now) {
                    const progress = Math.min((now - startTime) / duration, 1);
                    // easeOutCubic for a natural deceleration
                    const eased = 1 - Math.pow(1 - progress, 3);
                    counter.textContent = Math.round(eased * target);
                    if (progress < 1) requestAnimationFrame(tick);
                    else counter.textContent = target;
                }
                counter.textContent = '0';
                requestAnimationFrame(tick);
            });
        }

        function nextSlide() {
            if (currentSlideIdx < slides.length - 1) {
                goToSlide(currentSlideIdx + 1);
            } else {
                // Loop back to start if autoplaying or manual end click
                goToSlide(0);
            }
        }

        function prevSlide() {
            if (currentSlideIdx > 0) {
                goToSlide(currentSlideIdx - 1);
            }
        }

        // Autoplay toggle
        function toggleAutoplay() {
            const btn = document.getElementById('autoplayBtn');
            if (isAutoplayActive) {
                clearInterval(autoplayInterval);
                isAutoplayActive = false;
                btn.innerHTML = '<i class="bi bi-play-circle-fill"></i> Autoplay';
                btn.style.borderColor = 'rgba(255, 255, 255, 0.08)';
                btn.style.color = 'white';
            } else {
                isAutoplayActive = true;
                btn.innerHTML = '<i class="bi bi-pause-circle-fill"></i> Pausar';
                btn.style.borderColor = 'var(--neon)';
                btn.style.color = 'var(--neon-light)';
                autoplayInterval = setInterval(() => {
                    nextSlide();
                }, 6000); // 6 seconds per slide
            }
        }

        // Fullscreen toggle
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    alert(`Error intentando activar pantalla completa: ${err.message}`);
                });
            } else {
                document.exitFullscreen();
            }
        }

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight' || e.key === ' ') {
                e.preventDefault();
                nextSlide();
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                prevSlide();
            }
        });

        // Initialize on load
        window.onload = () => {
            initPresentation();
        };
    </script>
</body>
</html>
