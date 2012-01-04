<?php function get_Hours()
{
    $time = date_i18n(get_option('time_format'));
    $times = explode(":", $time);
    echo $times[0];
}

function get_Minutes()
{
    $time = date_i18n(get_option('time_format'));
    $times = explode(":", $time);
    echo $times[1];
}

?>

<!doctype html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7 ]> <html lang="ru" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="ru" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="ru" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="ru" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="ru" class="no-js"> <!--<![endif]-->
<head>
    <title>MAXIGROOVE</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/jquery.js"></script>

    <!-- reference your own javascript files here -->

    <script src="<?php bloginfo('template_directory'); ?>/js/modernizr-2.0.6.min.js"></script>
    <script src="<?php bloginfo('template_directory'); ?>/js/plugins.js"></script>
    <script src="<?php bloginfo('template_directory'); ?>/js/script.js"></script>


    <script type="text/javascript">
        var serverHours = parseInt("<?php echo get_Hours(); ?>");
        var serverMinutes = parseInt("<?php echo get_Minutes(); ?>");
    </script>
    <script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/timer.js"></script>
    <script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/geoip.js"></script>
    <!-- test runner files -->
    <script src="<?php bloginfo('template_directory'); ?>/js/qunit.js"></script>
    <script src="<?php bloginfo('template_directory'); ?>/js/tests.js"></script>
    <script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/jcarousellite_1.0.1.js"></script>
    <script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/jquery.cycle.all.min.js"></script>
    <script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/main.js"></script>
    <link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen"/>
    <!--[if IE 7]><link rel="stylesheet" type="text/css" href="<?php bloginfo('template_directory'); ?>css/ie7.css" media="screen"/><![endif]-->
</head>
<body>
<div class="width">
    <section id="header">
        <header class="top">
            <div class="box">
                <div class="contru">
                    <span class="left">Доставляем товары по всей России, привезем и вам, в</span>
						<span class="val_left">
							<span id="city" class="val">г. Комсомольск-на-Амуре</span>
						</span>
                </div>
                <div class="righ-box">
                    <a href="#" class="item1">Войти</a>
                    &Iota;
                    <a href="#" class="item2">Зарегистрироваться</a>
                </div>
            </div>
            <div class="box">
                <div class="number">
                    <div class="left-num">
                        <a href="#">0</a>
                        <a href="#">0</a>
                        <a href="#">0</a>
                    </div>
                    <div class="left-num">
                        <a href="#">0</a>
                        <a href="#">0</a>
                        <a href="#">0</a>
                    </div>
                    <div class="left-num">
                        <a href="#">1</a>
                        <a href="#">2</a>
                        <a href="#">3</a>
                    </div>
                    <div class="text">
                        <p>Столько человек уже воспользовались <br/> услугами нашего сервиса</p>
                    </div>
                </div>
                <div class="time">
                    <div id="alarm"></div>

                </div>
            </div>
            <nav class="block-menu">
                <ul>
                    <li class="item1"><a href="#">Первый раз на сайте?</a></li>
                    <li class="item2"><a href="#">Он-лайн консультант</a></li>
                    <li class="item3"><a href="#">Бесплатный звонок</a></li>
                    <li class="item4 activ"><a href="#">Скачать форму заказа</a></li>
                </ul>
            </nav>
            <div class="box">
                <a href="<?php bloginfo('url') ?>" id="logo"></a>
                <div class="contact">
                    <div class="left">
                        <div class="fone">
                            <em>розничный отдел</em>
                            <strong><span>8 (4162)</span> 218-718</strong>
                        </div>
                        <ul class="contact">
                            <li class="qip">611 250 763</li>
                            <li class="mail"><a href="mailto:zakaz@taobao.ru.com">zakaz@taobao.ru.com</a></li>
                            <li class="skype">taobao.ru.com</li>
                        </ul>
                    </div>
                    <div class="left right">
                        <div class="fone">
                            <em>розничный отдел</em>
                            <strong><span>8 (4162)</span> 218-718</strong>
                        </div>
                        <ul class="contact">
                            <li class="qip">611 250 763</li>
                            <li class="mail"><a href="mailto:zakaz@taobao.ru.com">zakaz@taobao.ru.com</a></li>
                            <li class="skype">taobao.ru.com</li>
                        </ul>
                    </div>
                </div>
            </div>
            <nav class="menu">
                    <?php wp_nav_menu($args = array(
                        'menu' => 'Top',
                        'container' => 'div',
                        'container_class' => 'bgmenu',
                        'menu_class' => '',
                        'menu_id' => '',
                        'echo' => true,
                        'fallback_cb' => 'wp_page_menu',
                        'depth' => 0,
                    )
                );?>

                <a href="#" class="blog">&nbsp;</a>
            </nav>
            <div class="slaider-box">
                <span class="prev"></span>
                <span class="next"></span>
                <div class="slaide">
                    <ul>
                        <li>
                            <p><a href="#">ВНИМАНИЕ! Все заказы, принятые до 01.12.2011, будут доставлены ДО НОВОГО ГОДА! </a></p>
                            <p>Заявки, пришедшие после 1 декабря, скорее всего, попадут к Вам в руки после праздников. Все зависит от того, в каком городе Вы
                                находитесь и какой транспортной компанией будет отправлена Ваша посылка!</p>
                        </li>
                        <li>
                            <p><a href="#">ВНИМАНИЕ! Все заказы, принятые до 01.12.2011, будут доставлены ДО НОВОГО ГОДА! </a></p>
                            <p>Заявки, пришедшие после 1 декабря, скорее всего, попадут к Вам в руки после праздников. Все зависит от того, в каком городе Вы
                                находитесь и какой транспортной компанией будет отправлена Ваша посылка!</p>
                        </li>
                        <li>
                            <p><a href="#">ВНИМАНИЕ! Все заказы, принятые до 01.12.2011, будут доставлены ДО НОВОГО ГОДА! </a></p>
                            <p>Заявки, пришедшие после 1 декабря, скорее всего, попадут к Вам в руки после праздников. Все зависит от того, в каком городе Вы
                                находитесь и какой транспортной компанией будет отправлена Ваша посылка!</p>
                        </li>
                    </ul>
                </div>
            </div>
        </header>
    </section>