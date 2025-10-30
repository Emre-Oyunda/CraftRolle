<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/csrf.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $st = db()->prepare("SELECT * FROM users WHERE username = ?");
    $st->execute([$username]);
    $user = $st->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        redirect(base_url('dashboard.php'));
    } else {
        $error = 'Kullanıcı adı veya şifre hatalı.';
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Giriş - <?= e(APP_NAME) ?></title>
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
        <a href="<?= base_url('login.php') ?>">Giriş</a> 
        · <a href="<?= base_url('register.php') ?>">Kayıt Ol</a>
      </div>
    </div>

    <div class="card" style="max-width: 500px; margin: 50px auto;">
      <h2>🔐 Giriş Yap</h2>
      
      <?php if ($error): ?>
        <div style="padding: 10px; background: #ff000020; border-radius: 8px; margin: 15px 0; color: #d63031;">
          <?= e($error) ?>
        </div>
      <?php endif; ?>
      
      <form method="post" style="margin-top: 20px;">
        <?= csrf_field() ?>
        
        <div style="margin-bottom: 15px;">
          <label style="display: block; margin-bottom: 5px; font-weight: 500;">Kullanıcı Adı</label>
          <input type="text" name="username" required 
                 style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
        </div>
        
        <div style="margin-bottom: 20px;">
          <label style="display: block; margin-bottom: 5px; font-weight: 500;">Şifre</label>
          <input type="password" name="password" required 
                 style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
        </div>
        
        <button type="submit" class="btn" style="width: 100%;">Giriş Yap</button>
      </form>
      
      <div style="text-align: center; margin-top: 20px;">
        <a href="<?= base_url('register.php') ?>">Hesabın yok mu? Kayıt ol</a>
      </div>
    </div>

    <div class="small" style="text-align:center; margin-top:12px;">
      © <?= date('Y') ?> <?= e(APP_NAME) ?>
    </div>
  </div>
</body>
</html>
