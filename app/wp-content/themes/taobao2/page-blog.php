<?php get_header(); ?>
<?php //var`s for comments
$no="комментариев нет";
$one="комментарий - 1";
$more="комментариев - %";
$onenumber="1";
$morenumber="%";

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
                <?php require_once "functions/slider-video-blog.php"; ?>
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
                    <?php query_posts('post_type=video_slider&order=ASC');  $numberPosts = 1; ?>
                    <?php while (have_posts()) : the_post(); ?>
                    <?php if($numberPosts) { ?>

                        <li>
                            <p><a href="#"><?php the_title(); ?></a></p>
                            <?php $numberPosts=$numberPosts-1; ?>
                        </li>
                            <?php }else{ ?>
                            <?php $numberPosts=$numberPosts+1;} ?>
                        <?php endwhile; ?>
                        <?php wp_reset_query(); ?>
                    </ul>
                </div>
                <div class="left-box right-box">
                    <ul>
                        <?php query_posts('post_type=video_slider&order=ASC');  $numberPosts = 0; ?>
                        <?php while (have_posts()) : the_post(); ?>
                        <?php if($numberPosts) { ?>
                        <li>
                            <p><a href="#"><?php the_title(); ?></a></p>
                            <?php $numberPosts=$numberPosts-1; ?>
                        </li>
                            <?php }else{ ?>
                            <?php $numberPosts=$numberPosts+1;} ?>
                        <?php endwhile; ?>
                        <?php wp_reset_query(); ?>
                    </ul>
                </div>



            </div>
        </div>
    </div>
    <div class="bottom"></div>
</section>
    <div class="right">
        <div class="boxen">
            <?php get_sidebar('calc') ?>
        </div>
        <div class="blog-gree">
            <?php get_sidebar('blog') ?>
        </div>
    </div>
</section>
</div>

<?php get_footer(); ?>