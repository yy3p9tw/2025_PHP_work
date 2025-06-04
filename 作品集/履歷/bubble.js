const bubbleCount = 20;
const bubbles = document.querySelector('.bubbles');
for(let i=0; i<bubbleCount; i++) {
  const span = document.createElement('span');
  // 隨機 left
  span.style.left = Math.random() * 100 + 'vw';
  // 隨機動畫時間
  span.style.animationDuration = (12 + Math.random() * 8) + 's';
  // 隨機寬高
  const size = 40 + Math.random() * 40;
  span.style.width = size + 'px';
  span.style.height = size + 'px';
  bubbles.appendChild(span);
}