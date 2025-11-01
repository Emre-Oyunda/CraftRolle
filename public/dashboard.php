<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_login();

$user = current_user();

$booksCount = db()->query("SELECT COUNT(*) FROM books WHERE user_id = " . (int)$user['id'] . " AND is_deleted = 0")->fetchColumn();
$notesCount = db()->query("SELECT COUNT(*) FROM notes WHERE user_id = " . (int)$user['id'] . " AND is_deleted = 0")->fetchColumn();
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Panel - <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body.dashboard-legacy {
  margin: 0;
  min-height: 100vh;
  font-family: 'Inter','Segoe UI',Tahoma,sans-serif;
  background:
    radial-gradient(circle at 12% -10%, rgba(255,214,244,0.8), transparent 55%),
    radial-gradient(circle at 90% 0%, rgba(192,215,255,0.6), transparent 55%),
    linear-gradient(135deg,#f6f1ff 0%,#efe4ff 45%,#ffe5f5 100%);
  color:#2b2144;
  padding:48px 18px 80px;
}
body.dashboard-legacy.dark-mode {
  color:#f5ecff;
  background:
    radial-gradient(circle at 14% -12%, rgba(80,64,130,0.55), transparent 55%),
    radial-gradient(circle at 90% 0%, rgba(210,86,150,0.48), transparent 60%),
    linear-gradient(135deg,#0f0b1f 0%,#161129 40%,#1f1736 100%);
}
.dashboard-shell {
  max-width: 1100px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 24px;
  position: relative;
  z-index: 1;
}
.card.header {
  width: 100%;
  padding: 18px 22px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  border-radius: 20px;
  background: rgba(255,255,255,0.82);
  border: 1px solid rgba(255,255,255,0.7);
  box-shadow: 0 22px 46px rgba(120,90,210,0.14);
  backdrop-filter: blur(18px);
}
body.dashboard-legacy.dark-mode .card.header {
  background: rgba(24,21,46,0.92);
  border-color: rgba(108,92,190,0.38);
  box-shadow: 0 24px 52px rgba(8,6,18,0.7);
}
.card.header .left-stack {
  display:flex;
  flex-wrap:wrap;
  align-items:center;
  gap:10px;
}
.brand {
  background: linear-gradient(120deg,#ff9fdc,#8c74ff);
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
  font-weight:800;
}
.badge {
  background:rgba(30,24,52,0.85);
  border:1px solid rgba(112,94,190,0.32);
  color:#f7ebff;
  padding:8px 14px;
  border-radius:999px;
  font-weight:600;
  display:inline-flex;
  gap:6px;
  align-items:center;
}
.header .nav-inline {
  display:flex;
  flex:1 1 280px;
  flex-wrap:wrap;
  align-items:center;
  justify-content:flex-end;
  gap:10px;
}
.header .nav-inline a {
  padding:8px 14px;
  border-radius:999px;
  border:1px solid rgba(124,91,255,0.2);
  background:rgba(255,255,255,0.9);
  font-weight:600;
  color:#2b2144;
  text-decoration:none;
  transition:box-shadow 0.2s ease,transform 0.2s ease;
}
.header .nav-inline a:hover {
  transform:translateY(-1px);
  box-shadow:0 16px 32px rgba(124,91,255,0.18);
}
body.dashboard-legacy.dark-mode .header .nav-inline a {
  background:rgba(28,24,50,0.92);
  border-color:rgba(108,92,190,0.36);
  color:#f6ecff;
}
.theme-toggle {
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:9px 16px;
  border-radius:999px;
  border:1px solid rgba(255,255,255,0.68);
  background:linear-gradient(120deg,#fcd9ff,#d6c6ff);
  font-weight:600;
  color:#3a295b;
  cursor:pointer;
  box-shadow:0 20px 38px rgba(150,110,255,0.24);
  transition:transform 0.2s ease,box-shadow 0.2s ease;
}
.theme-toggle:hover {
  transform:translateY(-2px);
  box-shadow:0 26px 54px rgba(150,110,255,0.28);
}
body.dashboard-legacy.dark-mode .theme-toggle {
  background:rgba(26,23,46,0.92);
  border-color:rgba(108,92,190,0.42);
  color:#f6ebff;
  box-shadow:0 20px 48px rgba(8,6,18,0.65);
}
.primary-hero {
  border-radius:24px;
  background:rgba(255,255,255,0.86);
  border:1px solid rgba(255,255,255,0.7);
  box-shadow:0 24px 52px rgba(120,90,210,0.16);
  padding:26px 28px;
  backdrop-filter:blur(18px);
}
body.dashboard-legacy.dark-mode .primary-hero {
  background:rgba(24,21,46,0.9);
  border-color:rgba(108,92,190,0.36);
  box-shadow:0 26px 60px rgba(8,6,18,0.68);
}
.primary-hero h2 {
  margin:0 0 10px;
  font-size:clamp(1.8rem,3vw,2.4rem);
}
.primary-hero p {
  margin:0;
  color:rgba(43,33,68,0.72);
  max-width:620px;
  line-height:1.6;
}
body.dashboard-legacy.dark-mode .primary-hero p {
  color:rgba(235,224,255,0.74);
}
.quick-stats {
  display:grid;
  gap:16px;
  grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
  margin-top:18px;
}
.stat-card {
  border-radius:18px;
  background:linear-gradient(135deg,rgba(255,190,233,0.22),rgba(190,176,255,0.22));
  border:1px solid rgba(190,176,255,0.32);
  padding:18px 20px;
  display:grid;
  gap:6px;
}
body.dashboard-legacy.dark-mode .stat-card {
  background:rgba(32,29,58,0.88);
  border-color:rgba(108,92,190,0.36);
}
.stat-card strong {
  font-size:2.1rem;
  color:#432a70;
}
body.dashboard-legacy.dark-mode .stat-card strong {
  color:#f7ecff;
}
.stat-card span {
  font-size:0.9rem;
  color:rgba(43,33,68,0.6);
}
body.dashboard-legacy.dark-mode .stat-card span {
  color:rgba(235,224,255,0.66);
}
.grid.g-3 {
  display:grid;
  gap:18px;
  grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
}
.card.action-card {
  background:rgba(255,255,255,0.9);
  border-radius:22px;
  border:1px solid rgba(255,255,255,0.7);
  box-shadow:0 18px 42px rgba(120,90,210,0.12);
  padding:22px;
  display:grid;
  gap:12px;
}
body.dashboard-legacy.dark-mode .card.action-card {
  background:rgba(28,24,50,0.92);
  border-color:rgba(108,92,190,0.36);
  box-shadow:0 20px 48px rgba(8,6,18,0.68);
}
.action-card h3 {
  margin:0;
  font-size:1.18rem;
  color:#4b2d78;
  display:flex;
  gap:8px;
  align-items:center;
}
body.dashboard-legacy.dark-mode .action-card h3 {
  color:#fbd8ff;
}
.action-card p {
  margin:0;
  color:rgba(43,33,68,0.68);
  line-height:1.55;
}
body.dashboard-legacy.dark-mode .action-card p {
  color:rgba(235,224,255,0.68);
}
.action-buttons {
  display:flex;
  gap:10px;
  flex-wrap:wrap;
}
.pill-btn {
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:10px 16px;
  border-radius:14px;
  text-decoration:none;
  font-weight:600;
  background:linear-gradient(120deg,#7c5bff,#f58acb);
  color:#fff;
  border:1px solid rgba(124,91,255,0.28);
  box-shadow:0 20px 42px rgba(124,91,255,0.24);
  transition:transform 0.24s ease,box-shadow 0.24s ease;
}
.pill-btn:hover {
  transform:translateY(-2px);
  box-shadow:0 26px 56px rgba(124,91,255,0.28);
}
.pill-btn.secondary {
  background:rgba(255,255,255,0.86);
  color:#2b2144;
  border:1px solid rgba(124,91,255,0.2);
  box-shadow:none;
}
body.dashboard-legacy.dark-mode .pill-btn.secondary {
  background:rgba(28,24,50,0.88);
  color:#f5ecff;
  border-color:rgba(108,92,190,0.38);
}
.bottom-nav {
  position:sticky;
  bottom:0;
  display:flex;
  gap:10px;
  justify-content:space-around;
  padding:12px;
  border-radius:16px;
  background:rgba(20,17,40,0.18);
  backdrop-filter:blur(16px);
  border:1px solid rgba(124,91,255,0.24);
  margin-top:28px;
}
.bottom-nav a {
  flex:1;
  text-align:center;
  padding:10px;
  border-radius:12px;
  background:rgba(255,255,255,0.85);
  border:1px solid rgba(124,91,255,0.2);
  color:#2b2144;
  font-weight:600;
  text-decoration:none;
}
body.dashboard-legacy.dark-mode .bottom-nav {
  background:rgba(8,6,18,0.7);
  border-color:rgba(108,92,190,0.34);
}
body.dashboard-legacy.dark-mode .bottom-nav a {
  background:rgba(28,24,50,0.9);
  border-color:rgba(108,92,190,0.34);
  color:#f5ecff;
}
.dashboard-footer {
  text-align:center;
  color:rgba(43,33,68,0.62);
  font-size:0.88rem;
  margin-top:12px;
}
body.dashboard-legacy.dark-mode .dashboard-footer {
  color:rgba(236,224,255,0.58);
}
@media(max-width:900px) {
  .header .nav-inline {
    justify-content:flex-start;
  }
}
@media(max-width:720px) {
  body.dashboard-legacy {
    padding:36px 14px 90px;
  }
  .card.header {
    align-items:flex-start;
  }
  .header .nav-inline {
    width:100%;
    justify-content:flex-start;
  }
  .primary-hero {
    padding:22px;
  }
  .bottom-nav {
    position:fixed;
    left:14px;
    right:14px;
    bottom:18px;
  }
}
</style>
</head>
<body class="dashboard-legacy">
<div class="dashboard-shell">
  <div class="card header">
    <div class="left-stack">
      <a class="btn" href="<?= base_url('index.php') ?>" style="text-decoration:none;">🌸 <span class="brand"><?= e(APP_NAME) ?></span></a>
      <button class="theme-toggle" id="dashboard-theme" type="button" aria-pressed="false"><span id="dash-theme-icon">🌸</span><span id="dash-theme-text">Pembe</span></button>
    </div>
    <div class="nav-inline">
      <span class="badge">Merhaba, <?= e($user['username']) ?></span>
      <a href="<?= base_url('dashboard.php') ?>">Panel</a>
      <a href="<?= base_url('books.php') ?>">Kitaplarım</a>
      <a href="<?= base_url('notes.php') ?>">Notlarım</a>
      <a href="<?= base_url('eglence.php') ?>">Eğlence</a>
      <a href="<?= base_url('designer_cover.php') ?>">Kapak</a>
      <a href="<?= base_url('../profil/profil_advanced.php') ?>">Profilim</a>
      <a href="<?= base_url('designer_map.php') ?>">Harita</a>
      <a href="<?= base_url('logout.php') ?>">Çıkış</a>
    </div>
  </div>

  <section class="primary-hero">
    <h2>🏠 Hoş Geldin, <?= e($user['username']) ?>!</h2>
    <p>KitapKurdu panelinden kitaplarını yönet, notlarını düzenle ve tasarım araçlarına saniyeler içinde ulaş. Aşağıdaki kısayollar seni bekliyor.</p>
    <div class="quick-stats">
      <div class="stat-card">
        <div style="font-size:1.5rem;">📚</div>
        <strong><?= (int)$booksCount ?></strong>
        <span>Kitap stüdyona göz at.</span>
        <a class="pill-btn secondary" href="<?= base_url('books.php') ?>">Kitaplarımı Aç</a>
      </div>
      <div class="stat-card">
        <div style="font-size:1.5rem;">📝</div>
        <strong><?= (int)$notesCount ?></strong>
        <span>Fikirlerini kaydetmeye devam et.</span>
        <a class="pill-btn secondary" href="<?= base_url('notes.php') ?>">Notlarımı Aç</a>
      </div>
      <div class="stat-card">
        <div style="font-size:1.5rem;">🎉</div>
        <strong>Eğlence</strong>
        <span>Kitap yazarken işini kolaylaştır!</span>
        <div class="action-buttons" style="margin-top:6px;">
          <a class="pill-btn secondary" href="<?= base_url('eglence.php') ?>">Eğlence</a>
        </div>
      </div>
    </div>
  </section>

  <div class="grid g-3">
    <div class="card action-card">
      <h3>📚 Kitaplarım</h3>
      <p>Yeni bir kitap projesi başlat ya da mevcut çalışmalarını düzenlemeye devam et.</p>
      <div class="action-buttons">
        <a class="pill-btn" href="<?= base_url('book_new.php') ?>">+ Yeni Kitap</a>
        <a class="pill-btn secondary" href="<?= base_url('books.php') ?>">Tüm Kitaplarım</a>
      </div>
    </div>
    <div class="card action-card">
      <h3>📝 Notlarım</h3>
      <p>Karakter fişleri, sahne planları ve fikirlerini tek noktada topla.</p>
      <div class="action-buttons">
        <a class="pill-btn" href="<?= base_url('notes.php') ?>">Notlarımı Aç</a>
      </div>
    </div>
    <div class="card action-card">
      <h3>🎨 Tasarım Stüdyosu</h3>
      <p>Kapak ve harita araçlarıyla evrenine görsel kimlik kazandır.</p>
      <div class="action-buttons">
        <a class="pill-btn secondary" href="<?= base_url('designer_cover.php') ?>">Kapak Tasarla</a>
        <a class="pill-btn secondary" href="<?= base_url('designer_map.php') ?>">Harita Oluştur</a>
      </div>
    </div>
  </div>

  <div class="bottom-nav">
    <a href="<?= base_url('books.php') ?>">📚 Kitaplar</a>
    <a href="<?= base_url('notes.php') ?>">📝 Notlar</a>
    <a href="<?= base_url('designer_cover.php') ?>">🎨 Kapak</a>
    <a href="<?= base_url('designer_map.php') ?>">🗺️ Harita</a>
  </div>
  <div class="dashboard-footer">© <?= date('Y') ?> <?= e(APP_NAME) ?>🌸</div>
</div>

<script>
(function(){
  const btn=document.getElementById('dashboard-theme');
  if(!btn) return;
  const icon=document.getElementById('dash-theme-icon');
  const text=document.getElementById('dash-theme-text');
  const key='craft-dashboard-theme';
  function apply(mode){
    const isDark=mode==='dark';
    document.body.classList.toggle('dark-mode',isDark);
    if(icon) icon.textContent=isDark?'🌙':'🌸';
    if(text) text.textContent=isDark?'Siyah':'Pembe';
    btn.setAttribute('aria-pressed',isDark?'true':'false');
    try{localStorage.setItem(key,mode);}catch(e){}
  }
  let saved=null;
  try{saved=localStorage.getItem(key);}catch(e){}
  apply(saved==='dark'?'dark':'light');
  btn.addEventListener('click',()=>{
    apply(document.body.classList.contains('dark-mode')?'light':'dark');
  });
})();
</script>
</body>
</html>
