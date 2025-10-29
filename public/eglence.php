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
  <title>Eğlence - <?= e(APP_NAME) ?></title>
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
      <h2>🎮 Eğlence</h2>
      <p>Yazarlara özel eğlence araçları burada yer alacak...</p>
      
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
        <div class="card" style="text-align: center;">
          <div style="font-size: 48px; margin-bottom: 10px;">🎲</div>
          <h3>Karakter İsmi Üretici</h3>
          <p class="small">Hikayeleriniz için rastgele karakter isimleri oluşturun</p>
        </div>
        
        <div class="card" style="text-align: center;">
          <div style="font-size: 48px; margin-bottom: 10px;">📖</div>
          <h3>Hikaye Başlatıcı</h3>
          <p class="small">İlham almak için rastgele hikaye başlangıçları</p>
        </div>
        
        <div class="card" style="text-align: center;">
          <div style="font-size: 48px; margin-bottom: 10px;">🎭</div>
          <h3>Karakter Geliştiricisi</h3>
          <p class="small">Karakterleriniz için detaylı profiller oluşturun</p>
        </div>
      </div>
    </div>

    <div class="small" style="text-align:center; margin-top:12px;">
      © <?= date('Y') ?> <?= e(APP_NAME) ?>
    </div>
  </div>
</body>
</html>
