<?php get_header(); ?>



<div id="zagolovok"><h1>Метки</h1></div>




<?php rewind_posts(); ?>  
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<div class="clearfloat"></div>


<h1><a href="<?php the_permalink() ?>" rel="bookmark" title="Читать запись полностью: <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>


<?php the_excerpt(); ?>




<small>
<?php the_time('d F ') ?><?php the_time('Y') ?> | <a href="<?php the_permalink() ?>" title="<?php the_title(); ?>">Читать полностью</a>
</small>



<div class="clearfloat" style="height: 20px;"></div>


<?php endwhile; else: ?>
<p><?php _e('Нет таких меток.'); ?></p>
<?php endif; ?>






<div class="clearfloat" style="height: 20px;"></div>
<?php if(function_exists('wp_pagenavi')) { wp_pagenavi(); } ?>









<?php get_footer(); ?>