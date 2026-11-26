/* Please ❤ this if you like it! */


(function($) { "use strict";		

// 모바일메뉴 사이트맵- 모바일로 전환
(function () {
    var $lnb = $('.nav__content');
    var $li = $lnb.find('.lnb__li');

    function initMenu() {
        // 기존 토글 제거 + 이벤트 해제
        $li.find('.toggle').remove();
        $li.find('>a').off('click');
        $li.find('.lnb__sub').stop(true, true);

        if ($(window).width() < 769) {
            // 모바일: 세로 정렬 + 숨김
            $li.find('.lnb__sub')
                .css({'display': 'block'}) // 세로 정렬
                .hide(); // 처음에는 안 보이게

            // 토글 버튼 추가
            $li.each(function(){
                if($(this).find('.lnb__sub').length > 0){
                    $(this).append('<span class="toggle"></span>');
                }
            });

            // 부드러운 전환
            $li.find('>a, .toggle').on('click', function (e) {
                var $parent = $(this).parent();

                // 하위 메뉴가 없으면 → 링크 그대로 이동
                if ($parent.find('.lnb__sub').length === 0) {
                    return;
                }

                // 하위 메뉴가 있을 때만 토글 동작
                e.preventDefault();

                if ($parent.hasClass('is-on')) {
                    $parent.removeClass('is-on')
                        .find('.lnb__sub').stop(true, true).slideUp(200);
                } else {
                    $parent.addClass('is-on')
                        .find('.lnb__sub').stop(true, true).slideDown(200);
                    $parent.siblings().removeClass('is-on')
                        .find('.lnb__sub').stop(true, true).slideUp(200);
                }
            });
            
        } else {
            // PC: flex로 표시
            $li.removeClass('is-on')
               .find('.lnb__sub').css({'display': 'flex'});
        }
    }

    // 초기 실행
    initMenu();

    // 화면 크기 변경 시 재실행
    $(window).on('resize', function () {
        initMenu();
    });

})();

//$li.find('.toggle').bind('click', function (e) { // 메뉴도 클릭되게하려면 이걸 사용    
// 모바일메뉴 사이트맵- 모바일로 전환 END

	
})(jQuery);