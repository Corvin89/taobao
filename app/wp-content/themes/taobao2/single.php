<?php get_header(); ?>
<?php //var`s for comments
$no="комментариев нет";
$one="комментарий - 1";
$more="комментариев - %";
$onenumber="1";
$morenumber="%";
function catch_that_image() {
  global $post, $posts;
  ob_start();
  ob_end_clean();
  $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
  return $matches[1][1];
}
?>
<body>	
	<div class="width">
		<section id="container">
			<section id="content">
				<h2 class="title">Блог Taobao.ru.com</h2>
				<span class="post">Всего записей: 137</span>
				<?php rewind_posts(); $i=1; ?>  
				<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
				<div class="top"></div>
				<div class="body">
					<div class="page">
						<h2><?php the_title(); ?></h2>
							
									<p><?php the_content(); ?></p>
							
												<div class="article">
							<span class="label"><?php the_tags('', ', ', '<br />'); ?></span>
							<?php endwhile; else: ?>
							<p><?php _e('По вашему запросу ничего нет.'); ?></p>
							<?php endif; ?>
							<a class="com"><?php comments_number(0,$onenumber,$morenumber);?></a>
							<span class="data"><?php the_time('d.m.Y');?></span> 
						</div>
						<div class="soc"><img src="img/soc.gif" alt="" title="" /></div>	
						<div class="comentars">
							<h3>Комментарии</h3>
							<form action="" method="post">
								<div class="item">
									<div class="ava"><a href="#"><img src="img/ava.gif" alt="" title="" /></a></div>
									<textarea></textarea>
								</div>
								<div class="coment">
									<div class="ava"><a href="#"><img src="img/ava.gif" alt="" title="" /></a></div>
									<div class="boxer">
										<p><a href="#" class="name">T-ula</a> <span class="date">22 ноября 2011, 20:55</span></p>
										<p>Здравствуйте! Хотела бы заказать 5 брендовых курточек (одной модели), не будет ли проблем с таможней? Обычно многие поставщики не советуют заказывать более 4-х шт.  одной модели?</p>
									</div>
									<div class="podcoment">
										<div class="ava"><a href="#"><img src="img/ava.gif" alt="" title="" /></a></div>
										<div class="boxe">
											<p><a href="#" class="nameblue">T-ula</a> <span class="date">22 ноября 2011, 20:55</span></p>
											<p>Здравствуйте! Хотела бы заказать 5 брендовых курточек (одной модели), не будет ли проблем с таможней? Обычно многие поставщики не советуют заказывать более 4-х шт.  одной модели?</p>
										</div>
									</div>
								</div>
								<div class="coment">
									<div class="ava"><a href="#"><img src="img/ava.gif" alt="" title="" /></a></div>
									<div class="boxer">
										<p><a href="#" class="name">T-ula</a> <span class="date">22 ноября 2011, 20:55</span></p>
										<p>Здравствуйте! Хотела бы заказать 5 брендовых курточек (одной модели), не будет ли проблем с таможней? Обычно многие поставщики не советуют заказывать более 4-х шт.  одной модели?</p>
									</div>
								</div>
							</form>
							<ul class="rss">
								<li><a href="#" class="rss">RSS</a></li>
								<li><a href="#" class="mail">Подписаться по e-mail</a></li>
							</ul>
						</div>			
					</div>					
				</div>
				<div class="bottom"></div>
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
								<label>Введите слово или фразу на <br/> русском языке и нажмите <br/> кнопку “Перевести” </label>
								<input type="text" class="text" />
								<input type="submit" class="sub" value="Перевести" />
							</div>
							<div class="item">
								<label>Затем нажмите "Поиск на <br/> Taobao" и у вас откроется <br/> страница с результатами поиска.</label>
								<input type="text" class="text" />
								<input type="submit" class="sub" value="Поиск на Taobao.com" />
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
									<p>По восточному календарю покровителем наступающего года будет <a href="#">чёрный водяной Дракон.</a></p>
								</li>
								<li>
									<p><span class="data">02.11.2011</span> <span class="com">15</span></p>
									<p><b>В чём встречать Новый 2012 год?</b></p>
									<p>По восточному календарю покровителем наступающего года будет <a href="#">чёрный водяной Дракон.</a></p>
								</li>
								<li>
									<p><span class="data">02.11.2011</span> <span class="com">15</span></p>
									<p><b>В чём встречать Новый 2012 год?</b></p>
									<p>По восточному календарю покровителем наступающего года будет <a href="#">чёрный водяной Дракон.</a></p>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
	<footer id="footer">
		<div class="box">
			<div class="block">
				<ul>
					<li><a href="#"><img src="img/icon.jpg" alt="" title="" /></a></li>
					<li><a href="#"><img src="img/icon1.jpg" alt="" title="" /></a></li>
					<li><a href="#"><img src="img/icon2.jpg" alt="" title="" /></a></li>
					<li><a href="#"><img src="img/icon3.jpg" alt="" title="" /></a></li>
					<li><a href="#"><img src="img/icon4.jpg" alt="" title="" /></a></li>
					<li><a href="#"><img src="img/icon5.jpg" alt="" title="" /></a></li>
					<li><a href="#"><img src="img/icon6.jpg" alt="" title="" /></a></li>
				</ul>
				<p>Сервис покупок в Китае taobao.ru.com <br/> PANDA TRADE CO.,LTD <br/> Амурская область, г.Благовещенск ул.Чайковского 7, офис 204</p>
			</div>
			<div class="contact">
						<div class="left">
							<div class="fone">
								<em>розничный отдел</em>
								<strong><span>8 (4162)</span> 218-718</strong>
							</div>
							<ul class="contact">
								<li class="qip">611 250 763</li>
								<li class="mail"><a href="mailto:zakaz@taobao.ru.com">zakaz@taobao.ru.com</a></li>
								<li class="skype">taobao.ru.com</li>
							</ul>
						</div>
						<div class="left right">
							<div class="fone">
								<em>розничный отдел</em>
								<strong><span>8 (4162)</span> 218-718</strong>
							</div>
							<ul class="contact">
								<li class="qip">611 250 763</li>
								<li class="mail"><a href="mailto:zakaz@taobao.ru.com">zakaz@taobao.ru.com</a></li>
								<li class="skype">taobao.ru.com</li>
							</ul>
						</div>
					</div>
		</div>			
	</footer>
</body>
</html>