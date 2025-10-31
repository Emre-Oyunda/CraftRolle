<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
$user = current_user();

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare("SELECT b.*, u.username FROM books b JOIN users u ON u.id=b.user_id WHERE b.id=? AND b.is_deleted=0");
$st->execute([$id]);
$book = $st->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    http_response_code(404);
    die('Kitap bulunamadı');
}

if ($book['visibility'] === 'private' && (empty($_SESSION['user_id']) || (int)$_SESSION['user_id'] !== (int)$book['user_id'])) {
    http_response_code(403);
    die('Bu kitap gizli.');
}
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($book['title']) ?> — <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body.book-view {
  font-family: 'Inter', 'Segoe UI', Tahoma, sans-serif;
  min-height: 100vh;
  padding: 36px 20px 48px;
  background: radial-gradient(circle at 12% 18%, #fff3fb 0%, #ffe5f5 35%, #f5ecff 70%, #f4f6ff 100%);
  color: #3f2951;
  transition: background 0.45s ease, color 0.45s ease;
  position: relative;
  overflow-x: hidden;
}

body.book-view::before,
body.book-view::after {
  content: '';
  position: fixed;
  border-radius: 50%;
  filter: blur(140px);
  opacity: 0.55;
  z-index: 0;
  transition: opacity 0.5s ease, transform 0.6s ease;
}

body.book-view::before {
  width: 440px;
  height: 440px;
  top: -140px;
  left: -80px;
  background: linear-gradient(135deg, rgba(255, 183, 224, 0.88), rgba(245, 207, 255, 0.7));
}

body.book-view::after {
  width: 360px;
  height: 360px;
  bottom: -160px;
  right: -80px;
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.55), rgba(255, 107, 157, 0.55));
}

body.book-view.dark-theme {
  background: radial-gradient(circle at 22% 20%, #140d24 0%, #0b0717 45%, #06030f 100%);
  color: #efe3ff;
}

body.book-view.dark-theme::before,
body.book-view.dark-theme::after {
  opacity: 0.25;
  transform: scale(1.08);
}

.book-view .container {
  max-width: 1200px;
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

body.book-view.dark-theme .glass-card {
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

body.book-view.dark-theme .theme-toggle {
  background: rgba(23, 18, 39, 0.75);
  border: 1px solid rgba(124, 58, 237, 0.35);
  color: #f4ddff;
  box-shadow: 0 14px 34px rgba(5, 2, 12, 0.6);
}

body.book-view.dark-theme .toggle-track {
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.6), rgba(18, 10, 56, 0.6));
  border: 1px solid rgba(124, 58, 237, 0.4);
}

body.book-view.dark-theme .toggle-thumb {
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

body.book-view.dark-theme .user-chip {
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

.book-meta {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.book-meta .badge-row {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  font-size: 0.9rem;
  opacity: 0.75;
}

.book-meta .cover-preview {
  max-width: 200px;
  border-radius: 14px;
  border: 1px solid rgba(255, 255, 255, 0.55);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.18);
}

.book-viewer-shell {
  display: grid;
  gap: 18px;
}

.book-viewer-container {
  perspective: 2000px;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 480px;
  padding: 40px 20px;
  position: relative;
}

.book-3d {
  position: relative;
  width: min(90vw, 800px);
  height: calc(min(90vw, 800px) * 0.62);
  transform-style: preserve-3d;
  transition: transform 0.4s ease;
}

.book-spine {
  position: absolute;
  left: 50%;
  top: 0;
  width: 20px;
  height: 100%;
  background: linear-gradient(to right, #8b4513, #a0522d, #8b4513);
  transform: translateX(-50%);
  box-shadow: inset 2px 0 4px rgba(0,0,0,0.3), inset -2px 0 4px rgba(0,0,0,0.3);
  z-index: 50;
}

.book-page {
  position: absolute;
  width: calc(min(90vw, 800px) / 2 - 20px);
  height: calc(min(90vw, 800px) * 0.62);
  background: linear-gradient(to right, #fefefe 0%, #f8f8f8 50%, #fefefe 100%);
  border: 1px solid #e3dce6;
  border-radius: 0 12px 12px 0;
  padding: clamp(18px, 3vw, 40px) clamp(16px, 3vw, 30px);
  box-shadow: 0 18px 40px rgba(0,0,0,0.18), inset -2px 0 12px rgba(0,0,0,0.05);
  transform-style: preserve-3d;
  transform-origin: left center;
  transition: transform 0.8s cubic-bezier(0.645, 0.045, 0.355, 1);
  backface-visibility: hidden;
}

.book-page.left-page {
  left: clamp(6px, 1.5vw, 14px);
  transform-origin: right center;
  border-radius: 12px 0 0 12px;
  background: linear-gradient(to left, #fefefe 0%, #f8f8f8 50%, #fefefe 100%);
  box-shadow: 0 18px 40px rgba(0,0,0,0.18), inset 2px 0 12px rgba(0,0,0,0.05);
}

.book-page.right-page {
  right: clamp(6px, 1.5vw, 14px);
  transform-origin: left center;
}

.book-page.measurement {
  visibility: hidden !important;
  pointer-events: none;
  position: absolute;
  left: -9999px;
  top: -9999px;
}

.book-page.flipped {
  transform: rotateY(-175deg);
  z-index: 2000 !important;
}

.book-page.left-page.flipped {
  transform: rotateY(175deg);
}

.page-content {
  position: relative;
  height: 100%;
  font-size: clamp(13px, 2.1vw, 15px);
  line-height: 1.75;
  color: #2d2437;
  text-align: justify;
  white-space: pre-wrap;
  overflow: hidden;
}

body.book-view.dark-theme .book-page {
  background: linear-gradient(to right, #1f1633 0%, #1a1230 50%, #21183a 100%);
  border-color: rgba(124, 58, 237, 0.35);
  color: #f2e8ff;
}

body.book-view.dark-theme .page-content {
  color: #efe3ff;
}

.page-number {
  position: absolute;
  bottom: 15px;
  font-size: 12px;
  color: #9b88a8;
  font-style: italic;
}

.left-page .page-number { left: 30px; }
.right-page .page-number { right: 30px; }

.viewer-controls {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  margin-top: 30px;
  flex-wrap: wrap;
}

.viewer-controls button,
.viewer-controls a.btn {
  padding: 12px 24px;
  border-radius: 14px;
  border: none;
  background: linear-gradient(135deg, #ff7fc8, #c66ce8);
  color: #fff;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 18px 34px rgba(198, 107, 231, 0.28);
  transition: transform 0.25s ease, box-shadow 0.3s ease;
}

.viewer-controls button:hover:not(:disabled),
.viewer-controls a.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 24px 44px rgba(198, 107, 231, 0.35);
}

.viewer-controls button:disabled {
  opacity: 0.45;
  cursor: not-allowed;
  box-shadow: none;
}

.page-indicator {
  padding: 10px 18px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.45);
  font-weight: 600;
}

body.book-view.dark-theme .page-indicator {
  background: rgba(23, 18, 39, 0.65);
  border: 1px solid rgba(124, 58, 237, 0.3);
  color: #f4ddff;
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
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.8);
  font-size: 0.92rem;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
  box-shadow: 0 4px 14px rgba(198, 107, 231, 0.18);
  color: #3f2951;
}

.bottom-nav a:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(198, 107, 231, 0.26);
}

body.book-view.dark-theme .bottom-nav a {
  background: rgba(23, 18, 39, 0.7);
  border: 1px solid rgba(124, 58, 237, 0.3);
  color: #f4ddff;
}

body.book-view.dark-theme .bottom-nav a:hover {
  box-shadow: 0 10px 24px rgba(124, 58, 237, 0.35);
}

.footer-note {
  text-align: center;
  font-size: 0.85rem;
  opacity: 0.7;
  margin-top: 8px;
}

@media (max-width: 1024px) {
  .creator-grid,
  .book-viewer-container {
    grid-template-columns: 1fr;
  }

  .book-3d {
    width: min(90vw, 640px);
    height: calc(min(90vw, 640px) * 0.65);
  }

  .book-page {
    width: calc(min(90vw, 640px) / 2 - 18px);
    height: calc(min(90vw, 640px) * 0.65);
    padding: clamp(16px, 3.2vw, 32px) clamp(14px, 3vw, 24px);
  }
}

@media (max-width: 768px) {
  body.book-view {
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

  .book-3d {
    width: min(92vw, 440px);
    height: calc(min(92vw, 440px) * 0.66);
  }

  .book-page {
    width: calc(min(92vw, 440px) / 2 - 14px);
    height: calc(min(92vw, 440px) * 0.66);
    padding: clamp(14px, 4vw, 24px) clamp(12px, 3.5vw, 18px);
  }
}
</style>
</head>
<body class="book-view">
<div class="container">
  <div class="glass-card top-shell">
    <div class="brand-block">
      <a class="brand-link" href="<?= base_url('index.php') ?>">
        <span class="brand-icon">🌸</span>
        <span class="brand"><?= e(APP_NAME) ?></span>
      </a>
      <p class="brand-tagline">Kitabını Craftrolle raflarında gezin; pembe ve siyah temalarla okuma deneyimini renklendir.</p>
      <a class="ghost-btn" href="<?= base_url('books.php') ?>">← Kitap listeme dön</a>
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
      <span class="user-chip">📖 <?= e($book['username']) ?></span>
      <div class="nav-links">
        <a class="ghost-btn" href="<?= base_url('notes.php') ?>">📝 Notlar</a>
        <a class="ghost-btn" href="<?= base_url('designer_cover.php') ?>">🎨 Kapak Tasarım</a>
        <a class="ghost-btn" href="<?= base_url('designer_map.php') ?>">🗺️ Harita Tasarım</a>
        <a class="ghost-btn" href="<?= base_url('logout.php') ?>">Çıkış</a>
      </div>
    </div>
  </div>

  <div class="glass-card book-viewer-shell">
    <div class="book-meta">
      <h1 style="margin:0; font-size:1.8rem;"><?= e($book['title']) ?></h1>
      <div class="badge-row">
        <span>✍️ Yazar: <strong><?= e($book['username']) ?></strong></span>
        <span>🔒 Görünürlük: <strong><?= e(ucfirst($book['visibility'])) ?></strong></span>
        <span>📅 <?= e(date('d.m.Y H:i', strtotime($book['created_at']))) ?></span>
      </div>
      <?php if ($book['cover_path']): ?>
        <img class="cover-preview" src="<?= base_url('../'.ltrim($book['cover_path'],'/')) ?>" alt="Kitap kapağı">
      <?php endif; ?>
    </div>

    <div class="book-viewer-container">
      <div class="book-3d" id="book3d">
        <div class="book-spine"></div>
      </div>
    </div>

    <div class="viewer-controls">
      <button id="prevBtn" type="button">◀ Önceki Sayfa</button>
      <div class="page-indicator">
        <span id="currentPage">1</span> - <span id="currentPageNext">2</span> / <span id="totalPages">0</span>
      </div>
      <button id="nextBtn" type="button">Sonraki Sayfa ▶</button>
      <a class="btn" href="<?= base_url('export_print.php?id='.(int)$book['id']) ?>">📄 Yazdır/PDF</a>
    </div>
  </div>

  <div class="bottom-nav">
    <a href="<?= base_url('books.php') ?>">📚 Kitaplar</a>
    <a href="<?= base_url('notes.php') ?>">📝 Notlar</a>
    <a href="<?= base_url('designer_cover.php') ?>">🎨 Kapak</a>
    <a href="<?= base_url('designer_map.php') ?>">🗺️ Harita</a>
  </div>
  <div class="footer-note">© <?= date('Y') ?> <?= e(APP_NAME) ?> · Craftrolle 3D kitap deneyimi</div>
</div>

<script>
const bookTitle = <?= json_encode($book['title']) ?>;
const bookContent = <?= json_encode($book['content'] ?: 'Bu kitap henüz içerik içermiyor.') ?>;
const initialText = (bookTitle ? bookTitle + '\n\n' : '') + bookContent;

function createMeasurementPage(container) {
  const page = document.createElement('div');
  page.className = 'book-page left-page measurement';
  page.innerHTML = '<div class="page-content"></div><div class="page-number"></div>';
  container.appendChild(page);
  return page;
}

function paginateContent(text) {
  const bookEl = document.getElementById('book3d');
  const measurementPage = createMeasurementPage(bookEl);
  const measurementContent = measurementPage.querySelector('.page-content');
  const tokens = text.length ? text.split(/(\s+)/) : [];
  const pages = [];
  let pageTokens = [];

  const flushPage = () => {
    const pageText = pageTokens.join('').replace(/\s+$/, '');
    pages.push(pageText);
    pageTokens = [];
  };

  for (let i = 0; i < tokens.length; i++) {
    const token = tokens[i];
    pageTokens.push(token);
    measurementContent.textContent = pageTokens.join('');
    if (measurementContent.scrollHeight > measurementContent.clientHeight + 1) {
      pageTokens.pop();
      measurementContent.textContent = pageTokens.join('');
      flushPage();

      if (token.trim().length > 0) {
        pageTokens.push(token.replace(/^\s+/, ''));
        measurementContent.textContent = pageTokens.join('');
        while (measurementContent.scrollHeight > measurementContent.clientHeight + 1 && pageTokens.length > 1) {
          const overflowToken = pageTokens.pop();
          measurementContent.textContent = pageTokens.join('');
          flushPage();
          pageTokens.push(overflowToken);
          measurementContent.textContent = pageTokens.join('');
        }
      }
    }
  }

  if (pageTokens.length) {
    measurementContent.textContent = pageTokens.join('');
    flushPage();
  }

  bookEl.removeChild(measurementPage);

  if (pages.length === 0) {
    pages.push('Bu kitap henüz içerik içermiyor.');
  }

  if (pages.length % 2 !== 0) {
    pages.push('');
  }

  return pages;
}

function createPageElement(index, text) {
  const isLeft = index % 2 === 0;
  const page = document.createElement('div');
  page.className = `book-page ${isLeft ? 'left-page' : 'right-page'}`;
  page.dataset.page = index;
  page.style.zIndex = String(2000 - index);
  const content = document.createElement('div');
  content.className = 'page-content';
  content.textContent = text;
  const number = document.createElement('div');
  number.className = 'page-number';
  number.textContent = index + 1;
  page.appendChild(content);
  page.appendChild(number);
  return page;
}

class Book3DViewer {
  constructor(totalPages) {
    this.bookEl = document.getElementById('book3d');
    this.prevBtn = document.getElementById('prevBtn');
    this.nextBtn = document.getElementById('nextBtn');
    this.currentPageDisplay = document.getElementById('currentPage');
    this.currentPageNextDisplay = document.getElementById('currentPageNext');
    this.totalPagesDisplay = document.getElementById('totalPages');
    this.totalPagesDisplay.textContent = totalPages;
    this.pages = Array.from(this.bookEl.querySelectorAll('.book-page'));
    this.currentSpread = 0;
    this.totalSpreads = totalPages / 2;
    this.attachEvents();
    this.render();
    this.updateControls();
  }

  attachEvents() {
    this.prevHandler = () => this.prevPage();
    this.nextHandler = () => this.nextPage();
    this.prevBtn.addEventListener('click', this.prevHandler);
    this.nextBtn.addEventListener('click', this.nextHandler);

    this.keyHandler = (e) => {
      if (e.key === 'ArrowLeft') this.prevPage();
      if (e.key === 'ArrowRight') this.nextPage();
    };
    document.addEventListener('keydown', this.keyHandler);

    let touchStartX = 0;
    let touchEndX = 0;
    const container = document.querySelector('.book-viewer-container');
    container?.addEventListener('touchstart', (e) => {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
    container?.addEventListener('touchend', (e) => {
      touchEndX = e.changedTouches[0].screenX;
      if (touchEndX < touchStartX - 60) this.nextPage();
      if (touchEndX > touchStartX + 60) this.prevPage();
    }, { passive: true });
  }

  destroy() {
    this.prevBtn.removeEventListener('click', this.prevHandler);
    this.nextBtn.removeEventListener('click', this.nextHandler);
    document.removeEventListener('keydown', this.keyHandler);
  }

  render() {
    this.pages.forEach((page) => page.classList.remove('flipped'));
    this.pages.forEach((page, index) => {
      const spreadIndex = Math.floor(index / 2);
      if (spreadIndex < this.currentSpread) {
        page.classList.add('flipped');
      }
    });
  }

  updateControls() {
    this.pages = Array.from(this.bookEl.querySelectorAll('.book-page'));
    this.prevBtn.disabled = this.currentSpread === 0;
    this.nextBtn.disabled = this.currentSpread >= this.totalSpreads - 1;
    const leftPage = this.currentSpread * 2 + 1;
    const rightPage = Math.min(this.currentSpread * 2 + 2, this.totalSpreads * 2);
    this.currentPageDisplay.textContent = leftPage;
    this.currentPageNextDisplay.textContent = rightPage;
  }

  nextPage() {
    if (this.currentSpread < this.totalSpreads - 1) {
      this.currentSpread++;
      this.render();
      this.updateControls();
    }
  }

  prevPage() {
    if (this.currentSpread > 0) {
      this.currentSpread--;
      this.render();
      this.updateControls();
    }
  }
}

let viewerInstance = null;
let resizeTimer = null;

function renderBook() {
  const bookEl = document.getElementById('book3d');
  bookEl.innerHTML = '<div class="book-spine"></div>';
  const pages = paginateContent(initialText);
  pages.forEach((text, index) => {
    const pageEl = createPageElement(index, text);
    bookEl.appendChild(pageEl);
  });
  if (viewerInstance) {
    viewerInstance.destroy();
  }
  viewerInstance = new Book3DViewer(pages.length);
}

renderBook();

window.addEventListener('resize', () => {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(renderBook, 250);
});

(function() {
  const themeToggle = document.getElementById('theme-toggle');
  const themeThumb = document.getElementById('theme-thumb');
  const themeLabel = document.getElementById('theme-label');
  const storageKey = 'craft-book-view-theme';

  if (!themeToggle) return;

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

const viewerContainer = document.querySelector('.book-viewer-container');
viewerContainer?.addEventListener('mousemove', (e) => {
  const rect = viewerContainer.getBoundingClientRect();
  const book = document.getElementById('book3d');
  if (!book) return;
  const x = e.clientX - rect.left;
  const y = e.clientY - rect.top;
  const rotateY = (x - rect.width / 2) / 40;
  const rotateX = (rect.height / 2 - y) / 40;
  book.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
});

viewerContainer?.addEventListener('mouseleave', () => {
  const book = document.getElementById('book3d');
  if (book) book.style.transform = 'rotateX(0deg) rotateY(0deg)';
});
</script>
</body>
</html>
