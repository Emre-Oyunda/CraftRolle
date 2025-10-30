<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_login();
$user = current_user();

// Get user's books
$st = db()->prepare("SELECT * FROM books WHERE user_id = ? AND is_deleted = 0 ORDER BY created_at DESC");
$st->execute([$user['id']]);
$books = $st->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kitaplarım - <?= e(APP_NAME) ?></title>
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
  body.dark-theme .btn {
    border:1px solid #2a2144;
    background: #161226;
    box-shadow: 
      0 2px 8px rgba(0, 0, 0, 0.3),
      inset 0 1px 1px rgba(124, 58, 237, 0.2);
  }
  
  body.dark-theme .btn:hover {
    box-shadow: 
      0 4px 12px rgba(124, 58, 237, 0.4),
      inset 0 1px 1px rgba(124, 58, 237, 0.3);
    border-color: #3a2a54;
  }
  
  /* SMALL TEXT */
  body.dark-theme .small {
    color: #d4b5d7;
  }
  
  /* H3 */
  body.dark-theme h3 {
    color: #ffd2f0;
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
  
  .theme-toggle-icon {
    font-size: 20px;
  }
  
  /* Responsive */
  @media (max-width: 768px) {
    .theme-toggle {
      top: 10px;
      right: 10px;
      padding: 10px 18px;
      font-size: 12px;
    }
  }
  </style>
</head>
<body>

<!-- Theme Toggle Button -->
<button class="theme-toggle" id="theme-toggle">
  <span class="theme-toggle-icon" id="theme-icon">🌸</span>
  <span id="theme-text">Pembe</span>
</button>

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
    localStorage.setItem('books-theme', 'dark');
  } else {
    icon.textContent = '🌸';
    text.textContent = 'Pembe';
    localStorage.setItem('books-theme', 'light');
  }
}

// Load saved theme
(function() {
  const savedTheme = localStorage.getItem('books-theme');
  if (savedTheme === 'dark') {
    document.body.classList.add('dark-theme');
    document.getElementById('theme-icon').textContent = '🌙';
    document.getElementById('theme-text').textContent = 'Siyah';
  }
})();

// Attach event
document.getElementById('theme-toggle').addEventListener('click', toggleTheme);
</script>

  <div class="container">
    <div class="card header">
      <div>
        <a class="btn" href="<?= base_url('index.php') ?>" style="text-decoration:none;">
          🌸 <span class="brand"><?= e(APP_NAME) ?></span>
        </a>
      </div>
      <div>
        <span class="badge">Merhaba, <?= e($user['username']) ?></span>
        · <a href="<?= base_url('dashboard.php') ?>">Panel</a>
        · <a href="<?= base_url('books.php') ?>">Kitaplarım</a>
        · <a href="<?= base_url('notes.php') ?>">Notlarım</a>
        · <a href="<?= base_url('eglence.php') ?>">Eğlence</a>
        · <a href="<?= base_url('designer_cover.php') ?>">Kapak</a>
        · <a href="<?= base_url('designer_map.php') ?>">Harita</a>
        · <a href="<?= base_url('logout.php') ?>">Çıkış</a>
      </div>
    </div>

    <div class="card">
      <h2>📚 Kitaplarım</h2>
      <a href="<?= base_url('book_new.php') ?>" class="btn">+ Yeni Kitap Oluştur</a>
      
      <div style="margin-top: 30px;">
        <?php if (empty($books)): ?>
          <p>Henüz kitabınız yok. Hemen bir tane oluşturun!</p>
        <?php else: ?>
          <?php foreach ($books as $book): ?>
            <div class="card" style="margin: 15px 0;">
              <h3><?= e($book['title']) ?></h3>
              <div class="small">
                Oluşturulma: <?= date('d.m.Y H:i', strtotime($book['created_at'])) ?>
                · Görünürlük: <?= e($book['visibility']) ?>
              </div>
              <?php if ($book['cover_path']): ?>
                <img src="<?= base_url('../' . ltrim($book['cover_path'], '/')) ?>" 
                     style="max-width:150px; border-radius:8px; margin:10px 0;">
              <?php endif; ?>
              <div style="margin-top: 10px;">
                <a href="<?= base_url('view_book.php?id=' . $book['id']) ?>" class="btn">
                  📖 3D Görüntüle
                </a>
                <a href="<?= base_url('book_edit.php?id=' . $book['id']) ?>" class="btn">
                  ✏️ Düzenle
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="small" style="text-align:center; margin-top:12px;">
      © <?= date('Y') ?> <?= e(APP_NAME) ?>
    </div>
  </div>
</body>
</html>
