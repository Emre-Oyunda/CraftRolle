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
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(APP_NAME) ?> - 3D Kitap Görüntüleyici</title>
  <link rel="stylesheet" href="../assets/css/style.css">
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
          <a href="<?= base_url('login.php') ?>">Giriş</a> 
          · <a href="<?= base_url('register.php') ?>">Kayıt Ol</a>
        <?php endif; ?>
      </div>
    </div>

<?php
$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare("SELECT b.*, u.username FROM books b JOIN users u ON u.id=b.user_id WHERE b.id=? AND b.is_deleted=0");
$st->execute([$id]);
$book = $st->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    http_response_code(404);
    die('<div class="card"><h2>Kitap bulunamadı</h2><p><a href="' . base_url('books.php') . '">Kitaplara dön</a></p></div></div></body></html>');
}

if ($book['visibility'] === 'private' && (empty($_SESSION['user_id']) || (int)$_SESSION['user_id'] !== (int)$book['user_id'])) {
    http_response_code(403);
    die('<div class="card"><h2>Bu kitap gizli.</h2><p>Bu kitaba erişim yetkiniz yok.</p></div></div></body></html>');
}

// Prepare book pages
$content = $book['content'] ?: '';
$chunkSize = 1500; // Characters per page
$pages = [];

if (strlen($content) > 0) {
    $chunks = str_split($content, $chunkSize);
    foreach ($chunks as $chunk) {
        $pages[] = $chunk;
    }
} else {
    $pages[] = "Bu kitap henüz içerik içermiyor.";
}

// Ensure even number of pages for proper book display
if (count($pages) % 2 !== 0) {
    $pages[] = '';
}
?>

    <div class="card">
      <h2><?= e($book['title']) ?></h2>
      <div class="small">
        Yazar: <?= e($book['username']) ?> · 
        Görünürlük: <?= e($book['visibility']) ?>
      </div>
      
      <?php if ($book['cover_path']): ?>
        <img src="<?= base_url('../' . ltrim($book['cover_path'], '/')) ?>" 
             style="max-width:200px; border-radius:12px; border:1px solid var(--border); margin:15px 0; display:block;">
      <?php endif; ?>

      <!-- 3D Book Viewer -->
      <div class="book-viewer-container">
        <div class="book-3d" id="book3d">
          <div class="book-spine"></div>
          
          <?php 
          // Generate pages
          $totalPages = count($pages);
          for ($i = 0; $i < $totalPages; $i += 2) {
            $leftPageNum = $i;
            $rightPageNum = $i + 1;
            $leftContent = $pages[$leftPageNum] ?? '';
            $rightContent = $pages[$rightPageNum] ?? '';
            $zIndex = 1000 - $i;
            ?>
            
            <!-- Left Page -->
            <div class="book-page left-page" 
                 data-page="<?= $leftPageNum ?>" 
                 style="z-index: <?= $zIndex ?>;">
              <div class="page-content">
                <?php if ($leftPageNum === 0): ?>
                  <h1><?= e($book['title']) ?></h1>
                <?php endif; ?>
                <p><?= nl2br(e($leftContent)) ?></p>
              </div>
              <div class="page-number"><?= $leftPageNum + 1 ?></div>
            </div>
            
            <!-- Right Page -->
            <div class="book-page right-page" 
                 data-page="<?= $rightPageNum ?>" 
                 style="z-index: <?= $zIndex - 1 ?>;">
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
        <a class="btn" href="<?= base_url('export_print.php?id=' . (int)$book['id']) ?>">
          📄 Yazdır/PDF
        </a>
      </div>
    </div>

    <div class="bottom-nav">
      <a href="<?= base_url('books.php') ?>">📚 Kitaplar</a>
      <a href="<?= base_url('notes.php') ?>">📝 Notlar</a>
      <a href="<?= base_url('designer_cover.php') ?>">🎨 Kapak</a>
      <a href="<?= base_url('designer_map.php') ?>">🗺️ Harita</a>
    </div>
    
    <div class="small" style="text-align:center; margin-top:12px;">
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
        // Initial render
        this.render();
        this.updateControls();
        
        // Add keyboard navigation
        document.addEventListener('keydown', (e) => {
          if (e.key === 'ArrowLeft') {
            this.prevPage();
          } else if (e.key === 'ArrowRight') {
            this.nextPage();
          }
        });

        // Add touch/swipe support
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
        
        const handleSwipe = () => {
          if (touchEndX < touchStartX - 50) {
            this.nextPage();
          }
          if (touchEndX > touchStartX + 50) {
            this.prevPage();
          }
        };
        
        this.handleSwipe = handleSwipe;
      }

      render() {
        this.pages.forEach((page, index) => {
          const spreadIndex = Math.floor(index / 2);
          const isLeftPage = index % 2 === 0;
          
          // Reset transform
          page.classList.remove('flipped');
          
          if (spreadIndex < this.currentSpread) {
            // Pages that should be flipped (already read)
            page.classList.add('flipped');
          }
          
          // Update z-index for proper stacking
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
          this.playPageTurnSound();
        }
      }

      prevPage() {
        if (this.currentSpread > 0) {
          this.currentSpread--;
          this.render();
          this.updateControls();
          this.playPageTurnSound();
        }
      }

      updateControls() {
        // Update button states
        this.prevBtn.disabled = this.currentSpread === 0;
        this.nextBtn.disabled = this.currentSpread >= this.totalSpreads - 1;
        
        // Update page indicator
        const leftPageNum = this.currentSpread * 2 + 1;
        const rightPageNum = this.currentSpread * 2 + 2;
        
        this.currentPageDisplay.textContent = leftPageNum;
        this.currentPageNextDisplay.textContent = rightPageNum;
      }

      playPageTurnSound() {
        // Optional: Add a subtle page turn sound effect
        // This would require an audio file
        // const audio = new Audio('/assets/sounds/page-turn.mp3');
        // audio.volume = 0.2;
        // audio.play().catch(() => {});
      }

      goToSpread(spreadIndex) {
        if (spreadIndex >= 0 && spreadIndex < this.totalSpreads) {
          this.currentSpread = spreadIndex;
          this.render();
          this.updateControls();
        }
      }
    }

    // Initialize the book viewer
    const book3D = new Book3DViewer();

    // Optional: Auto-rotate book slightly for 3D effect on mouse move
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

    // Reset rotation when mouse leaves
    document.querySelector('.book-viewer-container')?.addEventListener('mouseleave', () => {
      const book = document.getElementById('book3d');
      if (book) {
        book.style.transform = 'rotateX(0deg) rotateY(0deg)';
      }
    });
  </script>
</body>
</html>
