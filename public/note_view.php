<?php
// public/note_view.php — tek notu göster + aynı sayfada düzenle/sil + otomatik taslak
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';

require_login();

function safe_redirect($url) {
  if (!headers_sent()) header("Location: $url");
  else echo '<script>location.href=' . json_encode($url) . ';</script>';
  exit;
}

$uid = $_SESSION['user_id'] ?? null;
$id  = (int)($_GET['id'] ?? 0);
if (!$uid || $id <= 0) { safe_redirect(base_url('note.php')); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();

  if (isset($_POST['do_update'])) {
    $title   = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    if ($content === '') { $content = $_POST['rte_fallback'] ?? ''; }

    $st = db()->prepare("UPDATE notes SET title=?, content=?, updated_at=? WHERE id=? AND user_id=?");
    $st->execute([$title, $content, date('c'), $id, $uid]);
    safe_redirect(base_url('note_view.php?id=' . $id . '&updated=1'));
  }

  if (isset($_POST['do_delete'])) {
    $st = db()->prepare("DELETE FROM notes WHERE id=? AND user_id=?");
    $st->execute([$id, $uid]);
    safe_redirect(base_url('note.php?deleted=1'));
  }

  safe_redirect(base_url('note_view.php?id=' . $id));
}

$st = db()->prepare("SELECT id,user_id,title,content,updated_at FROM notes WHERE id=? AND user_id=?");
$st->execute([$id, $uid]);
$note = $st->fetch(PDO::FETCH_ASSOC);
if (!$note) { safe_redirect(base_url('note.php')); }

$user = current_user();
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($note['title']) ?> — <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
  body.notes-page {
    font-family: 'Inter', 'Segoe UI', Tahoma, sans-serif;
    min-height: 100vh;
    padding: 36px 20px 48px;
    background: radial-gradient(circle at 12% 18%, #fff1fb 0%, #ffe4f3 40%, #f6edff 72%, #f3f3ff 100%);
    color: #3c2950;
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
    background: linear-gradient(135deg, rgba(255, 183, 224, 0.9), rgba(247, 207, 255, 0.7));
  }

  body.notes-page::after {
    width: 360px;
    height: 360px;
    bottom: -140px;
    right: -80px;
    background: linear-gradient(135deg, rgba(124, 58, 237, 0.7), rgba(255, 107, 157, 0.7));
  }

  body.notes-page.dark-theme {
    background: radial-gradient(circle at 20% 20%, #1a1230 0%, #110a22 45%, #090616 100%);
    color: #f1eaff;
  }

  body.notes-page.dark-theme::before,
  body.notes-page.dark-theme::after {
    opacity: 0.25;
    transform: scale(1.08);
  }

  .notes-page .container {
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
    box-shadow: 0 18px 48px rgba(197, 135, 255, 0.2);
    backdrop-filter: blur(24px);
    transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
  }

  .glass-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 26px 64px rgba(198, 107, 231, 0.28);
  }

  body.notes-page.dark-theme .glass-card {
    background: rgba(19, 14, 32, 0.75);
    border: 1px solid rgba(124, 58, 237, 0.32);
    box-shadow: 0 20px 60px rgba(6, 3, 14, 0.6);
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
    background: linear-gradient(135deg, #ff75bf, #c66be7);
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
    color: #4e2e66;
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

  body.notes-page.dark-theme .theme-toggle {
    background: rgba(23, 18, 39, 0.75);
    border: 1px solid rgba(124, 58, 237, 0.35);
    color: #f4ddff;
    box-shadow: 0 14px 34px rgba(6, 3, 14, 0.6);
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
    border: 1px solid rgba(255, 255, 255, 0.55);
    font-weight: 600;
  }

  body.notes-page.dark-theme .user-chip {
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

  .back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 12px;
    border: 1px solid rgba(79, 47, 100, 0.2);
    background: rgba(255, 255, 255, 0.65);
    text-decoration: none;
    color: inherit;
    font-weight: 600;
    box-shadow: 0 10px 22px rgba(198, 107, 231, 0.18);
  }

  body.notes-page.dark-theme .back-link {
    background: rgba(23, 18, 39, 0.7);
    border: 1px solid rgba(124, 58, 237, 0.3);
  }

  .status-toast {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    border-radius: 14px;
    border: 1px solid rgba(57, 167, 116, 0.3);
    background: rgba(64, 180, 127, 0.12);
    color: #236b47;
    font-weight: 600;
  }

  body.notes-page.dark-theme .status-toast {
    background: rgba(53, 188, 134, 0.15);
    border-color: rgba(124, 221, 160, 0.35);
    color: #88f6bb;
  }

  .draft-status-chip {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.45);
    font-size: 0.9rem;
    color: inherit;
  }

  body.notes-page.dark-theme .draft-status-chip {
    background: rgba(23, 18, 39, 0.7);
    border: 1px solid rgba(124, 58, 237, 0.3);
  }

  .detail-grid {
    display: grid;
    gap: 18px;
    grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
    align-items: start;
  }

  .reading-pane {
    display: grid;
    gap: 18px;
  }

  .note-title {
    font-size: 1.55rem;
    line-height: 1.3;
  }

  .meta {
    font-size: 0.9rem;
    opacity: 0.7;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .reading-box {
    border-radius: 18px;
    padding: 24px;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.6);
    min-height: 320px;
    box-shadow: inset 0 0 0 1px rgba(245, 224, 255, 0.65);
    color: #31223f;
    line-height: 1.7;
  }

  .reading-box article p {
    margin-bottom: 1em;
  }

  body.notes-page.dark-theme .reading-box {
    background: rgba(17, 12, 28, 0.85);
    border: 1px solid rgba(124, 58, 237, 0.35);
    color: #efe3ff;
    box-shadow: inset 0 0 0 1px rgba(18, 10, 36, 0.65);
  }

  .edit-form label {
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 6px;
    display: block;
  }

  .edit-form input[type="text"] {
    width: 100%;
    border-radius: 12px;
    border: 1px solid rgba(125, 73, 148, 0.22);
    padding: 12px 14px;
    background: rgba(255, 255, 255, 0.75);
    color: inherit;
    font-size: 1rem;
    transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
  }

  .edit-form input[type="text"]:focus {
    outline: none;
    border-color: rgba(198, 107, 231, 0.6);
    box-shadow: 0 0 0 4px rgba(198, 107, 231, 0.18);
    background: rgba(255, 255, 255, 0.9);
  }

  body.notes-page.dark-theme .edit-form input[type="text"] {
    background: rgba(18, 14, 36, 0.75);
    border: 1px solid rgba(124, 58, 237, 0.28);
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
    background: rgba(255, 255, 255, 0.78);
    padding: 16px;
    color: inherit;
    line-height: 1.6;
    font-size: 1rem;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.4);
    transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
  }

  body.notes-page.dark-theme .rte {
    background: rgba(18, 14, 36, 0.8);
    border: 1px solid rgba(124, 58, 237, 0.32);
    box-shadow: inset 0 0 0 1px rgba(9, 6, 20, 0.6);
  }

  .btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
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

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 22px 42px rgba(197, 106, 230, 0.45);
  }

  .btn-danger {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, #f87171, #b91c1c);
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.25s ease, box-shadow 0.3s ease;
  }

  .btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 32px rgba(185, 28, 28, 0.35);
  }

  .button-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 16px;
  }

  .footer-note {
    text-align: center;
    font-size: 0.85rem;
    opacity: 0.7;
    margin-top: 8px;
  }

  @media (max-width: 1024px) {
    .detail-grid {
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

    .button-row {
      flex-direction: column;
    }

    .btn-primary,
    .btn-danger {
      width: 100%;
      justify-content: center;
    }
  }
</style>
</head>
<body class="notes-page note-view">
<div class="container">
  <div class="glass-card top-shell">
    <div class="brand-block">
      <a class="brand-link" href="<?= base_url('index.php') ?>">
        <span class="brand-icon">🌸</span>
        <span class="brand"><?= e(APP_NAME) ?></span>
      </a>
      <p class="brand-tagline">Notunu iki pencerede yönet: Solda pembe okuma alanı, sağda siyah mod bile destekleyen düzenleyici.</p>
      <a class="back-link" href="<?= base_url('note.php') ?>">← Notlarıma geri dön</a>
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
      <span class="user-chip">👋 <?= e($user['username']) ?></span>
      <div class="nav-links">
        <a class="ghost-btn" href="<?= base_url('notes.php') ?>">📝 Not Yaz</a>
        <a class="ghost-btn" href="<?= base_url('books.php') ?>">📚 Kitaplar</a>
        <a class="ghost-btn" href="<?= base_url('logout.php') ?>">Çıkış</a>
      </div>
    </div>
  </div>

  <?php if (isset($_GET['updated'])): ?>
    <div class="glass-card status-toast">✅ Değişiklikler kaydedildi.</div>
  <?php endif; ?>

  <div id="draft-status" class="draft-status-chip">💾 Taslak hazır</div>

  <div class="detail-grid">
    <div class="glass-card reading-pane">
      <div>
        <h1 class="note-title"><?= e($note['title']) ?></h1>
        <div class="meta">⏱️ <?= e(date('d.m.Y H:i', strtotime($note['updated_at']))) ?></div>
      </div>
      <div class="reading-box">
        <article><?= $note['content'] ?: '<em style="color:#888">Bu notun içeriği boş.</em>' ?></article>
      </div>
    </div>

    <div class="glass-card">
      <h2 style="margin-bottom: 12px;">🛠️ Notu Düzenle</h2>
      <p style="font-size:0.88rem; opacity:0.75; margin-bottom:14px;">Değişiklikler otomatik taslak olarak tarayıcında tutulur. Kaydettiğinde sol tarafta güncel hali görünecek.</p>
      <form method="post" id="note-update-form" class="edit-form" action="<?= e(base_url('note_view.php?id='.$note['id'])) ?>" data-draft-key="note_draft_user_<?= (int)$uid ?>_id_<?= (int)$note['id'] ?>">
        <?php csrf_field(); ?>
        <label for="note-title">Başlık</label>
        <input id="note-title" name="title" type="text" value="<?= e($note['title']) ?>">

        <div class="toolbar" style="margin:12px 0;">
          <button type="button" data-cmd="bold" class="btn">B</button>
          <button type="button" data-cmd="italic" class="btn"><i>İ</i></button>
          <button type="button" data-cmd="underline" class="btn"><u>A</u></button>
          <button type="button" data-cmd="h1" class="btn">Başlık</button>
          <button type="button" data-cmd="ul" class="btn">Liste</button>
        </div>

        <div id="rte" class="rte" contenteditable="true" spellcheck="true"><?= $note['content'] ?></div>

        <input type="hidden" name="content" id="content-hidden">
        <textarea name="rte_fallback" id="rte-fallback" style="display:none"></textarea>

        <div class="button-row">
          <button type="submit" class="btn-primary" name="do_update" value="1">💾 Güncelle</button>
        </div>
      </form>

      <form id="note-delete-form" method="post" action="<?= e(base_url('note_view.php?id='.$note['id'])) ?>" style="margin-top:16px" onsubmit="return confirm('Bu not silinsin mi?')">
        <?php csrf_field(); ?>
        <button type="submit" class="btn-danger" name="do_delete" value="1">🗑️ Sil</button>
      </form>
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
<script src="../assets/js/notes_editor.js?v=4"></script>
</body>
</html>
