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
            <h2>Внутренний курс <span>Taobao.ru.com:</span></h2>
            <span class="calcul">5,3</span>

            <div class="calcul">
                <a href="#">Калькулятор</a>

                <p>Рассчитать стоимость товаров с учетом доставки.</p>
            </div>
            <div class="top-s"></div>
            <div class="body-s">
                <h2>Русский поиск <span>на Taobao.com:</span></h2>

                <form action="" method="post">
                    <div class="item">
                        <label>Введите слово или фразу на <br/> русском языке и нажмите <br/> кнопку “Перевести”
                        </label>
                        <input type="text" class="text"/>
                        <input type="submit" class="sub" value="Перевести"/>
                    </div>
                    <div class="item">
                        <label>Затем нажмите "Поиск на <br/> Taobao" и у вас откроется <br/> страница с результатами
                            поиска.</label>
                        <input type="text" class="text"/>
                        <input type="submit" class="sub" value="Поиск на Taobao.com"/>
                    </div>
                    <div class="item">
                        <a href="#">Видеоинструкция</a>
                    </div>
                </form>
            </div>
            <div class="bottom-s"></div>
            <div class="blog-gree">
                <div class="blog">
                    <h2>Новое в блоге</h2>
                    <ul>
                        <li>
                            <p><span class="data">02.11.2011</span> <span class="com">15</span></p>

                            <p><b>В чём встречать Новый 2012 год?</b></p>

                            <p>По восточному календарю покровителем наступающего года будет <a href="#">чёрный водяной
                                Дракон.</a></p>
                        </li>
                        <li>
                            <p><span class="data">02.11.2011</span> <span class="com">15</span></p>

                            <p><b>В чём встречать Новый 2012 год?</b></p>

                            <p>По восточному календарю покровителем наступающего года будет <a href="#">чёрный водяной
                                Дракон.</a></p>
                        </li>
                        <li>
                            <p><span class="data">02.11.2011</span> <span class="com">15</span></p>

                            <p><b>В чём встречать Новый 2012 год?</b></p>

                            <p>По восточному календарю покровителем наступающего года будет <a href="#">чёрный водяной
                                Дракон.</a></p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
</div>
<?php get_footer(); ?>