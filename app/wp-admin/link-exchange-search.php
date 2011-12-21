<?php
require_once('admin.php'); 
$title = __('Каталог - Поиск');
$parent_file = 'link-exchange.php';
$today = current_time('mysql', 1); 
require_once('admin-header.php');

$eblex_settings = $table_prefix . "eblex_settings";
$eblex_categories = $table_prefix . "eblex_categories";
$eblex_links = $table_prefix . "eblex_links";

$l_categorycount = $wpdb->get_var("SELECT count(*) FROM `$eblex_categories` WHERE `parent`=''");
$l_subcategorycount = $wpdb->get_var("SELECT count(*) FROM `$eblex_categories` WHERE `parent`!=''");
$l_linkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links`");
$l_activelinkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `status`='1'");
$l_inactivelinkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `status`=0");
$l_reciprocallinkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `reciprocalurl`!=''");
$l_linkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links`");
?>
<?php
if ($noticemsg!="")
{
?>
<div id="message1" class="updated fade"><p><?php _e($noticemsg); ?></p></div>
<?php 
}
?>
<style type="text/css">
<!--
.catlink {
border:0px;
font-size:14px;
font-weight:bold;
color:#6666FF;
}

.catlink:hover {
color:#000066;
}

.catsublink {
border:0px;
font-size:12px;
color:#6666FF;
}

.catsublink:hover {
color:#000066;
}

.plink {
border:0px;
font-size:16px;
font-weight:bold;
color:#000000;
}

.plink:hover {
color:#0000CC;
}

.purl {
color:#CCCCCC;
font-size:11px;
}

.linkbox1
{
background-color:#FFFFFF;
border:1px #FFFFFF solid;
width:100%;
padding:3px;
}

.linkbox1:hover
{
background-color:#F9F9F9;
border:1px #CCCCCC solid;
}

.linkbox2
{
background-color:#FAFAFA;
border:1px #FFFFFF solid;
width:100%;
padding:3px;
}

.linkbox2:hover
{
background-color:#F9F9F9;
border:1px #CCCCCC solid;
}

.catbox {
width:95%;
padding:5px;
border:1px #FFFFFF solid;
}

.catbox:hover {
border:1px #EEEEEE solid;
}
-->
</style>
<?php
if (strlen($_POST['title']) < 3 && $_POST['title']!="")
{
?>
<div id="message1" class="updated fade"><p><?php _e("Поисковый запрос слишком короткий!"); ?></p></div>
<?php 
}
?>
<div class="wrap">
<script language="JavaScript" type="text/javascript">
function deletelink(linkname) {
	var answer = confirm("Удалить эту ссылку?")
	if (answer){
		return true;
	}
	else{
		return false;
	}
}
</script>
<h2><?php _e('Поиск по каталогу ссылок'); ?></h2>

<form id="form1" name="form1" method="post" action="">
  <table class="optiontable">
    <tr valign="top">
      <th scope="row"><?php _e('Запрос:') ?></th>
      <td><input name="title" type="text" id="title" value="<?=$_POST['title'];?>" size="40"<?php if ($e_validated != -1 && $e_validated != 0) {echo(" value=\"$e_c_title\"");} ?> /><br/>Слова, содержащие менее 3-х символов будут игнорироваться!</td>
    </tr>
    <tr valign="top">
      <th scope="row"><?php _e('Метод поиска:') ?></th>
      <td><label>
        <input name="any" type="radio" value="yes" checked="checked" />
        Искать хотя бы одно слово</label><br/>
		<label><input name="any" type="radio" value="no" />
        Искать со всеми словами</label></td>
    </tr>
    <tr valign="top">
      <th scope="row"><?php _e('Искать в:') ?></th>
      <td><select name="region" id="region">
        <option value="title" <?php if ($_POST['region'] == "title") {echo("selected");} ?>>Заголовке</option>
        <option value="url" <?php if ($_POST['region'] == "url") {echo("selected");} ?>>в URL</option>
        <option value="rurl" <?php if ($_POST['region'] == "rurl") {echo("selected");} ?>>в URL обратной ссылки</option>
        <option value="description" <?php if ($_POST['region'] == "description") {echo("selected");} ?>>в описании</option>
        <option value="adescription" <?php if ($_POST['region'] == "adescription") {echo("selected");} ?>>в комментариях администратора</option>
        <option value="email" <?php if ($_POST['region'] == "email") {echo("selected");} ?>>в E-mail</option>
      </select>      </td>
    </tr>

    <tr valign="top">
      <th scope="row"><?php _e('Опции:') ?></th>
      <td><label>
        <input name="visible" type="checkbox" id="visible" value="yes" <?php if ($_POST['visible'] == "yes" || $_POST['title'] == "") {echo("checked");} ?>/>
        Ссылка видимая</label>
        <label>(активаня)<br/>
        <input name="approved" type="checkbox" id="approved" value="yes" <?php if ($_POST['approved'] == "yes" || $_POST['title'] == "") {echo("checked");} ?> />
       Ссылка проверенная</label><br/>
        <label><input name="reciprocal" type="checkbox" id="reciprocal" value="yes" <?php if ($_POST['reciprocal'] == "yes" || $_POST['title'] == "") {echo("checked");} ?>/>
        Ссылка имеет обратную ссылку</label></td>
    </tr>
  </table>
<p class="submit">
<input name="Search" type="submit" id="Search" style="float:right;" value="<?php _e('Поиск') ?>"/> 
</p>
<br/><br/>
</form>
<?php
if ($_POST['title'] != "")
{
	if (strlen($_POST['title']) > 2)
	{
?>
<script language="JavaScript" type="text/javascript">
function hideresults()
{
	document.getElementById("searchresults").innerHTML = "";
}
</script>
<div id="searchresults">
<h2><?php _e('Результаты поиска для "'.$_POST['title'].'"'); ?></h2>
<br/>
<?php
switch ($_POST['region'])
{
	case "title":
	$region = "title";
	break;
	case "url":
	$region = "url";
	break;
	case "rurl":
	$region = "reciprocalurl";
	break;
	case "description":
	$region = "description";
	break;
	case "adescription":
	$region = "administratorcomment";
	break;
	case "email":
	$region = "email";
	break;
}

$addon = "";

if ($_POST['visible'] == "yes")
{
	$addon .= " AND `status`='1'";
}
else
{
	$addon .= " AND `status`='0'";
}

if ($_POST['approved'] == "yes")
{
	$addon .= " AND `active`='1'";
}
else
{
	$addon .= " AND `active`='0'";
}

if ($_POST['reciprocal'] == "yes")
{
	$addon .= " AND `nonreciprocal`='0'";
}
else
{
	$addon .= " AND `nonreciprocal`='1'";
}

$term = $_POST['title'];
$searchterms = "";
$x = 0;
if ($_POST['any'] == "yes")
{
	$searchtermsplit = explode(" ",$term);
	for ($i=0;$i<count($searchtermsplit);$i++)
	{
		if (strlen($searchtermsplit[$i]) > 2)
		{
			if ($x != 0)
			{
				$searchterms .= "|| ";
				$x++;
			}
			$searchterms .= "`$region` LIKE '%".$searchtermsplit[$i]."%'";
		} 
	}
}
else
{
	$searchterms = "`$region` LIKE '%$term%'";
}

if ($searchterms != "")
{
$search = $wpdb->get_results("SELECT * FROM `$eblex_links` WHERE $searchterms $addon ORDER BY `zindex` DESC");

$resultcount = 0;
foreach ($search as $row)
{
	if ($class == "linkbox1") {$class = "linkbox2";} else {$class = "linkbox1";}
	$categoryparent = $wpdb->get_var("SELECT `parent` FROM `$eblex_categories` WHERE `id`='".$row -> category."'");
	$categoryparentnicename = $wpdb->get_var("SELECT `nicename` FROM `$eblex_categories` WHERE `parent`='".$row -> category."'");
	$category = $wpdb->get_var("SELECT `nicename` FROM `$eblex_categories` WHERE `id`='".$row -> category."'");
	
	if ($categoryparent != "")
	{
		$dlink = "?c=".$categoryparentnicename."&s=".$category;
	}
	else
	{
		$dlink = "?c=".$category;
	}
	
	$resultcount++;
	?>
	<div class="<?php echo($class); ?>">
	<a href="<?php echo($row->url); ?>" class="plink" target="_blank"><?php echo($row->title); if ($row->status == "0") {echo("(скрытая)");} ?></a><br/><a href="link-exchange-browse.php<?php echo($dlink."&id=".$row->id."&action=details"); ?>">[Детали]</a> <a href="link-exchange-browse.php<?php echo($dlink."&id=".$row->id."&action=edit"); ?>">[Редактировать]</a> <a href="link-exchange-browse.php<?php echo($dlink."&id=".$row->id."&action=delete"); ?>" onclick="return deletelink()">[Удалить]</a><br/>
	<span class="purl"><?php echo(str_replace("/","",str_replace("http://","",$row->url))); ?></span><br/>
	<?php echo(str_replace("\n","<br/>",$row->description)); ?>
	</div>
	<br/>
	<?php
}
}

if ($resultcount == 0)
{
	_e("По Вашему запросу ничего не найдено.");
}
?>
<p class="submit">
<input name="Search" type="button" id="Search" style="float:right;" onclick="hideresults()" value="<?php _e('Назад') ?>"/> 
</p>
<br/>
<br/>
<br/>
</div>
<?php
		}
}
?>
</div>
<?php
require('./admin-footer.php');
?>
