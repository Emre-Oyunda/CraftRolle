<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_login();
$user = current_user();

$navLinks = [
    ['label' => '📚 Kitaplar', 'href' => 'books.php'],
    ['label' => '📝 Notlar', 'href' => 'notes.php'],
    ['label' => '🎨 Kapak', 'href' => 'designer_cover.php'],
    ['label' => '🗺️ Harita', 'href' => 'designer_map.php'],
    ['label' => '⚙️ Panel', 'href' => 'dashboard.php'],
    ['label' => '✨ Eğlence', 'href' => 'eglence.php'],
    ['label' => '🚪 Çıkış', 'href' => 'logout.php'],
];
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(APP_NAME) ?> — Harita Tasarım Stüdyosu</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
  body.map-designer {
    font-family: 'Inter', 'Segoe UI', Tahoma, sans-serif;
    min-height: 100vh;
    margin: 0;
    padding: 38px 20px 60px;
    color: #1d2331;
    background:
      radial-gradient(circle at 20% 15%, rgba(209, 244, 255, 0.85), transparent 55%),
      radial-gradient(circle at 80% 10%, rgba(254, 228, 255, 0.7), transparent 50%),
      linear-gradient(135deg, #b0d8ff 0%, #f0f4ff 35%, #ffe6f7 100%);
    overflow-x: hidden;
    transition: background 0.5s ease, color 0.45s ease;
  }

  body.map-designer.dark-theme {
    color: #e7edff;
    background:
      radial-gradient(circle at 15% 10%, rgba(91, 89, 255, 0.25), transparent 55%),
      radial-gradient(circle at 85% 12%, rgba(255, 99, 171, 0.22), transparent 60%),
      linear-gradient(135deg, #090e22 0%, #111735 50%, #1b0f2e 100%);
  }

  body.map-designer::before,
  body.map-designer::after {
    content: '';
    position: fixed;
    width: 480px;
    height: 480px;
    border-radius: 50%;
    z-index: 0;
    filter: blur(140px);
    opacity: 0.45;
    pointer-events: none;
    transition: opacity 0.5s ease;
  }

  body.map-designer::before {
    top: -120px;
    left: -120px;
    background: linear-gradient(135deg, rgba(99, 179, 237, 0.75), rgba(255, 168, 211, 0.55));
  }

  body.map-designer::after {
    bottom: -160px;
    right: -140px;
    background: linear-gradient(135deg, rgba(77, 62, 198, 0.65), rgba(161, 90, 255, 0.5));
  }

  body.map-designer.dark-theme::before,
  body.map-designer.dark-theme::after {
    opacity: 0.18;
  }

  .map-designer .container {
    position: relative;
    z-index: 1;
    max-width: 1180px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 22px;
  }

  .glass-card {
    background: rgba(255, 255, 255, 0.78);
    border-radius: 26px;
    border: 1px solid rgba(255, 255, 255, 0.6);
    box-shadow: 0 24px 55px rgba(124, 122, 255, 0.18);
    backdrop-filter: blur(24px);
    padding: 26px 32px;
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
  }

  .glass-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 32px 70px rgba(124, 122, 255, 0.22);
  }

  body.map-designer.dark-theme .glass-card {
    background: rgba(15, 18, 38, 0.78);
    border: 1px solid rgba(108, 87, 255, 0.35);
    box-shadow: 0 26px 65px rgba(4, 6, 18, 0.6);
  }

  .top-shell {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
  }

  .brand-block {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .brand-link {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    font-size: 1.6rem;
    font-weight: 700;
    color: inherit;
    text-decoration: none;
  }

  .brand-link span.brand {
    background: linear-gradient(135deg, #5a7dff, #c56cff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .brand-tagline {
    font-size: 0.95rem;
    opacity: 0.75;
    max-width: 520px;
    line-height: 1.5;
  }

  .header-actions {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
  }

  .theme-toggle {
    display: inline-flex;
    align-items: center;
    gap: 14px;
    padding: 10px 18px 10px 12px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.7);
    background: rgba(255, 255, 255, 0.68);
    color: #2b2f45;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 16px 32px rgba(111, 155, 255, 0.28);
    transition: transform 0.25s ease, box-shadow 0.3s ease, border-color 0.3s ease;
  }

  .theme-toggle:hover {
    transform: translateY(-2px);
    box-shadow: 0 22px 46px rgba(111, 155, 255, 0.32);
  }

  .toggle-track {
    position: relative;
    width: 56px;
    height: 28px;
    border-radius: 999px;
    background: linear-gradient(135deg, rgba(80, 187, 255, 0.55), rgba(144, 96, 255, 0.55));
    border: 1px solid rgba(255, 255, 255, 0.8);
    padding: 3px;
  }

  .toggle-thumb {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #fff;
    display: grid;
    place-items: center;
    font-size: 15px;
    transition: transform 0.4s ease, background 0.4s ease;
  }

  .theme-labels {
    display: flex;
    flex-direction: column;
    line-height: 1.1;
  }

  .user-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.65);
    border: 1px solid rgba(255, 255, 255, 0.55);
    font-weight: 600;
  }

  .nav-links {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
  }

  .ghost-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 16px;
    border-radius: 999px;
    border: 1px solid rgba(43, 47, 69, 0.18);
    background: transparent;
    color: inherit;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.25s ease, transform 0.25s ease;
  }

  .ghost-btn:hover {
    background: rgba(255, 255, 255, 0.32);
    transform: translateY(-1px);
  }

  body.map-designer.dark-theme .theme-toggle {
    background: rgba(20, 24, 52, 0.8);
    color: #e7edff;
    border: 1px solid rgba(108, 87, 255, 0.45);
    box-shadow: 0 18px 48px rgba(0, 0, 0, 0.55);
  }

  body.map-designer.dark-theme .toggle-track {
    background: linear-gradient(135deg, rgba(108, 87, 255, 0.55), rgba(34, 28, 74, 0.55));
    border-color: rgba(108, 87, 255, 0.45);
  }

  body.map-designer.dark-theme .toggle-thumb {
    transform: translateX(26px) rotate(360deg);
    background: #181b39;
  }

  body.map-designer.dark-theme .user-chip {
    background: rgba(20, 24, 52, 0.8);
    border: 1px solid rgba(108, 87, 255, 0.3);
  }

  body.map-designer.dark-theme .ghost-btn {
    border: 1px solid rgba(231, 237, 255, 0.18);
  }

  .map-dashboard {
    display: flex;
    flex-direction: column;
    gap: 26px;
  }

  .map-intro {
    display: flex;
    flex-wrap: wrap;
    gap: 18px 32px;
    justify-content: space-between;
    align-items: flex-end;
  }

  .map-intro h1 {
    font-size: clamp(1.75rem, 2.4vw, 2.4rem);
    margin: 0;
  }

  .map-intro p {
    max-width: 620px;
    margin: 0;
    font-size: 1rem;
    line-height: 1.6;
    opacity: 0.85;
  }

  .stat-row {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
  }

  .stat-card {
    min-width: 160px;
    padding: 12px 18px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.5);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4);
    display: grid;
    gap: 2px;
  }

  .stat-card span.label {
    font-size: 0.85rem;
    opacity: 0.7;
  }

  .stat-card strong {
    font-size: 1.4rem;
  }

  .map-grid {
    display: grid;
    gap: 28px;
    grid-template-columns: minmax(0, 1.25fr) minmax(0, 0.75fr);
    align-items: start;
  }

  .map-stage {
    position: relative;
    border-radius: 24px;
    background: linear-gradient(160deg, rgba(33, 105, 146, 0.55), rgba(40, 122, 166, 0.35));
    border: 1px solid rgba(255, 255, 255, 0.55);
    box-shadow: 0 22px 52px rgba(33, 105, 146, 0.25);
    padding: 18px;
    overflow: hidden;
  }

  body.map-designer.dark-theme .map-stage {
    background: linear-gradient(170deg, rgba(12, 29, 54, 0.8), rgba(33, 60, 94, 0.55));
    border: 1px solid rgba(108, 87, 255, 0.28);
    box-shadow: 0 28px 60px rgba(3, 8, 22, 0.72);
  }

  .map-stage::after {
    content: '';
    position: absolute;
    inset: 18px;
    border-radius: 20px;
    pointer-events: none;
    background-image: linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
    background-size: 40px 40px;
    mix-blend-mode: overlay;
  }

  #map-canvas {
    display: block;
    width: min(100%, 960px);
    height: auto;
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, 0.65);
    box-shadow: 0 12px 32px rgba(15, 43, 78, 0.28);
    background: radial-gradient(circle at 35% 30%, rgba(255, 255, 255, 0.18), transparent 65%),
      #1b4b68;
    cursor: crosshair;
    transition: transform 0.35s ease, box-shadow 0.35s ease;
  }

  .map-stage.zoomed #map-canvas {
    transform: scale(1.12);
    box-shadow: 0 18px 48px rgba(15, 43, 78, 0.32);
  }

  .map-overlay-info {
    position: absolute;
    top: 22px;
    left: 28px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.85);
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.35);
    pointer-events: none;
  }

  .map-compass {
    position: absolute;
    top: 26px;
    right: 24px;
    width: 68px;
    height: 68px;
    border-radius: 50%;
    background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.55), rgba(255, 255, 255, 0.12));
    border: 1px solid rgba(255, 255, 255, 0.5);
    display: grid;
    place-items: center;
    font-weight: 700;
    color: #1d2331;
  }

  body.map-designer.dark-theme .map-compass {
    color: #e7edff;
    background: radial-gradient(circle at 30% 30%, rgba(34, 54, 104, 0.55), rgba(12, 30, 58, 0.42));
  }

  .map-controls {
    display: flex;
    flex-direction: column;
    gap: 22px;
  }

  .control-section {
    padding: 18px 20px;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.5);
    box-shadow: 0 16px 38px rgba(111, 155, 255, 0.12);
    display: grid;
    gap: 14px;
  }

  body.map-designer.dark-theme .control-section {
    background: rgba(20, 24, 52, 0.82);
    border: 1px solid rgba(108, 87, 255, 0.22);
    box-shadow: 0 18px 40px rgba(3, 8, 22, 0.6);
  }

  .control-section h2 {
    margin: 0;
    font-size: 1.05rem;
  }

  .tool-switch {
    display: inline-flex;
    padding: 4px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.65);
    border: 1px solid rgba(43, 47, 69, 0.08);
    gap: 4px;
  }

  .tool-switch button {
    border: none;
    border-radius: 999px;
    padding: 10px 16px;
    font-weight: 600;
    background: transparent;
    color: inherit;
    cursor: pointer;
    transition: background 0.25s ease, box-shadow 0.25s ease;
  }

  .tool-switch button.is-active {
    background: linear-gradient(135deg, rgba(91, 127, 255, 0.85), rgba(160, 98, 255, 0.85));
    color: #f6f8ff;
    box-shadow: 0 12px 24px rgba(111, 155, 255, 0.28);
  }

  body.map-designer.dark-theme .tool-switch {
    background: rgba(28, 33, 70, 0.78);
    border: 1px solid rgba(108, 87, 255, 0.22);
  }

  .palette-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(70px, 1fr));
    gap: 10px;
  }

  .palette-btn {
    border: none;
    border-radius: 14px;
    padding: 18px 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    font-size: 0.86rem;
    font-weight: 600;
    color: #1d2331;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
  }

  .palette-btn span.sample {
    width: 42px;
    height: 25px;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.55);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.35);
  }

  .palette-btn.is-active {
    transform: translateY(-2px);
    box-shadow: 0 16px 32px rgba(111, 155, 255, 0.22);
  }

  body.map-designer.dark-theme .palette-btn {
    color: #e7edff;
    border: 1px solid rgba(108, 87, 255, 0.15);
  }

  .control-section label {
    font-weight: 600;
    font-size: 0.9rem;
  }

  .control-section select,
  .control-section input[type="file"] {
    width: 100%;
    border-radius: 14px;
    border: 1px solid rgba(43, 47, 69, 0.18);
    background: rgba(255, 255, 255, 0.8);
    padding: 11px 14px;
    font-size: 0.95rem;
    color: inherit;
    transition: border-color 0.25s ease, box-shadow 0.25s ease;
  }

  .control-section select:focus,
  .control-section input[type="file"]:focus {
    outline: none;
    border-color: rgba(111, 155, 255, 0.65);
    box-shadow: 0 0 0 4px rgba(111, 155, 255, 0.25);
  }

  .seg {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .seg button {
    border-radius: 12px;
    border: none;
    padding: 10px 16px;
    font-weight: 600;
    cursor: pointer;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.85), rgba(231, 238, 255, 0.85));
    color: #1d2331;
    box-shadow: 0 12px 24px rgba(111, 155, 255, 0.22);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
  }

  .seg button:hover {
    transform: translateY(-1px);
    box-shadow: 0 16px 32px rgba(111, 155, 255, 0.28);
  }

  .seg button.danger {
    background: linear-gradient(135deg, #ff8e9a, #ff6b7f);
    color: #fff;
    box-shadow: 0 16px 28px rgba(255, 108, 137, 0.32);
  }

  body.map-designer.dark-theme .seg button {
    background: linear-gradient(135deg, rgba(34, 41, 84, 0.9), rgba(48, 60, 112, 0.9));
    color: #e7edff;
    box-shadow: 0 18px 32px rgba(3, 8, 22, 0.55);
  }

  .map-legend {
    display: grid;
    gap: 18px;
  }

  .legend-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  }

  .legend-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.68);
    border: 1px solid rgba(255, 255, 255, 0.45);
  }

  body.map-designer.dark-theme .legend-item {
    background: rgba(24, 28, 62, 0.82);
    border: 1px solid rgba(108, 87, 255, 0.22);
  }

  .legend-swatch {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.6);
  }

  .map-tips {
    display: grid;
    gap: 14px;
    font-size: 0.95rem;
    opacity: 0.82;
  }

  .bottom-nav {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
    margin-top: 12px;
  }

  .bottom-nav a {
    padding: 10px 20px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.8);
    box-shadow: 0 6px 18px rgba(111, 155, 255, 0.2);
    text-decoration: none;
    color: #1d2331;
    font-weight: 600;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
  }

  .bottom-nav a:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(111, 155, 255, 0.28);
  }

  body.map-designer.dark-theme .bottom-nav a {
    background: rgba(24, 28, 62, 0.78);
    border: 1px solid rgba(108, 87, 255, 0.25);
    color: #e7edff;
    box-shadow: 0 18px 36px rgba(3, 8, 22, 0.55);
  }

  .footer-note {
    text-align: center;
    font-size: 0.85rem;
    opacity: 0.7;
    margin-top: 6px;
  }

  .result-hint {
    font-size: 0.9rem;
    min-height: 24px;
    color: rgba(29, 35, 49, 0.78);
  }

  body.map-designer.dark-theme .result-hint {
    color: rgba(231, 237, 255, 0.78);
  }

  @media (max-width: 1140px) {
    .map-grid {
      grid-template-columns: 1fr;
    }

    .map-controls {
      flex-direction: row;
      flex-wrap: wrap;
    }

    .control-section {
      flex: 1 1 320px;
    }
  }

  @media (max-width: 768px) {
    body.map-designer {
      padding: 28px 16px 48px;
    }

    .glass-card {
      padding: 22px;
    }

    .header-actions {
      width: 100%;
      justify-content: space-between;
    }

    .theme-toggle {
      width: 100%;
      justify-content: center;
    }

    .user-chip,
    .nav-links {
      width: 100%;
      justify-content: center;
    }

    .map-stage {
      padding: 16px;
    }

    .map-stage::after {
      inset: 12px;
    }

    .map-controls {
      flex-direction: column;
    }

    .control-section {
      flex: 1 1 auto;
    }
  }

  @media (max-width: 520px) {
    .palette-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    #map-canvas {
      width: 100%;
    }
  }
  </style>
</head>
<body class="map-designer">
  <div class="container">
    <div class="glass-card top-shell">
      <div class="brand-block">
        <a class="brand-link" href="<?= base_url('index.php') ?>">
          <span class="brand-icon">🗺️</span>
          <span class="brand"><?= e(APP_NAME) ?></span>
        </a>
        <p class="brand-tagline">Dağ geçitlerinden sahil kasabalarına, hikâyene gerçekçi dokular katan Craftrolle Harita Stüdyosu.</p>
      </div>
      <div class="header-actions">
        <button class="theme-toggle" id="theme-toggle" type="button" aria-pressed="false">
          <span class="toggle-track">
            <span class="toggle-thumb" id="theme-thumb">🌤️</span>
          </span>
          <span class="theme-labels">
            <span class="theme-name" id="theme-label">Gündüz</span>
            <span class="theme-sub">Atmosfer</span>
          </span>
        </button>
        <span class="user-chip">👋 <?= $user ? e($user['username']) : 'Misafir' ?></span>
        <div class="nav-links">
          <?php foreach ($navLinks as $item): ?>
            <a class="ghost-btn" href="<?= base_url($item['href']) ?>"><?= $item['label'] ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="glass-card map-dashboard">
      <div class="map-intro">
        <div>
          <h1>Gerçekçi Harita Tasarım Panosu</h1>
          <p>Çok katmanlı arazi boyama, simgelerle yerleşim planlama, yağmur efektleriyle atmosfer ekleme ve tek tıkla dışa aktarım. Roman diyarınız bir anda nefes alsın.</p>
        </div>
        <div class="stat-row">
          <div class="stat-card">
            <span class="label">Arazi paleti</span>
            <strong id="stat-terrain">0</strong>
            <span class="small">aktif doku</span>
          </div>
          <div class="stat-card">
            <span class="label">Yerleşim</span>
            <strong id="stat-objects">0</strong>
            <span class="small">ikon yerleştirildi</span>
          </div>
          <div class="stat-card">
            <span class="label">Atmosfer</span>
            <strong id="stat-weather">Açık</strong>
            <span class="small">yağmur durumu</span>
          </div>
        </div>
      </div>

      <div class="map-grid">
        <div class="map-stage">
          <div class="map-overlay-info">
            <span>🖱️ Sürükle — boyayın</span>
            <span>⌥ / Alt — hızlı sil</span>
            <span>Çift tık ✨ ızgarayı yaklaştırır</span>
          </div>
          <div class="map-compass">N</div>
          <canvas id="map-canvas" width="960" height="640"></canvas>
        </div>

        <aside class="map-controls">
          <div class="control-section">
            <h2>Mod Seçimi</h2>
            <div class="tool-switch">
              <button id="tool-terrain" type="button">Arazi</button>
              <button id="tool-object" type="button">Yerleşim</button>
              <button id="tool-erase" type="button">Silgi</button>
            </div>
          </div>

          <div class="control-section">
            <h2>Arazi Paleti</h2>
            <div id="palette" class="palette-grid"></div>
          </div>

          <div class="control-section">
            <h2>Yerleşim İkonları</h2>
            <?php csrf_field(); ?>
            <label for="obj-type">Obje türü</label>
            <select id="obj-type">
              <option value="tree">🌲 Kuzey Ormanı</option>
              <option value="village">🏘️ Köy</option>
              <option value="castle">🏰 Kale</option>
              <option value="harbor">⚓ Liman</option>
              <option value="tower">🗼 Gözetleme</option>
              <option value="monument">🗿 Anıt</option>
              <option value="ship">🚢 Filo</option>
            </select>
            <label for="obj-size">Obje boyutu</label>
            <select id="obj-size">
              <option value="1">Simgesel</option>
              <option value="1.4">Orta</option>
              <option value="1.8">Büyük</option>
            </select>
          </div>

          <div class="control-section">
            <h2>Hızlı İşlemler</h2>
            <div class="seg">
              <button id="undo" type="button">⤺ Geri Al</button>
              <button id="redo" type="button">⤻ İleri Al</button>
            </div>
            <div class="seg">
              <button id="toggle-rain" type="button">🌧️ Yağmur Efekti</button>
              <button id="focus-water" type="button">🌊 Kıyı Oluştur</button>
            </div>
            <div class="seg">
              <button id="save-png" type="button">🖼️ PNG Kaydet</button>
              <button id="save-json" type="button">💾 JSON Kaydet</button>
            </div>
          </div>

          <div class="control-section">
            <label for="load-json">JSON yükle</label>
            <input type="file" id="load-json" accept="application/json">
            <div class="seg">
              <button id="clear-map" type="button" class="danger">Haritayı Temizle</button>
            </div>
            <div id="map-result" class="result-hint"></div>
          </div>
        </aside>
      </div>
    </div>

    <div class="glass-card map-legend">
      <h2>Atlas Rehberi</h2>
      <div class="legend-grid">
        <div class="legend-item">
          <span class="legend-swatch" style="background: linear-gradient(135deg, #0a365b, #0f4f76);"></span>
          <span>Derin Deniz</span>
        </div>
        <div class="legend-item">
          <span class="legend-swatch" style="background: linear-gradient(135deg, #4fb7d6, #9be8f7);"></span>
          <span>Kıyı & Lagün</span>
        </div>
        <div class="legend-item">
          <span class="legend-swatch" style="background: linear-gradient(135deg, #7ecb78, #4da55a);"></span>
          <span>Ovalar & Çayırlar</span>
        </div>
        <div class="legend-item">
          <span class="legend-swatch" style="background: linear-gradient(135deg, #3c7048, #274f32);"></span>
          <span>Sık Orman</span>
        </div>
        <div class="legend-item">
          <span class="legend-swatch" style="background: linear-gradient(135deg, #8b6d4c, #d8b894);"></span>
          <span>Dağ & Sırt</span>
        </div>
        <div class="legend-item">
          <span class="legend-swatch" style="background: linear-gradient(135deg, #d1aa6d, #f5d48f);"></span>
          <span>Çöl & Bozkır</span>
        </div>
        <div class="legend-item">
          <span class="legend-swatch" style="background: linear-gradient(135deg, #e9f4ff, #b8d7ff);"></span>
          <span>Buzullar</span>
        </div>
      </div>
      <div class="map-tips">
        <strong>İpucu:</strong>
        <span>• Kıyı hattını önce "Lagün" ile boyayıp ardından "Orman" ve "Dağ" katmanları ile derinlik verin.</span>
        <span>• JSON dışa aktarımını kullanarak haritaları kitap bölümleri arasında paylaşın.</span>
        <span>• "Kıyı Oluştur" butonu, seçili noktayı otomatik olarak sahil dokusuna dönüştürüp doğal bir görünüm sağlar.</span>
      </div>
    </div>

    <div class="bottom-nav">
      <a href="<?= base_url('books.php') ?>">📚 Kitaplar</a>
      <a href="<?= base_url('notes.php') ?>">📝 Notlar</a>
      <a href="<?= base_url('designer_cover.php') ?>">🎨 Kapak</a>
      <a href="<?= base_url('designer_map.php') ?>">🗺️ Harita</a>
    </div>
    <div class="footer-note">© <?= date('Y') ?> <?= e(APP_NAME) ?> · Craftrolle harita stüdyosu</div>
  </div>

  <script src="../assets/js/map_designer_pro.js"></script>
  <script>
  (function() {
    const themeToggle = document.getElementById('theme-toggle');
    const themeThumb = document.getElementById('theme-thumb');
    const themeLabel = document.getElementById('theme-label');
    const storageKey = 'craft-map-theme';

    if (!themeToggle) { return; }

    const applyTheme = (mode) => {
      const isDark = mode === 'dark';
      document.body.classList.toggle('dark-theme', isDark);
      themeThumb.textContent = isDark ? '🌙' : '🌤️';
      themeLabel.textContent = isDark ? 'Gece' : 'Gündüz';
      themeToggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
      localStorage.setItem(storageKey, mode);
    };

    const stored = localStorage.getItem(storageKey);
    applyTheme(stored === 'dark' ? 'dark' : 'light');

    themeToggle.addEventListener('click', () => {
      const nextMode = document.body.classList.contains('dark-theme') ? 'light' : 'dark';
      applyTheme(nextMode);
    });
  })();
  </script>
</body>
</html>
