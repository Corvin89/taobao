<?php
/*
Template Name: First
*/
?>
<?php get_header(); ?>













	<div class="newsbox">
		<div class="top"></div>
		
	<div class="center">		
<?php rewind_posts(); ?>
<?php query_posts('category_name=news&posts_per_page=3'); ?>

<font style="font-size: 14px; font-weight: bold; margin-bottom: 10px;">Информационная лента</font><br><br><br>

<? $con=0; ?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
 

<font style="font-size: 14px; color: #5EC315; margin-bottom: 5px;"><?php the_title(); ?></font>

<?php the_content();?> 

<span class="newssmall">
<p><?php the_time('d F ') ?><?php the_time('Y') ?>  | <?php the_time('H:i')?></p>
</span>

<? if($con<2){echo '<hr>'; $con++;}; ?>

<div class="clearfloat"></div>	
	
<?php endwhile; else: ?>
<p>Нет новостей.</p>
<?php endif; ?>	


<hr>
<a href="http://taobao.ru.com/category/news/">Все новости >> </a>


	</div>
	
		<div class="bottom"></div>
	</div>



















<?php wp_reset_query(); ?>
<?php rewind_posts(); ?> 
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>



<div id="zagolovok"><h1><?php the_title(); ?></h1></div>




 
<?php the_content();?>




<?php endwhile; else: ?>
<p><?php _e('По вашему запросу ничего нет.'); ?></p>
<?php endif; ?>
























    
	
	

	
	
	
	
	
	
	
	
	
	
	













<?php get_footer(); ?>