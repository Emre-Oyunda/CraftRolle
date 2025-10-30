<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/csrf.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (strlen($username) < 3) {
        $error = 'Kullanıcı adı en az 3 karakter olmalıdır.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Geçerli bir e-posta adresi giriniz.';
    } elseif (strlen($password) < 6) {
        $error = 'Şifre en az 6 karakter olmalıdır.';
    } elseif ($password !== $password_confirm) {
        $error = 'Şifreler eşleşmiyor.';
    } else {
        // Check if username exists
        $st = db()->prepare("SELECT id FROM users WHERE username = ?");
        $st->execute([$username]);
        if ($st->fetch()) {
            $error = 'Bu kullanıcı adı zaten kullanılıyor.';
        } else {
            // Check if email exists
            $st = db()->prepare("SELECT id FROM users WHERE email = ?");
            $st->execute([$email]);
            if ($st->fetch()) {
                $error = 'Bu e-posta adresi zaten kullanılıyor.';
            } else {
                // Create user
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $st = db()->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $st->execute([$username, $email, $hashed]);
                
                $success = 'Kayıt başarılı! Şimdi giriş yapabilirsiniz.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kayıt Ol - <?= e(APP_NAME) ?></title>
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
      <h2>📝 Kayıt Ol</h2>
      
      <?php if ($error): ?>
        <div style="padding: 10px; background: #ff000020; border-radius: 8px; margin: 15px 0; color: #d63031;">
          <?= e($error) ?>
        </div>
      <?php endif; ?>
      
      <?php if ($success): ?>
        <div style="padding: 10px; background: #00b89420; border-radius: 8px; margin: 15px 0; color: #00b894;">
          <?= e($success) ?>
          <div style="margin-top: 10px;">
            <a href="<?= base_url('login.php') ?>" class="btn">Giriş Yap</a>
          </div>
        </div>
      <?php else: ?>
        <form method="post" style="margin-top: 20px;">
          <?= csrf_field() ?>
          
          <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Kullanıcı Adı</label>
            <input type="text" name="username" required 
                   style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
          </div>
          
          <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">E-posta</label>
            <input type="email" name="email" required 
                   style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
          </div>
          
          <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Şifre</label>
            <input type="password" name="password" required 
                   style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
          </div>
          
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Şifre Tekrar</label>
            <input type="password" name="password_confirm" required 
                   style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
          </div>
          
          <button type="submit" class="btn" style="width: 100%;">Kayıt Ol</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
          <a href="<?= base_url('login.php') ?>">Zaten hesabın var mı? Giriş yap</a>
        </div>
      <?php endif; ?>
    </div>

    <div class="small" style="text-align:center; margin-top:12px;">
      © <?= date('Y') ?> <?= e(APP_NAME) ?>
    </div>
  </div>
</body>
</html>
