<?php
/*
Template Name: Otziv
*/
?>
<?php get_header(); ?>





















<?php wp_reset_query(); ?>
<?php rewind_posts(); ?> 
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>



<div id="zagolovok"><h1><?php the_title(); ?></h1></div>




 
<?php the_content();?>




<?php endwhile; else: ?>
<p><?php _e('По вашему запросу ничего нет.'); ?></p>
<?php endif; ?>






<div class="clearfloat" style="height: 20px;"></div>
<?php comments_template(); ?>










    
	
	

	
	
	
	
	
	
	
	
	
	
	













<?php get_footer(); ?>