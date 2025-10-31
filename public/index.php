<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';

$user = current_user();
$isAdmin = (bool)($user['is_admin'] ?? false);

$navLinks = [
  ['label' => 'Panel', 'href' => 'dashboard.php'],
  ['label' => 'Kitaplar', 'href' => 'books.php'],
  ['label' => 'Notlar', 'href' => 'notes.php'],
  ['label' => 'Eğlence', 'href' => 'eglence.php'],
  ['label' => 'Kapak', 'href' => 'designer_cover.php'],
  ['label' => 'Harita', 'href' => 'designer_map.php'],
];

if ($isAdmin) {
  $navLinks[] = ['label' => 'Admin', 'href' => '../admin/panel.php'];
}

function user_initial(?array $user): string {
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
  <title><?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    :root {
      --bg-light: #f9f6ff;
      --bg-accent: #ffe9f6;
      --violet-500: #7c3aed;
      --violet-400: #9f67ff;
      --pink-400: #f472b6;
      --pink-200: #ffd5f1;
      --text-base: #201a33;
      --text-muted: rgba(32, 26, 51, 0.68);
      --glass-light: rgba(255, 255, 255, 0.78);
      --glass-dark: rgba(14, 10, 26, 0.78);
      --shadow-lg: 0 26px 70px rgba(124, 58, 237, 0.18);
    }

    * { box-sizing: border-box; }

    body.landing-page {
      margin: 0;
      min-height: 100vh;
      font-family: 'Inter', 'Segoe UI', Tahoma, sans-serif;
      color: var(--text-base);
      background:
        radial-gradient(circle at 10% -10%, rgba(255, 206, 242, 0.8), transparent 55%),
        radial-gradient(circle at 90% 0%, rgba(198, 215, 255, 0.6), transparent 55%),
        linear-gradient(135deg, #f9f6ff 0%, #f1e6ff 45%, var(--bg-accent) 100%);
      padding: 64px 18px 80px;
      position: relative;
      overflow-x: hidden;
    }

    body.landing-page::before,
    body.landing-page::after {
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

    body.landing-page::before {
      top: -160px;
      left: -160px;
      background: linear-gradient(135deg, rgba(255, 177, 226, 0.65), rgba(255, 236, 252, 0.5));
    }

    body.landing-page::after {
      bottom: -200px;
      right: -160px;
      background: linear-gradient(135deg, rgba(151, 122, 255, 0.6), rgba(111, 196, 255, 0.5));
    }

    .landing-shell {
      position: relative;
      z-index: 1;
      max-width: 1180px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      gap: 30px;
    }

    .glass-card {
      background: var(--glass-light);
      border-radius: 28px;
      border: 1px solid rgba(255, 255, 255, 0.7);
      padding: 24px 30px;
      box-shadow: var(--shadow-lg);
      backdrop-filter: blur(20px);
      transition: transform 0.28s ease, box-shadow 0.28s ease;
    }

    .glass-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 32px 80px rgba(124, 58, 237, 0.26);
    }

    .landing-header {
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
      font-size: 1.74rem;
      font-weight: 700;
      text-decoration: none;
      color: inherit;
    }

    .brand-link .brand-name {
      background: linear-gradient(120deg, var(--pink-400), var(--violet-500));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .nav-pills {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
    }

    .nav-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 10px 16px;
      border-radius: 999px;
      border: 1px solid rgba(255, 255, 255, 0.65);
      background: rgba(255, 255, 255, 0.78);
      font-weight: 600;
      color: var(--text-base);
      text-decoration: none;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .nav-pill:hover {
      transform: translateY(-2px);
      box-shadow: 0 20px 40px rgba(124, 58, 237, 0.18);
    }

    .user-chip {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 14px;
      border-radius: 999px;
      background: rgba(24, 22, 44, 0.82);
      color: #f7ecff;
      border: 1px solid rgba(124, 58, 237, 0.3);
      font-weight: 600;
    }

    .avatar-seed {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      display: grid;
      place-items: center;
      background: linear-gradient(135deg, var(--violet-500), #201437);
      color: #ffe7fb;
      font-weight: 700;
      font-size: 0.98rem;
    }

    .hero {
      display: grid;
      gap: 22px;
    }

    .hero h1 {
      margin: 0;
      font-size: clamp(2.2rem, 3vw, 2.8rem);
      letter-spacing: -0.03em;
    }

    .hero p {
      margin: 0;
      max-width: 640px;
      line-height: 1.6;
      color: var(--text-muted);
    }

    .hero-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      align-items: center;
    }

    .pill-btn {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 12px 20px;
      border-radius: 16px;
      border: 1px solid rgba(124, 58, 237, 0.32);
      background: linear-gradient(120deg, var(--violet-500), var(--pink-400));
      color: #fff;
      font-weight: 700;
      text-decoration: none;
      box-shadow: 0 20px 40px rgba(124, 58, 237, 0.22);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .pill-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 26px 60px rgba(124, 58, 237, 0.28);
    }

    .pill-btn.secondary {
      background: rgba(255, 255, 255, 0.78);
      color: var(--text-base);
      border: 1px solid rgba(124, 58, 237, 0.2);
      box-shadow: none;
    }

    .badge-row {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    .badge-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 14px;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.82);
      border: 1px solid rgba(124, 58, 237, 0.18);
      font-weight: 600;
      color: var(--text-muted);
      font-size: 0.92rem;
    }

    .feature-grid {
      display: grid;
      gap: 18px;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }

    .feature-card {
      padding: 22px 20px;
      border-radius: 22px;
      background: rgba(255, 255, 255, 0.82);
      border: 1px solid rgba(255, 255, 255, 0.7);
      box-shadow: 0 18px 50px rgba(124, 58, 237, 0.12);
      display: grid;
      gap: 10px;
      transition: transform 0.22s ease;
    }

    .feature-card:hover {
      transform: translateY(-4px);
    }

    .feature-card h3 {
      margin: 0;
      font-size: 1.14rem;
      color: #4b2c74;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .feature-card p {
      margin: 0;
      line-height: 1.54;
      color: var(--text-muted);
      font-size: 0.95rem;
    }

    .quick-actions {
      display: grid;
      gap: 14px;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    }

    .action-card {
      padding: 20px;
      border-radius: 22px;
      background: linear-gradient(135deg, rgba(124, 58, 237, 0.14), rgba(244, 114, 182, 0.18));
      border: 1px solid rgba(124, 58, 237, 0.2);
      display: grid;
      gap: 14px;
    }

    .action-card h4 {
      margin: 0;
      font-size: 1.1rem;
      color: #3e256b;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .action-links {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .action-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 9px 14px;
      border-radius: 999px;
      text-decoration: none;
      font-weight: 600;
      background: rgba(255, 255, 255, 0.86);
      color: var(--text-base);
      border: 1px solid rgba(124, 58, 237, 0.22);
      transition: transform 0.2s ease;
    }

    .action-link:hover {
      transform: translateY(-2px);
    }

    .cta-banner {
      padding: 28px 30px;
      border-radius: 28px;
      background: linear-gradient(160deg, rgba(124, 58, 237, 0.88), rgba(244, 114, 182, 0.82));
      color: #fff;
      display: grid;
      gap: 14px;
      align-items: center;
    }

    .cta-banner h3 {
      margin: 0;
      font-size: 1.54rem;
    }

    .footer-note {
      text-align: center;
      color: rgba(32, 26, 51, 0.72);
      font-size: 0.9rem;
    }

    @media (max-width: 860px) {
      body.landing-page { padding: 48px 16px 70px; }
      .landing-header { flex-direction: column; align-items: flex-start; }
      .nav-pills { width: 100%; justify-content: flex-start; }
      .hero-actions { justify-content: flex-start; }
    }

    @media (max-width: 560px) {
      body.landing-page { padding: 40px 14px 60px; }
      .glass-card { padding: 22px; }
      .feature-card { padding: 18px; }
      .cta-banner { padding: 24px; }
    }
  </style>
</head>
<body class="landing-page">
  <div class="landing-shell">
    <header class="glass-card landing-header">
      <a class="brand-link" href="<?= base_url('index.php') ?>">
        <span>🌸</span>
        <span class="brand-name"><?= e(APP_NAME) ?></span>
      </a>
      <div class="nav-pills">
        <?php foreach ($navLinks as $link): ?>
          <a class="nav-pill" href="<?= base_url($link['href']) ?>"><?= e($link['label']) ?></a>
        <?php endforeach; ?>
        <?php if ($user): ?>
          <span class="user-chip">
            <span class="avatar-seed"><?= e(user_initial($user)) ?></span>
            <span>Merhaba, <?= e($user['username']) ?></span>
          </span>
          <a class="nav-pill" href="<?= base_url('logout.php') ?>">Çıkış</a>
        <?php else: ?>
          <a class="nav-pill" href="<?= base_url('login.php') ?>">Giriş</a>
          <a class="nav-pill" href="<?= base_url('register.php') ?>">Kayıt Ol</a>
        <?php endif; ?>
      </div>
    </header>

    <section class="glass-card hero">
      <h1>Hikayelerini Craftrolle ile hayata geçir.</h1>
      <p>
        Yazdığın dünyaları 3D kitaplara dönüştür, arkadaşlarınla paylaş, kapak ve haritalar oluştur.
        Tek bir stüdyoda hem yazar hem tasarımcı olmanın keyfini çıkar.
      </p>
      <div class="hero-actions">
        <a class="pill-btn" href="<?= base_url($user ? 'dashboard.php' : 'register.php') ?>">
          <?= $user ? 'Panoya Git' : 'Hemen Başla' ?>
        </a>
        <a class="pill-btn secondary" href="<?= base_url('books.php') ?>">Örnek Kitaplara Göz At</a>
      </div>
      <div class="badge-row">
        <span class="badge-pill">🚀 Hızlıca fikirden kitaba</span>
        <span class="badge-pill">🎨 Zengin tasarım araçları</span>
        <span class="badge-pill">📚 3D kitap önizleme</span>
        <span class="badge-pill">🤝 Topluluk paylaşımı</span>
      </div>
    </section>

    <section class="feature-grid">
      <div class="feature-card">
        <h3>📚 Kitap Stüdyosu</h3>
        <p>Metinlerini bölümlere ayır, kolayca düzenle ve gerçekçi 3D çevirme animasyonuyla canlandır.</p>
      </div>
      <div class="feature-card">
        <h3>🎨 Kapak Tasarımcısı</h3>
        <p>Hazır şablonlarla dakikalar içinde göz alıcı kapaklar oluştur, dışa aktar ve paylaş.</p>
      </div>
      <div class="feature-card">
        <h3>🗺️ Harita Editörü</h3>
        <p>Fantastik evrenler için haritalar çiz, mekânlarını konumlandır, hikayene görsel derinlik kat.</p>
      </div>
      <div class="feature-card">
        <h3>📝 Notlar & Fikir Ağacı</h3>
        <p>Karakter, olay ve sahne notlarını tek merkezde tut, yaratıcı süreci kaybetme.</p>
      </div>
      <div class="feature-card">
        <h3>🎉 Eğlence Stüdyosu</h3>
        <p>Zar, kelime, duygu kartları ve isim üreticisiyle yazarlık blokunu kır.</p>
      </div>
      <div class="feature-card">
        <h3>📄 Yazdırma & İhracat</h3>
        <p>PDF veya baskı için hazır formatlarda proje dışa aktar, profesyonel çıktılar al.</p>
      </div>
    </section>

    <section class="glass-card">
      <?php if ($user): ?>
        <h2 style="margin: 0 0 10px;">Merhaba <?= e($user['username']) ?> 👋</h2>
        <p style="margin: 0 0 26px; color: var(--text-muted);">Bugün hangi projeni büyütmek istersin? Popüler araçlar aşağıda seni bekliyor.</p>
        <div class="quick-actions">
          <div class="action-card">
            <h4>✍️ Yazmaya Devam Et</h4>
            <p style="margin:0;color:var(--text-muted);">En son kitap taslağını aç veya yeni bir bölüm oluştur.</p>
            <div class="action-links">
              <a class="action-link" href="<?= base_url('books.php') ?>">Kitaplarım</a>
              <a class="action-link" href="<?= base_url('book_new.php') ?>">Yeni Kitap</a>
            </div>
          </div>
          <div class="action-card">
            <h4>🎨 Tasarımla</h4>
            <p style="margin:0;color:var(--text-muted);">Kapak veya harita ile evrenine görsel kimlik kazandır.</p>
            <div class="action-links">
              <a class="action-link" href="<?= base_url('designer_cover.php') ?>">Kapak Tasarımı</a>
              <a class="action-link" href="<?= base_url('designer_map.php') ?>">Harita Tasarımı</a>
            </div>
          </div>
          <div class="action-card">
            <h4>💡 İlham Al</h4>
            <p style="margin:0;color:var(--text-muted);">Rastgele fikirler ve kart desteleriyle hikayeni tazele.</p>
            <div class="action-links">
              <a class="action-link" href="<?= base_url('eglence.php') ?>">Eğlence Stüdyosu</a>
              <a class="action-link" href="<?= base_url('notes.php') ?>">Notlarım</a>
            </div>
          </div>
        </div>
      <?php else: ?>
        <h2 style="margin: 0 0 14px;">Yeni bir evren kurmaya hazır mısın?</h2>
        <p style="margin: 0 0 26px; color: var(--text-muted);">Craftrolle, hikayelerini derleyip paylaşabileceğin kapsamlı bir yazarlık stüdyosu sunar.</p>
        <div class="hero-actions" style="margin-bottom: 8px;">
          <a class="pill-btn" href="<?= base_url('register.php') ?>">Kayıt Ol</a>
          <a class="pill-btn secondary" href="<?= base_url('login.php') ?>">Giriş Yap</a>
        </div>
        <div class="badge-row">
          <span class="badge-pill">⚡ Deneme süresi yok, hemen başla</span>
          <span class="badge-pill">🌐 Çevrimiçi arayüz</span>
          <span class="badge-pill">💾 Çalışmaların güvende</span>
        </div>
      <?php endif; ?>
    </section>

    <section class="cta-banner">
      <h3>3D kitap önizleyici ile sahnelerini canlı gör.</h3>
      <p style="margin:0;max-width:620px;line-height:1.6;">
        Kütüphanendeki her proje tek tıklamayla 3D olarak açılır. Sayfa çevirme efektleri, ışık ve gölge detayları ile okuyucularını etkileyen sunumlar hazırla.
      </p>
      <div>
        <a class="pill-btn secondary" href="<?= base_url('3d/view_book.php') ?>" style="background: rgba(255,255,255,0.18); color:#fff; border-color: rgba(255,255,255,0.4);">
          3D Görüntüleyiciyi Aç
        </a>
      </div>
    </section>

    <footer class="footer-note">© <?= date('Y') ?> <?= e(APP_NAME) ?> · Hikayeler burada hayat bulur.</footer>
  </div>
</body>
</html>
