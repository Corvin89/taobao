<?php
require_once('admin.php');
$title = __('Каталог - Входящие');
$parent_file = 'link-exchange.php';
$today = current_time('mysql', 1);
require_once('admin-header.php');

$eblex_settings = $table_prefix . "eblex_settings";
$eblex_categories = $table_prefix . "eblex_categories";
$eblex_links = $table_prefix . "eblex_links";

if ($l_titlecategory == "") {
    $l_titlecategory = "Root";
} 

if ($l_ressubcategory == "") {
    $ccatid = $l_rescategory;
} else {
    $ccatid = $l_ressubcategory;
} 

if ($ccatid == "") {
    $ccatid = "0";
} 

function check_email($str)
{
    if (ereg("^.+@.+\..+$", $str))
        return 1;
    else
        return 0;
} 
// *******************************************DELETE********************************************
if ($_GET['action'] == "delete") {
    $linkid = $_GET['id'];
    $details = $wpdb->get_results("SELECT * FROM `$eblex_links` WHERE `id`='$linkid' ORDER BY `zindex` DESC");
    $s_email3 = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='email3'");
    $s_emailt2 = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='emailt2'");

    if (isset($details[0]->id)) {
        $wpdb->query("DELETE FROM `$eblex_links` WHERE `id`='$linkid'");
        $noticemsg = "Ссылка удалена!";

        if ($s_email3 == "1") {
            $s_emailt2 = str_replace("{LINK}", $details[0]->url, $s_emailt2);
            $s_emailfrom = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='emailfrom'");
            $headers = "From: $s_emailfrom \r\n";
            $headers .= "Content-Type: text/html; charset=utf-8 ";
            $headers .= "MIME-Version: 1.0 ";
            @wp_mail($details[0]->email, "Link rejection notification", $s_emailt2, $headers);
        } 
    } 
} 
// **********************************************************************************************
// *******************************************APPROVE********************************************
if ($_GET['action'] == "approve") {
    $linkid = $_GET['id'];
    $details = $wpdb->get_results("SELECT * FROM `$eblex_links` WHERE `id`='$linkid' ORDER BY `zindex` DESC");
    $s_email2 = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='email2'");
    $s_emailt1 = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='emailt1'");

    if (isset($details[0]->id)) {
        $wpdb->query("UPDATE `$eblex_links` SET `active`='1' WHERE `id`='$linkid'");
        $noticemsg = "Ссылка одобрена!";

        if ($s_email2 == "1") {
            $s_emailt1 = str_replace("{LINK}", $details[0]->url, $s_emailt1);
            @wp_mail($details[0]->email, "Link approval notification", $s_emailt1, "From: Link administrator\n");
        } 
    } 
} 
// **********************************************************************************************
// ********************************************** PAGING ****************************************
// **********************************************************************************************
$linkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `active`='0'");
$number_of_pages = ceil($linkcount / 10);
$page = $_GET['p'];

if ($page > $number_of_pages && $number_of_pages != "0") {
    if ($_GET['action'] == "delete" && $_POST['confirm'] == "yes") {
        $page--;
        if ($page > $number_of_pages) {
            $noticemsg = "Неправильный номер страницы!";
        } 
    } else {
        $noticemsg = "Неправильный номер страницы!";
    } 
} 

if (!is_numeric($page) || $page == "") {
    $page = 1;
} 

$page--;
$limit1 = $page * 10;
$limit2 = 10;
$link = $wpdb->get_results("SELECT * FROM `$eblex_links` WHERE `active`='0' ORDER BY `zindex` DESC LIMIT $limit1,$limit2");
$checkcount = count($link);
// ************************************************************************
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

.pagebox {
border:1px #CCCCCC solid;
padding:4px;
padding-left:6px;
padding-right:6px;
text-align:center;
}

.pagebox:hover {
background-color:#F5F3FE;
}

.pageboxselected {
border:1px #CCCCCC solid;
padding:4px;
padding-left:6px;
padding-right:6px;
text-align:center;
background-color:#E6E8FF;
}

.pageboxselected:hover {
background-color:#CDCEFE;
}
-->
</style>
<script language="JavaScript" type="text/javascript">
function getpage()
{
	page = prompt("<?php _e('Перейти к:'); ?>");
	if (page != null)
	{
		document.getElementById('gotopage').href = document.getElementById('gotopage').href + page;
	}
	else
	{
		return false;
	}
}
</script>
<?php
if ($noticemsg!="")
{
?>
<div id="message1" class="updated fade"><p><?php _e($noticemsg); ?></p></div>
<?php 
}
?>

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

<div class="wrap">

<?php
//*******************************************DETAILS********************************************
if ($_GET['action'] == "details")
{
	$linkid = $_GET['id'];
	$details = $wpdb->get_results("SELECT * FROM `$eblex_links` WHERE `id`='$linkid' ORDER BY `zindex` DESC");
	$linkcategory = $wpdb->get_var("SELECT `title` FROM `$eblex_categories` WHERE `id`='".$details[0] -> category."' ORDER BY `zindex` DESC");
	
	if (isset($details[0] -> id))
	{
	?>
	<script language="JavaScript" type="text/javascript">
 	function hideview()
	{
		document.getElementById('viewlink').innerHTML = "";
	}
    </script>
	<div id="viewlink">
	<h2><?php _e('Детали ссылки'); ?></h2>
	<p>
	<div><strong>Заголовок:</strong> <?php echo($details[0] -> title); ?></div>
	<div><strong>URL:</strong> <a href="<?php echo($details[0] -> url); ?>"><?php echo(str_replace("http://","",$details[0] -> url)); ?></a></div>
	<div><strong>Обратная ссылка, URL:</strong> <?php if ($details[0] -> nonreciprocal == "0") { if ($details[0] -> reciprocalurl != "http://") {?><a href="<?php echo($details[0] -> reciprocalurl); ?>"><?php } else {echo("Не найдена");} echo(str_replace("http://","",$details[0] -> reciprocalurl)); } else {_e("Без обратной ссылки");}?></a></div>
	<div><strong>Катгория:</strong> <?php echo($linkcategory); ?></div>
	<div><strong>Описание:</strong> <?php echo(str_replace("\n","<br/>",$details[0] -> description)); ?></div>
	<div><strong>Комментарий администратора:</strong> <?php if ($details[0] -> administratorcomment != "") {echo(str_replace("\n","<br/>",$details[0] -> administratorcomment));} else {echo("N/A");} ?></div>
	<div><strong>E-mail:</strong> <a href="mailto:<?php echo($details[0] -> email); ?>"><?php echo($details[0] -> email); ?></a></div>
	<div><strong>Добавлена:</strong> <?php echo(date("H:i:s j.n.Y.",$details[0] -> time)); ?></div>
	<div><strong>Порядок сортировки:</strong> <?php echo($details[0] -> zindex); ?></div>
	<div><strong>Статус:</strong> <?php if ($details[0] -> status == "1") {echo("Активная");} else {echo("Скрытая");} ?></div>
    <div><strong>Статус проверки:</strong> <?php if ($details[0] -> active == "1") {_e("Принята");} else {_e("Отклонена");} ?></div>
	<p class="submit">
	<input type="button" value="<?php _e('OK') ?>" name="ok" style="float:left;" onclick="hideview()"/>
	</p>
	<br/><br/>
	</p>
	</div>
	<?php
}
}
?>

<?php
$linkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `active`='0'"); //are there any links at all?

if ($linkcount > 0)
{
?>

<h2><?php _e('Непроверенные ссылки'); ?></h2>
<?php
$linkcount = 0;

foreach ($link as $row)
{
	if ($class == "linkbox1") {$class = "linkbox2";} else {$class = "linkbox1";}
	if ($_GET['s'] != "")
	{
		$dlink = "?c=".$_GET['c']."&s=".$_GET['s'];
	}
	else
	{
		$dlink = "?c=".$_GET['c'];
	}
	
	$parentcheck = $wpdb->get_var("SELECT `parent` FROM `$eblex_categories` WHERE `id`='".$row->category."'");
	$addonbrowselink = "";
	$categoryname = "";
	$categorynicename = "";
	
	if ($parentcheck != "")
	{
		$parentname = $wpdb->get_var("SELECT `title` FROM `$eblex_categories` WHERE `id`='".$parentcheck ."'");
		$parentnicename = $wpdb->get_var("SELECT `nicename` FROM `$eblex_categories` WHERE `id`='".$parentcheck ."'");
		$categoryname = $parentname." &raquo; ";
		$catstring = "subcategory";
	}
	else
	{
		$categoryname = "";
		$parentnicename = "";
		$catstring = "category";
	}
	
	$categoryname .= $wpdb->get_var("SELECT `title` FROM `$eblex_categories` WHERE `id`='".$row->category."'");
	$categorynicename .= $wpdb->get_var("SELECT `nicename` FROM `$eblex_categories` WHERE `id`='".$row->category."'");

	if ($parentnicename != "")
	{
		$addonbrowselink .= "?c=$parentnicename&s=$categorynicename";
	}
	else
	{
		$addonbrowselink .= "?c=$categorynicename";
	}
	
	?>
	<div class="<?php echo($class); ?>">
	<a href="<?php echo($row->url); ?>" class="plink" target="_blank"><?php echo($row->title); if ($row->status == "0") {echo("(скрытая)");} ?></a><br/><a href="link-exchange-inbox.php<?php echo($dlink."&id=".$row->id."&action=approve"); ?>">[Проверена]</a> <a href="link-exchange-inbox.php<?php echo($dlink."&id=".$row->id."&action=details"); ?>">[Детали]</a> <a href="link-exchange-browse.php<?php echo($dlink."&id=".$row->id."&action=edit"); ?>">[Правка]</a> <a href="link-exchange-inbox.php<?php echo($dlink."&id=".$row->id."&action=delete"); ?>" onclick="return deletelink()">[Удалить]</a> - <a href="<?php echo($row->url); ?>" target="_blank">[Посетить сайт]</a><?php if ($row->reciprocalurl != "http://" && $row->reciprocalurl != "" && $row->nonreciprocal != "1") { ?> <a href="<?php echo($row->reciprocalurl); ?>" target="_blank">[Посетить страницу с обратной ссылкой]</a><?php } ?><br/>
	<span class="purl"><?php echo(str_replace("/","",str_replace("http://","",$row->url))); ?></span><br/>
	<strong>Предложена:</strong> <?php echo(date("H:i:s j.n.Y.",$row -> time)); ?><br/>
	<strong>В категорию: <?php echo($catstring); ?>:</strong> <a href="link-exchange-browse.php<?php echo($addonbrowselink); ?>"><?php echo($categoryname); ?></a>
	<br/>
	<strong>Описание:</strong> <?php echo($row->description); ?>
	</div>
	<br/>
	<?php
}
$linkcount++;
}
if ($linkcount == 0)
{
	_e("<div align=\"center\"><strong>Нет ссылок для отображения.</strong></div>");
}
else
{
?>
<br/>
<div align="right">
<?php
$realpage = $page+1;
_e("Страница <strong>$realpage</strong> из <strong>$number_of_pages</strong> &nbsp;");
$x = 0;
$threedots = 0;
for ($i=1;$i<=$number_of_pages;$i++)
{
	if ($i > 5 && $threedots == 0 && $number_of_pages>10)
	{
		echo("...&nbsp;");
		$threedots = 1;
	}
	
	if ($i == $page+1)
	{
		$pageboxstyle = "отметить";
	}
	else
	{
		$pageboxstyle = "";
	}
	
	if ($i <= 5 || $i > $number_of_pages-5)
	{
	echo("<a href=\"?p=$i"."\" class=\"pagebox$pageboxstyle\">$i</a>&nbsp;");
	}
	$x++;
}
?>
<a href="<?php echo("?p="); ?>" class="pagebox" id="gotopage" onclick="return getpage()">Перейти к</a>
</div>
<?php
}
require('./admin-footer.php');
?>
