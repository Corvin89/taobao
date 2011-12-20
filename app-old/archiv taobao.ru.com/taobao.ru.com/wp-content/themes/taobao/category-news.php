<?php get_header(); ?>



<div id="zagolovok"><h1>Новости</h1></div>




<?php rewind_posts(); ?>  

<? $i=0; ?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<div class="clearfloat"></div>

<? if($i>0){echo "<hr>";}; $i++; ?>


<h1><?php the_title(); ?></h1>


<?php the_content();?>




<small>
<?php the_time('d F ') ?><?php the_time('Y') ?>
</small>



<div class="clearfloat"></div>


<?php endwhile; else: ?>
<p><?php _e('Новостей пока нет.'); ?></p>
<?php endif; ?>






<div class="clearfloat" style="height: 20px;"></div>
<?php if(function_exists('wp_pagenavi')) { wp_pagenavi(); } ?>
















    
	
	

	
	
	
	
	
	
	
	
	
	
	













<?php get_footer(); ?>