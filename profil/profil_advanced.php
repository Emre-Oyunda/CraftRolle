<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';

$user = current_user();
if (!$user) {
  header('Location: ../public/login.php?next=' . rawurlencode('../profil/profil_advanced.php'));
  exit;
}

$pdo = new PDO('sqlite:' . DB_PATH, null, null, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$pdo->exec("CREATE TABLE IF NOT EXISTS messages (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  from_id INTEGER NOT NULL,
  to_id INTEGER NOT NULL,
  content TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function save_upload(array $f, string $sub=''): ?string {
  if (empty($f['tmp_name']) || !is_uploaded_file($f['tmp_name'])) return null;
  $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
  $ext = $ext ? ('.' . strtolower($ext)) : '';
  $dir = rtrim(UPLOAD_DIR, '/\\') . ($sub ? ('/' . $sub) : '');
  if (!is_dir($dir)) @mkdir($dir, 0777, true);
  $name = uniqid('up_') . $ext;
  $ok   = @move_uploaded_file($f['tmp_name'], $dir . '/' . $name);
  if (!$ok) return null;
  return 'uploads' . ($sub ? ('/' . $sub) : '') . '/' . $name;
}

$errbox = [];
try {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = $_GET['a'] ?? '';

    if ($a === 'post_create') {
      $content = trim((string)($_POST['content'] ?? ''));
      if ($content === '' || mb_strlen($content) > 280) throw new RuntimeException('Gönderi boş/uzun (max 280).');

      $img = null;
      if (!empty($_FILES['post_image'])) $img = save_upload($_FILES['post_image'], 'posts');

      $st = $pdo->prepare("INSERT INTO posts (user_id,type,content,image_path,visibility,created_at) VALUES (?,?,?,?,?,datetime('now'))");
      $st->execute([(int)$user['id'], ($img ? 'photo' : 'text'), $content, $img, 'public']);
      header('Location: ?tab=feed');
      exit;
    }

    if ($a === 'book_share') {
      $title = trim((string)($_POST['title'] ?? ''));
      if ($title === '') throw new RuntimeException('Kitap adı gerekli.');

      $author_name = trim((string)($_POST['author'] ?? '')) ?: null;
      $note        = trim((string)($_POST['note'] ?? '')) ?: null;

      $cover = null;
      if (!empty($_FILES['cover_file'])) $cover = save_upload($_FILES['cover_file'], 'covers');
      if (!$cover) {
        $url = trim((string)($_POST['cover_url'] ?? ''));
        if ($url !== '') $cover = $url;
      }

      $st = $pdo->prepare("INSERT INTO books (user_id,title,author_name,cover_path,note,is_public,created_at) VALUES (?,?,?,?,?,1,datetime('now'))");
      $st->execute([(int)$user['id'], $title, $author_name, $cover, $note]);
      header('Location: ?tab=books');
      exit;
    }

    if ($a === 'message_send') {
      $to_id   = (int)($_POST['to_id'] ?? 0);
      $content = trim((string)($_POST['content'] ?? ''));
      if ($to_id <= 0 || $to_id === (int)$user['id']) throw new RuntimeException('Alıcı seçin.');
      if ($content === '' || mb_strlen($content) > 500) throw new RuntimeException('Mesaj boş/uzun (max 500).');

      $st = $pdo->prepare("INSERT INTO messages (from_id,to_id,content,created_at) VALUES (?,?,?,datetime('now'))");
      $st->execute([(int)$user['id'], $to_id, $content]);
      header('Location: ?tab=messages');
      exit;
    }
  }
} catch (Throwable $e) {
  $errbox[] = $e->getMessage();
}

$tab = $_GET['tab'] ?? 'feed';

$display = $user['display_name'] ?: ($user['username'] ?? $user['email']);
$ava = $user['avatar_path'] ?: ($user['avatar_url'] ?? '');
if (!$ava) {
  $ch = mb_strtoupper(mb_substr($display ?: 'U', 0, 1));
  $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='96' height='96'><defs><linearGradient id='g' x1='0' x2='1'><stop offset='0%' stop-color='#2a2144'/><stop offset='100%' stop-color='#161226'/></linearGradient></defs><rect width='100%' height='100%' rx='16' fill='url(#g)'/><text x='50%' y='55%' text-anchor='middle' font-family='system-ui,-apple-system,Segoe UI,Roboto' font-size='42' fill='#ffd2f0' font-weight='800'>{$ch}</text></svg>";
  $ava = 'data:image/svg+xml;utf8,' . rawurlencode($svg);
} else {
  if (!preg_match('~^https?://~', $ava)) $ava = base_url(ltrim($ava, '/'));
}

$posts_count = (int)$pdo->query("SELECT COUNT(*) FROM posts WHERE user_id=" . (int)$user['id'])->fetchColumn();
$books_count = (int)$pdo->query("SELECT COUNT(*) FROM books WHERE user_id=" . (int)$user['id'])->fetchColumn();
$inbox_count = (int)$pdo->query("SELECT COUNT(*) FROM messages WHERE to_id=" . (int)$user['id'])->fetchColumn();

$active_feed  = $tab === 'feed' ? 'active' : '';
$active_books = $tab === 'books' ? 'active' : '';
$active_msg   = $tab === 'messages' ? 'active' : '';

$posts = $books = $userlist = $msgs = [];
try {
  if ($tab === 'feed') {
    $st = $pdo->query("SELECT p.*, u.display_name, u.email, u.avatar_path, u.avatar_url
                       FROM posts p JOIN users u ON u.id=p.user_id
                       ORDER BY p.created_at DESC LIMIT 50");
    $posts = $st->fetchAll();
  } elseif ($tab === 'books') {
    $st = $pdo->query("SELECT b.*, u.display_name, u.email
                       FROM books b JOIN users u ON u.id=b.user_id
                       ORDER BY b.created_at DESC LIMIT 50");
    $books = $st->fetchAll();
  } elseif ($tab === 'messages') {
    $u = $pdo->prepare("SELECT id, display_name, email FROM users WHERE id<>? ORDER BY display_name ASC, email ASC");
    $u->execute([(int)$user['id']]);
    $userlist = $u->fetchAll();

    $m = $pdo->prepare("SELECT m.*, u.display_name, u.email FROM messages m
                        JOIN users u ON u.id=m.from_id
                        WHERE m.to_id=? ORDER BY m.created_at DESC LIMIT 50");
    $m->execute([(int)$user['id']]);
    $msgs = $m->fetchAll();
  }
} catch (Throwable $e) {
  $errbox[] = 'Veri okunamadı: ' . $e->getMessage();
}

$qtab = fn(string $t) => '?tab=' . $t;
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(APP_NAME) ?> • Profilim</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
  :root {
    --violet-700: #2a2144;
    --violet-500: #7c5bff;
    --violet-300: #bca6ff;
    --pink-400: #f58acb;
    --pink-200: #ffd9f1;
    --text-base: #241d3f;
    --text-muted: rgba(36, 29, 63, 0.68);
    --glass-light: rgba(255, 255, 255, 0.82);
    --glass-dark: rgba(18, 16, 34, 0.9);
  }

  * { box-sizing: border-box; }

  body.profile-page {
    margin: 0;
    min-height: 100vh;
    font-family: 'Inter','Segoe UI',Tahoma,sans-serif;
    color: var(--text-base);
    background:
      radial-gradient(circle at 10% -12%, rgba(255, 214, 244, 0.82), transparent 55%),
      radial-gradient(circle at 90% 0%, rgba(193, 214, 255, 0.62), transparent 55%),
      linear-gradient(135deg, #f5f2ff 0%, #efe4ff 45%, #ffe8f6 100%);
    padding: 60px 18px 90px;
    transition: background 0.35s ease, color 0.35s ease;
  }

  body.profile-page.dark-mode {
    color: #f6ebff;
    background:
      radial-gradient(circle at 12% -12%, rgba(82, 63, 140, 0.55), transparent 55%),
      radial-gradient(circle at 88% 6%, rgba(205, 82, 150, 0.45), transparent 60%),
      linear-gradient(135deg, #0f0b1f 0%, #161229 42%, #21183a 100%);
  }

  .profile-shell {
    max-width: 1180px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 26px;
  }

  .glass-card {
    background: var(--glass-light);
    border-radius: 26px;
    border: 1px solid rgba(255, 255, 255, 0.7);
    padding: 24px 28px;
    box-shadow: 0 26px 70px rgba(124, 90, 210, 0.16);
    backdrop-filter: blur(20px);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
  }

  .glass-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 32px 82px rgba(124, 90, 210, 0.22);
  }

  body.profile-page.dark-mode .glass-card {
    background: var(--glass-dark);
    border-color: rgba(112, 92, 190, 0.36);
    box-shadow: 0 30px 70px rgba(9, 7, 20, 0.68);
  }

  .profile-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
  }

  .brand-link {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 1.6rem;
    font-weight: 700;
    text-decoration: none;
    color: inherit;
  }

  .brand-link .brand-name {
    background: linear-gradient(120deg, var(--pink-400), #8f74ff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .header-nav {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
  }

  .nav-pill,
  .welcome-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 14px;
    border-radius: 999px;
    border: 1px solid rgba(124, 90, 220, 0.26);
    background: rgba(255, 255, 255, 0.82);
    font-weight: 600;
    color: inherit;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .nav-pill:hover { transform: translateY(-2px); box-shadow: 0 18px 40px rgba(124, 90, 220, 0.2); }

  .welcome-pill {
    background: rgba(32, 24, 56, 0.88);
    border-color: rgba(112, 92, 190, 0.36);
    color: #f7ebff;
  }

  body.profile-page.dark-mode .nav-pill {
    background: rgba(28, 24, 50, 0.92);
    border-color: rgba(112, 92, 190, 0.34);
    color: #f4ecff;
  }

  .theme-toggle {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 16px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.68);
    background: linear-gradient(120deg, #fbd9ff, #d6c6ff);
    font-weight: 600;
    color: #3a295b;
    cursor: pointer;
    box-shadow: 0 22px 46px rgba(150, 110, 255, 0.24);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .theme-toggle:hover { transform: translateY(-2px); box-shadow: 0 26px 56px rgba(150, 110, 255, 0.3); }

  body.profile-page.dark-mode .theme-toggle {
    background: rgba(26, 23, 46, 0.92);
    border-color: rgba(112, 92, 190, 0.42);
    color: #f6ebff;
    box-shadow: 0 22px 50px rgba(8, 6, 18, 0.68);
  }

  .profile-tabs {
    display: flex;
    gap: 10px;
  }

  .profile-tabs a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 18px;
    border-radius: 999px;
    border: 1px solid rgba(124, 90, 220, 0.26);
    background: rgba(255, 255, 255, 0.86);
    font-weight: 600;
    color: inherit;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .profile-tabs a:hover { transform: translateY(-2px); }

  .profile-tabs a.active {
    background: linear-gradient(120deg, var(--violet-500), var(--pink-400));
    color: #fff;
    border-color: transparent;
    box-shadow: 0 18px 42px rgba(124, 90, 220, 0.24);
  }

  body.profile-page.dark-mode .profile-tabs a {
    background: rgba(28, 24, 50, 0.92);
    border-color: rgba(112, 92, 190, 0.34);
    color: #f4ecff;
  }

  .profile-grid {
    display: grid;
    gap: 18px;
    grid-template-columns: 280px 1fr 280px;
  }

  @media (max-width: 1100px) {
    .profile-grid { grid-template-columns: 260px 1fr; }
    .profile-column.right { grid-column: 1 / -1; }
  }

  @media (max-width: 820px) {
    body.profile-page { padding: 46px 14px 90px; }
    .profile-grid { grid-template-columns: 1fr; }
  }

  .profile-card { display: grid; gap: 12px; }

  .profile-info {
    display: flex;
    align-items: center;
    gap: 14px;
  }

  .profile-info .avatar {
    width: 74px;
    height: 74px;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid rgba(124, 90, 220, 0.28);
    background: rgba(28, 24, 50, 0.9);
  }

  .profile-info .avatar img { width: 100%; height: 100%; object-fit: cover; }

  .profile-info .name { font-size: 1.18rem; font-weight: 700; }

  .callout { font-size: 0.92rem; color: var(--text-muted); line-height: 1.6; }
  body.profile-page.dark-mode .callout { color: rgba(236, 224, 255, 0.72); }

  .stat-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
  }

  .stat-pill {
    padding: 12px 10px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.86);
    border: 1px solid rgba(124, 90, 220, 0.22);
    text-align: center;
  }

  .stat-pill strong { display: block; font-size: 1.2rem; }
  .stat-pill span { font-size: 0.86rem; color: var(--text-muted); }
  body.profile-page.dark-mode .stat-pill { background: rgba(28, 24, 50, 0.9); color: #f4ecff; }

  .input-control {
    width: 100%;
    padding: 11px 14px;
    border-radius: 14px;
    border: 1px solid rgba(124, 90, 220, 0.24);
    background: rgba(255, 255, 255, 0.9);
    font-size: 1rem;
    color: inherit;
  }

  textarea.input-control { min-height: 90px; resize: vertical; }
  body.profile-page.dark-mode .input-control { background: rgba(28, 24, 50, 0.9); border-color: rgba(112, 92, 190, 0.36); color: #f5ecff; }

  .pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 18px;
    border-radius: 14px;
    border: 1px solid rgba(124, 90, 220, 0.28);
    background: linear-gradient(120deg, var(--violet-500), var(--pink-400));
    color: #fff;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0 22px 48px rgba(124, 90, 220, 0.24);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .pill-btn:hover { transform: translateY(-2px); box-shadow: 0 28px 60px rgba(124, 90, 220, 0.28); }

  .pill-btn.secondary { background: rgba(255, 255, 255, 0.88); color: inherit; border: 1px solid rgba(124, 90, 220, 0.18); box-shadow: none; }
  body.profile-page.dark-mode .pill-btn.secondary { background: rgba(28, 24, 50, 0.88); color: #f5ecff; }

  .feed-list { display: grid; gap: 14px; }

  .feed-item {
    display: grid;
    grid-template-columns: 64px 1fr;
    gap: 12px;
    padding: 16px;
    border-radius: 20px;
    border: 1px solid rgba(124, 90, 220, 0.24);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.86), rgba(255, 255, 255, 0.78));
  }

  body.profile-page.dark-mode .feed-item { background: rgba(28, 24, 50, 0.95); border-color: rgba(112, 92, 190, 0.36); }

  .feed-item .avatar {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid rgba(124, 90, 220, 0.28);
    background: rgba(28, 24, 50, 0.9);
  }

  .feed-item .meta { font-size: 0.84rem; color: rgba(36, 29, 63, 0.6); }
  body.profile-page.dark-mode .feed-item .meta { color: rgba(236, 224, 255, 0.7); }

  .feed-item .content { white-space: pre-wrap; line-height: 1.6; }

  .feed-item .photo {
    margin-top: 10px;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid rgba(124, 90, 220, 0.24);
    background: rgba(28, 24, 50, 0.9);
  }

  .book-grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); }
  .book-card { display: grid; gap: 10px; }
  .book-card .cover { width: 100%; aspect-ratio: 3/4; border-radius: 16px; border: 1px solid rgba(124, 90, 220, 0.24); object-fit: cover; background: rgba(28, 24, 50, 0.9); }

  .message-list { display: grid; gap: 12px; }
  .message-card { border-radius: 18px; border: 1px solid rgba(124, 90, 220, 0.24); padding: 16px; background: rgba(255, 255, 255, 0.88); }
  body.profile-page.dark-mode .message-card { background: rgba(28, 24, 50, 0.92); border-color: rgba(112, 92, 190, 0.34); }

  .profile-footer { text-align: center; font-size: 0.88rem; color: rgba(36, 29, 63, 0.62); }
  body.profile-page.dark-mode .profile-footer { color: rgba(236, 224, 255, 0.58); }

  .error-card { border: 1px solid rgba(255, 100, 140, 0.5); color: #ffd6e3; background: rgba(255, 100, 140, 0.12); }
  .info-card { font-size: 0.95rem; line-height: 1.6; }

  select.input-control { appearance: none; }
</style>
</head>
<body class="profile-page">
<div class="profile-shell">
  <header class="glass-card profile-header">
    <a class="brand-link" href="<?= base_url('../public/index.php') ?>">
      <span>🌸</span>
      <span class="brand-name"><?= e(APP_NAME) ?></span>
    </a>
    <div class="header-nav">
      <button class="theme-toggle" id="profile-theme-toggle" type="button" aria-pressed="false">
        <span id="profile-theme-icon">🌸</span>
        <span id="profile-theme-text">Pembe</span>
      </button>
      <span class="welcome-pill">Merhaba, <?= e($user['username'] ?? $user['display_name'] ?? $user['email']) ?></span>
      <a class="nav-pill" href="<?= base_url('../public/dashboard.php') ?>">Panel</a>
      <a class="nav-pill" href="<?= base_url('../public/books.php') ?>">Kitaplarım</a>
      <a class="nav-pill" href="<?= base_url('../public/notes.php') ?>">Notlarım</a>
      <a class="nav-pill" href="<?= base_url('../public/eglence.php') ?>">Eğlence</a>
      <a class="nav-pill" href="<?= base_url('../public/designer_cover.php') ?>">Kapak</a>
      <a class="nav-pill" href="<?= base_url('../public/designer_map.php') ?>">Harita</a>
      <a class="nav-pill" href="<?= base_url('../profil/profil_advanced.php') ?>">Profilim</a>
      <a class="nav-pill" href="<?= base_url('../public/logout.php') ?>">Çıkış</a>
    </div>
  </header>

  <?php if ($errbox): ?>
    <div class="glass-card error-card"><strong>Uyarı:</strong> <?= h(implode(' | ', $errbox)) ?></div>
  <?php endif; ?>

  <nav class="glass-card profile-tabs">
    <a class="<?= $active_feed ?>" href="<?= $qtab('feed') ?>">📰 Akış</a>
    <a class="<?= $active_books ?>" href="<?= $qtab('books') ?>">📚 Kitaplar</a>
    <a class="<?= $active_msg ?>" href="<?= $qtab('messages') ?>">✉️ Mesajlar</a>
  </nav>

  <main class="profile-grid">
    <section class="profile-column left">
      <div class="glass-card profile-card">
        <div class="profile-info">
          <div class="avatar"><img src="<?= h($ava) ?>" alt=""></div>
          <div>
            <div class="name"><?= e($display) ?></div>
            <div class="meta" style="font-size:0.9rem;color:rgba(36,29,63,0.6);">&nbsp;<?= e($user['email']) ?></div>
          </div>
        </div>
        <div class="callout">Akıştan paylaş, kitaplarına görseller ekle ve toplulukla bağlantıda kal.</div>
        <div class="stat-grid">
          <div class="stat-pill"><strong><?= $posts_count ?></strong><span>Gönderi</span></div>
          <div class="stat-pill"><strong><?= $books_count ?></strong><span>Kitap</span></div>
          <div class="stat-pill"><strong><?= $inbox_count ?></strong><span>Gelen</span></div>
        </div>
      </div>
    </section>

    <section class="profile-column main">
      <?php if ($tab === 'feed'): ?>
        <div class="glass-card">
          <form class="composer-form" method="post" action="?a=post_create&amp;tab=feed" enctype="multipart/form-data">
            <textarea class="input-control" name="content" rows="3" maxlength="280" placeholder="Ne düşünüyorsun? (max 280)"></textarea>
            <input class="input-control" type="file" name="post_image" accept="image/*">
            <?php csrf_field(); ?>
            <button class="pill-btn" type="submit">Paylaş</button>
          </form>
        </div>

        <div class="feed-list">
          <?php if (!$posts): ?>
            <div class="glass-card info-card">Henüz gönderi yok.</div>
          <?php else: foreach ($posts as $p):
            $pp = $p['avatar_path'] ?: ($p['avatar_url'] ?? '');
            if (!$pp) {
              $nm = $p['display_name'] ?: $p['email'];
              $ch = mb_strtoupper(mb_substr($nm ?: 'U', 0, 1));
              $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='40' height='40'><rect width='100%' height='100%' rx='8' fill='#161226'/><text x='50%' y='60%' text-anchor='middle' font-family='system-ui,-apple-system,Segoe UI,Roboto' font-size='20' fill='#ffd2f0' font-weight='800'>{$ch}</text></svg>";
              $pp = 'data:image/svg+xml;utf8,' . rawurlencode($svg);
            } else {
              if (!preg_match('~^https?://~', $pp)) $pp = base_url(ltrim($pp, '/'));
            }
          ?>
            <article class="glass-card feed-item">
              <div class="avatar"><img src="<?= h($pp) ?>" alt=""></div>
              <div>
                <div style="font-weight:700;">
                  <?= h($p['display_name'] ?: $p['email']) ?>
                  <span class="meta">• <?= h($p['created_at']) ?></span>
                </div>
                <div class="content"><?= nl2br(h($p['content'] ?? '')) ?></div>
                <?php if (!empty($p['image_path'])): ?>
                  <div class="photo"><img src="<?= h(preg_match('~^https?://~', (string)$p['image_path']) ? (string)$p['image_path'] : base_url(ltrim((string)$p['image_path'], '/'))) ?>" alt=""></div>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; endif; ?>
        </div>

      <?php elseif ($tab === 'books'): ?>
        <div class="glass-card">
          <form class="share-form" method="post" action="?a=book_share&amp;tab=books" enctype="multipart/form-data">
            <div style="display:grid;gap:10px;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));">
              <input class="input-control" name="title" placeholder="Kitap adı *" required>
              <input class="input-control" name="author" placeholder="Yazar">
            </div>
            <div style="display:grid;gap:10px;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));">
              <input class="input-control" type="file" name="cover_file" accept="image/*">
              <input class="input-control" name="cover_url" placeholder="Kapak URL (opsiyonel)">
            </div>
            <textarea class="input-control" name="note" rows="3" placeholder="Kısa yorum"></textarea>
            <?php csrf_field(); ?>
            <button class="pill-btn" type="submit">Paylaş</button>
          </form>
        </div>

        <div class="book-grid">
          <?php if (!$books): ?>
            <div class="glass-card info-card">Henüz paylaşılan kitap yok.</div>
          <?php else: foreach ($books as $b): ?>
            <div class="glass-card book-card">
              <?php if (!empty($b['cover_path'])): ?>
                <img class="cover" src="<?= h(preg_match('~^https?://~', $b['cover_path']) ? $b['cover_path'] : base_url(ltrim($b['cover_path'], '/'))) ?>" alt="">
              <?php else: ?>
                <div class="cover" style="display:flex;align-items:center;justify-content:center;font-size:38px;color:var(--pink-200);">📘</div>
              <?php endif; ?>
              <div style="font-weight:700;">&nbsp;<?= h($b['title']) ?></div>
              <?php if (!empty($b['author_name'])): ?><div class="meta">Yazar: <?= h($b['author_name']) ?></div><?php endif; ?>
              <div class="meta" style="margin-top:-4px;"><?= h($b['display_name'] ?? $b['email'] ?? '') ?> • <?= h($b['created_at'] ?? '') ?></div>
              <?php if (!empty($b['note'])): ?><div class="content"><?= nl2br(h($b['note'])) ?></div><?php endif; ?>
            </div>
          <?php endforeach; endif; ?>
        </div>

      <?php elseif ($tab === 'messages'): ?>
        <div class="glass-card">
          <form class="message-form" method="post" action="?a=message_send&amp;tab=messages">
            <div style="display:grid;gap:10px;grid-template-columns:1fr 2fr;">
              <select class="input-control" name="to_id" required>
                <option value="">Alıcı seçiniz…</option>
                <?php foreach ($userlist as $urow): $nm = $urow['display_name'] ?: $urow['email']; ?>
                  <option value="<?= (int)$urow['id'] ?>"><?= h($nm) ?> (<?= h($urow['email']) ?>)</option>
                <?php endforeach; ?>
              </select>
              <input class="input-control" name="content" maxlength="500" placeholder="Mesajınız">
            </div>
            <?php csrf_field(); ?>
            <button class="pill-btn" type="submit">Gönder</button>
          </form>
        </div>

        <div class="message-list">
          <?php if (!$msgs): ?>
            <div class="glass-card info-card">Gelen mesaj yok.</div>
          <?php else: foreach ($msgs as $m): ?>
            <div class="glass-card message-card">
              <div style="font-weight:700;">
                <?= h($m['display_name'] ?: $m['email']) ?>
                <span class="meta">• <?= h($m['created_at'] ?? '') ?></span>
              </div>
              <div class="content" style="margin-top:6px;">&nbsp;<?= nl2br(h($m['content'] ?? '')) ?></div>
            </div>
          <?php endforeach; endif; ?>
        </div>
      <?php endif; ?>
    </section>

    <aside class="profile-column right">
      <div class="glass-card info-card">
        <strong>İpucu</strong>
        <p class="callout" style="margin-top:6px;">Akıştan paylaş, kitaplarını ekle, arkadaşlarına mesaj at. Craftrolle stüdyosunun tüm araçlarına buradan ulaş.</p>
      </div>
    </aside>
  </main>

  <div class="profile-footer">© <?= date('Y') ?> <?= e(APP_NAME) ?></div>
</div>

<script>
(function(){
  const btn  = document.getElementById('profile-theme-toggle');
  if(!btn) return;
  const icon = document.getElementById('profile-theme-icon');
  const text = document.getElementById('profile-theme-text');
  const key  = 'craft-profile-theme';

  function apply(mode){
    const isDark = mode === 'dark';
    document.body.classList.toggle('dark-mode', isDark);
    if(icon) icon.textContent = isDark ? '🌙' : '🌸';
    if(text) text.textContent = isDark ? 'Gece' : 'Pembe';
    btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
    try { localStorage.setItem(key, mode); } catch (_) {}
  }

  let saved = null;
  try { saved = localStorage.getItem(key); } catch (_) {}
  apply(saved === 'dark' ? 'dark' : 'light');

  btn.addEventListener('click', () => {
    apply(document.body.classList.contains('dark-mode') ? 'light' : 'dark');
  });
})();
</script>
</body>
</html>
