<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';

require_login();
$user = current_user();

$booksCount = (int) db()->query("SELECT COUNT(*) FROM books WHERE user_id = " . (int) $user['id'] . " AND is_deleted = 0")->fetchColumn();
$notesCount = (int) db()->query("SELECT COUNT(*) FROM notes WHERE user_id = " . (int) $user['id'] . " AND is_deleted = 0")->fetchColumn();

$navLinks = [
  ['label' => '🏠 Panel', 'href' => 'dashboard.php'],
  ['label' => '📚 Kitaplar', 'href' => 'books.php'],
  ['label' => '📝 Notlar', 'href' => 'notes.php'],
  ['label' => '🎨 Kapak', 'href' => 'designer_cover.php'],
  ['label' => '🗺️ Harita', 'href' => 'designer_map.php'],
  ['label' => '🎉 Eğlence', 'href' => 'eglence.php'],
  ['label' => '🚪 Çıkış', 'href' => 'logout.php'],
];

$quickActions = [
  [
    'icon' => '📖',
    'title' => 'Yeni Kitap',
    'description' => 'Yeni hikayene hemen başla, başlık ve bölüm planını oluştur.',
    'href' => 'book_new.php',
    'label' => 'Kitap Oluştur'
  ],
  [
    'icon' => '🗒️',
    'title' => 'Hızlı Not',
    'description' => 'Fikirlerini kaybetmeden yakala, not düzenleyici seni bekliyor.',
    'href' => 'notes.php',
    'label' => 'Not Defteri'
  ],
  [
    'icon' => '🎨',
    'title' => 'Kapak Tasarımı',
    'description' => 'Kitabın için görsel bir kimlik oluştur, farklı şablonları dene.',
    'href' => 'designer_cover.php',
    'label' => 'Kapak Stüdyosu'
  ],
  [
    'icon' => '🗺️',
    'title' => 'Harita Atölyesi',
    'description' => 'Krallık sınırlarını, şehirlerini ve gizli bölgeleri çiz.',
    'href' => 'designer_map.php',
    'label' => 'Harita Çiz'
  ]
];
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(APP_NAME) ?> — Panel</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    body.dashboard-page {
      font-family: 'Inter', 'Segoe UI', Tahoma, sans-serif;
      min-height: 100vh;
      margin: 0;
      padding: 44px 18px 56px;
      background:
        radial-gradient(circle at 12% 8%, rgba(255, 210, 243, 0.7), transparent 55%),
        radial-gradient(circle at 88% 6%, rgba(192, 226, 255, 0.7), transparent 55%),
        linear-gradient(135deg, #f5f6ff 0%, #f2ebff 40%, #ffe7f6 100%);
      color: #1f1f35;
      transition: background 0.45s ease, color 0.45s ease;
    }

    body.dashboard-page::before,
    body.dashboard-page::after {
      content: '';
      position: fixed;
      width: 460px;
      height: 460px;
      border-radius: 50%;
      filter: blur(140px);
      opacity: 0.32;
      z-index: 0;
      pointer-events: none;
    }

    body.dashboard-page::before {
      top: -150px;
      left: -120px;
      background: linear-gradient(135deg, rgba(255, 147, 197, 0.65), rgba(255, 225, 249, 0.55));
    }

    body.dashboard-page::after {
      bottom: -180px;
      right: -140px;
      background: linear-gradient(135deg, rgba(160, 140, 255, 0.6), rgba(108, 182, 255, 0.55));
    }

    .dashboard-shell {
      position: relative;
      z-index: 1;
      max-width: 1080px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    .glass-card {
      background: rgba(255, 255, 255, 0.78);
      border: 1px solid rgba(255, 255, 255, 0.6);
      border-radius: 26px;
      padding: 26px 30px;
      box-shadow: 0 26px 56px rgba(160, 138, 255, 0.18);
      backdrop-filter: blur(22px);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .glass-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 32px 70px rgba(160, 138, 255, 0.24);
    }

    .top-header {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 18px;
    }

    .brand-link {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      font-size: 1.6rem;
      font-weight: 700;
      text-decoration: none;
      color: inherit;
    }

    .brand-link .brand-name {
      background: linear-gradient(135deg, #ff81c7, #9a7bff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .nav-line {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .nav-pill {
      padding: 8px 14px;
      border-radius: 999px;
      border: 1px solid rgba(255, 255, 255, 0.55);
      background: rgba(255, 255, 255, 0.7);
      color: inherit;
      text-decoration: none;
      font-weight: 600;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .nav-pill:hover {
      transform: translateY(-2px);
      box-shadow: 0 16px 34px rgba(160, 138, 255, 0.28);
    }

    .greeting-chip {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 16px;
      border-radius: 14px;
      background: rgba(255, 255, 255, 0.68);
      border: 1px solid rgba(255, 255, 255, 0.55);
      font-weight: 600;
    }

    .overview {
      display: flex;
      flex-wrap: wrap;
      gap: 22px;
      align-items: center;
      justify-content: space-between;
    }

    .overview h1 {
      margin: 0;
      font-size: clamp(1.9rem, 2.6vw, 2.4rem);
    }

    .overview p {
      margin: 8px 0 0;
      color: rgba(33, 33, 68, 0.68);
      line-height: 1.6;
      max-width: 520px;
    }

    .stat-row {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
    }

    .stat-card {
      min-width: 160px;
      padding: 14px 18px;
      border-radius: 18px;
      background: rgba(255, 255, 255, 0.75);
      border: 1px solid rgba(255, 255, 255, 0.55);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
      display: grid;
      gap: 4px;
    }

    .stat-card span.label {
      font-size: 0.85rem;
      color: rgba(33, 33, 68, 0.58);
    }

    .stat-card strong {
      font-size: 1.4rem;
      color: #2f3062;
    }

    .quick-grid {
      display: grid;
      gap: 22px;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    }

    .quick-card {
      position: relative;
      padding: 22px;
      border-radius: 20px;
      background: rgba(255, 255, 255, 0.74);
      border: 1px solid rgba(255, 255, 255, 0.55);
      box-shadow: 0 18px 40px rgba(160, 138, 255, 0.16);
      display: grid;
      gap: 12px;
      overflow: hidden;
    }

    .quick-card::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.35), rgba(255, 255, 255, 0));
      opacity: 0;
      transition: opacity 0.35s ease;
    }

    .quick-card:hover::after {
      opacity: 1;
    }

    .quick-card strong.icon {
      font-size: 2.1rem;
    }

    .quick-card h3 {
      margin: 0;
      font-size: 1.1rem;
      color: #2e3261;
    }

    .quick-card p {
      margin: 0;
      color: rgba(33, 33, 68, 0.66);
      line-height: 1.5;
      font-size: 0.95rem;
    }

    .quick-card a.btn {
      align-self: start;
      border-radius: 12px;
      padding: 10px 16px;
      font-weight: 600;
      text-decoration: none;
      color: #fff;
      background: linear-gradient(135deg, #ff80c4, #a27fff);
      box-shadow: 0 16px 32px rgba(160, 138, 255, 0.3);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .quick-card a.btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 22px 40px rgba(160, 138, 255, 0.35);
    }

    .footer-note {
      text-align: center;
      font-size: 0.86rem;
      color: rgba(33, 33, 68, 0.6);
      margin-top: 8px;
    }

    @media (max-width: 768px) {
      body.dashboard-page {
        padding: 30px 14px 44px;
      }

      .glass-card {
        padding: 22px;
      }

      .nav-line {
        gap: 8px;
      }
    }
  </style>
</head>
<body class="dashboard-page">
  <div class="dashboard-shell">
    <header class="glass-card top-header">
      <a class="brand-link" href="<?= base_url('index.php') ?>">
        <span>🌸</span>
        <span class="brand-name"><?= e(APP_NAME) ?></span>
      </a>
      <div class="nav-line">
        <span class="greeting-chip">👋 Merhaba, <?= e($user['username']) ?></span>
        <?php foreach ($navLinks as $item): ?>
          <a class="nav-pill" href="<?= base_url($item['href']) ?>"><?= $item['label'] ?></a>
        <?php endforeach; ?>
      </div>
    </header>

    <section class="glass-card">
      <div class="overview">
        <div>
          <h1>Paneline hoş geldin!</h1>
          <p>Kitaplarını geliştir, notlarını düzenle ve Craftrolle stüdyolarını keşfet. Aşağıdaki kısayollarla üretim ritmini hızlandırabilirsin.</p>
        </div>
        <div class="stat-row">
          <div class="stat-card">
            <span class="label">Toplam kitap</span>
            <strong><?= $booksCount ?></strong>
            <span class="label">hazır proje</span>
          </div>
          <div class="stat-card">
            <span class="label">Toplam not</span>
            <strong><?= $notesCount ?></strong>
            <span class="label">aktif fikir</span>
          </div>
        </div>
      </div>
    </section>

    <section class="glass-card">
      <h2 style="margin-top:0; font-size:1.3rem; color:#2b2d56;">Hızlı Erişim Kartları</h2>
      <div class="quick-grid">
        <?php foreach ($quickActions as $action): ?>
          <div class="quick-card">
            <strong class="icon"><?= $action['icon'] ?></strong>
            <h3><?= $action['title'] ?></h3>
            <p><?= $action['description'] ?></p>
            <a class="btn" href="<?= base_url($action['href']) ?>"><?= $action['label'] ?></a>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <footer class="footer-note">© <?= date('Y') ?> <?= e(APP_NAME) ?> · Craftrolle yaratıcı panel</footer>
  </div>
</body>
</html>
