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
                <div class="work">
                    <h3>Мы являемся посредником taobao.com и других аукционов Китая:</h3>
                    <div class="desk">
                        <div class="icon"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/icon.gif" alt="" title="" /></a></div>
                        <p>У нас работают русскоговорящие менеджеры, владеющие не только русским, но также китайским и английским языками. Работаем вместе и всегда рады обслужить Вас!</p>
                    </div>
                    <div class="desk">
                        <div class="icon"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/icon1.gif" alt="" title="" /></a></div>
                        <p>Главный офис компании находится всего в километре от Китая: в г. Благовещенске, Амурской области. В Китае, в г.Хэйхэ расположены дополнительный офис и склад компании. Пересекаем границу всего за 10 минут! Именно благодаря нашему месторасположению, Вы можете быть уверены, что Ваш заказ проверится до того, как пересечет границу.</p>
                    </div>
                </div>
                <div class="work">
                    <h3>У вас есть товар, хотите недорого перевезти его в Россию? Тогда вам – к нам!</h3>
                    <div class="desk">
                        <div class="icon"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/icon2.gif" alt="" title="" /></a></div>
                        <p>У нас отсутствуют дополнительные таможенные пошлины, задержки, ограничения по цене и весу, которые могут возникнуть при отправке груза через EMS и ChinaPost. У нас перевозка Карго!</p>
                    </div>
                    <div class="desk">
                        <div class="icon"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/icon3.gif" alt="" title="" /></a></div>
                        <p>Работая с клиентами, прежде всего, мы стремимся зарекомендовать себя как самого надежного посредника <a href="#">taobao.com</a></p>
                    </div>
                    <div class="desk">
                        <div class="icon"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/icon4.gif" alt="" title="" /></a></div>
                        <p>Исключительно точные сроки выполнения заказа! Повторимся: находимся на границе с Китаем. Так сказать, одна нога здесь – другая там.</p>
                    </div>
                    <div class="desk">
                        <div class="icon"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/icon5.gif" alt="" title="" /></a></div>
                        <p>Принимаем заказы любой сложности, габаритов и широкого ассортимента (кроме наркотиков, оружия и запрещенных для перевозки товаров) – доставим  все и в любых количествах.</p>
                    </div>
                    <div class="desk">
                        <div class="icon"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/icon6.gif" alt="" title="" /></a></div>
                        <p>Работаем со всеми аукционами и интернет-магазинами Китая.</p>
                    </div>
                    <div class="desk">
                        <div class="icon"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/icon7.gif" alt="" title="" /></a></div>
                        <p>Сроки доставки могут сокращаться за счет установленных особых тесных связей с проверенными поставщиками.</p>
                    </div>
                    <div class="desk">
                        <div class="icon"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/icon8.gif" alt="" title="" /></a></div>
                        <p>Предлагаем простые и проверенные <a href="#">способы доставки по России.</a></p>
                    </div>
                </div>
                <div class="banner">
                    <a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/taobao.gif" alt="" title="" /></a>
                </div>
            </div>
        </div>

        <?php the_content(); ?>
        <?php endwhile; else: ?>
        <p><?php _e('По вашему запросу ничего нет.'); ?></p>
        <?php endif; ?>
        <div class="bottom"></div>
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