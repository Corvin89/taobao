<?php
require_once('admin.php');
$title = __('Каталог - Подкатегории');
$parent_file = 'link-exchange.php';
$today = current_time('mysql', 1);
require_once('admin-header.php');
// global $table_prefix, $wpdb;
$eblex_settings = $table_prefix . "eblex_settings";
$eblex_categories = $table_prefix . "eblex_categories";
$eblex_links = $table_prefix . "eblex_links";

$l_subcategory = $_GET['c'];
if ($l_subcategory == 'root') {
    header("Location:link-exchange-categories.php");
    exit();
} 
$l_id = $wpdb->get_var("SELECT `id` FROM `$eblex_categories` WHERE `nicename`='$l_subcategory'");
$l_parent = $l_id;
$l_count = $wpdb->get_var("SELECT count(*) FROM `$eblex_categories` WHERE `parent`='$l_id'");

if ($l_subcategory == "" || $l_id == "") {
    header("Location:link-exchange-categories.php");
    exit();
} 
$l_parentname = $wpdb->get_var("SELECT `title` FROM `$eblex_categories` WHERE `nicename`='$l_subcategory'");

function _mq($var)
{
    if (!get_magic_quotes_gpc()) {
        return addslashes($var);
    } else {
        return $var;
    } 
} 

function _rmq($var)
{
    if (get_magic_quotes_gpc()) {
        return stripslashes($var);
    } else {
        return $var;
    } 
} 

$tr = array(
   "Ґ"=>"G","Ё"=>"YO","Є"=>"E","Ї"=>"YI","І"=>"I",
   "і"=>"i","ґ"=>"g","ё"=>"yo","№"=>"#","є"=>"e",
   "ї"=>"yi","А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
   "Д"=>"D","Е"=>"E","Ж"=>"ZH","З"=>"Z","И"=>"I",
   "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
   "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
   "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
   "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"'","Ы"=>"YI","Ь"=>"",
   "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
   "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"zh",
   "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
   "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
   "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
   "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"'",
   "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
  );
function eblex_niceify($l_title)
{
	global $tr;
   	return strtr($l_title,$tr);
} 
// Add new category
$success = 0;
$cat = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='$l_id' ORDER BY `zindex` DESC");
if (isset($_POST['title']) && $_POST['title'] != "") {
    $l_title = $_POST['title'];
    $l_description = $_POST['description'];
    $l_keywords = $_POST['keywords'];
    $l_parent = $_POST['parent'];
    $l_visibility = $_POST['visibility'];
    $l_priorityindex = $_POST['priorityindex'];
    $l_nicename = eblex_niceify($l_title);

    $nduplicate = 0;
    foreach ($cat as $row) {
        if (strtolower($row->nicename) == strtolower($l_nicename)) {
            $nduplicate = 1;
        } 
    } 

    if ($nduplicate == 0) {
        $duplicate = 0;
        foreach ($cat as $row) {
            if (strtolower($row->title) == strtolower($l_title)) {
                $duplicate = 1;
            } 
        } 

        if ($duplicate == 0) {
            if ($l_visibility == "" || $l_visibility == "yes") {
                if ($l_priorityindex != "" && is_numeric($l_priorityindex)) {
                    $l_title = _mq($_POST['title']);
                    $l_description = _mq($_POST['description']);
                    $l_keywords = _mq($_POST['keywords']);
                    $l_parent = _mq($_POST['parent']);
                    $l_time = time();
                    $l_idx = md5(uniqid(rand(), true) . $l_title);

                    if ($l_visibility == "yes") {
                        $l_visibilityvalue = "1";
                    } else {
                        $l_visibilityvalue = "0";
                    } 

                    $validated = 1;
                    if ($l_parent != "") {
                        foreach ($cat as $row) {
                            if ($row->id != $l_parent) {
                                $validated = 1;
                            } 
                        } 
                    } 

                    if ($validated == 1) {
                        $wpdb->query("INSERT INTO `$eblex_categories` (`id`, `parent`, `title`, `description`, `keywords`, `nicename`, `time`, `visible`, `zindex`) VALUES ('$l_idx', '$l_parent', '$l_title', '$l_description', '$l_keywords', '$l_nicename', '$l_time', '$l_visibilityvalue', '$l_priorityindex');");
                        $success = 1;
                        $cat = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='$l_parent' ORDER BY `zindex` DESC");
                        $l_count = $wpdb->get_var("SELECT count(*) FROM `$eblex_categories` WHERE `parent`='$l_parent'");
                    } else {
                        $noticemsg = "Invalid parent!";
                    } 
                } else {
                    $noticemsg = "Invalid priority index!";
                } 
            } else {
                $noticemsg = "Invalid visibility value!";
            } 
        } else {
            $noticemsg = "Another subcategory with the same name already exists!";
        } 
    } else {
        $noticemsg = "Another subcategory with the same name already exists!";
    } 
} 
// *********************************EDIT********************************
$e_success = 0;
$e_c_id = $_GET['id'];
$e_c_check = $wpdb->get_var("SELECT `id` FROM `$eblex_categories` WHERE `id`='$e_c_id'");

if ($e_c_check != '') {
    $e_cat = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `id`!='$e_c_id' AND `id`!='$e_c_id' ORDER BY `zindex` DESC");
    if (isset($_POST['e_title']) && $_POST['e_title'] != "") {
        $e_c_title = $_POST['e_title'];
        $e_c_description = $_POST['e_description'];
        $e_c_keywords = $_POST['e_keywords'];
        $e_c_parent = $_POST['e_category'];
        $e_c_visibility = $_POST['e_visibility'];
        $e_c_priorityindex = $_POST['e_priorityindex'];
        $e_c_nicename = eblex_niceify($e_c_title);
        $e_c_id = $_GET['id'];

        if ($e_c_nicename != "") {
            $nduplicate = 0;
            foreach ($e_cat as $row) {
                if (strtolower($row->nicename) == strtolower($e_c_nicename)) {
                    $e_c_nduplicate = 1;
                } 
            } 

            if ($e_c_nduplicate == 0) {
                $e_c_duplicate = 0;
                foreach ($e_cat as $row) {
                    if (strtolower($row->title) == strtolower($e_c_title)) {
                        $e_c_duplicate = 1;
                    } 
                } 

                if ($e_c_duplicate == 0) {
                    if ($e_c_visibility == "" || $e_c_visibility == "yes") {
                        if ($e_c_priorityindex != "" && is_numeric($e_c_priorityindex)) {
                            $e_c_title = _mq($_POST['e_title']);
                            $e_c_description = _mq($_POST['e_description']);
                            $e_c_keywords = _mq($_POST['e_keywords']);
                            $e_c_parent = _mq($_POST['e_category']);
                            $e_c_time = time();
                            $e_c_id = $_GET['id'];

                            if ($e_c_visibility == "yes") {
                                $e_c_visibilityvalue = "1";
                            } else {
                                $e_c_visibilityvalue = "0";
                            } 
                            $wpdb->query("UPDATE `$eblex_categories` SET `parent`='$e_c_parent', `title`='$e_c_title', `description`='$e_c_description', `keywords`='$e_c_keywords', `nicename`='$e_c_nicename', `visible`='$e_c_visibilityvalue', `zindex`='$e_c_priorityindex' WHERE `id`='$e_c_id'");
                            $e_success = 1;
                        } else {
                            $noticemsg = "Invalid priority index!";
                        } 
                    } else {
                        $noticemsg = "Invalid visibility value!";
                    } 
                } else {
                    $noticemsg = "Another category with the same name already exists!";
                } 
            } else {
                $noticemsg = "Another category with the same name already exists!";
            } 
        } else {
            $noticemsg = "Too much non-url friendly characters exist inside your new subcategory name!";
        } 
    } 
} 
// ------------------------------------DELETE------------------------------------
if ($_GET['action'] == "delete" && $_POST['confirm'] == "yes") {
    $catid = $_GET['id'];
    $catidconfirmed = $wpdb->get_var("SELECT `id` FROM `$eblex_categories` WHERE `id`='$catid' LIMIT 1");

    if ($catidconfirmed != '') {
        $wpdb->query("DELETE FROM `$eblex_links` WHERE `category`='" . $catidconfirmed . "'");
        $wpdb->query("DELETE FROM `$eblex_categories` WHERE `id`='" . $catidconfirmed . "' LIMIT 1");
        $noticemsg = "Subcategory successfully deleted!";
    } 
} 
// ------------------------------------------------------------------------------
// -------------------------PAGING---------------------------
$categorycount = $wpdb->get_var("SELECT count(*) FROM `$eblex_categories` WHERE `parent`='$l_id' AND `id`!='0'");
$number_of_pages = ceil($categorycount / 10);
$page = $_GET['p'];

if (!is_numeric($page)) {
    $page = 1;
} 

if ($page > $number_of_pages && $number_of_pages != "0") {
    if ($_GET['action'] == "delete" && $_POST['confirm'] == "yes") {
        $page--;
        if ($page > $number_of_pages) {
            $noticemsg = "Invalid page number!";
        } 
    } else {
        $noticemsg = "Invalid page number!";
    } 
} 

$pg = $page;
$page--;
$limit1 = $page * 10;
$limit2 = 10;
$cat = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='$l_id' AND `id`!='0' ORDER BY `zindex` DESC LIMIT $limit1,$limit2");
$checkcount = count($cat);
// ************************************************************************

?>
<?php
if ($noticemsg != "")
{
?>
<div id="message1" class="updated fade"><p><?php _e($noticemsg); ?></p></div>
<?php 
}
if ($success == 1)
{
?>
<div id="message1" class="updated fade"><p><?php _e("Новая подкатегория успешно добавлена!"); ?></p></div>
<?php 
}
?>
<div class="wrap">
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
	page = prompt("<?php _e('Enter the page number you would like to jump to:'); ?>");
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
<h2>Навигация</h2>
	<p>
	<div class="catbox">
	<a href="link-exchange-categories.php<?php if ($_GET['rp'] != "1") {echo("?p=".$_GET['rp']);} ?>" class="catsublink">&laquo; Назад</a>
	</div>
	</p>
<br/>
<?php
//*******************************************VIEW********************************************
if ($_GET['action'] == "view")
{
	$catid = $_GET['id'];
	$details = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `id`='$catid' ORDER BY `zindex` DESC");
	$numberoflinks = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `category`='".$catid."' AND `active`='1'");
	$parentcategoryid = $wpdb->get_var("SELECT `parent` FROM `$eblex_categories` WHERE `id`='".$catid."'");
	$parentcategory = $wpdb->get_var("SELECT `title` FROM `$eblex_categories` WHERE `id`='".$parentcategoryid."'");
	
	if (isset($details[0] -> id))
	{
	?>
	<script language="JavaScript" type="text/javascript">
 	function hideview()
	{
		document.getElementById('viewcategory').innerHTML = "";
	}
    </script>
	<div id="viewcategory">
	<h2>Детали подкатегории</h2>
	<p>
	<div><strong>Название:</strong> <?php echo($details[0] -> title); ?></div>
	<div><strong>Описание:</strong> <?php echo($details[0] -> description); ?></div>
	<div><strong>Ключевые слова:</strong> <?php if ($details[0] -> keywords != "") {echo($details[0] -> keywords);} else {echo("N/A");} ?></div>
	<div><strong>Создана:</strong> <?php echo(date("H:i:s j.n.Y.",$details[0] -> time)); ?></div>
	<div><strong>Число ссылок:</strong> <?php echo($numberoflinks); ?> </div>
	<div><strong>Родитель:</strong> <?php echo($parentcategory); ?> </div>
	<div><strong>Порядок сортировки:</strong> <?php echo($details[0] -> zindex); ?></div>
	<div><strong>Статус:</strong> <?php if ($details[0] -> visible == "1") {echo("Открытая");} else {echo("Скрытая");} ?></div>
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
//**********************************************EDIT********************************************
if ($_GET['action'] == "edit" && $_POST['e_title'] == "")
{
	$catid = $_GET['id'];
	$details = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `id`='$catid' ORDER BY `zindex` DESC");
	
	if (isset($details[0] -> id))
	{
	
	$e_validated = 1;
	$e_c_title = $details[0] -> title;
	$e_c_description = $details[0] -> description;
	$e_c_parent = $details[0] -> parent;
	$e_c_keywords = $details[0] -> keywords;
	$e_c_priorityindex = $details[0] -> zindex;
	$e_c_visibility = $details[0] -> visibility;
	$e_c_id = $details[0] -> id;
	echo("<!-- $e_c_parent -->");
	?>
	<script language="JavaScript" type="text/javascript">
 	function hideedit()
	{
		document.getElementById('editcat').innerHTML = "";
	}
    </script>
	<div id="editcat">
	<h2>Редактировать категорию</h2>
	<p>
<form action="" method="post" name="form">
<br/>
<table class="optiontable"> 
<tr valign="top"> 
<th scope="row"><?php _e('Название:') ?></th> 
<td><input name="e_title" type="text" id="e_title"<?php if ($e_validated != -1 && $e_validated != 0) {echo(" value=\"$e_c_title\"");} ?> size="40" /></td> 
</tr><tr valign="top"> 
<th scope="row"><?php _e('Описание:') ?></th> 
<td><textarea name="e_description" cols="40" rows="4" class="code" id="e_description"><?php if ($e_validated != -1 && $e_validated != 0) {echo("$e_c_description");} ?></textarea> <br />
        <?php _e('Опишите в двух словах, какие ссылки содержаться в этой категории.') ?></td> 
</tr>
<tr valign="top">
  <th scope="row"><?php _e('Родительская категория:') ?></th>
  <td><select name="e_category" size="6" id="e_category">
    <option value="0" style="font-weight:bold; font-size:18px;"<?php if ($e_c_parent == "0" || $e_c_parent == "") echo(" selected"); ?>>Root</option>
    <?php
	$categorylist=$wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='' ORDER BY `zindex` DESC");
	foreach ($categorylist as $row)
  	{
	if ($row->id != "0")
	{
	?>
	<option value="<?=$row->id;?>" style="font-size:14px;"<?php if ($e_c_parent == $row->id) echo(" selected"); ?>>&nbsp;&nbsp;<?=$row->title;?></option>
	<?php
	  }
	}
	?>
    </select></td>
</tr> 
<tr valign="top">
  <th scope="row"><?php _e('Видимость:') ?></th>
  <td><input name="e_visibility" type="checkbox" id="e_visibility" value="yes"<?php if ($noticemsg!="" && $c_visibility=="yes") {echo(" checked");} else {if ($noticemsg=="") {echo(" checked");}} ?>/>
      <br />
        <?php _e('Если не выбрана, подкатегория будет скрыта.') ?></td>
</tr> 
<tr valign="top"> 
<th scope="row"><?php _e('Порядок сортировки:') ?></th> 
<td><label for="default_role">
  <input name="e_priorityindex" type="text" class="code" id="e_priorityindex"<?php if ($e_validated != -1 && $e_validated != 0) {echo(" value=\"$e_c_priorityindex\"");} else {echo(" value=\"0\"");} ?> size="5"/>
</label></td> 
</tr>
</table>
<p class="submit">
<input name="Dismiss" type="button" id="Dismiss" style="float:left;" onclick="hideedit()" value="<?php _e('Назад') ?>"/> 
<input type="reset" value="<?php _e('Очистить') ?>" name="reset" style="float:left;"/>
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
//*******************************************DELETE*********************************************
if ($_GET['action'] == "delete" && $_POST['confirm'] != "yes")
{
	$catid = $_GET['id'];
	$details = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `id`='$catid'");
	
	if (isset($details[0] -> id))
	{
	$numberoflinks = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `category`='".$catid."' AND `active`='1'");
	?>
	<script language="JavaScript" type="text/javascript">
 	function hidedelete()
	{
		document.getElementById('deletecategory').innerHTML = "";
	}
    </script>
	<div id="deletecategory">
	<h2>Удалить подкатегорию</h2>
	<p>
	<div><strong>Вы уверены, что хотите удалить "<?php echo($details[0] -> title); ?>"?</strong></div>
	
	<?php
	if ($numberoflinks != 0)
	{
	?>
	<div>Вместе с ней Вы удалите еще и <strong><?php echo($numberoflinks); ?></strong> ссылок!</div>
	<?php
	}
	?>
	<form action="" method="post">
    <p class="submit">
	<input name="confirm" type="hidden" value="yes" />
	<input type="submit" value="<?php _e('Удалить') ?>" name="ok" style="float:right;"/>
	<input type="button" value="<?php _e('НЕТ!') ?>" name="ok" style="float:left;" onclick="hidedelete()"/>
	</p>
	</form>
	<br/><br/>
	</p>
	</div>
	<?php
}
}
//**********************************************************************************************
?>

<?php
if ($checkcount != "0") { 
?>
<h2><?php _e('Подкатегории в "'.$l_parentname.'"'); ?></h2>

<table id="the-list-x" width="100%" cellpadding="3" cellspacing="3">
  <tr>
    <td width="58%"><div align="center"><strong>Название</strong></div></td>
    <td width="9%"><div align="center"><strong>Видимость</strong></div></td>
    <td width="8%"><div align="center"><strong>Порядок сортировки</strong></div></td>
    <td width="8%">&nbsp;</td>
    <td width="8%">&nbsp;</td>
    <td width="9%">&nbsp;</td>
  </tr>
  <?php
  foreach ($cat as $row)
  {
  $class = ('alternate' == $class) ? '' : 'alternate';
  $l_linkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `category`='".$row->id."' AND `active`='1'");
  ?>
  <tr class="<?=$class;?>">
    <td><?=$row->title;?> (<?php if ($l_linkcount == 0) {echo("No links");} else {echo($l_linkcount);} ?>)</td>
    <td><div align="center"><?php if ($row->visible==1) {_e('yes');} else {_e('no');} ?></div></td>
    <td><div align="center">
      <?=$row->zindex;?>
    </div></td>
    <td><div align="center"><a href="link-exchange-subcategories.php?c=<?=$_GET['c'];?>&id=<?=$row->id;?><?php echo("&p=".$pg."&rp=".$_GET['rp']); ?>&action=view" class="edit"> <?php _e("Просмотр"); ?> </a></div></td>
    <td><div align="center"><a href="link-exchange-subcategories.php?c=<?=$_GET['c'];?>&id=<?=$row->id;?><?php echo("&p=".$pg."&rp=".$_GET['rp']); ?>&action=edit" class="edit"> <?php _e("Правка"); ?> </a></div></td>
    <td><div align="center"><a href="link-exchange-subcategories.php?c=<?=$_GET['c'];?>&id=<?=$row->id;?><?php echo("&p=".$pg."&rp=".$_GET['rp']); ?>&action=delete" class="delete"> <?php _e("Удалить"); ?> </a></div></td>
  </tr>
  <?php
  }
  ?>
</table>
<br/>
<div align="right">
<?php
$realpage = $page+1;
_e("Страница <strong>$realpage</strong> из <strong>$number_of_pages</strong> &nbsp;");
$addon = "?c=".$_GET['c'];
$x = 0;
$threedots = 0;
if ($_GET['rp'] == "")
{
	$rp = "1";
}
else
{
	$rp = $_GET['rp'];
}
$returnaddon = "&rp=".$rp;

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
	echo("<a href=\"$addon"."&p=$i$returnaddon"."\" class=\"pagebox$pageboxstyle\">$i</a>&nbsp;");
	}
	$x++;
}
?>
<a href="<?php echo($addon."$returnaddon&p="); ?>" class="pagebox" id="gotopage" onclick="return getpage()">Go to page</a>
</div>
<?php } ?>
<form action="" method="post" name="categoryadd">
<p>  <br/>
  <h2><?php _e('Создать новую подкатегорию'); if ($l_count == "0") {_e(" in \"$l_parentname\"");} ?></h2>&nbsp;</p>
<table class="optiontable">
  <tr valign="top">
    <th scope="row"><?php _e('Название:') ?></th>
    <td><input name="title" type="text" id="title" size="40" value="<?php if ($noticemsg!="") {echo($l_title);} ?>"/></td>
  </tr>
  <tr valign="top">
    <th scope="row"><?php _e('Описание:') ?></th>
    <td><textarea name="description" cols="42" rows="3" id="description"><?php if ($noticemsg!="") {echo($l_description);} ?></textarea>
      <br />
        <?php _e('Опишите в двух словах, какие ссылки содержаться в этой категории.') ?></td>
  </tr>
  <tr valign="top">
    <th scope="row"><?php _e('Родительская категория:') ?></th>
    <td>
	<?php echo("$l_parentname"); ?>
	<input name="parent" type="hidden" id="parent" value="<?php echo($l_parent); ?>" /></td>
  </tr>
  <tr valign="top">
    <th scope="row"><?php _e('Видимость:') ?>    </th>
    <td><input name="visibility" type="checkbox" id="visibility" value="yes"<?php if ($noticemsg!="" && $l_visibility=="yes") {echo(" checked");} else {if ($noticemsg=="") {echo(" checked");}} ?>/>
      <br />
        <?php _e('Если не выбрана, подкатегория будет скрыта.') ?></td>
  </tr>
  <tr valign="top">
    <th scope="row"><?php _e('Порядок сортировки:') ?></th>
    <td><label for="users_can_register">
      <input name="priorityindex" type="text" class="code" id="priorityindex" size="5" value="<?php if ($noticemsg!="") {echo($l_priorityindex);} else {echo("0");} ?>"/>
    </label></td>
  </tr>
</table>
<p class="submit">
<input type="submit" value="<?php _e('Создать подкатгорию &raquo;') ?>" name="submit" />
</p>
</form>
<br/>
<br/>
</div>
<?php
require('./admin-footer.php');
?>
