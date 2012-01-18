<?php get_header(); ?>

<section id="container">
    <section id="content">
        <?php wp_reset_query(); ?>
        <?php rewind_posts(); ?>
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <h2 class="title"><?php the_title(); ?></h2>
        <div class="top"></div>
        <div class="body">
            <div class="allbox padding">
                <?php the_content(); ?>
            </div>
        </div>
        <div class="bottom"></div>
        <?php endwhile; else: ?>
        <p><?php _e('По вашему запросу ничего нет.'); ?></p>
        <?php endif; ?>
    </section>

    <div class="right">
        <div class="boxen">
            <?php get_sidebar('calc') ?>

            <div class="blog-gree">
            <?php get_sidebar('blog') ?>
            </div>
        </div>
    </div>
</section>
</div>

<?php get_footer();?>