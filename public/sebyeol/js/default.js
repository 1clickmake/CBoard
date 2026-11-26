/**
 * default.js

*/

(function($) { "use strict";	
    // progress top 버튼 
	$(document).ready(function(){"use strict";
		var progressPath = document.querySelector('.progress-circle path');
		var pathLength = progressPath.getTotalLength();
		progressPath.style.transition = progressPath.style.WebkitTransition = 'none';
		progressPath.style.strokeDasharray = pathLength + ' ' + pathLength;
		progressPath.style.strokeDashoffset = pathLength;
		progressPath.getBoundingClientRect();
		progressPath.style.transition = progressPath.style.WebkitTransition = 'stroke-dashoffset 10ms linear';		
		var updateProgress = function () {
			var scroll = $(window).scrollTop();
			var height = $(document).height() - $(window).height();
			var progress = pathLength - (scroll * pathLength / height);
			progressPath.style.strokeDashoffset = progress;
		}
		updateProgress();
		$(window).scroll(updateProgress);	
		var offset = 50;
		var duration = 550;
		jQuery(window).on('scroll', function() {
			if (jQuery(this).scrollTop() > offset) {
				jQuery('.progress-wrap').addClass('active-progress');
			} else {
				jQuery('.progress-wrap').removeClass('active-progress');
			}
		});				
		jQuery('.progress-wrap').on('click', function(event) {
			event.preventDefault();
			jQuery('html, body').animate({scrollTop: 0}, duration);
			return false;
		})

	}); // progress top 버튼 End  

	/* onepage 메뉴이동 추가 */
	$(".nav__content > .lnb_ul li a").on("click", function(){
		$("html, body").removeClass("not_scroll");
		$("body").removeClass("nav-active");
		//$(".mask").css("display","none");
		posY = $(window).scrollTop(posY);
		
	});


	
})(jQuery);

//aos
AOS.init({
	//once : true,
	duration: 1500,
	//throttleDelay : 99,
});


