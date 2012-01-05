$(window).load(function(){		
	initCarousel();	
	initSlider();
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
