<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_login();
$user = current_user();

// Get user's books
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
      <h2>📚 Kitaplarım</h2>
      <a href="<?= base_url('book_create.php') ?>" class="btn">+ Yeni Kitap Oluştur</a>
      
      <div style="margin-top: 30px;">
        <?php if (empty($books)): ?>
          <p>Henüz kitabınız yok. Hemen bir tane oluşturun!</p>
        <?php else: ?>
          <?php foreach ($books as $book): ?>
            <div class="card" style="margin: 15px 0;">
              <h3><?= e($book['title']) ?></h3>
              <div class="small">
                Oluşturulma: <?= date('d.m.Y H:i', strtotime($book['created_at'])) ?>
                · Görünürlük: <?= e($book['visibility']) ?>
              </div>
              <?php if ($book['cover_path']): ?>
                <img src="<?= base_url('../' . ltrim($book['cover_path'], '/')) ?>" 
                     style="max-width:150px; border-radius:8px; margin:10px 0;">
              <?php endif; ?>
              <div style="margin-top: 10px;">
                <a href="<?= base_url('view_book.php?id=' . $book['id']) ?>" class="btn">
                  📖 3D Görüntüle
                </a>
                <a href="<?= base_url('book_edit.php?id=' . $book['id']) ?>" class="btn">
                  ✏️ Düzenle
                </a>
              </div>
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
