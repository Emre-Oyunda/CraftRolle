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
<title><?= e(APP_NAME) ?> — Eğlence</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
  /* === DUAL THEME - Pembe & Siyah === */
  
  /* PEMBE TEMA (Default - Pastel) */
  body {
    background: linear-gradient(135deg, #fef5ff 0%, #fff0f9 25%, #f8f0ff 50%, #fff5fb 75%, #fef5ff 100%);
    color:#5a3d5c;
    font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Arial;
    margin:0;
    min-height:100vh;
    position:relative;
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
  
  /* SİYAH TEMA - İlk kodundaki arka plan */
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
  
  .wrap{
    max-width:1200px;
    margin:24px auto;
    padding:0 16px;
    position:relative;
    z-index:1;
  }
  
  /* === KARTLAR === */
  .card{
    border:1px solid rgba(255, 182, 193, 0.4);
    border-radius:20px;
    padding:24px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 250, 253, 0.8) 100%);
    backdrop-filter: blur(10px);
    margin-bottom:20px;
    box-shadow: 
      0 4px 20px rgba(221, 160, 221, 0.15),
      inset 0 1px 0 rgba(255, 255, 255, 0.8);
    transition: all 0.3s ease;
  }
  
  body.dark-theme .card {
    border:1px solid #2a2144;
    background: rgba(255,255,255,.04);
    box-shadow: 
      0 4px 16px rgba(0, 0, 0, 0.3),
      inset 0 0 20px rgba(124, 58, 237, 0.05);
  }
  
  .card:hover {
    transform: translateY(-2px);
    box-shadow: 
      0 8px 30px rgba(255, 182, 193, 0.25),
      inset 0 1px 0 rgba(255, 255, 255, 1);
    border-color: rgba(255, 182, 193, 0.6);
  }
  
  body.dark-theme .card:hover {
    transform: translateY(-2px);
    box-shadow: 
      0 8px 24px rgba(124, 58, 237, 0.25),
      inset 0 0 30px rgba(124, 58, 237, 0.1);
    border-color: #3a2a54;
  }
  
  .wrap .card h2 {
    background: linear-gradient(135deg, #ff6b9d, #c44569);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-size: 1.8em;
    margin-bottom: 12px;
    font-weight: 700;
  }
  
  .wrap .card h3 {
    color: #8b5a8e;
    font-size: 1.3em;
    margin: 0 0 16px 0;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  
  body.dark-theme .wrap .card h3 {
    color: #ffd2f0;
  }
  
  .eg-grid{
    display:grid;
    gap:20px;
    grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
  }
  
  /* === BUTONLAR === */
  .btn{
    padding:12px 20px;
    border:1px solid rgba(255, 182, 193, 0.5);
    border-radius:12px;
    background: linear-gradient(135deg, #dda0dd 0%, #d8a0d8 100%);
    color:#fff !important;
    cursor:pointer;
    text-decoration:none;
    display:inline-block;
    font-weight:500;
    transition: all 0.3s ease;
    box-shadow: 0 3px 10px rgba(221, 160, 221, 0.3);
    position: relative;
    overflow: hidden;
  }
  
  body.dark-theme .btn {
    border:1px solid #2a2144;
    background: #161226;
    box-shadow: 
      0 2px 8px rgba(0, 0, 0, 0.3),
      inset 0 1px 1px rgba(124, 58, 237, 0.2);
  }
  
  .btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.25);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
  }
  
  .btn:hover::before {
    width: 300px;
    height: 300px;
  }
  
  .btn:hover{
    transform: translateY(-2px);
    box-shadow: 0 5px 18px rgba(221, 160, 221, 0.4);
    border-color: rgba(255, 182, 193, 0.7);
  }
  
  body.dark-theme .btn:hover {
    box-shadow: 
      0 4px 12px rgba(124, 58, 237, 0.4),
      inset 0 1px 1px rgba(124, 58, 237, 0.3);
    border-color: #3a2a54;
  }
  
  .btn:active {
    transform: translateY(0);
  }
  
  .btn.active{
    background: linear-gradient(135deg, #ffb6c1 0%, #ffc0cb 100%) !important;
    box-shadow: 0 0 0 3px rgba(255, 182, 193, 0.4);
    border-color: rgba(255, 182, 193, 0.8);
  }
  
  body.dark-theme .btn.active {
    background: #1f1636 !important;
    outline: 2px solid #7c3aed;
    box-shadow: 0 0 0 2px rgba(124,58,237,.25) inset, 0 0 20px rgba(124,58,237,0.4);
    border-color: #7c3aed;
  }
  
  .small{
    opacity:.8;
    font-size:0.9em;
    line-height:1.6;
    color:#7a5c7d;
  }
  
  body.dark-theme .small {
    opacity: 0.85;
    color: #d4b5d7;
  }
  
  .out{
    min-height:32px;
    margin:16px 0;
    font-weight:600;
    padding:12px;
    background: rgba(255, 182, 193, 0.12);
    border-radius:12px;
    border:1px solid rgba(221, 160, 221, 0.25);
    line-height:1.6;
    color:#5a3d5c;
  }
  
  body.dark-theme .out {
    min-height:28px;
    margin:6px 0;
    font-weight:600;
    background: rgba(124, 58, 237, 0.08);
    border:1px solid rgba(124, 58, 237, 0.2);
    color:#f5e8ff;
  }
  
  .row{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    align-items:center;
    margin:12px 0;
  }
  
  body.dark-theme .row {
    gap:8px;
  }
  
  .chip{
    border:1px solid rgba(255, 182, 193, 0.5);
    border-radius:999px;
    padding:10px 18px;
    font-size:1rem;
    background: linear-gradient(135deg, rgba(255, 182, 193, 0.25) 0%, rgba(221, 160, 221, 0.25) 100%);
    font-weight:600;
    box-shadow: 0 2px 8px rgba(221, 160, 221, 0.2);
    transition: all 0.3s ease;
    color:#5a3d5c;
  }
  
  body.dark-theme .chip {
    border:1px solid #2a2144;
    border-radius:999px;
    padding:6px 10px;
    font-size:.95rem;
    background: #0f0c1e;
    box-shadow: 
      0 2px 6px rgba(0, 0, 0, 0.3),
      inset 0 0 10px rgba(124, 58, 237, 0.1);
    color:#ffd2f0;
  }
  
  .chip:hover {
    transform: scale(1.05);
    box-shadow: 0 3px 12px rgba(255, 182, 193, 0.35);
  }
  
  body.dark-theme .chip:hover {
    transform: scale(1.05);
    box-shadow: 
      0 3px 10px rgba(124, 58, 237, 0.4),
      inset 0 0 15px rgba(124, 58, 237, 0.15);
  }
  
  .timer{
    font-variant-numeric:tabular-nums;
    font-size:2rem;
    font-weight:700;
    background: linear-gradient(135deg, #dda0dd, #ffb6c1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
  
  body.dark-theme .timer {
    font-variant-numeric:tabular-nums;
    font-size:1.6rem;
    font-weight:700;
    color: #ffd2f0;
    background: none;
    -webkit-text-fill-color: currentColor;
  }
  
  /* Checkbox */
  input[type="checkbox"] {
    appearance: none;
    width: 20px;
    height: 20px;
    border: 2px solid rgba(221, 160, 221, 0.6);
    border-radius: 4px;
    background: rgba(255, 255, 255, 0.8);
    cursor: pointer;
    position: relative;
    transition: all 0.3s ease;
    vertical-align: middle;
  }
  
  body.dark-theme input[type="checkbox"] {
    border: 2px solid #2a2144;
    background: #0f0c1e;
  }
  
  input[type="checkbox"]:checked {
    background: linear-gradient(135deg, #dda0dd, #ffb6c1);
    border-color: #ffb6c1;
  }
  
  body.dark-theme input[type="checkbox"]:checked {
    background: #7c3aed;
    border-color: #7c3aed;
  }
  
  input[type="checkbox"]:checked::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 14px;
    font-weight: bold;
  }
  
  /* Links */
  a {
    color: #c084c0;
    transition: all 0.2s ease;
  }
  
  a:hover {
    color: #d8a0d8;
  }
  
  body.dark-theme a {
    color: #ffd2f0;
  }
  
  body.dark-theme a:hover {
    color: #ff69b4;
  }
  
  /* Selection */
  ::selection {
    background: rgba(255, 182, 193, 0.4);
    color: #5a3d5c;
  }
  
  body.dark-theme ::selection {
    background: rgba(124, 58, 237, 0.3);
    color: #fff;
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
    .eg-grid {
      grid-template-columns: 1fr;
    }
    
    .wrap .card h2 {
      font-size: 1.5em;
    }
    
    .wrap .card h3 {
      font-size: 1.2em;
    }
    
    .theme-toggle {
      top: 10px;
      right: 10px;
      padding: 10px 18px;
      font-size: 12px;
    }
  }
  
  html {
    scroll-behavior: smooth;
  }
</style>
</head><body>

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
    localStorage.setItem('eglence-theme', 'dark');
  } else {
    icon.textContent = '🌸';
    text.textContent = 'Pembe';
    localStorage.setItem('eglence-theme', 'light');
  }
}

// Load saved theme
(function() {
  const savedTheme = localStorage.getItem('eglence-theme');
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
    <div><a class="btn" href="<?= base_url('index.php') ?>" style="text-decoration:none;">🌸 <span class="brand"><?= e(APP_NAME) ?></span></a></div>
    <div>
      <?php if($user): ?>
        <span class="badge">Merhaba, <?= e($user['username']) ?></span>
        · <a href="<?= base_url('dashboard.php') ?>">Panel</a>
        · <a href="<?= base_url('books.php') ?>">Kitaplarım</a>
        · <a href="<?= base_url('notes.php') ?>">Notlarım</a>
        · <a href="<?= base_url('eglence.php') ?>" class="active">Eğlence</a>
        · <a href="<?= base_url('designer_cover.php') ?>">Kapak</a>
        · <a href="<?= base_url('designer_map.php') ?>">Harita</a>
        · <a href="<?= base_url('logout.php') ?>">Çıkış</a>
      <?php else: ?>
        <a href="<?= base_url('login.php') ?>">Giriş</a> · <a href="<?= base_url('register.php') ?>">Kayıt Ol</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="wrap">
  <div class="card">
    <h2>🎉 Eğlence</h2>
    <p class="small">Yaratıcı Zar · 3 Kelime · İlk Cümle · İsim Oluştur (TR/EN) · Olay Kartı (TR/EN) · Duygu Tekerleği</p>
  </div>

  <div class="eg-grid">
    <!-- YARATICI ZAR -->
    <div class="card">
      <h3>🎲 Yaratıcı Zar</h3>
      <div id="zar-out" class="out"></div>
      <div class="row">
        <button class="btn" id="zar-btn">Zar At</button>
        <button class="btn" id="zar-reset" title="Tüm kombinasyonları yeniden karıştır">Sıfırla</button>
      </div>
      <p class="small">Rastgele karakter,olay oluşturma</p>
    </div>

    <!-- 3 KELİME -->
    <div class="card">
      <h3>🧩 3 Kelime</h3>
      <div id="kelime-row" class="row"></div>
      <div class="row">
        <button class="btn" id="kelime-yeni">Yeni 3 Kelime</button>
        <button class="btn" id="kelime-reset" title="Kelime destesini sıfırla">Sıfırla</button>
      </div>
      <p class="small">3 kelime ile cümle kurmaya çalış</p>
    </div>

    <!-- İLK CÜMLE -->
    <div class="card">
      <h3>✍️ İlk Cümle</h3>
      <p id="baslatici-out" class="out"></p>
      <div class="row">
        <button class="btn" id="baslatici-yeni">Yeni Başlatıcı</button>
        <button class="btn" id="baslatici-reset" title="Başlatıcı havuzunu sıfırlar">Sıfırla</button>
      </div>
      <p class="small">İlk cümleni oluştur ve kitap yazmaya başla</p>
    </div>

    <!-- İSİM (TR/EN) -->
    <div class="card">
      <h3>🧑‍🤝‍🧑 İsim Üret</h3>
      <div id="isim-out" class="out"></div>
      <div class="row">
        <button class="btn" id="isim-tr">Türkçe</button>
        <button class="btn" id="isim-en">English</button>
        <button class="btn" id="isim-yeni">Yeni İsim</button>
        <button class="btn" id="isim-reset" title="Görülen isim listesini temizler">Sıfırla</button>
      </div>
      <p class="small"><code>Üretmek için</code> dili seç ve ürete tıkla.</p>
    </div>

    <!-- OLAY KARTI -->
    <div class="card">
      <h3>🗂️ Olay Kartı</h3>
      <div id="olay-out" class="out"></div>
      <div class="row">
        <button class="btn" id="olay-yeni">Yeni Olay Kartı</button>
        <label class="small">EN <input type="checkbox" id="olay-en"></label>
        <button class="btn" id="olay-reset" title="Tüm desteleri sıfırla">Sıfırla</button>
      </div>
      <p class="small">Rastgele olay oluştur</p>
    </div>

    <!-- DUYGU TEKERLEĞİ -->
    <div class="card">
      <h3>🎡 Duygu Tekerleği</h3>
      <div class="row" id="duygu-row"></div>
      <div id="duygu-out" class="out"></div>
      <div class="row">
        <button class="btn" id="duygu-random">Rastgele Duygu</button>
        <button class="btn" id="duygu-reset" title="Tüm duygu destelerini sıfırla">Sıfırla</button>
      </div>
      <p class="small">Rastgele duygu oluştur</p>
    </div>
  </div>
</div>

<script>
(function(){
  function $(s){ return document.querySelector(s); }

  /* === 🎲 Yaratıcı Zar (OFFLINE / tekrar yok) === */
  (function(){
    const DECK_KEY = 'kk_zar_offline_deck_v1';
    const kisiler = ["genç yazar","tuhaf komşu","gece bekçisi","eski öğretmen","balıkçı","kütüphaneci","postacı","mimar","pazarcı","fotoğrafçı","müzisyen","gazeteci","kalaycı","terzi","saatçi"];
    const mekanlar = ["kıyı kasabası","eski sinema","pazar yeri","kütüphane","istasyon peronu","rüzgârlı tepe","sahaf dükkânı","pasaj içi","sarnıç","çarşı içi","ıssız park","liman iskelesi","çatı katı","eski han","depo"];
    const catismalar = ["zaman daralır","sır ortaya çıkar","yol kapanır","yanlış anlaşılma büyür","elektrikler kesilir","anahtar uymuyor","tanık kaybolur","yağmur bastırır","beklenmedik misafir gelir","sinyal kesilir","plan deşifre olur","izler silinir","gürültü şikâyeti büyür","şifre yanlış çıkar","görev el değiştirir"];

    function jget(k,d){try{return JSON.parse(localStorage.getItem(k)||JSON.stringify(d))}catch(_){return d}}
    function jset(k,v){localStorage.setItem(k,JSON.stringify(v))}
    function shuffle(a){const r=a.slice();for(let i=r.length-1;i>0;i--){const j=Math.floor(Math.random()*(i+1));[r[i],r[j]]=[r[j],r[i]]}return r}

    function buildDeck(){
      const out=[];
      for(const a of kisiler){for(const b of mekanlar){for(const c of catismalar){out.push({a,b,c})}}}
      return shuffle(out);
    }
    function ensureDeck(){
      let st=jget(DECK_KEY,null);
      if(!(st&&Array.isArray(st.remaining)&&st.remaining.length)){ st={remaining:buildDeck()}; jset(DECK_KEY,st); }
      return st;
    }
    function roll(){
      const out=document.getElementById('zar-out'); if(!out) return;
      const st=ensureDeck();
      if(!st.remaining.length){ out.textContent='Havuz bitti. Sıfırla ile karıştır.'; return; }
      const x=st.remaining.pop(); jset(DECK_KEY,st);
      out.textContent=`Karakter: ${x.a} · Mekân: ${x.b} · Çatışma: ${x.c}`;
    }
    document.getElementById('zar-btn')?.addEventListener('click', roll);
    document.getElementById('zar-reset')?.addEventListener('click', ()=>{ localStorage.removeItem(DECK_KEY); const o=document.getElementById('zar-out'); if(o) o.textContent='Havuz sıfırlandı.'; });
    if(document.getElementById('zar-out')) roll();
  })();

  /* === 🧩 3 Kelime (JSON + tekrar yok) === */
  (function(){
    const WORDS_URL = 'api/words_tr.json';
    const WORDS_KEY = 'kk_words_deck_v1';
    const WORDS_SIG = 'kk_words_sig_v1';

    function jget(k, d){ try{ return JSON.parse(localStorage.getItem(k)||JSON.stringify(d)); }catch(e){ return d; } }
    function jset(k, v){ localStorage.setItem(k, JSON.stringify(v)); }
    function cshuffle(a){const r=a.slice();for(let i=r.length-1;i>0;i--){const j=Math.floor(Math.random()*(i+1));[r[i],r[j]]=[r[j],r[i]]}return r}

    async function loadWords(){
      const res = await fetch(WORDS_URL, {cache:'no-store'});
      if(!res.ok) throw new Error('words '+res.status);
      const list = await res.json();
      if(!Array.isArray(list) || list.length < 3) throw new Error('words too small');
      return list;
    }
    function makeSig(list){ return `${list.length}:${list[0]}:${list[list.length-1]}`; }

    async function ensureDeck(){
      const prevSig = jget(WORDS_SIG, null);
      let deck = jget(WORDS_KEY, null);
      if(deck && Array.isArray(deck.remaining) && deck.remaining.length >= 3 && prevSig){
        return { remaining: deck.remaining, sig: prevSig };
      }
      const list = await loadWords();
      const sig  = makeSig(list);
      const remaining = cshuffle(list);
      jset(WORDS_KEY, { remaining });
      jset(WORDS_SIG, sig);
      return { remaining, sig };
    }

    async function nextTriple(){
      let st = jget(WORDS_KEY, null);
      if(!(st && Array.isArray(st.remaining) && st.remaining.length >= 3)){
        const built = await ensureDeck();
        st = { remaining: built.remaining };
      }
      const triple = [];
      for(let i=0;i<3;i++){ triple.push(st.remaining.pop()); }
      jset(WORDS_KEY, st);
      return triple;
    }

    async function renderTriple(){
      const row = document.getElementById('kelime-row');
      if(!row) return;
      row.innerHTML = '<span class="small">Yükleniyor...</span>';
      try{
        const words = await nextTriple();
        row.innerHTML = '';
        words.forEach(w=>{
          const s = document.createElement('span');
          s.className = 'chip';
          s.textContent = w;
          row.appendChild(s);
        });
      }catch(e){
        row.innerHTML = '<span class="small">Liste yüklenemedi.</span>';
      }
    }

    document.getElementById('kelime-yeni')?.addEventListener('click', renderTriple);
    document.getElementById('kelime-reset')?.addEventListener('click', ()=>{
      localStorage.removeItem(WORDS_KEY);
      localStorage.removeItem(WORDS_SIG);
      const row = document.getElementById('kelime-row');
      if(row) row.innerHTML = '<span class="small">Havuz sıfırlandı.</span>';
    });
    if(document.getElementById('kelime-row')) renderTriple();
  })();

  /* === ✍️ İlk Cümle (JSON + tekrar yok) === */
  (function(){
    function jget(k, d){ try{ return JSON.parse(localStorage.getItem(k)||JSON.stringify(d)); }catch(e){ return d; } }
    function jset(k, v){ localStorage.setItem(k, JSON.stringify(v)); }
    const STARTER_KEY = 'kk_bigdeck_starters_tr';
    const STARTER_URL = 'api/starters_tr.json';
    function cshuffle(a){const r=a.slice();for(let i=r.length-1;i>0;i--){const j=Math.floor(Math.random()*(i+1));[r[i],r[j]]=[r[j],r[i]]}return r}
    async function ensureDeck(){
      let st=jget(STARTER_KEY,null);
      if(st&&Array.isArray(st.remaining)&&st.remaining.length>0) return st;
      const res=await fetch(STARTER_URL,{cache:'no-store'});
      if(!res.ok) throw new Error('starter list '+res.status);
      const list=await res.json();
      if(!Array.isArray(list)||list.length===0) throw new Error('starter empty');
      st={remaining:cshuffle(list),ts:Date.now()}; jset(STARTER_KEY,st); return st;
    }
    async function starterNext(){
      const st=await ensureDeck();
      if(st.remaining.length===0) return {done:true,value:null};
      const x=st.remaining.pop(); jset(STARTER_KEY,st); return {done:false,value:x};
    }
    async function newStarter(){
      const o=$('#baslatici-out'); if(!o) return;
      o.textContent='Yükleniyor...';
      try{
        const r=await starterNext();
        o.textContent=r.done?'Havuz bitti. "Sıfırla" ya da listeyi büyüt.':r.value;
      }catch(e){
        o.textContent='Liste yüklenemedi.';
      }
    }
    document.getElementById('baslatici-yeni')?.addEventListener('click', newStarter);
    document.getElementById('baslatici-reset')?.addEventListener('click', function(){
      localStorage.removeItem(STARTER_KEY);
      const o=$('#baslatici-out'); if(o) o.textContent='Havuz sıfırlandı.';
    });
    if($('#baslatici-out')) newStarter();
  })();

  /* === 🧑‍🤝‍🧑 İsim (API + tekrar yok) === */
  (function(){
    const SEEN_TR_KEY = 'kk_seen_names_tr';
    const SEEN_EN_KEY = 'kk_seen_names_en';
    const SEEN_LIMIT  = 2000;
    function jget2(k, d){ try{ return JSON.parse(localStorage.getItem(k)||JSON.stringify(d)); }catch(e){ return d; } }
    function jset2(k, v){ localStorage.setItem(k, JSON.stringify(v)); }
    function seenArr(lang){ return jget2(lang==='TR'?SEEN_TR_KEY:SEEN_EN_KEY, []); }
    function addSeen(name, lang){ let arr=seenArr(lang); arr.push(name); if(arr.length>SEEN_LIMIT) arr=arr.slice(-SEEN_LIMIT); jset2(lang==='TR'?SEEN_TR_KEY:SEEN_EN_KEY, arr); }
    function isSeen(name, lang){ return seenArr(lang).includes(name); }
    async function fetchBatch(lang, count=50){
      const nat=(lang==='TR')?'tr':'us,gb,ca,au,nz,ie';
      const url=`https://randomuser.me/api/?inc=name&noinfo=1&nat=${encodeURIComponent(nat)}&results=${count}`;
      const res=await fetch(url,{cache:'no-store'});
      if(!res.ok) throw new Error('API '+res.status);
      const j=await res.json();
      const list=(j.results||[]).map(r=>{
        const f=r.name?.first||''; const l=r.name?.last||'';
        const cap=s=>s?s.charAt(0).toUpperCase()+s.slice(1):'';
        return (f&&l)?`${cap(f)} ${cap(l)}`:null;
      }).filter(Boolean);
      return list;
    }
    async function nextUniqueName(lang){
      for(let tryNo=0; tryNo<5; tryNo++){
        const batch=await fetchBatch(lang,50);
        for(const nm of batch){ if(!isSeen(nm,lang)){ addSeen(nm,lang); return nm; } }
      }
      localStorage.removeItem(lang==='TR'?SEEN_TR_KEY:SEEN_EN_KEY);
      const batch=await fetchBatch(lang,50);
      const nm=batch[0]||'İsim alınamadı'; addSeen(nm,lang); return nm;
    }
    let LANG=localStorage.getItem('lang_btn')||'TR';
    function updBtns(){ const tr=$('#isim-tr'),en=$('#isim-en'); if(tr&&en){ tr.classList.toggle('active',LANG==='TR'); en.classList.toggle('active',LANG==='EN'); } }
    async function showNew(){ const out=$('#isim-out'); if(!out) return; out.textContent='Yükleniyor...'; try{ const nm=await nextUniqueName(LANG); out.textContent=nm; }catch(e){ out.textContent='API hatası, tekrar deneyin.'; } }
    document.getElementById('isim-tr')?.addEventListener('click', ()=>{ LANG='TR'; localStorage.setItem('lang_btn','TR'); updBtns(); showNew(); });
    document.getElementById('isim-en')?.addEventListener('click', ()=>{ LANG='EN'; localStorage.setItem('lang_btn','EN'); updBtns(); showNew(); });
    document.getElementById('isim-yeni')?.addEventListener('click', showNew);
    document.getElementById('isim-reset')?.addEventListener('click', ()=>{ localStorage.removeItem(SEEN_TR_KEY); localStorage.removeItem(SEEN_EN_KEY); const out=$('#isim-out'); if(out) out.textContent='Havuz sıfırlandı.'; });
    updBtns(); if($('#isim-out')) showNew();
  })();

  /* === 🗂️ Olay Kartı (JSON, rotasyon, tekrar yok, TR/EN) === */
  (function(){
    const URL_TR = 'api/olay_tr.json';
    const URL_EN = 'api/olay_en.json';
    const LS_STATE_TR = 'kk_olay_state_tr_v2';
    const LS_STATE_EN = 'kk_olay_state_en_v2';
    const LS_SIG_TR   = 'kk_olay_sig_tr_v2';
    const LS_SIG_EN   = 'kk_olay_sig_en_v2';

    const FALLBACK_TR = {
      yer:["kıyı kasabası","çatı katı","eski sinema","pazar yeri","kütüphane","istasyon peronu","rüzgârlı tepe","sahaf dükkânı","pasaj içi","sarnıç","çarşı içi","liman iskelesi","ıssız park","eski han","depo","tramvay durağı","metro istasyonu","otogar peronu","fener tepesi","çamaşırhane","gece pazarı","terk edilmiş fabrika","ahşap köprü","botanik bahçesi","belediye binası önü","çay bahçesi","sahil yürüyüş yolu","çocuk parkı","müze girişi","sanat atölyesi","tenis kortu kenarı","eski konak avlusu","mezarlık kapısı","spor salonu koridoru","balıkçı barınağı","hastane kantini","okul bahçesi","bakımevi koridoru","küçük otel lobisi","karakol karşısı"],
      zaman:["şafak","öğle","ikindi","gün batımı","gece yarısı","yağmurdan sonra","fırtına öncesi","resmî tatil sabahı","pazar akşamı","son ders çıkışı","sirenler sustuğunda","iftar vaktinde","mesai bitiminde","servis saatinde","akşamüstü serinliğinde","lodos yükselirken","elektriklerin yeni geldiği anda","maç çıkışında","konser öncesi","konser sonrası","sınavdan hemen önce","sınavdan hemen sonra","günün en sessiz anında","kalabalık en yoğunken","otobüs geciktiğinde","hava kararmadan az önce","ilk kar yağarken","dalgalar çekilirken","poyraz dindikten sonra","ayın görünmediği bir gecede"],
      hava:["yağmurlu","rüzgârlı","sisli","karlı","bunaltıcı sıcak","parçalı bulutlu","açık ve serin","çisenti","dolu","lodoslu","poyrazlı","tozlu rüzgâr","nemli ve yapışkan","kuru soğuk","ılık esinti","gök gürültülü","şimşekli","kapalı","ayaz","yağmur sonrası toprak kokulu","tıkanan hava","güneşli ama sert rüzgârlı","kurşuni bulutlu","dumanlı","çöl sıcakları","ay ışıklı berrak","sabah serinliği","akşam serini","puslu","yağmur öncesi basınçlı"],
      olay:["kayıp bir not bulunur","yanlış kişiye mesaj gider","eski bir sır ortaya çıkar","beklenmedik bir misafir gelir","elektrikler kesilir","yanlış anlaşılma büyür","zarfın içinden fotoğraf çıkar","sinyal kesilir","anahtar uymuyor","paketten sürpriz çıkar","tanık kaybolur","yağmur bastırır","plan deşifre olur","izler silinir","gürültü şikâyeti büyür","yanlış bavul alınır","telefon farklı bir ağızdan cevap verir","eski bir borç hatırlatılır","yan masadan bir not kayar","camdan içeri küçük bir kuş girer","sirke sesi yaklaşır","bir çocuğun sorusu her şeyi değiştirir","beklenen kargo başka adrese gitmiştir","bir kamera kaydı ortaya çıkar","uzaktan tanıdık bir şarkı duyulur","harita yanlış katlanmıştır","biri adınızı doğru telaffuz eder","çift ayakkabının teki kaybolur","çizimde olmayan bir kapı bulunur","küçük bir yalan büyür","yanlış kapı çalınır","kayıp yüzük bulunur ama sahibini kabul etmez","eski bir fotoğrafta yeni bir detay fark edilir","randevu saati iki farklı kâğıtta farklı görünür","yabancı dilde gelen mektup tercümesizdir","kulaktan kulağa yayılan haber tersine döner"]
    };
    const FALLBACK_EN = {
      yer:["seaside town","attic","old cinema","bazaar","library","platform","windy hill","bookshop","arcade","cistern","downtown","harbor pier","empty park","old inn","warehouse","tram stop","subway station","coach terminal bay","lighthouse hill","laundromat","night market","abandoned factory","wooden bridge","botanical garden","city hall steps","tea garden","promenade","playground","museum entrance","art studio","tennis court sideline","mansion courtyard","cemetery gate","gym corridor","fishermen's shelter","hospital cafeteria","school yard","care home hallway","small hotel lobby","across the precinct"],
      zaman:["dawn","noon","late afternoon","sunset","midnight","after the rain","before the storm","holiday morning","Sunday evening","after the last class","when sirens go silent","at iftar time","right after work","during shuttle hour","in the evening chill","as the southwester rises","the moment power returns","after the match","before the concert","after the concert","right before the exam","right after the exam","in the day's quietest moment","at peak crowd","when the bus is late","just before dark","as the first snow falls","as the tide retreats","after the northeaster calms","on a moonless night"],
      hava:["rainy","windy","foggy","snowy","sweltering","partly cloudy","clear and chilly","drizzle","hail","southwesterly","northeasterly","dusty wind","humid and sticky","dry cold","mild breeze","thunderstorm","lightning about","overcast","hard frost","earth-after-rain scent","stifling air","sunny yet gusty","leaden clouds","smoky haze","desert heat","moonlit and crisp","cool morning air","evening cool","hazy","pressured pre-rain"],
      olay:["a lost note is found","a message goes to the wrong person","an old secret surfaces","an unexpected guest arrives","the power goes out","a misunderstanding escalates","a photo falls out of an envelope","signal drops","the key doesn't fit","the package holds a surprise","a witness disappears","heavy rain starts","the plan is exposed","tracks are wiped","a noise complaint grows","the wrong suitcase is taken","a phone answers in a different voice","an old debt is recalled","a note slides from the next table","a tiny bird flies indoors","sirens approach","a child's question changes everything","the parcel went to another address","a camera recording emerges","a familiar song is heard from afar","a map is folded the wrong way","someone pronounces your name perfectly","one shoe of a pair goes missing","a door not in the drawing is found","a small lie grows","the wrong door is knocked","a lost ring is found but refuses its owner","a new detail appears in an old photo","the appointment time differs on two papers","a letter arrives in a foreign language without translation","a rumor reverses as it spreads"]
    };

    function jget(k,d){ try{ return JSON.parse(localStorage.getItem(k)||JSON.stringify(d)); } catch(_){ return d; } }
    function jset(k,v){ localStorage.setItem(k, JSON.stringify(v)); }
    function shuffle(a){ const r=a.slice(); for(let i=r.length-1;i>0;i--){ const j=Math.floor(Math.random()*(i+1)); [r[i],r[j]]=[r[j],r[i]]; } return r; }
    async function fetchJson(u){ try{ const r=await fetch(u,{cache:'no-store'}); if(!r.ok) throw new Error(r.status+' '+u); return await r.json(); } catch(e){ console.warn('OLAY fetch fail:', e); return null; } }
    function sigOf(d){ return `y${(d.yer||[]).length}-z${(d.zaman||[]).length}-h${(d.hava||[]).length}-o${(d.olay||[]).length}`; }

    function Deck(key, arr){
      function load(){ try{ return JSON.parse(localStorage.getItem(key)||'[]'); }catch(_){ return []; } }
      function save(a){ localStorage.setItem(key, JSON.stringify(a)); }
      function next(){ let d=load(); if(!d.length){ d=shuffle(arr.slice()); save(d); } const x=d.pop(); save(d); return x; }
      function reset(){ save([]); }
      return { next, reset };
    }

    async function loadLists(lang){
      if(lang==='EN'){
        const j = await fetchJson(URL_EN);
        if(j && j.yer && j.zaman && j.hava && j.olay) return j;
        return FALLBACK_EN;
      }else{
        const j = await fetchJson(URL_TR);
        if(j && j.yer && j.zaman && j.hava && j.olay) return j;
        return FALLBACK_TR;
      }
    }
    function stateKeys(lang){
      return lang==='EN'
        ? {LS_STATE:LS_STATE_EN, LS_SIG:LS_SIG_EN}
        : {LS_STATE:LS_STATE_TR, LS_SIG:LS_SIG_TR};
    }

    async function ensureDecks(lang){
      const lists = await loadLists(lang);
      const sig   = sigOf(lists);
      const {LS_STATE, LS_SIG} = stateKeys(lang);

      const yer   = Deck(LS_STATE+'_yer',   lists.yer);
      const zaman = Deck(LS_STATE+'_zaman', lists.zaman);
      const hava  = Deck(LS_STATE+'_hava',  lists.hava);
      const olay  = Deck(LS_STATE+'_olay',  lists.olay);

      const prevSig = jget(LS_SIG, null);
      if(prevSig !== sig){
        yer.reset(); zaman.reset(); hava.reset(); olay.reset();
        jset(LS_SIG, sig);
      }
      return {yer,zaman,hava,olay};
    }

    async function drawOnce(){
      const en = document.getElementById('olay-en')?.checked;
      const lang = en ? 'EN' : 'TR';
      const decks = await ensureDecks(lang);
      const y = decks.yer.next();
      const z = decks.zaman.next();
      const h = decks.hava.next();
      const o = decks.olay.next();

      const out = document.getElementById('olay-out');
      if(!out) return;
      out.textContent = en
        ? `Place: ${y} · Time: ${z} · Weather: ${h} · Event: ${o}`
        : `Yer: ${y} · Zaman: ${z} · Hava: ${h} · Olay: ${o}`;
    }

    function hardReset(){
      [LS_STATE_TR, LS_STATE_EN].forEach(prefix=>{
        try{
          localStorage.removeItem(prefix+'_yer');
          localStorage.removeItem(prefix+'_zaman');
          localStorage.removeItem(prefix+'_hava');
          localStorage.removeItem(prefix+'_olay');
        }catch(_){}
      });
      const out = document.getElementById('olay-out');
      if(out) out.textContent = 'Havuz sıfırlandı.';
    }

    document.getElementById('olay-yeni')?.addEventListener('click', drawOnce);
    document.getElementById('olay-en')?.addEventListener('change', drawOnce);
    document.getElementById('olay-reset')?.addEventListener('click', hardReset);
    if(document.getElementById('olay-out')) drawOnce();
  })();

  /* === 🎡 Duygu Tekerleği (OFFLINE / 150 benzersiz) === */
  (function(){
    const STATE_KEY = 'kk_emotions_offline_state_v1';
    const TARGET = 150;
    const FR = {
      "Neşe": {
        lead:["Güneş perdeden sızarken","Sabah kapıyı açınca","Kaldırım taşları arasında","Pencere eşiğinde","Sokağın köşesinde","Rüzgâr saçlarımdan geçerken","Kahve kokusu yükselirken","Merdiven başında","Gün ışığı omuzlarıma düşerken","Şehrin erken saatlerinde","Kuş sesleri arasında","Bulutlar pamukken"],
        verb:["adımlarım hafifledi","gülüşüm büyüdü","içimde bir melodi başladı","kalem kendiliğinden aktı","renkler ses verdi","zaman koşmayı bıraktı","kapı kolları selam verdi","cam aynaya göz kırptı","bulutlar benimle yürüdü","gün avucumda ısındı","gölgeler dansa kalktı","pencereler içeri ışık çağırdı"],
        tail:["ve sokak adımımı tanıdı.","ben de şehri tanıdım.","kalan yorgunluk eridi.","sanki bahar cebimdeydi.","gün küçük sürprizler bıraktı.","kahve fincanı teşekkür etti.","rüzgârla beşlik çaktım.","renkler birbirini alkışladı.","gülüşüm kapıları açtı."]
      },
      "Üzüntü": {
        lead:["Pencerenin buğusunda","Sahanlığın loşunda","Uzayan sıranın sonunda","Kütüphanenin sessizliğinde","Akşamüstü çizgisinde","Yağmurun ince halinde","Ev içinin rüzgârında","Eski fotoğrafların kenarında","Sokağın ıslak taşlarında","Perde aralığında"],
        verb:["sözlerim köşeye oturdu","zaman omzuma çöktü","çay soğudu","adımlarım ağırlaştı","kalem susmayı öğrendi","ışık yerinden ayrıldı","bekleyiş büyüdü","gün renklerini sakladı","ayna beklemeyi gösterdi","şehir iç çekti"],
        tail:["ve cümle yarım kaldı.","yastığın soğuğu içime sızdı.","mektuplar adıma değildi.","adımın gölgesi benden önce yoruldu.","boşluk kapının altından girdi.","şarkı benden bir parça eksiltti."]
      },
      "Öfke": {
        lead:["Kapı eşiğinde","Asansör önünde","Dar koridorda","Otobüs durağında","Kalabalığın ortasında","Masanın kenarında","Kavanoz kapağında","Fermuarla boğuşurken","Sıra yaklaşmışken","Zemin inat ederken"],
        verb:["cümlelerim keskinleşti","nefesim büyüdü","sabırım çizildi","kilit beni yanlış sandı","zaman tik tik çentik attı","cam ısı değil öfke aldı","poşet kulpu köşede koptu","sayfa aradığım yerde yapıştı","buton basılmayı reddetti"],
        tail:["ve avucumda yumruğa dönüştü.","adımlarım zemini dövdü.","kapı cevap vermedi.","sabrımda ince bir çatlak kaldı."]
      },
      "Korku": {
        lead:["Karanlığın kıyısında","Merdiven gölgesinde","Kapının altındaki çizgide","Koridorun ortasında","Perdenin titrediği anda","Sessiz odada","Yansımayla göz göze gelirken","Dolabın içinden gelen tıkta"],
        verb:["adımım benden önce atladı","gölge boy attı","kilit içeriden nefes aldı","yankı ismimi fısıldadı","pencere içeri baktı","rüzgâr adımı yanlış söyledi","basamak ayak değmeden inledi","perde rüzgârsız kıpırdadı"],
        tail:["ve içimdeki sessizlik büyüdü.","benim sesim geride kaldı.","ışık çizgisi beni inceledi.","kapı kilitli olmadığı hâlde direndi."]
      },
      "Şaşkınlık": {
        lead:["Zamanın tökezlediği anda","Göz kırptığım sırada","Yürürken birden","Merdiven başında","Haritanın önünde","Aynanın karşısında","Bilet elimdeyken","Kapı numarasının altında"],
        verb:["görüntü değişti","anahtar odayı çevirdi","gölge benden hızlı düşündü","postacı yazılmamış mektubu getirdi","kitap beni raftan çağırdı","rüzgâr sayfayı ters çevirdi","kilit anahtara 'sen kimsin' dedi","gün ışığı yerden sızdı"],
        tail:["ve cümle ortasında yeni bir sahne açıldı.","adres beni seçti.","düğme ceketini aradı.","merdiven yukarı değil ileri çıktı."]
      },
      "Sükûnet": {
        lead:["Günün kenarında","Sabahın ilk buharında","Pencere pervazında","Koltuğun gölgesinde","Defterin beyazında","Rüzgârın yumuşak yerinde","Odanın derin köşesinde","Işığın ince çizgisinde"],
        verb:["nefesi saydım","kelimeler yumuşadı","zaman yerinde kaldı","gölge yelken açtı","kalem incitmeden ilerledi","sokak sesi kapıda yumuşadı","masa küçük sesleri sakladı","ışık bardakta dinlendi"],
        tail:["ve cümle devam etmeyi acele etmedi.","omuzlarım sabır ördü.","gün usulca oturdu.","adımlar taşlara teşekkür etti."]
      }
    };

    function jget(k,d){try{return JSON.parse(localStorage.getItem(k)||JSON.stringify(d))}catch(_){return d}}
    function jset(k,v){localStorage.setItem(k,JSON.stringify(v))}
    function shuffle(a){const r=a.slice();for(let i=r.length-1;i>0;i--){const j=Math.floor(Math.random()*(i+1));[r[i],r[j]]=[r[j],r[i]]}return r}

    function buildDeck(){
      const keys=Object.keys(FR);
      return keys.map(k=>{
        const {lead=[],verb=[],tail=[]}=FR[k];
        const Li=lead.length, Vi=verb.length, Ti=tail.length;
        const total=Li*Vi*Ti;
        const pick=Math.min(total||0, 150);
        const seen=new Set(); const out=[];
        while(out.length<pick && total>0){
          const a=Math.floor(Math.random()*Li), b=Math.floor(Math.random()*Vi), c=Math.floor(Math.random()*Ti);
          const key=a+'-'+b+'-'+c; if(seen.has(key)) continue;
          seen.add(key); out.push(`${lead[a]} ${verb[b]} ${tail[c]}`);
        }
        return {k, bas: shuffle(out)};
      });
    }

    function ensureState(){
      let st=jget(STATE_KEY,null);
      const labels=Object.keys(FR);
      if(!(st&&Array.isArray(st.remaining)&&st.remaining.length===labels.length)){
        st={ remaining: buildDeck().map(d=>shuffle(d.bas)), labels };
        jset(STATE_KEY,st);
      }
      return st;
    }

    function nextFrom(i, st){
      if(!(st.remaining[i] && st.remaining[i].length)){
        const decks=buildDeck();
        st.remaining[i]=shuffle(decks[i].bas);
      }
      const x=st.remaining[i].pop(); jset(STATE_KEY,st); return x;
    }
    function nextRandom(st){
      const i=Math.floor(Math.random()*st.remaining.length);
      return {i, text: nextFrom(i, st)};
    }

    function mount(){
      const row=document.getElementById('duygu-row'); if(!row) return;
      const st=ensureState();
      row.innerHTML='';
      const labels=st.labels;
      labels.forEach((name,i)=>{
        const b=document.createElement('button'); b.className='btn'; b.textContent=name;
        b.addEventListener('click',()=>{ document.getElementById('duygu-out').innerHTML = `<b>${name}:</b> ${nextFrom(i,st)}`; });
        row.appendChild(b);
      });
      const r=nextRandom(st);
      document.getElementById('duygu-out').innerHTML=`<b>${labels[r.i]}:</b> ${r.text}`;
      document.getElementById('duygu-random')?.addEventListener('click',()=>{ const rr=nextRandom(st); document.getElementById('duygu-out').innerHTML=`<b>${labels[rr.i]}:</b> ${rr.text}`; });
      document.getElementById('duygu-reset')?.addEventListener('click',()=>{ localStorage.removeItem(STATE_KEY); document.getElementById('duygu-out').textContent='Havuz sıfırlandı.'; });
    }
    mount();
  })();

})();
</script>
</body></html>
