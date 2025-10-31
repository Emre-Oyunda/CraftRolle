(function() {
  const canvas = document.getElementById('cover-canvas');
  if (!canvas) { return; }
  const ctx = canvas.getContext('2d');
  const templateSelect = document.getElementById('template');
  const titleInput = document.getElementById('title-input');
  const authorInput = document.getElementById('author-input');
  const downloadBtn = document.getElementById('upload-cover');
  const resultEl = document.getElementById('cover-result');

  const width = canvas.width;
  const height = canvas.height;

  const templates = {
    romance: {
      name: 'Romance (Pembe)',
      gradient: ['#ff9acb', '#ff6fb5', '#f35ba6'],
      titleColor: '#fff3fc',
      authorColor: '#ffe0f2',
      accent: (ctx) => {
        ctx.save();
        ctx.globalAlpha = 0.25;
        ctx.fillStyle = '#ffffff';
        for (let i = 0; i < 12; i++) {
          const x = Math.random() * width;
          const y = Math.random() * height;
          const r = 30 + Math.random() * 30;
          ctx.beginPath();
          ctx.moveTo(x, y);
          ctx.bezierCurveTo(x - r, y - r, x - 3 * r, y + r, x, y + 3 * r);
          ctx.bezierCurveTo(x + 3 * r, y + r, x + r, y - r, x, y);
          ctx.closePath();
          ctx.fill();
        }
        ctx.restore();
      }
    },
    scifi: {
      name: 'Sci-Fi',
      gradient: ['#1a0240', '#350a6d', '#0d172f'],
      titleColor: '#79e8ff',
      authorColor: '#9ecaff',
      accent: (ctx) => {
        ctx.save();
        ctx.globalAlpha = 0.45;
        ctx.strokeStyle = '#79e8ff';
        ctx.lineWidth = 3;
        for (let i = 0; i < 5; i++) {
          const offset = i * 80;
          ctx.beginPath();
          ctx.moveTo(offset, height);
          ctx.lineTo(width, height - offset);
          ctx.stroke();
        }
        ctx.setLineDash([10, 10]);
        ctx.strokeStyle = '#ff6fff';
        ctx.beginPath();
        ctx.arc(width * 0.75, height * 0.3, 90, 0, Math.PI * 2);
        ctx.stroke();
        ctx.restore();
      }
    },
    minimal: {
      name: 'Minimal',
      gradient: ['#f7f8fc', '#edeef4', '#dee1ed'],
      titleColor: '#2d2f43',
      authorColor: '#585b73',
      accent: (ctx) => {
        ctx.save();
        ctx.fillStyle = '#2d2f4330';
        ctx.fillRect(width * 0.1, height * 0.18, width * 0.8, height * 0.1);
        ctx.fillRect(width * 0.1, height * 0.65, width * 0.8, height * 0.04);
        ctx.restore();
      }
    },
    fantasy: {
      name: 'Fantasy',
      gradient: ['#261b40', '#512a88', '#a868f0'],
      titleColor: '#ffeec8',
      authorColor: '#ffe0a3',
      accent: (ctx) => {
        ctx.save();
        ctx.fillStyle = '#ffd67f55';
        for (let i = 0; i < 40; i++) {
          const x = Math.random() * width;
          const y = Math.random() * height * 0.7;
          const size = Math.random() * 4 + 2;
          ctx.beginPath();
          ctx.moveTo(x, y);
          ctx.lineTo(x + size, y + size * 3);
          ctx.lineTo(x - size, y + size * 3);
          ctx.closePath();
          ctx.fill();
        }
        ctx.globalAlpha = 0.6;
        ctx.fillStyle = '#ffe6a8';
        ctx.beginPath();
        ctx.arc(width * 0.3, height * 0.75, 120, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();
      }
    },
    noir: {
      name: 'Noir',
      gradient: ['#0d0d0f', '#1a1a1d', '#050505'],
      titleColor: '#f5f5f5',
      authorColor: '#c9c9c9',
      accent: (ctx) => {
        ctx.save();
        ctx.fillStyle = '#f5f5f522';
        ctx.fillRect(width * 0.15, 0, width * 0.07, height);
        ctx.fillRect(width * 0.45, 0, width * 0.1, height);
        ctx.fillRect(width * 0.75, 0, width * 0.05, height);
        ctx.globalAlpha = 0.4;
        ctx.beginPath();
        ctx.moveTo(0, height * 0.85);
        ctx.lineTo(width, height * 0.55);
        ctx.lineTo(width, height);
        ctx.lineTo(0, height);
        ctx.closePath();
        ctx.fill();
        ctx.restore();
      }
    },
    nature: {
      name: 'Nature',
      gradient: ['#045255', '#0c8f6d', '#8fcf6b'],
      titleColor: '#f6ffed',
      authorColor: '#def7d2',
      accent: (ctx) => {
        ctx.save();
        ctx.fillStyle = '#ffffff22';
        for (let i = 0; i < 18; i++) {
          const x = Math.random() * width;
          const y = height * 0.4 + Math.random() * height * 0.6;
          const leafW = 40 + Math.random() * 60;
          const leafH = 120 + Math.random() * 80;
          ctx.beginPath();
          ctx.ellipse(x, y, leafW, leafH, Math.random() * Math.PI, 0, Math.PI * 2);
          ctx.fill();
        }
        ctx.restore();
      }
    },
    retro: {
      name: 'Retro Pop',
      gradient: ['#ff6f61', '#ffa177', '#ffd466'],
      titleColor: '#1d1b38',
      authorColor: '#312f4d',
      accent: (ctx) => {
        ctx.save();
        const palette = ['#1d1b38', '#e8fbf7', '#ffec00', '#f43b86'];
        for (let i = 0; i < palette.length; i++) {
          ctx.fillStyle = palette[i] + '99';
          ctx.beginPath();
          ctx.arc(width * (0.2 + i * 0.2), height * (0.25 + i * 0.12), 60 + i * 18, 0, Math.PI * 2);
          ctx.fill();
        }
        ctx.restore();
      }
    }
  };

  function drawBackground(template) {
    const gradient = ctx.createLinearGradient(0, 0, 0, height);
    template.gradient.forEach((color, index) => {
      gradient.addColorStop(index / (template.gradient.length - 1), color);
    });
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, width, height);
  }

  function wrapText(text, maxWidth, font, lineHeight) {
    ctx.font = font;
    const words = text.trim().split(/\s+/);
    const lines = [];
    let currentLine = words.shift() || '';
    words.forEach(word => {
      const testLine = currentLine + ' ' + word;
      const metrics = ctx.measureText(testLine);
      if (metrics.width > maxWidth) {
        lines.push(currentLine);
        currentLine = word;
      } else {
        currentLine = testLine;
      }
    });
    lines.push(currentLine);
    return lines.filter(Boolean);
  }

  function drawCover() {
    const key = templateSelect.value;
    const template = templates[key] || templates.romance;
    const title = titleInput.value || bookTitleFallback();
    const author = authorInput.value || 'Bilinmeyen Yazar';

    drawBackground(template);
    template.accent(ctx);

    ctx.fillStyle = template.titleColor;
    ctx.textAlign = 'center';

    const titleFont = template.titleFont || 'bold 64px "Playfair Display", serif';
    const titleLines = wrapText(title.toUpperCase(), width * 0.7, titleFont, 72);
    const titleStartY = height * 0.35 - ((titleLines.length - 1) * 36);
    titleLines.forEach((line, index) => {
      ctx.font = titleFont;
      ctx.fillText(line, width / 2, titleStartY + index * 72);
    });

    ctx.fillStyle = template.authorColor;
    const authorFont = template.authorFont || '600 34px "Montserrat", sans-serif';
    ctx.font = authorFont;
    ctx.fillText(author, width / 2, height * 0.82);

    ctx.font = 'italic 24px "Montserrat", sans-serif';
    ctx.globalAlpha = 0.75;
    ctx.fillText('Craftrolle Kapak Atölyesi', width / 2, height * 0.9);
    ctx.globalAlpha = 1;
  }

  function bookTitleFallback() {
    const options = [
      'Dokunuş', 'Yıldız Tozu', 'Sessiz Rüya', 'Gölgelerin Ötesi', 'Kayıp Bahar', 'Sonsuz Döngü'
    ];
    return options[Math.floor(Math.random() * options.length)];
  }

  function updateCover() {
    drawCover();
  }

  templateSelect.addEventListener('change', updateCover);
  titleInput.addEventListener('input', updateCover);
  authorInput.addEventListener('input', updateCover);

  downloadBtn.addEventListener('click', () => {
    const dataUrl = canvas.toDataURL('image/png');
    const link = document.createElement('a');
    link.download = (titleInput.value || 'craftrolle_kapak') + '.png';
    link.href = dataUrl;
    link.click();
    if (resultEl) {
      resultEl.textContent = 'PNG olarak indirildi.';
    }
  });

  drawCover();
})();
