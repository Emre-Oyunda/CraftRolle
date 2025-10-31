<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
if (file_exists(__DIR__ . '/../src/csrf.php')) require_once __DIR__ . '/../src/csrf.php';

$user = current_user();

if (!function_exists('csrf_boot')) { function csrf_boot(){} }
if (!function_exists('csrf_token')) {
  function csrf_token(){
    if(session_status() !== PHP_SESSION_ACTIVE) @session_start();
    if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
  }
}

if (!function_exists('db')){
  function db(): PDO {
    return new PDO('sqlite:'.DB_PATH, null, null, [
      PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
    ]);
  }
}

$per_page = 10;
$page     = max(1, (int)($_GET['page'] ?? 1));
$q        = trim($_GET['q'] ?? '');
$qLike    = ($q !== '') ? "%$q%" : null;

function avatar_url_row($row){
  $ap = $row['avatar_path'] ?? null;
  if ($ap) return preg_match('~^https?://~',$ap) ? $ap : base_url(ltrim($ap,'/'));
  $ch = mb_strtoupper(mb_substr($row['username']??'U',0,1));
  $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='96' height='96'><defs><linearGradient id='g' x1='0' x2='1'><stop offset='0%' stop-color='#2a2144'/><stop offset='100%' stop-color='#161226'/></linearGradient></defs><rect width='100%' height='100%' rx='16' fill='url(#g)'/><text x='50%' y='55%' text-anchor='middle' font-family='system-ui,-apple-system,Segoe UI,Roboto' font-size='42' fill='#ffd2f0' font-weight='800'>{$ch}</text></svg>";
  return 'data:image/svg+xml;utf8,'.rawurlencode($svg);
}
function cover_url_or_placeholder($title,$author,$cover_path){
  if($cover_path){
    return preg_match('~^https?://~',$cover_path) ? $cover_path : base_url(ltrim($cover_path,'/'));
  }
  $t = mb_strtoupper(mb_substr($title ?: 'K', 0, 1));
  $a = mb_strtoupper(mb_substr($author ?: 'A', 0, 1));
  $txt = htmlspecialchars($t.$a, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='400' height='560'><defs><linearGradient id='g' x1='0' x2='1'><stop offset='0%' stop-color='#2a2144'/><stop offset='100%' stop-color='#161226'/></linearGradient></defs><rect width='100%' height='100%' fill='url(#g)'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' font-family='system-ui,-apple-system,Segoe UI,Roboto' font-size='140' fill='#ffd2f0' font-weight='800'>{$txt}</text></svg>";
  return 'data:image/svg+xml;utf8,'.rawurlencode($svg);
}
function file_optional_include($path){
  if (is_file($path)) include $path;
}

function ensure_like_schema(){
  $pdo = db();
  $pdo->exec("CREATE TABLE IF NOT EXISTS post_likes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    UNIQUE(post_id,user_id)
  )");
}
function ps_like_count_index(int $post_id): int {
  $st = db()->prepare("SELECT COUNT(*) FROM post_likes WHERE post_id=?");
  $st->execute([$post_id]);
  return (int)$st->fetchColumn();
}
function ps_user_liked_index(int $post_id, int $uid): bool {
  if ($uid<=0) return false;
  $st = db()->prepare("SELECT 1 FROM post_likes WHERE post_id=? AND user_id=? LIMIT 1");
  $st->execute([$post_id,$uid]);
  return (bool)$st->fetchColumn();
}
function csrf_validate_ajax_index(): bool {
  if(session_status() !== PHP_SESSION_ACTIVE) @session_start();
  $tok = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
  $sess= $_SESSION['csrf'] ?? '';
  return $tok && $sess && hash_equals($sess, $tok);
}
ensure_like_schema();

if (($_GET['ajax'] ?? '') === 'like' && $_SERVER['REQUEST_METHOD']==='POST') {
  header('Content-Type: application/json; charset=utf-8');
  $me = current_user();
  if(!$me){ http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Oturum gerekli']); exit; }
  if(!csrf_validate_ajax_index()){ http_response_code(403); echo json_encode(['ok'=>false,'error'=>'CSRF']); exit; }
  $in = json_decode(file_get_contents('php://input'), true) ?: [];
  $postId = (int)($in['post_id'] ?? 0);
  if($postId<=0){ http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Geçersiz post']); exit; }

  $pdo = db();
  $pdo->prepare("DELETE FROM post_likes WHERE post_id=? AND user_id=?")->execute([$postId,(int)$me['id']]);
  $status = 'unliked';
  if($pdo->query("SELECT changes()")
        ->fetchColumn()==0){
    $pdo->prepare("INSERT OR IGNORE INTO post_likes(post_id,user_id,created_at) VALUES (?,?,datetime('now'))")
        ->execute([$postId,(int)$me['id']]);
    $status='liked';
  }
  $cnt = (int)$pdo->query("SELECT COUNT(*) FROM post_likes WHERE post_id=".$postId)->fetchColumn();
  echo json_encode(['ok'=>true,'status'=>$status,'count'=>$cnt]);
  exit;
}

$errors = [];
$posts = $books = [];

try {
  if ($qLike){
    $st = db()->prepare("
      SELECT p.id, p.user_id, p.type, p.content, p.image_path, p.visibility, p.created_at AS ts,
             u.username, u.avatar_path
      FROM posts p
      JOIN users u ON u.id=p.user_id
      WHERE p.visibility='public'
        AND p.type IN ('text','photo')
        AND (p.content LIKE ? OR u.username LIKE ?)
      ORDER BY p.created_at DESC
      LIMIT 200
    ");
    $st->execute([$qLike,$qLike]);
  } else {
    $st = db()->query("
      SELECT p.id, p.user_id, p.type, p.content, p.image_path, p.visibility, p.created_at AS ts,
             u.username, u.avatar_path
      FROM posts p
      JOIN users u ON u.id=p.user_id
      WHERE p.visibility='public'
        AND p.type IN ('text','photo')
      ORDER BY p.created_at DESC
      LIMIT 200
    ");
  }
  $posts = $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (Throwable $e) {
  $errors[] = "Gönderiler yüklenemedi: ".$e->getMessage();
}

try {
  if ($qLike){
    $sb = db()->prepare("
      SELECT b.id AS book_id, b.user_id, b.title,
             b.author AS author_name,
             b.cover_url AS cover_path,
             b.created_at AS ts,
             u.username, u.avatar_path
      FROM books_shared b
      JOIN users u ON u.id=b.user_id
      WHERE (b.title LIKE ? OR b.author LIKE ? OR u.username LIKE ?)
      ORDER BY b.created_at DESC
      LIMIT 200
    ");
    $sb->execute([$qLike,$qLike,$qLike]);
  } else {
    $sb = db()->query("
      SELECT b.id AS book_id, b.user_id, b.title,
             b.author AS author_name,
             b.cover_url AS cover_path,
             b.created_at AS ts,
             u.username, u.avatar_path
      FROM books_shared b
      JOIN users u ON u.id=b.user_id
      ORDER BY b.created_at DESC
      LIMIT 200
    ");
  }
  $books = $sb ? $sb->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (Throwable $e) {
  $errors[] = "Kitaplar yüklenemedi: ".$e->getMessage();
}

$feed = [];

foreach ($posts as $p) {
  $feed[] = [
    'kind' => 'post',
    'post_id' => (int)($p['id'] ?? 0),
    'ts'   => $p['ts'] ?? null,
    'user_id' => $p['user_id'] ?? null,
    'username'=> $p['username'] ?? null,
    'avatar_path'=> $p['avatar_path'] ?? null,
    'type' => $p['type'] ?? null,
    'content' => $p['content'] ?? null,
    'image_path' => $p['image_path'] ?? null,
  ];
}
foreach ($books as $b) {
  $feed[] = [
    'kind' => 'book',
    'ts'   => $b['ts'] ?? null,
    'user_id' => $b['user_id'] ?? null,
    'username'=> $b['username'] ?? null,
    'avatar_path'=> $b['avatar_path'] ?? null,
    'title'=> $b['title'] ?? null,
    'author_name'=> $b['author_name'] ?? null,
    'cover_path'=> $b['cover_path'] ?? null,
    'book_id'=> $b['book_id'] ?? null,
  ];
}

usort($feed, function($a,$b){
  return strcmp($b['ts'] ?? '', $a['ts'] ?? '');
});

$total = count($feed);
$total_pages = max(1, (int)ceil($total / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;
$rows = array_slice($feed, $offset, $per_page);

$navLinks = [
  ['icon' => '🏠', 'label' => 'Panel', 'href' => 'dashboard.php'],
  ['icon' => '📚', 'label' => 'Kitaplarım', 'href' => 'books.php'],
  ['icon' => '📝', 'label' => 'Notlarım', 'href' => 'notes.php'],
  ['icon' => '🎉', 'label' => 'Eğlence', 'href' => 'eglence.php'],
  ['icon' => '🎨', 'label' => 'Kapak', 'href' => 'designer_cover.php'],
  ['icon' => '🗺️', 'label' => 'Harita', 'href' => 'designer_map.php'],
];

if (!empty($user) && !empty($user['is_admin']) && (int)$user['is_admin']===1) {
  $navLinks[] = ['icon' => '🛠️', 'label' => 'Admin', 'href' => '../admin/panel.php'];
}

csrf_boot();
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<style>
:root {
  --violet-700: #2a1d4d;
  --violet-500: #6c4ddc;
  --violet-300: #bca6ff;
  --pink-400: #f58acb;
  --pink-200: #ffd8ef;
  --glass-light: rgba(255, 255, 255, 0.78);
  --glass-dark: rgba(19, 17, 35, 0.86);
  --text-base: #221b39;
  --text-muted: rgba(34, 27, 57, 0.7);
}

* { box-sizing: border-box; }

body.hub-page {
  margin: 0;
  min-height: 100vh;
  font-family: 'Inter', 'Segoe UI', Tahoma, sans-serif;
  color: var(--text-base);
  background:
    radial-gradient(circle at 8% -6%, rgba(255, 218, 243, 0.85), transparent 58%),
    radial-gradient(circle at 88% 0%, rgba(185, 206, 255, 0.7), transparent 55%),
    linear-gradient(135deg, #f5f1ff 0%, #efe6ff 40%, #ffe7f5 100%);
  padding: 56px 16px 90px;
  transition: background 0.35s ease, color 0.35s ease;
}

body.hub-page::before,
body.hub-page::after {
  content: '';
  position: fixed;
  width: 420px;
  height: 420px;
  border-radius: 50%;
  pointer-events: none;
  filter: blur(140px);
  opacity: 0.26;
  z-index: 0;
}

body.hub-page::before {
  top: -180px;
  left: -160px;
  background: linear-gradient(135deg, rgba(255, 180, 230, 0.68), rgba(255, 236, 252, 0.55));
}

body.hub-page::after {
  bottom: -190px;
  right: -150px;
  background: linear-gradient(135deg, rgba(140, 122, 255, 0.62), rgba(107, 198, 255, 0.52));
}

body.hub-page.dark-theme {
  color: #f7ecff;
  background:
    radial-gradient(circle at 15% -12%, rgba(83, 60, 140, 0.55), transparent 58%),
    radial-gradient(circle at 90% 0%, rgba(201, 72, 140, 0.45), transparent 55%),
    linear-gradient(135deg, #0f0b1f 0%, #161229 45%, #1f1737 100%);
}

body.hub-page.dark-theme::before,
body.hub-page.dark-theme::after {
  opacity: 0.16;
}

.page-wrap {
  position: relative;
  z-index: 1;
  max-width: 1180px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 26px;
}

.glass-panel {
  background: var(--glass-light);
  border-radius: 28px;
  border: 1px solid rgba(255, 255, 255, 0.68);
  padding: 26px 30px;
  box-shadow: 0 26px 60px rgba(120, 90, 200, 0.16);
  backdrop-filter: blur(20px);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.glass-panel:hover {
  transform: translateY(-3px);
  box-shadow: 0 34px 78px rgba(120, 90, 200, 0.22);
}

body.hub-page.dark-theme .glass-panel {
  background: var(--glass-dark);
  border-color: rgba(108, 90, 190, 0.42);
  box-shadow: 0 30px 70px rgba(10, 7, 22, 0.7);
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
  font-size: 1.7rem;
  font-weight: 700;
  color: inherit;
  text-decoration: none;
}

.brand-link .brand-name {
  background: linear-gradient(120deg, #ff9fdc, #8f72ff);
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
  background: rgba(255, 255, 255, 0.78);
  border: 1px solid rgba(255, 255, 255, 0.68);
  font-weight: 600;
  text-decoration: none;
  color: inherit;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.nav-pill:hover {
  transform: translateY(-2px);
  box-shadow: 0 18px 36px rgba(124, 88, 220, 0.24);
}

.nav-pill.is-active {
  background: linear-gradient(120deg, #ffb9e9, #b79dff);
  color: #fff;
  box-shadow: 0 20px 42px rgba(124, 88, 220, 0.3);
}

body.hub-page.dark-theme .nav-pill {
  background: rgba(25, 22, 47, 0.9);
  border-color: rgba(108, 90, 190, 0.36);
}

.theme-toggle {
  display: inline-flex;
  align-items: center;
  gap: 9px;
  padding: 10px 18px;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.65);
  background: linear-gradient(135deg, #fbd5ff, #d7c6ff);
  color: #3a295b;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 20px 38px rgba(155, 110, 255, 0.26);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.theme-toggle:hover {
  transform: translateY(-2px);
  box-shadow: 0 28px 54px rgba(155, 110, 255, 0.32);
}

body.hub-page.dark-theme .theme-toggle {
  background: rgba(26, 22, 44, 0.92);
  border: 1px solid rgba(108, 90, 190, 0.4);
  color: #f7ebff;
  box-shadow: 0 22px 46px rgba(5, 4, 16, 0.65);
}

.user-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border-radius: 999px;
  background: rgba(30, 25, 52, 0.85);
  color: #f5e9ff;
  border: 1px solid rgba(112, 91, 190, 0.32);
  font-weight: 600;
}

.user-pill .seed {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  background: linear-gradient(135deg, #8f6bff, #2b1e4a);
  color: #ffeafa;
  font-weight: 700;
}

.hero-card {
  display: grid;
  gap: 18px;
}

.hero-card h1 {
  margin: 0;
  font-size: clamp(2rem, 3vw, 2.8rem);
  letter-spacing: -0.03em;
}

.hero-card p {
  margin: 0;
  max-width: 640px;
  line-height: 1.6;
  color: var(--text-muted);
}

body.hub-page.dark-theme .hero-card p {
  color: rgba(236, 224, 255, 0.74);
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.pill-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  border-radius: 16px;
  border: 1px solid rgba(118, 96, 210, 0.32);
  background: linear-gradient(135deg, #7c5bff, #f58acb);
  color: #fff;
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 22px 46px rgba(124, 90, 220, 0.25);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.pill-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 28px 60px rgba(124, 90, 220, 0.3);
}

.pill-btn.secondary {
  background: rgba(255, 255, 255, 0.84);
  color: inherit;
  border: 1px solid rgba(118, 96, 210, 0.22);
  box-shadow: none;
}

body.hub-page.dark-theme .pill-btn.secondary {
  background: rgba(26, 23, 46, 0.88);
  color: #f3e7ff;
}

.badge-row {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.badge-pill {
  padding: 8px 14px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.78);
  border: 1px solid rgba(118, 96, 210, 0.18);
  font-weight: 600;
  color: var(--text-muted);
  font-size: 0.92rem;
}

body.hub-page.dark-theme .badge-pill {
  background: rgba(26, 23, 46, 0.9);
  color: rgba(236, 224, 255, 0.72);
}

.feature-grid {
  display: grid;
  gap: 18px;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
}

.feature-card {
  padding: 22px;
  border-radius: 24px;
  background: rgba(255, 255, 255, 0.82);
  border: 1px solid rgba(255, 255, 255, 0.68);
  box-shadow: 0 20px 48px rgba(120, 90, 220, 0.14);
  display: grid;
  gap: 10px;
  transition: transform 0.22s ease;
}

.feature-card:hover {
  transform: translateY(-4px);
}

body.hub-page.dark-theme .feature-card {
  background: rgba(26, 23, 46, 0.92);
  border-color: rgba(108, 90, 190, 0.34);
}

.feature-card h3 {
  margin: 0;
  color: #563184;
  display: flex;
  align-items: center;
  gap: 10px;
}

body.hub-page.dark-theme .feature-card h3 {
  color: #ffd6f3;
}

.feature-card p {
  margin: 0;
  line-height: 1.54;
  color: var(--text-muted);
}

body.hub-page.dark-theme .feature-card p {
  color: rgba(236, 224, 255, 0.74);
}

.search-form {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
  margin-bottom: 18px;
}

.search-input {
  min-width: 240px;
  flex: 1;
  padding: 12px 14px;
  border-radius: 14px;
  border: 1px solid rgba(118, 96, 210, 0.30);
  background: rgba(255, 255, 255, 0.9);
  color: inherit;
  font-size: 1rem;
}

body.hub-page.dark-theme .search-input {
  background: rgba(26, 23, 46, 0.88);
  border-color: rgba(108, 90, 190, 0.38);
  color: #f5e9ff;
}

.home-feed {
  display: grid;
  gap: 16px;
}

.feed-item {
  display: grid;
  grid-template-columns: 70px 1fr;
  gap: 14px;
  padding: 16px;
  border-radius: 20px;
  border: 1px solid rgba(118, 96, 210, 0.24);
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.82), rgba(255, 255, 255, 0.74));
}

body.hub-page.dark-theme .feed-item {
  background: rgba(25, 22, 46, 0.92);
  border-color: rgba(108, 90, 190, 0.38);
}

.feed-ava {
  width: 70px;
  height: 70px;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid rgba(118, 96, 210, 0.3);
  background: rgba(32, 27, 58, 0.9);
}

.feed-ava img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.feed-header {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
  margin-bottom: 6px;
}

.feed-header a {
  color: inherit;
  text-decoration: none;
  font-weight: 700;
}

.feed-header a:hover { text-decoration: underline; }

.feed-meta {
  font-size: 0.86rem;
  color: rgba(34, 27, 57, 0.62);
}

body.hub-page.dark-theme .feed-meta {
  color: rgba(236, 224, 255, 0.64);
}

.feed-photo {
  margin-top: 8px;
  border-radius: 14px;
  overflow: hidden;
  border: 1px solid rgba(118, 96, 210, 0.24);
  background: rgba(32, 27, 58, 0.92);
}

.feed-photo img {
  width: 100%;
  height: auto;
  display: block;
}

.feed-book {
  margin-top: 6px;
  display: grid;
  gap: 14px;
  grid-template-columns: 120px 1fr;
  align-items: start;
}

.feed-book .cover {
  width: 120px;
  aspect-ratio: 5/7;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid rgba(118, 96, 210, 0.28);
  background: rgba(32, 27, 58, 0.9);
}

.feed-book .cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.feed-book h3 {
  margin: 0;
  font-size: 1.16rem;
}

.like-line {
  margin-top: 10px;
}

.like-btn {
  padding: 8px 12px;
  border-radius: 12px;
  border: 1px solid rgba(118, 96, 210, 0.34);
  background: rgba(255, 255, 255, 0.85);
  color: inherit;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.like-btn[aria-pressed="true"] {
  background: linear-gradient(135deg, #f7b4de, #bba2ff);
  color: #fff;
  box-shadow: 0 0 0 2px rgba(124, 90, 220, 0.3) inset;
}

body.hub-page.dark-theme .like-btn {
  background: rgba(26, 23, 46, 0.88);
  border-color: rgba(108, 90, 190, 0.42);
  color: #f3e9ff;
}

.pager {
  display: flex;
  justify-content: center;
  gap: 12px;
  margin-top: 18px;
  align-items: center;
}

.soft-text {
  font-size: 0.88rem;
  color: rgba(34, 27, 57, 0.7);
}

body.hub-page.dark-theme .soft-text {
  color: rgba(236, 224, 255, 0.66);
}

.floating-nav {
  position: sticky;
  bottom: 16px;
  margin-top: 24px;
  display: flex;
  gap: 10px;
  justify-content: center;
  padding: 12px;
  border-radius: 20px;
  background: rgba(24, 20, 44, 0.18);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(118, 96, 210, 0.25);
}

.floating-nav a {
  flex: 1;
  text-align: center;
  padding: 10px 14px;
  border-radius: 14px;
  text-decoration: none;
  font-weight: 600;
  color: inherit;
  background: rgba(255, 255, 255, 0.78);
  border: 1px solid rgba(118, 96, 210, 0.26);
}

body.hub-page.dark-theme .floating-nav {
  background: rgba(12, 10, 24, 0.75);
  border-color: rgba(108, 90, 190, 0.32);
}

body.hub-page.dark-theme .floating-nav a {
  background: rgba(26, 23, 46, 0.9);
  border-color: rgba(108, 90, 190, 0.34);
  color: #f5ebff;
}

.footer-note {
  text-align: center;
  font-size: 0.9rem;
  color: rgba(34, 27, 57, 0.6);
  margin-top: 18px;
}

body.hub-page.dark-theme .footer-note {
  color: rgba(236, 224, 255, 0.58);
}

@media (max-width: 860px) {
  body.hub-page { padding: 44px 12px 80px; }
  .top-header { flex-direction: column; align-items: flex-start; }
  .nav-line { width: 100%; }
  .feed-item { grid-template-columns: 60px 1fr; }
  .feed-book { grid-template-columns: 100px 1fr; }
  .floating-nav { position: fixed; left: 12px; right: 12px; bottom: 18px; }
}

@media (max-width: 560px) {
  .glass-panel { padding: 22px; }
  .hero-actions { flex-direction: column; align-items: stretch; }
  .pill-btn { justify-content: center; }
  .feed-item { grid-template-columns: 1fr; }
  .feed-ava { width: 56px; height: 56px; }
  .feed-book { grid-template-columns: 1fr; }
}
</style>
</head>
<body class="hub-page">
<div class="page-wrap">
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
        <a class="nav-pill" href="<?= base_url($item['href']) ?>">
          <span><?= $item['icon'] ?></span><?= e($item['label']) ?>
        </a>
      <?php endforeach; ?>
      <?php if($user): ?>
        <span class="user-pill">
          <span class="seed"><?= e(mb_strtoupper(mb_substr($user['username'] ?? 'K', 0, 1))) ?></span>
          Merhaba, <?= e($user['username']) ?>
        </span>
        <a class="nav-pill" href="<?= base_url('logout.php') ?>">Çıkış</a>
      <?php else: ?>
        <a class="nav-pill" href="<?= base_url('login.php') ?>">Giriş</a>
        <a class="nav-pill" href="<?= base_url('register.php') ?>">Kayıt Ol</a>
      <?php endif; ?>
      <?php file_optional_include(__DIR__."/_Navbar.php"); ?>
    </div>
  </header>

  <section class="glass-panel hero-card">
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
      <span class="badge-pill">🚀 Fikirden kitaba hızlı süreç</span>
      <span class="badge-pill">🎨 Tasarım stüdyoları</span>
      <span class="badge-pill">📚 3D kitap görüntüleyici</span>
      <span class="badge-pill">🤝 Topluluk paylaşımları</span>
    </div>
  </section>

  <section class="feature-grid">
    <div class="feature-card">
      <h3>📚 Kitap Stüdyosu</h3>
      <p>Metinlerini bölümlere ayır, düzenle ve gerçekçi sayfa çevirme animasyonlarıyla canlandır.</p>
    </div>
    <div class="feature-card">
      <h3>🎨 Kapak Tasarımcısı</h3>
      <p>Hazır şablonlar ve renklerle kitabın kimliğini belirleyen kapaklar oluştur.</p>
    </div>
    <div class="feature-card">
      <h3>🗺️ Harita Editörü</h3>
      <p>Fantastik evrenlerin için bölgeler çiz, karakterlerini konumlandır.</p>
    </div>
    <div class="feature-card">
      <h3>📝 Not & Fikir Bankası</h3>
      <p>Karakter, sahne ve olay notlarını kaybetmeden sakla ve dilediğinde güncelle.</p>
    </div>
    <div class="feature-card">
      <h3>🎉 Eğlence Stüdyosu</h3>
      <p>Zar, kelime ve duygu kartlarıyla yazarlık blokunu kır.</p>
    </div>
    <div class="feature-card">
      <h3>📄 Yazdırma & PDF</h3>
      <p>Projelerini baskıya hazır formatlarda dışa aktar, paylaş veya arşivle.</p>
    </div>
  </section>

  <section class="glass-panel">
    <form method="get" class="search-form">
      <input class="search-input" type="text" name="q" placeholder="Gönderi, kitap ya da kullanıcı ara…" value="<?= e($q) ?>">
      <button class="pill-btn" type="submit">Ara</button>
      <?php if($q!==''): ?><a class="pill-btn secondary" href="<?= base_url('index.php') ?>">Temizle</a><?php endif; ?>
    </form>

    <h2 style="margin:0 0 12px;">🌸 <?= e(APP_NAME) ?> Akışı</h2>
    <p style="margin:0 0 20px; color: var(--text-muted);">Topluluk gönderileri ve paylaşılan kitaplar tek akışta listelenir. Beğenerek destek olabilirsin.</p>

    <?php if($errors): ?>
      <div class="glass-panel" style="background:rgba(255,100,140,0.12); border-color: rgba(255,100,140,0.35); color:#ffeff4; padding:16px;">
        <strong>Uyarı:</strong> <?= e(implode(' | ', $errors)) ?>
      </div>
    <?php endif; ?>

    <div class="home-feed">
      <?php if(!$rows): ?>
        <div class="soft-text">Henüz herkese açık paylaşım veya kitap yok.</div>
      <?php else: foreach($rows as $r):
        $when = !empty($r['ts']) ? date('d.m.Y H:i', strtotime($r['ts'])) : '';
        $purl = base_url('../profil/kisi.php?uid='.(int)($r['user_id'] ?? 0));
        $ava  = avatar_url_row($r);
      ?>
      <article class="feed-item">
        <div class="feed-ava"><a href="<?= $purl ?>"><img src="<?= e($ava) ?>" alt=""></a></div>
        <div>
          <header class="feed-header">
            <a href="<?= $purl ?>">@<?= e($r['username'] ?? 'kullanici') ?></a>
            <?php if($when): ?><span class="feed-meta">· <?= e($when) ?></span><?php endif; ?>
            <span class="feed-meta">· <?= e(strtoupper($r['kind']==='post' ? ($r['type'] ?? 'POST') : 'BOOK')) ?></span>
          </header>

          <?php if(($r['kind'] ?? '')==='post'): ?>
            <?php
              $pid   = (int)($r['post_id'] ?? 0);
              $count = $pid ? ps_like_count_index($pid) : 0;
              $liked = $pid ? ps_user_liked_index($pid, (int)($user['id'] ?? 0)) : false;
            ?>
            <?php if(($r['type'] ?? '')==='text'): ?>
              <div style="white-space:pre-wrap; line-height:1.6;"><?= nl2br(e($r['content'] ?? '')) ?></div>
            <?php else: ?>
              <?php if(!empty($r['image_path'])): ?>
                <div class="feed-photo">
                  <img src="<?= e(preg_match('~^https?://~',$r['image_path']) ? $r['image_path'] : base_url(ltrim($r['image_path'],'/'))) ?>" alt="">
                </div>
              <?php endif; ?>
              <?php if(!empty($r['content'])): ?><div style="margin-top:6px;white-space:pre-wrap; line-height:1.6;"><?= nl2br(e($r['content'])) ?></div><?php endif; ?>
            <?php endif; ?>
            <div class="like-line">
              <button type="button" class="like-btn" data-id="<?= $pid ?>" aria-pressed="<?= $liked?'true':'false' ?>">
                ❤️ <span class="like-count"><?= (int)$count ?></span>
              </button>
            </div>
          <?php else: ?>
            <?php
              $cover = cover_url_or_placeholder($r['title'] ?? '', $r['author_name'] ?? '', $r['cover_path'] ?? null);
              $burl  = base_url('view_book.php?id='.(int)($r['book_id'] ?? 0));
            ?>
            <div class="feed-book">
              <a class="cover" href="<?= $burl ?>"><img src="<?= e($cover) ?>" alt=""></a>
              <div>
                <h3><a href="<?= $burl ?>" style="color:inherit; text-decoration:none;"><?= e($r['title'] ?? 'Kitap') ?></a></h3>
                <?php if(!empty($r['author_name'])): ?><div class="feed-meta">Yazar: <?= e($r['author_name']) ?></div><?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </article>
      <?php endforeach; endif; ?>
    </div>

    <?php if($total > $per_page): ?>
      <div class="pager">
        <?php
          $total_pages = max(1, (int)ceil($total / $per_page));
          $page = max(1, min($page, $total_pages));
          $mk = function($p) use ($q){
            $qs = []; if($p>1) $qs['page']=$p; if($q!=='') $qs['q']=$q;
            return base_url('index.php'.($qs?('?'.http_build_query($qs)):''));
          };
        ?>
        <?php if($page>1): ?><a class="pill-btn secondary" href="<?= $mk($page-1) ?>">« Önceki</a><?php endif; ?>
        <span class="soft-text">Sayfa <?= $page ?> / <?= $total_pages ?></span>
        <?php if($page<$total_pages): ?><a class="pill-btn secondary" href="<?= $mk($page+1) ?>">Sonraki »</a><?php endif; ?>
      </div>
    <?php endif; ?>
  </section>

  <section class="glass-panel" style="background: linear-gradient(160deg, rgba(124, 88, 220, 0.9), rgba(244, 138, 203, 0.82)); color:#fff;">
    <h3 style="margin:0 0 10px;">3D kitap önizleyici ile sahnelerini canlı gör.</h3>
    <p style="margin:0 0 16px; line-height:1.6;">Kütüphanendeki her proje tek tıklamayla 3D olarak açılır. Sayfa çevirme efektleri, ışık ve gölge detayları ile okuyucularını etkileyen sunumlar hazırla.</p>
    <a class="pill-btn secondary" href="<?= base_url('3d/view_book.php') ?>" style="background: rgba(255,255,255,0.2); color:#fff; border-color: rgba(255,255,255,0.4);">3D Görüntüleyiciyi Aç</a>
  </section>
</div>

<nav class="floating-nav">
  <a href="<?= base_url('books.php') ?>">📚 Kitaplar</a>
  <a href="<?= base_url('notes.php') ?>">📝 Notlar</a>
  <a href="<?= base_url('designer_cover.php') ?>">🎨 Kapak</a>
  <a href="<?= base_url('designer_map.php') ?>">🗺️ Harita</a>
</nav>

<footer class="footer-note">© <?= date('Y') ?> <?= e(APP_NAME) ?> · Craftrolle topluluğu</footer>

<script>
(function(){
  const toggleBtn = document.getElementById('theme-toggle');
  if(!toggleBtn) return;
  const icon = document.getElementById('theme-icon');
  const text = document.getElementById('theme-text');
  const storageKey = 'craft-landing-theme';

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

<script>
function csrfToken(){
  const tag = document.querySelector('meta[name="csrf-token"]');
  return tag ? tag.content : '';
}

addEventListener('click', async (ev)=>{
  const btn = ev.target.closest('.like-btn');
  if(!btn) return;
  ev.preventDefault();
  if(btn.dataset.loading==='1') return;
  btn.dataset.loading='1';
  btn.disabled = true;
  const postId = Number(btn.getAttribute('data-id'));
  const cntEl = btn.querySelector('.like-count');
  try{
    const res = await fetch(location.pathname+'?ajax=like', {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-Token':csrfToken()},
      body: JSON.stringify({post_id:postId})
    });
    if(res.status===401){ alert('Beğenmek için giriş yap.'); return; }
    const data = await res.json().catch(()=>({}));
    if(!res.ok || !data?.ok) throw new Error(data?.error||'Hata');
    btn.setAttribute('aria-pressed', data.status==='liked'?'true':'false');
    if(cntEl) cntEl.textContent = String(data.count||0);
  }catch(e){
    alert('Beğeni olmadı.');
  }finally{
    btn.dataset.loading='0';
    btn.disabled=false;
  }
});
</script>
</body>
</html>
