<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
if (file_exists(__DIR__ . '/../src/csrf.php')) require_once __DIR__ . '/../src/csrf.php';

if (!function_exists('csrf_boot')) {
    function csrf_boot(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
    }
}

if (!function_exists('csrf_check')) {
    function csrf_check(): void
    {
        if (function_exists('verify_csrf')) {
            verify_csrf();
        }
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        $token = function_exists('csrf_token') ? csrf_token() : bin2hex(random_bytes(32));
        return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
    }
}

if (!function_exists('base_url_local')) {
    function base_url_local(string $path = ''): string
    {
        return function_exists('base_url') ? base_url($path) : '/' . ltrim($path, '/');
    }
}

if (!function_exists('track_user_session_login')) {
    function track_user_session_login(int $user_id): void
    {
        if ($user_id <= 0) {
            return;
        }

        try {
            $pdo = db();
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS user_sessions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    ip VARCHAR(45) NOT NULL,
                    user_agent VARCHAR(400) NOT NULL,
                    first_seen DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    last_seen DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    login_count INT NOT NULL DEFAULT 1,
                    lat DECIMAL(11,8) NULL,
                    lng DECIMAL(11,8) NULL,
                    geo_source VARCHAR(50) NULL,
                    geo_at DATETIME NULL,
                    UNIQUE KEY uniq_user_session (user_id, ip, user_agent),
                    KEY idx_user (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
            );

            $ipHeader = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
            if (strpos($ipHeader, ',') !== false) {
                $ipHeader = explode(',', $ipHeader)[0];
            }
            $ip = trim($ipHeader) ?: '0.0.0.0';
            $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? 'web'), 0, 400);

            $stmt = $pdo->prepare(
                "INSERT INTO user_sessions (user_id, ip, user_agent, first_seen, last_seen, login_count)
                 VALUES (?, ?, ?, NOW(), NOW(), 1)
                 ON DUPLICATE KEY UPDATE last_seen = NOW(), login_count = login_count + 1"
            );
            $stmt->execute([$user_id, $ip, $ua]);
        } catch (Throwable $th) {
            // sessiz devam
        }
    }
}

function sanitize_next(string $value): string
{
    $value = trim($value);

    if ($value === '' || str_contains($value, '://') || str_starts_with($value, '//')) {
        return '';
    }

    return $value;
}

csrf_boot();

$currentUser = current_user();
$safeNext = sanitize_next((string)($_GET['next'] ?? ''));

if ($currentUser) {
    $target = $safeNext !== '' ? $safeNext : 'dashboard.php';
    header('Location: ' . base_url_local($target));
    exit;
}

$error = '';
$identifierValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (function_exists('csrf_check')) {
        csrf_check();
    }

    $identifierValue = trim((string)($_POST['identifier'] ?? $_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $postNext = sanitize_next((string)($_POST['next'] ?? ''));

    if ($postNext !== '') {
        $safeNext = $postNext;
    }

    if ($identifierValue !== '' && $password !== '') {
        try {
            $stmt = db()->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
            $stmt->execute([$identifierValue, $identifierValue]);
            $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $th) {
            $userRow = false;
            $error = 'Beklenmeyen bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
        }

        if (!$error && $userRow && password_verify($password, $userRow['password'] ?? '')) {
            $_SESSION['user_id'] = (int)$userRow['id'];
            track_user_session_login((int)$userRow['id']);

            $target = $safeNext !== '' ? $safeNext : 'dashboard.php';
            header('Location: ' . base_url_local($target));
            exit;
        }

        if (!$error) {
            $error = 'Giriş bilgileri hatalı.';
        }
    } else {
        $error = 'Lütfen e-posta veya kullanıcı adı ile şifre girin.';
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(APP_NAME) ?> • Giriş</title>
<link rel="stylesheet" href="../assets/css/style.css">
<base href="/public/">
<meta name="theme-color" content="#f5f1ff">
<style>
:root {
  --bg-light: #f9f5ff;
  --bg-accent: #ffe8f7;
  --card-bg: rgba(255, 255, 255, 0.86);
  --border-soft: rgba(140, 122, 255, 0.26);
  --text-base: #251b3c;
  --text-muted: rgba(37, 27, 60, 0.7);
  --pill-bg: rgba(255, 255, 255, 0.82);
  --button-gradient: linear-gradient(135deg, #7c5bff, #f58acb);
  --shadow-soft: 0 24px 60px rgba(120, 90, 200, 0.18);
}

* {
  box-sizing: border-box;
}

body.auth-page {
  margin: 0;
  min-height: 100vh;
  font-family: 'Inter', 'Segoe UI', sans-serif;
  color: var(--text-base);
  background: linear-gradient(140deg, var(--bg-light), var(--bg-accent));
  padding: 60px 16px 80px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.page-shell {
  width: 100%;
  max-width: 1120px;
  display: grid;
  gap: 24px;
}

.glass-panel {
  background: var(--card-bg);
  border-radius: 28px;
  border: 1px solid rgba(255, 255, 255, 0.7);
  padding: 26px 32px;
  box-shadow: var(--shadow-soft);
  backdrop-filter: blur(18px);
}

.top-bar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.brand-link {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  font-size: 1.6rem;
  font-weight: 700;
  color: inherit;
  text-decoration: none;
}

.brand-link .brand-name {
  background: linear-gradient(120deg, #ff9fdc, #8f72ff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.top-controls {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-items: center;
}

.theme-toggle {
  border: 1px solid rgba(255, 255, 255, 0.7);
  background: var(--pill-bg);
  border-radius: 999px;
  padding: 10px 18px;
  font-weight: 600;
  cursor: pointer;
}

.top-nav {
  display: flex;
  gap: 10px;
}

.nav-pill {
  border-radius: 999px;
  padding: 10px 16px;
  border: 1px solid rgba(255, 255, 255, 0.7);
  background: var(--pill-bg);
  font-weight: 600;
  text-decoration: none;
  color: inherit;
}

.nav-pill.is-active {
  background: var(--button-gradient);
  color: #fff;
  box-shadow: 0 14px 34px rgba(124, 90, 220, 0.25);
}

.auth-grid {
  display: grid;
  gap: 22px;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
}

.hero-card {
  display: grid;
  gap: 14px;
}

.hero-card h1 {
  margin: 0;
  font-size: clamp(2rem, 3vw, 2.6rem);
}

.hero-card p {
  margin: 0;
  line-height: 1.6;
  color: var(--text-muted);
}

.hero-list {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 8px;
  color: var(--text-muted);
}

.hero-link {
  color: #6c4ddc;
  font-weight: 600;
  text-decoration: none;
}

.auth-card {
  display: grid;
  gap: 18px;
}

.hint {
  margin: 0;
  color: var(--text-muted);
}

.alert {
  padding: 12px 14px;
  border-radius: 16px;
  border: 1px solid rgba(255, 120, 140, 0.4);
  background: rgba(255, 120, 140, 0.12);
  color: #a11f4d;
  font-weight: 600;
}

.auth-form {
  display: grid;
  gap: 16px;
}

.field {
  display: grid;
  gap: 6px;
}

.field-label {
  font-size: 0.9rem;
  font-weight: 600;
}

.field-input {
  padding: 14px 16px;
  border-radius: 16px;
  border: 1px solid var(--border-soft);
  background: rgba(255, 255, 255, 0.92);
  font-size: 1rem;
  color: inherit;
}

.field-input:focus {
  border-color: rgba(124, 90, 220, 0.6);
  box-shadow: 0 0 0 3px rgba(124, 90, 220, 0.18);
  outline: none;
}

.primary-btn {
  border: none;
  border-radius: 18px;
  padding: 14px 20px;
  font-weight: 700;
  color: #fff;
  background: var(--button-gradient);
  box-shadow: 0 20px 48px rgba(124, 90, 220, 0.28);
  cursor: pointer;
}

.auth-alt {
  text-align: center;
  color: var(--text-muted);
  font-size: 0.95rem;
}

.auth-alt a {
  color: #6c4ddc;
  font-weight: 600;
  text-decoration: none;
}

.quick-links {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: center;
}

.quick-links a {
  padding: 10px 16px;
  border-radius: 14px;
  border: 1px solid rgba(255, 255, 255, 0.7);
  background: var(--pill-bg);
  text-decoration: none;
  font-weight: 600;
  color: inherit;
}

.page-footer {
  text-align: center;
  color: var(--text-muted);
  font-size: 0.9rem;
}

@media (max-width: 720px) {
  .glass-panel { padding: 22px 24px; }
  .auth-grid { grid-template-columns: 1fr; }
  .top-bar { flex-direction: column; align-items: flex-start; }
  .top-controls { width: 100%; justify-content: space-between; }
}
</style>
</head>
<body class="auth-page">
<div class="page-shell">
  <header class="top-bar glass-panel">
    <a class="brand-link" href="<?= base_url_local('index.php') ?>">
      <span class="brand-icon">🌸</span>
      <span class="brand-name"><?= e(APP_NAME) ?></span>
    </a>
    <div class="top-controls">
      <button class="theme-toggle" id="theme-toggle" type="button" aria-pressed="false">
        <span class="theme-icon" id="theme-icon">🌸</span>
        <span class="theme-text" id="theme-text">Pembe</span>
      </button>
      <nav class="top-nav">
        <a class="nav-pill is-active" href="<?= base_url_local('login.php') ?>">Giriş</a>
        <a class="nav-pill" href="<?= base_url_local('register.php') ?>">Kayıt Ol</a>
      </nav>
    </div>
  </header>

  <main class="auth-grid">
    <section class="hero-card glass-panel">
      <h1>Tekrar hoş geldin! 💫</h1>
      <p>CraftRolle ile hikâyelerini 3D kitaplara dönüştür, kapaklarını tasarla ve topluluktan ilham al. Dilediğin cihazdan hız kesmeden devam edebilirsin.</p>
      <ul class="hero-list">
        <li>📚 Bölüm bazlı yazım stüdyosu</li>
        <li>🎨 Kapak & harita tasarım araçları</li>
        <li>📝 Not & karakter arşivi</li>
        <li>🤝 Topluluk paylaşımları ve beğeniler</li>
      </ul>
      <a class="hero-link" href="<?= base_url_local('register.php') ?>">Aramıza ilk kez mi katılıyorsun? Kaydol →</a>
    </section>

    <section class="auth-card glass-panel">
      <h2>Giriş Yap</h2>
      <p class="hint">CraftRolle hesabınla devam et ve projelerini kaldığın yerden sürdür.</p>
      <?php if ($error): ?>
        <div class="alert"><?= e($error) ?></div>
      <?php endif; ?>
      <form class="auth-form" method="post" action="" novalidate>
        <?php if (function_exists('csrf_field')): ?>
          <?= csrf_field() ?>
        <?php endif; ?>
        <?php if ($safeNext !== ''): ?>
          <input type="hidden" name="next" value="<?= e($safeNext) ?>">
        <?php endif; ?>

        <label class="field">
          <span class="field-label">E-posta veya kullanıcı adı</span>
          <input class="field-input" type="text" name="identifier" value="<?= e($identifierValue) ?>" autocomplete="username" required>
        </label>

        <label class="field">
          <span class="field-label">Şifre</span>
          <input class="field-input" type="password" name="password" autocomplete="current-password" required>
        </label>

        <button class="primary-btn" type="submit">Giriş Yap</button>
      </form>
      <div class="auth-alt">
        <span>Yeni misin?</span>
        <a href="<?= base_url_local('register.php') ?>">Hemen hesap oluştur</a>
      </div>
    </section>
  </main>

  <section class="quick-links glass-panel">
    <a href="<?= base_url_local('books.php') ?>">📚 Kitap Stüdyosu</a>
    <a href="<?= base_url_local('notes.php') ?>">📝 Notlar</a>
    <a href="<?= base_url_local('designer_cover.php') ?>">🎨 Kapak Tasarımcısı</a>
    <a href="<?= base_url_local('designer_map.php') ?>">🗺️ Harita Editörü</a>
  </section>

  <footer class="page-footer">© <?= date('Y') ?> <?= e(APP_NAME) ?> • 🌸</footer>
</div>

<script>
(function () {
  const toggleBtn = document.getElementById('theme-toggle');
  const icon = document.getElementById('theme-icon');
  const text = document.getElementById('theme-text');
  const storageKey = 'craft-auth-theme';

  function apply(mode) {
    const isDark = mode === 'dark';
    document.body.classList.toggle('dark-theme', isDark);
    if (icon) icon.textContent = isDark ? '🌙' : '🌸';
    if (text) text.textContent = isDark ? 'Gece' : 'Pembe';
    toggleBtn?.setAttribute('aria-pressed', isDark ? 'true' : 'false');
    try {
      localStorage.setItem(storageKey, mode);
    } catch (err) {
      /* sessiz */
    }
  }

  let saved = null;
  try {
    saved = localStorage.getItem(storageKey);
  } catch (err) {
    saved = null;
  }

  if (!saved && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    saved = 'dark';
  }

  apply(saved === 'dark' ? 'dark' : 'light');

  toggleBtn?.addEventListener('click', () => {
    const next = document.body.classList.contains('dark-theme') ? 'light' : 'dark';
    apply(next);
  });
})();
</script>
</body>
</html>
