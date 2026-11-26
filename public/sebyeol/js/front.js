/**
 * js/front.js
*/
$(function () {
    const $body = $('body');
    /* ---------------------------------------
       메뉴 아이콘 토글
    ---------------------------------------- */
    $(document).on('click', '.menu-icon', function (e) {
        e.preventDefault();
        $body.toggleClass('nav-active');
    });

    $(document).on("click", ".modal-open", function (e) {
        e.preventDefault();
        $body.removeClass('nav-active');
        $('#' + $(this).data("modal")).iziModal('open', this);
    });
}); //mobilemenu 열림 및 modal 위치인식 및 바디스트롤잠금적용 : iziModal.js, mobile-responsive.js-하위메뉴적용포함

$(document).ready(function() {
    setTimeout(function  () {
        $("#visual").addClass("active");            
        // $('.menuTab-wrap').addClass('active');
    });
  
    // 스크롤이 설정한 높이 이상 내려갔을때 스타일추가하기 
    if ($(window).width() > 0) {
        $('body#sub #header').addClass('bg_on');
        $('body#sub .nav-but-wrap').addClass('bg_on');    
        $(window).on("scroll",function(ev){            
            if($(window).scrollTop() > 50 ) { 
                $('#header').addClass('bg_on'); 
                $('.nav-but-wrap').addClass('bg_on');     
            }            
            else{
                    $('#header').removeClass('bg_on');  
                    $('body#sub #header').addClass('bg_on'); 
                    $('.nav-but-wrap').removeClass('bg_on');   
                    $('body#sub .nav-but-wrap').addClass('bg_on');    
                }

        return false;
        });
    }	
  

});  /*  end */


$(function () { 
    gnb();  
    classToggle();
    fileUpload(); // fileupload
    $(this).find('.nice-select').niceSelect(); // selectbox

   scrollTopMov();
   subMenuToggle();
   accordion();
   uiTab();

});


//xscroll
$(window).on('load', function(){
    xScroll('.x-scroll');
    kScroll('.k-scroll'); // scroll bar true
});

// iscroll outerwidth
function calcWidth(target) {
    var $target = $(target);

    $target.each(function(){
        var child = $(this).children(),
            width = 0;

        child.each(function(){
            width += $(this).outerWidth(true);
        });
        $(this).css('width', width);
    });
}

// iscroll
function xScroll(obj) {
    var $obj = $(obj),
        tabs = $obj.find('.tabs');

    if ( $(obj).length <= 0 ) {
        return
    } else {
        $(window).resize(function(){
            calcWidth(tabs);
        });
        calcWidth(tabs);
        new IScroll(obj , {
            scrollX : true,
            scrollY : false,
            mouseWheel : false,
            autoCenterScroll : true,
            bounce : true
        });
    }
}

// iscroll
function kScroll(obj) {
    var $obj = $(obj),
        tabs = $obj.find('.tabs');

    if ( $(obj).length <= 0 ) {
        return
    } else {
        $(window).resize(function(){
            calcWidth(tabs);
        });
        calcWidth(tabs);
        new IScroll(obj , {
            scrollX : true,
            scrollY : false,
            mouseWheel : false,
            autoCenterScroll : true,
            bounce : true,
            scrollbars: true
        });
    }
}


// file upload 
function fileUpload() {
    var obj = $('.inputfile-wrap');
    
    if (obj.length <= 0) return;

    var fileTarget = obj.find('input[type=file]');

    fileTarget.on('change', function(){
        if (window.FileReader) {
            var filename = $(this)[0].files[0].name;
        } else {
            var filename = $(this).val().split('/').pop().split('\\').pop();
        }

        $(this).siblings('input[type=text]').val(filename);
    });
}


// class toggle
function classToggle() {
    $('[data-toggle="class-toggle"]').find('button, a').click(function(){
        var li = $(this).closest('li');
        var lis = li.siblings('li');
        var wraps = $(this).closest($('[data-toggle="class-toggle"]'));
        var toggleSelf = wraps.data('toggle-self');
        var toggleClass = wraps.data('toggle-class');
        var className = toggleClass === undefined ? 'active' : toggleClass;

        if (toggleSelf){
            if (li.hasClass(className)){
                li.removeClass(className);
            } else {
                lis.removeClass(className);
                li.addClass(className);
            }
        } else {
            lis.removeClass(className);
            li.addClass(className);
        }
    });
}


// gnb pc nav
function gnb() {
    $('#gnb #gnb1_ul').hover(function(){
        $('#gnb').addClass('on');
        $('.gnb2_ul').css('display','block');
        $('header.gbg-white').addClass('bg_on');   
        setTimeout(function(){
          $('.gnb2_ul').addClass('on');
        },200);
      }, function(){
        $('#gnb').removeClass('on');
        $('.gnb2_ul').css('display','none');
        $('.gnb2_ul').removeClass('on');
        $('header.gbg-white').removeClass('bg_on'); 
      })
      
    // gnb pc nav End
    //gnb active
    //$("#gnb .gnb1_li").each(function(){
    //	if ($(this).children("a").text() == $(".location .depth_1").text() ){
    //        $(this).children("a").addClass('active');
    //	}
    //});    
    
    // GNB End	 
}

// accordion
function accordion() {
    var wrap = $('.ui-accordion'),
        list = wrap.find('li'),
        title = wrap.find('.accord-title'),
        toggle = title.find('.btn-toggle');

    if(wrap.length <= 0) return;

    // 접근성 대응
    list.each(function(){
        var $btn = $(this).find('.accord-title .btn-toggle'),
            $content = $(this).find('.accord-cont'),
            id = $(this).index();

        $btn.attr({
            'id' : 'accord-toggle' + id,
            'aria-controls' : 'accord-cont' + id
        });
        $content.attr({
            'id' : 'accord-cont' + id,
            'role' : 'region',
            'aria-labelledby' : 'accord-toggle' + id
        })
    });

    toggle.click(function(){
        var li = $(this).parent('.accord-title').parent('li'),
            cont = $(this).parent('.accord-title').siblings('.accord-cont'),
            blind = $(this).find('.blind');

        if (li.hasClass('open')) {
            $(this).attr('aria-expanded', 'false');
            cont.slideUp();
            li.removeClass('open');
            blind.text('상세보기');
        } else {
            $(this).attr('aria-expanded', 'true');
            cont.slideDown();
            li.addClass('open');
            blind.text('닫기');
        }
    });
}

// tab
function uiTab() {
    var tab = '[data-evt="tab"]';
    $(document).on('click', tab + ' a', function (e) {
        e.preventDefault();

        var $this = $(this),
            id = $this.attr('href'),
            $target = $('[data-id=' + id + ']'),
            $siblings = $this.parents('li').siblings('').find('a');

        $siblings.each(function () {
            var id = $(this).attr('href');
            $(this).parents('li').removeClass('active');
            $('[data-id=' + id + ']').hide();
        });
        $this.parents('li').addClass('active');
        $target.show();

        return false;
    });

    if ( tab.length <= 0 ) return;
    $(tab).find('a').each(function(){
        var tg = $(this).attr('href');

        $('[data-id="'+tg+'"]').hide();
    });
    $(tab + ' > li:first-child a').click();
}


    // 스크롤 앵커이동
    function scrollTopMov() {

        if (jQuery(window).width() > 1024) {
            $('.tab--scroll').click(function(){
                $('html, body').animate({
                scrollTop: $( $.attr(this, 'href') ).offset().top - 0
                }, 500);
                return false;
                });
        }
        else{
            
            $('.tab--scroll').click(function(){
                $('html, body').animate({
                scrollTop: $( $.attr(this, 'href') ).offset().top - 0
                }, 500);
                return false;
                });
            
        } 


    }  

     /* Toggle */ 
     function subMenuToggle() {       

        // $('.submenuM').on('click', function(){
        //     $(this).siblings('.submenu_toggle').stop().slideToggle();
        // })	
 
         $(".dropdownbox").click(function(){
             $(".optionArea").toggleClass("showMenu");
             $(".optionArea li").click(function(){
                 $(".dropdownbox > p").text($(this).text());
                 $(".optionArea").removeClass("showMenu");
             });
         });
 
     }
 

    //motion
    function motion_active() {
        $('.motion').each(function (index, item) {
            if($(window).scrollTop() > $(this).offset().top - $(window).height() * 0.6){
                $(this).addClass('active');
            }
        });
    }
    $(window).scroll(function (event) {
        if($('.motion').offset()){
            motion_active();
        }
    });
    motion_active();

