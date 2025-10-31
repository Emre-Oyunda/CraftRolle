<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_login();

$user = current_user();
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(APP_NAME) ?> — Harita Tasarım</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    :root {
      --map-bg-light: linear-gradient(135deg, #c0e5ff 0%, #f4f6ff 45%, #ffe6f5 100%);
      --map-bg-dark: linear-gradient(135deg, #070b1a 0%, #121c33 50%, #1a1334 100%);
      --card-bg-light: rgba(255, 255, 255, 0.78);
      --card-bg-dark: rgba(23, 29, 57, 0.78);
      --card-border-light: rgba(255, 255, 255, 0.65);
      --card-border-dark: rgba(114, 105, 231, 0.35);
      --accent: #2c3f8d;
      --accent-soft: #4f70ff;
      --text-muted: rgba(18, 28, 57, 0.7);
      --text-muted-dark: rgba(231, 235, 255, 0.7);
      --danger: #ff6b7f;
    }

    body.map-page {
      font-family: 'Inter', 'Segoe UI', Tahoma, sans-serif;
      min-height: 100vh;
      margin: 0;
      padding: 40px 18px 56px;
      color: #11162b;
      background: var(--map-bg-light);
      transition: background 0.45s ease, color 0.45s ease;
    }

    body.map-page::before,
    body.map-page::after {
      content: '';
      position: fixed;
      width: 480px;
      height: 480px;
      border-radius: 50%;
      filter: blur(140px);
      opacity: 0.32;
      z-index: 0;
      pointer-events: none;
    }

    body.map-page::before {
      top: -160px;
      left: -120px;
      background: linear-gradient(135deg, rgba(109, 168, 255, 0.75), rgba(255, 193, 238, 0.65));
    }

    body.map-page::after {
      bottom: -180px;
      right: -140px;
      background: linear-gradient(135deg, rgba(79, 62, 226, 0.6), rgba(255, 140, 172, 0.55));
    }

    body.map-page.dark-theme {
      color: #e6ecff;
      background: var(--map-bg-dark);
    }

    body.map-page.dark-theme::before,
    body.map-page.dark-theme::after {
      opacity: 0.18;
    }

    .page-shell {
      position: relative;
      z-index: 1;
      max-width: 1100px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      gap: 22px;
    }

    .glass-card {
      background: var(--card-bg-light);
      border: 1px solid var(--card-border-light);
      border-radius: 24px;
      padding: 26px 28px;
      box-shadow: 0 24px 58px rgba(92, 120, 217, 0.18);
      backdrop-filter: blur(22px);
      transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
    }

    .glass-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 30px 72px rgba(92, 120, 217, 0.24);
    }

    body.map-page.dark-theme .glass-card {
      background: var(--card-bg-dark);
      border-color: var(--card-border-dark);
      box-shadow: 0 30px 62px rgba(4, 6, 24, 0.65);
    }

    .map-header {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 18px;
    }

    .brand-link {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      font-size: 1.6rem;
      font-weight: 700;
      text-decoration: none;
      color: inherit;
    }

    .brand-link span.brand {
      background: linear-gradient(135deg, #4f70ff, #c06bff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .nav-links {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      align-items: center;
      font-size: 0.95rem;
    }

    .nav-links a {
      padding: 8px 14px;
      border-radius: 999px;
      text-decoration: none;
      color: inherit;
      background: rgba(255, 255, 255, 0.65);
      border: 1px solid rgba(255, 255, 255, 0.45);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .nav-links a:hover {
      transform: translateY(-2px);
      box-shadow: 0 14px 24px rgba(110, 136, 255, 0.28);
    }

    body.map-page.dark-theme .nav-links a {
      background: rgba(27, 33, 69, 0.78);
      border-color: rgba(114, 105, 231, 0.28);
    }

    .badge {
      padding: 6px 10px;
      border-radius: 10px;
      background: rgba(255, 255, 255, 0.7);
      border: 1px solid rgba(255, 255, 255, 0.5);
      font-weight: 600;
    }

    body.map-page.dark-theme .badge {
      background: rgba(23, 29, 57, 0.68);
      border-color: rgba(114, 105, 231, 0.28);
    }

    .map-main h2 {
      margin: 0 0 6px;
      font-size: clamp(1.8rem, 2.1vw, 2.2rem);
    }

    .map-main p.lead {
      margin: 0;
      color: var(--text-muted);
      line-height: 1.6;
    }

    body.map-page.dark-theme .map-main p.lead {
      color: var(--text-muted-dark);
    }

    .map-layout {
      display: grid;
      grid-template-columns: minmax(0, 1fr) minmax(260px, 0.85fr);
      gap: 28px;
      margin-top: 24px;
    }

    .map-stage {
      position: relative;
      border-radius: 20px;
      background: rgba(15, 43, 78, 0.22);
      border: 1px solid rgba(255, 255, 255, 0.55);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 24px 50px rgba(46, 82, 133, 0.25);
      padding: 16px;
      overflow: hidden;
    }

    body.map-page.dark-theme .map-stage {
      background: rgba(12, 22, 44, 0.55);
      border-color: rgba(116, 105, 231, 0.32);
      box-shadow: 0 30px 58px rgba(5, 8, 20, 0.72);
    }

    .canvas-wrap {
      position: relative;
      border-radius: 16px;
      overflow: hidden;
      background: linear-gradient(135deg, #1f567a, #0b2f47);
    }

    #map-canvas {
      display: block;
      width: min(100%, 960px);
      height: auto;
    }

    .canvas-hint {
      position: absolute;
      top: 16px;
      left: 16px;
      padding: 8px 14px;
      border-radius: 12px;
      background: rgba(0, 0, 0, 0.45);
      color: #f4f7ff;
      font-size: 0.9rem;
      letter-spacing: 0.01em;
      display: flex;
      flex-direction: column;
      gap: 2px;
      pointer-events: none;
    }

    .map-panel {
      display: grid;
      gap: 18px;
    }

    .tool-section {
      border-radius: 18px;
      padding: 18px 20px;
      background: rgba(255, 255, 255, 0.74);
      border: 1px solid rgba(255, 255, 255, 0.55);
      box-shadow: 0 16px 34px rgba(109, 140, 255, 0.14);
      display: grid;
      gap: 14px;
    }

    body.map-page.dark-theme .tool-section {
      background: rgba(25, 30, 62, 0.82);
      border-color: rgba(114, 105, 231, 0.26);
      box-shadow: 0 24px 44px rgba(5, 8, 22, 0.6);
    }

    .tool-section h3 {
      margin: 0;
      font-size: 1rem;
    }

    .seg {
      display: inline-flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .seg button,
    .seg .tool-btn {
      border: none;
      border-radius: 12px;
      padding: 10px 16px;
      font-weight: 600;
      cursor: pointer;
      background: linear-gradient(135deg, #ffffff, #edf0ff);
      color: #1c2544;
      box-shadow: 0 14px 28px rgba(95, 121, 221, 0.2);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .seg button:hover,
    .palette-btn:hover,
    .action-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 20px 32px rgba(95, 121, 221, 0.26);
    }

    .seg button.is-active {
      background: linear-gradient(135deg, #597aff, #8f6dff);
      color: #f5f7ff;
    }

    .palette-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(68px, 1fr));
      gap: 12px;
    }

    .palette-btn {
      border: none;
      border-radius: 16px;
      padding: 12px 10px;
      display: grid;
      place-items: center;
      gap: 6px;
      font-size: 0.85rem;
      font-weight: 600;
      color: inherit;
      background: rgba(255, 255, 255, 0.8);
      box-shadow: 0 10px 22px rgba(95, 121, 221, 0.18);
      cursor: pointer;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .palette-btn .sample {
      width: 38px;
      height: 24px;
      border-radius: 10px;
      border: 1px solid rgba(255, 255, 255, 0.5);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.45);
    }

    .palette-btn.is-active {
      transform: translateY(-3px);
      box-shadow: 0 18px 34px rgba(95, 121, 221, 0.28);
    }

    label.select-label {
      font-weight: 600;
      font-size: 0.92rem;
    }

    select,
    input[type="file"] {
      width: 100%;
      border-radius: 12px;
      border: 1px solid rgba(28, 37, 68, 0.18);
      padding: 11px 14px;
      background: rgba(255, 255, 255, 0.86);
      font-size: 0.95rem;
      color: inherit;
      transition: border-color 0.25s ease, box-shadow 0.25s ease;
    }

    select:focus,
    input[type="file"]:focus {
      outline: none;
      border-color: rgba(95, 121, 221, 0.6);
      box-shadow: 0 0 0 4px rgba(95, 121, 221, 0.18);
    }

    .action-row {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .action-btn {
      border-radius: 12px;
      border: none;
      padding: 10px 18px;
      font-weight: 600;
      cursor: pointer;
      background: linear-gradient(135deg, #5878ff, #a56cff);
      color: #fff;
      box-shadow: 0 16px 32px rgba(95, 121, 221, 0.3);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .action-btn.secondary {
      background: linear-gradient(135deg, #ffffff, #edf0ff);
      color: #1c2544;
      border: 1px solid rgba(28, 37, 68, 0.1);
    }

    .action-btn.danger {
      background: linear-gradient(135deg, #ff8aa0, #ff6b7f);
      box-shadow: 0 18px 32px rgba(255, 118, 145, 0.32);
    }

    .result-text {
      min-height: 22px;
      font-size: 0.9rem;
      color: var(--text-muted);
    }

    body.map-page.dark-theme .result-text {
      color: var(--text-muted-dark);
    }

    .bottom-nav {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 16px;
      margin-top: 6px;
    }

    .bottom-nav a {
      padding: 8px 16px;
      border-radius: 999px;
      text-decoration: none;
      font-weight: 600;
      color: inherit;
      background: rgba(255, 255, 255, 0.78);
      border: 1px solid rgba(255, 255, 255, 0.55);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .bottom-nav a:hover {
      transform: translateY(-2px);
      box-shadow: 0 14px 28px rgba(110, 136, 255, 0.22);
    }

    body.map-page.dark-theme .bottom-nav a {
      background: rgba(27, 33, 69, 0.78);
      border-color: rgba(114, 105, 231, 0.28);
    }

    .footer-note {
      text-align: center;
      font-size: 0.86rem;
      color: var(--text-muted);
    }

    body.map-page.dark-theme .footer-note {
      color: var(--text-muted-dark);
    }

    @media (max-width: 1080px) {
      .map-layout {
        grid-template-columns: 1fr;
      }

      .map-panel {
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      }
    }

    @media (max-width: 768px) {
      body.map-page {
        padding: 28px 14px 40px;
      }

      .glass-card {
        padding: 22px;
      }

      .nav-links {
        gap: 8px;
      }

      .map-panel {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body class="map-page">
  <div class="page-shell">
    <header class="glass-card map-header">
      <a class="brand-link" href="<?= base_url('index.php') ?>">
        <span>🗺️</span>
        <span class="brand"><?= e(APP_NAME) ?></span>
      </a>
      <div class="nav-links">
        <span class="badge">Merhaba, <?= e($user['username']) ?></span>
        <a href="<?= base_url('dashboard.php') ?>">Panel</a>
        <a href="<?= base_url('books.php') ?>">Kitaplarım</a>
        <a href="<?= base_url('notes.php') ?>">Notlarım</a>
        <a href="<?= base_url('eglence.php') ?>">Eğlence</a>
        <a href="<?= base_url('designer_cover.php') ?>">Kapak</a>
        <a href="<?= base_url('designer_map.php') ?>">Harita</a>
        <a href="<?= base_url('logout.php') ?>">Çıkış</a>
      </div>
    </header>

    <main class="glass-card map-main">
      <h2>Harita Tasarım PRO</h2>
      <p class="lead">Hikâyenin diyarını katman katman inşa et. Zemin dokularını boyayıp özel ikonlarla şehirlerini, dağlarını ve limanlarını yerleştir.</p>

      <div class="map-layout">
        <section class="map-stage">
          <div class="canvas-wrap">
            <canvas id="map-canvas" width="960" height="640"></canvas>
            <div class="canvas-hint">
              <span>🖱️ Basılı tutup sürükleyerek boya</span>
              <span>⌥ Alt basılıyken silgi</span>
              <span>Çift tıkla sahneyi yaklaştır</span>
            </div>
          </div>
        </section>

        <aside class="map-panel">
          <section class="tool-section">
            <h3>Çizim Modu</h3>
            <div class="seg">
              <button id="tool-terrain" type="button">Arazi</button>
              <button id="tool-object" type="button">Obje</button>
              <button id="tool-erase" type="button">Silgi</button>
            </div>
          </section>

          <section class="tool-section">
            <h3>Arazi Paleti</h3>
            <div id="palette" class="palette-grid"></div>
          </section>

          <section class="tool-section">
            <h3>Obje Seçimi</h3>
            <?php csrf_field(); ?>
            <label class="select-label" for="obj-type">Obje türü</label>
            <select id="obj-type">
              <option value="tree">🌳 Ağaç</option>
              <option value="house">🏠 Ev</option>
              <option value="rock">🪨 Kaya</option>
              <option value="river">🌊 Nehir</option>
              <option value="castle">🏰 Kale</option>
            </select>
            <label class="select-label" for="obj-size">Obje boyutu</label>
            <select id="obj-size">
              <option value="1">Küçük</option>
              <option value="2">Orta</option>
              <option value="3">Büyük</option>
            </select>
          </section>

          <section class="tool-section">
            <h3>Hızlı İşlemler</h3>
            <div class="seg">
              <button id="undo" type="button">⤺ Geri Al</button>
              <button id="redo" type="button">⤻ İleri Al</button>
            </div>
            <div class="seg">
              <button id="toggle-rain" type="button">🌧️ Yağmur</button>
            </div>
            <div class="action-row">
              <button id="save-png" type="button" class="action-btn">🖼️ PNG Kaydet</button>
              <button id="save-json" type="button" class="action-btn secondary">💾 JSON Kaydet</button>
            </div>
          </section>

          <section class="tool-section">
            <h3>JSON Yükle</h3>
            <input type="file" id="load-json" accept="application/json">
            <button id="clear-map" type="button" class="action-btn danger" style="margin-top:8px;">Haritayı Temizle</button>
            <div id="map-result" class="result-text"></div>
          </section>
        </aside>
      </div>
    </main>

    <nav class="bottom-nav">
      <a href="<?= base_url('books.php') ?>">📚 Kitaplar</a>
      <a href="<?= base_url('notes.php') ?>">📝 Notlar</a>
      <a href="<?= base_url('designer_cover.php') ?>">🎨 Kapak</a>
      <a href="<?= base_url('designer_map.php') ?>">🗺️ Harita</a>
    </nav>
    <div class="footer-note">© <?= date('Y') ?> <?= e(APP_NAME) ?></div>
  </div>

  <script src="../assets/js/map_designer_pro.js"></script>
</body>
</html>
