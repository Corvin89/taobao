$(window).load(function(){		
	initCarousel();	
	initSlider();
	initTabs();
});

 function initCarousel(){
$("div.slaider-box div.slaide").jCarouselLite({
    btnNext: "div.slaider-box span.next",
    btnPrev: "div.slaider-box span.prev",
    speed: 400,
	visible: 1,
	circular: false
});
$("div.slaider-video").jCarouselLite({
    btnNext: "div.videos span.next",
    btnPrev: "div.videos span.prev",
    speed: 400,
	visible: 3,
	circular: false
});
}

function initSlider(){
$('.slider ul').cycle({
        fx:     'fade',
        timeout: 5000,
        pager:  '.pager'
});
}

function initTabs(){
 $('.tabs dt').click(function(){
 var thisClass = this.className.slice(0,2);
 $('div.t1').hide();
 $('div.t2').hide();
 $('div.t3').hide();
 $('div.' + thisClass).show();
 $('.tabs dt').removeClass('tab-current');
 $(this).addClass('tab-current');
 return false;
 });
 $('dt.t1').click();
}