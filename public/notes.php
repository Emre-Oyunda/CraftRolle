<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';

require_login();

$uid = $_SESSION['user_id'] ?? null;
$created_note = null;
$notes = [];
$notesCount = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $title = trim($_POST['title'] ?? '');
    if ($title === '') {
        $title = 'Not';
    }

    $content = $_POST['content'] ?? '';
    if ($content === '') {
        $content = $_POST['rte_fallback'] ?? '';
    }

    $st = db()->prepare('INSERT INTO notes(user_id, title, content, updated_at) VALUES(?,?,?,?)');
    $st->execute([$uid, $title, $content, date('c')]);

    $id = (int) db()->lastInsertId();
    $st = db()->prepare('SELECT * FROM notes WHERE id = ? AND user_id = ?');
    $st->execute([$id, $uid]);
    $created_note = $st->fetch(PDO::FETCH_ASSOC);
}

$user = current_user();

if ($uid) {
    $st = db()->prepare('SELECT n.*, b.title AS book_title FROM notes n LEFT JOIN books b ON n.book_id = b.id WHERE n.user_id = ? AND n.is_deleted = 0 ORDER BY n.updated_at DESC');
    $st->execute([$uid]);
    $notes = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $notesCount = count($notes);
}
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(APP_NAME) ?> — Not Yaz</title>
<link rel="stylesheet" href="../assets/css/style.css">
<meta name="theme-color" content="#fdd7ed">
<style>
  .notes-page {
    --accent: #ff8ac3;
    --accent-strong: #ff6bb0;
    --accent-soft: rgba(255, 138, 195, 0.2);
    --accent-shadow: rgba(255, 138, 195, 0.25);
    --bg: radial-gradient(circle at 20% -10%, rgba(255, 206, 231, 0.65), transparent 55%), radial-gradient(circle at 90% 10%, rgba(136, 87, 233, 0.42), transparent 52%), #fdf1f7;
    --card-bg: rgba(255, 255, 255, 0.82);
    --card-border: rgba(255, 138, 195, 0.22);
    --card-shadow: 0 28px 45px rgba(87, 23, 73, 0.08);
    --text-color: #211225;
    --muted-color: rgba(33, 18, 37, 0.64);
    --btn-text: #ffffff;
    --btn-ghost-bg: rgba(255, 255, 255, 0.55);
    --btn-ghost-border: rgba(255, 138, 195, 0.35);
    --btn-shadow: 0 15px 30px rgba(255, 138, 195, 0.28);
    --input-bg: rgba(255, 255, 255, 0.9);
    --input-border: rgba(255, 138, 195, 0.35);
    --input-focus: rgba(255, 138, 195, 0.45);
    --preview-bg: rgba(255, 255, 255, 0.95);
    --preview-text: #1c0f24;
    --status-success: #63cfa5;
    --status-chip-bg: rgba(255, 138, 195, 0.18);
    --status-chip-dot: #ff8ac3;
    --divider: rgba(255, 138, 195, 0.25);
    background: var(--bg);
    color: var(--text-color);
    min-height: 100vh;
    padding: clamp(18px, 5vw, 42px);
    transition: background 0.6s ease, color 0.4s ease;
    position: relative;
    overflow-x: hidden;
  }
  .notes-page.theme-dark {
    --accent: #ff84c8;
    --accent-strong: #ff4fb8;
    --accent-soft: rgba(255, 132, 200, 0.22);
    --accent-shadow: rgba(255, 132, 200, 0.35);
    --bg: radial-gradient(circle at 10% -20%, rgba(90, 40, 150, 0.4), transparent 55%), radial-gradient(circle at 80% -10%, rgba(255, 66, 155, 0.18), transparent 45%), #0b0614;
    --card-bg: rgba(18, 14, 32, 0.82);
    --card-border: rgba(153, 117, 210, 0.16);
    --card-shadow: 0 30px 65px rgba(0, 0, 0, 0.45);
    --text-color: #eee3ff;
    --muted-color: rgba(238, 227, 255, 0.65);
    --btn-text: #160414;
    --btn-ghost-bg: rgba(255, 255, 255, 0.08);
    --btn-ghost-border: rgba(255, 132, 200, 0.3);
    --btn-shadow: 0 18px 36px rgba(255, 79, 184, 0.28);
    --input-bg: rgba(21, 18, 32, 0.92);
    --input-border: rgba(255, 132, 200, 0.22);
    --input-focus: rgba(255, 132, 200, 0.45);
    --preview-bg: rgba(13, 10, 24, 0.9);
    --preview-text: #f6ecff;
    --status-success: #7ce5b8;
    --status-chip-bg: rgba(255, 132, 200, 0.18);
    --status-chip-dot: #ff84c8;
    --divider: rgba(255, 132, 200, 0.22);
    color-scheme: dark;
  }
  .notes-page.theme-transition-block * {
    transition: none !important;
  }
  .notes-page::before,
  .notes-page::after {
    content: "";
    position: fixed;
    width: 420px;
    height: 420px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.32), transparent 70%);
    filter: blur(0);
    opacity: 0.55;
    pointer-events: none;
    z-index: 0;
    transition: opacity 0.5s ease;
  }
  .notes-page::before { top: -180px; left: -140px; }
  .notes-page::after { bottom: -200px; right: -120px; }
  .notes-page.theme-dark::before,
  .notes-page.theme-dark::after {
    opacity: 0.28;
    background: radial-gradient(circle, rgba(255, 132, 200, 0.24), transparent 72%);
  }
  .notes-page .container {
    max-width: 1180px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
  }
  .notes-page .card {
    background: var(--card-bg);
    border-radius: 20px;
    padding: clamp(18px, 2.5vw, 26px);
    margin-bottom: 22px;
    border: 1px solid var(--card-border);
    box-shadow: var(--card-shadow);
    backdrop-filter: blur(24px);
    transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
  }
  .notes-page .card:not(.topnav):hover {
    transform: translateY(-4px);
    box-shadow: 0 24px 55px var(--accent-shadow);
  }
  .notes-page .topnav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: clamp(16px, 3vw, 32px);
    flex-wrap: wrap;
    margin-bottom: 28px;
  }
  .notes-page .nav-left {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
  }
  .notes-page .brand-link {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    font-size: 1.05rem;
  }
  .notes-page .brand {
    font-size: 1.6rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--accent), var(--accent-strong));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
  .notes-page .user-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    border-radius: 999px;
    background: var(--accent-soft);
    color: var(--muted-color);
    font-size: 0.92rem;
  }
  .notes-page .user-chip::before {
    content: "";
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--accent);
  }
  .notes-page .menu {
    display: flex;
    align-items: center;
    gap: 18px;
    flex-wrap: wrap;
    justify-content: flex-end;
  }
  .notes-page .nav-links {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
  }
  .notes-page .nav-divider {
    display: inline-flex;
    width: 1px;
    height: 26px;
    background: var(--divider);
    margin: 0 4px;
  }
  .notes-page .nav-link {
    padding: 8px 14px;
    border-radius: 12px;
    color: var(--muted-color);
    font-weight: 500;
    position: relative;
    transition: color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
  }
  .notes-page .nav-link:hover {
    color: var(--text-color);
    background: var(--accent-soft);
  }
  .notes-page .nav-link.is-active {
    color: var(--text-color);
    background: var(--accent-soft);
    box-shadow: inset 0 0 0 1px rgba(255, 138, 195, 0.35);
  }
  .notes-page .nav-link.nav-link-primary {
    background: linear-gradient(135deg, var(--accent), var(--accent-strong));
    color: var(--btn-text);
    box-shadow: 0 12px 24px var(--accent-shadow);
  }
  .notes-page .nav-link.nav-link-primary:hover {
    color: var(--btn-text);
    filter: brightness(1.05);
  }
  .notes-page .theme-toggle {
    display: inline-flex;
    gap: 10px;
    padding-left: 12px;
    border-left: 1px solid var(--divider);
  }
  .notes-page .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border-radius: 12px;
    padding: 10px 18px;
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    background: linear-gradient(135deg, var(--accent), var(--accent-strong));
    color: var(--btn-text);
    box-shadow: var(--btn-shadow);
    transition: transform 0.25s ease, box-shadow 0.25s ease, filter 0.25s ease;
  }
  .notes-page .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 36px var(--accent-shadow);
    filter: brightness(1.03);
  }
  .notes-page .btn:focus-visible {
    outline: 3px solid var(--accent);
    outline-offset: 2px;
  }
  .notes-page .btn-ghost {
    background: var(--btn-ghost-bg);
    color: var(--text-color);
    box-shadow: none;
    border: 1px solid var(--btn-ghost-border);
    transition: background 0.25s ease, color 0.25s ease, border 0.25s ease, transform 0.25s ease;
  }
  .notes-page .btn-ghost:hover {
    background: rgba(255, 255, 255, 0.75);
    transform: translateY(-2px);
  }
  .notes-page.theme-dark .btn-ghost:hover {
    background: rgba(255, 255, 255, 0.12);
  }
  .notes-page .theme-btn {
    min-width: 130px;
  }
  .notes-page .theme-btn.is-active {
    background: linear-gradient(135deg, var(--accent-strong), var(--accent));
    box-shadow: 0 20px 30px var(--accent-shadow);
  }
  .notes-page .status-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 18px;
  }
  .notes-page .status-chip {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 16px;
    border-radius: 999px;
    background: var(--status-chip-bg);
    color: var(--muted-color);
    font-size: 0.88rem;
    font-weight: 500;
  }
  .notes-page .status-chip .dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--status-chip-dot);
    box-shadow: 0 0 0 4px rgba(255, 138, 195, 0.2);
  }
  .notes-page .status-chip.muted {
    opacity: 0.7;
  }
  .notes-page .success-banner {
    display: flex;
    align-items: center;
    gap: 16px;
    background: linear-gradient(135deg, rgba(99, 207, 165, 0.12), rgba(99, 207, 165, 0));
    border: 1px solid rgba(99, 207, 165, 0.35);
  }
  .notes-page .success-banner .icon {
    font-size: 1.6rem;
  }
  .notes-page .two-cols {
    display: grid;
    gap: clamp(18px, 2.6vw, 30px);
    grid-template-columns: minmax(320px, 1.2fr) minmax(260px, 1fr);
    align-items: start;
  }
  .notes-page .form-card h2 {
    margin-bottom: 6px;
  }
  .notes-page .form-card .small {
    color: var(--muted-color);
    font-size: 0.9rem;
  }
  .notes-page .form-field {
    margin-top: 18px;
  }
  .notes-page label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: var(--muted-color);
    font-size: 0.92rem;
  }
  .notes-page input[type="text"],
  .notes-page textarea {
    width: 100%;
    border-radius: 14px;
    border: 1px solid var(--input-border);
    background: var(--input-bg);
    padding: 12px 16px;
    font-size: 1rem;
    color: var(--text-color);
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.3s ease;
  }
  .notes-page input[type="text"]::placeholder {
    color: rgba(109, 91, 118, 0.55);
  }
  .notes-page input[type="text"]:focus,
  .notes-page textarea:focus {
    border-color: var(--input-focus);
    box-shadow: 0 0 0 3px rgba(255, 138, 195, 0.18);
    outline: none;
  }
  .notes-page .toolbar {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 18px;
  }
  .notes-page .toolbar .btn {
    padding: 8px 12px;
    font-size: 0.9rem;
    background: var(--btn-ghost-bg);
    color: var(--text-color);
    border: 1px solid transparent;
    box-shadow: none;
  }
  .notes-page .toolbar .btn:hover {
    border-color: rgba(255, 138, 195, 0.4);
    background: var(--accent-soft);
  }
  .notes-page .toolbar .btn:focus-visible {
    outline: 2px solid var(--accent);
    outline-offset: 1px;
  }
  .notes-page .rte {
    min-height: 240px;
    border-radius: 16px;
    border: 1px solid var(--input-border);
    margin-top: 12px;
    padding: 16px;
    background: var(--input-bg);
    color: var(--text-color);
    font-size: 1rem;
    line-height: 1.6;
    box-shadow: inset 0 1px 6px rgba(0, 0, 0, 0.03);
    overflow-y: auto;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.3s ease;
  }
  .notes-page .rte:focus {
    border-color: var(--input-focus);
    box-shadow: 0 0 0 3px rgba(255, 138, 195, 0.18);
    outline: none;
  }
  .notes-page .hint {
    display: block;
    margin-top: 8px;
    font-size: 0.85rem;
    color: var(--muted-color);
  }
  .notes-page .preview-card {
    position: sticky;
    top: 40px;
  }
  .notes-page .preview-card .preview-wrapper {
    background: var(--preview-bg);
    border-radius: 18px;
    padding: 18px;
    border: 1px solid rgba(255, 255, 255, 0.55);
    box-shadow: inset 0 0 0 1px rgba(255, 138, 195, 0.1), 0 25px 45px rgba(39, 17, 32, 0.08);
  }
  .notes-page.theme-dark .preview-card .preview-wrapper {
    border-color: rgba(255, 132, 200, 0.18);
    box-shadow: inset 0 0 0 1px rgba(255, 132, 200, 0.12), 0 25px 55px rgba(0, 0, 0, 0.36);
  }
  .notes-page .preview-title {
    color: var(--text-color);
    font-size: 1.05rem;
    font-weight: 600;
    margin-bottom: 6px;
  }
  .notes-page .preview-content {
    margin-top: 12px;
    border-radius: 14px;
    padding: 14px;
    background: rgba(255, 255, 255, 0.75);
    color: var(--preview-text);
    max-height: 320px;
    overflow: auto;
    font-size: 0.96rem;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.45);
  }
  .notes-page.theme-dark .preview-content {
    background: rgba(12, 7, 20, 0.92);
    box-shadow: inset 0 0 0 1px rgba(255, 132, 200, 0.12);
  }
  .notes-page .preview-empty {
    padding: 16px;
    background: var(--accent-soft);
    border-radius: 16px;
    color: var(--muted-color);
    border: 1px dashed rgba(255, 138, 195, 0.3);
  }
  .notes-page .preview-date {
    margin-top: 12px;
    font-size: 0.85rem;
    color: var(--muted-color);
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .notes-page .notes-archive {
    margin-top: 26px;
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    box-shadow: var(--card-shadow);
  }
  .notes-page .notes-archive-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 18px;
  }
  .notes-page .notes-archive-head .small {
    margin: 6px 0 0 0;
  }
  .notes-page .notes-count {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 999px;
    background: var(--accent-soft);
    color: var(--muted-color);
    font-weight: 600;
    font-size: 0.9rem;
    white-space: nowrap;
  }
  .notes-page .notes-count::before {
    content: "◎";
    font-size: 0.7rem;
  }
  .notes-page .notes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
  }
  .notes-page .note-item {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 18px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.55);
    border: 1px solid rgba(255, 138, 195, 0.18);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    min-height: 180px;
  }
  .notes-page .note-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 18px 35px var(--accent-shadow);
  }
  .notes-page.theme-dark .note-item {
    background: rgba(13, 9, 24, 0.8);
    border-color: rgba(255, 132, 200, 0.2);
    box-shadow: inset 0 0 0 1px rgba(255, 132, 200, 0.12);
  }
  .notes-page .note-item-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
  }
  .notes-page .note-title {
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--text-color);
    margin: 0;
  }
  .notes-page .note-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.78rem;
    background: rgba(255, 255, 255, 0.5);
    color: var(--muted-color);
    white-space: nowrap;
  }
  .notes-page.theme-dark .note-tag {
    background: rgba(255, 132, 200, 0.15);
    color: rgba(238, 227, 255, 0.7);
  }
  .notes-page .note-excerpt {
    color: var(--muted-color);
    font-size: 0.92rem;
    line-height: 1.6;
    margin: 0;
  }
  .notes-page .note-excerpt.empty {
    font-style: italic;
    opacity: 0.7;
  }
  .notes-page .note-meta {
    margin-top: auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    font-size: 0.85rem;
    color: var(--muted-color);
  }
  .notes-page .notes-empty {
    padding: 20px;
    border-radius: 16px;
    background: var(--accent-soft);
    border: 1px dashed rgba(255, 138, 195, 0.4);
    color: var(--muted-color);
  }
  .notes-page .submit-row {
    margin-top: 24px;
    display: flex;
    justify-content: flex-end;
  }
  .notes-page .submit-row .btn {
    min-width: 140px;
  }
  .notes-page .small {
    font-size: 0.88rem;
    color: var(--muted-color);
  }
  .notes-page noscript {
    display: block;
    margin-top: 12px;
    font-size: 0.85rem;
    color: #d77f7f;
  }
  .notes-page .footer {
    text-align: center;
    margin-top: 24px;
    color: var(--muted-color);
    font-size: 0.85rem;
  }
  .notes-page .preview-content::-webkit-scrollbar,
  .notes-page .rte::-webkit-scrollbar {
    width: 8px;
  }
  .notes-page .preview-content::-webkit-scrollbar-track,
  .notes-page .rte::-webkit-scrollbar-track {
    background: transparent;
  }
  .notes-page .preview-content::-webkit-scrollbar-thumb,
  .notes-page .rte::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.18);
    border-radius: 10px;
  }
  .notes-page.theme-dark .preview-content::-webkit-scrollbar-thumb,
  .notes-page.theme-dark .rte::-webkit-scrollbar-thumb {
    background: rgba(255, 132, 200, 0.35);
  }
  @media (max-width: 1024px) {
    .notes-page .two-cols {
      grid-template-columns: 1fr;
    }
    .notes-page .preview-card {
      position: relative;
      top: unset;
    }
    .notes-page .menu {
      width: 100%;
      justify-content: space-between;
    }
    .notes-page .nav-links {
      justify-content: flex-start;
    }
    .notes-page .theme-toggle {
      padding-left: 0;
      border-left: none;
      width: 100%;
      justify-content: flex-start;
      gap: 12px;
    }
    .notes-page .notes-grid {
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
  }
  @media (max-width: 640px) {
    .notes-page {
      padding: 18px;
    }
    .notes-page .topnav {
      flex-direction: column;
      align-items: stretch;
    }
    .notes-page .menu {
      flex-direction: column;
      align-items: stretch;
      gap: 10px;
    }
    .notes-page .nav-links {
      flex-direction: column;
      align-items: stretch;
      gap: 8px;
    }
    .notes-page .nav-divider {
      display: none;
    }
    .notes-page .theme-toggle {
      flex-direction: row;
      flex-wrap: wrap;
      gap: 10px;
    }
    .notes-page .status-row {
      flex-direction: column;
      align-items: flex-start;
    }
    .notes-page .submit-row {
      justify-content: stretch;
    }
    .notes-page .submit-row .btn {
      width: 100%;
    }
    .notes-page .notes-grid {
      grid-template-columns: 1fr;
    }
  }
</style>
</head>
<body class="notes-page theme-pink theme-transition-block">
<div class="container">
  <div class="card topnav">
    <div class="nav-left">
      <a class="btn btn-ghost brand-link" href="<?= base_url('index.php') ?>">
        🌸 <span class="brand"><?= e(APP_NAME) ?></span>
      </a>
      <span class="user-chip">Merhaba, <?= e($user['username']) ?></span>
    </div>
    <div class="menu">
      <nav class="nav-links" aria-label="Ana menü">
        <a class="nav-link nav-link-primary is-active" href="<?= base_url('notes.php') ?>">✨ Not Yaz</a>
        <a class="nav-link" href="<?= base_url('notes.php') ?>#notes-archive">📒 Notlarım</a>
        <span class="nav-divider" aria-hidden="true"></span>
        <a class="nav-link" href="<?= base_url('dashboard.php') ?>">📊 Panel</a>
        <a class="nav-link" href="<?= base_url('books.php') ?>">📚 Kitaplarım</a>
        <a class="nav-link" href="<?= base_url('eglence.php') ?>">🎉 Eğlence</a>
        <a class="nav-link" href="<?= base_url('designer_cover.php') ?>">🎨 Kapak</a>
        <a class="nav-link" href="<?= base_url('designer_map.php') ?>">🗺 Harita</a>
        <a class="nav-link" href="<?= base_url('../profil/profil_advanced.php' . ($user ? '?id=' . $user['id'] : '')) ?>">👤 Profilim</a>
        <a class="nav-link" href="<?= base_url('logout.php') ?>">🚪 Çıkış</a>
      </nav>
      <div class="theme-toggle" role="group" aria-label="Tema seçimleri">
        <button type="button" class="btn btn-ghost theme-btn is-active" data-theme-btn="pink">Pembe Tema</button>
        <button type="button" class="btn btn-ghost theme-btn" data-theme-btn="dark">Siyah Tema</button>
      </div>
    </div>
  </div>

  <?php if ($created_note): ?>
    <div class="card success-banner">
      <div class="icon">✨</div>
      <div>
        <strong>Not kaydedildi!</strong>
        <p class="small" style="margin-top:4px;">Önizleme kartı sağda güncellendi. Harika bir adım attın ✨</p>
      </div>
    </div>
  <?php endif; ?>

  <div class="status-row">
    <div id="draft-status" class="status-chip">
      <span class="dot" aria-hidden="true"></span>
      Taslak hazır
    </div>
    <div id="content-counter" class="status-chip muted">0 kelime · 0 karakter</div>
  </div>

  <div class="two-cols">
    <div class="card form-card">
      <h2>Not Yaz</h2>
      <p class="small">Zengin metin düzenleyici ve otomatik taslak ile fikirlerin güvende.</p>

      <form method="post" id="note-form" action="<?= e(base_url('notes.php')) ?>" data-draft-key="notes_draft_user_<?= (int) $uid ?>">
        <?= csrf_field() ?>

        <div class="toolbar" aria-label="Metin düzenleme araçları">
          <button type="button" class="btn btn-ghost" data-cmd="bold" title="Kalın (Ctrl+B)"><strong>B</strong></button>
          <button type="button" class="btn btn-ghost" data-cmd="italic" title="İtalik (Ctrl+I)"><em>İ</em></button>
          <button type="button" class="btn btn-ghost" data-cmd="underline" title="Altı çizili (Ctrl+U)"><span style="text-decoration:underline;">A</span></button>
          <button type="button" class="btn btn-ghost" data-cmd="h1" title="Başlık ekle">Başlık</button>
          <button type="button" class="btn btn-ghost" data-cmd="ul" title="Madde işaretli liste">Liste</button>
        </div>

        <div class="form-field">
          <label for="note-title">Başlık</label>
          <input id="note-title" name="title" type="text" placeholder="Bugün ne üzerine not alacaksın?" autocomplete="off">
        </div>

        <div class="form-field">
          <label for="rte">İçerik</label>
          <div id="rte" class="rte" contenteditable="true" spellcheck="true" aria-label="Not içeriği"></div>
          <span class="hint">Metnini biçimlendirmek için yukarıdaki araçları kullanabilir, görselleri yapıştırabilirsin.</span>
        </div>

        <input type="hidden" name="content" id="content-hidden">
        <textarea name="rte_fallback" id="rte-fallback" style="display:none"></textarea>

        <div class="submit-row">
          <button class="btn" type="submit">Kaydet</button>
        </div>
      </form>

      <noscript>
        JavaScript kapalıysa taslak kaydı ve zengin düzenleme çalışmaz.
      </noscript>
    </div>

    <div class="card preview-card">
      <h2>Önizleme</h2>
      <p class="small">“Kaydet”ten sonra son notun burada mini kart olarak görünür.</p>

      <?php if ($created_note): ?>
        <div class="preview-wrapper" aria-live="polite">
          <div class="preview-title"><?= e($created_note['title']) ?></div>
          <div class="preview-content"><?= $created_note['content'] ?></div>
          <div class="preview-date">🗓 <?= e(date('d.m.Y H:i', strtotime($created_note['updated_at'] ?? 'now'))) ?></div>
        </div>
      <?php else: ?>
        <div class="preview-empty">
          Henüz bir not kaydetmedin. Sol taraftan yazmaya başla, kaydettiğinde burada şık bir önizleme göreceksin.
        </div>
      <?php endif; ?>
    </div>
  </div>

  <section id="notes-archive" class="card notes-archive">
    <div class="notes-archive-head">
      <div>
        <h2>Notlarım</h2>
        <p class="small">Tüm notların en güncelden eskiye sıralanıyor.</p>
      </div>
      <span class="notes-count"><?= e(number_format($notesCount, 0, ',', '.')) ?> not</span>
    </div>
    <?php if (!$notesCount): ?>
      <div class="notes-empty">
        Henüz kaydedilmiş not yok. Sol taraftan yazdığın ilk not burada listelenmeye başlayacak.
      </div>
    <?php else: ?>
      <div class="notes-grid">
        <?php foreach ($notes as $note): ?>
          <?php
            $plain = trim(preg_replace('/\s+/', ' ', strip_tags($note['content'] ?? '')));
            if ($plain === '') {
                $excerpt = '';
                $hasMore = false;
            } else {
                if (function_exists('mb_substr')) {
                    $excerpt = mb_substr($plain, 0, 160, 'UTF-8');
                    $hasMore = function_exists('mb_strlen') ? mb_strlen($plain, 'UTF-8') > 160 : strlen($plain) > 160;
                } else {
                    $excerpt = substr($plain, 0, 160);
                    $hasMore = strlen($plain) > 160;
                }
                if ($hasMore) {
                    $excerpt .= '…';
                }
            }
            $displayDate = $note['updated_at'] ?? ($note['created_at'] ?? 'now');
          ?>
          <article class="note-item">
            <div class="note-item-head">
              <h3 class="note-title"><?= e($note['title']) ?></h3>
              <?php if (!empty($note['book_title'])): ?>
                <span class="note-tag">📚 <?= e($note['book_title']) ?></span>
              <?php endif; ?>
            </div>
            <?php if ($excerpt !== ''): ?>
              <p class="note-excerpt"><?= e($excerpt) ?></p>
            <?php else: ?>
              <p class="note-excerpt empty">İçerik henüz eklenmemiş.</p>
            <?php endif; ?>
            <div class="note-meta">
              <span class="note-date">🕒 <?= e(date('d.m.Y H:i', strtotime($displayDate))) ?></span>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <div class="footer">
    © <?= date('Y') ?> <?= e(APP_NAME) ?> · Üretkenliğin için tasarlandı.
  </div>
</div>

<script src="../assets/js/notes_editor.js?v=3"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    const themeButtons = document.querySelectorAll('[data-theme-btn]');
    const themeMeta = document.querySelector('meta[name="theme-color"]');
    const THEME_KEY = 'notes-theme';
    const THEME_META_COLORS = { pink: '#fdd7ed', dark: '#09060f' };

    const applyTheme = (theme, opts) => {
      if (!theme) { return; }
      body.classList.remove('theme-pink', 'theme-dark');
      body.classList.add('theme-' + theme);
      themeButtons.forEach(btn => {
        btn.classList.toggle('is-active', btn.dataset.themeBtn === theme);
      });
      if (!opts || !opts.skipSave) {
        try {
          localStorage.setItem(THEME_KEY, theme);
        } catch (err) {
          console.warn('Tema tercihi kaydedilemedi:', err);
        }
      }
      if (themeMeta && THEME_META_COLORS[theme]) {
        themeMeta.setAttribute('content', THEME_META_COLORS[theme]);
      }
    };

    let initialTheme = 'pink';
    try {
      const stored = localStorage.getItem(THEME_KEY);
      if (stored) {
        initialTheme = stored;
      } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        initialTheme = 'dark';
      }
    } catch (err) {
      initialTheme = 'pink';
    }
    applyTheme(initialTheme, { skipSave: true });
    requestAnimationFrame(() => body.classList.remove('theme-transition-block'));

    themeButtons.forEach(btn => {
      btn.addEventListener('click', () => applyTheme(btn.dataset.themeBtn));
    });

    const rte = document.getElementById('rte');
    const hidden = document.getElementById('content-hidden');
    const fallback = document.getElementById('rte-fallback');
    const counter = document.getElementById('content-counter');
    const form = document.getElementById('note-form');

    const updateCounter = () => {
      if (!counter || !rte) { return; }
      const plain = rte.innerText.replace(/\s+/g, ' ').trim();
      const words = plain.length ? plain.split(' ').length : 0;
      const chars = plain.length;
      counter.textContent = words + ' kelime · ' + chars + ' karakter';
    };

    const syncContent = () => {
      if (hidden && rte) {
        hidden.value = rte.innerHTML.trim();
      }
      if (fallback && rte) {
        fallback.value = rte.innerText.trim();
      }
    };

    if (rte) {
      rte.addEventListener('input', () => {
        syncContent();
        updateCounter();
      });
      rte.addEventListener('blur', updateCounter);
    }

    if (form) {
      form.addEventListener('submit', syncContent);
    }

    updateCounter();
  });
</script>
</body>
</html>
