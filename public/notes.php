<?php
// public/notes.php — Not yaz + sağda önizleme + otomatik taslak
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';

require_login();

$uid = $_SESSION['user_id'] ?? null;
$created_note = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();

  $title = trim($_POST['title'] ?? 'Not');

  // 1) JS'nin doldurduğu hidden 'content'
  // 2) JS çalışmasa bile fallback textarea
  $content = $_POST['content'] ?? '';
  if ($content === '') { $content = $_POST['rte_fallback'] ?? ''; }

  $st = db()->prepare("INSERT INTO notes(user_id,title,content,updated_at) VALUES(?,?,?,?)");
  $st->execute([$uid, $title, $content, date('c')]);

  $id = (int) db()->lastInsertId();
  $st = db()->prepare("SELECT * FROM notes WHERE id=? AND user_id=?");
  $st->execute([$id, $uid]);
  $created_note = $st->fetch(PDO::FETCH_ASSOC);
}

$user = current_user();
$currentScript = basename($_SERVER['PHP_SELF'] ?? '');
$navLinks = [
  ['href' => 'dashboard.php',      'icon' => '📊', 'label' => 'Panel'],
  ['href' => 'books.php',          'icon' => '📚', 'label' => 'Kitaplarım'],
  ['href' => 'book_new.php',       'icon' => '➕', 'label' => 'Yeni Kitap'],
  ['href' => 'notes.php',          'icon' => '📝', 'label' => 'Notlar'],
  ['href' => 'designer_cover.php', 'icon' => '🎨', 'label' => 'Kapak Tasarım'],
  ['href' => 'designer_map.php',   'icon' => '🗺️', 'label' => 'Harita Tasarım'],
  ['href' => 'eglence.php',        'icon' => '💡', 'label' => 'Eğlence'],
];
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(APP_NAME) ?> — Not Yaz</title>
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
    opacity: 0.75;
    max-width: 380px;
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

  .draft-status {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.55);
    border: 1px solid rgba(255, 255, 255, 0.4);
    font-size: 0.9rem;
    color: inherit;
    margin-left: 4px;
  }

  body.notes-page.dark-theme .draft-status {
    background: rgba(23, 18, 39, 0.7);
    border: 1px solid rgba(124, 58, 237, 0.3);
  }

  .notes-grid {
    display: grid;
    gap: 20px;
    grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
    align-items: start;
  }

  .notes-grid h2 {
    font-size: 1.4rem;
    margin-bottom: 12px;
  }

  .micro-copy {
    font-size: 0.85rem;
    opacity: 0.72;
    margin-top: -6px;
    margin-bottom: 10px;
  }

  .notes-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  .notes-form label {
    font-weight: 600;
    font-size: 0.95rem;
    letter-spacing: 0.01em;
  }

  .notes-form input,
  .notes-form textarea {
    width: 100%;
    border-radius: 12px;
    border: 1px solid rgba(125, 73, 148, 0.22);
    padding: 12px 14px;
    background: rgba(255, 255, 255, 0.7);
    color: inherit;
    font-size: 1rem;
    transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
  }

  .notes-form input:focus,
  .notes-form textarea:focus,
  .rte:focus {
    outline: none;
    border-color: rgba(198, 107, 231, 0.6);
    box-shadow: 0 0 0 4px rgba(198, 107, 231, 0.18);
    background: rgba(255, 255, 255, 0.85);
  }

  body.notes-page.dark-theme .notes-form input,
  body.notes-page.dark-theme .notes-form textarea {
    background: rgba(21, 16, 34, 0.7);
    border: 1px solid rgba(124, 58, 237, 0.28);
  }

  body.notes-page.dark-theme .notes-form input:focus,
  body.notes-page.dark-theme .notes-form textarea:focus,
  body.notes-page.dark-theme .rte:focus {
    border-color: rgba(255, 111, 181, 0.7);
    box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.22);
    background: rgba(21, 16, 34, 0.9);
  }

  .toolbar {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
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

  .toolbar .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 18px rgba(198, 107, 231, 0.35);
  }

  body.notes-page.dark-theme .toolbar .btn {
    background: linear-gradient(135deg, rgba(124, 58, 237, 0.8), rgba(255, 111, 181, 0.8));
  }

  .rte {
    min-height: 260px;
    border-radius: 16px;
    border: 1px solid rgba(125, 73, 148, 0.22);
    background: rgba(255, 255, 255, 0.75);
    padding: 16px;
    color: inherit;
    line-height: 1.6;
    font-size: 1rem;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.4);
    transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
  }

  .rte p {
    margin-bottom: 0.8em;
  }

  body.notes-page.dark-theme .rte {
    background: rgba(21, 16, 34, 0.78);
    border: 1px solid rgba(124, 58, 237, 0.3);
    box-shadow: inset 0 0 0 1px rgba(9, 6, 20, 0.6);
  }

  .notes-form button[type="submit"] {
    align-self: flex-start;
    padding: 12px 20px;
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, #ff7fc7, #c56ae6);
    color: #fff;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 16px 30px rgba(197, 106, 230, 0.35);
    transition: transform 0.25s ease, box-shadow 0.3s ease;
  }

  .notes-form button[type="submit"]:hover {
    transform: translateY(-2px);
    box-shadow: 0 22px 42px rgba(197, 106, 230, 0.45);
  }

  body.notes-page.dark-theme .notes-form button[type="submit"] {
    background: linear-gradient(135deg, #7c3aed, #ff6fb5);
  }

  .preview-card {
    display: flex;
    flex-direction: column;
    gap: 14px;
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, 0.5);
    background: rgba(255, 255, 255, 0.75);
    padding: 18px;
    min-height: 340px;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35);
  }

  .preview-title {
    font-weight: 700;
    font-size: 1.05rem;
  }

  .preview-content {
    background: rgba(255, 255, 255, 0.95);
    color: #2c2436;
    padding: 16px;
    border-radius: 12px;
    max-height: 320px;
    overflow: auto;
    font-size: 0.98rem;
    line-height: 1.65;
    box-shadow: inset 0 0 0 1px rgba(242, 213, 255, 0.6);
  }

  .preview-content::-webkit-scrollbar {
    width: 8px;
  }

  .preview-content::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.6);
    border-radius: 12px;
  }

  .preview-content::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #ff9ed3, #c66be7);
    border-radius: 12px;
  }

  .preview-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.85rem;
    opacity: 0.75;
  }

  .preview-empty {
    font-size: 0.95rem;
    opacity: 0.78;
    background: rgba(255, 255, 255, 0.55);
    border-radius: 12px;
    padding: 16px;
    line-height: 1.6;
  }

  body.notes-page.dark-theme .preview-card {
    background: rgba(21, 16, 36, 0.78);
    border: 1px solid rgba(124, 58, 237, 0.3);
    box-shadow: inset 0 0 0 1px rgba(18, 10, 36, 0.6);
  }

  body.notes-page.dark-theme .preview-content {
    background: rgba(12, 9, 24, 0.92);
    color: #f3eaff;
    box-shadow: inset 0 0 0 1px rgba(124, 58, 237, 0.28);
  }

  body.notes-page.dark-theme .preview-empty {
    background: rgba(18, 14, 36, 0.6);
  }

  .helper-card {
    display: grid;
    gap: 10px;
    margin-top: 16px;
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
    .notes-grid {
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

    .notes-form button[type="submit"] {
      width: 100%;
      justify-content: center;
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
      <p class="brand-tagline">Notlarını yaz, pembe & siyah temalar arasında tek dokunuşla geçiş yap.</p>
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
        <span class="user-chip">👋 <?= e($user['username']) ?></span>
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

  <div id="draft-status" class="draft-status">💾 Taslak modu hazır</div>

  <div class="notes-grid">
    <div class="glass-card">
      <h2>📝 Not Yaz</h2>
      <p class="micro-copy">Taslaklar otomatik olarak tarayıcınızda saklanır. Kaydetmeden ayrılırsanız bile içerik kaybolmaz.</p>
      <form method="post" id="note-form" class="notes-form" action="<?= e(base_url('notes.php')) ?>" data-draft-key="notes_draft_user_<?= (int)$uid ?>">
        <?php csrf_field(); ?>

        <div class="toolbar">
          <button type="button" class="btn" data-cmd="bold" title="Kalın">B</button>
          <button type="button" class="btn" data-cmd="italic" title="İtalik"><i>İ</i></button>
          <button type="button" class="btn" data-cmd="underline" title="Altı çizili"><u>A</u></button>
          <button type="button" class="btn" data-cmd="h1" title="Başlık">Başlık</button>
          <button type="button" class="btn" data-cmd="ul" title="Liste">Liste</button>
        </div>

        <label for="note-title">Başlık</label>
        <input id="note-title" name="title" type="text" placeholder="Not başlığı" value="<?= isset($_POST['title']) ? e($_POST['title']) : '' ?>">

        <label for="rte">İçerik</label>
        <div id="rte" class="rte" contenteditable="true" spellcheck="true"></div>

        <input type="hidden" name="content" id="content-hidden">
        <textarea name="rte_fallback" id="rte-fallback" style="display:none"></textarea>

        <button type="submit">Kaydet</button>
      </form>

      <noscript class="micro-copy" style="color:#b85b7f;">
        JavaScript kapalıysa taslak kaydı ve zengin düzenleme çalışmaz.
      </noscript>
    </div>

    <div class="glass-card">
      <h2>👀 Önizleme</h2>
      <?php if ($created_note): ?>
        <div class="preview-card">
          <div class="preview-title"><?= e($created_note['title']) ?></div>
          <div class="preview-content"><?= $created_note['content'] ?></div>
          <div class="preview-meta">
            <span>🗂️ Taslak</span>
            <span><?= e(date('d.m.Y H:i', strtotime($created_note['updated_at']))) ?></span>
          </div>
        </div>
      <?php else: ?>
        <div class="preview-card">
          <div class="preview-empty">
            💡 İlk notunuzu kaydettiğinizde burada pembe bir önizleme kartı oluşur. Tema düğmesi ile siyah moda geçerek gece yazımı yapabilirsiniz.
          </div>
        </div>
      <?php endif; ?>
      <div class="helper-card">
        <strong>🎯 İpucu</strong>
        <ul>
          <li><kbd>Ctrl</kbd> + <kbd>B</kbd> ile kalın yapabilirsiniz.</li>
          <li>Tema düğmesi tercihinizi kaydeder, sayfayı yenileseniz bile aynı kalır.</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="footer-note">© <?= date('Y') ?> <?= e(APP_NAME) ?> · Craftrolle not deneyimi</div>
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
</script>

<script src="../assets/js/notes_editor.js?v=3"></script>
</body>
</html>
