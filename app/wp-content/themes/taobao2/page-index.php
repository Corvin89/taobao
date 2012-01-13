<?php get_header(); ?>

<section id="container">
    <div class="left">
        <div class="slider">
            <ul>
                <?php query_posts('post_type=baner_slider&order=ASC'); ?>
                <?php while (have_posts()) : the_post(); ?>
                <li><a href="#">
                    <?php the_post_thumbnail(); ?>
                </a></li>
                <?php endwhile; ?>
                <?php wp_reset_query(); ?>
            </ul>
            <div class="pager"></div>
        </div>
        <div class="videos">
            <h2>Видеоинструкции <span> по работе с сервисом:</span></h2>
            <span class="prev">&nbsp;</span>
            <span class="next">&nbsp;</span>

            <div class="slaider-video">
                <ul>
                    <li>
                        <div class="foto"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/video.gif"
                                                           alt="" title=""/></a></div>
                        <div class="text-video">
                            <p><a href="#">Регистрация на сайте Taobao.ru.com</a></p>
                        </div>
                    </li>
                    <li>
                        <div class="foto"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/video.gif"
                                                           alt="" title=""/></a></div>
                        <div class="text-video">
                            <p><a href="#">Регистрация на сайте Taobao.ru.com</a></p>
                        </div>
                    </li>
                    <li>
                        <div class="foto"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/video.gif"
                                                           alt="" title=""/></a></div>
                        <div class="text-video">
                            <p><a href="#">Регистрация на сайте Taobao.ru.com</a></p>
                        </div>
                    </li>
                    <li>
                        <div class="foto"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/video.gif"
                                                           alt="" title=""/></a></div>
                        <div class="text-video">
                            <p><a href="#">Регистрация на сайте Taobao.ru.com</a></p>
                        </div>
                    </li>
                    <li>
                        <div class="foto"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/video.gif"
                                                           alt="" title=""/></a></div>
                        <div class="text-video">
                            <p><a href="#">Регистрация на сайте Taobao.ru.com</a></p>
                        </div>
                    </li>
                    <li>
                        <div class="foto"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/video.gif"
                                                           alt="" title=""/></a></div>
                        <div class="text-video">
                            <p><a href="#">Регистрация на сайте Taobao.ru.com</a></p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="right">
        <div class="boxen">
            <h2>Внутренний курс <span>Taobao.ru.com:</span></h2>
            <span class="calcul">5,3</span>

            <div class="calcul">
                <a href="<?php bloginfo('url'); ?>/?page_id=1693">Калькулятор</a>

                <p>Рассчитать стоимость товаров с учетом доставки.</p>
            </div>
            <div class="top-s"></div>
            <div class="body-s">
                <h2>Русский поиск <span>на Taobao.com:</span></h2>

                <form action="" method="post">
                    <div class="item">
                        <label>Введите слово или фразу на <br/> русском языке и нажмите <br/> кнопку “Перевести”
                        </label>
                        <input type="text" class="text"/>
                        <input type="submit" class="sub" value="Перевести"/>
                    </div>
                    <div class="item">
                        <label>Затем нажмите "Поиск на <br/> Taobao" и у вас откроется <br/> страница с результатами
                            поиска.</label>
                        <input type="text" class="text"/>
                        <input type="submit" class="sub" value="Поиск на Taobao.com"/>
                    </div>
                    <div class="item">
                        <a href="#">Видеоинструкция</a>
                    </div>
                </form>
            </div>
            <div class="bottom-s"></div>
        </div>
    </div>
</section>
<div id="bg-wraper">
    <div id="wraper">
        <div class="read">
            <h2>Полезно почитать</h2>
            <ul>
                <li><a href="#">Первые шаги на Таобао. Что такое Таобао? Справочная информация о компании, предлагаемых
                    услугах.</a></li>
                <li><a href="#">Первые шаги на таобао. Как искать вещи? Как правильно выбирать продавца? Все доступно и
                    с картинками.</a></li>
                <li><a href="#">Почему продавец долго отправляет вещи.</a></li>
                <li><a href="#">Как правильно выбрать фирменную одежду на китайском аукционе.</a></li>
            </ul>
        </div>
        <div class="report">
            <h2>Новые отзывы <a href="#">все отзывы</a></h2>
            <ul>
                <li>
                    <p><strong>Александр</strong><span>, Белгород, 01.11.2011</span></p>

                    <p><a href="#">Получил посылку ))) все запечатано все цело !) Очень оперативно работа и внимание к
                        клиенту! Теперь...</a></p>
                </li>
                <li>
                    <p><strong>Александр</strong><span>, Белгород, 01.11.2011</span></p>

                    <p><a href="#">Получил посылку ))) все запечатано все цело !) Очень оперативно работа и внимание к
                        клиенту! Теперь...</a></p>
                </li>
                <li>
                    <p><strong>Александр</strong><span>, Белгород, 01.11.2011</span></p>

                    <p><a href="#">Получил посылку ))) все запечатано все цело !) Очень оперативно работа и внимание к
                        клиенту! Теперь...</a></p>
                </li>
                <li>
                    <p><strong>Александр</strong><span>, Белгород, 01.11.2011</span></p>

                    <p><a href="#">Получил посылку ))) все запечатано все цело !) Очень оперативно работа и внимание к
                        клиенту! Теперь...</a></p>
                </li>
            </ul>
        </div>
    </div>
</div>
<section id="bg-main">
    <section id="main">
        <div class="block">
			<?php query_posts('post_type=optionstext&p=2690')?>
             <?php if (have_posts()) : ?>
                   <?php while (have_posts()) : the_post();  ?>
                       <div class="news">
                             	
			      			<p><?php the_content();?></p>
									
                      </div>
                   <?php endwhile; ?>
             <?php endif;?>	
        </div>
        <div class="blog">
            <h2>Новое в блоге</h2>
            <ul>
                <li>
                    <p><span class="data">02.11.2011</span> <span class="com">15</span></p>

                    <p><b>В чём встречать Новый 2012 год?</b></p>

                    <p>По восточному календарю покровителем наступающего года будет <a href="#">чёрный водяной
                        Дракон.</a></p>
                </li>
                <li>
                    <p><span class="data">02.11.2011</span> <span class="com">15</span></p>

                    <p><b>В чём встречать Новый 2012 год?</b></p>

                    <p>По восточному календарю покровителем наступающего года будет <a href="#">чёрный водяной
                        Дракон.</a></p>
                </li>
                <li>
                    <p><span class="data">02.11.2011</span> <span class="com">15</span></p>

                    <p><b>В чём встречать Новый 2012 год?</b></p>

                    <p>По восточному календарю покровителем наступающего года будет <a href="#">чёрный водяной
                        Дракон.</a></p>
                </li>
                <li>
                    <p><span class="data">02.11.2011</span> <span class="com">15</span></p>

                    <p><b>В чём встречать Новый 2012 год?</b></p>

                    <p>По восточному календарю покровителем наступающего года будет <a href="#">чёрный водяной
                        Дракон.</a></p>
                </li>
            </ul>
        </div>
    </section>
</section>
<div class="last">
    <div class="text">
        <div class="text-left">
			<?php query_posts('post_type=optionstext&p=2692')?>
             <?php if (have_posts()) : ?>
                   <?php while (have_posts()) : the_post();  ?>
                       <div class="news">
                             	
			      			<p><strong><?php the_title();?></strong></p>
			      			<p><?php the_content();?></p>
									
                      </div>
                   <?php endwhile; ?>
             <?php endif;?>	            
        </div>
        <div class="text-right">
            <div class="col-1">
				<?php query_posts('post_type=optionstext&p=2694')?>
	             <?php if (have_posts()) : ?>
	                   <?php while (have_posts()) : the_post();  ?>
	                       <div class="news">
	                             	
				      			<p><strong><?php the_title();?></strong></p>
				      			<p><?php the_content();?></p>
										
	                      </div>
	                   <?php endwhile; ?>
	             <?php endif;?>	      
            </div>
            <div class="col-1 right">
				<?php query_posts('post_type=optionstext&p=2695 ')?>
	             <?php if (have_posts()) : ?>
	                   <?php while (have_posts()) : the_post();  ?>
	                       <div class="news">
	                             	
				      			<p><strong><?php the_title();?></strong></p>
				      			<p><?php the_content();?></p>
										
	                      </div>
	                   <?php endwhile; ?>
	             <?php endif;?>	     
            </div>
        </div>
    </div>
</div>
</div>

<?php get_footer(); ?>
