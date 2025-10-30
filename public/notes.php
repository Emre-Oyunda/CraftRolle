<?php
// public/notes.php — Not yaz + sağda önizleme + otomatik taslak
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';

if (!function_exists('csrf_check')) {
    function csrf_check(): void {
        if (function_exists('verify_csrf')) {
            verify_csrf();
        }
    }
}

require_login();

$uid = $_SESSION['user_id'] ?? null;
$created_note = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $title = trim($_POST['title'] ?? 'Not');
    if ($title === '') {
        $title = 'Not';
    }

    $content = $_POST['content'] ?? '';
    if ($content === '') {
        $content = $_POST['rte_fallback'] ?? '';
    }

    $st = db()->prepare('INSERT INTO notes(user_id, title, content, updated_at) VALUES(?,?,?,?)');
    $st->execute([$uid, $title, $content, date('c')]);

    $id = (int) db()->lastInsertId();
    $st = db()->prepare('SELECT * FROM notes WHERE id=? AND user_id=?');
    $st->execute([$id, $uid]);
    $created_note = $st->fetch(PDO::FETCH_ASSOC);
}

$user = current_user();
?>
<!doctype html>
<html lang="tr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(APP_NAME) ?> — Not Yaz</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
  .two-cols{display:grid;gap:16px;grid-template-columns:1.1fr 0.9fr}
  @media (max-width:900px){.two-cols{grid-template-columns:1fr}}
  .preview-card{border:1px solid #2a2144;border-radius:12px;background:rgba(255,255,255,.03);padding:12px}
  .preview-content{background:#fff;color:#111;padding:10px;border-radius:8px;max-height:320px;overflow:auto;font-size:.95rem}
  .small{opacity:.85;font-size:.9rem}
  .topnav{display:flex;justify-content:space-between;align-items:center;gap:12px}
  .topnav .menu{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
  .btn{padding:8px 12px;border:1px solid #2a2144;border-radius:10px;background:#161226;color:#ffd2f0;text-decoration:none;display:inline-block}
  .rte{min-height:220px;background:#fff;color:#111;border-radius:10px;padding:10px;border:1px solid #ccc}
  .toolbar{display:flex;gap:8px;margin:8px 0}
  .toolbar .btn{padding:6px 10px}
  .muted{opacity:.75}
</style>
</head><body>
<div class="container">

  <div class="card header topnav">
    <a class="btn" href="<?= base_url('index.php') ?>" style="text-decoration:none;">🌸 <span class="brand"><?= e(APP_NAME) ?></span></a>
    <div class="menu">
      <a class="btn" href="<?= base_url('notes.php') ?>">📝 Not Yaz</a>
      <a class="btn" href="<?= base_url('note.php') ?>">📒 Notlarım</a>
      <a href="<?= base_url('books.php') ?>">Kitaplar</a>
      <a href="<?= base_url('logout.php') ?>">Çıkış</a>
    </div>
  </div>

  <!-- Taslak durumu etiketi -->
  <div id="draft-status" class="small muted" style="margin:-4px 0 8px 0;">Taslak hazır</div>

  <div class="two-cols">
    <!-- SOL: Not Yaz -->
    <div class="card">
      <h2>Not Yaz</h2>
      <!-- kullanıcıya özel taslak anahtarı -->
      <form method="post" id="note-form" action="<?= e(base_url('notes.php')) ?>" data-draft-key="notes_draft_user_<?= (int)$uid ?>">
        <?= csrf_field() ?>

        <div class="toolbar">
          <button type="button" class="btn" data-cmd="bold">B</button>
          <button type="button" class="btn" data-cmd="italic"><i>İ</i></button>
          <button type="button" class="btn" data-cmd="underline"><u>A</u></button>
          <button type="button" class="btn" data-cmd="h1">Başlık</button>
          <button type="button" class="btn" data-cmd="ul">Liste</button>
        </div>

        <label>Başlık</label>
        <input id="note-title" name="title" placeholder="Not başlığı">

        <label>İçerik</label>
        <div id="rte" class="rte" contenteditable="true" spellcheck="true"></div>

        <!-- JS bu hidden'ı doldurur -->
        <input type="hidden" name="content" id="content-hidden">
        <!-- JS çalışmasa bile sunucu buradan okur -->
        <textarea name="rte_fallback" id="rte-fallback" style="display:none"></textarea>

        <button class="btn" style="margin-top:10px">Kaydet</button>
      </form>

      <noscript class="small" style="display:block;margin-top:8px;color:#e6b0b0">
        JavaScript kapalıysa taslak kaydı ve zengin düzenleme çalışmaz.
      </noscript>
    </div>

    <!-- SAĞ: Sadece küçük önizleme -->
    <div class="card">
      <h2>Önizleme</h2>
      <?php if ($created_note): ?>
        <div class="preview-card">
          <div class="small" style="font-weight:600"><?= e($created_note['title']) ?></div>
          <div class="preview-content"><?= $created_note['content'] ?></div>
          <div class="small" style="margin-top:6px;">
            <?= e(date('d.m.Y H:i', strtotime($created_note['updated_at']))) ?>
          </div>
        </div>
      <?php else: ?>
        <p class="small">“Kaydet”ten sonra son notun burada küçük kart olarak görünür.</p>
      <?php endif; ?>
    </div>
  </div>

</div>
<!-- cache kırıcı ?v=3 ile yükle -->
<script src="../assets/js/notes_editor.js?v=3"></script>
</body></html>
