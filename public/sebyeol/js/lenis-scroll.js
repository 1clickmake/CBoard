gsap.registerPlugin(ScrollTrigger);

// 1. Lenis 초기화
const lenis = new Lenis({
  duration: 1.5,
  easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
  smooth: true // Lenis v1.x 버전에 따라 smooth 대신 smoothWheel: true 일 수 있습니다.
});

// 2. Lenis 스크롤 프레임 동기화
function raf(time) {
  lenis.raf(time);
  ScrollTrigger.update(); // 매 프레임마다 ScrollTrigger 강제 업데이트
  requestAnimationFrame(raf);
}
requestAnimationFrame(raf);

// 3. 스크롤 이벤트 발생 시 AOS 및 Waypoint 리프레시
lenis.on('scroll', () => {
  AOS.refresh();
  if (typeof Waypoint !== 'undefined') {
    Waypoint.refreshAll();
  }
});

// ★ 4. 추가: body 사이즈 변화 감지 (초기 멈춤 현상 해결 핵심)
const resizeObserver = new ResizeObserver(() => {
  lenis.resize();         // Lenis 스크롤 영역 재계산
  ScrollTrigger.refresh(); // GSAP 애니메이션 트리거 위치 재계산 (추천)
});
resizeObserver.observe(document.body);


// 앵커 링크 부드러운 이동 (기존 코드 유지)
document.querySelectorAll('a[href^="#"]').forEach(link => {
  link.addEventListener('click', e => {
    e.preventDefault();
    const targetId = link.getAttribute('href');
    const targetEl = document.querySelector(targetId);
    if (targetEl) {
      lenis.scrollTo(targetEl, {
        offset: 0,
        duration: 1.2,
        easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t))
      });
    }
  });
});