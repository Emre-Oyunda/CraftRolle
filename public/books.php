<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_login();
$user = current_user();

$st = db()->prepare("SELECT * FROM books WHERE user_id = ? AND is_deleted = 0 ORDER BY created_at DESC");
$st->execute([$user['id']]);
$books = $st->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kitaplarım - <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
  body.books-page {
    font-family: 'Inter', 'Segoe UI', Tahoma, sans-serif;
    min-height: 100vh;
    padding: 36px 20px 48px;
    background: radial-gradient(circle at 12% 20%, #fff4fb 0%, #ffe6f5 35%, #f7ecff 65%, #f5f7ff 100%);
    color: #412a4f;
    transition: background 0.45s ease, color 0.45s ease;
    position: relative;
    overflow-x: hidden;
  }

  body.books-page::before,
  body.books-page::after {
    content: '';
    position: fixed;
    border-radius: 50%;
    filter: blur(140px);
    opacity: 0.55;
    z-index: 0;
    transition: opacity 0.5s ease, transform 0.6s ease;
  }

  body.books-page::before {
    width: 420px;
    height: 420px;
    top: -120px;
    left: -80px;
    background: linear-gradient(135deg, rgba(255, 183, 224, 0.8), rgba(245, 207, 255, 0.65));
  }

  body.books-page::after {
    width: 360px;
    height: 360px;
    bottom: -140px;
    right: -80px;
    background: linear-gradient(135deg, rgba(124, 58, 237, 0.6), rgba(255, 107, 157, 0.6));
  }

  body.books-page.dark-theme {
    background: radial-gradient(circle at 20% 20%, #140d24 0%, #0c0717 45%, #06030f 100%);
    color: #ede2ff;
  }

  body.books-page.dark-theme::before,
  body.books-page.dark-theme::after {
    opacity: 0.25;
    transform: scale(1.08);
  }

  .books-page .container {
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

  body.books-page.dark-theme .glass-card {
    background: rgba(17, 13, 30, 0.78);
    border: 1px solid rgba(124, 58, 237, 0.3);
    box-shadow: 0 20px 60px rgba(5, 2, 12, 0.6);
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
    background: linear-gradient(135deg, #ff7ac2, #c36ce8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .brand-tagline {
    font-size: 0.92rem;
    opacity: 0.75;
    max-width: 420px;
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

  .theme-name {
    font-size: 0.9rem;
  }

  .theme-sub {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    opacity: 0.6;
  }

  body.books-page.dark-theme .theme-toggle {
    background: rgba(23, 18, 39, 0.75);
    border: 1px solid rgba(124, 58, 237, 0.35);
    color: #f4ddff;
    box-shadow: 0 14px 34px rgba(5, 2, 12, 0.6);
  }

  body.books-page.dark-theme .toggle-track {
    background: linear-gradient(135deg, rgba(124, 58, 237, 0.6), rgba(18, 10, 56, 0.6));
    border: 1px solid rgba(124, 58, 237, 0.4);
  }

  body.books-page.dark-theme .toggle-thumb {
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

  body.books-page.dark-theme .user-chip {
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

  .nav-links {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .books-overview {
    display: grid;
    gap: 18px;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    align-items: start;
  }

  .cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    border-radius: 16px;
    background: linear-gradient(135deg, #ff7fc8, #c66ce8);
    color: #fff;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 16px 34px rgba(198, 106, 232, 0.35);
    transition: transform 0.25s ease, box-shadow 0.3s ease;
  }

  .cta-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 22px 48px rgba(198, 106, 232, 0.45);
  }

  body.books-page.dark-theme .cta-btn {
    background: linear-gradient(135deg, #7c3aed, #ff6fb5);
  }

  .stat-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.45);
    font-weight: 600;
    margin-top: 16px;
  }

  body.books-page.dark-theme .stat-pill {
    background: rgba(23, 18, 39, 0.65);
    border: 1px solid rgba(124, 58, 237, 0.3);
  }

  .book-gallery {
    displayenche truncated...
    gap: 16px;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  }

  .book-card {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 20px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.72);
    border: 1px solid rgba(255, 255, 255, 0.55);
    box-shadow: inset 0 0 0 1px rgba(250, 220, 255, 0.55);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
  }

  .book-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 18px 40px rgba(198, 107, 231, 0.22);
  }

  body.books-page.dark-theme .book-card {
    background: rgba(17, 13, 30, 0.75);
    border: 1px solid rgba(124, 58, 237, 0.3);
    box-shadow: inset 0 0 0 1px rgba(12, 8, 24, 0.72);
  }

  .book-card h3 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 700;
  }

  .book-meta {
    font-size: 0.85rem;
    opacity: 0.75;
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
  }

  .book-cover {
    max-width: 160px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.55);
    box-shadow: 0 10px 18px rgba(0, 0, 0, 0.12);
  }

  .book-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .book-actions .btn {
    padding: 10px 14px;
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, rgba(255, 134, 199, 0.85), rgba(198, 107, 231, 0.85));
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .book-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 24px rgba(198, 107, 231, 0.35);
  }

  body.books-page.dark-theme .book-actions .btn {
    background: linear-gradient(135deg, rgba(124, 58, 237, 0.8), rgba(255, 111, 181, 0.8));
  }

  .empty-state {
    display: grid;
    gap: 14px;
    text-align: center;
    padding: 40px 20px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.45);
  }

  body.books-page.dark-theme .empty-state {
    background: rgba(17, 13, 30, 0.7);
    border: 1px solid rgba(124, 58, 237, 0.3);
  }

  .footer-note {
    text-align: center;
    font-size: 0.85rem;
    opacity: 0.7;
    margin-top: 8px;
  }

  @media (max-width: 1024px) {
    .books-overview {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 768px) {
    body.books-page {
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
  }
  </style>
</head>
<body class="books-page">
<div class="container">
  <div class="glass-card top-shell">
    <div class="brand-block">
      <a class="brand-link" href="<?= base_url('index.php') ?>">
        <span class="brand-icon">🌸</span>
        <span class="brand"><?= e(APP_NAME) ?></span>
      </a>
      <p class="brand-tagline">Kitaplarını pembe raflarda sergile, siyah moda geçerek gece düzenlemeleri yap.</p>
      <a class="ghost-btn" href="<?= base_url('dashboard.php') ?>">← Panele dön</a>
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
      <span class="user-chip">📚 <?= e($user['username']) ?></span>
      <div class="nav-links">
        <a class="ghost-btn" href="<?= base_url('notes.php') ?>">📝 Notlar</a>
        <a class="ghost-btn" href="<?= base_url('designer_cover.php') ?>">🎨 Kapak Tasarım</a>
        <a class="ghost-btn" href="<?= base_url('designer_map.php') ?>">🗺️ Harita Tasarım</a>
        <a class="ghost-btn" href="<?= base_url('logout.php') ?>">Çıkış</a>
      </div>
    </div>
  </div>

  <div class="books-overview">
    <div class="glass-card">
      <h2>📚 Kitap Rafın</h2>
      <p>Kitaplarını düzenle, kapaklarını geliştir ve 3D görüntüleyiciyle dene. Tema düğmesi her rafta çalışır.</p>
      <a class="cta-btn" href="<?= base_url('book_new.php') ?>">➕ Yeni Kitap Oluştur</a>
      <div class="stat-pill">✨ Toplam <?= number_format(count($books), 0, ',', '.') ?> kitap</div>
    </div>

    <div class="glass-card">
      <h2>🔥 Son Eklenenler</h2>
      <?php if (empty($books)): ?>
        <div class="empty-state">
          <div style="font-size:42px;">📭</div>
          <p>Henüz kitap eklenmemiş. İlk kitabını oluşturarak Craftrolle raflarını doldurabilirsin.</p>
          <a class="cta-btn" href="<?= base_url('book_new.php') ?>">📕 İlk Kitabımı Oluştur</a>
        </div>
      <?php else: ?>
        <div class="book-gallery">
          <?php foreach ($books as $book): ?>
            <div class="book-card">
              <h3><?= e($book['title']) ?></h3>
              <div class="book-meta">
                <span>📆 <?= e(date('d.m.Y H:i', strtotime($book['created_at']))) ?></span>
                <span>🔒 <?= e(ucfirst($book['visibility'])) ?></span>
              </div>
              <?php if ($book['cover_path']): ?>
                <img class="book-cover" src="<?= base_url('../' . ltrim($book['cover_path'], '/')) ?>" alt="Kitap kapağı">
              <?php endif; ?>
              <div class="book-actions">
                <a class="btn" href="<?= base_url('book_view.php?id=' . (int)$book['id']) ?>">📖 3D Görüntüle</a>
                <a class="btn" href="<?= base_url('book_edit.php?id=' . (int)$book['id']) ?>">✏️ Düzenle</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="footer-note">© <?= date('Y') ?> <?= e(APP_NAME) ?> · Craftrolle kitap rafı</div>
</div>

<script>
(function() {
  const themeToggle = document.getElementById('theme-toggle');
  const themeThumb = document.getElementById('theme-thumb');
  const themeLabel = document.getElementById('theme-label');
  const storageKey = 'craft-books-theme';

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
