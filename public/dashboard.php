<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_login();
$user = current_user();

// Get statistics
$booksCount = db()->query("SELECT COUNT(*) FROM books WHERE user_id = " . $user['id'] . " AND is_deleted = 0")->fetchColumn();
$notesCount = db()->query("SELECT COUNT(*) FROM notes WHERE user_id = " . $user['id'] . " AND is_deleted = 0")->fetchColumn();
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel - <?= e(APP_NAME) ?></title>
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
      <h2>🏠 Hoş Geldin, <?= e($user['username']) ?>!</h2>
      
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0;">
        <div class="card" style="text-align: center; background: linear-gradient(135deg, #ff6b9d20, #c4456920);">
          <div style="font-size: 48px;">📚</div>
          <h3 style="margin: 10px 0;"><?= $booksCount ?></h3>
          <p>Kitap</p>
        </div>
        
        <div class="card" style="text-align: center; background: linear-gradient(135deg, #ff6b9d20, #c4456920);">
          <div style="font-size: 48px;">📝</div>
          <h3 style="margin: 10px 0;"><?= $notesCount ?></h3>
          <p>Not</p>
        </div>
      </div>

      <h3>Hızlı Erişim</h3>
      <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 15px;">
        <a href="<?= base_url('book_create.php') ?>" class="btn">+ Yeni Kitap</a>
        <a href="<?= base_url('note_create.php') ?>" class="btn">+ Yeni Not</a>
        <a href="<?= base_url('designer_cover.php') ?>" class="btn">🎨 Kapak Tasarla</a>
        <a href="<?= base_url('designer_map.php') ?>" class="btn">🗺️ Harita Oluştur</a>
      </div>
    </div>

    <div class="small" style="text-align:center; margin-top:12px;">
      © <?= date('Y') ?> <?= e(APP_NAME) ?>
    </div>
  </div>
</body>
</html>
