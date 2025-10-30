<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
$user = current_user();
?>
<!doctype html>
<html lang="tr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kitap Düzenle - <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
/* === DUAL THEME - Pembe & Siyah === */

/* PEMBE TEMA (Default) */
body {
  background: linear-gradient(135deg, #fef5ff 0%, #fff0f9 25%, #f8f0ff 50%, #fff5fb 75%, #fef5ff 100%);
  color:#5a3d5c;
  transition: all 0.5s ease;
}

body::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: 
    radial-gradient(circle at 20% 50%, rgba(255, 182, 193, 0.15) 0%, transparent 50%),
    radial-gradient(circle at 80% 80%, rgba(221, 160, 221, 0.12) 0%, transparent 50%),
    radial-gradient(circle at 40% 20%, rgba(255, 192, 203, 0.1) 0%, transparent 50%);
  pointer-events: none;
  z-index: 0;
  transition: all 0.5s ease;
}

/* SİYAH TEMA */
body.dark-theme {
  background: #0e0b1a;
  color:#f5e8ff;
}

body.dark-theme::before {
  opacity: 0;
}

.container {
  position: relative;
  z-index: 1;
}

/* KARTLAR */
body.dark-theme .card {
  border:1px solid #2a2144;
  background: rgba(255,255,255,.04);
  box-shadow: 
    0 4px 16px rgba(0, 0, 0, 0.3),
    inset 0 0 20px rgba(124, 58, 237, 0.05);
}

body.dark-theme .card:hover {
  box-shadow: 
    0 8px 24px rgba(124, 58, 237, 0.25),
    inset 0 0 30px rgba(124, 58, 237, 0.1);
  border-color: #3a2a54;
}

/* BUTONLAR */
body.dark-theme .btn,
body.dark-theme button {
  border:1px solid #2a2144;
  background: #161226;
  color: #ffd2f0;
  box-shadow: 
    0 2px 8px rgba(0, 0, 0, 0.3),
    inset 0 1px 1px rgba(124, 58, 237, 0.2);
}

body.dark-theme .btn:hover,
body.dark-theme button:hover {
  box-shadow: 
    0 4px 12px rgba(124, 58, 237, 0.4),
    inset 0 1px 1px rgba(124, 58, 237, 0.3);
  border-color: #3a2a54;
}

/* INPUT & TEXTAREA */
body.dark-theme input,
body.dark-theme textarea,
body.dark-theme select {
  background: rgba(22, 18, 38, 0.6);
  border: 1px solid #2a2144;
  color: #f5e8ff;
}

body.dark-theme input:focus,
body.dark-theme textarea:focus,
body.dark-theme select:focus {
  border-color: #7c3aed;
  box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
}

body.dark-theme input::placeholder,
body.dark-theme textarea::placeholder {
  color: rgba(245, 232, 255, 0.4);
}

/* SMALL TEXT */
body.dark-theme .small {
  color: #d4b5d7;
}

/* LABELS */
body.dark-theme label {
  color: #f5b6e8;
}

/* LINKS */
body.dark-theme a {
  color: #f5b6e8;
}

body.dark-theme a:hover {
  color: #ff69b4;
}

/* BADGE */
body.dark-theme .badge {
  background: rgba(124, 58, 237, 0.2);
  color: #ffd2f0;
}

/* BRAND */
body.dark-theme .brand {
  background: linear-gradient(135deg, #ff69b4, #ba55d3);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* === WRITER ENHANCEMENTS === */

.writer-zone {
  margin: 20px 0;
  padding: 24px;
  background: rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  border: 1px solid rgba(221, 160, 221, 0.3);
  transition: all 0.3s ease;
}

body.dark-theme .writer-zone {
  background: rgba(22, 18, 38, 0.5);
  border: 1px solid #2a2144;
}

.writer-zone:focus-within {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(221, 160, 221, 0.3);
}

body.dark-theme .writer-zone:focus-within {
  box-shadow: 0 8px 24px rgba(124, 58, 237, 0.3);
  border-color: #3a2a54;
}

.writing-textarea {
  font-family: 'Georgia', 'Times New Roman', serif;
  font-size: 16px;
  line-height: 1.8;
  padding: 16px;
  min-height: 400px;
  resize: vertical;
  transition: all 0.3s ease;
}

.writing-textarea:focus {
  outline: none;
  border-color: #dda0dd;
  box-shadow: 0 0 0 4px rgba(221, 160, 221, 0.15);
}

body.dark-theme .writing-textarea:focus {
  border-color: #7c3aed;
  box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.15);
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

body.dark-theme .stats-bar {
  border-top: 1px solid rgba(124, 58, 237, 0.2);
  color: #d4b5d7;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 6px;
}

.stat-icon {
  font-size: 16px;
}

.inspiration-quote {
  text-align: center;
  font-style: italic;
  color: #a97da9;
  margin: 16px 0;
  padding: 12px;
  background: rgba(221, 160, 221, 0.1);
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.3s ease;
}

body.dark-theme .inspiration-quote {
  color: #d4b5d7;
  background: rgba(124, 58, 237, 0.1);
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

body.dark-theme .save-indicator.unsaved {
  background: rgba(255, 200, 124, 0.15);
  color: #ffb870;
}

.save-indicator.saved {
  background: rgba(124, 221, 160, 0.2);
  color: #2d7a4d;
  animation: pulse-save 0.5s ease;
}

body.dark-theme .save-indicator.saved {
  background: rgba(124, 221, 160, 0.15);
  color: #7ce8a0;
}

@keyframes pulse-save {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.focus-mode-btn {
  position: fixed;
  bottom: 80px;
  right: 20px;
  z-index: 999;
  background: linear-gradient(135deg, #dda0dd 0%, #d8a0d8 100%);
  border: none;
  border-radius: 50%;
  width: 56px;
  height: 56px;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(221, 160, 221, 0.4);
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
}

body.dark-theme .focus-mode-btn {
  background: #161226;
  box-shadow: 0 4px 15px rgba(124, 58, 237, 0.4);
}

.focus-mode-btn:hover {
  transform: scale(1.1) rotate(5deg);
  box-shadow: 0 6px 20px rgba(221, 160, 221, 0.6);
}

body.dark-theme .focus-mode-btn:hover {
  box-shadow: 0 6px 20px rgba(124, 58, 237, 0.6);
}

/* Focus Mode */
body.focus-mode .card.header,
body.focus-mode .bottom-nav,
body.focus-mode .theme-toggle,
body.focus-mode h2,
body.focus-mode .grid,
body.focus-mode .inspiration-quote,
body.focus-mode .cover-section,
body.focus-mode .action-buttons {
  opacity: 0.2;
  pointer-events: none;
  transition: opacity 0.5s ease;
}

body.focus-mode .writer-zone {
  transform: scale(1.02);
  box-shadow: 0 12px 40px rgba(221, 160, 221, 0.4);
}

body.focus-mode.dark-theme .writer-zone {
  box-shadow: 0 12px 40px rgba(124, 58, 237, 0.4);
}

/* Cover Section */
.cover-section {
  margin-top: 30px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(5px);
  border-radius: 12px;
  border: 1px solid rgba(221, 160, 221, 0.2);
}

body.dark-theme .cover-section {
  background: rgba(22, 18, 38, 0.3);
  border: 1px solid #2a2144;
}

.cover-preview {
  max-width: 100%;
  max-height: 300px;
  border-radius: 12px;
  border: 1px solid rgba(221, 160, 221, 0.3);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

body.dark-theme .cover-preview {
  border: 1px solid #2a2144;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

.cover-preview:hover {
  transform: scale(1.02);
  box-shadow: 0 6px 20px rgba(221, 160, 221, 0.3);
}

body.dark-theme .cover-preview:hover {
  box-shadow: 0 6px 20px rgba(124, 58, 237, 0.3);
}

.action-buttons {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-top: 20px;
}

/* === THEME TOGGLE BUTTON === */
.theme-toggle {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1000;
  background: linear-gradient(135deg, #dda0dd 0%, #d8a0d8 100%);
  border: 1px solid rgba(255, 182, 193, 0.5);
  border-radius: 50px;
  padding: 12px 24px;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(221, 160, 221, 0.3);
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
  color: white;
  font-size: 14px;
}

body.dark-theme .theme-toggle {
  background: #161226;
  border: 1px solid #2a2144;
  box-shadow: 
    0 4px 15px rgba(0, 0, 0, 0.4),
    inset 0 0 20px rgba(124, 58, 237, 0.15);
}

.theme-toggle:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(221, 160, 221, 0.5);
}

body.dark-theme .theme-toggle:hover {
  box-shadow: 
    0 6px 20px rgba(124, 58, 237, 0.4),
    inset 0 0 30px rgba(124, 58, 237, 0.25);
  border-color: #3a2a54;
}

/* Responsive */
@media (max-width: 768px) {
  .theme-toggle {
    top: 10px;
    right: 10px;
    padding: 10px 18px;
    font-size: 12px;
  }
  
  .focus-mode-btn {
    bottom: 70px;
    right: 10px;
    width: 48px;
    height: 48px;
    font-size: 20px;
  }
  
  .stats-bar {
    flex-wrap: wrap;
    gap: 12px;
  }
  
  .action-buttons {
    gap: 8px;
  }
}
</style>
</head><body><div class="container">

<!-- Theme Toggle Button -->
<button class="theme-toggle" id="theme-toggle" type="button">
  <span class="theme-toggle-icon" id="theme-icon">🌸</span>
  <span id="theme-text">Pembe</span>
</button>

<!-- Focus Mode Button -->
<button class="focus-mode-btn" id="focus-mode-btn" type="button" title="Odaklanma Modu">
  👁️
</button>

  <div class="card header">
    <div><a class="btn" href="<?= base_url('index.php') ?>" style="text-decoration:none;">🌸 <span class="brand"><?= e(APP_NAME) ?></span></a></div>
    <div>
      <?php if($user): ?>
        <span class="badge">Merhaba, <?= e($user['username']) ?></span>
        · <a href="<?= base_url('dashboard.php') ?>">Panel</a>
        · <a href="<?= base_url('books.php') ?>">Kitaplarım</a>
        · <a href="<?= base_url('notes.php') ?>">Notlarım</a>
        · <a href="<?= base_url('eglence.php') ?>">Eğlence</a>
        · <a href="<?= base_url('designer_cover.php') ?>">Kapak</a>
        · <a href="<?= base_url('designer_map.php') ?>">Harita</a>
        · <a href="<?= base_url('logout.php') ?>">Çıkış</a>
      <?php else: ?>
        <a href="<?= base_url('login.php') ?>">Giriş</a> · <a href="<?= base_url('register.php') ?>">Kayıt Ol</a>
      <?php endif; ?>
    </div>
  </div>

<?php require_login(); csrf_check();
$id=(int)($_GET['id']??0);
$st=db()->prepare("SELECT * FROM books WHERE id=? AND user_id=? AND is_deleted=0");
$st->execute([$id,$_SESSION['user_id']]); $book=$st->fetch(PDO::FETCH_ASSOC);
if(!$book){ http_response_code(404); die('Kitap bulunamadı'); }
?>
<div class="card">
  <h2>✏️ Kitap Düzenle</h2>
  
  <div class="inspiration-quote" id="inspiration-quote">
    "Düzenlemek, yazmak kadar yaratıcı bir süreçtir."
  </div>
  
  <form id="book-form" method="post" action="book_save.php">
    <?php csrf_field(); ?>
    <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
    
    <div class="grid g-2">
      <div>
        <label>📖 Kitap Başlığı</label>
        <input name="title" id="title-input" value="<?= e($book['title']) ?>" required>
      </div>
      <div>
        <label>🔒 Görünürlük</label>
        <select name="visibility">
          <option value="private" <?= $book['visibility']==='private'?'selected':'' ?>>Gizli (Sadece Ben)</option>
          <option value="unlisted" <?= $book['visibility']==='unlisted'?'selected':'' ?>>Liste Dışı (Link ile)</option>
          <option value="public" <?= $book['visibility']==='public'?'selected':'' ?>>Herkese Açık</option>
        </select>
      </div>
    </div>
    
    <label>📝 Kısa Açıklama</label>
    <textarea name="description" rows="2" placeholder="Kitabınızın özeti"><?= e($book['description']) ?></textarea>
    
    <label>✨ Kitabınızı Düzenleyin</label>
    <div class="writer-zone">
      <textarea 
        name="content" 
        id="content-textarea" 
        class="writing-textarea"><?= e($book['content']) ?></textarea>
      
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
    
    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 20px;">
      <div class="save-indicator saved" id="save-badge">
        <span>✅</span>
        <span>Son kaydetme: <?= e(date('H:i', strtotime($book['updated_at']))) ?></span>
      </div>
      <button type="submit">💾 Kaydet</button>
    </div>
  </form>
  
  <div class="cover-section">
    <h3>🎨 Kitap Kapağı</h3>
    <?php if($book['cover_path']): ?>
      <img src="<?= base_url('../'.ltrim($book['cover_path'],'/')) ?>" class="cover-preview" alt="Kitap Kapağı">
    <?php else: ?>
      <div class="small" style="padding: 20px; text-align: center; opacity: 0.7;">
        📭 Henüz kapak yüklenmemiş
      </div>
    <?php endif; ?>
    
    <form method="post" enctype="multipart/form-data" action="upload_cover_file.php" style="margin-top: 16px;">
      <?php csrf_field(); ?>
      <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
      <label>📤 Yeni Kapak Yükle</label>
      <input type="file" name="cover" accept="image/*">
      <button type="submit">📥 Kapak Yükle</button>
    </form>
    
    <div class="small" style="margin-top: 12px;">
      💡 <strong>İpucu:</strong> <a href="designer_cover.php">Kapak Tasarım</a> aracıyla şablon seçip PNG olarak kaydedin, sonra buraya yükleyin.
    </div>
  </div>
  
  <div class="action-buttons">
    <a class="btn" href="<?= base_url('view_book.php?id='.(int)$book['id']) ?>">
      📖 3D Görüntüle
    </a>
    <a class="btn" href="<?= base_url('export_print.php?id='.(int)$book['id']) ?>">
      📄 Yazdır/PDF
    </a>
    <a class="btn" href="<?= base_url('books.php') ?>">
      📚 Tüm Kitaplar
    </a>
  </div>
</div>
<script src="../assets/js/editor.js"></script>

  <div class="bottom-nav">
    <a href="<?= base_url('books.php') ?>">📚 Kitaplar</a>
    <a href="<?= base_url('notes.php') ?>">📝 Notlar</a>
    <a href="<?= base_url('designer_cover.php') ?>">🎨 Kapak</a>
    <a href="<?= base_url('designer_map.php') ?>">🗺️ Harita</a>
  </div>
  <div class="small" style="text-align:center;margin-top:12px;">© <?= date('Y') ?> <?= e(APP_NAME) ?></div>
</div>

<script>
// === THEME SWITCHER ===
function toggleTheme() {
  const body = document.body;
  const icon = document.getElementById('theme-icon');
  const text = document.getElementById('theme-text');
  
  body.classList.toggle('dark-theme');
  
  if (body.classList.contains('dark-theme')) {
    icon.textContent = '🌙';
    text.textContent = 'Siyah';
    localStorage.setItem('book-edit-theme', 'dark');
  } else {
    icon.textContent = '🌸';
    text.textContent = 'Pembe';
    localStorage.setItem('book-edit-theme', 'light');
  }
}

// Load saved theme
(function() {
  const savedTheme = localStorage.getItem('book-edit-theme');
  if (savedTheme === 'dark') {
    document.body.classList.add('dark-theme');
    document.getElementById('theme-icon').textContent = '🌙';
    document.getElementById('theme-text').textContent = 'Siyah';
  }
})();

document.getElementById('theme-toggle').addEventListener('click', toggleTheme);

// === FOCUS MODE ===
document.getElementById('focus-mode-btn').addEventListener('click', function() {
  document.body.classList.toggle('focus-mode');
  const icon = this;
  if (document.body.classList.contains('focus-mode')) {
    icon.textContent = '✖️';
    icon.title = 'Normal Moda Dön';
  } else {
    icon.textContent = '👁️';
    icon.title = 'Odaklanma Modu';
  }
});

// === WRITING STATS ===
const contentTextarea = document.getElementById('content-textarea');
const charCount = document.getElementById('char-count');
const wordCount = document.getElementById('word-count');
const pageCount = document.getElementById('page-count');
const readTime = document.getElementById('read-time');

function updateStats() {
  const text = contentTextarea.value;
  const chars = text.length;
  const words = text.trim() ? text.trim().split(/\s+/).length : 0;
  const pages = Math.ceil(words / 250); // 250 kelime/sayfa
  const minutes = Math.ceil(words / 200); // 200 kelime/dakika okuma hızı
  
  charCount.textContent = chars.toLocaleString('tr-TR');
  wordCount.textContent = words.toLocaleString('tr-TR');
  pageCount.textContent = pages.toLocaleString('tr-TR');
  readTime.textContent = minutes.toLocaleString('tr-TR');
}

contentTextarea.addEventListener('input', updateStats);
updateStats();

// === INSPIRATION QUOTES ===
const quotes = [
  '"Düzenlemek, yazmak kadar yaratıcı bir süreçtir."',
  '"Her düzenleme, hikâyeyi daha güçlü kılar."',
  '"Mükemmellik düzenlemelerde gizlidir."',
  '"Yazmak cesaret, düzenlemek ise sanattır."',
  '"Her okuma, yeni bir fırsat sunar."',
  '"Kelimeler, düzenlemeyle parlar."',
  '"Hikâyeniz, her dokunuşla olgunlaşır."',
  '"Sabır ve düzenleme, başyapıt yaratır."',
  '"Yazdıklarınıza ikinci bir şans verin."',
  '"Düzenlemek, hikâyenize saygıdır."'
];

function changeQuote() {
  const quoteEl = document.getElementById('inspiration-quote');
  const randomQuote = quotes[Math.floor(Math.random() * quotes.length)];
  quoteEl.style.opacity = '0';
  setTimeout(() => {
    quoteEl.textContent = randomQuote;
    quoteEl.style.opacity = '1';
  }, 300);
}

// Change quote every 30 seconds
setInterval(changeQuote, 30000);

// === AUTO-SAVE INDICATOR (Simulated) ===
let saveTimeout;
const saveBadge = document.getElementById('save-badge');
const originalContent = contentTextarea.value;

contentTextarea.addEventListener('input', function() {
  saveBadge.className = 'save-indicator unsaved';
  saveBadge.innerHTML = '<span>⏳</span><span>Düzenleniyor...</span>';
  
  clearTimeout(saveTimeout);
  saveTimeout = setTimeout(() => {
    // Simulate auto-save
    saveBadge.className = 'save-indicator saved';
    saveBadge.innerHTML = '<span>✅</span><span>Otomatik kaydedildi</span>';
    
    setTimeout(() => {
      saveBadge.className = 'save-indicator unsaved';
      saveBadge.innerHTML = '<span>💾</span><span>Değişiklikler kaydedildi</span>';
    }, 2000);
  }, 3000);
});

// === FORM SUBMIT ===
document.getElementById('book-form').addEventListener('submit', function() {
  saveBadge.className = 'save-indicator saved';
  saveBadge.innerHTML = '<span>✅</span><span>Kaydediliyor...</span>';
});
</script>

</body></html>
