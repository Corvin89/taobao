<?php
/*
Template Name: Calculator
*/
?>
<?php get_header(); ?>









<?php if (have_posts()) : while (have_posts()) : the_post(); ?>



<div id="zagolovok"><h1><?php the_title(); ?></h1></div>



 
<?php the_content();?>




<?php endwhile; else: ?>
<p><?php _e('По вашему запросу ничего нет.'); ?></p>
<?php endif; ?>








<?/*


<div id="calculator"></div>
<h1>Калькулятор стоимости доставки (EMS)</h1>







<?
$handle = fopen("http://emspost.ru/api/rest/?method=ems.get.locations&type=russia&plain=true", "rb");
$contents = '';
while (!feof($handle)) {
  $contents .= fread($handle, 8192);
}
fclose($handle);

$obj = json_decode($contents, true);
?>




<form action="/index.php/dostavka-i-oplata/#calculator" method="post">



<p>Стоимость товара (юани)<br />
<input name="cost" type="text">
</p>


<p>Вес посылки (кг, максимум 30)<br />
<input name="weight" type="text">
</p>


<p>Ваше местоположение <br />
<select name="location">
<?
		for($n=0; $n<count($obj[rsp][locations]); $n++)
		{
		echo '<option value="'.$obj[rsp][locations][$n][value].'">'.$obj[rsp][locations][$n][name].'</option>';
		};
?>
</select></p>


   
   <p><input type="submit" value="Расчитать"></p>
   

  </form>


  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  


<?
if ($_SERVER['REQUEST_METHOD']=='POST') {



$request = "http://emspost.ru/api/rest?method=ems.calculate&from=city--blagoveshhensk&to=".$_POST['location']."&weight=".$_POST['weight']."&type=att";

$handle = fopen($request, "rb");
$contents = '';
while (!feof($handle)) {
  $contents .= fread($handle, 8192);
}
fclose($handle);





$obj = json_decode($contents, true);


$price = $obj[rsp][price];
$min = $obj[rsp][term][min];
$max = $obj[rsp][term][max];


$final_price = ceil($_POST['cost']*1.3*4.6) + $price;



	if($obj[rsp][stat]=='ok')
	{
		echo '<div class="wpcf7-response-output wpcf7-mail-sent-ok">Стоимость доставки: <b>'.$final_price.' рублей</b>. Товар будет идти <b>от '.$min.' до '.$max.' дней</b>.</div>';
	}else{
		echo '<div class="wpcf7-response-output wpcf7-validation-errors">Ошибка калькулятора. Пожалуйста, проверьте правильность введения данных.</div>';
	};







};
?>
	

	
	
	
	
	
	
	
	
*/?>












<?php get_footer(); ?>