<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
$user = current_user();

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare("SELECT b.*, u.username FROM books b JOIN users u ON u.id=b.user_id WHERE b.id=? AND b.is_deleted=0");
$st->execute([$id]);
$book = $st->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    http_response_code(404);
    die('Kitap bulunamadı');
}

if ($book['visibility'] === 'private' && (empty($_SESSION['user_id']) || (int)$_SESSION['user_id'] !== (int)$book['user_id'])) {
    http_response_code(403);
    die('Bu kitap gizli.');
}

// Prepare book pages
$content = $book['content'] ?: 'Bu kitap henüz içerik içermiyor.';
$chunkSize = 1500;
$pages = [];

if (strlen($content) > 0) {
    $chunks = str_split($content, $chunkSize);
    foreach ($chunks as $chunk) {
        $pages[] = $chunk;
    }
}

// Ensure even number of pages
if (count($pages) % 2 !== 0) {
    $pages[] = '';
}
$totalPages = count($pages);
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(APP_NAME) ?> - 3D Kitap Görüntüleyici</title>
<style>
:root {
  --primary: #ff6b9d;
  --secondary: #c44569;
  --bg: #fef5f1;
  --card-bg: #fff;
  --text: #2d3436;
  --border: #dfe6e9;
  --shadow: rgba(0,0,0,0.1);
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: linear-gradient(135deg, #fef5f1 0%, #ffe8e0 100%);
  color: var(--text);
  min-height: 100vh;
  padding: 20px;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
}

.card {
  background: var(--card-bg);
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 4px 12px var(--shadow);
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
}

.brand {
  font-size: 1.5em;
  font-weight: bold;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.btn {
  display: inline-block;
  padding: 10px 20px;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  text-decoration: none;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  transition: transform 0.2s;
  font-size: 14px;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 107, 157, 0.4);
}

.badge {
  background: linear-gradient(135deg, #ff6b9d20, #c4456920);
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
}

.small {
  font-size: 0.9em;
  color: #636e72;
  margin: 10px 0;
}

a {
  color: var(--primary);
  text-decoration: none;
  transition: color 0.2s;
}

a:hover {
  color: var(--secondary);
}

h2 {
  color: var(--text);
  margin-bottom: 15px;
}

/* ========== 3D BOOK VIEWER STYLES ========== */

.book-viewer-container {
  perspective: 2000px;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 600px;
  padding: 40px 20px;
  position: relative;
  margin: 30px 0;
}

.book-3d {
  position: relative;
  width: 800px;
  height: 500px;
  transform-style: preserve-3d;
  transition: transform 0.6s ease;
}

.book-spine {
  position: absolute;
  left: 50%;
  top: 0;
  width: 20px;
  height: 100%;
  background: linear-gradient(to right, #8b4513, #a0522d, #8b4513);
  transform: translateX(-50%);
  box-shadow: 
    inset 2px 0 4px rgba(0,0,0,0.3),
    inset -2px 0 4px rgba(0,0,0,0.3);
  z-index: 100;
}

.book-page {
  position: absolute;
  width: 380px;
  height: 500px;
  background: linear-gradient(to right, #fefefe 0%, #f8f8f8 50%, #fefefe 100%);
  border: 1px solid #ddd;
  border-radius: 0 8px 8px 0;
  padding: 40px 30px;
  box-shadow: 
    0 8px 16px rgba(0,0,0,0.2),
    inset -2px 0 8px rgba(0,0,0,0.05);
  transform-style: preserve-3d;
  transform-origin: left center;
  transition: transform 0.8s cubic-bezier(0.645, 0.045, 0.355, 1);
  overflow: hidden;
  backface-visibility: hidden;
}

.book-page.left-page {
  left: 10px;
  transform-origin: right center;
  border-radius: 8px 0 0 8px;
  background: linear-gradient(to left, #fefefe 0%, #f8f8f8 50%, #fefefe 100%);
  box-shadow: 
    0 8px 16px rgba(0,0,0,0.2),
    inset 2px 0 8px rgba(0,0,0,0.05);
}

.book-page.right-page {
  right: 10px;
  transform-origin: left center;
}

.book-page.flipped {
  transform: rotateY(-175deg);
  z-index: 200 !important;
}

.book-page.left-page.flipped {
  transform: rotateY(175deg);
}

.page-content {
  position: relative;
  height: 100%;
  overflow-y: auto;
  overflow-x: hidden;
  font-size: 15px;
  line-height: 1.8;
  color: #2d3436;
  text-align: justify;
  scrollbar-width: thin;
  scrollbar-color: #ddd transparent;
}

.page-content::-webkit-scrollbar {
  width: 6px;
}

.page-content::-webkit-scrollbar-track {
  background: transparent;
}

.page-content::-webkit-scrollbar-thumb {
  background: #ddd;
  border-radius: 3px;
}

.page-content h1 {
  font-size: 24px;
  color: var(--primary);
  margin-bottom: 20px;
  text-align: center;
  font-weight: 600;
}

.page-content p {
  margin-bottom: 15px;
}

.page-number {
  position: absolute;
  bottom: 15px;
  font-size: 12px;
  color: #999;
  font-style: italic;
}

.left-page .page-number {
  left: 30px;
}

.right-page .page-number {
  right: 30px;
}

.book-page::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: 
    repeating-linear-gradient(
      0deg,
      transparent,
      transparent 2px,
      rgba(0,0,0,0.01) 2px,
      rgba(0,0,0,0.01) 4px
    );
  pointer-events: none;
  opacity: 0.3;
}

.viewer-controls {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  margin-top: 30px;
  flex-wrap: wrap;
}

.viewer-controls button {
  padding: 12px 30px;
  font-size: 16px;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s;
  font-weight: 500;
  box-shadow: 0 4px 12px rgba(255, 107, 157, 0.3);
}

.viewer-controls button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(255, 107, 157, 0.5);
}

.viewer-controls button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-indicator {
  padding: 8px 16px;
  background: rgba(255, 107, 157, 0.1);
  border-radius: 20px;
  font-size: 14px;
  color: var(--text);
  font-weight: 500;
}

.bottom-nav {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 30px;
  flex-wrap: wrap;
}

.bottom-nav a {
  padding: 10px 20px;
  background: white;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.3s;
  box-shadow: 0 2px 8px var(--shadow);
}

.bottom-nav a:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px var(--shadow);
}

@media (max-width: 900px) {
  .book-3d {
    width: 600px;
    height: 400px;
  }
  
  .book-page {
    width: 280px;
    height: 400px;
    padding: 30px 20px;
  }
  
  .page-content {
    font-size: 14px;
  }
}

@media (max-width: 650px) {
  .book-3d {
    width: 400px;
    height: 300px;
  }
  
  .book-page {
    width: 185px;
    height: 300px;
    padding: 20px 15px;
  }
  
  .page-content {
    font-size: 12px;
  }
  
  .page-content h1 {
    font-size: 18px;
  }
  
  .book-spine {
    width: 15px;
  }
}
</style>
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

  <div class="card">
    <h2><?= e($book['title']) ?></h2>
    <div class="small">
      Yazar: <?= e($book['username']) ?> · Görünürlük: <?= e($book['visibility']) ?>
    </div>
    
    <?php if($book['cover_path']): ?>
      <img src="<?= base_url('../'.ltrim($book['cover_path'],'/')) ?>" 
           style="max-width:200px; border-radius:12px; border:1px solid var(--border); margin:15px 0; display:block;">
    <?php endif; ?>

    <!-- 3D Book Viewer -->
    <div class="book-viewer-container">
      <div class="book-3d" id="book3d">
        <div class="book-spine"></div>
        
        <?php 
        for ($i = 0; $i < $totalPages; $i += 2) {
          $leftPageNum = $i;
          $rightPageNum = $i + 1;
          $leftContent = $pages[$leftPageNum] ?? '';
          $rightContent = $pages[$rightPageNum] ?? '';
          $zIndex = 1000 - $i;
        ?>
          
          <!-- Left Page -->
          <div class="book-page left-page" data-page="<?= $leftPageNum ?>" style="z-index: <?= $zIndex ?>;">
            <div class="page-content">
              <?php if ($leftPageNum === 0): ?>
                <h1><?= e($book['title']) ?></h1>
              <?php endif; ?>
              <p><?= nl2br(e($leftContent)) ?></p>
            </div>
            <div class="page-number"><?= $leftPageNum + 1 ?></div>
          </div>
          
          <!-- Right Page -->
          <div class="book-page right-page" data-page="<?= $rightPageNum ?>" style="z-index: <?= $zIndex - 1 ?>;">
            <div class="page-content">
              <p><?= nl2br(e($rightContent)) ?></p>
            </div>
            <div class="page-number"><?= $rightPageNum + 1 ?></div>
          </div>
          
        <?php } ?>
      </div>
    </div>

    <!-- Viewer Controls -->
    <div class="viewer-controls">
      <button onclick="book3D.prevPage()" id="prevBtn">◀ Önceki Sayfa</button>
      <div class="page-indicator">
        <span id="currentPage">1</span> - <span id="currentPageNext">2</span> / <?= $totalPages ?>
      </div>
      <button onclick="book3D.nextPage()" id="nextBtn">Sonraki Sayfa ▶</button>
      <a class="btn" href="<?= base_url('export_print.php?id='.(int)$book['id']) ?>">📄 Yazdır/PDF</a>
    </div>
  </div>

  <div class="bottom-nav">
    <a href="<?= base_url('books.php') ?>">📚 Kitaplar</a>
    <a href="<?= base_url('notes.php') ?>">📝 Notlar</a>
    <a href="<?= base_url('designer_cover.php') ?>">🎨 Kapak</a>
    <a href="<?= base_url('designer_map.php') ?>">🗺️ Harita</a>
  </div>
  
  <div class="small" style="text-align:center;margin-top:12px;">
    © <?= date('Y') ?> <?= e(APP_NAME) ?>
  </div>
</div>

<!-- 3D Book Viewer JavaScript -->
<script>
class Book3DViewer {
  constructor() {
    this.currentSpread = 0;
    this.pages = Array.from(document.querySelectorAll('.book-page'));
    this.totalSpreads = this.pages.length / 2;
    this.prevBtn = document.getElementById('prevBtn');
    this.nextBtn = document.getElementById('nextBtn');
    this.currentPageDisplay = document.getElementById('currentPage');
    this.currentPageNextDisplay = document.getElementById('currentPageNext');
    
    this.init();
  }

  init() {
    this.render();
    this.updateControls();
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') {
        this.prevPage();
      } else if (e.key === 'ArrowRight') {
        this.nextPage();
      }
    });

    // Touch/swipe support
    let touchStartX = 0;
    let touchEndX = 0;
    
    const bookContainer = document.querySelector('.book-viewer-container');
    
    bookContainer.addEventListener('touchstart', (e) => {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
    
    bookContainer.addEventListener('touchend', (e) => {
      touchEndX = e.changedTouches[0].screenX;
      this.handleSwipe();
    }, { passive: true });
    
    this.handleSwipe = () => {
      if (touchEndX < touchStartX - 50) {
        this.nextPage();
      }
      if (touchEndX > touchStartX + 50) {
        this.prevPage();
      }
    };
  }

  render() {
    this.pages.forEach((page, index) => {
      const spreadIndex = Math.floor(index / 2);
      
      page.classList.remove('flipped');
      
      if (spreadIndex < this.currentSpread) {
        page.classList.add('flipped');
      }
      
      const baseZIndex = 1000 - spreadIndex * 2;
      if (spreadIndex < this.currentSpread) {
        page.style.zIndex = baseZIndex + 2000;
      } else if (spreadIndex === this.currentSpread) {
        page.style.zIndex = baseZIndex + 1000;
      } else {
        page.style.zIndex = baseZIndex;
      }
    });
  }

  nextPage() {
    if (this.currentSpread < this.totalSpreads - 1) {
      this.currentSpread++;
      this.render();
      this.updateControls();
    }
  }

  prevPage() {
    if (this.currentSpread > 0) {
      this.currentSpread--;
      this.render();
      this.updateControls();
    }
  }

  updateControls() {
    this.prevBtn.disabled = this.currentSpread === 0;
    this.nextBtn.disabled = this.currentSpread >= this.totalSpreads - 1;
    
    const leftPageNum = this.currentSpread * 2 + 1;
    const rightPageNum = this.currentSpread * 2 + 2;
    
    this.currentPageDisplay.textContent = leftPageNum;
    this.currentPageNextDisplay.textContent = rightPageNum;
  }
}

// Initialize the book viewer
const book3D = new Book3DViewer();

// Mouse move effect
document.querySelector('.book-viewer-container')?.addEventListener('mousemove', (e) => {
  const container = e.currentTarget;
  const book = document.getElementById('book3d');
  
  if (!book) return;
  
  const rect = container.getBoundingClientRect();
  const x = e.clientX - rect.left;
  const y = e.clientY - rect.top;
  
  const centerX = rect.width / 2;
  const centerY = rect.height / 2;
  
  const rotateX = (y - centerY) / 50;
  const rotateY = (x - centerX) / 50;
  
  book.style.transform = `rotateX(${-rotateX}deg) rotateY(${rotateY}deg)`;
});

// Reset rotation
document.querySelector('.book-viewer-container')?.addEventListener('mouseleave', () => {
  const book = document.getElementById('book3d');
  if (book) {
    book.style.transform = 'rotateX(0deg) rotateY(0deg)';
  }
});
</script>
</body>
</html>
