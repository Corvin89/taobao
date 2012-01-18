<?php get_header(); ?>








<?php rewind_posts(); ?>  
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>



<div id="zagolovok"><h1><?php the_title(); ?></h1></div>



 
<?php the_content();?>


<small>
<?php the_tags('<img src="http://taobao.ru.com/wp-content/themes/taobao/images/tag_green.png" width="16" height="16"> ' , ', ' , ' | '); ?> <?php the_time('d F ') ?><?php the_time('Y') ?>
</small>


<div class="clearfloat" style="height: 20px;"></div>


<?php endwhile; else: ?>
<p><?php _e('�� ������ ������� ������ ���.'); ?></p>
<?php endif; ?>













<?/*
<div id="vk_comments"></div>
<script type="text/javascript">
VK.Widgets.Comments("vk_comments", {limit: 15, width: "780", attach: "*"});
</script>
*/?>





<?php comments_template(); ?>
































<?php get_footer(); ?>