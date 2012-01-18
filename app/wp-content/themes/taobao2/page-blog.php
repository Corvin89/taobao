<?php get_header(); ?>
<?php //var`s for comments
$no="комментариев нет";
$one="комментарий - 1";
$more="комментариев - %";
?>

<section id="container">
<section id="content">
    <h2 class="title">Блог Taobao.ru.com</h2>
    <span class="post">Всего записей: <?php echo get_category('4')->category_count;?></span>
    <div class="top"></div>
    <div class="body">
        <div class="page">
			<?php 
			query_posts('posts_per_page=1&category_name=blog');
			?>
			<?php while(have_posts()) : the_post(); global $post;?>
            <h2><a href="#"><?php the_title();?></a></h2>
				<p>
					<?php list($teaser, $junk) = explode('<!--more',$post->post_content);
					echo apply_filters('the_content', $teaser); ?>
					<a href="<?php the_permalink();?>">Есть решение!</a>
				</p>
            <div class="article">
			
			
                <span class="label"><?php the_tags('', ', ', '<br />'); ?></span>
                <a class="com"><?php comments_number("0",$one,$more);?></a>
                <span class="data"><?php the_time('d.m.Y');?></span>
				 <?php endwhile;?>
				 <?php wp_reset_query();?>
		    </div>
            <div class="all">
                <div class="left-box">
                    <h2 class="post">Новые статьи</h2>
                    <ul>
					<?php 
					query_posts('posts_per_page=6&category_name=blog');
					?>
						<?php while(have_posts()) : the_post(); global $post; setup_postdata($post);?>
						<li>
						    <p><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></p>
                            <p><span><?php the_time('d.m.Y');?></span>
							<span><?php comments_number($no,$one,$more);?></span></p>
                        </li>
						<?php endwhile; ?>
						<?php wp_reset_query();?>
                    </ul>
                </div>
                <div class="left-box right-box">
				<?php 
				query_posts('posts_per_page=5&category_name=polezno-pochitat');
				?>
                    <h2 class="read">Полезно почитать</h2>
                    <ul>
						<?php while(have_posts()) : the_post(); global $post; setup_postdata($post);?>
                        <li>
                            <p><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></p>
                            <p><span><?php the_time('d.m.Y');?><?php comments_number($no,$one,$more);?></span></p>
                        </li>
						<?php endwhile;?>
						<?php wp_reset_query();?>
                    </ul>
                </div>
            </div>
            <div class="all">
                <div class="line-title">
					<?php if ( function_exists('wp_tag_cloud') ) : ?>
                    <h2 class="tegi">Поиск статей по тегам</h2>
					
					
					<span><a href="#" class="rig">все статьи</a> →</span>
                </div>
                <div class="tegs">                   
					<?php wp_tag_cloud( 'smallest=8&largest=22&number=25' ); ?>
                <?php endif; ?>
				</div>
            </div>
            <div class="all">
                <h2 class="video">Это интересно</h2>
                <ul class="video">
                    <li>
                        <div class="foto"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/video.gif" alt="" title="" /></a></div>
                        <div class="text-video">
                            <p><a href="#">Регистрация на сайте Taobao.ru.com</a></p>
                        </div>
                    </li>
                    <li>
                        <div class="foto"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/video.gif" alt="" title="" /></a></div>
                        <div class="text-video">
                            <p><a href="#">Регистрация на сайте Taobao.ru.com</a></p>
                        </div>
                    </li>
                    <li>
                        <div class="foto"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/video.gif" alt="" title="" /></a></div>
                        <div class="text-video">
                            <p><a href="#">Регистрация на сайте Taobao.ru.com</a></p>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="all">
                <div class="line-title">
                    <h2 class="list">Видеоинструкции</h2> <span><a href="#" class="rig">все видео</a> →</span>
                </div>
                <div class="left-box">
                    <ul>
                        <li>
                            <p><a href="#">В долине кукол</a></p>
                        </li>
                        <li>
                            <p><a href="#">Пуловер. Достоин мужского внимания</a></p>
                        </li>
                        <li>
                            <p><a href="#">Информация для покупателей: вес вещей</a></p>
                        </li>
                        <li>
                            <p><a href="#">В чём встречать Новый 2012 год?</a></p>
                        </li>
                        <li>
                            <p><a href="#">Какие цвета актуальны в этом сезоне?</a></p>
                        </li>
                        <li>
                            <p><a href="#">Какие цвета актуальны в этом сезоне?</a></p>
                        </li>
                    </ul>
                </div>
                <div class="left-box right-box">
                    <ul>
                        <li>
                            <p><a href="#">Первые шаги на Таобао. Что такое Таобао? Справочная информация о компании, предлагаемых услугах.</a></p>
                        </li>
                        <li>
                            <p><a href="#">Первые шаги на таобао. Как искать вещи?</a></p>
                        </li>
                        <li>
                            <p><a href="#">Как правильно выбирать продавца? Все доступно и с картинками.</a></p>
                        </li>
                        <li>
                            <p><a href="#">Как правильно выбирать продавца? Все доступно и с картинками.</a></p>
                        </li>
                        <li>
                            <p><a href="#">Как правильно выбирать продавца? Все доступно и с картинками.</a></p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="bottom"></div>
</section>
<div class="right">
    <div class="boxen">
        <h2>Внутренний курс <span>Taobao.ru.com:</span></h2>
        <span class="calcul">5,3</span>
        <div class="calcul">
            <a href="#">Калькулятор</a>
            <p>Рассчитать стоимость товаров с учетом доставки.</p>
        </div>
        <div class="top-s"></div>
        <div class="body-s">
            <h2>Русский поиск <span>на Taobao.com:</span></h2>
            <form action="" method="post">
                <div class="item">
                    <label>Введите слово или фразу на <br/> русском языке и нажмите <br/> кнопку “Перевести” </label>
                    <input type="text" class="text" />
                    <input type="submit" class="sub" value="Перевести" />
                </div>
                <div class="item">
                    <label>Затем нажмите "Поиск на <br/> Taobao" и у вас откроется <br/> страница с результатами поиска.</label>
                    <input type="text" class="text" />
                    <input type="submit" class="sub" value="Поиск на Taobao.com" />
                </div>
                <div class="item">
                    <a href="#">Видеоинструкция</a>
                </div>
            </form>
        </div>
        <div class="bottom-s"></div>
        <div class="blog-gree">
            <div class="blog">
                <h2>Новое в блоге</h2>
                <ul>
                    <li>
                        <p><span class="data">02.11.2011</span> <span class="com">15</span></p>
                        <p><b>В чём встречать Новый 2012 год?</b></p>
                        <p>По восточному календарю покровителем наступающего года будет <a href="#">чёрный водяной Дракон.</a></p>
                    </li>
                    <li>
                        <p><span class="data">02.11.2011</span> <span class="com">15</span></p>
                        <p><b>В чём встречать Новый 2012 год?</b></p>
                        <p>По восточному календарю покровителем наступающего года будет <a href="#">чёрный водяной Дракон.</a></p>
                    </li>
                    <li>
                        <p><span class="data">02.11.2011</span> <span class="com">15</span></p>
                        <p><b>В чём встречать Новый 2012 год?</b></p>
                        <p>По восточному календарю покровителем наступающего года будет <a href="#">чёрный водяной Дракон.</a></p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
</section>
</div>

<?php get_footer(); ?>