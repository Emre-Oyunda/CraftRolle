<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';

require_login();
if (function_exists('csrf_check')) {
    csrf_check();
}

$user = current_user();
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Yeni Kitap - <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
*, *::before, *::after {
  box-sizing: border-box;
}

body.book-new {
  font-family: 'Inter', 'Segoe UI', Tahoma, sans-serif;
  min-height: 100vh;
  margin: 0;
  padding: clamp(24px, 6vw, 48px) clamp(16px, 6vw, 32px) clamp(40px, 8vw, 64px);
  background: radial-gradient(circle at 12% 18%, #fff3fb 0%, #ffe6f4 35%, #f6eaff 70%, #f3f5ff 100%);
  color: #3f2851;
  transition: background 0.45s ease, color 0.45s ease;
  position: relative;
  overflow-x: hidden;
}

body.book-new::before,
body.book-new::after {
  content: '';
  position: fixed;
  border-radius: 50%;
  filter: blur(140px);
  opacity: 0.55;
  z-index: 0;
  transition: opacity 0.5s ease, transform 0.6s ease;
}

body.book-new::before {
  width: 420px;
  height: 420px;
  top: -120px;
  left: -80px;
  background: linear-gradient(135deg, rgba(255, 183, 224, 0.9), rgba(245, 207, 255, 0.7));
}

body.book-new::after {
  width: 360px;
  height: 360px;
  bottom: -140px;
  right: -80px;
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.6), rgba(255, 107, 157, 0.6));
}

body.book-new.dark-theme {
  background: radial-gradient(circle at 20% 20%, #150e24 0%, #0c0717 45%, #06030f 100%);
  color: #efe3ff;
}

body.book-new.dark-theme::before,
body.book-new.dark-theme::after {
  opacity: 0.25;
  transform: scale(1.08);
}

.container {
  max-width: 1080px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  gap: clamp(18px, 3vw, 28px);
}

.glass-card {
  background: rgba(255, 255, 255, 0.78);
  border-radius: 22px;
  border: 1px solid rgba(255, 255, 255, 0.65);
  padding: clamp(22px, 3vw, 32px);
  box-shadow: 0 18px 48px rgba(198, 135, 255, 0.18);
  backdrop-filter: blur(26px);
  transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
}

@media (hover: hover) {
  .glass-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 26px 64px rgba(198, 107, 231, 0.28);
  }
}

body.book-new.dark-theme .glass-card {
  background: rgba(18, 14, 34, 0.78);
  border: 1px solid rgba(124, 58, 237, 0.3);
  box-shadow: 0 20px 60px rgba(6, 2, 12, 0.6);
}

.top-shell {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: clamp(16px, 3vw, 24px);
}

.brand-block {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 220px;
}

.brand-link {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  font-size: clamp(1.3rem, 3vw, 1.6rem);
  font-weight: 700;
  color: inherit;
  text-decoration: none;
}

.brand-icon {
  font-size: clamp(1.4rem, 3vw, 1.8rem);
}

.brand-link span.brand {
  background: linear-gradient(135deg, #ff7ac0, #c46de8);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.brand-tagline {
  font-size: 0.92rem;
  opacity: 0.75;
  max-width: 460px;
  line-height: 1.5;
}

.ghost-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 16px;
  border-radius: 999px;
  border: 1px solid rgba(79, 47, 100, 0.22);
  background: transparent;
  color: inherit;
  font-weight: 600;
  text-decoration: none;
  transition: background 0.25s ease, transform 0.25s ease, border-color 0.25s ease;
}

@media (hover: hover) {
  .ghost-btn:hover {
    background: rgba(255, 255, 255, 0.38);
    transform: translateY(-1px);
  }
}

body.book-new.dark-theme .ghost-btn {
  border-color: rgba(124, 58, 237, 0.35);
  background: rgba(23, 18, 39, 0.65);
}

.header-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
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
  background: rgba(255, 255, 255, 0.75);
  color: #4f2f66;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 10px 28px rgba(255, 153, 211, 0.25);
  transition: transform 0.25s ease, box-shadow 0.3s ease, border-color 0.3s ease;
}

@media (hover: hover) {
  .theme-toggle:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 36px rgba(198, 107, 231, 0.32);
  }
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
  background: #fff;
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

.theme-name {
  font-size: 0.9rem;
}

.theme-sub {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  opacity: 0.6;
}

body.book-new.dark-theme .theme-toggle {
  background: rgba(23, 18, 39, 0.75);
  border: 1px solid rgba(124, 58, 237, 0.35);
  color: #f4ddff;
  box-shadow: 0 14px 34px rgba(5, 2, 12, 0.6);
}

body.book-new.dark-theme .toggle-track {
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.6), rgba(18, 10, 56, 0.6));
  border: 1px solid rgba(124, 58, 237, 0.4);
}

body.book-new.dark-theme .toggle-thumb {
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
  background: rgba(255, 255, 255, 0.65);
  border: 1px solid rgba(255, 255, 255, 0.55);
  font-weight: 600;
}

body.book-new.dark-theme .user-chip {
  background: rgba(23, 18, 39, 0.7);
  border: 1px solid rgba(124, 58, 237, 0.3);
  color: #f4e1ff;
}

.nav-links {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.creator-grid {
  display: grid;
  gap: clamp(18px, 3vw, 26px);
  grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
  align-items: start;
}

.creator-grid h2 {
  margin-top: 0;
  margin-bottom: 12px;
}

.creator-grid p {
  margin-top: 0;
  margin-bottom: 18px;
  font-size: 0.95rem;
  line-height: 1.6;
  opacity: 0.8;
}

.weapon-panel {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.weapon-panel form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.weapon-panel label {
  font-weight: 600;
  font-size: 0.95rem;
}

.weapon-panel input,
.weapon-panel textarea,
.weapon-panel select {
  width: 100%;
  border-radius: 12px;
  border: 1px solid rgba(125, 73, 148, 0.22);
  padding: 12px 14px;
  background: rgba(255, 255, 255, 0.85);
  color: inherit;
  font-size: 1rem;
  transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
}

.weapon-panel input:focus,
.weapon-panel textarea:focus,
.weapon-panel select:focus,
.writer-zone textarea:focus {
  outline: none;
  border-color: rgba(198, 107, 231, 0.6);
  box-shadow: 0 0 0 4px rgba(198, 107, 231, 0.18);
  background: rgba(255, 255, 255, 0.95);
}

body.book-new.dark-theme .weapon-panel input,
body.book-new.dark-theme .weapon-panel textarea,
body.book-new.dark-theme .weapon-panel select {
  background: rgba(18, 14, 36, 0.85);
  border: 1px solid rgba(124, 58, 237, 0.3);
}

.toolbar {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 12px;
}

.toolbar .btn {
  padding: 8px 12px;
  border-radius: 10px;
  border: none;
  background: linear-gradient(135deg, rgba(255, 134, 199, 0.85), rgba(198, 107, 231, 0.85));
  color: #fff;
  cursor: pointer;
  font-weight: 600;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

@media (hover: hover) {
  .toolbar .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 18px rgba(198, 107, 231, 0.35);
  }
}

body.book-new.dark-theme .toolbar .btn {
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.8), rgba(255, 111, 181, 0.8));
}

.writer-zone {
  display: flex;
  flex-direction: column;
  gap: 14px;
  padding: clamp(18px, 3vw, 24px);
  background: rgba(255, 255, 255, 0.82);
  border-radius: 18px;
  border: 1px solid rgba(125, 73, 148, 0.22);
  transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
}

body.book-new.dark-theme .writer-zone {
  background: rgba(18, 14, 36, 0.85);
  border: 1px solid rgba(124, 58, 237, 0.32);
}

.writing-textarea {
  font-family: 'Georgia', 'Times New Roman', serif;
  font-size: clamp(15px, 2.2vw, 16px);
  line-height: 1.75;
  padding: 16px;
  min-height: clamp(260px, 55vh, 420px);
  resize: vertical;
  background: transparent;
  border: 1px solid rgba(125, 73, 148, 0.22);
  border-radius: 12px;
}

body.book-new.dark-theme .writing-textarea {
  border: 1px solid rgba(124, 58, 237, 0.3);
}

.stats-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-top: 6px;
  padding-top: 12px;
  border-top: 1px solid rgba(221, 160, 221, 0.2);
  font-size: 0.82rem;
  color: #8b6b8d;
}

body.book-new.dark-theme .stats-bar {
  border-top: 1px solid rgba(124, 58, 237, 0.22);
  color: #d4b5d7;
}

.stat-item {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.stat-icon {
  font-size: 1rem;
}

.save-indicator {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 0.82rem;
  font-weight: 600;
  transition: transform 0.3s ease, background 0.3s ease, color 0.3s ease;
}

.save-indicator.unsaved {
  background: rgba(255, 200, 124, 0.2);
  color: #d17a00;
}

body.book-new.dark-theme .save-indicator.unsaved {
  background: rgba(255, 200, 124, 0.15);
  color: #ffb870;
}

.save-indicator.saved {
  background: rgba(124, 221, 160, 0.22);
  color: #2d7a4d;
  animation: pulse-save 0.5s ease;
}

body.book-new.dark-theme .save-indicator.saved {
  background: rgba(124, 221, 160, 0.18);
  color: #7ce8a0;
}

@keyframes pulse-save {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.creator-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
  margin-top: 12px;
}

.creator-footer button {
  padding: 12px 20px;
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #ff7fc7, #c56ae6);
  color: #fff;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 16px 30px rgba(197, 106, 230, 0.3);
  transition: transform 0.25s ease, box-shadow 0.3s ease;
}

@media (hover: hover) {
  .creator-footer button:hover {
    transform: translateY(-2px);
    box-shadow: 0 22px 42px rgba(197, 106, 230, 0.45);
  }
}

body.book-new.dark-theme .creator-footer button {
  background: linear-gradient(135deg, #7c3aed, #ff6fb5);
}

.inspiration-quote {
  text-align: center;
  font-style: italic;
  color: #a97da9;
  margin-top: 18px;
  padding: 14px 18px;
  background: rgba(221, 160, 221, 0.12);
  border-radius: 12px;
  font-size: 0.9rem;
  transition: opacity 0.3s ease;
}

body.book-new.dark-theme .inspiration-quote {
  color: #d4b5d7;
  background: rgba(124, 58, 237, 0.12);
}

.bottom-nav {
  display: flex;
  justify-content: center;
  gap: 14px;
  margin-top: clamp(16px, 4vw, 30px);
  flex-wrap: wrap;
}

.bottom-nav a {
  padding: 10px 18px;
  background: rgba(255, 255, 255, 0.82);
  border-radius: 12px;
  font-size: 0.92rem;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(198, 107, 231, 0.18);
  color: inherit;
  text-decoration: none;
  border: 1px solid transparent;
}

@media (hover: hover) {
  .bottom-nav a:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(198, 107, 231, 0.25);
    border-color: rgba(198, 107, 231, 0.28);
  }
}

body.book-new.dark-theme .bottom-nav a {
  background: rgba(23, 18, 39, 0.7);
  border: 1px solid rgba(124, 58, 237, 0.32);
}

.footer-note {
  text-align: center;
  font-size: 0.82rem;
  opacity: 0.7;
  margin-top: 8px;
}

@media (max-width: 1024px) {
  .creator-grid {
    grid-template-columns: 1fr;
    grid-auto-flow: row;
  }
}

@media (max-width: 860px) {
  .top-shell {
    flex-direction: column;
    align-items: stretch;
  }

  .header-actions {
    width: 100%;
    justify-content: flex-start;
  }

  .header-actions > * {
    flex: 1 1 100%;
  }

  .theme-toggle,
  .user-chip {
    justify-content: center;
  }

  .nav-links {
    justify-content: center;
  }
}

@media (max-width: 680px) {
  body.book-new {
    padding: 24px clamp(12px, 5vw, 18px) 40px;
  }

  .brand-block,
  .brand-tagline {
    text-align: center;
  }

  .brand-link {
    justify-content: center;
  }

  .ghost-btn {
    align-self: center;
  }

  .header-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .nav-links {
    width: 100%;
  }

  .nav-links .ghost-btn {
    justify-content: center;
    width: 100%;
  }

  .creator-grid {
    display: flex;
    flex-direction: column;
  }

  .creator-grid > * {
    width: 100%;
  }

  .creator-footer {
    flex-direction: column;
    align-items: stretch;
  }

  .creator-footer button,
  .save-indicator {
    width: 100%;
    justify-content: center;
  }

  .toolbar .btn {
    flex: 1 1 calc(50% - 10px);
    text-align: center;
  }
}

@media (max-width: 560px) {
  .toolbar .btn {
    flex: 1 1 100%;
  }

  .writer-zone {
    padding: 16px;
  }

  .bottom-nav a {
    flex: 1 1 45%;
    text-align: center;
  }
}

@media (max-width: 420px) {
  body.book-new {
    padding: 20px 14px 36px;
  }

  .theme-toggle {
    padding: 10px 12px;
  }

  .toggle-track {
    width: 48px;
    height: 26px;
  }

  .toggle-thumb {
    width: 20px;
    height: 20px;
  }

  .stats-bar {
    flex-direction: column;
    align-items: flex-start;
  }

  .bottom-nav a {
    flex: 1 1 100%;
  }
}

@media (max-width: 340px) {
  .theme-labels {
    display: none;
  }

  .theme-toggle {
    justify-content: center;
    gap: 10px;
  }
}

@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}
</style>
</head>
<body class="book-new">
<div class="container">
  <div class="glass-card top-shell">
    <div class="brand-block">
      <a class="brand-link" href="<?= base_url('index.php') ?>">
        <span class="brand-icon">??</span>
        <span class="brand"><?= e(APP_NAME) ?></span>
      </a>
      <p class="brand-tagline">Yeni kitab?n? yazmaya pembe bir sayfadan ba?la; tek tu?la siyah moda ge?.</p>
      <a class="ghost-btn" href="<?= base_url('books.php') ?>">? Kitaplara d?n</a>
    </div>
    <div class="header-actions">
      <button class="theme-toggle" id="theme-toggle" type="button" aria-pressed="false">
        <span class="toggle-track">
          <span class="toggle-thumb" id="theme-thumb">??</span>
        </span>
        <span class="theme-labels">
          <span class="theme-name" id="theme-label">Pembe</span>
          <span class="theme-sub">Tema</span>
        </span>
      </button>
      <span class="user-chip">?? <?= e($user['username']) ?></span>
      <div class="nav-links">
        <a class="ghost-btn" href="<?= base_url('dashboard.php') ?>">?? Panel</a>
        <a class="ghost-btn" href="<?= base_url('notes.php') ?>">?? Notlar</a>
        <a class="ghost-btn" href="<?= base_url('designer_cover.php') ?>">?? Kapak</a>
        <a class="ghost-btn" href="<?= base_url('designer_map.php') ?>">??? Harita</a>
        <a class="ghost-btn" href="<?= base_url('logout.php') ?>">??k??</a>
      </div>
    </div>
  </div>

  <div class="glass-card creator-grid">
    <div>
      <h2>? Yeni Kitap Olu?tur</h2>
      <p>Ba?l??? se?, g?r?n?rl??? ayarla ve hik?yeni yazmaya ba?la. Taslaklar taray?c?nda saklan?r, kaybolmaz.</p>
      <div class="weapon-panel">
        <form id="book-form" method="post" action="book_save.php">
          <?= csrf_field(); ?>
          <input type="hidden" name="book_id" value="">

          <label for="title-input">?? Kitap Ba?l???</label>
          <input name="title" id="title-input" required placeholder="B?y?k macera burada ba?lar...">

          <label for="visibility">?? G?r?n?rl?k</label>
          <select name="visibility" id="visibility">
            <option value="private">Gizli (Sadece Ben)</option>
            <option value="unlisted">Liste D??? (Link ile)</option>
            <option value="public">Herkese A??k</option>
          </select>

          <label for="description">?? K?sa A??klama</label>
          <textarea name="description" id="description" rows="2" placeholder="Okuyucular kitap kart?nda bu sat?r? g?r?r"></textarea>

          <div class="creator-footer">
            <div class="save-indicator unsaved" id="save-badge">
              <span>??</span>
              <span>Hen?z kaydedilmedi</span>
            </div>
            <button type="submit">?? Kaydet ve Devam Et</button>
          </div>
        </form>
      </div>
    </div>

    <div>
      <h2>??? Hik?yeni Yaz</h2>
      <p style="font-size:0.88rem; opacity:0.75; margin-bottom:14px;">Kalemin ?s?n?yor. Tema d??mesi ile gece yaz?mlar?nda g?zlerini dinlendir.</p>
      <div class="toolbar">
        <button type="button" data-cmd="bold" class="btn">B</button>
        <button type="button" data-cmd="italic" class="btn"><i>?</i></button>
        <button type="button" data-cmd="underline" class="btn"><u>A</u></button>
        <button type="button" data-cmd="h1" class="btn">Ba?l?k</button>
        <button type="button" data-cmd="ul" class="btn">Liste</button>
      </div>
      <div class="writer-zone">
        <textarea
          name="content"
          id="content-textarea"
          form="book-form"
          class="writing-textarea"
          placeholder="Bir zamanlar, uzak bir diyarda...&#10;&#10;Her b?y?k hik?ye tek bir kelimeyle ba?lar. ?imdi s?ra sende."></textarea>

        <div class="stats-bar">
          <div class="stat-item">
            <span class="stat-icon">??</span>
            <span><strong id="char-count">0</strong> karakter</span>
          </div>
          <div class="stat-item">
            <span class="stat-icon">??</span>
            <span><strong id="word-count">0</strong> kelime</span>
          </div>
          <div class="stat-item">
            <span class="stat-icon">??</span>
            <span><strong id="page-count">0</strong> sayfa (yakla??k)</span>
          </div>
          <div class="stat-item">
            <span class="stat-icon">??</span>
            <span><strong id="read-time">0</strong> dk okuma</span>
          </div>
        </div>
      </div>

      <div class="inspiration-quote" id="inspiration-quote">
        "Bir kitap yazmak, i?indeki d?nyay? k???da d?kmektir."
      </div>
    </div>
  </div>

  <div class="bottom-nav">
    <a href="<?= base_url('books.php') ?>">?? Kitaplar</a>
    <a href="<?= base_url('notes.php') ?>">?? Notlar</a>
    <a href="<?= base_url('designer_cover.php') ?>">?? Kapak</a>
    <a href="<?= base_url('designer_map.php') ?>">??? Harita</a>
  </div>
  <div class="footer-note">? <?= date('Y') ?> <?= e(APP_NAME) ?> ? </div>
</div>

<script src="../assets/js/editor.js"></script>
<script>
(function() {
  const themeToggle = document.getElementById('theme-toggle');
  if (!themeToggle) { return; }

  const themeThumb = document.getElementById('theme-thumb');
  const themeLabel = document.getElementById('theme-label');
  const storageKey = 'craft-book-new-theme';

  const applyTheme = (mode) => {
    const isDark = mode === 'dark';
    document.body.classList.toggle('dark-theme', isDark);
    themeThumb.textContent = isDark ? '??' : '??';
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

(function() {
  const focusBtn = document.getElementById('focus-mode-btn');
  if (!focusBtn) { return; }

  focusBtn.addEventListener('click', function() {
    document.body.classList.toggle('focus-mode');
    if (document.body.classList.contains('focus-mode')) {
      focusBtn.textContent = '??';
      focusBtn.title = 'Normal Moda D?n';
    } else {
      focusBtn.textContent = '???';
      focusBtn.title = 'Odaklanma Modu';
    }
  });
})();

(function() {
  const textarea = document.getElementById('content-textarea');
  const charCount = document.getElementById('char-count');
  const wordCount = document.getElementById('word-count');
  const pageCount = document.getElementById('page-count');
  const readTime = document.getElementById('read-time');
  const saveBadge = document.getElementById('save-badge');
  const form = document.getElementById('book-form');
  const quoteEl = document.getElementById('inspiration-quote');

  if (!textarea || !charCount || !wordCount || !pageCount || !readTime) {
    return;
  }

  const quotes = [
    '"Bir kitap yazmak, i?indeki d?nyay? k???da d?kmektir."',
    '"Her c?mle, yeni bir olas?l???n kap?s?n? aralar."',
    '"Yazmak, d???ncelere ?ekil vermektir."',
    '"Kelimeler, hayallerin mimarl???d?r."',
    '"Her yazar, kendi evreninin yarat?c?s?d?r."',
    '"Yaz?, ruhun aynas?d?r."',
    '"Hik?yeler, zaman?n ?tesine uzan?r."',
    '"Yazmak cesarettir, payla?mak ise ?zg?rl?k."',
    '"Her sayfa, yeni bir maceran?n ba?lang?c?d?r."',
    '"Kalem, hayal g?c?n?n de?ne?idir."'
  ];

  const updateStats = () => {
    const text = textarea.value;
    const chars = text.length;
    const words = text.trim() ? text.trim().split(/\s+/).length : 0;
    const pages = Math.max(0, Math.ceil(words / 250));
    const minutes = Math.max(0, Math.ceil(words / 200));

    charCount.textContent = chars.toLocaleString('tr-TR');
    wordCount.textContent = words.toLocaleString('tr-TR');
    pageCount.textContent = pages.toLocaleString('tr-TR');
    readTime.textContent = minutes.toLocaleString('tr-TR');
  };

  textarea.addEventListener('input', () => {
    updateStats();
    if (saveBadge) {
      saveBadge.className = 'save-indicator unsaved';
      saveBadge.innerHTML = '<span>?</span><span>Yaz?l?yor...</span>';
    }
  }, { passive: true });

  updateStats();

  if (quoteEl) {
    setInterval(() => {
      const randomQuote = quotes[Math.floor(Math.random() * quotes.length)];
      quoteEl.style.opacity = '0';
      setTimeout(() => {
        quoteEl.textContent = randomQuote;
        quoteEl.style.opacity = '1';
      }, 300);
    }, 30000);
  }

  if (form && saveBadge) {
    let saveTimeout;

    textarea.addEventListener('input', () => {
      clearTimeout(saveTimeout);
      saveTimeout = setTimeout(() => {
        saveBadge.className = 'save-indicator saved';
        saveBadge.innerHTML = '<span>?</span><span>Otomatik kaydedildi</span>';

        setTimeout(() => {
          saveBadge.className = 'save-indicator unsaved';
          saveBadge.innerHTML = '<span>??</span><span>De?i?iklikler kaydedildi</span>';
        }, 2000);
      }, 2800);
    }, { passive: true });

    form.addEventListener('submit', () => {
      saveBadge.className = 'save-indicator saved';
      saveBadge.innerHTML = '<span>?</span><span>Kaydediliyor...</span>';
    });
  }
})();
</script>
</body>
</html>
