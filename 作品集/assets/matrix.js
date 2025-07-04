// Matrix 數字雨動畫（全頁背景+hero區塊，速度統一）
document.addEventListener('DOMContentLoaded', function() {
  // 全頁背景
  const rainInterval = 70; // 統一速度
  const fontSize = 28;
  const symbols = ['❤','★','✿','❀','❁','☀','☁','☂','☃','♫','♪','☘','☾','☽','☀','☁','☂','☃','❄','❅','❆','✧','✦','✩','✪','✫','✬','✭','✮','✯','✰','❣','❥','❦','❧','♥','♡','❀','❁','✿','★','☆'];
  const colors = ['#ffb6c1','#ffd700','#aeeeee','#b39ddb','#ffecb3','#b2dfdb','#f8bbd0','#c5e1a5','#fff59d','#b3e5fc','#f48fb1','#fbc02d','#81d4fa','#f06292','#ffd54f','#aed581','#ce93d8'];
  // 全頁
  const bgCanvas = document.getElementById('matrix-canvas-bg');
  if (bgCanvas) {
    const ctx = bgCanvas.getContext('2d');
    let width = window.innerWidth, height = window.innerHeight;
    function resize() {
      width = bgCanvas.width = window.innerWidth;
      height = bgCanvas.height = window.innerHeight;
      initRain();
    }
    window.addEventListener('resize', resize);
    let columns, drops;
    function initRain() {
      columns = Math.floor(width / fontSize);
      drops = Array(columns).fill(1);
    }
    resize();
    let lastDraw = 0;
    function draw(now) {
      if (!lastDraw || now - lastDraw > rainInterval) {
        ctx.fillStyle = 'rgba(255,255,255,0.13)';
        ctx.fillRect(0, 0, width, height);
        ctx.font = fontSize + 'px Noto Sans TC, Arial, sans-serif';
        for (let i = 0; i < columns; i++) {
          const text = symbols[Math.floor(Math.random() * symbols.length)];
          ctx.fillStyle = colors[Math.floor(Math.random() * colors.length)];
          ctx.fillText(text, i * fontSize, drops[i] * fontSize);
          if (Math.random() > 0.985) {
            drops[i] = 0;
          }
          drops[i]++;
          if (drops[i] * fontSize > height) drops[i] = 0;
        }
        lastDraw = now;
      }
      requestAnimationFrame(draw);
    }
    requestAnimationFrame(draw);
  }
  // hero 專屬
  const heroCanvas = document.getElementById('matrix-canvas');
  if (heroCanvas) {
    const ctx = heroCanvas.getContext('2d');
    let width = heroCanvas.offsetWidth, height = heroCanvas.offsetHeight;
    function resize() {
      width = heroCanvas.offsetWidth = heroCanvas.clientWidth = heroCanvas.parentElement.offsetWidth;
      height = heroCanvas.offsetHeight = heroCanvas.clientHeight = heroCanvas.parentElement.offsetHeight;
      heroCanvas.width = width;
      heroCanvas.height = height;
      updateRain();
    }
    window.addEventListener('resize', resize);
    let columns = Math.floor(width / fontSize);
    let drops = Array(columns).fill(1);
    function updateRain() {
      columns = Math.floor(width / fontSize);
      drops = Array(columns).fill(1);
    }
    resize();
    let lastDraw = 0;
    function draw(now) {
      if (!lastDraw || now - lastDraw > rainInterval) {
        ctx.clearRect(0, 0, width, height);
        ctx.font = fontSize + 'px Noto Sans TC, Arial, sans-serif';
        for (let i = 0; i < columns; i++) {
          const text = symbols[Math.floor(Math.random() * symbols.length)];
          ctx.fillStyle = colors[Math.floor(Math.random() * colors.length)];
          ctx.fillText(text, i * fontSize, drops[i] * fontSize);
          if (Math.random() > 0.975) {
            drops[i] = 0;
          }
          drops[i]++;
          if (drops[i] * fontSize > height) drops[i] = 0;
        }
        lastDraw = now;
      }
      requestAnimationFrame(draw);
    }
    requestAnimationFrame(draw);
  }
});