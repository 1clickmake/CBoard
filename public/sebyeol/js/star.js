(() => {
  const canvas = document.getElementById('stars');
  const section = canvas.parentElement;
  const ctx = canvas.getContext('2d');
  const IMAGE_WIDTH = 1920, IMAGE_HEIGHT = 1080;
  const COUNT = 160; // 추가로 반짝이는 별 개수
  let width = 0, height = 0, dpr = 1, stars = [], raf = 0;
  const rand = (a,b) => a + Math.random() * (b-a);
  function resize() {
    const rect = section.getBoundingClientRect();
    width = rect.width; height = rect.height;
    dpr = Math.min(window.devicePixelRatio || 1, 2);
    canvas.width = Math.round(width * dpr);
    canvas.height = Math.round(height * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  }
  // 원본 이미지의 하늘 영역에만 별을 배치합니다.
  function makeStars() {
    stars = Array.from({length: COUNT}, () => ({
      x: rand(20, 1900), y: rand(25, 760),
      radius: rand(0.75, 1.5),
      base: rand(0.07, 0.19), peak: rand(0.68, 0.98),
      speed: rand(0.00125, 0.0031), phase: rand(0, Math.PI * 2)
    }));
  }
  function render(time) {
    ctx.clearRect(0, 0, width, height);
    // background-size: cover와 동일한 좌표 변환
    const scale = Math.max(width / IMAGE_WIDTH, height / IMAGE_HEIGHT);
    const offsetX = (width - IMAGE_WIDTH * scale) / 2;
    const offsetY = (height - IMAGE_HEIGHT * scale) / 2;
    for (const s of stars) {
      const x = offsetX + s.x * scale, y = offsetY + s.y * scale;
      if (x < 0 || x > width || y < 0 || y > height) continue;
      const wave = (Math.sin(time * s.speed + s.phase) + 1) / 2;
      const alpha = s.base + (s.peak - s.base) * Math.pow(wave, 2);
      ctx.beginPath();
      ctx.arc(x, y, Math.max(0.65, s.radius * Math.min(scale, 1.5)), 0, Math.PI * 2);
      ctx.fillStyle = `rgba(220,235,255,${alpha})`;
      ctx.fill();
    }
    raf = requestAnimationFrame(render);
  }
  resize(); makeStars();
  window.addEventListener('resize', resize, {passive: true});
  raf = requestAnimationFrame(render);
})();