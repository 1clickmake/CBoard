document.addEventListener("DOMContentLoaded", () => {
  gsap.registerPlugin(ScrollTrigger)

  // 1. Lenis 초기화
  const lenis = new Lenis({
    duration: 1.2,
    easing: t => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    smooth: true,
    smoothTouch: false,
  })

  // requestAnimationFrame 루프
  function raf(time) {
    lenis.raf(time)
    requestAnimationFrame(raf)
  }
  requestAnimationFrame(raf)

  // Lenis와 ScrollTrigger 연동
  lenis.on("scroll", ScrollTrigger.update)

  // 2. ScrollTrigger scrollerProxy 설정
  ScrollTrigger.scrollerProxy(document.documentElement, {
    scrollTop(value) {
      return arguments.length ? lenis.scrollTo(value) : window.scrollY
    },
    getBoundingClientRect() {
      return {
        top: 0,
        left: 0,
        width: window.innerWidth,
        height: window.innerHeight
      }
    },
    pinType: getComputedStyle(document.documentElement).transform !== "none" ? "transform" : "fixed"
  })

  ScrollTrigger.defaults({
    onRefresh: () => lenis.update()
  })

  // -------------------------------------------------------------
  // ★ 4. 여기에 추가: body 사이즈 변화 감지 및 리사이즈 처리
  // -------------------------------------------------------------
  const resizeObserver = new ResizeObserver(() => {
    lenis.resize();         // Lenis의 스크롤 가상 높이 재연산
    ScrollTrigger.refresh(); // scrollerProxy 및 트리거 위치 재연산
  });
  resizeObserver.observe(document.body);
  // -------------------------------------------------------------

  // 3. 초기 실행 (초기 위치 및 스크롤 바 새로고침)
  ScrollTrigger.refresh()

  // 앵커 링크 부드럽게 이동
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener("click", e => {
      e.preventDefault()
      const id = link.getAttribute("href")
      const target = document.querySelector(id)
      if (target) {
        lenis.scrollTo(target, {
          offset: 0,      // 필요 시 -헤더 높이만큼 조절 : -50
          duration: 1.2,
          easing: t => Math.min(1, 1.001 - Math.pow(2, -10 * t))
        })
      }
    })
  })
})
