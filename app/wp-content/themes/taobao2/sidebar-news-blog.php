<h2>Новое в блоге new-blog</h2>
<ul>
    <?php
    query_posts('cat=4&showposts=4');
    if (have_posts()) :
        while (have_posts()) : the_post();
            echo("<li>"); ?>
            <p><span class="data"><?php the_date('d.m.Y'); ?></span> <span class="com"><?php comments_number('0','1','%')?></span></p>
            <p class="title"><a href="<?php the_permalink() ?>">"<?php the_title() ?>"</a></p> <?php
            echo the_excerpt("<p>", "</p>");
            echo("</li>");
        endwhile;
    endif;
    ?>
</ul>

