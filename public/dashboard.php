<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';

require_login();
$user = current_user();

$booksCount = (int)db()->query("SELECT COUNT(*) FROM books WHERE user_id = " . (int)$user['id'] . " AND is_deleted = 0")->fetchColumn();
$notesCount = (int)db()->query("SELECT COUNT(*) FROM notes WHERE user_id = " . (int)$user['id'] . " AND is_deleted = 0")->fetchColumn();

$navLinks = [
  ['icon' => '🏠', 'label' => 'Panel', 'href' => 'dashboard.php'],
  ['icon' => '📚', 'label' => 'Kitaplarım', 'href' => 'books.php'],
  ['icon' => '📝', 'label' => 'Notlarım', 'href' => 'notes.php'],
  ['icon' => '🎉', 'label' => 'Eğlence', 'href' => 'eglence.php'],
  ['icon' => '🎨', 'label' => 'Kapak', 'href' => 'designer_cover.php'],
  ['icon' => '🗺️', 'label' => 'Harita', 'href' => 'designer_map.php'],
];

if (!empty($user['is_admin']) && (int)$user['is_admin'] === 1) {
  $navLinks[] = ['icon' => '🛠️', 'label' => 'Admin', 'href' => '../admin/panel.php'];
}

function user_initial_dashboard(array $user): string {
  $name = $user['username'] ?? '';
  $char = $name !== '' ? mb_substr($name, 0, 1) : 'K';
  return mb_strtoupper($char);
}
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel · <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    :root {
      --bg-base: #f4efff;
      --bg-accent: #ffe8f6;
      --violet-600: #5f46cc;
      --violet-400: #9274ff;
      --pink-400: #f58acb;
      --pink-200: #ffd9f1;
      --text-base: #231c3c;
      --text-muted: rgba(35, 28, 60, 0.7);
      --glass-light: rgba(255, 255, 255, 0.82);
      --glass-dark: rgba(17, 15, 30, 0.86);
    }

    * { box-sizing: border-box; }

    body.dashboard-page {
      margin: 0;
      min-height: 100vh;
      font-family: 'Inter', 'Segoe UI', Tahoma, sans-serif;
      color: var(--text-base);
      background:
        radial-gradient(circle at 12% -8%, rgba(255, 214, 244, 0.82), transparent 55%),
        radial-gradient(circle at 92% 0%, rgba(189, 206, 255, 0.68), transparent 60%),
        linear-gradient(135deg, var(--bg-base) 0%, #efe7ff 45%, var(--bg-accent) 100%);
      padding: 60px 18px 110px;
      position: relative;
      transition: background 0.35s ease, color 0.35s ease;
    }

    body.dashboard-page::before,
    body.dashboard-page::after {
      content: '';
      position: fixed;
      width: 420px;
      height: 420px;
      border-radius: 50%;
      filter: blur(140px);
      opacity: 0.24;
      pointer-events: none;
      z-index: 0;
    }

    body.dashboard-page::before {
      top: -160px;
      left: -160px;
      background: linear-gradient(135deg, rgba(255, 182, 230, 0.68), rgba(255, 236, 252, 0.56));
    }

    body.dashboard-page::after {
      bottom: -190px;
      right: -150px;
      background: linear-gradient(135deg, rgba(152, 132, 255, 0.62), rgba(110, 198, 255, 0.5));
    }

    body.dashboard-page.dark-theme {
      color: #f5ecff;
      background:
        radial-gradient(circle at 16% -10%, rgba(84, 62, 140, 0.55), transparent 58%),
        radial-gradient(circle at 90% 0%, rgba(205, 82, 150, 0.45), transparent 60%),
        linear-gradient(135deg, #100c1f 0%, #161129 45%, #1e1736 100%);
    }

    body.dashboard-page.dark-theme::before,
    body.dashboard-page.dark-theme::after {
      opacity: 0.16;
    }

    .dashboard-shell {
      position: relative;
      z-index: 1;
      max-width: 1160px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      gap: 28px;
    }

    .glass-panel {
      background: var(--glass-light);
      border-radius: 26px;
      border: 1px solid rgba(255, 255, 255, 0.7);
      padding: 26px 30px;
      box-shadow: 0 26px 70px rgba(125, 94, 210, 0.18);
      backdrop-filter: blur(20px);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .glass-panel:hover {
      transform: translateY(-3px);
      box-shadow: 0 32px 80px rgba(125, 94, 210, 0.24);
    }

    body.dashboard-page.dark-theme .glass-panel {
      background: var(--glass-dark);
      border-color: rgba(112, 92, 190, 0.36);
      box-shadow: 0 30px 72px rgba(9, 7, 20, 0.7);
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
      font-size: 1.68rem;
      font-weight: 700;
      color: inherit;
      text-decoration: none;
    }

    .brand-link .brand-name {
      background: linear-gradient(120deg, #ff9fe0, #8f72ff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .nav-line {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 10px;
    }

    .nav-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 10px 16px;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.8);
      border: 1px solid rgba(255, 255, 255, 0.7);
      font-weight: 600;
      color: inherit;
      text-decoration: none;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .nav-pill:hover {
      transform: translateY(-2px);
      box-shadow: 0 20px 42px rgba(125, 94, 210, 0.24);
    }

    .nav-pill.is-active {
      background: linear-gradient(120deg, #ffb9e8, #b99eff);
      color: #fff;
      box-shadow: 0 22px 44px rgba(125, 94, 210, 0.3);
    }

    body.dashboard-page.dark-theme .nav-pill {
      background: rgba(24, 21, 44, 0.86);
      border-color: rgba(112, 92, 190, 0.38);
    }

    .theme-toggle {
      display: inline-flex;
      align-items: center;
      gap: 9px;
      padding: 10px 18px;
      border-radius: 999px;
      border: 1px solid rgba(255, 255, 255, 0.66);
      background: linear-gradient(135deg, #fbd5ff, #d8c6ff);
      color: #3a295b;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 20px 36px rgba(150, 110, 255, 0.26);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .theme-toggle:hover {
      transform: translateY(-2px);
      box-shadow: 0 26px 52px rgba(150, 110, 255, 0.3);
    }

    body.dashboard-page.dark-theme .theme-toggle {
      background: rgba(25, 22, 46, 0.92);
      border: 1px solid rgba(112, 92, 190, 0.42);
      color: #f6ebff;
      box-shadow: 0 20px 46px rgba(4, 3, 12, 0.65);
    }

    .user-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 15px;
      border-radius: 999px;
      background: rgba(28, 24, 50, 0.86);
      color: #f5eaff;
      border: 1px solid rgba(112, 92, 190, 0.36);
      font-weight: 600;
    }

    .user-pill .seed {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: grid;
      place-items: center;
      background: linear-gradient(135deg, #8d6dff, #271b48);
      color: #ffe9fb;
      font-weight: 700;
    }

    .hero-card {
      display: grid;
      gap: 18px;
    }

    .hero-card h1 {
      margin: 0;
      font-size: clamp(2rem, 3vw, 2.6rem);
      letter-spacing: -0.02em;
    }

    .hero-card p {
      margin: 0;
      max-width: 640px;
      line-height: 1.6;
      color: var(--text-muted);
    }

    body.dashboard-page.dark-theme .hero-card p {
      color: rgba(236, 224, 255, 0.72);
    }

    .stats-grid {
      display: grid;
      gap: 16px;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      margin-top: 12px;
    }

    .stats-card {
      padding: 20px;
      border-radius: 22px;
      background: linear-gradient(135deg, rgba(255, 185, 233, 0.22), rgba(185, 170, 255, 0.22));
      border: 1px solid rgba(185, 170, 255, 0.32);
      display: grid;
      gap: 6px;
    }

    .stats-card strong {
      font-size: 2.1rem;
    }

    .stats-card span {
      font-size: 0.92rem;
      color: var(--text-muted);
    }

    body.dashboard-page.dark-theme .stats-card {
      background: rgba(34, 29, 58, 0.86);
      border-color: rgba(112, 92, 190, 0.4);
    }

    body.dashboard-page.dark-theme .stats-card span {
      color: rgba(236, 224, 255, 0.72);
    }

    .action-grid {
      display: grid;
      gap: 16px;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      margin-top: 22px;
    }

    .action-card {
      padding: 20px;
      border-radius: 22px;
      background: rgba(255, 255, 255, 0.82);
      border: 1px solid rgba(255, 255, 255, 0.7);
      box-shadow: 0 20px 48px rgba(125, 94, 210, 0.14);
      display: grid;
      gap: 12px;
    }

    .action-card h3 {
      margin: 0;
      font-size: 1.16rem;
      display: flex;
      align-items: center;
      gap: 10px;
      color: #4a2d7a;
    }

    .action-card p {
      margin: 0;
      color: var(--text-muted);
      line-height: 1.5;
    }

    body.dashboard-page.dark-theme .action-card {
      background: rgba(24, 21, 46, 0.94);
      border-color: rgba(112, 92, 190, 0.36);
    }

    body.dashboard-page.dark-theme .action-card h3 {
      color: #ffd6f3;
    }

    body.dashboard-page.dark-theme .action-card p {
      color: rgba(236, 224, 255, 0.7);
    }

    .pill-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 11px 18px;
      border-radius: 14px;
      border: 1px solid rgba(118, 96, 210, 0.28);
      background: linear-gradient(135deg, #7c5bff, #f58acb);
      color: #fff;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .pill-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 24px 52px rgba(125, 94, 210, 0.28);
    }

    .pill-btn.secondary {
      background: rgba(255, 255, 255, 0.86);
      color: inherit;
      border: 1px solid rgba(118, 96, 210, 0.24);
      box-shadow: none;
    }

    body.dashboard-page.dark-theme .pill-btn.secondary {
      background: rgba(25, 22, 46, 0.88);
      color: #f4e9ff;
    }

    .floating-nav {
      position: sticky;
      bottom: 20px;
      display: flex;
      gap: 10px;
      justify-content: center;
      padding: 12px;
      border-radius: 20px;
      background: rgba(26, 22, 44, 0.16);
      backdrop-filter: blur(18px);
      border: 1px solid rgba(118, 96, 210, 0.26);
      margin-top: 26px;
    }

    .floating-nav a {
      flex: 1;
      text-align: center;
      padding: 10px 14px;
      border-radius: 14px;
      text-decoration: none;
      font-weight: 600;
      color: inherit;
      background: rgba(255, 255, 255, 0.82);
      border: 1px solid rgba(118, 96, 210, 0.28);
    }

    body.dashboard-page.dark-theme .floating-nav {
      background: rgba(10, 8, 20, 0.72);
      border-color: rgba(112, 92, 190, 0.34);
    }

    body.dashboard-page.dark-theme .floating-nav a {
      background: rgba(26, 22, 46, 0.9);
      border-color: rgba(112, 92, 190, 0.34);
      color: #f4e9ff;
    }

    .footer-note {
      text-align: center;
      font-size: 0.88rem;
      color: rgba(35, 28, 60, 0.66);
    }

    body.dashboard-page.dark-theme .footer-note {
      color: rgba(236, 224, 255, 0.6);
    }

    @media (max-width: 860px) {
      body.dashboard-page { padding: 46px 14px 100px; }
      .top-header { flex-direction: column; align-items: flex-start; }
      .floating-nav { position: fixed; left: 14px; right: 14px; bottom: 18px; }
    }

    @media (max-width: 560px) {
      .glass-panel { padding: 22px; }
      .nav-line { width: 100%; }
      .pill-btn { justify-content: center; }
      .action-card { text-align: center; }
      .stats-card { text-align: center; }
    }
  </style>
</head>
<body class="dashboard-page">
  <div class="dashboard-shell">
    <header class="glass-panel top-header">
      <a class="brand-link" href="<?= base_url('index.php') ?>">
        <span>🌸</span>
        <span class="brand-name"><?= e(APP_NAME) ?></span>
      </a>
      <div class="nav-line">
        <button class="theme-toggle" id="theme-toggle" type="button" aria-pressed="false">
          <span id="theme-icon">🌸</span>
          <span id="theme-text">Pembe</span>
        </button>
        <?php foreach ($navLinks as $item): ?>
          <a class="nav-pill<?= $item['href'] === 'dashboard.php' ? ' is-active' : '' ?>" href="<?= base_url($item['href']) ?>">
            <span><?= $item['icon'] ?></span><?= e($item['label']) ?>
          </a>
        <?php endforeach; ?>
        <span class="user-pill">
          <span class="seed"><?= e(user_initial_dashboard($user)) ?></span>
          <?= e($user['username']) ?>
        </span>
        <a class="nav-pill" href="<?= base_url('logout.php') ?>">Çıkış</a>
      </div>
    </header>

    <section class="glass-panel hero-card">
      <h1>Tek panelden tüm Craftrolle evrenine hükmet.</h1>
      <p>Kitaplarını yönet, notlarını düzenle ve tasarım araçlarına saniyeler içinde eriş. İlerleyişin aşağıdaki özetlerde.</p>
      <div class="stats-grid">
        <div class="stats-card">
          <div style="font-size: 1.6rem;">📚</div>
          <strong><?= $booksCount ?></strong>
          <span>aktif kitap</span>
        </div>
        <div class="stats-card">
          <div style="font-size: 1.6rem;">📝</div>
          <strong><?= $notesCount ?></strong>
          <span>kaydedilen not</span>
        </div>
      </div>
    </section>

    <section class="action-grid">
      <article class="action-card">
        <h3>✍️ Yazmaya Başla</h3>
        <p>Yeni bir kitap projesi oluştur ya da mevcut taslakların üzerinde çalışmayı sürdür.</p>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
          <a class="pill-btn" href="<?= base_url('book_new.php') ?>">+ Yeni Kitap</a>
          <a class="pill-btn secondary" href="<?= base_url('books.php') ?>">Kitaplarım</a>
        </div>
      </article>
      <article class="action-card">
        <h3>🗒️ Notlar ve Fikirler</h3>
        <p>Karakter ve sahne notlarını düzenle, fikirlerini kaybetmeden ilerle.</p>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
          <a class="pill-btn" href="<?= base_url('note_create.php') ?>">+ Yeni Not</a>
          <a class="pill-btn secondary" href="<?= base_url('notes.php') ?>">Notlarım</a>
        </div>
      </article>
      <article class="action-card">
        <h3>🎨 Tasarım Stüdyosu</h3>
        <p>Kapak ve harita araçlarıyla evrenine görsel kimlik kazandır.</p>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
          <a class="pill-btn secondary" href="<?= base_url('designer_cover.php') ?>">Kapak Tasarla</a>
          <a class="pill-btn secondary" href="<?= base_url('designer_map.php') ?>">Harita Oluştur</a>
        </div>
      </article>
      <article class="action-card">
        <h3>🎉 İlham ve Araçlar</h3>
        <p>Craftrolle eğlence stüdyosu ile yeni kombinasyonlar dene, yaratıcılığını tazele.</p>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
          <a class="pill-btn secondary" href="<?= base_url('eglence.php') ?>">Eğlence Stüdyosu</a>
          <a class="pill-btn secondary" href="<?= base_url('3d/view_book.php') ?>">3D Görüntüleyici</a>
        </div>
      </article>
    </section>
  </div>

  <nav class="floating-nav">
    <a href="<?= base_url('books.php') ?>">📚 Kitaplar</a>
    <a href="<?= base_url('notes.php') ?>">📝 Notlar</a>
    <a href="<?= base_url('designer_cover.php') ?>">🎨 Kapak</a>
    <a href="<?= base_url('designer_map.php') ?>">🗺️ Harita</a>
  </nav>

  <footer class="footer-note">© <?= date('Y') ?> <?= e(APP_NAME) ?> · Panel</footer>

  <script>
  (function(){
    const toggleBtn = document.getElementById('theme-toggle');
    if(!toggleBtn) return;
    const icon = document.getElementById('theme-icon');
    const text = document.getElementById('theme-text');
    const storageKey = 'craft-dashboard-theme';

    function apply(mode) {
      const isDark = mode === 'dark';
      document.body.classList.toggle('dark-theme', isDark);
      if(icon) icon.textContent = isDark ? '🌙' : '🌸';
      if(text) text.textContent = isDark ? 'Gece' : 'Pembe';
      toggleBtn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
      try { localStorage.setItem(storageKey, mode); } catch (_) {}
    }

    let saved = null;
    try { saved = localStorage.getItem(storageKey); } catch (_) {}
    apply(saved === 'dark' ? 'dark' : 'light');

    toggleBtn.addEventListener('click', () => {
      const next = document.body.classList.contains('dark-theme') ? 'light' : 'dark';
      apply(next);
    });
  })();
  </script>
</body>
</html>
