<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';

require_login();
$user = current_user();

$navLinks = [
    ['icon' => '🏠', 'label' => 'Panel', 'href' => 'dashboard.php'],
    ['icon' => '📚', 'label' => 'Kitaplarım', 'href' => 'books.php'],
    ['icon' => '📝', 'label' => 'Notlarım', 'href' => 'notes.php'],
    ['icon' => '🎉', 'label' => 'Eğlence', 'href' => 'eglence.php'],
    ['icon' => '🎨', 'label' => 'Kapak', 'href' => 'designer_cover.php'],
    ['icon' => '🗺️', 'label' => 'Harita', 'href' => 'designer_map.php'],
    ['icon' => '🚪', 'label' => 'Çıkış', 'href' => 'logout.php'],
];
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(APP_NAME) ?> — Eğlence Stüdyosu</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    :root {
      --pink-50: #fff6fb;
      --pink-200: #ffe6f5;
      --pink-400: #f9b6d9;
      --violet-200: #e1d6ff;
      --violet-400: #b79eff;
      --text-base: #252543;
      --text-muted: rgba(37, 37, 67, 0.68);
      --glass-light: rgba(255, 255, 255, 0.78);
      --glass-dark: rgba(24, 22, 44, 0.78);
    }

    body.playground-page {
      font-family: 'Inter', 'Segoe UI', Tahoma, sans-serif;
      margin: 0;
      min-height: 100vh;
      padding: 44px 18px 60px;
      color: var(--text-base);
      background:
        radial-gradient(circle at 12% -12%, rgba(255, 206, 242, 0.75), transparent 55%),
        radial-gradient(circle at 90% 0%, rgba(195, 223, 255, 0.7), transparent 50%),
        linear-gradient(135deg, #f9f6ff 0%, #f5edff 40%, #ffe9f6 100%);
      transition: background 0.45s ease, color 0.45s ease;
    }

    body.playground-page::before,
    body.playground-page::after {
      content: '';
      position: fixed;
      width: 430px;
      height: 430px;
      border-radius: 50%;
      filter: blur(140px);
      opacity: 0.28;
      pointer-events: none;
      z-index: 0;
    }

    body.playground-page::before {
      top: -150px;
      left: -120px;
      background: linear-gradient(135deg, rgba(255, 156, 214, 0.7), rgba(255, 228, 247, 0.55));
    }

    body.playground-page::after {
      bottom: -170px;
      right: -140px;
      background: linear-gradient(135deg, rgba(160, 140, 255, 0.65), rgba(102, 186, 255, 0.55));
    }

    body.playground-page.dark-theme {
      color: #f5ecff;
      background:
        radial-gradient(circle at 15% -10%, rgba(90, 63, 140, 0.55), transparent 55%),
        radial-gradient(circle at 92% 6%, rgba(232, 90, 146, 0.45), transparent 60%),
        linear-gradient(135deg, #0e0b1a 0%, #141024 45%, #1c1633 100%);
    }

    body.playground-page.dark-theme::before,
    body.playground-page.dark-theme::after {
      opacity: 0.12;
    }

    .playground-shell {
      position: relative;
      z-index: 1;
      max-width: 1120px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    .glass-panel {
      background: var(--glass-light);
      border-radius: 26px;
      border: 1px solid rgba(255, 255, 255, 0.6);
      padding: 26px 30px;
      box-shadow: 0 26px 60px rgba(176, 144, 255, 0.18);
      backdrop-filter: blur(22px);
      transition: transform 0.28s ease, box-shadow 0.28s ease;
    }

    .glass-panel:hover {
      transform: translateY(-3px);
      box-shadow: 0 34px 72px rgba(176, 144, 255, 0.24);
    }

    body.playground-page.dark-theme .glass-panel {
      background: var(--glass-dark);
      border: 1px solid rgba(118, 96, 210, 0.35);
      box-shadow: 0 30px 70px rgba(8, 6, 20, 0.68);
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
      font-size: 1.6rem;
      font-weight: 700;
      text-decoration: none;
      color: inherit;
    }

    .brand-link .brand-name {
      background: linear-gradient(135deg, #ff85d1, #a27dff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .nav-line {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      align-items: center;
    }

    .nav-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 9px 16px;
      border-radius: 999px;
      border: 1px solid rgba(255, 255, 255, 0.55);
      background: rgba(255, 255, 255, 0.72);
      font-weight: 600;
      text-decoration: none;
      color: inherit;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .nav-pill:hover {
      transform: translateY(-2px);
      box-shadow: 0 18px 34px rgba(176, 144, 255, 0.26);
    }

    .nav-pill.is-active {
      background: linear-gradient(135deg, #ffbade, #bea3ff);
      color: #fff;
      box-shadow: 0 18px 36px rgba(176, 144, 255, 0.32);
    }

    body.playground-page.dark-theme .nav-pill {
      background: rgba(24, 22, 44, 0.82);
      border-color: rgba(118, 96, 210, 0.32);
    }

    .theme-toggle {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 10px 18px;
      border-radius: 999px;
      border: 1px solid rgba(255, 255, 255, 0.65);
      background: linear-gradient(135deg, #f4caff, #ffb6d5);
      color: #452a5b;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 20px 36px rgba(198, 137, 255, 0.26);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .theme-toggle:hover {
      transform: translateY(-2px);
      box-shadow: 0 26px 52px rgba(198, 137, 255, 0.32);
    }

    body.playground-page.dark-theme .theme-toggle {
      background: rgba(27, 23, 46, 0.9);
      border: 1px solid rgba(118, 96, 210, 0.4);
      color: #f5ecff;
      box-shadow: 0 22px 48px rgba(5, 4, 12, 0.6);
    }

    .hero h1 {
      margin: 0 0 12px;
      font-size: clamp(1.9rem, 2.6vw, 2.4rem);
    }

    .hero p {
      margin: 0;
      max-width: 560px;
      line-height: 1.6;
      color: var(--text-muted);
    }

    body.playground-page.dark-theme .hero p {
      color: rgba(236, 224, 255, 0.74);
    }

    .tool-summary {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 14px;
    }

    .tool-chip {
      padding: 7px 14px;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.78);
      border: 1px solid rgba(255, 255, 255, 0.55);
      font-size: 0.9rem;
      font-weight: 600;
      color: var(--text-muted);
    }

    body.playground-page.dark-theme .tool-chip {
      background: rgba(24, 22, 44, 0.82);
      border-color: rgba(118, 96, 210, 0.28);
      color: rgba(232, 220, 255, 0.78);
    }

    .play-grid {
      display: grid;
      gap: 22px;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    }

    .play-card h3 {
      margin: 0 0 12px;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 1.24rem;
      color: #66356d;
    }

    body.playground-page.dark-theme .play-card h3 {
      color: #ffd6f0;
    }

    .note {
      margin: 10px 0 0;
      font-size: 0.92rem;
      color: var(--text-muted);
    }

    body.playground-page.dark-theme .note {
      color: rgba(236, 224, 255, 0.7);
    }

    .output-box {
      min-height: 34px;
      margin: 16px 0 12px;
      padding: 12px;
      border-radius: 14px;
      border: 1px solid rgba(223, 173, 239, 0.3);
      background: rgba(255, 255, 255, 0.78);
      font-weight: 600;
      line-height: 1.6;
      color: #5b366c;
    }

    body.playground-page.dark-theme .output-box {
      background: rgba(24, 22, 44, 0.82);
      border: 1px solid rgba(118, 96, 210, 0.25);
      color: #f4e9ff;
    }

    .btn-row {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
    }

    .pill-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 11px 18px;
      border-radius: 12px;
      border: 1px solid rgba(223, 173, 239, 0.4);
      background: linear-gradient(135deg, #dea8ff, #ffb6d4);
      color: #fff;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      position: relative;
      overflow: hidden;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .pill-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 20px 36px rgba(198, 137, 255, 0.3);
    }

    .pill-btn::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.28);
      transform: translate(-50%, -50%);
      transition: width 0.5s ease, height 0.5s ease;
    }

    .pill-btn:hover::after {
      width: 260px;
      height: 260px;
    }

    .pill-btn.secondary {
      background: rgba(255, 255, 255, 0.86);
      color: var(--text-base);
      border: 1px solid rgba(223, 173, 239, 0.32);
      box-shadow: none;
    }

    .pill-btn.secondary:hover {
      box-shadow: 0 16px 28px rgba(198, 137, 255, 0.18);
    }

    body.playground-page.dark-theme .pill-btn {
      background: linear-gradient(135deg, #352d5d, #221c3f);
      border: 1px solid rgba(118, 96, 210, 0.32);
    }

    body.playground-page.dark-theme .pill-btn.secondary {
      background: rgba(25, 22, 45, 0.82);
      color: #f1e5ff;
    }

    .token-chip {
      display: inline-flex;
      align-items: center;
      padding: 8px 16px;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.82);
      border: 1px solid rgba(223, 173, 239, 0.32);
      font-weight: 600;
      color: #64366e;
    }

    body.playground-page.dark-theme .token-chip {
      background: rgba(27, 24, 45, 0.82);
      border: 1px solid rgba(118, 96, 210, 0.28);
      color: #ffd6f1;
    }

    label.toggle-label {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.9rem;
      color: var(--text-muted);
    }

    body.playground-page.dark-theme label.toggle-label {
      color: rgba(236, 224, 255, 0.74);
    }

    input[type="checkbox"] {
      appearance: none;
      width: 20px;
      height: 20px;
      border: 2px solid rgba(223, 173, 239, 0.6);
      border-radius: 6px;
      background: rgba(255, 255, 255, 0.85);
      cursor: pointer;
      transition: all 0.25s ease;
    }

    input[type="checkbox"]:checked {
      background: linear-gradient(135deg, #dea8ff, #ffb6d4);
      border-color: #dea8ff;
    }

    input[type="checkbox"]:checked::after {
      content: '✓';
      display: block;
      text-align: center;
      color: #fff;
      font-weight: 700;
      font-size: 14px;
      line-height: 18px;
    }

    body.playground-page.dark-theme input[type="checkbox"] {
      border-color: rgba(118, 96, 210, 0.35);
      background: rgba(26, 23, 44, 0.82);
    }

    body.playground-page.dark-theme input[type="checkbox"]:checked {
      background: linear-gradient(135deg, #6e4fff, #a27dff);
      border-color: #8465ff;
    }

    .footer-note {
      text-align: center;
      font-size: 0.86rem;
      color: rgba(34, 36, 70, 0.6);
    }

    body.playground-page.dark-theme .footer-note {
      color: rgba(236, 224, 255, 0.58);
    }

    @media (max-width: 768px) {
      body.playground-page {
        padding: 30px 14px 48px;
      }

      .glass-panel {
        padding: 22px;
      }

      .nav-line {
        gap: 6px;
      }
    }
  </style>
</head>
<body class="playground-page">
  <div class="playground-shell">
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
          <a class="nav-pill<?= $item['href'] === 'eglence.php' ? ' is-active' : '' ?>" href="<?= base_url($item['href']) ?>">
            <span><?= $item['icon'] ?></span><?= $item['label'] ?>
          </a>
        <?php endforeach; ?>
      </div>
    </header>

    <section class="glass-panel hero">
      <h1>🎉 Eğlence Stüdyosu</h1>
      <p>Yaratıcı kaslarını ısıt, yazar blokajını kır ve sahnelerini renklendir. Craftrolle eğlence stüdyosu her tıklamada yeni bir kombinasyon sunar.</p>
      <div class="tool-summary">
        <span class="tool-chip">🎲 Yaratıcı Zar</span>
        <span class="tool-chip">🧩 3 Kelime</span>
        <span class="tool-chip">✍️ İlk Cümle</span>
        <span class="tool-chip">🧑‍🤝‍🧑 İsim Üret</span>
        <span class="tool-chip">🗂️ Olay Kartı</span>
        <span class="tool-chip">🎡 Duygu Tekerleği</span>
      </div>
    </section>

    <section class="play-grid">
      <article class="glass-panel play-card">
        <h3>🎲 Yaratıcı Zar</h3>
        <div id="zar-out" class="output-box"></div>
        <div class="btn-row">
          <button class="pill-btn" id="zar-btn">Zar At</button>
          <button class="pill-btn secondary" id="zar-reset" title="Tüm kombinasyonları yeniden karıştır">Sıfırla</button>
        </div>
        <p class="note">Karakter, mekân ve çatışma üçlüsüyle anında sahne kur.</p>
      </article>

      <article class="glass-panel play-card">
        <h3>🧩 3 Kelime</h3>
        <div id="kelime-row" class="btn-row"></div>
        <div class="btn-row">
          <button class="pill-btn" id="kelime-yeni">Yeni 3 Kelime</button>
          <button class="pill-btn secondary" id="kelime-reset" title="Kelime destesini sıfırla">Sıfırla</button>
        </div>
        <p class="note">Üç kelimeyle doğaçla, kısa cümleler ve sahneler çıkar.</p>
      </article>

      <article class="glass-panel play-card">
        <h3>✍️ İlk Cümle</h3>
        <div id="baslatici-out" class="output-box"></div>
        <div class="btn-row">
          <button class="pill-btn" id="baslatici-yeni">Yeni Başlatıcı</button>
          <button class="pill-btn secondary" id="baslatici-reset" title="Başlatıcı havuzunu sıfırlar">Sıfırla</button>
        </div>
        <p class="note">Yazmaya başlamanı kolaylaştıracak sürpriz giriş cümleleri.</p>
      </article>

      <article class="glass-panel play-card">
        <h3>🧑‍🤝‍🧑 İsim Üret</h3>
        <div id="isim-out" class="output-box"></div>
        <div class="btn-row">
          <button class="pill-btn secondary" id="isim-tr">Türkçe</button>
          <button class="pill-btn secondary" id="isim-en">English</button>
          <button class="pill-btn" id="isim-yeni">Yeni İsim</button>
          <button class="pill-btn secondary" id="isim-reset" title="Görülen isim listesini temizler">Sıfırla</button>
        </div>
        <p class="note">Türkçe ve İngilizce benzersiz karakter isimlerini keşfet.</p>
      </article>

      <article class="glass-panel play-card">
        <h3>🗂️ Olay Kartı</h3>
        <div id="olay-out" class="output-box"></div>
        <div class="btn-row">
          <button class="pill-btn" id="olay-yeni">Yeni Olay Kartı</button>
          <label class="toggle-label">EN <input type="checkbox" id="olay-en"></label>
          <button class="pill-btn secondary" id="olay-reset" title="Tüm desteleri sıfırla">Sıfırla</button>
        </div>
        <p class="note">Yer, zaman, hava ve olay tetikleyicileri ile sahneyi renklendir.</p>
      </article>

      <article class="glass-panel play-card">
        <h3>🎡 Duygu Tekerleği</h3>
        <div id="duygu-row" class="btn-row"></div>
        <div id="duygu-out" class="output-box"></div>
        <div class="btn-row">
          <button class="pill-btn" id="duygu-random">Rastgele Duygu</button>
          <button class="pill-btn secondary" id="duygu-reset" title="Tüm duygu destelerini sıfırla">Sıfırla</button>
        </div>
        <p class="note">Karakterinin iç dünyasını farklı duygu tonlarıyla keşfet.</p>
      </article>
    </section>

    <footer class="footer-note">© <?= date('Y') ?> <?= e(APP_NAME) ?> · Craftrolle eğlence stüdyosu</footer>
  </div>

  <!-- SCRIPTS -->
<script>
(function(){
  const toggleBtn = document.getElementById('theme-toggle');
  const icon = document.getElementById('theme-icon');
  const text = document.getElementById('theme-text');
  const storageKey = 'craft-playground-theme';

  function apply(mode) {
    const isDark = mode === 'dark';
    document.body.classList.toggle('dark-theme', isDark);
    icon.textContent = isDark ? '🌙' : '🌸';
    text.textContent = isDark ? 'Siyah' : 'Pembe';
    toggleBtn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
    localStorage.setItem(storageKey, mode);
  }

  const saved = localStorage.getItem(storageKey);
  apply(saved === 'dark' ? 'dark' : 'light');

  toggleBtn.addEventListener('click', () => {
    const next = document.body.classList.contains('dark-theme') ? 'light' : 'dark';
    apply(next);
  });
})();
</script>

<script>
(function(){
  const $ = (selector) => document.querySelector(selector);

  (function(){
    const DECK_KEY = 'kk_zar_offline_deck_v1';
    const kisiler = ["genç yazar","tuhaf komşu","gece bekçisi","eski öğretmen","balıkçı","kütüphaneci","postacı","mimar","pazarcı","fotoğrafçı","müzisyen","gazeteci","kalaycı","terzi","saatçi"];
    const mekanlar = ["kıyı kasabası","eski sinema","pazar yeri","kütüphane","istasyon peronu","rüzgârlı tepe","sahaf dükkânı","pasaj içi","sarnıç","çarşı içi","ıssız park","liman iskelesi","çatı katı","eski han","depo"];
    const catismalar = ["zaman daralır","sır ortaya çıkar","yol kapanır","yanlış anlaşılma büyür","elektrikler kesilir","anahtar uymuyor","tanık kaybolur","yağmur bastırır","beklenmedik misafir gelir","sinyal kesilir","plan deşifre olur","izler silinir","gürültü şikâyeti büyür","şifre yanlış çıkar","görev el değiştirir"];

    function jget(key, fallback) {
      try { return JSON.parse(localStorage.getItem(key) || JSON.stringify(fallback)); } catch (_) { return fallback; }
    }

    function jset(key, value) {
      localStorage.setItem(key, JSON.stringify(value));
    }

    function shuffle(arr) {
      const copy = arr.slice();
      for (let i = copy.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [copy[i], copy[j]] = [copy[j], copy[i]];
      }
      return copy;
    }

    function buildDeck() {
      const stack = [];
      for (const a of kisiler) {
        for (const b of mekanlar) {
          for (const c of catismalar) {
            stack.push({ a, b, c });
          }
        }
      }
      return shuffle(stack);
    }

    function ensureDeck() {
      let state = jget(DECK_KEY, null);
      if (!(state && Array.isArray(state.remaining) && state.remaining.length)) {
        state = { remaining: buildDeck() };
        jset(DECK_KEY, state);
      }
      return state;
    }

    function roll() {
      const out = document.getElementById('zar-out');
      if (!out) return;
      const state = ensureDeck();
      if (!state.remaining.length) {
        out.textContent = 'Havuz bitti. Sıfırla ile karıştır.';
        return;
      }
      const pick = state.remaining.pop();
      jset(DECK_KEY, state);
      out.textContent = `Karakter: ${pick.a} · Mekân: ${pick.b} · Çatışma: ${pick.c}`;
    }

    document.getElementById('zar-btn')?.addEventListener('click', roll);
    document.getElementById('zar-reset')?.addEventListener('click', () => {
      localStorage.removeItem(DECK_KEY);
      const out = document.getElementById('zar-out');
      if (out) out.textContent = 'Havuz sıfırlandı.';
    });
    if (document.getElementById('zar-out')) roll();
  })();
  (function(){
    const WORDS_URL = 'api/words_tr.json';
    const WORDS_KEY = 'kk_words_deck_v1';
    const WORDS_SIG = 'kk_words_sig_v1';

    function jget(key, fallback) {
      try { return JSON.parse(localStorage.getItem(key) || JSON.stringify(fallback)); } catch (_) { return fallback; }
    }

    function jset(key, value) {
      localStorage.setItem(key, JSON.stringify(value));
    }

    function shuffle(arr) {
      const copy = arr.slice();
      for (let i = copy.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [copy[i], copy[j]] = [copy[j], copy[i]];
      }
      return copy;
    }

    async function loadWords() {
      const res = await fetch(WORDS_URL, { cache: 'no-store' });
      if (!res.ok) throw new Error('words ' + res.status);
      const list = await res.json();
      if (!Array.isArray(list) || list.length < 3) throw new Error('words too small');
      return list;
    }

    function makeSig(list) {
      return `${list.length}:${list[0]}:${list[list.length - 1]}`;
    }

    async function ensureDeck() {
      const prevSig = jget(WORDS_SIG, null);
      let deck = jget(WORDS_KEY, null);
      if (deck && Array.isArray(deck.remaining) && deck.remaining.length >= 3 && prevSig) {
        return deck;
      }
      const list = await loadWords();
      const sig = makeSig(list);
      const remaining = shuffle(list);
      const payload = { remaining };
      jset(WORDS_KEY, payload);
      jset(WORDS_SIG, sig);
      return payload;
    }

    async function nextTriple() {
      let state = jget(WORDS_KEY, null);
      if (!(state && Array.isArray(state.remaining) && state.remaining.length >= 3)) {
        state = await ensureDeck();
      }
      const triple = [];
      for (let i = 0; i < 3; i++) {
        triple.push(state.remaining.pop());
      }
      jset(WORDS_KEY, state);
      return triple;
    }

    async function renderTriple() {
      const row = document.getElementById('kelime-row');
      if (!row) return;
      row.innerHTML = '<span class="tool-chip">Yükleniyor...</span>';
      try {
        const words = await nextTriple();
        row.innerHTML = '';
        words.forEach(word => {
          const badge = document.createElement('span');
          badge.className = 'token-chip';
          badge.textContent = word;
          row.appendChild(badge);
        });
      } catch (err) {
        row.innerHTML = '<span class="tool-chip">Liste yüklenemedi.</span>';
      }
    }

    document.getElementById('kelime-yeni')?.addEventListener('click', renderTriple);
    document.getElementById('kelime-reset')?.addEventListener('click', () => {
      localStorage.removeItem(WORDS_KEY);
      localStorage.removeItem(WORDS_SIG);
      const row = document.getElementById('kelime-row');
      if (row) row.innerHTML = '<span class="tool-chip">Havuz sıfırlandı.</span>';
    });
    if (document.getElementById('kelime-row')) renderTriple();
  })();

  (function(){
    const STARTER_KEY = 'kk_bigdeck_starters_tr';
    const STARTER_URL = 'api/starters_tr.json';

    function jget(key, fallback) {
      try { return JSON.parse(localStorage.getItem(key) || JSON.stringify(fallback)); } catch (_) { return fallback; }
    }

    function jset(key, value) {
      localStorage.setItem(key, JSON.stringify(value));
    }

    function shuffle(arr) {
      const copy = arr.slice();
      for (let i = copy.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [copy[i], copy[j]] = [copy[j], copy[i]];
      }
      return copy;
    }

    async function ensureDeck() {
      let state = jget(STARTER_KEY, null);
      if (state && Array.isArray(state.remaining) && state.remaining.length) return state;
      const res = await fetch(STARTER_URL, { cache: 'no-store' });
      if (!res.ok) throw new Error('starter list ' + res.status);
      const list = await res.json();
      if (!Array.isArray(list) || !list.length) throw new Error('starter empty');
      state = { remaining: shuffle(list), ts: Date.now() };
      jset(STARTER_KEY, state);
      return state;
    }

    async function starterNext() {
      const state = await ensureDeck();
      if (!state.remaining.length) return { done: true, value: null };
      const pick = state.remaining.pop();
      jset(STARTER_KEY, state);
      return { done: false, value: pick };
    }

    async function newStarter() {
      const out = $('#baslatici-out');
      if (!out) return;
      out.textContent = 'Yükleniyor...';
      try {
        const res = await starterNext();
        out.textContent = res.done ? 'Havuz bitti. "Sıfırla" ya da listeyi büyüt.' : res.value;
      } catch (err) {
        out.textContent = 'Liste yüklenemedi.';
      }
    }

    document.getElementById('baslatici-yeni')?.addEventListener('click', newStarter);
    document.getElementById('baslatici-reset')?.addEventListener('click', () => {
      localStorage.removeItem(STARTER_KEY);
      const out = $('#baslatici-out');
      if (out) out.textContent = 'Havuz sıfırlandı.';
    });
    if ($('#baslatici-out')) newStarter();
  })();
  (function(){
    const SEEN_TR_KEY = 'kk_seen_names_tr';
    const SEEN_EN_KEY = 'kk_seen_names_en';
    const LIMIT = 2000;

    function jget(key, fallback) {
      try { return JSON.parse(localStorage.getItem(key) || JSON.stringify(fallback)); } catch (_) { return fallback; }
    }

    function jset(key, value) {
      localStorage.setItem(key, JSON.stringify(value));
    }

    function seen(lang) {
      return jget(lang === 'TR' ? SEEN_TR_KEY : SEEN_EN_KEY, []);
    }

    function addSeen(name, lang) {
      let list = seen(lang);
      list.push(name);
      if (list.length > LIMIT) list = list.slice(-LIMIT);
      jset(lang === 'TR' ? SEEN_TR_KEY : SEEN_EN_KEY, list);
    }

    function isSeen(name, lang) {
      return seen(lang).includes(name);
    }

    async function fetchBatch(lang, count = 50) {
      const nat = lang === 'TR' ? 'tr' : 'us,gb,ca,au,nz,ie';
      const url = `https://randomuser.me/api/?inc=name&noinfo=1&nat=${encodeURIComponent(nat)}&results=${count}`;
      const res = await fetch(url, { cache: 'no-store' });
      if (!res.ok) throw new Error('API ' + res.status);
      const data = await res.json();
      const names = (data.results || []).map(row => {
        const first = row.name?.first || '';
        const last = row.name?.last || '';
        const cap = (s) => (s ? s.charAt(0).toUpperCase() + s.slice(1) : '');
        return first && last ? `${cap(first)} ${cap(last)}` : null;
      }).filter(Boolean);
      return names;
    }

    async function nextName(lang) {
      for (let attempt = 0; attempt < 5; attempt++) {
        const batch = await fetchBatch(lang, 50);
        const fresh = batch.find(name => !isSeen(name, lang));
        if (fresh) {
          addSeen(fresh, lang);
          return fresh;
        }
      }
      localStorage.removeItem(lang === 'TR' ? SEEN_TR_KEY : SEEN_EN_KEY);
      const batch = await fetchBatch(lang, 50);
      const fallback = batch[0] || 'İsim alınamadı';
      addSeen(fallback, lang);
      return fallback;
    }

    let lang = localStorage.getItem('lang_btn') || 'TR';

    function updateLangButtons() {
      const trBtn = document.getElementById('isim-tr');
      const enBtn = document.getElementById('isim-en');
      if (trBtn && enBtn) {
        trBtn.classList.toggle('secondary', lang !== 'TR');
        trBtn.classList.toggle('is-active', lang === 'TR');
        enBtn.classList.toggle('secondary', lang !== 'EN');
        enBtn.classList.toggle('is-active', lang === 'EN');
      }
    }

    async function renderName() {
      const out = document.getElementById('isim-out');
      if (!out) return;
      out.textContent = 'Yükleniyor...';
      try {
        const name = await nextName(lang);
        out.textContent = name;
      } catch (err) {
        out.textContent = 'API hatası, tekrar deneyin.';
      }
    }

    document.getElementById('isim-tr')?.addEventListener('click', () => {
      lang = 'TR';
      localStorage.setItem('lang_btn', 'TR');
      updateLangButtons();
      renderName();
    });

    document.getElementById('isim-en')?.addEventListener('click', () => {
      lang = 'EN';
      localStorage.setItem('lang_btn', 'EN');
      updateLangButtons();
      renderName();
    });

    document.getElementById('isim-yeni')?.addEventListener('click', renderName);
    document.getElementById('isim-reset')?.addEventListener('click', () => {
      localStorage.removeItem(SEEN_TR_KEY);
      localStorage.removeItem(SEEN_EN_KEY);
      const out = document.getElementById('isim-out');
      if (out) out.textContent = 'Havuz sıfırlandı.';
    });

    updateLangButtons();
    if (document.getElementById('isim-out')) renderName();
  })();
  (function(){
    const URL_TR = 'api/olay_tr.json';
    const URL_EN = 'api/olay_en.json';
    const LS_STATE_TR = 'kk_olay_state_tr_v2';
    const LS_STATE_EN = 'kk_olay_state_en_v2';
    const LS_SIG_TR = 'kk_olay_sig_tr_v2';
    const LS_SIG_EN = 'kk_olay_sig_en_v2';

    const FALLBACK_TR = {
      yer: ["kıyı kasabası","çatı katı","eski sinema","pazar yeri","kütüphane","istasyon peronu","rüzgârlı tepe","sahaf dükkânı","pasaj içi","sarnıç","çarşı içi","liman iskelesi","ıssız park","eski han","depo","tramvay durağı","metro istasyonu","otogar peronu","fener tepesi","çamaşırhane","gece pazarı","terk edilmiş fabrika","ahşap köprü","botanik bahçesi","belediye binası önü","çay bahçesi","sahil yürüyüş yolu","çocuk parkı","müze girişi","sanat atölyesi","tenis kortu kenarı","eski konak avlusu","mezarlık kapısı","spor salonu koridoru","balıkçı barınağı","hastane kantini","okul bahçesi","bakımevi koridoru","küçük otel lobisi","karakol karşısı"],
      zaman: ["şafak","öğle","ikindi","gün batımı","gece yarısı","yağmurdan sonra","fırtına öncesi","resmî tatil sabahı","pazar akşamı","son ders çıkışı","sirenler sustuğunda","iftar vaktinde","mesai bitiminde","servis saatinde","akşamüstü serinliğinde","lodos yükselirken","elektriklerin yeni geldiği anda","maç çıkışında","konser öncesi","konser sonrası","sınavdan hemen önce","sınavdan hemen sonra","günün en sessiz anında","kalabalık en yoğunken","otobüs geciktiğinde","hava kararmadan az önce","ilk kar yağarken","dalgalar çekilirken","poyraz dindikten sonra","ayın görünmediği bir gecede"],
      hava: ["yağmurlu","rüzgârlı","sisli","karlı","bunaltıcı sıcak","parçalı bulutlu","açık ve serin","çisenti","dolu","lodoslu","poyrazlı","tozlu rüzgâr","nemli ve yapışkan","kuru soğuk","ılık esinti","gök gürültülü","şimşekli","kapalı","ayaz","yağmur sonrası toprak kokulu","tıkanan hava","güneşli ama sert rüzgârlı","kurşuni bulutlu","dumanlı","çöl sıcakları","ay ışıklı berrak","sabah serinliği","akşam serini","puslu","yağmur öncesi basınçlı"],
      olay: ["kayıp bir not bulunur","yanlış kişiye mesaj gider","eski bir sır ortaya çıkar","beklenmedik bir misafir gelir","elektrikler kesilir","yanlış anlaşılma büyür","zarfın içinden fotoğraf çıkar","sinyal kesilir","anahtar uymuyor","paketten sürpriz çıkar","tanık kaybolur","yağmur bastırır","plan deşifre olur","izler silinir","gürültü şikâyeti büyür","yanlış bavul alınır","telefon farklı bir ağızdan cevap verir","eski bir borç hatırlatılır","yan masadan bir not kayar","camdan içeri küçük bir kuş girer","sirke sesi yaklaşır","bir çocuğun sorusu her şeyi değiştirir","beklenen kargo başka adrese gitmiştir","bir kamera kaydı ortaya çıkar","uzaktan tanıdık bir şarkı duyulur","harita yanlış katlanmıştır","biri adınızı doğru telaffuz eder","çift ayakkabının teki kaybolur","çizimde olmayan bir kapı bulunur","küçük bir yalan büyür","yanlış kapı çalınır","kayıp yüzük bulunur ama sahibini kabul etmez","eski bir fotoğrafta yeni bir detay fark edilir","randevu saati iki farklı kâğıtta farklı görünür","yabancı dilde gelen mektup tercümesizdir","kulaktan kulağa yayılan haber tersine döner"]
    };

    const FALLBACK_EN = {
      yer: ["seaside town","attic","old cinema","bazaar","library","platform","windy hill","bookshop","arcade","cistern","downtown","harbor pier","empty park","old inn","warehouse","tram stop","subway station","coach terminal bay","lighthouse hill","laundromat","night market","abandoned factory","wooden bridge","botanical garden","city hall steps","tea garden","promenade","playground","museum entrance","art studio","tennis court sideline","mansion courtyard","cemetery gate","gym corridor","fishermen's shelter","hospital cafeteria","school yard","care home hallway","small hotel lobby","across the precinct"],
      zaman: ["dawn","noon","late afternoon","sunset","midnight","after the rain","before the storm","holiday morning","Sunday evening","after the last class","when sirens go silent","at iftar time","right after work","during shuttle hour","in the evening chill","as the southwester rises","the moment power returns","after the match","before the concert","after the concert","right before the exam","right after the exam","in the day's quietest moment","at peak crowd","when the bus is late","just before dark","as the first snow falls","as the tide retreats","after the northeaster calms","on a moonless night"],
      hava: ["rainy","windy","foggy","snowy","sweltering","partly cloudy","clear and chilly","drizzle","hail","southwesterly","northeasterly","dusty wind","humid and sticky","dry cold","mild breeze","thunderstorm","lightning about","overcast","hard frost","earth-after-rain scent","stifling air","sunny yet gusty","leaden clouds","smoky haze","desert heat","moonlit and crisp","cool morning air","evening cool","hazy","pressured pre-rain"],
      olay: ["a lost note is found","a message goes to the wrong person","an old secret surfaces","an unexpected guest arrives","the power goes out","a misunderstanding escalates","a photo falls out of an envelope","signal drops","the key doesn't fit","the package holds a surprise","a witness disappears","heavy rain starts","the plan is exposed","tracks are wiped","a noise complaint grows","the wrong suitcase is taken","a phone answers in a different voice","an old debt is recalled","a note slides from the next table","a tiny bird flies indoors","sirens approach","a child's question changes everything","the parcel went to another address","a camera recording emerges","a familiar song is heard from afar","a map is folded the wrong way","someone pronounces your name perfectly","one shoe of a pair goes missing","a door not in the drawing is found","a small lie grows","the wrong door is knocked","a lost ring is found but refuses its owner","a new detail appears in an old photo","the appointment time differs on two papers","a letter arrives in a foreign language without translation","a rumor reverses as it spreads"]
    };

    function jget(key, fallback) {
      try { return JSON.parse(localStorage.getItem(key) || JSON.stringify(fallback)); } catch (_) { return fallback; }
    }

    function jset(key, value) {
      localStorage.setItem(key, JSON.stringify(value));
    }

    function shuffle(arr) {
      const copy = arr.slice();
      for (let i = copy.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [copy[i], copy[j]] = [copy[j], copy[i]];
      }
      return copy;
    }

    function Deck(key, list) {
      function load() {
        try { return JSON.parse(localStorage.getItem(key) || '[]'); } catch (_) { return []; }
      }
      function save(payload) {
        localStorage.setItem(key, JSON.stringify(payload));
      }
      function next() {
        let data = load();
        if (!data.length) {
          data = shuffle(list.slice());
          save(data);
        }
        const pick = data.pop();
        save(data);
        return pick;
      }
      function reset() { save([]); }
      return { next, reset };
    }

    async function fetchJson(url) {
      try {
        const res = await fetch(url, { cache: 'no-store' });
        if (!res.ok) throw new Error(res.status + ' ' + url);
        return await res.json();
      } catch (err) {
        console.warn('OLAY fetch fail:', err);
        return null;
      }
    }

    function signature(data) {
      return `y${(data.yer || []).length}-z${(data.zaman || []).length}-h${(data.hava || []).length}-o${(data.olay || []).length}`;
    }

    async function loadLists(lang) {
      if (lang === 'EN') {
        const data = await fetchJson(URL_EN);
        return data && data.yer && data.zaman && data.hava && data.olay ? data : FALLBACK_EN;
      }
      const data = await fetchJson(URL_TR);
      return data && data.yer && data.zaman && data.hava && data.olay ? data : FALLBACK_TR;
    }

    function stateKeys(lang) {
      return lang === 'EN'
        ? { LS_STATE: LS_STATE_EN, LS_SIG: LS_SIG_EN }
        : { LS_STATE: LS_STATE_TR, LS_SIG: LS_SIG_TR };
    }

    async function ensureDecks(lang) {
      const lists = await loadLists(lang);
      const sig = signature(lists);
      const { LS_STATE, LS_SIG } = stateKeys(lang);

      const yer = Deck(LS_STATE + '_yer', lists.yer);
      const zaman = Deck(LS_STATE + '_zaman', lists.zaman);
      const hava = Deck(LS_STATE + '_hava', lists.hava);
      const olay = Deck(LS_STATE + '_olay', lists.olay);

      const prevSig = jget(LS_SIG, null);
      if (prevSig !== sig) {
        yer.reset();
        zaman.reset();
        hava.reset();
        olay.reset();
        jset(LS_SIG, sig);
      }

      return { yer, zaman, hava, olay };
    }

    async function drawOnce() {
      const english = document.getElementById('olay-en')?.checked;
      const lang = english ? 'EN' : 'TR';
      const decks = await ensureDecks(lang);
      const y = decks.yer.next();
      const z = decks.zaman.next();
      const h = decks.hava.next();
      const o = decks.olay.next();
      const out = document.getElementById('olay-out');
      if (!out) return;
      out.textContent = english
        ? `Place: ${y} · Time: ${z} · Weather: ${h} · Event: ${o}`
        : `Yer: ${y} · Zaman: ${z} · Hava: ${h} · Olay: ${o}`;
    }

    function hardReset() {
      [LS_STATE_TR, LS_STATE_EN].forEach(prefix => {
        try {
          localStorage.removeItem(prefix + '_yer');
          localStorage.removeItem(prefix + '_zaman');
          localStorage.removeItem(prefix + '_hava');
          localStorage.removeItem(prefix + '_olay');
        } catch (_) {}
      });
      const out = document.getElementById('olay-out');
      if (out) out.textContent = 'Havuz sıfırlandı.';
    }

    document.getElementById('olay-yeni')?.addEventListener('click', drawOnce);
    document.getElementById('olay-en')?.addEventListener('change', drawOnce);
    document.getElementById('olay-reset')?.addEventListener('click', hardReset);
    if (document.getElementById('olay-out')) drawOnce();
  })();

  (function(){
    const STATE_KEY = 'kk_emotions_offline_state_v1';
    const TARGET = 150;
    const FR = {
      "Neşe": {
        lead: ["Güneş perdeden sızarken","Sabah kapıyı açınca","Kaldırım taşları arasında","Pencere eşiğinde","Sokağın köşesinde","Rüzgâr saçlarımdan geçerken","Kahve kokusu yükselirken","Merdiven başında","Gün ışığı omuzlarıma düşerken","Şehrin erken saatlerinde","Kuş sesleri arasında","Bulutlar pamukken"],
        verb: ["adımlarım hafifledi","gülüşüm büyüdü","içimde bir melodi başladı","kalem kendiliğinden aktı","renkler ses verdi","zaman koşmayı bıraktı","kapı kolları selam verdi","cam aynaya göz kırptı","bulutlar benimle yürüdü","gün avucumda ısındı","gölgeler dansa kalktı","pencereler içeri ışık çağırdı"],
        tail: ["ve sokak adımımı tanıdı.","ben de şehri tanıdım.","kalan yorgunluk eridi.","sanki bahar cebimdeydi.","gün küçük sürprizler bıraktı.","kahve fincanı teşekkür etti.","rüzgârla beşlik çaktım.","renkler birbirini alkışladı.","gülüşüm kapıları açtı."]
      },
      "Üzüntü": {
        lead: ["Pencerenin buğusunda","Sahanlığın loşunda","Uzayan sıranın sonunda","Kütüphanenin sessizliğinde","Akşamüstü çizgisinde","Yağmurun ince halinde","Ev içinin rüzgârında","Eski fotoğrafların kenarında","Sokağın ıslak taşlarında","Perde aralığında"],
        verb: ["sözlerim köşeye oturdu","zaman omzuma çöktü","çay soğudu","adımlarım ağırlaştı","kalem susmayı öğrendi","ışık yerinden ayrıldı","bekleyiş büyüdü","gün renklerini sakladı","ayna beklemeyi gösterdi","şehir iç çekti"],
        tail: ["ve cümle yarım kaldı.","yastığın soğuğu içime sızdı.","mektuplar adıma değildi.","adımın gölgesi benden önce yoruldu.","boşluk kapının altından girdi.","şarkı benden bir parça eksiltti."]
      },
      "Öfke": {
        lead: ["Kapı eşiğinde","Asansör önünde","Dar koridorda","Otobüs durağında","Kalabalığın ortasında","Masanın kenarında","Kavanoz kapağında","Fermuarla boğuşurken","Sıra yaklaşmışken","Zemin inat ederken"],
        verb: ["cümlelerim keskinleşti","nefesim büyüdü","sabırım çizildi","kilit beni yanlış sandı","zaman tik tik çentik attı","cam ısı değil öfke aldı","poşet kulpu köşede koptu","sayfa aradığım yerde yapıştı","buton basılmayı reddetti"],
        tail: ["ve avucumda yumruğa dönüştü.","adımlarım zemini dövdü.","kapı cevap vermedi.","sabrımda ince bir çatlak kaldı."]
      },
      "Korku": {
        lead: ["Karanlığın kıyısında","Merdiven gölgesinde","Kapının altındaki çizgide","Koridorun ortasında","Perdenin titrediği anda","Sessiz odada","Yansımayla göz göze gelirken","Dolabın içinden gelen tıkta"],
        verb: ["adımım benden önce atladı","gölge boy attı","kilit içeriden nefes aldı","yankı ismimi fısıldadı","pencere içeri baktı","rüzgâr adımı yanlış söyledi","basamak ayak değmeden inledi","perde rüzgârsız kıpırdadı"],
        tail: ["ve içimdeki sessizlik büyüdü.","benim sesim geride kaldı.","ışık çizgisi beni inceledi.","kapı kilitli olmadığı hâlde direndi."]
      },
      "Şaşkınlık": {
        lead: ["Zamanın tökezlediği anda","Göz kırptığım sırada","Yürürken birden","Merdiven başında","Haritanın önünde","Aynanın karşısında","Bilet elimdeyken","Kapı numarasının altında"],
        verb: ["görüntü değişti","anahtar odayı çevirdi","gölge benden hızlı düşündü","postacı yazılmamış mektubu getirdi","kitap beni raftan çağırdı","rüzgâr sayfayı ters çevirdi","kilit anahtara 'sen kimsin' dedi","gün ışığı yerden sızdı"],
        tail: ["ve cümle ortasında yeni bir sahne açıldı.","adres beni seçti.","düğme ceketini aradı.","merdiven yukarı değil ileri çıktı."]
      },
      "Sükûnet": {
        lead: ["Günün kenarında","Sabahın ilk buharında","Pencere pervazında","Koltuğun gölgesinde","Defterin beyazında","Rüzgârın yumuşak yerinde","Odanın derin köşesinde","Işığın ince çizgisinde"],
        verb: ["nefesi saydım","kelimeler yumuşadı","zaman yerinde kaldı","gölge yelken açtı","kalem incitmeden ilerledi","sokak sesi kapıda yumuşadı","masa küçük sesleri sakladı","ışık bardakta dinlendi"],
        tail: ["ve cümle devam etmeyi acele etmedi.","omuzlarım sabır ördü.","gün usulca oturdu.","adımlar taşlara teşekkür etti."]
      }
    };

    function jget(key, fallback) {
      try { return JSON.parse(localStorage.getItem(key) || JSON.stringify(fallback)); } catch (_) { return fallback; }
    }

    function jset(key, value) {
      localStorage.setItem(key, JSON.stringify(value));
    }

    function shuffle(arr) {
      const copy = arr.slice();
      for (let i = copy.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [copy[i], copy[j]] = [copy[j], copy[i]];
      }
      return copy;
    }

    function buildDeck() {
      return Object.keys(FR).map(key => {
        const { lead = [], verb = [], tail = [] } = FR[key];
        const L = lead.length;
        const V = verb.length;
        const T = tail.length;
        const total = L * V * T;
        const pick = Math.min(total || 0, TARGET);
        const seen = new Set();
        const out = [];
        while (out.length < pick && total > 0) {
          const a = Math.floor(Math.random() * L);
          const b = Math.floor(Math.random() * V);
          const c = Math.floor(Math.random() * T);
          const sig = `${a}-${b}-${c}`;
          if (seen.has(sig)) continue;
          seen.add(sig);
          out.push(`${lead[a]} ${verb[b]} ${tail[c]}`);
        }
        return { label: key, deck: shuffle(out) };
      });
    }

    function ensureState() {
      let state = jget(STATE_KEY, null);
      if (!(state && Array.isArray(state.remaining))) {
        const decks = buildDeck();
        state = {
          labels: decks.map(d => d.label),
          remaining: decks.map(d => d.deck)
        };
        jset(STATE_KEY, state);
      }
      return state;
    }

    function nextFrom(index, state) {
      if (!(state.remaining[index] && state.remaining[index].length)) {
        const decks = buildDeck();
        state.remaining[index] = decks[index].deck;
      }
      const value = state.remaining[index].pop();
      jset(STATE_KEY, state);
      return value;
    }

    function nextRandom(state) {
      const index = Math.floor(Math.random() * state.remaining.length);
      return { index, text: nextFrom(index, state) };
    }

    function mount() {
      const row = document.getElementById('duygu-row');
      if (!row) return;
      const state = ensureState();
      row.innerHTML = '';
      state.labels.forEach((label, index) => {
        const button = document.createElement('button');
        button.className = 'pill-btn secondary';
        button.textContent = label;
        button.addEventListener('click', () => {
          document.getElementById('duygu-out').innerHTML = `<strong>${label}:</strong> ${nextFrom(index, state)}`;
        });
        row.appendChild(button);
      });
      const first = nextRandom(state);
      document.getElementById('duygu-out').innerHTML = `<strong>${state.labels[first.index]}:</strong> ${first.text}`;
      document.getElementById('duygu-random')?.addEventListener('click', () => {
        const item = nextRandom(state);
        document.getElementById('duygu-out').innerHTML = `<strong>${state.labels[item.index]}:</strong> ${item.text}`;
      });
      document.getElementById('duygu-reset')?.addEventListener('click', () => {
        localStorage.removeItem(STATE_KEY);
        document.getElementById('duygu-out').textContent = 'Havuz sıfırlandı.';
      });
    }

    mount();
  })();
})();
</script>
</body>
</html>
