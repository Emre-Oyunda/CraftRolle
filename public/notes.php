<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_login();
$user = current_user();

$st = db()->prepare("SELECT n.*, b.title as book_title FROM notes n LEFT JOIN books b ON n.book_id = b.id WHERE n.user_id = ? AND n.is_deleted = 0 ORDER BY n.created_at DESC");
$st->execute([$user['id']]);
$notes = $st->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Notlarım - <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <div class="container">
    <div class="card header">
      <div>
        <a class="btn" href="<?= base_url('index.php') ?>" style="text-decoration:none;">
          🌸 <span class="brand"><?= e(APP_NAME) ?></span>
        </a>
      </div>
      <div>
        <span class="badge">Merhaba, <?= e($user['username']) ?></span>
        · <a href="<?= base_url('dashboard.php') ?>">Panel</a>
        · <a href="<?= base_url('books.php') ?>">Kitaplarım</a>
        · <a href="<?= base_url('notes.php') ?>">Notlarım</a>
        · <a href="<?= base_url('logout.php') ?>">Çıkış</a>
      </div>
    </div>

    <div class="card">
      <h2>📝 Notlarım</h2>
      <a href="<?= base_url('note_create.php') ?>" class="btn">+ Yeni Not Ekle</a>
      
      <div style="margin-top: 30px;">
        <?php if (empty($notes)): ?>
          <p>Henüz notunuz yok.</p>
        <?php else: ?>
          <?php foreach ($notes as $note): ?>
            <div class="card" style="margin: 15px 0;">
              <h3><?= e($note['title']) ?></h3>
              <?php if ($note['book_title']): ?>
                <div class="badge">📚 <?= e($note['book_title']) ?></div>
              <?php endif; ?>
              <div class="small">
                <?= date('d.m.Y H:i', strtotime($note['created_at'])) ?>
              </div>
              <p style="margin-top: 10px;"><?= nl2br(e(substr($note['content'], 0, 200))) ?><?= strlen($note['content']) > 200 ? '...' : '' ?></p>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="small" style="text-align:center; margin-top:12px;">
      © <?= date('Y') ?> <?= e(APP_NAME) ?>
    </div>
  </div>
</body>
</html>
