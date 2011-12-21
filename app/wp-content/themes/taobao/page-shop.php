<?php
/*
Template Name: Shops
*/
?>
<?php get_header(); ?>





















<?php wp_reset_query(); ?>
<?php rewind_posts(); ?> 
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>



<div id="zagolovok"><h1><?php the_title(); ?></h1></div>




<div id="shops">
<?php the_content();?>
</div>



<?php endwhile; else: ?>
<p><?php _e('По вашему запросу ничего нет.'); ?></p>
<?php endif; ?>















    
	
	

	
	
	
	
	
	
	
	
	
	
	













<?php get_footer(); ?>