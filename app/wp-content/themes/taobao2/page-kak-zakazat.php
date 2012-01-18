<?php get_header(); ?>
<section id="container">
    <section id="content">
        <?php wp_reset_query(); ?>
        <?php rewind_posts(); ?>
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <h2 class="title"><?php the_title(); ?></h2>
        <div class="top"></div>
        <div class="body">
                <div class="steps">
                <div class="step-1">
                    <p><?php echo get_post_meta($post->ID, "step-1", true);?></p>
                </div>
                <div class="step-2">
                    <p><?php echo get_post_meta($post->ID, "step-2", true);?></p>
                </div>
                <div class="step-3">
                    <p><?php echo get_post_meta($post->ID, "step-3", true);?></p>
                </div>
                <div class="step-4">
                    <p><?php echo get_post_meta($post->ID, "step-4", true);?></p>
                </div>
                <div class="step-5">
                    <p><?php echo get_post_meta($post->ID, "step-5-1", true);?></p>

                    <p><?php echo get_post_meta($post->ID, "step-5-2", true);?></p>
                </div>
                <div class="step-6">
                    <p><?php echo get_post_meta($post->ID, "step-6", true);?></p>
                </div>
                <div class="step-7">
                    <p><?php echo get_post_meta($post->ID, "step-7", true);?></p>
                </div>
                </div>
                    <span class="slogan"><?php the_content(); ?></span>
            <?php echo do_shortcode('[contact-form 2 "Обратная связь"]') ?>
        </div>
        <div class="bottom"></div>
        <?php endwhile; else: ?>
        <p><?php _e('По вашему запросу ничего нет.'); ?></p>
        <?php endif; ?>
    </section>
    <div class="right">
        <div class="boxen">
            <?php get_sidebar('calc') ?>
            </div>
        <div class="blog-gree">
            <?php get_sidebar('blog') ?>
        </div>
    </div>
</section>
</div>
<?php get_footer(); ?>