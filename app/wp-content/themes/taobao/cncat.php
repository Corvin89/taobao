<?php
/*
Template Name: Cncat
*/
?>
<?php get_header(); ?>









<?php if (have_posts()) : while (have_posts()) : the_post(); ?>



<div id="zagolovok"><h1><?php the_title(); ?></h1></div>



 
<?php the_content();?>




<?php endwhile; else: ?>
<p><?php _e('�� ������ ������� ������ ���.'); ?></p>
<?php endif; ?>








<?php print $cncat_contents?>
	
	
	
	
	
	
	
	
	
	













<?php get_footer(); ?>