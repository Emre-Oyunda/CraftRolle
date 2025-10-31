<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
$user = current_user();
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(APP_NAME) ?> — Kapak Tasarım</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body.cover-designer {
  font-family: 'Inter', 'Segoe UI', Tahoma, sans-serif;
  min-height: 100vh;
  padding: 36px 20px 48px;
  background: radial-gradient(circle at 12% 18%, #fff1fb 0%, #ffe7f5 35%, #f6ecff 70%, #f5f7ff 100%);
  color: #3f2851;
  transition: background 0.45s ease, color 0.45s ease;
  position: relative;
  overflow-x: hidden;
}

body.cover-designer::before,
body.cover-designer::after {
  content: '';
  position: fixed;
  border-radius: 50%;
  filter: blur(140px);
  opacity: 0.55;
  z-index: 0;
  transition: opacity 0.5s ease, transform 0.6s ease;
}

body.cover-designer::before {
  width: 420px;
  height: 420px;
  top: -120px;
  left: -80px;
  background: linear-gradient(135deg, rgba(255, 183, 224, 0.9), rgba(245, 207, 255, 0.7));
}

body.cover-designer::after {
  width: 360px;
  height: 360px;
  bottom: -140px;
  right: -80px;
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.6), rgba(255, 107, 157, 0.6));
}

body.cover-designer.dark-theme {
  background: radial-gradient(circle at 20% 20%, #140d24 0%, #0c0717 45%, #06030f 100%);
  color: #efe3ff;
}

body.cover-designer.dark-theme::before,
body.cover-designer.dark-theme::after {
  opacity: 0.25;
  transform: scale(1.08);
}

.cover-designer .container {
  max-width: 1100px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.glass-card {
  background: rgba(255, 255, 255, 0.75);
  border-radius: 22px;
  border: 1px solid rgba(255, 255, 255, 0.6);
  padding: 26px;
  box-shadow: 0 18px 48px rgba(198, 135, 255, 0.2);
  backdrop-filter: blur(24px);
  transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
}

.glass-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 26px 64px rgba(198, 107, 231, 0.28);
}

body.cover-designer.dark-theme .glass-card {
  background: rgba(18, 14, 34, 0.78);
  border: 1px solid rgba(124, 58, 237, 0.3);
  box-shadow: 0 20px 60px rgba(6, 2, 12, 0.6);
}

.top-shell {
  display: flex;
  justify-content: space-between;
  align-items: center;
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

.brand-icon {
  font-size: 1.8rem;
}

.brand-link span.brand {
  background: linear-gradient(135deg, #ff7ac0, #c76ce9);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.brand-tagline {
  font-size: 0.92rem;
  opacity: 0.75;
  max-width: 440px;
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
  padding: 10px 16px 10px 12px;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.6);
  background: rgba(255, 255, 255, 0.6);
  color: #4f2f66;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 10px 28px rgba(255, 153, 211, 0.25);
  transition: transform 0.25s ease, box-shadow 0.3s ease, border-color 0.3s ease;
}

.theme-toggle:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 36px rgba(198, 107, 231, 0.32);
}

.toggle-track {
  position: relative;
  width: 54px;
  height: 28px;
  border-radius: 999px;
  background: linear-gradient(135deg, rgba(255, 119, 188, 0.55), rgba(198, 107, 231, 0.55));
  border: 1px solid rgba(255, 255, 255, 0.7);
  padding: 3px;
}

.toggle-thumb {
  position: absolute;
  top: 3px;
  left: 3px;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: white;
  color: #ff6bb7;
  font-size: 15px;
  display: grid;
  place-items: center;
  transition: transform 0.4s ease, color 0.4s ease, background 0.4s ease;
}

.theme-labels {
  display: flex;
  flex-direction: column;
  line-height: 1.1;
}

body.cover-designer.dark-theme .theme-toggle {
  background: rgba(23, 18, 39, 0.75);
  border: 1px solid rgba(124, 58, 237, 0.35);
  color: #f4ddff;
  box-shadow: 0 14px 34px rgba(5, 2, 12, 0.6);
}

body.cover-designer.dark-theme .toggle-track {
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.6), rgba(18, 10, 56, 0.6));
  border: 1px solid rgba(124, 58, 237, 0.4);
}

body.cover-designer.dark-theme .toggle-thumb {
  transform: translateX(24px) rotate(360deg);
  background: #21163a;
  color: #ffd6ff;
}

.user-chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.55);
  font-weight: 600;
}

body.cover-designer.dark-theme .user-chip {
  background: rgba(23, 18, 39, 0.7);
  border: 1px solid rgba(124, 58, 237, 0.3);
  color: #f4e1ff;
}

.ghost-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 16px;
  border-radius: 999px;
  border: 1px solid rgba(79, 47, 100, 0.2);
  background: transparent;
  color: inherit;
  font-weight: 600;
  text-decoration: none;
  transition: background 0.25s ease, transform 0.25s ease;
}

.ghost-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-1px);
}

.designer-body {
  display: grid;
  gap: 18px;
  grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
  align-items: start;
}

.canvas-wrap {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
  align-items: flex-start;
}

.canvas-frame {
  background: rgba(255, 255, 255, 0.85);
  border-radius: 20px;
  padding: 16px;
  border: 1px solid rgba(255, 255, 255, 0.55);
  box-shadow: 0 18px 36px rgba(0, 0, 0, 0.12);
}

body.cover-designer.dark-theme .canvas-frame {
  background: rgba(18, 14, 36, 0.8);
  border: 1px solid rgba(124, 58, 237, 0.35);
}

#cover-canvas {
  display: block;
  width: min(90vw, 420px);
  height: calc(min(90vw, 420px) * 1.5);
  border-radius: 12px;
  background: #fff;
  box-shadow: inset 0 0 0 1px rgba(0,0,0,0.1);
}

.toolbox {
  display: grid;
  gap: 14px;
  min-width: 260px;
}

.toolbox label {
  font-weight: 600;
  font-size: 0.95rem;
}

.toolbox input,
.toolbox select {
  width: 100%;
  border-radius: 12px;
  border: 1px solid rgba(125, 73, 148, 0.22);
  padding: 12px 14px;
  background: rgba(255, 255, 255, 0.78);
  color: inherit;
  font-size: 1rem;
  transition: border-color 0.25s ease, box-shadow 0.25s ease;
}

.toolbox input:focus,
.toolbox select:focus {
  outline: none;
  border-color: rgba(198, 107, 231, 0.6);
  box-shadow: 0 0 0 4px rgba(198, 107, 231, 0.18);
}

#upload-cover {
  padding: 12px 20px;
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #ff7fc8, #c66ce8);
  color: #fff;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 16px 30px rgba(198, 107, 231, 0.32);
  transition: transform 0.25s ease, box-shadow 0.3s ease;
}

#upload-cover:hover {
  transform: translateY(-2px);
  box-shadow: 0 22px 40px rgba(198, 107, 231, 0.4);
}

body.cover-designer.dark-theme #upload-cover {
  background: linear-gradient(135deg, #7c3aed, #ff6fb5);
}

.template-preview {
  display: grid;
  gap: 10px;
  background: rgba(255, 255, 255, 0.55);
  border: 1px solid rgba(255, 255, 255, 0.45);
  border-radius: 16px;
  padding: 16px;
  font-size: 0.9rem;
  line-height: 1.6;
}

body.cover-designer.dark-theme .template-preview {
  background: rgba(18, 14, 36, 0.7);
  border: 1px solid rgba(124, 58, 237, 0.3);
}

.bottom-nav {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 30px;
  flex-wrap: wrap;
}

.bottom-nav a {
  padding: 10px 20px;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 12px;
  font-size: 0.92rem;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
  box-shadow: 0 4px 14px rgba(198, 107, 231, 0.18);
  color: #3f2851;
}

.bottom-nav a:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(198, 107, 231, 0.26);
}

body.cover-designer.dark-theme .bottom-nav a {
  background: rgba(23, 18, 39, 0.65);
  border: 1px solid rgba(124, 58, 237, 0.3);
  color: #f4ddff;
}

body.cover-designer.dark-theme .bottom-nav a:hover {
  box-shadow: 0 10px 24px rgba(124, 58, 237, 0.35);
}

.footer-note {
  text-align: center;
  font-size: 0.85rem;
  opacity: 0.7;
  margin-top: 8px;
}

@media (max-width: 1024px) {
  .designer-body {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  body.cover-designer {
    padding: 24px 16px 36px;
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

  .user-chip, .nav-links {
    width: 100%;
    justify-content: center;
  }

  .canvas-wrap {
    flex-direction: column;
    align-items: center;
  }

  #cover-canvas {
    width: min(90vw, 340px);
    height: calc(min(90vw, 340px) * 1.5);
  }

  .toolbox {
    width: 100%;
  }
}
</style>
</head>
<body class="cover-designer">
<div class="container">
  <div class="glass-card top-shell">
    <div class="brand-block">
      <a class="brand-link" href="<?= base_url('index.php') ?>">
        <span class="brand-icon">🌸</span>
        <span class="brand"><?= e(APP_NAME) ?></span>
      </a>
      <p class="brand-tagline">3 şablon, sınırsız renk—Craftrolle kapak stüdyosuna hoş geldin.</p>
      <a class="ghost-btn" href="<?= base_url('books.php') ?>">← Kitaplara dön</a>
    </div>
    <div class="header-actions">
      <button class="theme-toggle" id="theme-toggle" type="button" aria-pressed="false">
        <span class="toggle-track">
          <span class="toggle-thumb" id="theme-thumb">🌸</span>
        </span>
        <span class="theme-labels">
          <span class="theme-name" id="theme-label">Pembe</span>
          <span class="theme-sub">Tema</span>
        </span>
      </button>
      <span class="user-chip">🎨 <?= $user ? e($user['username']) : 'Misafir' ?></span>
      <div class="nav-links">
        <a class="ghost-btn" href="<?= base_url('books.php') ?>">📚 Kitaplar</a>
        <a class="ghost-btn" href="<?= base_url('notes.php') ?>">📝 Notlar</a>
        <a class="ghost-btn" href="<?= base_url('designer_map.php') ?>">🗺️ Harita Tasarım</a>
        <a class="ghost-btn" href="<?= base_url('logout.php') ?>">Çıkış</a>
      </div>
    </div>
  </div>

  <div class="glass-card designer-body">
    <div class="canvas-wrap">
      <div class="canvas-frame">
        <canvas id="cover-canvas" width="600" height="900"></canvas>
      </div>
      <div class="toolbox">
        <?php csrf_field(); ?>
        <label for="template">Şablon</label>
        <select id="template">
          <option value="romance">Romance (Pembe)</option>
          <option value="scifi">Sci‑Fi</option>
          <option value="minimal">Minimal</option>
        </select>

        <label for="title-input">Başlık</label>
        <input id="title-input" placeholder="Kapak başlığı">

        <label for="author-input">Yazar</label>
        <input id="author-input" placeholder="Yazar adı">

        <button id="upload-cover" type="button">PNG Kaydet/Yükle</button>
        <div id="cover-result" class="small"></div>

        <div class="template-preview">
          <strong>🎯 İpucu</strong>
          <p>Başlık & yazar alanlarını doldur, uygun şablonu seç ve PNG olarak dışa aktararak kitap kartında kullan.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="bottom-nav">
    <a href="<?= base_url('books.php') ?>">📚 Kitaplar</a>
    <a href="<?= base_url('notes.php') ?>">📝 Notlar</a>
    <a href="<?= base_url('designer_cover.php') ?>">🎨 Kapak</a>
    <a href="<?= base_url('designer_map.php') ?>">🗺️ Harita</a>
  </div>
  <div class="footer-note">© <?= date('Y') ?> <?= e(APP_NAME) ?> · Craftrolle kapak stüdyosu</div>
</div>

<script src="../assets/js/cover_designer_pro.js"></script>
<script>
(function() {
  const themeToggle = document.getElementById('theme-toggle');
  const themeThumb = document.getElementById('theme-thumb');
  const themeLabel = document.getElementById('theme-label');
  const storageKey = 'craft-cover-designer-theme';

  if (!themeToggle) { return; }

  const applyTheme = (mode) => {
    const isDark = mode === 'dark';
    document.body.classList.toggle('dark-theme', isDark);
    themeThumb.textContent = isDark ? '🌙' : '🌸';
    themeLabel.textContent = isDark ? 'Siyah' : 'Pembe';
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
