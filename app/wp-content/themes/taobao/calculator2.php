<?php
/*
Template Name: Калькулятор с расчетом доставки
*/

// НАСТРОЙКИ
// Все, что рядом с настройками, в т.ч. комментарии УДАЛЯТЬ НЕЛЬЗЯ!
// Править только само значение до знака точки с запятой
// В дробных значениях использовать только точку, например 4.9

$kurs_yuanya = 5.4; //актуальный курс юаня
$koefficient = 1.15;  //коэффициент посреднического процента (например, 1.2 = 20%; 1.15 = 15%)
$transport_kg = 300; //стоимость переправки через речку за кг, в рублях
$dostavka_do_blg = 14; //количество дней доставки до Благовещенска
$akciya = 2500; //сумма, при которой действует акция, в юанях


function ems($from, $weight = 31.5) {
		$request = "http://emspost.ru/api/rest?method=ems.calculate&from=city--blagoveshhensk&to=".$from."&weight=".$weight;
		@$handle = fopen($request, "rb");
		if($handle) {
			$contents = '';
			while (!feof($handle)) {
			  $contents .= fread($handle, 8192);
			}
			fclose($handle);
			$obj = json_decode($contents, true);

			$data['price_ems'] = $obj[rsp][price]; //стоимость отправления
			$data['min'] = $obj[rsp][term][min]; //минимальный срок доставки
			$data['max'] = $obj[rsp][term][max]; //максимальный срок доставки
			$data['stat'] = $obj[rsp][stat] ; //статус
		
			return  $data;
		} else { // Если сервер ЕМС не отвечает
			$data['price_ems'] = 0; //стоимость отправления
			$data['min'] = 0; //минимальный срок доставки
			$data['max'] = 0; //максимальный срок доставки
			$data['stat'] = 0 ; //статус
			return $data ;
		}
}

function ems_deliv($from, $weight = 1) {
	if($weight > 31.5) {
		$it = 0 ; // кол-во посылок по 31,5 кг
		$one = ems($from) ; // сколько стоит одна посылка 31,5
		
		for($i = $weight; $i >= 31.5; $i = $i - 31.5) {		
			$it++ ; // сколько всего посылок по 31,5 кг выходит
		}

		$ostatok = ems($from, $weight - ($it * 31.5)) ;
		$price = $one['price_ems'] * $it ;
		
		$data['min'] = $one['min'] ;
		$data['max'] = $one['max'] ;
		$data['stat'] = $one['stat'] ;
		$data['price_ems'] = $price + $ostatok['price_ems'] ;
	} else {	
		$data = ems($from, $weight) ;
	}
	return $data ;
}

?>
<?php get_header(); ?>

<a name="calculator"></a>
<div id="calculator"></div>
<h1>Калькулятор стоимости доставки</h1>
<p>Для удобства расчета стоимости товара с учетом доставки, воспользуйтесь приведенной ниже формой.


<form method="post">


<table border="0">
  <tr>
    <td width="250"><p>Стоимость товара на таобао (юани)</p></td>
    <td><input name="cost_china" type="text" value="<?php echo $_POST['cost_china'] ;?>"></td>
  </tr>
  <tr>
    <td><p>Стоимость доставки по Китаю (юани)</p></td>
    <td><input name="dostavka_china" type="text" value="<?php echo $_POST['dostavka_china'] ;?>"></td>
  </tr>
  <tr>
    <td><p>Вес посылки (кг.)</p></td>
    <td><input name="weight" type="text" value="<?php echo $_POST['weight'] ;?>"></td>
  </tr>
  <tr>
    <td><p>Ваше местоположение</p></td>
    <td>
	
<select name="location">

<?/*
$handle = fopen("http://emspost.ru/api/rest/?method=ems.get.locations&type=russia&plain=true", "rb");
$contents = '';
while (!feof($handle)) {
  $contents .= fread($handle, 8192);
}
fclose($handle);
$obj = json_decode($contents, true);


		for($n=0; $n<count($obj[rsp][locations]); $n++)
		{		echo '<option value="'.$obj[rsp][locations][$n][value].'">'.$obj[rsp][locations][$n][name].'</option>';		};
*/?>

<option value="city--abakan">Абакан</option>
<option value="city--anadyr">Анадырь</option>
<option value="city--anapa">Анапа</option>
<option value="city--arhangelsk">Архангельск</option>
<option value="city--astrahan">Астрахань</option>
<option value="city--barnaul">Барнаул</option>
<option value="city--belgorod">Белгород</option>
<option value="city--birobidzhan">Биробиджан</option>
<option value="city--blagoveshhensk">Благовещенск</option>
<option value="city--brjansk">Брянск</option>
<option value="city--velikij-novgorod">Великий Новгород</option>
<option value="city--vladivostok">Владивосток</option>
<option value="city--vladikavkaz">Владикавказ</option>
<option value="city--vladimir">Владимир</option>
<option value="city--volgograd">Волгоград</option>
<option value="city--vologda">Вологда</option>
<option value="city--vorkuta">Воркута</option>
<option value="city--voronezh">Воронеж</option>
<option value="city--gorno-altajsk">Горно-Алтайск</option>
<option value="city--groznyj">Грозный</option>
<option value="city--dudinka">Дудинка</option>
<option value="city--ekaterinburg">Екатеринбург</option>
<option value="city--elizovo">Елизово</option>
<option value="city--ivanovo">Иваново</option>
<option value="city--izhevsk">Ижевск</option>
<option value="city--irkutsk">Иркутск</option>
<option value="city--ioshkar-ola">Йошкар-Ола</option>
<option value="city--kazan">Казань</option>
<option value="city--kaliningrad">Калининград</option>
<option value="city--kaluga">Калуга</option>
<option value="city--kemerovo">Кемерово</option>
<option value="city--kirov">Киров</option>
<option value="city--kostomuksha">Костомукша</option>
<option value="city--kostroma">Кострома</option>
<option value="city--krasnodar">Краснодар</option>
<option value="city--krasnojarsk">Красноярск</option>
<option value="city--kurgan">Курган</option>
<option value="city--kursk">Курск</option>
<option value="city--kyzyl">Кызыл</option>
<option value="city--lipeck">Липецк</option>
<option value="city--magadan">Магадан</option>
<option value="city--magnitogorsk">Магнитогорск</option>
<option value="city--majkop">Майкоп</option>
<option value="city--mahachkala">Махачкала</option>
<option value="city--mineralnye-vody">Минеральные Воды</option>
<option value="city--mirnyj">Мирный</option>
<option value="city--moskva">Москва</option>
<option value="city--murmansk">Мурманск</option>
<option value="city--mytishhi">Мытищи</option>
<option value="city--naberezhnye-chelny">Набережные Челны</option>
<option value="city--nadym">Надым</option>
<option value="city--nazran">Назрань</option>
<option value="city--nalchik">Нальчик</option>
<option value="city--narjan-mar">Нарьян-Мар</option>
<option value="city--nerjungri">Нерюнгри</option>
<option value="city--neftejugansk">Нефтеюганск</option>
<option value="city--nizhnevartovsk">Нижневартовск</option>
<option value="city--nizhnij-novgorod">Нижний Новгород</option>
<option value="city--novokuzneck">Новокузнецк</option>
<option value="city--novorossijsk">Новороссийск</option>
<option value="city--novosibirsk">Новосибирск</option>
<option value="city--novyj-urengoj">Новый Уренгой</option>
<option value="city--norilsk">Норильск</option>
<option value="city--nojabrsk">Ноябрьск</option>
<option value="city--omsk">Омск</option>
<option value="city--orel">Орел</option>
<option value="city--orenburg">Оренбург</option>
<option value="city--penza">Пенза</option>
<option value="city--perm">Пермь</option>
<option value="city--petrozavodsk">Петрозаводск</option>
<option value="city--petropavlovsk-kamchatskij">Петропавловск-Камчатский</option>
<option value="city--pskov">Псков</option>
<option value="city--rostov-na-donu">Ростов-на-Дону</option>
<option value="city--rjazan">Рязань</option>
<option value="city--salehard">Салехард</option>
<option value="city--samara">Самара</option>
<option value="city--sankt-peterburg">Санкт-Петербург</option>
<option value="city--saransk">Саранск</option>
<option value="city--saratov">Саратов</option>
<option value="city--smolensk">Смоленск</option>
<option value="city--sochi">Сочи</option>
<option value="city--stavropol">Ставрополь</option>
<option value="city--strezhevoj">Стрежевой</option>
<option value="city--surgut">Сургут</option>
<option value="city--syktyvkar">Сыктывкар</option>
<option value="city--tambov">Тамбов</option>
<option value="city--tver">Тверь</option>
<option value="city--toljatti">Тольятти</option>
<option value="city--tomsk">Томск</option>
<option value="city--tula">Тула</option>
<option value="city--tynda">Тында</option>
<option value="city--tjumen">Тюмень</option>
<option value="city--ulan-udje">Улан-Удэ</option>
<option value="city--uljanovsk">Ульяновск</option>
<option value="city--usinsk">Усинск</option>
<option value="city--ufa">Уфа</option>
<option value="city--uhta">Ухта</option>
<option value="city--khabarovsk">Хабаровск</option>
<option value="city--khanty-mansijsk">Ханты-Мансийск</option>
<option value="city--kholmsk">Холмск</option>
<option value="city--cheboksary">Чебоксары</option>
<option value="city--cheljabinsk">Челябинск</option>
<option value="city--cherepovec">Череповец</option>
<option value="city--cherkessk">Черкесск</option>
<option value="city--chita">Чита</option>
<option value="city--elista">Элиста</option>
<option value="city--yuzhno-sahalinsk">Южно-Сахалинск</option>
<option value="city--yakutsk">Якутск</option>
<option value="city--yaroslavl">Ярославль</option>


<option value="region--altajskij-kraj">Алтайский край</option>
<option value="region--amurskaja-oblast">Амурская область</option>
<option value="region--arhangelskaja-oblast">Архангельская область</option>
<option value="region--astrahanskaja-oblast">Астраханская область</option>
<option value="region--belgorodskaja-oblast">Белгородская область</option>
<option value="region--brjanskaja-oblast">Брянская область</option>
<option value="region--vladimirskaja-oblast">Владимирская область</option>
<option value="region--volgogradskaja-oblast">Волгоградская область</option>
<option value="region--vologodskaja-oblast">Вологодская область</option>
<option value="region--voronezhskaja-oblast">Воронежская область</option>
<option value="region--evrejskaja-ao">Еврейская АО</option>
<option value="region--zabajkalskij-kraj">Забайкальский край</option>
<option value="region--ivanovskaja-oblast">Ивановская область</option>
<option value="region--irkutskaja-oblast">Иркутская область</option>
<option value="region--kabardino-balkarskaja-respublika">Кабардино-Балкарская Республика</option>
<option value="region--kaliningradskaja-oblast">Калининградская область</option>
<option value="region--kaluzhskaja-oblast">Калужская область</option>
<option value="region--kamchatskij-kraj">Камчатский край</option>
<option value="region--karachaevo-cherkesskaja-respublika">Карачаево-Черкесская Республика</option>
<option value="region--kemerovskaja-oblast">Кемеровская область</option>
<option value="region--kirovskaja-oblast">Кировская область</option>
<option value="region--kostromskaja-oblast">Костромская область</option>
<option value="region--krasnodarskij-kraj">Краснодарский край</option>
<option value="region--krasnojarskij-kraj">Красноярский край</option>
<option value="region--kurganskaja-oblast">Курганская область</option>
<option value="region--kurskaja-oblast">Курская область</option>
<option value="region--leningradskaja-oblast">Ленинградская область</option>
<option value="region--lipeckaja-oblast">Липецкая область</option>
<option value="region--magadanskaja-oblast">Магаданская область</option>
<option value="region--moskovskaja-oblast">Московская область</option>
<option value="region--murmanskaja-oblast">Мурманская область</option>
<option value="region--neneckij-ao">Ненецкий АО</option>
<option value="region--nizhegorodskaja-oblast">Нижегородская область</option>
<option value="region--novgorodskaja-oblast">Новгородская область</option>
<option value="region--novosibirskaja-oblast">Новосибирская область</option>
<option value="region--omskaja-oblast">Омская область</option>
<option value="region--orenburgskaja-oblast">Оренбургская область</option>
<option value="region--orlovskaja-oblast">Орловская область</option>
<option value="region--penzenskaja-oblast">Пензенская область</option>
<option value="region--permskij-kraj">Пермский край</option>
<option value="region--primorskij-kraj">Приморский край</option>
<option value="region--pskovskaja-oblast">Псковская область</option>
<option value="region--respublika-adygeja">Республика Адыгея</option>
<option value="region--respublika-altaj">Республика Алтай</option>
<option value="region--respublika-bashkortostan">Республика Башкортостан</option>
<option value="region--respublika-burjatija">Республика Бурятия</option>
<option value="region--respublika-dagestan">Республика Дагестан</option>
<option value="region--respublika-ingushetija">Республика Ингушетия</option>
<option value="region--respublika-kalmykija">Республика Калмыкия</option>
<option value="region--respublika-karelija">Республика Карелия</option>
<option value="region--respublika-komi">Республика Коми</option>
<option value="region--respublika-marij-el">Республика Марий Эл</option>
<option value="region--respublika-mordovija">Республика Мордовия</option>
<option value="region--respublika-saha-yakutija">Республика Саха (Якутия)</option>
<option value="region--respublika-sev.osetija-alanija">Республика Сев.Осетия-Алания</option>
<option value="region--respublika-tatarstan">Республика Татарстан</option>
<option value="region--respublika-tyva">Республика Тыва</option>
<option value="region--respublika-khakasija">Республика Хакасия</option>
<option value="region--rostovskaja-oblast">Ростовская область</option>
<option value="region--rjazanskaja-oblast">Рязанская область</option>
<option value="region--samarskaja-oblast">Самарская область</option>
<option value="region--saratovskaja-oblast">Саратовская область</option>
<option value="region--sahalinskaja-oblast">Сахалинская область</option>
<option value="region--sverdlovskaja-oblast">Свердловская область</option>
<option value="region--smolenskaja-oblast">Смоленская область</option>
<option value="region--stavropolskij-kraj">Ставропольский край</option>
<option value="region--tajmyrskij-ao">Таймырский АО</option>
<option value="region--tambovskaja-oblast">Тамбовская область</option>
<option value="region--tverskaja-oblast">Тверская область</option>
<option value="region--tomskaja-oblast">Томская область</option>
<option value="region--tulskaja-oblast">Тульская область</option>
<option value="region--tjumenskaja-oblast">Тюменская область</option>
<option value="region--udmurtskaja-respublika">Удмуртская Республика</option>
<option value="region--uljanovskaja-oblast">Ульяновская область</option>
<option value="region--khabarovskij-kraj">Хабаровский край</option>
<option value="region--khanty-mansijskij-ao">Ханты-Мансийский АО</option>
<option value="region--cheljabinskaja-oblast">Челябинская область</option>
<option value="region--chechenskaja-respublika">Чеченская республика</option>
<option value="region--chuvashskaja-respublika">Чувашская Республика</option>
<option value="region--chukotskij-ao">Чукотский АО</option>
<option value="region--yamalo-neneckij-ao">Ямало-Ненецкий АО</option>
<option value="region--yaroslavskaja-oblast">Ярославская область</option>


</select></p>	
	
	</td>
  </tr>
  <tr>
	<td><p>Оптовый заказ </p></td>
	<td><input type="checkbox" name="opt_check"></td>
  </tr>  
  <tr>
	<td colspan="2"><p><input type="submit" value="Расчитать"></p></td>
  </tr>
</table>




</form>


<?
if ($_SERVER['REQUEST_METHOD']=='POST') { //если инициирована форма

if(isset($_POST['opt_check'])) { // при оптовом заказе делаем пересчет комиссии
	$opt = ceil($_POST['cost_china'] * $kurs_yuanya) ; // оптимизация мля )))
	$transport_kg = 350; 
	if( $opt <= 49999) {
		$koefficient = 1.15 ;
	} elseif(($opt > 49999) && ($opt <= 149999)) {
		$koefficient = 1.10 ;
	} elseif($opt > 149999) {
		$koefficient = 1.05 ;
	}	
}

//КОСТЫЛИ ДЛЯ ИЗВЛЕЧЕНИЯ СТОИМОСТЬ ЖЕЛДОРА
switch ($_POST['location']) {
case 'city--abakan': $location_jde='1'; $location_rus='Абакан'; $location_nrg=''; break;
case 'city--anadyr': $location_jde='0'; $location_rus='Анадырь'; $location_nrg=''; break;
case 'city--anapa': $location_jde='0'; $location_rus='Анапа'; $location_nrg=''; break;
case 'city--arhangelsk': $location_jde='103'; $location_rus='Архангельск'; $location_nrg=''; break;
case 'city--astrahan': $location_jde='4'; $location_rus='Астрахань'; $location_nrg=''; break;
case 'city--barnaul': $location_jde='7'; $location_rus='Барнаул'; $location_nrg=''; break;
case 'city--belgorod': $location_jde='8'; $location_rus='Белгород'; $location_nrg=''; break;
case 'city--birobidzhan': $location_jde='12'; $location_rus='Биробиджан'; $location_nrg=''; break;
case 'city--blagoveshhensk': $location_jde='13'; $location_rus='Благовещенск'; $location_nrg=''; break;
case 'city--brjansk': $location_jde='15'; $location_rus='Брянск'; $location_nrg=''; break;
case 'city--velikij-novgorod': $location_jde='105'; $location_rus='Великий Новгород'; $location_nrg=''; break;
case 'city--vladivostok': $location_jde='16'; $location_rus='Владивосток'; $location_nrg=''; break;
case 'city--vladikavkaz': $location_jde='0'; $location_rus='Владикавказ'; $location_nrg=''; break;
case 'city--vladimir': $location_jde='17'; $location_rus='Владимир'; $location_nrg=''; break;
case 'city--volgograd': $location_jde='18'; $location_rus='Волгоград'; $location_nrg=''; break;
case 'city--vologda': $location_jde='20'; $location_rus='Вологда'; $location_nrg=''; break;
case 'city--vorkuta': $location_jde='0'; $location_rus='Воркута'; $location_nrg=''; break;
case 'city--voronezh': $location_jde='21'; $location_rus='Воронеж'; $location_nrg=''; break;
case 'city--gorno-altajsk': $location_jde='0'; $location_rus='Горно-алтайск'; $location_nrg=''; break;
case 'city--groznyj': $location_jde='0'; $location_rus='Грозный'; $location_nrg=''; break;
case 'city--dudinka': $location_jde='0'; $location_rus='Дудинка'; $location_nrg=''; break;
case 'city--ekaterinburg': $location_jde='24'; $location_rus='Екатеринбург'; $location_nrg=''; break;
case 'city--elizovo': $location_jde='0'; $location_rus='Елизово'; $location_nrg=''; break;
case 'city--ivanovo': $location_jde='25'; $location_rus='Иваново'; $location_nrg=''; break;
case 'city--izhevsk': $location_jde='26'; $location_rus='Ижевск'; $location_nrg=''; break;
case 'city--irkutsk': $location_jde='27'; $location_rus='Иркутск'; $location_nrg=''; break;
case 'city--ioshkar-ola': $location_jde='28'; $location_rus='Йошкар-Ола'; $location_nrg=''; break;
case 'city--kazan': $location_jde='29'; $location_rus='Казань'; $location_nrg=''; break;
case 'city--kaliningrad': $location_jde='0'; $location_rus='Калининград'; $location_nrg=''; break;
case 'city--kaluga': $location_jde='31'; $location_rus='Калуга'; $location_nrg=''; break;
case 'city--kemerovo': $location_jde='32'; $location_rus='Кемерово'; $location_nrg=''; break;
case 'city--kirov': $location_jde='33'; $location_rus='Киров'; $location_nrg=''; break;
case 'city--kostomuksha': $location_jde='0'; $location_rus='Костомукша'; $location_nrg=''; break;
case 'city--kostroma': $location_jde='35'; $location_rus='Кострома'; $location_nrg=''; break;
case 'city--krasnodar': $location_jde='36'; $location_rus='Краснодар'; $location_nrg=''; break;
case 'city--krasnojarsk': $location_jde='37'; $location_rus='Красноярск'; $location_nrg=''; break;
case 'city--kurgan': $location_jde='38'; $location_rus='Курган'; $location_nrg=''; break;
case 'city--kursk': $location_jde='39'; $location_rus='Курск'; $location_nrg=''; break;
case 'city--kyzyl': $location_jde='0'; $location_rus='Кызыл'; $location_nrg=''; break;
case 'city--lipeck': $location_jde='40'; $location_rus='Липецк'; $location_nrg=''; break;
case 'city--magadan': $location_jde='41'; $location_rus='Магадан'; $location_nrg=''; break;
case 'city--magnitogorsk': $location_jde='42'; $location_rus='Магнитогорск'; $location_nrg=''; break;
case 'city--majkop': $location_jde='0'; $location_rus='Майкоп'; $location_nrg=''; break;
case 'city--mahachkala': $location_jde='111'; $location_rus='Махачкала'; $location_nrg=''; break;
case 'city--mineralnye-vody': $location_jde='0'; $location_rus='Минеральные Воды'; $location_nrg=''; break;
case 'city--mirnyj': $location_jde='0'; $location_rus='Мирный'; $location_nrg=''; break;
case 'city--moskva': $location_jde='44'; $location_rus='Москва'; $location_nrg=''; break;
case 'city--murmansk': $location_jde='100'; $location_rus='Мурманск'; $location_nrg=''; break;
case 'city--mytishhi': $location_jde='0'; $location_rus='Мытищи'; $location_nrg=''; break;
case 'city--naberezhnye-chelny': $location_jde='45'; $location_rus='Набережные Челны'; $location_nrg=''; break;
case 'city--nadym': $location_jde='0'; $location_rus='Надым'; $location_nrg=''; break;
case 'city--nazran': $location_jde='0'; $location_rus='Назрань'; $location_nrg=''; break;
case 'city--nalchik': $location_jde='0'; $location_rus='Нальчик'; $location_nrg=''; break;
case 'city--narjan-mar': $location_jde='0'; $location_rus='Нарьян-Мар'; $location_nrg=''; break;
case 'city--nerjungri': $location_jde='47'; $location_rus='Нерюнгри'; $location_nrg=''; break;
case 'city--neftejugansk': $location_jde='0'; $location_rus='Нефтеюганск'; $location_nrg=''; break;
case 'city--nizhnevartovsk': $location_jde='49'; $location_rus='Нижневартовск'; $location_nrg=''; break;
case 'city--nizhnij-novgorod': $location_jde='50'; $location_rus='Нижний Новгород'; $location_nrg=''; break;
case 'city--novokuzneck': $location_jde='52'; $location_rus='Новокузнецк'; $location_nrg=''; break;
case 'city--novorossijsk': $location_jde='53'; $location_rus='Новороссийск'; $location_nrg=''; break;
case 'city--novosibirsk': $location_jde='54'; $location_rus='Новосибирск'; $location_nrg=''; break;
case 'city--novyj-urengoj': $location_jde='55'; $location_rus='Новый Уренгой'; $location_nrg=''; break;
case 'city--norilsk': $location_jde='0'; $location_rus='Норильск'; $location_nrg=''; break;
case 'city--nojabrsk': $location_jde='56'; $location_rus='Ноябрьск'; $location_nrg=''; break;
case 'city--omsk': $location_jde='57'; $location_rus='Омск'; $location_nrg=''; break;
case 'city--orel': $location_jde='58'; $location_rus='Орел'; $location_nrg=''; break;
case 'city--orenburg': $location_jde='59'; $location_rus='Оренбург'; $location_nrg=''; break;
case 'city--penza': $location_jde='60'; $location_rus='Пенза'; $location_nrg=''; break;
case 'city--perm': $location_jde='61'; $location_rus='Пермь'; $location_nrg=''; break;
case 'city--petrozavodsk': $location_jde='62'; $location_rus='Петрозаводск'; $location_nrg=''; break;
case 'city--petropavlovsk-kamchatskij': $location_jde='63'; $location_rus='Петропавловск-Камчатский'; $location_nrg=''; break;
case 'city--pskov': $location_jde='108'; $location_rus='Псков'; $location_nrg=''; break;
case 'city--rostov-na-donu': $location_jde='65'; $location_rus='Ростов-на-Дону'; $location_nrg=''; break;
case 'city--rjazan': $location_jde='66'; $location_rus='Рязань'; $location_nrg=''; break;
case 'city--salehard': $location_jde='0'; $location_rus='Салехард'; $location_nrg=''; break;
case 'city--samara': $location_jde='67'; $location_rus='Самара'; $location_nrg=''; break;
case 'city--sankt-peterburg': $location_jde='68'; $location_rus='Санкт-Петербург'; $location_nrg=''; break;
case 'city--saransk': $location_jde='69'; $location_rus='Саранск'; $location_nrg=''; break;
case 'city--saratov': $location_jde='70'; $location_rus='Саратов'; $location_nrg=''; break;
case 'city--smolensk': $location_jde='71'; $location_rus='Смоленск'; $location_nrg=''; break;
case 'city--sochi': $location_jde='72'; $location_rus='Сочи'; $location_nrg=''; break;
case 'city--stavropol': $location_jde='73'; $location_rus='Ставрополь'; $location_nrg=''; break;
case 'city--strezhevoj': $location_jde='0'; $location_rus='Стрежевой'; $location_nrg=''; break;
case 'city--surgut': $location_jde='76'; $location_rus='Сургут'; $location_nrg=''; break;
case 'city--syktyvkar': $location_jde='104'; $location_rus='Сыктывкар'; $location_nrg=''; break;
case 'city--tambov': $location_jde='78'; $location_rus='Тамбов'; $location_nrg=''; break;
case 'city--tver': $location_jde='99'; $location_rus='Тверь'; $location_nrg=''; break;
case 'city--toljatti': $location_jde='79'; $location_rus='Тольятти'; $location_nrg=''; break;
case 'city--tomsk': $location_jde='80'; $location_rus='Томск'; $location_nrg=''; break;
case 'city--tula': $location_jde='81'; $location_rus='Тула'; $location_nrg=''; break;
case 'city--tynda': $location_jde='0'; $location_rus='Тында'; $location_nrg=''; break;
case 'city--tjumen': $location_jde='82'; $location_rus='Тюмень'; $location_nrg=''; break;
case 'city--ulan-udje': $location_jde='83'; $location_rus='Улан-Удэ'; $location_nrg=''; break;
case 'city--uljanovsk': $location_jde='84'; $location_rus='Ульяновск'; $location_nrg=''; break;
case 'city--usinsk': $location_jde='0'; $location_rus='Усинск'; $location_nrg=''; break;
case 'city--ufa': $location_jde='86'; $location_rus='Уфа'; $location_nrg=''; break;
case 'city--uhta': $location_jde='0'; $location_rus='Ухта'; $location_nrg=''; break;
case 'city--khabarovsk': $location_jde='87'; $location_rus='Хабаровск'; $location_nrg=''; break;
case 'city--khanty-mansijsk': $location_jde='88'; $location_rus='Ханты-Мансийск'; $location_nrg=''; break;
case 'city--kholmsk': $location_jde='0'; $location_rus='Холмск'; $location_nrg=''; break;
case 'city--cheboksary': $location_jde='90'; $location_rus='Чебоксары'; $location_nrg=''; break;
case 'city--cheljabinsk': $location_jde='91'; $location_rus='Челябинск'; $location_nrg=''; break;
case 'city--cherepovec': $location_jde='102'; $location_rus='Череповец'; $location_nrg=''; break;
case 'city--cherkessk': $location_jde='0'; $location_rus='Черкесск'; $location_nrg=''; break;
case 'city--chita': $location_jde='92'; $location_rus='Чита'; $location_nrg=''; break;
case 'city--elista': $location_jde='0'; $location_rus='Элиста'; $location_nrg=''; break;
case 'city--yuzhno-sahalinsk': $location_jde='95'; $location_rus='Южно-Сахалинск'; $location_nrg=''; break;
case 'city--yakutsk': $location_jde='96'; $location_rus='Якутск'; $location_nrg=''; break;
case 'city--yaroslavl': $location_jde='97'; $location_rus='Ярославль'; $location_nrg=''; break;
default: $location_jde='0'; $location_rus='0'; $location_nrg='0';
}; 


if($location_jde<>0){ //если город для ЖДЕ подходит
	$url = "http://api.jde.ru/calculator_ifframe.php";
	$ch = curl_init(); // инициализируем сессию curl
	curl_setopt($ch, CURLOPT_URL,$url); // указываем URL, куда отправлять POST-запрос
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// разрешаем перенаправление
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // указываем, что результат запроса следует передать в переменную, а не вывести на экран
	curl_setopt($ch, CURLOPT_TIMEOUT, 3); // таймаут соединения
	curl_setopt($ch, CURLOPT_POST, 1); // указываем, что данные надо передать именно методом POST
	curl_setopt($ch, CURLOPT_POSTFIELDS, "Branch_From=13&Branch_To=".$location_jde."&Type=0&Weight=".$_POST['weight']."&Volume=&Oversize_Weight=&Oversize_Volume=&Lathing_Volume=&Lathing_Ratio=1&add=1&Volume=0.125"); // добавляем данные POST-запроса
	$result = curl_exec($ch); // выполняем запрос
	curl_close($ch); // завершаем сессию
	$result = mb_convert_encoding ($result ,"UTF-8" , "Windows-1251" ); //преобразуем результат в юникод
	$result = explode("Полная стоимость услуг доставки - <b>", $result); //вычленяем стоимость
	$st = explode(" руб</b>.</p>", $result[1]);
	$price_jde = $st[0]; //записываем стоимость в переменную
	$price_jde = preg_replace("/ +/i", "", $price_jde);; //удаляем лишние пробелы
};

//echo $location_jde.'<br>'.$location_rus.'<br>'.$location_nrg;


$ems = ems_deliv($_POST['location'], $_POST['weight']) ;

//$price_ems = $obj[rsp][price]; //стоимость отправления
//$min = $obj[rsp][term][min]; //минимальный срок доставки
//$max = $obj[rsp][term][max]; //максимальный срок доставки
$price_ems = $ems['price_ems'] ;
$min = $ems['min'] ;
$max = $ems['max'] ;

$min = $min + $dostavka_do_blg - 1; //добавляем к минимальному времени дней до Благовещенска
$max = $max + $dostavka_do_blg; //добавляем к максимальному времени дней до Благовещенска



$final_price_ems = ceil($_POST['cost_china']*$koefficient*$kurs_yuanya + $_POST['dostavka_china']*$kurs_yuanya + $_POST['weight']*$transport_kg) + $price_ems; //Окончательная цена в рублях
$final_price_jde = ceil($_POST['cost_china']*$koefficient*$kurs_yuanya + $_POST['dostavka_china']*$kurs_yuanya + $_POST['weight']*$transport_kg) + $price_jde; //Окончательная цена в рублях
//ceil() - Округляет дробь в большую сторону

	if($ems['stat']=='ok') // Проверка, вернул ли калькулятор ЕМС нормальный результат
	{?>

<hr>	
<br>	
	
	<div class="calculator">
<table width="100%" border="0" cellspacing="2" cellpadding="2">
  <tr>
    <td colspan="3"><div class="thead icon_delivery">Подробный расчет стоимости доставки товара</div></td>
  </tr>
  <tr>
    <td width="450">Стоимость товара в Китае:</td>
    <td><?=$_POST['cost_china'];?> юаней (<?echo round($_POST['cost_china']*$kurs_yuanya);?> руб)</td>
	<td>Курс Юаня <?=$kurs_yuanya;?> руб</td>
  </tr>
  <tr>
    <td>Стоимость доставки до склада в Китае:</td>
    <td><?=$_POST['dostavka_china'];?> юаней (<?echo round($_POST['dostavka_china']*$kurs_yuanya);?> руб)</td>
	<td>Курс Юаня <?=$kurs_yuanya;?> руб</td>
  </tr>
  <tr>
    <td>Вес товара:</td>
    <td><?=$_POST['weight'];?> кг</td>
	<td>Приблизительно</td>
  </tr>
  <tr>
    <td>Cтоимость доставки из Китая в Россию:</td>
    <td><?echo $_POST['weight']*$transport_kg;?> рублей</td>
	<td><?echo $transport_kg;?> руб за кг</td>
  </tr>
  <tr>
    <td>Комиссия за услуги нашей компании:</td>
    <td><?echo round($_POST['cost_china']*$kurs_yuanya*($koefficient - 1));?> рублей</td>
	<td><?echo ($koefficient - 1)*100;?>% от стоимости товара</td>
  </tr>

<?if($location_rus<>0){ //выводит строку с названием Города только если он есть в расшифровке 
?>
  <tr>
    <td>Город назначения:</td>
    <td colspan="2"><?=$location_rus;?></td>
  </tr>
<?};?>

  <tr>
    <td colspan="3"><div class="thead icon_ems">Служба доставки EMS</div></td>
  </tr>
  <tr>
    <td>Стоимость доставки по территории России службой EMS:</td>
    <td colspan="2"><?=$price_ems;?> рублей</td>
  </tr>
  <tr>
    <td>Срок доставки:</td>
    <td>от <?=$min;?> до <?=$max;?> дней</td>
	<td>С учетом <?echo $dostavka_do_blg;?> дней до России</td>
  </tr>
  <tr class="trfinal">
    <td>Конечная стоимость товара с учетом всех комиссий и доставки (EMS):</td>
    <td colspan="2"><b><?=$final_price_ems;?> рублей</b></td>
  </tr>

  
  
<?if($location_jde<>0){ //если подходит для ЖДЕ - выводит расчет 
?>
  <tr>
    <td colspan="3"><div class="thead icon_jde">Служба перевозки грузов "ЖелДорЭкспедиция"</div></td>
  </tr>
  <tr>
    <td>Стоимость доставки по территории России транспортной компанией ЖДЭ:</td>
    <td colspan="2"><?=$price_jde;?> рублей</td>
  </tr>
  <tr class="trfinal">
    <td>Конечная стоимость товара с учетом всех комиссий и доставки (ЖДЭ):</td>
    <td colspan="2"><b><?=$final_price_jde;?> рублей</b></td>
  </tr>
  
  
<?}elseif($location_jde==0){ //выводит строку если не подходит для доставки ЖДЕ
?>
  <tr>
    <td colspan="3"><div class="thead icon_jde">Служба перевозки грузов "ЖелДорЭкспедиция"</div></td>
  </tr>
  <tr>
    <td colspan="3">Доставка до Вашего местонахождения службой ЖелДорЭкспедиция не осуществляется.</td>
  </tr>
<?};?>

</table>
</div>
	

	<?}else{
		echo '<div class="wpcf7-response-output wpcf7-validation-errors">Ошибка калькулятора. Пожалуйста, проверьте правильность введения данных.</div>';
	};
};
?>

<br>
<hr>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<div id="zagolovok"><h1><?php the_title(); ?></h1></div>

<?php the_content();?>

<?php endwhile; else: ?>
<p><?php _e('По вашему запросу ничего нет.'); ?></p>
<?php endif; ?>

<?php get_footer(); ?>