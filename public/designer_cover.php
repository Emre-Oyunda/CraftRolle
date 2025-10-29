<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_login();
$user = current_user();
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kapak Tasarımı - <?= e(APP_NAME) ?></title>
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
      <h2>🎨 Kitap Kapağı Tasarımı</h2>
      <p>Kitaplarınız için özel kapaklar tasarlayın...</p>
      
      <div style="margin-top: 30px; text-align: center;">
        <div style="width: 300px; height: 400px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; box-shadow: 0 8px 24px rgba(0,0,0,0.3);">
          Kapak Tasarım Alanı
        </div>
        
        <div style="margin-top: 30px; display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
          <button class="btn">Arka Plan Seç</button>
          <button class="btn">Metin Ekle</button>
          <button class="btn">Görsel Ekle</button>
          <button class="btn">İndir</button>
        </div>
      </div>
    </div>

    <div class="small" style="text-align:center; margin-top:12px;">
      © <?= date('Y') ?> <?= e(APP_NAME) ?>
    </div>
  </div>
</body>
</html>
