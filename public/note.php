<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';

require_login();

$uid = $_SESSION['user_id'] ?? null;
$st = db()->prepare("SELECT id, title, content, updated_at FROM notes WHERE user_id=? ORDER BY updated_at DESC");
$st->execute([$uid]);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

$user = current_user();
$currentScript = basename($_SERVER['PHP_SELF'] ?? '');
$navLinks = [
  ['href' => 'dashboard.php',      'icon' => '📊', 'label' => 'Panel'],
  ['href' => 'books.php',          'icon' => '📚', 'label' => 'Kitaplarım'],
  ['href' => 'book_new.php',       'icon' => '➕', 'label' => 'Yeni Kitap'],
  ['href' => 'note.php',           'icon' => '📒', 'label' => 'Notlarım'],
  ['href' => 'notes.php',          'icon' => '📝', 'label' => 'Not Yaz'],
  ['href' => 'designer_cover.php', 'icon' => '🎨', 'label' => 'Kapak Tasarım'],
  ['href' => 'designer_map.php',   'icon' => '🗺️', 'label' => 'Harita Tasarım'],
  ['href' => 'eglence.php',        'icon' => '💡', 'label' => 'Eğlence'],
];

$noteCount = count($rows);
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(APP_NAME) ?> — Notlarım</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
  body.notes-page {
    font-family: 'Inter', 'Segoe UI', Tahoma, sans-serif;
    min-height: 100vh;
    padding: 36px 20px 48px;
    background: radial-gradient(circle at 10% 20%, #fff0fa 0%, #ffe0f1 35%, #ffeaf7 60%, #fdf5ff 100%);
    color: #412a4f;
    transition: background 0.45s ease, color 0.45s ease;
    position: relative;
    overflow-x: hidden;
  }

  body.notes-page::before,
  body.notes-page::after {
    content: '';
    position: fixed;
    border-radius: 50%;
    filter: blur(140px);
    opacity: 0.55;
    z-index: 0;
    transition: opacity 0.5s ease, transform 0.6s ease;
  }

  body.notes-page::before {
    width: 420px;
    height: 420px;
    top: -120px;
    left: -80px;
    background: linear-gradient(135deg, #ffb6d5 0%, #ffd7f0 100%);
  }

  body.notes-page::after {
    width: 360px;
    height: 360px;
    bottom: -140px;
    right: -80px;
    background: linear-gradient(135deg, rgba(124, 58, 237, 0.75), rgba(255, 107, 157, 0.75));
  }

  body.notes-page.dark-theme {
    background: radial-gradient(circle at 15% 15%, #241632 0%, #160f29 35%, #0f0b1e 70%, #090714 100%);
    color: #f2e9ff;
  }

  body.notes-page.dark-theme::before,
  body.notes-page.dark-theme::after {
    opacity: 0.25;
    transform: scale(1.05);
  }

  .notes-page .container {
    max-width: 1200px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    gap: 18px;
  }

  .glass-card {
    background: rgba(255, 255, 255, 0.72);
    border-radius: 22px;
    border: 1px solid rgba(255, 255, 255, 0.55);
    padding: 26px;
    box-shadow: 0 18px 48px rgba(255, 153, 211, 0.18);
    backdrop-filter: blur(24px);
    transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
  }

  .glass-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 26px 64px rgba(211, 135, 255, 0.25);
  }

  body.notes-page.dark-theme .glass-card {
    background: rgba(13, 10, 26, 0.72);
    border: 1px solid rgba(124, 58, 237, 0.35);
    box-shadow: 0 20px 60px rgba(8, 4, 20, 0.55);
  }

  body.notes-page.dark-theme .glass-card:hover {
    box-shadow: 0 26px 70px rgba(124, 58, 237, 0.45);
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
    gap: 6px;
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
    background: linear-gradient(135deg, #ff6fab, #c66be7);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .brand-tagline {
    font-size: 0.92rem;
    opacity: 0.72;
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
    background: rgba(255, 255, 255, 0.55);
    color: #4f2f64;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 10px 28px rgba(255, 153, 211, 0.25);
    transition: transform 0.25s ease, box-shadow 0.3s ease, border-color 0.3s ease;
  }

  .theme-toggle:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 36px rgba(198, 107, 231, 0.32);
  }

  .theme-toggle:focus-visible {
    outline: 2px solid rgba(198, 107, 231, 0.4);
    outline-offset: 3px;
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

  body.notes-page.dark-theme .theme-toggle {
    background: rgba(26, 20, 42, 0.75);
    border: 1px solid rgba(124, 58, 237, 0.3);
    color: #f0dcff;
    box-shadow: 0 14px 34px rgba(8, 4, 20, 0.6);
  }

  body.notes-page.dark-theme .toggle-track {
    background: linear-gradient(135deg, rgba(124, 58, 237, 0.6), rgba(18, 10, 56, 0.6));
    border: 1px solid rgba(124, 58, 237, 0.4);
  }

  body.notes-page.dark-theme .toggle-thumb {
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
    border: 1px solid rgba(255, 255, 255, 0.6);
    font-weight: 600;
  }

  body.notes-page.dark-theme .user-chip {
    background: rgba(23, 19, 36, 0.75);
    border: 1px solid rgba(124, 58, 237, 0.35);
    color: #f5deff;
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

  body.notes-page.dark-theme .ghost-btn:hover {
    background: rgba(124, 58, 237, 0.2);
  }

  .page-nav {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
  }

  .page-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.58);
    border: 1px solid rgba(255, 255, 255, 0.45);
    color: #4d2e62;
    font-weight: 600;
    text-decoration: none;
    transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease, border-color 0.25s ease;
  }

  .page-link:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 32px rgba(198, 107, 231, 0.18);
  }

  .page-link.is-active {
    background: linear-gradient(135deg, #ff7abf, #c66be7);
    color: #fff;
    border-color: transparent;
    box-shadow: 0 20px 40px rgba(198, 107, 231, 0.32);
  }

  body.notes-page.dark-theme .page-link {
    background: rgba(18, 14, 36, 0.75);
    border: 1px solid rgba(124, 58, 237, 0.25);
    color: #e6d6ff;
  }

  body.notes-page.dark-theme .page-link:hover {
    box-shadow: 0 18px 36px rgba(124, 58, 237, 0.28);
  }

  body.notes-page.dark-theme .page-link.is-active {
    background: linear-gradient(135deg, #7c3aed, #ff6fb5);
    box-shadow: 0 22px 44px rgba(124, 58, 237, 0.4);
  }

  .notes-overview {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 18px;
  }

  .stat-pill {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.45);
    font-weight: 600;
    margin-top: 18px;
  }

  body.notes-page.dark-theme .stat-pill {
    background: rgba(23, 18, 39, 0.65);
    border: 1px solid rgba(124, 58, 237, 0.3);
  }

  .cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    border-radius: 14px;
    background: linear-gradient(135deg, #ff7fc7, #c56ae6);
    color: #fff;
    font-weight: 700;
    text-decoration: none;
    margin-top: 20px;
    box-shadow: 0 16px 30px rgba(197, 106, 230, 0.35);
    transition: transform 0.25s ease, box-shadow 0.3s ease;
  }

  .cta-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 22px 42px rgba(197, 106, 230, 0.45);
  }

  body.notes-page.dark-theme .cta-btn {
    background: linear-gradient(135deg, #7c3aed, #ff6fb5);
  }

  .note-collection h2 {
    font-size: 1.4rem;
    margin-bottom: 12px;
  }

  .note-search {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
    padding: 10px 14px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.45);
  }

  .note-search input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 0.95rem;
    color: inherit;
  }

  .note-search input:focus {
    outline: none;
  }

  body.notes-page.dark-theme .note-search {
    background: rgba(18, 14, 36, 0.72);
    border: 1px solid rgba(124, 58, 237, 0.3);
  }

  .results-meta {
    font-size: 0.85rem;
    opacity: 0.75;
    margin-bottom: 12px;
  }

  .note-gallery {
    display: grid;
    gap: 16px;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  }

  .note-card {
    position: relative;
    border-radius: 18px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.72);
    border: 1px solid rgba(255, 255, 255, 0.55);
    box-shadow: inset 0 0 0 1px rgba(250, 220, 255, 0.55);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    gap: 14px;
    min-height: 180px;
  }

  .note-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 18px 40px rgba(198, 107, 231, 0.22);
  }

  body.notes-page.dark-theme .note-card {
    background: rgba(18, 14, 36, 0.72);
    border: 1px solid rgba(124, 58, 237, 0.3);
    box-shadow: inset 0 0 0 1px rgba(12, 8, 24, 0.72);
  }

  body.notes-page.dark-theme .note-card:hover {
    box-shadow: 0 22px 48px rgba(124, 58, 237, 0.32);
  }

  .note-card .note-date {
    font-size: 0.85rem;
    opacity: 0.75;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .note-card h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
  }

  .note-card p {
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.6;
    opacity: 0.85;
  }

  .note-card .note-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.45);
    font-size: 0.78rem;
    font-weight: 600;
    align-self: flex-start;
  }

  body.notes-page.dark-theme .note-card .note-tag {
    background: rgba(23, 18, 39, 0.65);
    border: 1px solid rgba(124, 58, 237, 0.3);
  }

  .no-results {
    display: none;
    padding: 24px 18px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.55);
    border: 1px solid rgba(255, 255, 255, 0.42);
    font-size: 0.95rem;
    text-align: center;
  }

  body.notes-page.dark-theme .no-results {
    background: rgba(18, 14, 36, 0.72);
    border: 1px solid rgba(124, 58, 237, 0.3);
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

  body.notes-page.dark-theme .empty-state {
    background: rgba(18, 14, 36, 0.7);
    border: 1px solid rgba(124, 58, 237, 0.3);
  }

  .helper-card {
    margin-top: 22px;
    display: grid;
    gap: 10px;
  }

  .helper-card strong {
    font-size: 0.92rem;
  }

  .helper-card ul {
    margin-left: 18px;
    line-height: 1.6;
    font-size: 0.9rem;
    opacity: 0.8;
  }

  kbd {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    background: rgba(79, 47, 100, 0.12);
    border: 1px solid rgba(79, 47, 100, 0.18);
    font-size: 0.85rem;
    font-weight: 600;
    font-family: 'Inter', 'Segoe UI', sans-serif;
  }

  body.notes-page.dark-theme kbd {
    background: rgba(124, 58, 237, 0.2);
    border-color: rgba(124, 58, 237, 0.45);
  }

  .footer-note {
    text-align: center;
    font-size: 0.85rem;
    opacity: 0.7;
    margin-top: 6px;
  }

  @media (max-width: 1100px) {
    .notes-overview {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 768px) {
    body.notes-page {
      padding: 24px 16px 36px;
    }

    .glass-card {
      padding: 22px;
    }

    .page-nav {
      grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    }

    .theme-toggle {
      width: 100%;
      justify-content: center;
    }

    .header-actions {
      width: 100%;
      justify-content: space-between;
    }

    .user-chip {
      width: 100%;
      justify-content: center;
    }

    .notes-overview {
      gap: 16px;
    }
  }
</style>
</head>
<body class="notes-page">
<div class="container">
  <div class="glass-card top-shell">
    <div class="brand-block">
      <a class="brand-link" href="<?= base_url('index.php') ?>">
        <span class="brand-icon">🌸</span>
        <span class="brand"><?= e(APP_NAME) ?></span>
      </a>
      <p class="brand-tagline">Tüm notlarını tek yerde topladık. Tema düğmesi ile pembe ya da siyah rafta görüntüleyebilirsin.</p>
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
      <?php if ($user): ?>
        <span class="user-chip">📚 <?= e($user['username']) ?></span>
        <a class="ghost-btn" href="<?= base_url('logout.php') ?>">Çıkış</a>
      <?php endif; ?>
    </div>
  </div>

  <nav class="page-nav">
    <?php foreach ($navLinks as $link): ?>
      <?php $isActive = $currentScript === $link['href']; ?>
      <a class="page-link<?= $isActive ? ' is-active' : '' ?>" href="<?= base_url($link['href']) ?>"<?= $isActive ? ' aria-current="page"' : '' ?>>
        <span><?= $link['icon'] ?></span>
        <span><?= e($link['label']) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="notes-overview">
    <div class="glass-card">
      <h2>📒 Not Arşivin</h2>
      <p>Güncellediğin tüm notlar ters kronolojik sıralandı. İncelerken tema düğmesi ile ışık/koyu raflar arasında geçiş yapabilirsin.</p>
      <a class="cta-btn" href="<?= base_url('notes.php') ?>">📝 Yeni Not Yaz</a>
      <div class="stat-pill">✨ Toplam <?= number_format($noteCount, 0, ',', '.') ?> not</div>
      <div class="helper-card">
        <strong>İpuçları</strong>
        <ul>
          <li>Bir not kartına tıklayarak detay sayfasına geç.</li>
          <li><kbd>Ctrl</kbd> + <kbd>F</kbd> ile tarayıcı aramasını kullanarak başlık içinde ara.</li>
        </ul>
      </div>
    </div>

    <div class="glass-card note-collection">
      <h2>🔖 Son Notlar</h2>
      <?php if (!$rows): ?>
        <div class="empty-state">
          <div style="font-size:42px;">🕊️</div>
          <p>Henüz not eklememişsin. Pembe ilham butonuna basarak ilk notunu yazabilirsin.</p>
          <a class="cta-btn" href="<?= base_url('notes.php') ?>">📝 İlk Notumu Yaz</a>
        </div>
      <?php else: ?>
        <div class="note-search">
          <span aria-hidden="true">🔍</span>
          <input id="note-search" type="search" placeholder="Başlıkta ara...">
        </div>
        <div class="results-meta">
          <span id="results-total">Toplam <?= number_format($noteCount, 0, ',', '.') ?> not</span>
        </div>
        <div class="note-gallery" id="note-gallery">
          <?php foreach ($rows as $note): ?>
            <?php
              $updated = strtotime($note['updated_at'] ?? '');
              $excerpt = trim(strip_tags($note['content'] ?? ''));
              if (mb_strlen($excerpt) > 120) {
                $excerpt = mb_substr($excerpt, 0, 117) . '…';
              }
            ?>
            <a class="note-card" href="<?= base_url('note_view.php?id=' . (int) $note['id']) ?>" data-title="<?= e($note['title']) ?>" data-body="<?= e($excerpt) ?>">
              <div class="note-date">⏱️ <?= $updated ? e(date('d.m.Y H:i', $updated)) : '—' ?></div>
              <h3><?= e($note['title']) ?></h3>
              <?php if ($excerpt): ?>
                <p><?= e($excerpt) ?></p>
              <?php endif; ?>
              <span class="note-tag">Görüntüle & düzenle</span>
            </a>
          <?php endforeach; ?>
        </div>
        <div class="no-results" id="no-results">Aramanıza uygun not bulunamadı.</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="footer-note">© <?= date('Y') ?> <?= e(APP_NAME) ?> · Craftrolle not arşivi</div>
</div>

<script>
(function() {
  const themeToggle = document.getElementById('theme-toggle');
  const themeThumb = document.getElementById('theme-thumb');
  const themeLabel = document.getElementById('theme-label');
  const storageKey = 'craft-notes-theme';

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

(function() {
  const searchInput = document.getElementById('note-search');
  const gallery = document.getElementById('note-gallery');
  const totalLabel = document.getElementById('results-total');
  const noResults = document.getElementById('no-results');
  if (!searchInput || !gallery) { return; }

  const cards = Array.from(gallery.querySelectorAll('.note-card'));
  const totalCount = cards.length;

  const updateTotals = (visible) => {
    if (totalLabel) {
      totalLabel.textContent = `${visible} / ${totalCount} not`;
    }
    if (noResults) {
      noResults.style.display = visible === 0 ? 'block' : 'none';
    }
  };

  const filterNotes = (term) => {
    const normalized = term.trim().toLowerCase();
    let visible = 0;
    cards.forEach(card => {
      const title = (card.dataset.title || '').toLowerCase();
      const body = (card.dataset.body || '').toLowerCase();
      const match = !normalized || title.includes(normalized) || body.includes(normalized);
      card.style.display = match ? 'flex' : 'none';
      if (match) { visible += 1; }
    });
    updateTotals(visible);
  };

  searchInput.addEventListener('input', (event) => {
    filterNotes(event.target.value);
  });

  filterNotes('');
})();
</script>
</body>
</html>
