<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
$user = current_user();
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Yeni Kitap - <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body.book-new {
  font-family: 'Inter', 'Segoe UI', Tahoma, sans-serif;
  min-height: 100vh;
  padding: 36px 20px 48px;
  background: radial-gradient(circle at 12% 18%, #fff3fb 0%, #ffe6f4 35%, #f6eaff 70%, #f3f5ff 100%);
  color: #3f2851;
  transition: background 0.45s ease, color 0.45s ease;
  position: relative;
  overflow-x: hidden;
}

body.book-new::before,
body.book-new::after {
  content: '';
  position: fixed;
  border-radius: 50%;
  filter: blur(140px);
  opacity: 0.55;
  z-index: 0;
  transition: opacity 0.5s ease, transform 0.6s ease;
}

body.book-new::before {
  width: 420px;
  height: 420px;
  top: -120px;
  left: -80px;
  background: linear-gradient(135deg, rgba(255, 183, 224, 0.9), rgba(245, 207, 255, 0.7));
}

body.book-new::after {
  width: 360px;
  height: 360px;
  bottom: -140px;
  right: -80px;
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.6), rgba(255, 107, 157, 0.6));
}

body.book-new.dark-theme {
  background: radial-gradient(circle at 20% 20%, #150e24 0%, #0c0717 45%, #06030f 100%);
  color: #efe3ff;
}

body.book-new.dark-theme::before,
body.book-new.dark-theme::after {
  opacity: 0.25;
  transform: scale(1.08);
}

.book-new .container {
  max-width: 1100px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.glass-card {
  background: rgba(255, 255, 255, 0.75);
  border-radius: 22px;
  border: 1px solid rgba(255, 255, 255, 0.6);
  padding: 26px;
  box-shadow: 0 18px 48px rgba(198, 135, 255, 0.2);
  backdrop-filter: blur(24px);
  transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
}

.glass-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 26px 64px rgba(198, 107, 231, 0.28);
}

body.book-new.dark-theme .glass-card {
  background: rgba(18, 14, 34, 0.78);
  border: 1px solid rgba(124, 58, 237, 0.3);
  box-shadow: 0 20px 60px rgba(6, 2, 12, 0.6);
}

.top-shell {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
}

.brand-block {
  display: flex;
  flex-direction: column;
  gap: 8px;
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

.brand-icon {
  font-size: 1.8rem;
}

.brand-link span.brand {
  background: linear-gradient(135deg, #ff7ac0, #c46de8);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.brand-tagline {
  font-size: 0.92rem;
  opacity: 0.75;
  max-width: 440px;
  line-height: 1.5;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
}

.theme-toggle {
  display: inline-flex;
  align-items: center;
  gap: 14px;
  padding: 10px 16px 10px 12px;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.6);
  background: rgba(255, 255, 255, 0.6);
  color: #4f2f66;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 10px 28px rgba(255, 153, 211, 0.25);
  transition: transform 0.25s ease, box-shadow 0.3s ease, border-color 0.3s ease;
}

.theme-toggle:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 36px rgba(198, 107, 231, 0.32);
}

.toggle-track {
  position: relative;
  width: 54px;
  height: 28px;
  border-radius: 999px;
  background: linear-gradient(135deg, rgba(255, 119, 188, 0.55), rgba(198, 107, 231, 0.55));
  border: 1px solid rgba(255, 255, 255, 0.7);
  padding: 3px;
}

.toggle-thumb {
  position: absolute;
  top: 3px;
  left: 3px;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: white;
  color: #ff6bb7;
  font-size: 15px;
  display: grid;
  place-items: center;
  transition: transform 0.4s ease, color 0.4s ease, background 0.4s ease;
}

.theme-labels {
  display: flex;
  flex-direction: column;
  line-height: 1.1;
}

.theme-name {
  font-size: 0.9rem;
}

.theme-sub {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  opacity: 0.6;
}

body.book-new.dark-theme .theme-toggle {
  background: rgba(23, 18, 39, 0.75);
  border: 1px solid rgba(124, 58, 237, 0.35);
  color: #f4ddff;
  box-shadow: 0 14px 34px rgba(5, 2, 12, 0.6);
}

body.book-new.dark-theme .toggle-track {
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.6), rgba(18, 10, 56, 0.6));
  border: 1px solid rgba(124, 58, 237, 0.4);
}

body.book-new.dark-theme .toggle-thumb {
  transform: translateX(24px) rotate(360deg);
  background: #21163a;
  color: #ffd6ff;
}

.user-chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.55);
  font-weight: 600;
}

body.book-new.dark-theme .user-chip {
  background: rgba(23, 18, 39, 0.7);
  border: 1px solid rgba(124, 58, 237, 0.3);
  color: #f4e1ff;
}

.ghost-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 16px;
  border-radius: 999px;
  border: 1px solid rgba(79, 47, 100, 0.2);
  background: transparent;
  color: inherit;
  font-weight: 600;
  text-decoration: none;
  transition: background 0.25s ease, transform 0.25s ease;
}

.ghost-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-1px);
}

.nav-links {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.creator-grid {
  display: grid;
  gap: 18px;
  grid-template-columns: minmax(0, 0.85fr) minmax(0, 1.15fr);
  align-items: start;
}

body.book-new.dark-theme .writer-zone {
  background: rgba(18, 14, 34, 0.8);
  border: 1px solid rgba(124, 58, 237, 0.32);
}

.weapon-panel {
  display: grid;
  gap: 14px;
  margin-top: 16px;
}

.weapon-panel label {
  font-weight: 600;
  font-size: 0.95rem;
}

.weapon-panel input,
.weapon-panel textarea,
.weapon-panel select {
  width: 100%;
  border-radius: 12px;
  border: 1px solid rgba(125, 73, 148, 0.22);
  padding: 12px 14px;
  background: rgba(255, 255, 255, 0.78);
  color: inherit;
  font-size: 1rem;
  transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
}

.weapon-panel input:focus,
.weapon-panel textarea:focus,
.weapon-panel select:focus,
.writer-zone textarea:focus {
  outline: none;
  border-color: rgba(198, 107, 231, 0.6);
  box-shadow: 0 0 0 4px rgba(198, 107, 231, 0.18);
  background: rgba(255, 255, 255, 0.9);
}

body.book-new.dark-theme .weapon-panel input,
body.book-new.dark-theme .weapon-panel textarea,
body.book-new.dark-theme .weapon-panel select {
  background: rgba(18, 14, 36, 0.85);
  border: 1px solid rgba(124, 58, 237, 0.3);
}

.toolbar {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 12px;
}

.toolbar .btn {
  padding: 8px 12px;
  border-radius: 10px;
  border: none;
  background: linear-gradient(135deg, rgba(255, 134, 199, 0.85), rgba(198, 107, 231, 0.85));
  color: #fff;
  cursor: pointer;
  font-weight: 600;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.toolbar .btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 18px rgba(198, 107, 231, 0.35);
}

body.book-new.dark-theme .toolbar .btn {
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.8), rgba(255, 111, 181, 0.8));
}

.writing-textarea {
  font-family: 'Georgia', 'Times New Roman', serif;
  font-size: 16px;
  line-height: 1.8;
  padding: 16px;
  min-height: 420px;
  resize: vertical;
  background: rgba(255, 255, 255, 0.85);
  border: 1px solid rgba(125, 73, 148, 0.22);
  color: inherit;
  transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
}

body.book-new.dark-theme .writing-textarea {
  background: rgba(18, 14, 36, 0.85);
  border: 1px solid rgba(124, 58, 237, 0.32);
}

.stats-bar {
  display: flex;
  gap: 20px;
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid rgba(221, 160, 221, 0.2);
  font-size: 13px;
  color: #8b6b8d;
}

body.book-new.dark-theme .stats-bar {
  border-top: 1px solid rgba(124, 58, 237, 0.2);
  color: #d4b5d7;
}

.save-indicator {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.save-indicator.unsaved {
  background: rgba(255, 200, 124, 0.2);
  color: #d17a00;
}

body.book-new.dark-theme .save-indicator.unsaved {
  background: rgba(255, 200, 124, 0.15);
  color: #ffb870;
}

.save-indicator.saved {
  background: rgba(124, 221, 160, 0.2);
  color: #2d7a4d;
  animation: pulse-save 0.5s ease;
}

body.book-new.dark-theme .save-indicator.saved {
  background: rgba(124, 221, 160, 0.15);
  color: #7ce8a0;
}

@keyframes pulse-save {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.creator-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 20px;
}

.creator-footer button {
  padding: 12px 20px;
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #ff7fc7, #c56ae6);
  color: #fff;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 16px 30px rgba(197, 106, 230, 0.35);
  transition: transform 0.25s ease, box-shadow 0.3s ease;
}

.creator-footer button:hover {
  transform: translateY(-2px);
  box-shadow: 0 22px 42px rgba(197, 106, 230, 0.45);
}

body.book-new.dark-theme .creator-footer button {
  background: linear-gradient(135deg, #7c3aed, #ff6fb5);
}

.bottom-nav {
  display: flex;
  justify-content(cursor truncated
  center;
  gap: 20px;
  margin-top: 30px;
  flex-wrap: wrap;
}

.bottom-nav a {
  padding: 10px 20px;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 12px;
  font-size: 0.92rem;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(198, 107, 231, 0.18);
  color: #3f2851;
}

.bottom-nav a:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(198, 107, 231, 0.25);
}

body.book-new.dark-theme .bottom-nav a {
  background: rgba(23, 18, 39, 0.65);
  border: 1px solid rgba(124, 58, 237, 0.3);
  color: #f4ddff;
}

body.book-new.dark-theme .bottom-nav a:hover {
  box-shadow: 0 8px 20px rgba(124, 58, 237, 0.35);
}

.footer-note {
  text-align: center;
  font-size: 0.85rem;
  opacity: 0.7;
  margin-top: 8px;
}

@media (max-width: 1024px) {
  .creator-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  body.book-new {
    padding: 24px 16px 36px;
  }

  .glass-card {
    padding: 22px;
  }

  .header-actions {
    width: 100%;
    justify-content: space-between;
  }

  .theme-toggle {
    width: 100%;
    justify-content: center;
  }

  .user-chip, .nav-links {
    width: 100%;
    justify-content: center;
  }

  .creator-footer {
    flex-direction: column;
    gap: 12px;
    align-items: flex-start;
  }

  .creator-footer button {
    width: 100%;
    justify-content: center;
  }
}
</style>
</head>
<body class="book-new">
<div class="container">
  <div class="glass-card top-shell">
    <div class="brand-block">
      <a class="brand-link" href="<?= base_url('index.php') ?>">
        <span class="brand-icon">🌸</span>
        <span class="brand"><?= e(APP_NAME) ?></span>
      </a>
      <p class="brand-tagline">Yeni kitabını yazmaya pembe bir sayfadan başla; tek tuşla siyah moda geç.</p>
      <a class="ghost-btn" href="<?= base_url('books.php') ?>">← Kitaplara dön</a>
    </div>
    <div class="header-actions">
      <button class="theme-toggle" id="theme-toggle" type="button" aria-pressed="false">
        <span class="toggle-track">
          <span class="toggle-thumb" id="theme-thumb">🌸</span>
        </span>
        <span class="theme-labels">
          <span class="theme-name" id="theme-label">Pembe</span>
          <span class="theme-sub">Tema</span>
        </span>
      </button>
      <span class="user-chip">✍️ <?= e($user['username']) ?></span>
      <div class="nav-links">
        <a class="ghost-btn" href="<?= base_url('notes.php') ?>">📝 Notlar</a>
        <a class="ghost-btn" href="<?= base_url('designer_cover.php') ?>">🎨 Kapak</a>
        <a class="ghost-btn" href="<?= base_url('designer_map.php') ?>">🗺️ Harita</a>
        <a class="ghost-btn" href="<?= base_url('logout.php') ?>">Çıkış</a>
      </div>
    </div>
  </div>

  <div class="glass-card creator-grid">
    <div>
      <h2>✨ Yeni Kitap Oluştur</h2>
      <p>Başlığı seç, görünürlüğü ayarla ve hikâyeni yazmaya başla. Taslaklar tarayıcında saklanır, kaybolmaz.</p>
      <div class="weapon-panel">
        <form id="book-form" method="post" action="book_save.php">
          <?php csrf_field(); ?>
          <input type="hidden" name="book_id" value="">

          <label for="title-input">📖 Kitap Başlığı</label>
          <input name="title" id="title-input" required placeholder="Büyük macera burada başlar...">

          <label for="visibility">🔒 Görünürlük</label>
          <select name="visibility" id="visibility">
            <option value="private">Gizli (Sadece Ben)</option>
            <option value="unlisted">Liste Dışı (Link ile)</option>
            <option value="public">Herkese Açık</option>
          </select>

          <label for="description">📝 Kısa Açıklama</label>
          <textarea name="description" id="description" rows="2" placeholder="Okuyucular kitap kartında bu satırı görür"></textarea>

          <div class="creator-footer">
            <div class="save-indicator unsaved" id="save-badge">
              <span>💾</span>
              <span>Henüz kaydedilmedi</span>
            </div>
            <button type="submit">💾 Kaydet ve Devam Et</button>
          </div>
        </form>
      </div>
    </div>

    <div>
      <h2>🖋️ Hikâyeni Yaz</h2>
      <p style="font-size:0.88rem; opacity:0.75; margin-bottom:14px;">Kalemin ısınıyor. Tema düğmesi ile gece yazımlarında gözlerini dinlendir.</p>
      <div class="toolbar">
        <button type="button" data-cmd="bold" class="btn">B</button>
        <button type="button" data-cmd="italic" class="btn"><i>İ</i></button>
        <button type="button" data-cmd="underline" class="btn"><u>A</u></button>
        <button type="button" data-cmd="h1" class="btn">Başlık</button>
        <button type="button" data-cmd="ul" class="btn">Liste</button>
      </div>
      <div class="writer-zone">
        <textarea 
          name="content" 
          id="content-textarea" 
          form="book-form"
          class="writing-textarea" 
          placeholder="Bir zamanlar, uzak bir diyarda...&#10;&#10;Her büyük hikâye tek bir kelimeyle başlar. Şimdi sıra sende."></textarea>

        <div class="stats-bar">
          <div class="stat-item">
            <span class="stat-icon">📊</span>
            <span><strong id="char-count">0</strong> karakter</span>
          </div>
          <div class="stat-item">
            <span class="stat-icon">📚</span>
            <span><strong id="word-count">0</strong> kelime</span>
          </div>
          <div class="stat-item">
            <span class="stat-icon">📄</span>
            <span><strong id="page-count">0</strong> sayfa (yaklaşık)</span>
          </div>
          <div class="stat-item">
            <span class="stat-icon">⏱️</span>
            <span><strong id="read-time">0</strong> dk okuma</span>
          </div>
        </div>
      </div>

      <div class="inspiration-quote" id="inspiration-quote">
        "Bir kitap yazmak, içindeki dünyayı kâğıda dökmektir."
      </div>
    </div>
  </div>

  <div class="bottom-nav">
    <a href="<?= base_url('books.php') ?>">📚 Kitaplar</a>
    <a href="<?= base_url('notes.php') ?>">📝 Notlar</a>
    <a href="<?= base_url('designer_cover.php') ?>">🎨 Kapak</a>
    <a href="<?= base_url('designer_map.php') ?>">🗺️ Harita</a>
  </div>
  <div class="footer-note">© <?= date('Y') ?> <?= e(APP_NAME) ?> · Craftrolle kitap stüdyosu</div>
</div>

<script src="../assets/js/editor.js"></script>
<script>
(function() {
  const themeToggle = document.getElementById('theme-toggle');
  const themeThumb = document.getElementById('theme-thumb');
  const themeLabel = document.getElementById('theme-label');
  const storageKey = 'craft-book-new-theme';

  if (!themeToggle) { return; }

  const applyTheme = (mode) => {
    const isDark = mode === 'dark';
    document.body.classList.toggle('dark-theme', isDark);
    themeThumb.textContent = isDark ? '🌙' : '🌸';
    themeLabel.textContent = isDark ? 'Siyah' : 'Pembe';
    themeToggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
    localStorage.setItem(storageKey, mode);
  };

  const stored = localStorage.getItem(storageKey);
  applyTheme(stored === 'dark' ? 'dark' : 'light');

  themeToggle.addEventListener('click', () => {
    const nextMode = document.body.classList.contains('dark-theme') ? 'light' : 'dark';
    applyTheme(nextMode);
  });
})();

(function() {
  const focusBtn = document.getElementById('focus-mode-btn');
  focusBtn.addEventListener('click', function() {
    document.body.classList.toggle('focus-mode');
    if (document.body.classList.contains('focus-mode')) {
      focusBtn.textContent = '✖️';
      focusBtn.title = 'Normal Moda Dön';
    } else {
      focusBtn.textContent = '👁️';
      focusBtn.title = 'Odaklanma Modu';
    }
  });
})();

(function() {
  const textarea = document.getElementById('content-textarea');
  const charCount = document.getElementById('char-count');
  const wordCount = document.getElementById('word-count');
  const pageCount = document.getElementById('page-count');
  const readTime = document.getElementById('read-time');
  const saveBadge = document.getElementById('save-badge');
  const quotes = [
    '"Bir kitap yazmak, içindeki dünyayı kâğıda dökmektir."',
    '"Her cümle, yeni bir olasılığın kapısını aralar."',
    '"Yazmak, düşüncelere şekil vermektir."',
    '"Kelimeler, hayallerin mimarlığıdır."',
    '"Her yazar, kendi evreninin yaratıcısıdır."',
    '"Yazı, ruhun aynasıdır."',
    '"Hikâyeler, zamanın ötesine uzanır."',
    '"Yazmak cesarettir, paylaşmak ise özgürlük."',
    '"Her sayfa, yeni bir maceranın başlangıcıdır."',
    '"Kalem, hayal gücünün değneğidir."'
  ];

  const quoteEl = document.getElementById('inspiration-quote');
  setInterval(() => {
    const randomQuote = quotes[Math.floor(Math.random() * quotes.length)];
    quoteEl.style.opacity = '0';
    setTimeout(() => {
      quoteEl.textContent = randomQuote;
      quoteEl.style.opacity = '1';
    }, 300);
  }, 30000);

  const updateStats = () => {
    const text = textarea.value;
    const chars = text.length;
    const words = text.trim() ? text.trim().split(/\s+/).length : 0;
    const pages = Math.ceil(words / 250);
    const minutes = Math.ceil(words / 200);

    charCount.textContent = chars.toLocaleString('tr-TR');
    wordCount.textContent = words.toLocaleString('tr-TR');
    pageCount.textContent = pages.toLocaleString('tr-TR');
    readTime.textContent = minutes.toLocaleString('tr-TR');
  };

  textarea.addEventListener('input', () => {
    updateStats();
    saveBadge.className = 'save-indicator unsaved';
    saveBadge.innerHTML = '<span>⏳</span><span>Yazılıyor...</span>';
  });

  updateStats();

  document.getElementById('book-form').addEventListener('submit', () => {
    saveBadge.className = 'save-indicator saved';
    saveBadge.innerHTML = '<span>✅</span><span>Kaydediliyor...</span>';
  });
})();
</script>
</body>
</html>
