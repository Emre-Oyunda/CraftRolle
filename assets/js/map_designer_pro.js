(function () {
  'use strict';

  const canvas = document.getElementById('map-canvas');
  if (!canvas) { return; }

  const ctx = canvas.getContext('2d');
  const stageEl = document.querySelector('.map-stage');
  const paletteEl = document.getElementById('palette');
  const resultEl = document.getElementById('map-result');
  const toolButtons = {
    terrain: document.getElementById('tool-terrain'),
    object: document.getElementById('tool-object'),
    erase: document.getElementById('tool-erase')
  };
  const objTypeSelect = document.getElementById('obj-type');
  const objSizeSelect = document.getElementById('obj-size');
  const undoBtn = document.getElementById('undo');
  const redoBtn = document.getElementById('redo');
  const toggleRainBtn = document.getElementById('toggle-rain');
  const focusWaterBtn = document.getElementById('focus-water');
  const savePngBtn = document.getElementById('save-png');
  const saveJsonBtn = document.getElementById('save-json');
  const loadJsonInput = document.getElementById('load-json');
  const clearBtn = document.getElementById('clear-map');
  const statTerrain = document.getElementById('stat-terrain');
  const statObjects = document.getElementById('stat-objects');
  const statWeather = document.getElementById('stat-weather');

  const baseWidth = 960;
  const baseHeight = 640;
  const dpr = window.devicePixelRatio || 1;
  canvas.style.width = baseWidth + 'px';
  canvas.style.height = baseHeight + 'px';
  canvas.width = baseWidth * dpr;
  canvas.height = baseHeight * dpr;
  ctx.scale(dpr, dpr);

  const cols = 48;
  const rows = 32;
  const tileSize = baseWidth / cols;
  const brushRadius = 1.1;

  const terrainPalette = [
    { id: 'deepwater', label: 'Derin Deniz', short: 'Deniz', gradient: ['#04263f', '#0b3c5d'], noise: 'rgba(96, 178, 255, 0.25)', edge: 'rgba(10, 54, 83, 0.6)', pattern: 'waves' },
    { id: 'shore', label: 'Lagün Suları', short: 'Lagün', gradient: ['#1c8bb8', '#6dd5e8'], noise: 'rgba(255, 255, 255, 0.4)', edge: 'rgba(38, 132, 170, 0.45)', pattern: 'foam' },
    { id: 'grass', label: 'Çayır Ovaları', short: 'Ovalar', gradient: ['#4c9f47', '#8ddc6f'], noise: 'rgba(255, 255, 255, 0.12)', edge: 'rgba(46, 122, 54, 0.35)', pattern: 'specks' },
    { id: 'forest', label: 'Yoğun Orman', short: 'Orman', gradient: ['#2f5d3a', '#3f834a'], noise: 'rgba(23, 52, 30, 0.55)', edge: 'rgba(20, 45, 28, 0.45)', pattern: 'trees' },
    { id: 'mountain', label: 'Dağ Sırtı', short: 'Dağ', gradient: ['#8b7460', '#c3b19f'], noise: 'rgba(255, 255, 255, 0.28)', edge: 'rgba(99, 78, 58, 0.4)', pattern: 'ridges' },
    { id: 'desert', label: 'Altın Kumlar', short: 'Çöl', gradient: ['#d6a864', '#f3d688'], noise: 'rgba(255, 225, 164, 0.45)', edge: 'rgba(170, 123, 52, 0.4)', pattern: 'dunes' },
    { id: 'snow', label: 'Buz Yalçınları', short: 'Buz', gradient: ['#f8fbff', '#c7d9f5'], noise: 'rgba(212, 230, 255, 0.6)', edge: 'rgba(170, 191, 219, 0.42)', pattern: 'cracks' },
    { id: 'road', label: 'Kervan Yolu', short: 'Yol', gradient: ['#a7865a', '#d7c6ac'], noise: 'rgba(255, 255, 255, 0.35)', edge: 'rgba(125, 96, 58, 0.45)', pattern: 'path' }
  ];

  const terrainMap = terrainPalette.reduce((acc, terrain) => {
    acc[terrain.id] = terrain;
    return acc;
  }, {});

  const objectIcons = {
    tree: '🌲',
    village: '🏘️',
    castle: '🏰',
    harbor: '⚓',
    tower: '🗼',
    monument: '🗿',
    ship: '🚢'
  };

  const state = {
    tiles: new Array(cols * rows).fill('deepwater'),
    objects: [],
    tool: 'terrain',
    selectedTerrain: 'grass',
    objectType: objTypeSelect ? objTypeSelect.value : 'tree',
    objectScale: objSizeSelect ? parseFloat(objSizeSelect.value) : 1,
    isRaining: false
  };

  const undoStack = [];
  const redoStack = [];
  const maxHistory = 80;
  const rainDrops = Array.from({ length: 90 }, () => createDrop());

  let lastTile = null;
  let isPointerDown = false;
  let animationId = null;

  function setResult(message, isError) {
    if (!resultEl) { return; }
    resultEl.textContent = message || '';
    resultEl.style.color = isError ? 'var(--danger, #ff6b7f)' : '';
  }

  function createDrop() {
    return {
      x: Math.random() * baseWidth,
      y: Math.random() * baseHeight,
      length: 10 + Math.random() * 10,
      speed: 3 + Math.random() * 3
    };
  }

  function advanceRain() {
    for (const drop of rainDrops) {
      drop.y += drop.speed;
      drop.x += Math.sin(drop.y * 0.02) * 0.6;
      if (drop.y > baseHeight + drop.length) {
        drop.y = -20 - Math.random() * 40;
        drop.x = Math.random() * baseWidth;
      }
    }
  }

  function startRainLoop() {
    if (animationId) { return; }
    const step = () => {
      if (!state.isRaining) {
        animationId = null;
        render();
        return;
      }
      advanceRain();
      render();
      animationId = window.requestAnimationFrame(step);
    };
    animationId = window.requestAnimationFrame(step);
  }

  function stopRainLoop() {
    if (animationId) {
      window.cancelAnimationFrame(animationId);
      animationId = null;
    }
    render();
  }

  function seededRandom(x, y, offset) {
    const seed = ((x + 37) * 73856093) ^ ((y + 91) * 19349663) ^ ((offset || 0) * 83492791);
    const s = Math.sin(seed) * 43758.5453123;
    return s - Math.floor(s);
  }

  function idx(col, row) {
    return row * cols + col;
  }

  function inBounds(col, row) {
    return col >= 0 && row >= 0 && col < cols && row < rows;
  }

  function drawBase() {
    const gradient = ctx.createLinearGradient(0, 0, 0, baseHeight);
    gradient.addColorStop(0, '#09243b');
    gradient.addColorStop(0.35, '#123c5c');
    gradient.addColorStop(1, '#1d5e7a');
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, baseWidth, baseHeight);

    const glow = ctx.createRadialGradient(baseWidth * 0.52, baseHeight * 0.38, 80, baseWidth * 0.55, baseHeight * 0.42, baseWidth * 0.85);
    glow.addColorStop(0, 'rgba(255, 255, 255, 0.13)');
    glow.addColorStop(1, 'rgba(255, 255, 255, 0)');
    ctx.fillStyle = glow;
    ctx.fillRect(0, 0, baseWidth, baseHeight);
  }

  function drawTerrainTile(style, col, row) {
    const px = col * tileSize;
    const py = row * tileSize;
    const grad = ctx.createLinearGradient(px, py, px, py + tileSize);
    const stops = style.gradient.length - 1;
    style.gradient.forEach((color, index) => {
      const ratio = stops <= 0 ? 0 : index / stops;
      grad.addColorStop(ratio, color);
    });

    ctx.fillStyle = grad;
    ctx.fillRect(px, py, tileSize, tileSize);

    ctx.save();
    drawPattern(style, col, row, px, py);
    ctx.restore();

    if (style.edge) {
      ctx.strokeStyle = style.edge;
      ctx.lineWidth = 0.6;
      ctx.strokeRect(px + 0.2, py + 0.2, tileSize - 0.4, tileSize - 0.4);
    }
  }

  function drawPattern(style, col, row, px, py) {
    switch (style.pattern) {
      case 'waves': {
        ctx.globalAlpha = 0.28;
        ctx.strokeStyle = style.noise;
        ctx.lineWidth = 1.2;
        for (let i = 0; i < 2; i++) {
          const offset = seededRandom(col, row, i) * tileSize * 0.3;
          ctx.beginPath();
          ctx.moveTo(px, py + tileSize * (0.25 + i * 0.35) + offset * 0.1);
          ctx.quadraticCurveTo(px + tileSize * 0.5, py + tileSize * (0.18 + i * 0.3) + offset, px + tileSize, py + tileSize * (0.3 + i * 0.28) + offset * 0.15);
          ctx.stroke();
        }
        break;
      }
      case 'foam': {
        ctx.globalAlpha = 0.32;
        ctx.fillStyle = style.noise;
        for (let i = 0; i < 4; i++) {
          const rx = px + seededRandom(col, row, i) * tileSize;
          const ry = py + seededRandom(col, row, i + 10) * tileSize * 0.4;
          const r = 4 + seededRandom(col, row, i + 20) * 6;
          ctx.beginPath();
          ctx.ellipse(rx, ry, r, r * 0.6, 0, 0, Math.PI * 2);
          ctx.fill();
        }
        break;
      }
      case 'specks': {
        ctx.globalAlpha = 0.28;
        ctx.fillStyle = style.noise;
        for (let i = 0; i < 5; i++) {
          const rx = px + seededRandom(col, row, i) * tileSize;
          const ry = py + seededRandom(col, row, i + 12) * tileSize;
          ctx.fillRect(rx, ry, 1.8, 1.8);
        }
        break;
      }
      case 'trees': {
        ctx.globalAlpha = 0.45;
        ctx.strokeStyle = style.noise;
        ctx.lineWidth = 1.4;
        for (let i = 0; i < 3; i++) {
          const rx = px + (0.2 + seededRandom(col, row, i) * 0.6) * tileSize;
          const baseY = py + tileSize * (0.35 + i * 0.18);
          ctx.beginPath();
          ctx.moveTo(rx, baseY + 6);
          ctx.lineTo(rx - 6, baseY - 8);
          ctx.lineTo(rx + 6, baseY - 8);
          ctx.closePath();
          ctx.stroke();
        }
        break;
      }
      case 'ridges': {
        ctx.globalAlpha = 0.36;
        ctx.strokeStyle = style.noise;
        ctx.lineWidth = 1.1;
        ctx.beginPath();
        ctx.moveTo(px + tileSize * 0.2, py + tileSize * 0.75);
        ctx.lineTo(px + tileSize * 0.5, py + tileSize * 0.3);
        ctx.lineTo(px + tileSize * 0.8, py + tileSize * 0.7);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(px + tileSize * 0.35, py + tileSize * 0.8);
        ctx.lineTo(px + tileSize * 0.6, py + tileSize * 0.45);
        ctx.stroke();
        break;
      }
      case 'dunes': {
        ctx.globalAlpha = 0.32;
        ctx.strokeStyle = style.noise;
        ctx.lineWidth = 1.1;
        for (let i = 0; i < 2; i++) {
          const offset = seededRandom(col, row, i) * 0.2;
          ctx.beginPath();
          ctx.moveTo(px, py + tileSize * (0.3 + i * 0.3 + offset));
          ctx.bezierCurveTo(px + tileSize * 0.3, py + tileSize * (0.22 + i * 0.32 + offset), px + tileSize * 0.65, py + tileSize * (0.4 + i * 0.34 + offset), px + tileSize, py + tileSize * (0.28 + i * 0.32 + offset));
          ctx.stroke();
        }
        break;
      }
      case 'cracks': {
        ctx.globalAlpha = 0.45;
        ctx.strokeStyle = style.noise;
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(px + tileSize * 0.2, py + tileSize * 0.2);
        ctx.lineTo(px + tileSize * 0.4, py + tileSize * 0.5);
        ctx.lineTo(px + tileSize * 0.25, py + tileSize * 0.8);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(px + tileSize * 0.7, py + tileSize * 0.15);
        ctx.lineTo(px + tileSize * 0.55, py + tileSize * 0.45);
        ctx.lineTo(px + tileSize * 0.78, py + tileSize * 0.72);
        ctx.stroke();
        break;
      }
      case 'path': {
        ctx.globalAlpha = 0.7;
        ctx.fillStyle = style.noise;
        const pathWidth = tileSize * 0.38;
        ctx.fillRect(px + tileSize * 0.31, py, pathWidth, tileSize);
        ctx.globalAlpha = 0.35;
        ctx.strokeStyle = 'rgba(73, 54, 34, 0.6)';
        ctx.lineWidth = 1;
        ctx.strokeRect(px + tileSize * 0.31, py, pathWidth, tileSize);
        break;
      }
      default:
        break;
    }
  }

  function drawTerrain() {
    for (let row = 0; row < rows; row++) {
      for (let col = 0; col < cols; col++) {
        const tileId = state.tiles[idx(col, row)];
        const style = terrainMap[tileId] || terrainMap.deepwater;
        drawTerrainTile(style, col, row);
      }
    }
  }

  function drawObjects() {
    ctx.save();
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    for (const obj of state.objects) {
      const icon = objectIcons[obj.type] || '❓';
      const size = 22 * (obj.size || 1);
      ctx.font = `bold ${size}px "Segoe UI Emoji", "Apple Color Emoji", "Noto Color Emoji", sans-serif`;
      const cx = (obj.col + 0.5) * tileSize;
      const cy = (obj.row + 0.5) * tileSize;
      ctx.shadowColor = 'rgba(0, 0, 0, 0.35)';
      ctx.shadowBlur = 6;
      ctx.shadowOffsetY = 2;
      ctx.fillText(icon, cx, cy);
    }
    ctx.restore();
  }

  function drawRain() {
    ctx.save();
    ctx.globalAlpha = 0.45;
    ctx.strokeStyle = 'rgba(226, 242, 255, 0.85)';
    ctx.lineWidth = 1.3;
    for (const drop of rainDrops) {
      ctx.beginPath();
      ctx.moveTo(drop.x, drop.y);
      ctx.lineTo(drop.x - 2, drop.y - drop.length);
      ctx.stroke();
    }
    ctx.restore();
  }

  function render() {
    ctx.clearRect(0, 0, baseWidth, baseHeight);
    drawBase();
    drawTerrain();
    drawObjects();
    if (state.isRaining) {
      drawRain();
    }
  }

  function renderPalette() {
    if (!paletteEl) { return; }
    paletteEl.innerHTML = '';
    terrainPalette.forEach((terrain) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'palette-btn';
      if (terrain.id === state.selectedTerrain) {
        btn.classList.add('is-active');
      }
      const sample = document.createElement('span');
      sample.className = 'sample';
      const lastColor = terrain.gradient[terrain.gradient.length - 1] || terrain.gradient[0];
      sample.style.background = `linear-gradient(135deg, ${terrain.gradient[0]}, ${lastColor})`;
      const label = document.createElement('span');
      label.textContent = terrain.short;
      btn.append(sample, label);
      btn.addEventListener('click', () => {
        state.selectedTerrain = terrain.id;
        updatePaletteActive();
        setResult(`${terrain.label} aktif.`);
      });
      paletteEl.appendChild(btn);
    });
  }

  function updatePaletteActive() {
    if (!paletteEl) { return; }
    const buttons = paletteEl.querySelectorAll('.palette-btn');
    buttons.forEach((btn, index) => {
      const terrain = terrainPalette[index];
      if (!terrain) { return; }
      btn.classList.toggle('is-active', terrain.id === state.selectedTerrain);
    });
  }

  function updateToolButtons() {
    Object.keys(toolButtons).forEach((key) => {
      const btn = toolButtons[key];
      if (!btn) { return; }
      btn.classList.toggle('is-active', state.tool === key);
    });
  }

  function serializeState() {
    return {
      tiles: state.tiles.slice(),
      objects: state.objects.map((obj) => ({ ...obj }))
    };
  }

  function restoreState(snapshot) {
    state.tiles = snapshot.tiles.slice();
    state.objects = snapshot.objects.map((obj) => ({ ...obj }));
    render();
    updateStats();
  }

  function pushHistory() {
    undoStack.push(serializeState());
    if (undoStack.length > maxHistory) {
      undoStack.shift();
    }
    redoStack.length = 0;
  }

  function paintTerrain(col, row) {
    for (let y = row - 2; y <= row + 2; y++) {
      for (let x = col - 2; x <= col + 2; x++) {
        if (!inBounds(x, y)) { continue; }
        const distance = Math.hypot(x - col, y - row);
        if (distance > brushRadius * 1.4) { continue; }
        state.tiles[idx(x, y)] = state.selectedTerrain;
      }
    }
  }

  function removeObjectAt(col, row) {
    const indexToRemove = state.objects.findIndex((obj) => Math.abs(obj.col - col) < 0.5 && Math.abs(obj.row - row) < 0.5);
    if (indexToRemove > -1) {
      state.objects.splice(indexToRemove, 1);
      return true;
    }
    return false;
  }

  function eraseAt(col, row) {
    const removed = removeObjectAt(col, row);
    if (removed) {
      return;
    }
    state.tiles[idx(col, row)] = 'deepwater';
  }

  function placeObject(col, row) {
    state.objects = state.objects.filter((obj) => !(obj.col === col && obj.row === row));
    state.objects.push({
      type: state.objectType,
      size: state.objectScale,
      col,
      row
    });
  }

  function sculptCoast(col, row) {
    const radius = 3;
    for (let y = row - radius; y <= row + radius; y++) {
      for (let x = col - radius; x <= col + radius; x++) {
        if (!inBounds(x, y)) { continue; }
        const distance = Math.hypot(x - col, y - row);
        if (distance <= 1.2) {
          state.tiles[idx(x, y)] = 'grass';
        } else if (distance <= 2.2) {
          state.tiles[idx(x, y)] = 'shore';
        } else if (distance <= radius) {
          state.tiles[idx(x, y)] = 'deepwater';
        }
      }
    }
  }

  function getTileFromEvent(event) {
    const rect = canvas.getBoundingClientRect();
    const scaleX = baseWidth / rect.width;
    const scaleY = baseHeight / rect.height;
    const canvasX = (event.clientX - rect.left) * scaleX;
    const canvasY = (event.clientY - rect.top) * scaleY;
    const col = Math.floor(canvasX / tileSize);
    const row = Math.floor(canvasY / tileSize);
    if (!inBounds(col, row)) { return null; }
    return { col, row };
  }

  function updateStats() {
    if (statTerrain) {
      const unique = new Set(state.tiles.filter((tile) => tile !== 'deepwater'));
      statTerrain.textContent = unique.size;
    }
    if (statObjects) {
      statObjects.textContent = state.objects.length;
    }
    if (statWeather) {
      statWeather.textContent = state.isRaining ? 'Yağışlı' : 'Açık';
    }
  }

  function handlePointerDown(event) {
    const tile = getTileFromEvent(event);
    if (!tile) { return; }
    lastTile = tile;
    pushHistory();
    if (event.altKey) {
      eraseAt(tile.col, tile.row);
      render();
      updateStats();
      return;
    }
    if (state.tool === 'object') {
      placeObject(tile.col, tile.row);
      render();
      updateStats();
      return;
    }
    isPointerDown = true;
    if (state.tool === 'erase') {
      eraseAt(tile.col, tile.row);
    } else {
      paintTerrain(tile.col, tile.row);
    }
    render();
    updateStats();
    if (canvas.setPointerCapture) {
      canvas.setPointerCapture(event.pointerId);
    }
  }

  function handlePointerMove(event) {
    const tile = getTileFromEvent(event);
    if (tile) {
      lastTile = tile;
    }
    if (!isPointerDown) { return; }
    if (!tile) { return; }
    if (event.altKey || state.tool === 'erase') {
      eraseAt(tile.col, tile.row);
    } else {
      paintTerrain(tile.col, tile.row);
    }
    render();
    updateStats();
  }

  function handlePointerUp(event) {
    isPointerDown = false;
    if (canvas.releasePointerCapture) {
      try {
        canvas.releasePointerCapture(event.pointerId);
      } catch (err) {
        /* ignore */
      }
    }
  }

  function handleUndo() {
    if (!undoStack.length) {
      setResult('Geri alınacak işlem yok.', true);
      return;
    }
    const snapshot = undoStack.pop();
    redoStack.push(serializeState());
    restoreState(snapshot);
    setResult('Bir adım geri alındı.');
  }

  function handleRedo() {
    if (!redoStack.length) {
      setResult('İleri alınacak işlem yok.', true);
      return;
    }
    undoStack.push(serializeState());
    const snapshot = redoStack.pop();
    restoreState(snapshot);
    setResult('Bir adım ileri alındı.');
  }

  function handleSavePng() {
    setResult('PNG hazırlanıyor...');
    const pack = () => {
      if (canvas.toBlob) {
        canvas.toBlob((blob) => {
          if (!blob) {
            setResult('PNG oluşturulamadı, tekrar deneyin.', true);
            return;
          }
          const url = URL.createObjectURL(blob);
          const link = document.createElement('a');
          link.href = url;
          link.download = 'craftrolle_harita.png';
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          URL.revokeObjectURL(url);
          const now = new Date();
          setResult('PNG indirildi (' + now.toLocaleTimeString('tr-TR') + ')');
        }, 'image/png');
      } else {
        const dataUrl = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.href = dataUrl;
        link.download = 'craftrolle_harita.png';
        link.click();
        setResult('PNG indirildi.');
      }
    };
    pack();
  }

  function handleSaveJson() {
    const payload = JSON.stringify({
      version: '1.0',
      cols,
      rows,
      tiles: state.tiles,
      objects: state.objects
    });
    const blob = new Blob([payload], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'craftrolle_harita.json';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    setResult('JSON dışa aktarıldı.');
  }

  function handleLoadJson(event) {
    const file = event.target.files && event.target.files[0];
    if (!file) { return; }
    const reader = new FileReader();
    reader.onload = (loadEvent) => {
      try {
        const data = JSON.parse(String(loadEvent.target.result));
        if (data.cols !== cols || data.rows !== rows || !Array.isArray(data.tiles)) {
          throw new Error('Uyumsuz harita boyutu');
        }
        pushHistory();
        state.tiles = data.tiles.slice(0, cols * rows);
        state.objects = Array.isArray(data.objects) ? data.objects.map((obj) => ({
          type: obj.type,
          size: obj.size || 1,
          col: obj.col,
          row: obj.row
        })) : [];
        render();
        updateStats();
        setResult('JSON haritası yüklendi.');
      } catch (err) {
        console.error(err);
        setResult('JSON okunamadı. Dosyayı kontrol edin.', true);
      } finally {
        loadJsonInput.value = '';
      }
    };
    reader.readAsText(file, 'utf-8');
  }

  function handleClear() {
    if (!window.confirm('Haritayı tamamen temizlemek istediğine emin misin?')) {
      return;
    }
    pushHistory();
    state.tiles.fill('deepwater');
    state.objects = [];
    render();
    updateStats();
    setResult('Harita sıfırlandı.');
  }

  function handleFocusWater() {
    if (!lastTile) {
      setResult('Önce haritada bir noktaya dokunun.', true);
      return;
    }
    pushHistory();
    sculptCoast(lastTile.col, lastTile.row);
    render();
    updateStats();
    setResult('Doğal kıyı hattı oluşturuldu.');
  }

  function setTool(tool) {
    state.tool = tool;
    updateToolButtons();
    const label = tool === 'terrain' ? 'Arazi boyama aktif.' : tool === 'object' ? 'Yerleşim ikonu ekleme aktif.' : 'Silgi modu aktif.';
    setResult(label);
  }

  if (toolButtons.terrain) {
    toolButtons.terrain.addEventListener('click', () => setTool('terrain'));
  }
  if (toolButtons.object) {
    toolButtons.object.addEventListener('click', () => setTool('object'));
  }
  if (toolButtons.erase) {
    toolButtons.erase.addEventListener('click', () => setTool('erase'));
  }

  if (objTypeSelect) {
    objTypeSelect.addEventListener('change', () => {
      state.objectType = objTypeSelect.value;
      setResult('Obje tipi: ' + objTypeSelect.options[objTypeSelect.selectedIndex].text);
    });
  }

  if (objSizeSelect) {
    objSizeSelect.addEventListener('change', () => {
      state.objectScale = parseFloat(objSizeSelect.value) || 1;
      setResult('Obje boyutu güncellendi.');
    });
  }

  if (undoBtn) {
    undoBtn.addEventListener('click', handleUndo);
  }
  if (redoBtn) {
    redoBtn.addEventListener('click', handleRedo);
  }
  if (toggleRainBtn) {
    toggleRainBtn.addEventListener('click', () => {
      state.isRaining = !state.isRaining;
      if (state.isRaining) {
        toggleRainBtn.textContent = '⛈️ Yağmur Açık';
        startRainLoop();
      } else {
        toggleRainBtn.textContent = '🌧️ Yağmur Efekti';
        stopRainLoop();
      }
      updateStats();
      setResult(state.isRaining ? 'Yağmur efekti açıldı.' : 'Yağmur efekti kapandı.');
    });
  }

  if (focusWaterBtn) {
    focusWaterBtn.addEventListener('click', handleFocusWater);
  }

  if (savePngBtn) {
    savePngBtn.addEventListener('click', handleSavePng);
  }
  if (saveJsonBtn) {
    saveJsonBtn.addEventListener('click', handleSaveJson);
  }
  if (loadJsonInput) {
    loadJsonInput.addEventListener('change', handleLoadJson);
  }
  if (clearBtn) {
    clearBtn.addEventListener('click', handleClear);
  }

  canvas.addEventListener('pointerdown', handlePointerDown);
  canvas.addEventListener('pointermove', handlePointerMove);
  canvas.addEventListener('pointerup', handlePointerUp);
  canvas.addEventListener('pointerleave', () => { isPointerDown = false; });
  canvas.addEventListener('pointercancel', () => { isPointerDown = false; });
  canvas.addEventListener('contextmenu', (event) => event.preventDefault());

  if (stageEl) {
    stageEl.addEventListener('dblclick', () => {
      stageEl.classList.toggle('zoomed');
      const zoomed = stageEl.classList.contains('zoomed');
      setResult(zoomed ? 'Harita yakınlaştırıldı.' : 'Harita normal görünüme döndü.');
    });
  }

  renderPalette();
  updatePaletteActive();
  updateToolButtons();
  updateStats();
  render();
  setResult('Harita stüdyosu hazır. Arazi boyamaya başlayabilirsin!');
})();
