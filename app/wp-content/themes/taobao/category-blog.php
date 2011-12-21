<?php get_header(); ?>



<div id="zagolovok"><h1>Блог</h1></div>




<?php rewind_posts(); ?>  

<? $i=0; ?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>


<div class="clearfloat"></div>
<? if($i>0){echo "<hr>";}; $i++; ?>


<h1><a href="<?php the_permalink() ?>" rel="bookmark" title="Читать запись на отдельной странице: <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>


<?php the_content();?>




<small>
<?php the_tags('<img src="http://taobao.ru.com/wp-content/themes/taobao/images/tag_green.png" width="16" height="16"> ' , ', ' , ' | '); ?> <?php the_time('d F ') ?><?php the_time('Y') ?>
</small>



<div class="clearfloat"></div>


<?php endwhile; else: ?>
<p><?php _e('В блоге пока нет записей.'); ?></p>
<?php endif; ?>






<div class="clearfloat" style="height: 20px;"></div>
<?php if(function_exists('wp_pagenavi')) { wp_pagenavi(); } ?>
















    
	
	

	
	
	
	
	
	
	
	
	
	
	













<?php get_footer(); ?>