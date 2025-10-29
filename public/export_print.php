<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare("SELECT b.*, u.username FROM books b JOIN users u ON u.id=b.user_id WHERE b.id=? AND b.is_deleted=0");
$st->execute([$id]);
$book = $st->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    http_response_code(404);
    die('Kitap bulunamadı');
}

$user = current_user();
if ($book['visibility'] === 'private' && (empty($user['id']) || (int)$user['id'] !== (int)$book['user_id'])) {
    http_response_code(403);
    die('Bu kitaba erişim yetkiniz yok.');
}
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($book['title']) ?> - Yazdır</title>
  <style>
    @media print {
      body { margin: 0; padding: 20px; }
      .no-print { display: none; }
    }
    body {
      font-family: 'Georgia', serif;
      line-height: 1.8;
      max-width: 800px;
      margin: 0 auto;
      padding: 40px 20px;
    }
    h1 {
      text-align: center;
      margin-bottom: 10px;
      font-size: 2.5em;
    }
    .meta {
      text-align: center;
      color: #666;
      margin-bottom: 40px;
      font-style: italic;
    }
    .content {
      text-align: justify;
      font-size: 14pt;
    }
    .btn {
      display: inline-block;
      padding: 10px 20px;
      background: #ff6b9d;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      margin: 20px 10px;
    }
  </style>
</head>
<body>
  <div class="no-print" style="text-align: center;">
    <button onclick="window.print()" class="btn">🖨️ Yazdır</button>
    <a href="<?= base_url('3d/view_book.php?id=' . $book['id']) ?>" class="btn">← Geri Dön</a>
  </div>

  <h1><?= e($book['title']) ?></h1>
  <div class="meta">
    Yazar: <?= e($book['username']) ?><br>
    Tarih: <?= date('d.m.Y', strtotime($book['created_at'])) ?>
  </div>
  
  <div class="content">
    <?= nl2br(e($book['content'])) ?>
  </div>
</body>
</html>
