<?php
/*
Template Name: Otziv
*/
?>
<?php get_header(); ?>

<section id="container">
    <section id="content">
        <h2 class="title">Отзывы о сервисе Taobao.ru.com</h2>
        <div class="top"></div>
        <div class="body">
            <div class="page">
                <h2>Какие цвета актуальны в этом сезоне?</h2>
                <p><b>Здесь Вы можете оставить свой отзыв о нашей работе или задать интересующий Вас вопрос.</b></p>
                <p>Cлезно просим вас. прежде, чем написать отзыв или замечание на сайте учтите некоторые моменты:</p>
                <p><b>График работы компании:</b> с 04.00 до 13.00 (время московское). выходные: суббота и воскресенье.</p>
                <p><b>Срок доставки</b> до Благовещенска (после оплаты): мин.13 дней, макс. 23 дня.</p>
                <p><b>Обработка заявки</b> в течении 1-2 дней.</p>
                <p>Компания старается всегда идти на встречу клиенту.</p>
                <p>Надеемся, что вы изучили все основные моменты работы с Таобао («что это и с чем его едят»).</p>
                <p>Ну и наконец мы просто такие же люди как и вы.<b> Мы вас любим :)</b></p>
                <div class="article">
                    <p>Также просим вас присоединиться к нам в соц. сетях и оставлять свои отзывы там! Вот ссылка на нашу страницу вконтакте!</p>
                    <p><b>Спасибо всем, кто написал свой отзыв !</b></p>
                </div>
                <div class="comentars">
                    <h3>Отзывы</h3>
                    <form action="" method="post">
                        <div class="item">
                            <div class="ava"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/ava.gif" alt="" title="" /></a></div>
                            <textarea></textarea>
                        </div>
                        <div class="coment">
                            <div class="ava"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/ava.gif" alt="" title="" /></a></div>
                            <div class="boxer">
                                <p><a href="#" class="name">T-ula</a> <span class="date">22 ноября 2011, 20:55</span></p>
                                <p>Здравствуйте! Хотела бы заказать 5 брендовых курточек (одной модели), не будет ли проблем с таможней? Обычно многие поставщики не советуют заказывать более 4-х шт.  одной модели?</p>
                            </div>
                            <div class="podcoment">
                                <div class="ava"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/ava.gif" alt="" title="" /></a></div>
                                <div class="boxe">
                                    <p><a href="#" class="nameblue">T-ula</a> <span class="date">22 ноября 2011, 20:55</span></p>
                                    <p>Здравствуйте! Хотела бы заказать 5 брендовых курточек (одной модели), не будет ли проблем с таможней? Обычно многие поставщики не советуют заказывать более 4-х шт.  одной модели?</p>
                                </div>
                            </div>
                        </div>
                        <div class="coment">
                            <div class="ava"><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/ava.gif" alt="" title="" /></a></div>
                            <div class="boxer">
                                <p><a href="#" class="name">T-ula</a> <span class="date">22 ноября 2011, 20:55</span></p>
                                <p>Здравствуйте! Хотела бы заказать 5 брендовых курточек (одной модели), не будет ли проблем с таможней? Обычно многие поставщики не советуют заказывать более 4-х шт.  одной модели?</p>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="pagenawi">
                    <a href="#" class="prev"></a>
                    <a href="#">1</a>
                    <a href="#">2</a>
                    <span class="activ">3</span>
                    <a href="#">4</a>
                    <a href="#">5</a>
                    <a href="#">6</a>
                    <a href="#">7</a>
                    <a href="#">8</a>
                    <a href="#">9</a>
                    <span>....</span>
                    <a href="#">78</a>
                    <a href="#">79</a>
                    <a href="#">80</a>
                    <a href="#">81</a>
                    <a href="#">82</a>
                    <a href="#" class="next"></a>
                </div>
            </div>
        </div>
        <div class="bottom"></div>
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