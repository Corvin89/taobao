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
                    <?php query_posts('post_type=video_slider&order=ASC'); ?>
                    <?php while (have_posts()) : the_post(); ?>
                    <li>
                        <div class="foto">
                            <a href="#">
                        <?php
                        $youtube = false;
                        $vimeo = false;
                        if($url = get_post_meta($post->ID, "Youtube", true)) {
                            preg_match('%(?:youtube\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $youtube);
                        } elseif($url = get_post_meta($post->ID, "Vimeo", true)) {
                            preg_match('%http:\/\/(www.vimeo|vimeo)\.com(\/|\/clip:)(\d+)(.*?)%i', $url, $vimeo);
                        }
                        if (!empty($youtube)) { ?>

                        <a href="http://www.youtube.com/watch?v=<?php echo $youtube[1]; ?>"
                            rel="wp-video-lightbox" title=""><img
                            src="//i2.ytimg.com/vi/<?php echo $youtube[1]; ?>/default.jpg"
                            alt="YouTube" width="60" /></a <?php
                        } elseif($vimeo) { ?>
                            <iframe src="http://player.vimeo.com/video/<?php echo $vimeo[3]; ?>?portrait=0&amp;autoplay=0" width="853" height="480" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
                            <?php } else {
                            echo "Видео не доступно.";
                        }?>
                            </a>
                        </div>
                        <div class="text-video">
                            <p><a href="#"><?php the_title(); ?></a></p>
                        </div>
                    </li>
                    <?php endwhile; ?>
                    <?php wp_reset_query(); ?>


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
            <?php get_sidebar('calc') ?>
    </div>
</section>
<div id="bg-wraper">
    <div id="wraper">
        <div class="read">
            <h2>Полезно почитать</h2>
            <ul>
                <?php
					// The Query
					$the_query = new WP_Query('category_name=polezno-pochitat&posts_per_page=4');    
					// The Loop
					while ( $the_query->have_posts() ) : $the_query->the_post();
						echo '<li>'; ?>
						<p class="title"><a href="<?php the_permalink() ?>">"<?php the_title() ?>"</a></p> <?php
						echo '</li>';
					endwhile;
					
					// Reset Post Data
					wp_reset_postdata();				
				?>
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
			<?php query_posts('post_type=optionstext&p=2707')?>
             <?php if (have_posts()) : ?>
                   <?php while (have_posts()) : the_post();  ?>
                       <div class="news">

			      			<p><?php the_content();?></p>

                      </div>
                   <?php endwhile; ?>
             <?php endif;?>
        </div>
        <div class="blog">
            <?php get_sidebar('news-blog') ?>
        </div>
    </section>
</section>
<div class="last">
    <div class="text">
        <div class="text-left">
			<?php query_posts('post_type=optionstext&p=2702')?>
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
				<?php query_posts('post_type=optionstext&p=2704')?>
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
				<?php query_posts('post_type=optionstext&p=2706 ')?>
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
