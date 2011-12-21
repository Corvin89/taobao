<?php get_header(); ?>








<?php rewind_posts(); ?>  
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>



<div id="zagolovok"><noindex><h1><?php the_title(); ?></h1></noindex></div>


<?php the_content();?>



<div class="clearfloat" style="height: 20px;"></div>


<?php endwhile; else: ?>
<p><?php _e('По вашему запросу ничего нет.'); ?></p>
<?php endif; ?>








<?php get_footer(); ?>