
// STYLE FORMS 

function style_forms(el) {  

    //button
    el.find(".button").each(function(){
        $(this).wrap('<span class="button_left"><span class="button_right"></span></span>');
        $(this).parents('.button_left').addClass($(this).attr("id")).addClass($(this).attr("class")).removeClass('button');
        if ( $(this).find('br').length ) $(this).css({'line-height':'0.9','height':'29px','padding-top':'4px'});
    });

    el.find(".button_left").hover(                                         
    function() {
        $(this).css({'background-position':'0 -33px'}).find('.button_right').css({'background-position':'right -33px'}).find('.button').css({'background-position':'0 -33px'});
    }, 
    function() {
        $(this).css({'background-position':'0 0'}).find('.button_right').css({'background-position':'right 0'}).find('.button').css({'background-position':'0 0'});
    }
    );

    el.find(".button_left").mousedown(function() {
        $(this).css({'background-position':'0 -66px'}).find('.button_right').css({'background-position':'right -66px'}).find('.button').css({'background-position':'0 -66px'});
    });    


    // input :text :password
    el.find("input.inpt").each(function(){
        $(this).wrap('<span class="inpt_left"><span class="inpt_right"></span></span>');
        $(this).parents('.inpt_left').addClass($(this).attr("id"));
    });	

    // textarea 
    el.find("textarea").not(".textarea textarea").each(function(){
        $(this).wrap('<div class="textarea"></div>');
    });	

    // checkbox    
    el.find(".chbox").each(function(){
        $(this).parent('label').addClass('chbox-label');
        $(this).wrap('<span class="checkbox"></span>');
        if ( $(this).attr("checked"))  $(this).parent('.checkbox').css({'background-position':'0 0'}); 
        else   $(this).parent('.checkbox').css({'background-position':'0 0'}); 
    }); 

    el.find(".chbox-label").click(function() {
        if ( $('.checkbox .chbox',this).attr("checked") ) { 
            $('.checkbox',this).css({'background-position':'0 0'});
            $('.chbox',this).attr("checked", false);
        }
        else {
            $('.checkbox',this).css({'background-position':'0 -17px'});
            $('.checkbox .chbox',this).attr("checked", true);
        }  
    });

    // radio    
    el.find("input.radio").each(function(){
        $(this).parent('label').addClass('radio-label');
        $(this).wrap('<span class="radiobox"></span>');
        if ( $(this).attr("checked") == "checked")  $(this).parent('.radiobox').css({'background-position':'0 0'}); 
        else   $(this).parent('.radiobox').css({'background-position':'0 0'}); 
    }); 

    el.find(".radio-label").click(function() {
        $(this).parent().parent().find('.radiobox').css({'background-position':'0 0'});
        $(this).children('.radiobox').css({'background-position':'0 -12px'});
        $(this).parent().parent().find('input.radio').attr("checked", false);
        $('input.radio',this).attr("checked", true);
    });



    // select
    el.find(".sel").each(function(){
        $(this).wrap('<span class="sel_left '+$(this).attr("id")+'"><span class="sel_right"></span></span>');
        $(this).before('<span class="sel_val"></span><ul></ul>');
        $(this).parent().find('.sel_val').html($(this).find(":selected").text());       
        $(this).find("option").each(function(){
            $(this).parent().prev('ul').append('<li>'+$(this).html()+'</li>');
        });
    });    

    el.find(".sel").each(function(){
        if ($(this).attr("disabled")) {$(this).parents('.sel_left').addClass("disabled"); }
    });    

    el.find(".sel_val").click(function() {
        if (!$(this).next('.sel').attr("disabled")) $(this).next('ul').show();
    });
    el.find(".sel_left").mouseleave(function() {
        $(this).find('ul').hide();
    });
    el.find(".sel_left li").click(function() {
        $(this).parent().parent().find('.sel_val').html( $(this).html()); 
        var num = $(this).index();
        $(this).parent().next('select').find('option').attr('selected','');
        $(this).parent().next('select').find('option').eq(num).attr('selected','selected');
        $('#search_form').submit()

        if ($(this).parent().next('select').attr('id') == 's_year') {
            //window.location.href='?pid='+$(this).parent().next('select').find('option').eq(num).val();
            show_form_ajax($(this).parent().next('select').find('option').eq(num).val());
        }

        if ($(this).parent().next('select').attr('name') == 'shift_name') {
            //alert($(this).parent().next('select').find('option').eq(num).val());
            window.location.href=$(this).parent().next('select').find('option').eq(num).val();

        }


        $(this).parent().find('li').removeClass('cur').removeClass('hover');
        $(this).addClass('cur');
        $(this).parent('ul').hide();      
    });

    el.find(".sel").focus(function() {
        $(this).prev('ul').show();
        $(this).prev('ul').find('li:first').addClass('hover');
    });
    el.find(".sel").focusout(function() {
        $(this).prev('ul').find('li.hover').removeClass('hover');
        $(this).prev('ul').hide();      
    });
    var i=50;
    el.find(".sel_left").each(function(){
        $(this).css({'z-index':i});
        i-=1;
    });	


    el.find(".sel").keydown(function(e) {
        switch(e.keyCode) { 
            // «стрелка вверх»
            case 38:
            if ($(this).prev('ul').find('li.hover').prev('li').length) { $(this).prev('ul').find('li.hover').removeClass('hover').prev('li').addClass('hover'); }
            else {$(this).prev('ul').find('li.hover').removeClass('hover'); $(this).prev('ul').find('li:last').addClass('hover'); }
            $(this).prev('ul').scrollTop( $(this).prev('ul').find('.hover').index() * 21) ;
            break;
            // «стрелка вниз»
            case 40:
            if ($(this).prev('ul').find('li.hover').next('li').length) { $(this).prev('ul').find('li.hover').removeClass('hover').next('li').addClass('hover'); }
            else {$(this).prev('ul').find('li.hover').removeClass('hover'); $(this).prev('ul').find('li:first').addClass('hover'); }
            $(this).prev('ul').scrollTop( $(this).prev('ul').find('.hover').index() * 21) ;
            break;
            // Enter
            case 13:
                $(this).prev('ul').find('li.hover').click();
                $(this).prev('ul').hide();
                break;
            case 32:
                $(this).prev('ul').find('li.hover').click();
                $(this).prev('ul').hide();
                break;
        }
    });



    // input :file
    el.find(".file").each(function(){
        $(this).wrap('<span class="file_inp"></span>');
        $(this).before('<span class="file_left"><span class="file_right"><span class="file_val"></span></span></span><span class="file_but">Browse...</span>');
    });	

    el.find(".file").change(function(){
        $(this).parent().find('.file_val').html( $(this).val());
    });	  


    //watermark
    el.find(":text[title!=''], :password[title!=''], textarea[title!='']").each(function(){
        $(this).wrap('<span class="el_wrap"></span>');
        $(this).before('<label class="watermark">&nbsp;&nbsp;&nbsp;'+$(this).attr('title')+'</label>');
        $(this).attr('title','');
    });
    if ( !($.browser.msie && $.browser.version == 7)) { el.find('.el_wrap').css({'display':'inline-block'})} 

    el.find(".el_wrap :text, .el_wrap :password, .el_wrap textarea").focus(function(){
        $(this).prev('.watermark').hide();  
    });

    el.find(".watermark").click(function() {
        $(this).hide(0);
        $(this).next('input, textarea').focus();
    });

    el.find(":text, :password, textarea").blur(function() {
        if ($(this).attr('value')=='')  $(this).prev('.watermark').show();
    });   

    //different

    el.find(".row_name").each(function(){
        if ($(this).height() > 20) $(this).addClass('double');
    });


}
////////////////




$(document).ready(function(){ 

    if ($('#rightbar').length) $('#content').addClass('with_right');
    if ($('#leftbar1').length) $('#content').addClass('with_left1');
    if ($('#leftbar2').length) $('#content').addClass('with_left2');
    $('#photo_comments .comment_item:last, .photo_com_item:last, .post_com_item:last').addClass('last');
    //if ($('.place_pay').length)  $('.place_pay').find

    // rating stars
    $('.stars span').hover(
    function(){
        var num_star = $(this).index();
        $(this).addClass('hover').prev().addClass('hover').prev().addClass('hover').prev().addClass('hover').prev().addClass('hover');
        $(this).addClass('hover').next().addClass('nohover').next().addClass('nohover').next().addClass('nohover').next().addClass('nohover');
    },
    function(){
        $(this).parent().find('span').removeClass('hover');
        $(this).parent().find('span').removeClass('nohover');
    }); 


    if ($('.events_menu ul').length) jQuery('.events_menu ul').jcarousel({
        scroll:1
    }); 

    function mycarousel_initCallback(carousel)
    {
        carousel.buttonNext.bind('click', function() {
            carousel.startAuto(0);
        });

        carousel.buttonPrev.bind('click', function() {
            carousel.startAuto(0);
        });

        carousel.clip.hover(function() {
            carousel.stopAuto();
        }, function() {
            carousel.startAuto();
        });
    };     

    if ($('.event_users ul').length) jQuery('.event_users ul').jcarousel({
        scroll:1,
        auto:1,
        wrap:'last',
        initCallback: mycarousel_initCallback 
    }); 

    // place photo_carousel, panorama

    if ($('.photo_carousel ul').length) jQuery('.photo_carousel ul').jcarousel({
        scroll:1
    });
    if ($('.panorama_carousel ul').length) jQuery('.panorama_carousel ul').jcarousel({
        scroll:1
    }); 

    $('.photo_carousel a').click(function(){
        $(this).parents('.photo_carousel').next('.photo_cont').find('.main_photo').attr('src',$(this).attr('href'));
        $(this).parents('.photo_carousel').next('.photo_cont').find('.photo_descr').html($(this).find('img').attr('alt'));
        $(this).parents('ul').find('.active').removeClass('active');
        $(this).parent('li').addClass('active');       
        return false;
    });
    $('.panorama_carousel a').click(function(){
        $(this).parents('.panorama_carousel').next('.panorama_cont').find('.main_panorama').attr('data',$(this).attr('href'));
        $(this).parents('.panorama_carousel').next('.panorama_cont').find('.panorama_descr').html($(this).find('img').attr('alt'));
        $(this).parents('ul').find('.active').removeClass('active');
        $(this).parent('li').addClass('active');       
        return false;
    });

    $('.photo_cont .main_photo').click(function(){	    
        $(this).parent('.photo_cont').prev('.photo_carousel').find('.active').removeClass('active').next('li').addClass('active');
        $(this).attr( 'src', $(this).parent('.photo_cont').prev('.photo_carousel').find('.active a').attr('href') );
        $(this).next('.photo_descr').html($(this).parent('.photo_cont').prev('.photo_carousel').find('.active img').attr('alt'));
        return false;
    });


    //календарь

    if ($('#visit_date').length) {
        $("#visit_date").datepicker();
    }
    if ($('.date_inpt').length) {
        $(".date_inpt").datepicker();
    }

    // попап отзывовов на главной
    $('.place_item .review_link').click(function(){	
        var left_pos = $(this).offset().left-100; 
        var top_pos = $(this).offset().top; 
        $('.place_review.pop-up').css({left: left_pos, top: top_pos });
        $('.place_review.pop-up').show();
        return false;   
    });
    $('.pop-up .close').click(function(){	
        $('.pop-up').hide();  
    });


    // попапы
    $('.pop-up .close').click(function(){	
        $('.pop-up').hide();  
    });
    $('.place_item .review_link').click(function(){	
        $('.pop-up').hide(); 
        var left_pos = $(this).offset().left-100; 
        var top_pos = $(this).offset().top; 
        $('.place_review.pop-up').css({left: left_pos, top: top_pos });
        $('.place_review.pop-up').show();
        return false;   
    });
    $('.place_item .map_link').click(function(){	
        $('.pop-up').hide(); 
        var left_pos = $(this).offset().left-100; 
        var top_pos = $(this).offset().top+30; 
        $('.place_view_map.pop-up').css({left: left_pos, top: top_pos });
        $('.place_view_map.pop-up').show();
        return false;   
    });
    $('.arhiv_link a').click(function(){	
        $('.pop-up').hide(); 
        var left_pos = $(this).offset().left-17; 
        var top_pos = $(this).offset().top-300; 
        $('.mes_arhiv.pop-up').css({left: left_pos, top: top_pos });
        $('.mes_arhiv.pop-up').show();
        return false;   
    });





    style_forms($('body'));
});



