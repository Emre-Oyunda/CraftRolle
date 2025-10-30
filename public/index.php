<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
$user = current_user();
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(APP_NAME) ?></title>
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
        <?php if($user): ?>
          <span class="badge">Merhaba, <?= e($user['username']) ?></span>
          · <a href="<?= base_url('dashboard.php') ?>">Panel</a>
          · <a href="<?= base_url('books.php') ?>">Kitaplarım</a>
          · <a href="<?= base_url('notes.php') ?>">Notlarım</a>
          · <a href="<?= base_url('logout.php') ?>">Çıkış</a>
        <?php else: ?>
          <a href="<?= base_url('login.php') ?>">Giriş</a> 
          · <a href="<?= base_url('register.php') ?>">Kayıt Ol</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <h2>🌸 <?= e(APP_NAME) ?>'e Hoş Geldiniz</h2>
      <p>Hikayelerinizi yazın, kitaplarınızı oluşturun ve gerçekçi 3D görüntüleyici ile okuyun!</p>
      
      <div style="margin-top: 20px;">
        <h3>Özellikler:</h3>
        <ul style="line-height: 2; margin-left: 20px;">
          <li>📚 Kitap oluşturma ve yönetimi</li>
          <li>📖 Gerçekçi 3D sayfa çevirme efekti</li>
          <li>📝 Not alma sistemi</li>
          <li>🎨 Kitap kapağı tasarım aracı</li>
          <li>🗺️ Harita tasarım aracı</li>
          <li>📄 PDF/Yazdırma desteği</li>
        </ul>
      </div>

      <?php if (!$user): ?>
        <div style="margin-top: 30px; text-align: center;">
          <a href="<?= base_url('register.php') ?>" class="btn" style="margin: 0 10px;">
            Kayıt Ol
          </a>
          <a href="<?= base_url('login.php') ?>" class="btn" style="margin: 0 10px;">
            Giriş Yap
          </a>
        </div>
      <?php endif; ?>
    </div>

    <div class="small" style="text-align:center; margin-top:12px;">
      © <?= date('Y') ?> <?= e(APP_NAME) ?>
    </div>
  </div>
</body>
</html>
