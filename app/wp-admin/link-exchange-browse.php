<?php
require_once('admin.php');
$title = __('Каталог - Просмотр ссылок');
$parent_file = 'link-exchange.php';
$today = current_time('mysql', 1);
require_once('admin-header.php');

$eblex_settings = $table_prefix . "eblex_settings";
$eblex_categories = $table_prefix . "eblex_categories";
$eblex_links = $table_prefix . "eblex_links";

function eblex_fixinput($str)
{
    $str = str_replace("\"", "&quot;", $str);
    $str = str_replace("<", "&lt;", $str);
    $str = str_replace(">", "&gt;", $str);

    return $str;
} 

$l_category = $_GET['c'];
if ($l_category != "") {
    $l_rescategory = $wpdb->get_var("SELECT `id` FROM `$eblex_categories` WHERE `nicename`='$l_category' AND `parent`=''");
    $l_titlecategory = $wpdb->get_var("SELECT `title` FROM `$eblex_categories` WHERE `nicename`='$l_category' AND `parent`=''");

    $l_subcategory = $_GET['s'];
    if ($l_subcategory != "") {
        $l_ressubcategory = $wpdb->get_var("SELECT `id` FROM `$eblex_categories` WHERE `nicename`='$l_subcategory' AND `parent`='$l_rescategory'");
        $l_subcategorymysql = " AND `id`='$l_ressubcategory'";
        $l_titlecategory = $wpdb->get_var("SELECT `title` FROM `$eblex_categories` WHERE `nicename`='$l_subcategory' AND `parent`='$l_rescategory'");
    } 
} 

if ($l_titlecategory == "") {
    $l_titlecategory = "Root";
} 

$cat = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='$l_rescategory'$l_subcategorymysql ORDER BY `zindex` DESC");
$catadd = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='' ORDER BY `zindex` DESC");
$catcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_categories` WHERE `parent`='$l_rescategory'$l_subcategorymysql");

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

$validated = -1;

if (isset($_POST['title']) && $_POST['title'] != "") {
    $validated = 0;
    $l_title = eblex_fixinput($_POST['title']);
    $l_description = eblex_fixinput($_POST['description']);
    if ($_POST['nonreciprocal'] == "yes") {
        $l_nonreciprocal = "1";
    } else {
        $l_nonreciprocal = "0";
    } 
    $l_url = eblex_fixinput($_POST['url']);
    $l_rurl = eblex_fixinput($_POST['rurl']);
    $l_email = eblex_fixinput($_POST['email']);
    $l_priorityindex = eblex_fixinput($_POST['priorityindex']);
    $l_acomment = eblex_fixinput($_POST['acomment']);
    $l_status = $_POST['status'];
    $l_category = $_POST['category'];
    $l_id = md5(uniqid(rand(), true) . $l_title);
    $l_time = time();

    if ($l_url != "" || $l_url != "http://") {
        if ($l_priorityindex != "" && is_numeric($l_priorityindex)) {
            if ($l_category != "") {
                foreach ($catadd as $row) {
                    if ($row->id != $l_parent) {
                        $validated = 1;
                    } 
                } 

                if ($validated == 1) {
                    if ($l_email != "" && check_email($l_email) == 1) {
                        if ($l_status == "0" || $l_status == "1") {
                            $wpdb->query("INSERT INTO `$eblex_links` (`title`, `active`, `nonreciprocal`, `url`, `category`, `description`, `email`, `reciprocalurl`, `status`, `time`, `administratorcomment`, `zindex`, `id`) VALUES ('$l_title', '1', '$l_nonreciprocal', '$l_url', '$l_category', '$l_description', '$l_email', '$l_rurl', '$l_status', '$l_time', '$l_acomment', '$l_priorityindex', '$l_id');");
                            $noticemsg = "Ссылка успешно добавлен!";
                            $validated = 2;
                        } else {
                            $noticemsg = "Неправильное значение статуса!";
                        } 
                    } else {
                        $noticemsg = "Неправильный e-mail!";
                    } 
                } else {
                    $noticemsg = "Несуществующая категория!";
                } 
            } else {
                $noticemsg = "Вы не выбрали категорию!";
            } 
        } else {
            $noticemsg = "Порядок сортировки указан неверно!";
        } 
    } else {
        $noticemsg = "URL некорректный!!";
    } 
} 

if (isset($_POST['e_title']) && $_POST['e_title'] != "") {
    $e_validated = 0;
    $e_l_title = eblex_fixinput($_POST['e_title']);
    $e_l_description = eblex_fixinput($_POST['e_description']);
    if ($_POST['e_nonreciprocal'] == "yes") {
        $e_l_nonreciprocal = "1";
    } else {
        $e_l_nonreciprocal = "0";
    } 
    $e_l_url = eblex_fixinput($_POST['e_url']);
    $e_l_rurl = eblex_fixinput($_POST['e_rurl']);
    $e_l_email = eblex_fixinput($_POST['e_email']);
    $e_l_priorityindex = $_POST['e_priorityindex'];
    $e_l_acomment = eblex_fixinput($_POST['e_acomment']);
    $e_l_status = $_POST['e_status'];
    $e_l_category = $_POST['e_category'];
    $e_l_id = $_POST['e_linkid'];

    $details = $wpdb->get_var("SELECT `id` FROM `$eblex_links` WHERE `id`='$e_l_id'");
    if (isset($details)) {
        $e_cat = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='$l_rescategory'$l_subcategorymysql ORDER BY `zindex` DESC");
        $e_catadd = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='' ORDER BY `zindex` DESC");

        if ($e_l_url != "" || $e_l_url != "http://") {
            if ($e_l_priorityindex != "" && is_numeric($e_l_priorityindex)) {
                if ($e_l_category != "") {
                    foreach ($e_catadd as $e_row) {
                        if ($e_row->id != $e_l_parent) {
                            $e_validated = 1;
                        } 
                    } 

                    if ($e_validated == 1) {
                        if ($e_l_email != "" && check_email($e_l_email) == 1) {
                            if ($e_l_status == "0" || $e_l_status == "1") {
                                $wpdb->query("UPDATE `$eblex_links` SET `title`='$e_l_title', `nonreciprocal`='$e_l_nonreciprocal', `url`='$e_l_url', `category`='$e_l_category', `description`='$e_l_description', `email`='$e_l_email', `reciprocalurl`='$e_l_rurl', `status`='$e_l_status', `administratorcomment`='$e_l_acomment', `zindex`='$e_l_priorityindex' WHERE `id`='$e_l_id'");
                                $noticemsg = "Ссылка успешно изменена!";
                                $e_validated = 2;
                            } else {
                                $noticemsg = "Неправильное значение статуса!";
                            } 
                        } else {
                            $noticemsg = "Неправильный e-mail!";
                        } 
                    } else {
                        $noticemsg = "Несуществующая категория!";
                    } 
                } else {
                    $noticemsg = "Вы не выбрали категорию!";
                } 
            } else {
                $noticemsg = "Порядок сортировки указан неверно!";
            } 
        } else {
            $noticemsg = "URL неправильный!";
        } 
    } else {
        $noticemsg = "Некорректная ссылка!";
    } 
} 
// **************************** Globals ******************************
if ($_GET['s'] == "") {
    if ($_GET['c'] == "") {
        $linkcategorynicename = "root";
    } else {
        $linkcategorynicename = $_GET['c'];
    } 
} else {
    $linkcategorynicename = $_GET['s'];
} 
// *******************************************DELETE********************************************
if ($_GET['action'] == "delete") {
    $linkid = $_GET['id'];
    $details = $wpdb->get_results("SELECT * FROM `$eblex_links` WHERE `id`='$linkid' ORDER BY `zindex` DESC");

    if (isset($details[0]->id)) {
        $wpdb->query("DELETE FROM `$eblex_links` WHERE `id`='$linkid'");
        $noticemsg = "Link deleted!";
    } 
} 
// **********************************************************************************************
// ********************************************** PAGING ****************************************
$categoryid = $wpdb->get_var("SELECT `id` FROM `$eblex_categories` WHERE `nicename`='$linkcategorynicename'");
$linkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `category`='$categoryid' AND `active`='1'");
$number_of_pages = ceil($linkcount / 10);
$page = $_GET['p'];

if ($page > $number_of_pages && $number_of_pages != "0") {
    if ($_GET['action'] == "delete" && $_POST['confirm'] == "yes") {
        $page--;
        if ($page > $number_of_pages) {
            $noticemsg = "Неверный номер страницы!";
        } 
    } else {
        $noticemsg = "Неверный номер страницы!";
    } 
} 

if (!is_numeric($page) || $page == "") {
    $page = 1;
} 

$page--;
$limit1 = $page * 10;
$limit2 = 10;
$link = $wpdb->get_results("SELECT * FROM `$eblex_links` WHERE `category`='$categoryid' AND `active`='1' ORDER BY `zindex` DESC LIMIT $limit1,$limit2");
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
	page = prompt("<?php _e('Введите номер страницы, на которую Вы хотите перейти:'); ?>");
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
	var answer = confirm("Удалить ссылку?")
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
	<div><strong>Обратная ссылка (URL):</strong> <?php if ($details[0] -> nonreciprocal == "0") { if ($details[0] -> reciprocalurl != "http://") { ?><a href="<?php echo($details[0] -> reciprocalurl); ?>"><?php  } else {echo("Обратная ссылка не найдена");} echo(str_replace("http://","",$details[0] -> reciprocalurl)); } else {_e("Без обратной ссылки");}?></a></div>
	<div><strong>Категория:</strong> <?php echo($linkcategory); ?></div>
	<div><strong>Описание:</strong> <?php echo(str_replace("\n","<br/>",$details[0] -> description)); ?></div>
	<div><strong>Комментарий администратора:</strong> <?php if ($details[0] -> administratorcomment != "") {echo(str_replace("\n","<br/>",$details[0] -> administratorcomment));} else {echo("N/A");} ?></div>
	<div><strong>E-mail:</strong> <a href="mailto:<?php echo($details[0] -> email); ?>"><?php echo($details[0] -> email); ?></a></div>
	<div><strong>Добавлена:</strong> <?php echo(date("H:i:s j.n.Y.",$details[0] -> time)); ?></div>
	<div><strong>Порядок сортировки:</strong> <?php echo($details[0] -> zindex); ?></div>
	<div><strong>Статус:</strong> <?php if ($details[0] -> status == "1") {echo("Видимая");} else {echo("Скрытая");} ?></div>
    <div><strong>Статус проверки:</strong> <?php if ($details[0] -> active == "1") {_e("Проверена");} else {_e("Непроверена");} ?></div>
	<p class="submit">
	<input type="button" value="<?php _e('OK') ?>" name="ok" style="float:left;" onclick="hideview()"/>
	</p>
	<br/><br/>
	</p>
	</div>
	<?php
}
}
//**********************************************************************************************
//*******************************************EDIT********************************************
if ($_GET['action'] == "edit")
{
	$linkid = $_GET['id'];
	$details = $wpdb->get_results("SELECT * FROM `$eblex_links` WHERE `id`='$linkid' ORDER BY `zindex` DESC");
	$e_cat=$wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='' ORDER BY `zindex` DESC");
	
	if (isset($details[0] -> id))
	{
	
	$e_validated = 1;
	$e_l_title = $details[0] -> title;
	$e_l_description = $details[0] -> description;
	$e_l_nonreciprocal = $details[0] -> nonreciprocal;
	$e_l_url = $details[0] -> url;
	$e_l_rurl = $details[0] -> reciprocalurl;
	$e_l_email = $details[0] -> email;
	$e_l_priorityindex = $details[0] -> zindex;
	$e_l_acomment = $details[0] -> administratorcomment;
	$e_l_status = $details[0] -> status;
	$e_l_category = $details[0] -> category;
	$e_l_id = $details[0] -> id;
	
	?>
	<script language="JavaScript" type="text/javascript">
 	function hideedit()
	{
		document.getElementById('editlink').innerHTML = "";
	}
    </script>
	<div id="editlink">
	<h2><?php _e('Редактировать ссылку'); ?></h2>
	<p>
<form action="" method="post" name="form">
<br/>
<table class="optiontable"> 
<tr valign="top"> 
<th scope="row"><?php _e('Заголовок:') ?></th> 
<td><input name="e_title" type="text" id="e_title"<?php if ($e_validated != -1 && $e_validated != 0) {echo(" value=\"$e_l_title\"");} ?> size="40" /></td> 
</tr> 
<tr valign="top"> 
<th scope="row"><?php _e('URL:') ?></th> 
<td><input name="e_url" type="text" id="e_url" style="width: 95%"<?php if ($e_validated != -1 && $e_validated != 0) {echo(" value=\"$e_l_url\"");} else {echo(" value=\"http://\"");} ?> size="45" onfocus="document.getElementById('e_url').select()"/></td> 
</tr>
<tr valign="top">
  <th scope="row"><?php _e('Категория:') ?></th>
  <td><select name="e_category" size="6" id="e_category">
    <option value="0" style="font-weight:bold; font-size:18px;"<?php if ($e_l_category == "0" || $e_l_category == "") echo(" selected"); ?>>Root</option>
    <?php
	foreach ($e_cat as $e_row)
  	{
	if ($e_row->id != "0")
	{
	?>
	<option value="<?=$e_row->id;?>" style="font-size:14px;"<?php if ($e_l_category == $e_row->id) echo(" selected"); ?>>&nbsp;&nbsp;<?=$e_row->title;?></option>
	<?php
	$e_subcat=$wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='".$e_row->id."' ORDER BY `zindex` DESC");
	
		foreach ($e_subcat as $e_subrow)
  		{
		?>
		<option value="<?php if ($e_subrow->parent!="") {echo($e_subrow->id);} ?>" style="font-style:italic; font-size:12px;"<?php if ($e_l_category == $e_subrow->id) echo(" selected"); ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$e_subrow->title;?></option>
		<?php
		}
	  }
	}
	?>
    </select></td>
</tr> 
<tr valign="top"> 
<th scope="row"><?php _e('Описание:') ?></th> 
<td><textarea name="e_description" cols="40" rows="4" class="code" id="e_description"><?php if ($e_validated != -1 && $e_validated != 0) {echo("$e_l_description");} ?></textarea></td> 
</tr>
<tr valign="top">
  <th scope="row"><?php _e('E-mail:') ?></th>
  <td><input name="e_email" type="text" id="e_email"<?php if ($e_validated != -1 && $e_validated != 0) {echo(" value=\"$e_l_email\"");} ?> size="40" class="code" />
    <br />
    <?php _e('E-mail автора ссылки'); ?></td>
</tr>
<tr valign="top"> 
<th scope="row"><?php _e('Обратная ссылка, URL:') ?> </th> 
<td><input name="e_rurl" type="text" class="code" id="e_rurl"<?php if ($e_validated != -1 && $e_validated != 0) {echo(" value=\"$e_l_rurl\"");} else {echo(" value=\"http://\"");} ?> style="width: 95%" size="45" onfocus="document.getElementById('e_rurl').select()"/><br/>
  <label>
  <input name="e_nonreciprocal" type="checkbox" id="e_nonreciprocal" value="yes" <?php if ($e_l_nonreciprocal == "1") {echo(" проверена");} ?>/>
  Без обратной ссылки</label></td> 
</tr>
<tr valign="top"> 
<th scope="row"><?php _e('Комментарий администратора:') ?></th> 
<td> <label for="users_can_register">
  <textarea name="e_acomment" cols="40" rows="4" class="code" id="e_acomment"><?php if ($e_validated != -1 && $e_validated != 0) {echo("$e_l_acomment");} ?></textarea>
</label></td> 
</tr> 
<tr valign="top"> 
<th scope="row"><?php _e('Порядок сортировки:') ?></th> 
<td><label for="default_role">
  <input name="e_priorityindex" type="text" class="code" id="e_priorityindex"<?php if ($e_validated != -1 && $e_validated != 0) {echo(" value=\"$e_l_priorityindex\"");} else {echo(" value=\"0\"");} ?> size="5"/>
</label></td> 
</tr>
<tr valign="top">
  <th scope="row"><?php _e('Статус:') ?></th>
  <td><label>
    <input name="e_status" type="radio" value="1"<?php if ($e_l_status == "1" || $e_l_status == "") {echo(" checked=\"checked\"");} ?>/>
    Активная (видимая) </label>
	<label>
    <input name="e_status" type="radio" value="0"<?php if ($e_l_status == "0") {echo(" checked=\"checked\"");} ?>/>
    Невидимая</label>
	<input name="e_linkid" type="hidden" id="e_linkid" value="<?php echo($e_l_id); ?>" /></td>
</tr> 
</table>
<p class="submit">
<input name="Dismiss" type="button" id="Dismiss" style="float:left;" onclick="hideedit()" value="<?php _e('Отмена') ?>"/> 
<input type="reset" value="<?php _e('Сбросить данные') ?>" name="reset" style="float:left;"/>
<input type="submit" value="<?php _e('Редактировать &raquo;') ?>" name="submit" />
</p>
</form>
	<br/><br/>
	</div>
	<p>
	<?php
}
}
//**********************************************************************************************
?>

<?php
if ($_GET['s'] == "" && $catcount>0)
{
?>
<h2>
<?php
	if ($_GET['c'] != "")
	{
		_e('Подкатегории в "'.$l_titlecategory.'"');
	}
	else
	{
		_e('Категории');
	}
?>
</h2>

<?php
if ($_GET['c'] != "")
{
?>
<div class="catbox">
<a href="link-exchange-browse.php<?php if ($_GET['s'] !="" ) {echo("?c=".$_GET['c']);} ?>" class="catsublink"><?php _e("&laquo; Назад"); ?></a>
</div>
<?php
}
?>
<?php
foreach ($cat as $row)
{
if ($row -> id != '0')
{
  $l_linkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `category`='".$row->id."' AND `active`='1'");
  $l_subcat = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='".$row->id."'");
 
  foreach ($l_subcat as $subrow)
  {
  	$l_sublinkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `category`='".$subrow->id."' AND `active`='1'");
  	$l_linkcount += $l_sublinkcount;
  }
  
  if ($l_linkcount == 0)
  {
  	$l_linkcount = "Нет ссылок";
  }
  
?>
<div class="catbox">
<img src="../wp-content/plugins/linkexchange/images/folder.gif" alt="&gt;" width="16" height="15" />
<a href="link-exchange-browse.php<?php if ($_GET['c'] != "") {echo("?c=".$_GET['c']); echo("&s="); echo($row->nicename);} else {echo("?c=".$row->nicename);} ?>" class="catlink"><?php _e($row->title);?> (<?php echo("$l_linkcount"); ?>)</a><br/>
<?php
	$first=0;
	$l_parent=$row->id;
	$subcat=$wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='$l_parent' ORDER BY `zindex` DESC");
	foreach ($subcat as $subrow)
	{
	$l_sublinkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `category`='".$subrow->id."' AND `active`='1'");
	 	 if ($l_sublinkcount == 0)
 		 {
 	 		$l_sublinkcount = "Нет ссылок";
 		 }
  
	if ($first == 0) {$first = 1;} else {echo(", ");}
?>
<a href="link-exchange-browse.php<?php echo("?c=".$row->nicename); echo("&s=".$subrow->nicename); ?>" class="catsublink"><?php _e($subrow->title." ($l_sublinkcount)"); ?></a>
<?php
	}
	$first=0;
?>
</div>
<?php
}
}
$l_catcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_categories` WHERE `parent`=''");
	if ($l_catcount == '1')
	{
		_e('Нет категорий для отображения. <br/>');
	}
}
else
{
	if ($_GET['s'] != "" || $catcount == 0)
	{
	$additionforlink = "";
			if ($_GET['s'] != "")
			{
				$additionforlink .= "?c=".$_GET['c'];
			}
?>

	<h2><?php _e('Навигация'); ?></h2>
	<p>
	<div class="catbox">
	<a href="link-exchange-browse.php<?php echo($additionforlink); ?>" class="catsublink">&laquo; Назад</a>
	</div>
	</p>
	<br/>
<?php
	}
}
?>

<?php
$linkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `category`='$categoryid' AND `active`='1'"); //are there any links at all?

if ($linkcount > 0)
{
?>

<h2><?php _e('Ссылки внтури "'.$l_titlecategory.'"'); ?></h2>
<?php

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
	?>
	<div class="<?php echo($class); ?>">
	<a href="<?php echo($row->url); ?>" class="plink" target="_blank"><?php echo($row->title); if ($row->status == "0") {echo("(inactive)");} ?></a><br/><a href="link-exchange-browse.php<?php echo($dlink."&id=".$row->id."&action=details"); ?>">[Детали]</a> <a href="link-exchange-browse.php<?php echo($dlink."&id=".$row->id."&action=edit"); ?>">[Править]</a> <a href="link-exchange-browse.php<?php echo($dlink."&id=".$row->id."&action=delete"); ?>" onclick="return deletelink()">[Удалить]</a><br/>
	<span class="purl"><?php echo(str_replace("/","",str_replace("http://","",$row->url))); ?></span><br/>
	<?php echo(str_replace("\n","<br/>",$row->description)); ?>
	</div>
	<br/>
	<?php
}
?>
<br/>
<div align="right">
<?php
$realpage = $page+1;
_e("Страница <strong>$realpage</strong> из <strong>$number_of_pages</strong> &nbsp;");
$x = 0;
$threedots = 0;

//-------------------- Take care of the GET globals --------------------
if ($_GET['c'] != "")
{
	$addition1 = "c=".$_GET['c']."&";
}
else
{
	$addition1 = "";
}

if ($_GET['s'] != "")
{
	$addition2 = "s=".$_GET['s']."&";
}
else
{
	$addition2 = "";
}
//---------------------------------------------------------------------

for ($i=1;$i<=$number_of_pages;$i++)
{
	if ($i > 5 && $threedots == 0 && $number_of_pages>10)
	{
		echo("...&nbsp;");
		$threedots = 1;
	}
	
	if ($i == $page+1)
	{
		$pageboxstyle = "selected";
	}
	else
	{
		$pageboxstyle = "";
	}
	
	if ($i <= 5 || $i > $number_of_pages-5)
	{
	echo("<a href=\"?".$addition1.$addition2."p=$i"."\" class=\"pagebox$pageboxstyle\">$i</a>&nbsp;");
	}
	$x++;
}
?>
<a href="<?php echo("?".$addition1.$addition2."p="); ?>" class="pagebox" id="gotopage" onclick="return getpage()">Перейти к</a>
</div>
<?php
}
?>
<br/>
<h2><?php _e('Добавить ссылку в "'.$l_titlecategory.'"'); ?></h2>
<form action="" method="post" name="form" id="form">
  <br/>
  <table class="optiontable">
    <tr valign="top">
      <th scope="row"><?php _e('Заголовок:') ?></th>
      <td><input name="title" type="text" id="title"<?php if ($validated != -1 && $validated != 0) {echo(" value=\"$l_title\"");} ?> size="40" /></td>
    </tr>
    <tr valign="top">
      <th scope="row"><?php _e('URL:') ?></th>
      <td><input name="url" type="text" id="url" style="width: 95%"<?php if ($validated != -1 && $validated != 0) {echo(" value=\"$l_url\"");} else {echo(" value=\"http://\"");} ?> size="45" onfocus="document.getElementById('url').select()"/></td>
    </tr>
    <tr valign="top">
      <th scope="row"><?php _e('Категория:') ?></th>
      <td><?php echo($l_titlecategory); ?>
        <input name="category" type="hidden" id="category" value="<?php echo($ccatid); ?>" /></td>
    </tr>
    <tr valign="top">
      <th scope="row"><?php _e('Описание:') ?></th>
      <td><textarea name="description" cols="40" rows="4" class="code" id="description"><?php if ($validated != -1 && $validated != 0) {echo("$l_description");} ?>
</textarea></td>
    </tr>
    <tr valign="top">
      <th scope="row"><?php _e('E-mail:') ?></th>
      <td><input name="email" type="text" id="email"<?php if ($validated != -1 && $validated != 0) {echo(" value=\"$l_email\"");} ?> size="40" class="code" />
          <br />
          <?php _e('E-mail автора ссылки'); ?></td>
    </tr>
    <tr valign="top">
      <th scope="row"><?php _e('Обратная ссылка, URL:') ?>      </th>
      <td><input name="rurl" type="text" class="code" id="rurl"<?php if ($validated != -1 && $validated != 0) {echo(" value=\"$l_rurl\"");} else {echo(" value=\"http://\"");} ?> style="width: 95%" size="45" onfocus="document.getElementById('rurl').select()"/><br/>
  <label>
  <input name="nonreciprocal" type="checkbox" id="nonreciprocal" value="yes" <?php if ($e_l_nonreciprocal == "1") {echo("checked");} ?>/>
  Без обратной ссылки</label></td>
    </tr>
    <tr valign="top">
      <th scope="row"><?php _e('Комментарий администратора:') ?></th>
      <td><label for="users_can_register">
        <textarea name="acomment" cols="40" rows="4" class="code" id="acomment"><?php if ($validated != -1 && $validated != 0) {echo("$l_acomment");} ?>
</textarea>
      </label></td>
    </tr>
    <tr valign="top">
      <th scope="row"><?php _e('Порядок сортировки:') ?></th>
      <td><label for="default_role">
        <input name="priorityindex" type="text" class="code" id="priorityindex"<?php if ($validated != -1 && $validated != 0) {echo(" value=\"$l_priorityindex\"");} else {echo(" value=\"0\"");} ?> size="5"/>
      </label></td>
    </tr>
    <tr valign="top">
      <th scope="row"><?php _e('Статус:') ?></th>
      <td><label>
        <input name="status" type="radio" value="1"<?php if ($l_status == "1" || $l_status == "") {echo(" checked=\"checked\"");} ?>/>
        Активная (видимая) </label>
          <label>
          <input name="status" type="radio" value="0"<?php if ($l_status == "0") {echo(" checked=\"checked\"");} ?>/>
            Невидимая</label></td>
    </tr>
  </table>
  <p class="submit">
    <input type="reset" value="<?php _e('Очистить') ?>" name="reset" style="float:left;"/>
    <input type="submit" value="<?php _e('Отправить &raquo;') ?>" name="submit" />
  </p>
</form>
<br/>
<br/>
</div>
<?php
require('./admin-footer.php');
?>
