$(document).ready(function() { 
            
	$(window).scroll(function(){
		var scroll = $(this).scrollTop();
		var hitest = $('#visual').offset().top - 100;   
		var h = $('#visual').outerHeight();
		
		if(scroll > hitest && scroll < hitest + h) {
			//$('.main-lnb').eq(0).addClass('fixed');
			$('.menuTab-wrap').removeClass('active');
		} else {
			//$('.main-lnb').removeClass('fixed');
			$('.menuTab-wrap').addClass('active');
		}
				
	});
	

	
});  /*  end */

