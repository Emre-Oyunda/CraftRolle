<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
if (file_exists(__DIR__ . '/../src/csrf.php')) {
    require_once __DIR__ . '/../src/csrf.php';
}

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
  --violet-900: #110b27;
  --violet-700: #2a1d4d;
  --violet-500: #6c4ddc;
  --violet-300: #bca6ff;
  --pink-400: #f58acb;
  --pink-200: #ffd8ef;
  --glass-light: rgba(255, 255, 255, 0.84);
  --glass-dark: rgba(19, 17, 35, 0.88);
  --text-base: #231c3c;
  --text-muted: rgba(35, 28, 60, 0.7);
  --error-bg: rgba(255, 100, 140, 0.14);
  --error-border: rgba(255, 100, 140, 0.45);
  --orb-size: 140px;
}

* {
  box-sizing: border-box;
}

body.auth-page {
  margin: 0;
  min-height: 100vh;
  font-family: 'Inter', 'Segoe UI', Tahoma, sans-serif;
  color: var(--text-base);
  background:
    radial-gradient(circle at 12% -8%, rgba(255, 218, 243, 0.82), transparent 58%),
    radial-gradient(circle at 88% -12%, rgba(190, 206, 255, 0.65), transparent 60%),
    linear-gradient(140deg, #f5f1ff 0%, #efe7ff 45%, #ffe8f6 100%);
  padding: 70px 20px 90px;
  position: relative;
  overflow-x: hidden;
  transition: background 0.4s ease, color 0.4s ease;
}

.orb {
  position: fixed;
  inset: auto;
  width: var(--orb-size);
  height: var(--orb-size);
  border-radius: 50%;
  background: radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.9), rgba(119, 76, 196, 0.32));
  filter: blur(0.6px);
  opacity: 0.45;
  pointer-events: none;
  animation: drift 22s linear infinite;
  z-index: 0;
}

.orb:nth-of-type(1) { top: 8%; left: 6%; animation-duration: 28s; }
.orb:nth-of-type(2) { top: 65%; right: 14%; animation-duration: 33s; }
.orb:nth-of-type(3) { bottom: 12%; left: 18%; animation-duration: 26s; }
.orb:nth-of-type(4) { top: 38%; right: 42%; animation-duration: 31s; }

@keyframes drift {
  0%   { transform: translate3d(-18px, -24px, 0) scale(1); opacity: 0.32; }
  50%  { transform: translate3d(24px, 30px, 0) scale(1.08); opacity: 0.58; }
  100% { transform: translate3d(-18px, -24px, 0) scale(1); opacity: 0.32; }
}

body.auth-page::before,
body.auth-page::after {
  content: '';
  position: fixed;
  width: 440px;
  height: 440px;
  border-radius: 50%;
  pointer-events: none;
  filter: blur(150px);
  opacity: 0.26;
  z-index: 0;
}

body.auth-page::before {
  top: -200px;
  left: -170px;
  background: linear-gradient(135deg, rgba(255, 176, 228, 0.68), rgba(255, 236, 252, 0.52));
}

body.auth-page::after {
  bottom: -210px;
  right: -170px;
  background: linear-gradient(135deg, rgba(140, 122, 255, 0.6), rgba(107, 198, 255, 0.5));
}

body.auth-page.dark-theme {
  color: #f7ecff;
  background:
    radial-gradient(circle at 10% -10%, rgba(83, 60, 140, 0.5), transparent 58%),
    radial-gradient(circle at 88% -8%, rgba(201, 72, 140, 0.42), transparent 60%),
    linear-gradient(140deg, #0f0b1f 0%, #16122b 48%, #1f1737 100%);
}

body.auth-page.dark-theme::before,
body.auth-page.dark-theme::after {
  opacity: 0.18;
}

.page-shell {
  position: relative;
  z-index: 1;
  max-width: 1100px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 28px;
}

.glass-panel {
  background: var(--glass-light);
  border-radius: 28px;
  border: 1px solid rgba(255, 255, 255, 0.62);
  padding: 26px 32px;
  box-shadow: 0 28px 70px rgba(120, 90, 200, 0.16);
  backdrop-filter: blur(24px);
  transition: transform 0.28s ease, box-shadow 0.28s ease, background 0.28s ease;
}

@supports not (backdrop-filter: blur(16px)) {
  .glass-panel {
    background: rgba(255, 255, 255, 0.9);
  }
}

.glass-panel:hover {
  transform: translateY(-4px);
  box-shadow: 0 36px 90px rgba(120, 90, 200, 0.22);
}

body.auth-page.dark-theme .glass-panel {
  background: var(--glass-dark);
  border-color: rgba(108, 90, 190, 0.36);
  box-shadow: 0 32px 80px rgba(10, 7, 22, 0.72);
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

.brand-name {
  background: linear-gradient(120deg, #ff9fdc, #8f72ff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.top-controls {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
}

.theme-toggle {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 10px 18px;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.68);
  background: linear-gradient(135deg, #fbd5ff, #d7c6ff);
  color: #3a285c;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 22px 44px rgba(155, 110, 255, 0.24);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.theme-toggle:hover {
  transform: translateY(-2px);
  box-shadow: 0 30px 54px rgba(155, 110, 255, 0.3);
}

body.auth-page.dark-theme .theme-toggle {
  background: rgba(26, 22, 44, 0.92);
  border: 1px solid rgba(108, 90, 190, 0.4);
  color: #f7ebff;
  box-shadow: 0 24px 48px rgba(5, 4, 16, 0.62);
}

.top-nav {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.nav-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 16px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.78);
  border: 1px solid rgba(255, 255, 255, 0.64);
  font-weight: 600;
  color: inherit;
  text-decoration: none;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.nav-pill:hover {
  transform: translateY(-2px);
  box-shadow: 0 18px 38px rgba(124, 88, 220, 0.22);
}

.nav-pill.is-active {
  background: linear-gradient(120deg, #ffb9e9, #b79dff);
  color: #fff;
  box-shadow: 0 20px 42px rgba(124, 88, 220, 0.26);
}

body.auth-page.dark-theme .nav-pill {
  background: rgba(25, 22, 47, 0.88);
  border-color: rgba(108, 90, 190, 0.36);
}

.auth-grid {
  display: grid;
  gap: 24px;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  align-items: stretch;
}

.hero-card {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.hero-card h1 {
  margin: 0;
  font-size: clamp(2rem, 3vw, 2.8rem);
  letter-spacing: -0.03em;
}

.hero-card p {
  margin: 0;
  line-height: 1.65;
  color: var(--text-muted);
}

body.auth-page.dark-theme .hero-card p {
  color: rgba(236, 224, 255, 0.74);
}

.hero-list {
  margin: 8px 0 0;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 10px;
  color: rgba(35, 28, 60, 0.78);
  font-weight: 600;
}

body.auth-page.dark-theme .hero-list {
  color: rgba(236, 224, 255, 0.78);
}

.hero-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.hero-badges span {
  padding: 8px 14px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.82);
  border: 1px solid rgba(255, 255, 255, 0.62);
  font-size: 0.88rem;
  font-weight: 600;
  color: rgba(35, 28, 60, 0.74);
}

body.auth-page.dark-theme .hero-badges span {
  background: rgba(26, 23, 46, 0.9);
  border-color: rgba(108, 90, 190, 0.34);
  color: rgba(236, 224, 255, 0.72);
}

.hero-link {
  margin-top: auto;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--violet-500);
  text-decoration: none;
  font-weight: 600;
}

.hero-link:hover {
  text-decoration: underline;
}

body.auth-page.dark-theme .hero-link {
  color: #d7c6ff;
}

.auth-card {
  display: grid;
  gap: 18px;
}

.auth-card h2 {
  margin: 0;
  font-size: 1.9rem;
}

.hint {
  margin: 0;
  color: var(--text-muted);
}

body.auth-page.dark-theme .hint {
  color: rgba(236, 224, 255, 0.68);
}

.alert {
  padding: 12px 14px;
  border-radius: 16px;
  border: 1px solid var(--error-border);
  background: var(--error-bg);
  color: #ffeff6;
  font-weight: 600;
}

.auth-form {
  display: grid;
  gap: 16px;
}

.field {
  display: grid;
  gap: 8px;
  position: relative;
}

.field[data-icon] {
  padding-left: 0;
}

.field[data-icon] .field-input {
  padding-left: 46px;
}

.field[data-icon]::before {
  content: attr(data-icon);
  position: absolute;
  top: 39px;
  left: 16px;
  width: 24px;
  height: 24px;
  display: grid;
  place-items: center;
  font-size: 1rem;
  opacity: 0.72;
  pointer-events: none;
}

.field-label {
  font-size: 0.92rem;
  font-weight: 600;
  color: rgba(35, 28, 60, 0.85);
}

body.auth-page.dark-theme .field-label {
  color: rgba(236, 224, 255, 0.78);
}

.field-control {
  position: relative;
  display: flex;
  align-items: center;
}

.field-input {
  width: 100%;
  padding: 14px 16px;
  border-radius: 16px;
  border: 1px solid rgba(118, 96, 210, 0.34);
  background: rgba(255, 255, 255, 0.92);
  color: inherit;
  font-size: 1rem;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.field-input:focus {
  border-color: rgba(124, 90, 220, 0.6);
  box-shadow: 0 0 0 3px rgba(124, 90, 220, 0.22);
  outline: none;
}

body.auth-page.dark-theme .field-input {
  background: rgba(26, 23, 46, 0.88);
  border-color: rgba(108, 90, 190, 0.38);
  color: #f5ebff;
}

.field:focus-within .field-label {
  color: var(--violet-500);
}

body.auth-page.dark-theme .field:focus-within .field-label {
  color: #d7c6ff;
}

.reveal-btn {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  border: none;
  background: rgba(124, 90, 220, 0.12);
  color: inherit;
  border-radius: 999px;
  font-size: 0.9rem;
  width: 38px;
  height: 32px;
  display: grid;
  place-items: center;
  cursor: pointer;
  transition: background 0.2s ease;
}

.reveal-btn:hover {
  background: rgba(124, 90, 220, 0.22);
}

.primary-btn {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  padding: 14px 18px;
  border-radius: 18px;
  border: 1px solid rgba(118, 96, 210, 0.26);
  background: linear-gradient(135deg, #7c5bff, #f58acb);
  color: #fff;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 24px 58px rgba(124, 90, 220, 0.28);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.primary-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 30px 70px rgba(124, 90, 220, 0.32);
}

.primary-btn:active {
  transform: translateY(0);
}

.auth-alt {
  display: flex;
  justify-content: center;
  gap: 8px;
  font-size: 0.95rem;
  color: var(--text-muted);
}

.auth-alt a {
  color: var(--violet-500);
  font-weight: 600;
  text-decoration: none;
}

.auth-alt a:hover {
  text-decoration: underline;
}

body.auth-page.dark-theme .auth-alt {
  color: rgba(236, 224, 255, 0.7);
}

body.auth-page.dark-theme .auth-alt a {
  color: #d7c6ff;
}

.quick-links {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  justify-content: center;
}

.quick-links a {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 16px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.62);
  color: inherit;
  text-decoration: none;
  font-weight: 600;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.quick-links a:hover {
  transform: translateY(-2px);
  box-shadow: 0 18px 36px rgba(124, 88, 220, 0.2);
}

body.auth-page.dark-theme .quick-links a {
  background: rgba(26, 23, 46, 0.9);
  border-color: rgba(108, 90, 190, 0.34);
}

.page-footer {
  text-align: center;
  color: var(--text-muted);
  font-size: 0.92rem;
}

body.auth-page.dark-theme .page-footer {
  color: rgba(236, 224, 255, 0.64);
}

@media (max-width: 900px) {
  body.auth-page {
    padding: 60px 16px 90px;
  }
  .top-bar {
    flex-direction: column;
    align-items: flex-start;
  }
  .top-controls {
    width: 100%;
    justify-content: space-between;
  }
}

@media (max-width: 620px) {
  body.auth-page {
    padding: 52px 14px 88px;
  }
  .glass-panel {
    padding: 22px 24px;
  }
  .top-controls {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }
  .theme-toggle {
    justify-content: center;
  }
  .top-nav {
    width: 100%;
    justify-content: center;
  }
  .hero-card {
    text-align: center;
  }
  .hero-list {
    justify-items: center;
  }
  .hero-badges {
    justify-content: center;
  }
  .hero-link {
    justify-content: center;
  }
  .quick-links {
    flex-direction: column;
    align-items: stretch;
  }
  .quick-links a {
    justify-content: center;
  }
  .field[data-icon]::before {
    left: 12px;
  }
}
</style>
</head>
<body class="auth-page">
<div class="orb"></div>
<div class="orb"></div>
<div class="orb"></div>
<div class="orb"></div>
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
      <div class="hero-badges">
        <span>⚡️ Canlı otomatik kayıt</span>
        <span>🔒 Güvenli oturum</span>
        <span>🌙 Pembe / Gece temasına geç</span>
      </div>
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

        <label class="field" data-icon="@">
          <span class="field-label">E-posta veya kullanıcı adı</span>
          <input class="field-input" type="text" name="identifier" value="<?= e($identifierValue) ?>" autocomplete="username" required>
        </label>

        <label class="field" data-icon="🔒">
          <span class="field-label">Şifre</span>
          <div class="field-control">
            <input class="field-input" type="password" name="password" autocomplete="current-password" required>
            <button class="reveal-btn" type="button" aria-label="Şifreyi göster">👁</button>
          </div>
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

  const revealBtn = document.querySelector('.reveal-btn');
  if (revealBtn) {
    revealBtn.addEventListener('click', () => {
      const field = revealBtn.closest('.field-control')?.querySelector('input');
      if (!field) return;
      const isPassword = field.getAttribute('type') === 'password';
      field.setAttribute('type', isPassword ? 'text' : 'password');
      revealBtn.textContent = isPassword ? '🙈' : '👁';
      revealBtn.setAttribute('aria-label', isPassword ? 'Şifreyi gizle' : 'Şifreyi göster');
    });
  }
})();
</script>
</body>
</html>
