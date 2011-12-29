<?php function get_Hours(){
    $time = date_i18n(get_option('time_format'));
    $times = explode(":", $time);
    echo $times[0];
}
function get_Minutes(){
    $time = date_i18n(get_option('time_format'));
    $times = explode(":", $time);
    echo $times[1];
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="language" content="ru"/>
    <title><?php wp_title(' '); ?><?php if (wp_title(' ', false)) {
        echo ' | ';
    } ?><?php bloginfo('name'); ?></title>
    <link rel="icon" type="image/png" href="http://taobao.ru.com/wp-content/themes/taobao/favicon.png"/>
    <link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://taobao.ru.com/feed/"/>
    <link rel="pingback" href="http://taobao.ru.com/xmlrpc.php"/>
    <link rel="stylesheet" href="http://taobao.ru.com/wp-content/themes/taobao/style.css" type="text/css"
          media="screen"/>
    <script type="text/javascript" src="http://userapi.com/js/api/openapi.js?23"></script>
    <script type="text/javascript">
        VK.init({apiId:2274527, onlyWidgets:true});
    </script>
    <?php wp_head(); ?>
    <script type="text/javascript">
        var serverHours = parseInt("<?php echo get_Hours(); ?>");
        var serverMinutes = parseInt("<?php echo get_Minutes(); ?>");
    </script>
    <script type="text/javascript" src="/wp-content/themes/taobao/js/timer.js"></script>
    <script type="text/javascript" src="/wp-content/themes/taobao/js/jquery.js"></script>
    <script>
        $(document).ready(function () {
            $('.button_helper a').click(function () {
                $('.content_helper').slideToggle(500);
                return false;
            });
        });
    </script>
    <script type="text/javascript">
        var ZINGAYA_PARAMS = {id:"8f1f898b96da893919493f889553ecd3", label:"Бесплатный звонок с сайта", tooltip:"Вы можете позвонить нам прямо из браузера", pos1:"right", pos2:"top", margin:41, color:"#ffffff", hover_color:"#FFFFFF", background_color:"#2d8c30", theme:"dark" };
    </script>
    <script type="text/javascript" src="http://zingaya.com/js/zingaya.js" charset="utf-8"></script>
    <script type="text/javascript">

        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-24759075-20']);
        _gaq.push(['_trackPageview']);

        (function () {
            var ga = document.createElement('script');
            ga.type = 'text/javascript';
            ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(ga, s);
        })();

    </script>
</head>
<body>

<div id="alarm"></div>

<div id="menu">
    <?php wp_nav_menu($args = array(
        'menu' => 'Top',
        'container' => 'div',
        'container_class' => '',
        'menu_class' => 'menu',
        'menu_id' => 'ccc',
        'echo' => true,
        'fallback_cb' => 'wp_page_menu',
        'depth' => 0,
    )
);?>

</div>

<div id="background"></div>
<div id="top">
    <? //счетчик посетителей
    global $wpdb;
    $table_name = $wpdb->prefix . "statpress";
    $today = gmdate('Ymd', current_time('timestamp'));
    $qry = $wpdb->get_results("SELECT count(DISTINCT(ip)) as pageview FROM $table_name WHERE spider='' and feed='' ;");
    $totalvisitors = $qry[0]->pageview;
    $totalvisitors = $totalvisitors + 186000;
    $qry = $wpdb->get_results("SELECT count(DISTINCT(ip)) as visitors FROM $table_name WHERE date = $today and spider='' and feed='';");
    $todayvisitors = $qry[0]->visitors;
    ?>
    <div class="visitors">За время работы нашего сервиса, сайт посетили <b><?echo $totalvisitors;?></b> человек</div>
</div>
<div id="contacts">
    <img border="0" src="<?php bloginfo('template_url')?>/images/contacts.png" width="280" height="160" border="0"
         usemap="#Map"/>
    <map name="Map" id="Map">
        <area shape="rect" coords="28,60,226,90" href="http://www.icq.com/people/611250763" target="_blank"
              title="ICQ"/>
        <area shape="rect" coords="28,92,226,116" href="mailto:zakaz@taobao.ru.com" title="E-mail"/>
        <area shape="rect" coords="28,117,226,144" href="skype:taobao.ru.com?call" title="Skype"/>
    </map>
</div>
<div id="button"><a href="http://taobao.ru.com/wp-content/uploads/2011/08/zakaz.xls" target="_blank"
                    title="Скачать форму заказа"><img border="0"
                                                      src="<?php bloginfo('template_url')?>/images/button.png"
                                                      width="220" height="60"/></a></div>
<div style="position: fixed; right: 0px; top: 340px; z-index: 4;">
    <img src="<?php bloginfo('template_url')?>/images/social5.png" width="60" height="230" border="0" usemap="#social"/>
</div>
<map name="social" id="social">
    <area shape="rect" coords="18,18,52,52" href="http://vkontakte.ru/taobao_ru_com" title="Мы ВКонтакте"
          target="_blank"/>
    <area shape="rect" coords="18,58,52,92"
          href="http://www.facebook.com/pages/taobaorucom-%D1%81%D0%B5%D1%80%D0%B2%D0%B8%D1%81-%D0%BF%D0%BE%D0%BA%D1%83%D0%BF%D0%BE%D0%BA-%D0%B2-%D0%9A%D0%B8%D1%82%D0%B0%D0%B5/223067401068794"
          title="Мы на Facebook" target="_blank"/>
    <area shape="rect" coords="18,98,52,132" href="http://www.odnoklassniki.ru/group/51711905104107"
          title="Мы в Одноклассниках" target="_blank"/>
    <area shape="rect" coords="18,138,52,171" href="http://my.mail.ru/mail/zakaz_taobao/" title="Мы на Моем мире"
          target="_blank"/>
    <area shape="rect" coords="18,178,52,212" href="http://taobao.ru.com/otzyvy/" title="Оставьте свой отзыв"/>
</map>
<div style="position: fixed; left: 0px; top: 340px; z-index: 4;">
    <a href="http://taobao.ru.com/dostavka-i-oplata/#calculator"><img
        src="<?php bloginfo('template_url')?>/images/calculator.png" width="60" height="230" border="0"/></a>
</div>
<div id="container">
    <div id="content">