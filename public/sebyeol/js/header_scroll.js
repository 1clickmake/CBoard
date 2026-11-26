/* header scroll */
var clickType = "click";
if ("ontouchstart" in document.documentElement) {
    clickType = "touchstart";
}
$(function () {
    header_scroll();        
});

$(window).bind("scroll", function () {
    var $_top = $(document).scrollTop();      
    var scroll_current = $(window).height();

/*    if(1 > $_top){
        $('body #header').removeClass('bg_on'); 
        $('body .nav-but-wrap').removeClass('bg_on');  
    }else{
        $('body #header').addClass('bg_on');
        $('body .nav-but-wrap').addClass('bg_on');  
    } */
    if(scroll_current > $_top){
        $('body header').addClass('show')
    }else{
        if(1 > $_top){
        $('body header').addClass('show')          
        }else{
        $('body header').removeClass('show');
       // $('body #header').addClass('bg_on');
       // $('body .nav-but-wrap').addClass('bg_on');
        }
    }

    scroll_current =  $_top;
}).trigger('scroll');


const header_scroll = function () {
    
    let before = 0;
    let direction;
    const header = document.querySelector('body header');

    document.addEventListener('scroll', function() {
        var currentScrollValue = document.documentElement.scrollTop;                
        if(before < window.scrollY) direction="down";
        else direction="up";
        before = window.scrollY;
    
        if (currentScrollValue > 50 && direction=="down" ) {
            header.classList.add('hide');

        } else {
            header.classList.remove('hide');
        }     
    });

}
/* header scroll END */

