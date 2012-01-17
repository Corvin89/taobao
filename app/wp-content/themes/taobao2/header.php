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
function getViews(){

//Function for counting number of viewers of site <Made by Vladislav Fedorischev><assist Alexandr Kuciy>
	$vis = StatPress_Print("%totalpageviews%");
	$visi=(int)$vis+616908;
	$visit="$visi";
	$a=array();
	$j=strlen($visit);
		for($i=0;$i<$j;$i++){
		$a[]=$visit{$i};
	} 	  
	$reverse=array_reverse($a,false);
	$count=count($reverse);
	for($count;$count<9;$count++){
		$reverse[]="0";
	}
	$normal=array_reverse($reverse,false);
	
	return $normal;
}
?>
<?php $normal=getViews();?>
<!doctype html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7 ]> <html lang="ru" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="ru" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="ru" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="ru" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="ru" class="no-js"> <!--<![endif]-->
<head>
    <title><?php wp_title() ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script type="text/javascript" src="<?php bloginfo('template_url'); ?>/js/jquery.js"></script>

    <!-- reference your own javascript files here -->

    <script src="<?php bloginfo('template_directory'); ?>/js/modernizr-2.0.6.min.js"></script>
    <script src="<?php bloginfo('template_directory'); ?>/js/plugins.js"></script>
    <script src="<?php bloginfo('template_directory'); ?>/js/script.js"></script>
    <!--<script src="<?php bloginfo('template_directory'); ?>/js/online-consultation.js"></script>-->

    <script type="text/javascript">
        var serverHours = parseInt("<?php echo get_Hours(); ?>");
        var serverMinutes = parseInt("<?php echo get_Minutes(); ?>");
        var clientIP = "<?php echo getenv('REMOTE_ADDR'); ?>";
    </script>
    <script type="text/javascript" src="<?php bloginfo('template_url'); ?>/js/timer.js"></script>
    <script type="text/javascript" src="<?php bloginfo('template_url'); ?>/js/geoip.js"></script>
    <!-- test runner files -->
    <script src="<?php bloginfo('template_directory'); ?>/js/qunit.js"></script>
    <script src="<?php bloginfo('template_directory'); ?>/js/tests.js"></script>
    <script type="text/javascript" src="<?php bloginfo('template_url'); ?>/js/jcarousellite_1.0.1.js"></script>
    <script type="text/javascript" src="<?php bloginfo('template_url'); ?>/js/jquery.cycle.all.min.js"></script>
    <script type="text/javascript" src="<?php bloginfo('template_url'); ?>/js/form.js"></script>
    <script type="text/javascript" src="<?php bloginfo('template_url'); ?>/js/main.js"></script>
    <link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen"/>
    <!--[if IE 7]><link rel="stylesheet" type="text/css" href="<?php bloginfo('template_url'); ?>css/ie7.css" media="screen"/><![endif]-->
    <?php wp_head(); ?>
</head>
<body>
<div class="width">
    <section id="header">
        <header class="top">
            <div class="box">
                <div class="contru">
                    <span class="left">Доставляем товары по всей России, привезем и вам, в</span>
						<span class="val_left">
							<span id="city" class="val">Город не определен.</span>
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
                        <a href="#"><?php echo $normal['0'];?></a>
                        <a href="#"><?php echo $normal['1'];?></a>
                        <a href="#"><?php echo $normal['2'];?></a>
                    </div>
                    <div class="left-num">
                        <a href="#"><?php echo $normal['3'];?></a>
                        <a href="#"><?php echo $normal['4'];?></a>
                        <a href="#"><?php echo $normal['5'];?></a>
                    </div>
                    <div class="left-num">
                        <a href="#"><?php echo $normal['6'];?></a>
                        <a href="#"><?php echo $normal['7'];?></a>
                        <a href="#"><?php echo $normal['8'];?></a>
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
                    <li class="item2"><div id="liveTexButton_2272">Онлайн консультант</div></li>
                    <li class="item3"><a href="http://zingaya.com/widget/8f1f898b96da893919493f889553ecd3" onclick="window.open(this.href+'?referrer='+escape(window.location.href), '_blank', 'width=236,height=220,resizable=no,toolbar=no,menubar=no,location=no,status=no'); return false" class="zingaya_button">Бесплатный звонок</a></li>
                    <li class="item4 activ"><a href="#">Скачать форму заказа</a></li>
                </ul>
            </nav>
            <div class="box">
                <a href="<?php bloginfo('url') ?>" id="logo"></a>
                <div class="contact">
					<div class="left">
				         <div class="fone">
				         	<em>розничный отдел</em>
				         	<strong><span><?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_1_fieldID_1_numInSet_0',true);?></span> <?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_1_fieldID_2_numInSet_0',true);?></strong>
				         </div>
						 <ul class="contact">
							<li><img src="http://web.icq.com/whitepages/online?icq=<?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_1_fieldID_3_numInSet_0',true); ?>&img=5" alt="Статус <?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_1_fieldID_3_numInSet_0',true); ?>" /><?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_1_fieldID_3_numInSet_0',true); ?></li>
							<li class="mail"><a href="<?php echo get_option('admin_email'); ?>"><?php echo get_option('admin_email'); ?></a></li>
							<li class="skype"><a href="skype:<?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_1_fieldID_4_numInSet_0',true);?>"><?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_1_fieldID_4_numInSet_0',true);?></a></li>					
						</ul>
				    </div>  
					<div class="left right">
				         <div class="fone">
				         	<em>оптовый отдел</em>
				         	<strong><span><?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_2_fieldID_1_numInSet_0',true);?></span> <?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_2_fieldID_2_numInSet_0',true);?></strong>
				         </div>
						 <ul class="contact">
							<li><img src="http://web.icq.com/whitepages/online?icq=<?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_2_fieldID_3_numInSet_0',true); ?>&img=5" alt="Статус <?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_2_fieldID_3_numInSet_0',true); ?>" /><?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_2_fieldID_3_numInSet_0',true); ?></li>
							<li class="mail"><a href="<?php echo get_option('admin_email'); ?>"><?php echo get_option('admin_email'); ?></a></li>
							<li class="skype"><a href="skype:<?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_2_fieldID_4_numInSet_0',true);?>"><?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_2_fieldID_4_numInSet_0',true);?></a></li>					
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

                <a href="<?php bloginfo('url') ?>/?page_id=2689" class="blog">&nbsp;</a>
            </nav>
            <div class="slaider-box">
                <span class="prev"></span>
                <span class="next"></span>
                <div class="slaide">
                    <ul>
                        <?php query_posts('post_type=messages_slider')?>
                        <?php if (have_posts()) : ?>
                        <?php while (have_posts()) : the_post();  ?>
                            <li>
                                <p> <?php the_content(); ?></p>
                            </li>
                            <?php endwhile; ?>
                        <?php endif;?>
                    </ul>
                </div>
            </div>
        </header>
    </section>
