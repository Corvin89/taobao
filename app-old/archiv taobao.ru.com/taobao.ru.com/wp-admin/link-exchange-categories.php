<?php
require_once('admin.php');
$title = __('Каталог - Категории');
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
// *******************************************DELETE********************************************
if ($_GET['action'] == "delete") {
    $id = $_GET['id'];
    $linkid = $_GET['id'];
    $details = $wpdb->get_results("SELECT * FROM `$eblex_links` WHERE `id`='$linkid' ORDER BY `zindex` DESC");

    if (isset($details[0]->id)) {
        $wpdb->query("DELETE FROM `$eblex_links` WHERE `id`='$linkid'");
        $noticemsg = "Ссылка удалена!";
    } 
} 
// **********************************************************************************************
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
$cat = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='' ORDER BY `zindex` DESC");
if (isset($_POST['title']) && $_POST['title'] != "") {
    $l_title = eblex_fixinput($_POST['title']);
    $l_description = eblex_fixinput($_POST['description']);
    $l_keywords = eblex_fixinput($_POST['keywords']);
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
                    $l_title = eblex_fixinput(_mq($_POST['title']));
                    $l_description = eblex_fixinput(_mq($_POST['description']));
                    $l_keywords = eblex_fixinput(_mq($_POST['keywords']));
                    $l_parent = eblex_fixinput(_mq($_POST['parent']));
                    $l_time = time();
                    $l_id = md5(uniqid(rand(), true) . $l_title);

                    if ($l_visibility == "yes") {
                        $l_visibilityvalue = "1";
                    } else {
                        $l_visibilityvalue = "0";
                    } 

                    $wpdb->query("INSERT INTO `$eblex_categories` (`id`, `parent`, `title`, `description`, `keywords`, `nicename`, `time`, `visible`, `zindex`) VALUES ('$l_id', '$l_parent', '$l_title', '$l_description', '$l_keywords', '$l_nicename', '$l_time', '$l_visibilityvalue', '$l_priorityindex');");
                    $success = 1;
                } else {
                    $noticemsg = "Неправильный порядок сортировки!";
                } 
            } else {
                $noticemsg = "Неправильный статус!";
            } 
        } else {
            $noticemsg = "Категория с таким именем уже существует!";
        } 
    } else {
        $noticemsg = "Категория с таким именем уже существует!";
    } 
} 
// *********************************EDIT********************************
$e_success = 0;
$e_c_id = $_GET['id'];
$e_c_check = $wpdb->get_var("SELECT `id` FROM `$eblex_categories` WHERE `id`='$e_c_id'");

if ($e_c_check != '') {
    $e_cat = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='' AND `id`!='$e_c_id' ORDER BY `zindex` DESC");
    if (isset($_POST['e_title']) && $_POST['e_title'] != "" && $e_c_check != "") {
        $e_c_title = $_POST['e_title'];
        $e_c_description = $_POST['e_description'];
        $e_c_keywords = $_POST['e_keywords'];
        $e_c_parent = $_POST['e_parent'];
        $e_c_visibility = $_POST['e_visibility'];
        $e_c_priorityindex = $_POST['e_priorityindex'];
        $e_c_nicename = eblex_niceify($e_c_title);

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
                        $e_c_parent = _mq($_POST['e_parent']);
                        $e_c_time = time();

                        if ($e_c_visibility == "yes") {
                            $e_c_visibilityvalue = "1";
                        } else {
                            $e_c_visibilityvalue = "0";
                        } 

                        $wpdb->query("UPDATE `$eblex_categories` SET `parent`='$e_c_parent', `title`='$e_c_title', `description`='$e_c_description', `keywords`='$e_c_keywords', `nicename`='$e_c_nicename', `visible`='$e_c_visibilityvalue', `zindex`='$e_c_priorityindex' WHERE `id`='$e_c_id'");
                        $e_success = 1;
                    } else {
                    $noticemsg = "Неправильный порядок сортировки!";
                } 
            } else {
                $noticemsg = "Неправильный статус!";
            } 
        } else {
            $noticemsg = "Категория с таким именем уже существует!";
        } 
    } else {
        $noticemsg = "Категория с таким именем уже существует!";
        } 
    } 
} 
// ------------------------------------DELETE------------------------------------
if ($_GET['action'] == "delete" && $_POST['confirm'] == "yes") {
    $catid = $_GET['id'];
    $catidconfirmed = $wpdb->get_var("SELECT `id` FROM `$eblex_categories` WHERE `id`='$catid' LIMIT 1");

    if ($catidconfirmed != '') {
        $wpdb->query("DELETE FROM `$eblex_links` WHERE `category`='" . $catidconfirmed . "'");
        $wpdb->query("DELETE FROM `$eblex_categories` WHERE `parent`='" . $catidconfirmed . "'");
        $wpdb->query("DELETE FROM `$eblex_categories` WHERE `id`='" . $catidconfirmed . "' LIMIT 1");
        $noticemsg = "Категория успешно удалена!";
    } 
} 
// ------------------------------------------------------------------------------
// -------------------------PAGING---------------------------
$categorycount = $wpdb->get_var("SELECT count(*) FROM `$eblex_categories` WHERE `parent`='' AND `id`!='0'");
$number_of_pages = ceil($categorycount / 10);
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

if (!is_numeric($page)) {
    $page = 1;
} 

$page--;
$limit1 = $page * 10;
$limit2 = 10;
$cat = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='' AND `id`!='0' ORDER BY `zindex` DESC LIMIT $limit1,$limit2");
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
<div id="message1" class="updated fade"><p><?php _e("Новая катагория успешно добавлена!"); ?></p></div>
<?php 
}
if ($e_success == 1)
{
?>
<div id="message1" class="updated fade"><p><?php _e("Категория успешно обновлена!"); ?></p></div>
<?php 
}
?>
<div class="wrap">

<?php
//*******************************************VIEW********************************************
if ($_GET['action'] == "view")
{
	$catid = $_GET['id'];
	$details = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `id`='$catid' ORDER BY `zindex` DESC");
	$numberoflinks = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `category`='".$catid."' AND `active`='1'");
	$numberofsubcategories = $wpdb->get_var("SELECT count(*) FROM `$eblex_categories` WHERE `parent`='".$catid."'");
	
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
	<h2><?php _e('Детали категории'); ?></h2>
	<p>
	<div><strong>Название:</strong> <?php echo($details[0] -> title); ?></div>
	<div><strong>Описание:</strong> <?php echo($details[0] -> description); ?></div>
	<div><strong>Ключевые слова:</strong> <?php if ($details[0] -> keywords != "") {echo($details[0] -> keywords);} else {echo("N/A");} ?></div>
	<div><strong>Добавлена:</strong> <?php echo(date("H:i:s j.n.Y.",$details[0] -> time)); ?></div>
	<div><strong>Число ссылок:</strong> <?php echo($numberoflinks); ?> </div>
	<div><strong>Количество подкатегорий:</strong> <?php echo($numberofsubcategories); ?> </div>
	<div><strong>Порядок сортировки:</strong> <?php echo($details[0] -> zindex); ?></div>
	<div><strong>Статус:</strong> <?php if ($details[0] -> visible == "1") {echo("Открытая");} else {echo("Невидимая");} ?></div>
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
if ($_GET['action'] == "edit" && $e_success != 1)
{
	$catid = $_GET['id'];
	$details = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `id`='$catid' ORDER BY `zindex` DESC");
	//$e_cat=$wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='' ORDER BY `zindex` DESC");
	
	if (isset($details[0] -> id))
	{
	
	$e_validated = 1;
	$e_c_title = $details[0] -> title;
	$e_c_description = $details[0] -> description;
	$e_c_parent = $details[0] -> parent;
	$e_c_keywords = $details[0] -> keywords;
	$e_c_priorityindex = $details[0] -> zindex;
	$e_c_visibility = $details[0] -> visible;
	$e_c_id = $details[0] -> id;
	
	?>
	<script language="JavaScript" type="text/javascript">
 	function hideedit()
	{
		document.getElementById('editcat').innerHTML = "";
	}
    </script>
	<div id="editcat">
	<h2><?php _e('Правит категорию'); ?></h2>
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
        <?php _e('Расскажите в двух словах какие ссылки размещены в этой категории.') ?></td> 
</tr>
<tr valign="top">
  <th scope="row"><?php _e('Ключевые слова:') ?></th>
  <td><input name="e_keywords" type="text" id="e_keywords"<?php if ($e_validated != -1 && $e_validated != 0) {echo(" value=\"$e_l_keywords\"");} ?> size="40" class="code" />
      <br/>
    <?php _e("Введите ключевые слова, через запятую. Они будут отбражаться в мета-теге &quot;keywords&quot;."); ?></td>
</tr>
<tr valign="top">
  <th scope="row"><?php _e('Видимость:') ?></th>
  <td><input name="e_visibility" type="checkbox" id="e_visibility" value="yes"<?php if ($e_c_visibility=="1") {echo(" checked");} ?>/>
      <br />
        <?php _e('Если не выбрано, то категория будет закрыта от просмотра.') ?></td>
</tr> 
<tr valign="top"> 
<th scope="row"><?php _e('Порядок сортировки:') ?></th> 
<td><label for="default_role">
  <input name="e_priorityindex" type="text" class="code" id="e_priorityindex"<?php if ($e_validated != -1 && $e_validated != 0) {echo(" value=\"$e_c_priorityindex\"");} else {echo(" value=\"0\"");} ?> size="5"/>
</label></td> 
</tr>
</table>
<p class="submit">
<input name="Dismiss" type="button" id="Dismiss" style="float:left;" onclick="hideedit()" value="<?php _e('Dismiss') ?>"/> 
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
	$numberofsubcategories = $wpdb->get_var("SELECT count(*) FROM `$eblex_categories` WHERE `parent`='".$catid."'");
	
	$deletesubcategories = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='".$catid."'");
	
	foreach ($deletesubcategories as $row)
	{
		$numberoflinks += $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `category`='".$row->id."' AND `active`='1'");
	}
	
	?>
	<script language="JavaScript" type="text/javascript">
 	function hidedelete()
	{
		document.getElementById('deletecategory').innerHTML = "";
	}
    </script>
	<div id="deletecategory">
	<h2><?php _e('Удаление категории'); ?></h2>
	<p>
	<div><strong>Вы уверены, что хотите удалить "<?php echo($details[0] -> title); ?>"?</strong></div>
	
	<?php
	if ($numberofsubcategories != 0 && $numberoflinks != 0)
	{
	?>
	<div>Вместе с ней Вы удалите <strong><?php echo($numberofsubcategories); ?></strong> подкатегорий и <strong><?php echo($numberoflinks); ?></strong> ссылок!</div>
	<?php
	}
	?>
	
	<?php
	if ($numberofsubcategories == 0 && $numberoflinks != 0)
	{
	?>
	<div>Вместе с ней Вы удалите <strong><?php echo($numberoflinks); ?></strong> ссылок!</div>
	<?php
	}
	?>
	
	<?php
	if ($numberofsubcategories != 0 && $numberoflinks == 0)
	{
	?>
	<div>Вместе с ней Вы удалите <strong><?php echo($numberofsubcategories); ?></strong> подкатегорий</div>
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
if ($checkcount != 0)
{
?>
<h2><?php _e('Категории'); ?></h2>
<style type="text/css">
<!--
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
<table id="the-list-x" width="100%" cellpadding="3" cellspacing="3">
  <tr>
    <td width="47%"><div align="center"><strong>Имя категории</strong></div></td>
    <td width="11%"><div align="center"><strong>Подкатегории</strong></div></td>
    <td width="9%"><div align="center"><strong>Видимость</strong></div></td>
    <td width="8%"><div align="center"><strong>Порядок сортировки</strong></div></td>
    <td width="8%">&nbsp;</td>
    <td width="8%">&nbsp;</td>
    <td width="9%">&nbsp;</td>
  </tr>
  <?php
foreach ($cat as $row)
{
  if ($row -> id != '0')
  {
  $class = ('alternate' == $class) ? '' : 'alternate';
  $l_count = $wpdb->get_var("SELECT count(*) FROM `$eblex_categories` WHERE `parent`='".$row->id."'");
  $l_linkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `category`='".$row->id."' AND `active`='1'");
  $l_subcat = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='".$row->id."'");
 
  foreach ($l_subcat as $subrow)
  {
  $l_sublinkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `category`='".$subrow->id."' AND `active`='1'");
  $l_linkcount += $l_sublinkcount;
  }
  ?>
  <tr class="<?=$class;?>">
    <td><a href="link-exchange-subcategories.php?rp=<?php if ($_GET['p'] == "") {echo("1");} else {echo($_GET['p']);} ?>&c=<?=$row->nicename;?>"><?=$row->title;?> <?php if ($l_linkcount == "0") {echo("(No links)");} else {echo("(".$l_linkcount.")");} ?></a> </td>
    <td><div align="center">
      <?php if ($l_count == "0") {_e('None');} else {_e($l_count);} ?>
    </div></td>
    <td><div align="center"><?php if ($row->visible==1) {_e('yes');} else {_e('no');} ?></div></td>
    <td><div align="center">
      <?=$row->zindex;?>
    </div></td>
    <td><div align="center"><a href="link-exchange-categories.php?id=<?=$row->id;?>&action=view&p=<?=$page+1;?>" class="edit"> <?php _e("Просмотр"); ?> </a></div></td>
    <td><div align="center"><a href="link-exchange-categories.php?id=<?=$row->id;?>&action=edit&p=<?=$page+1;?>" class="edit"> <?php _e("Правка"); ?> </a></div></td>
    <td><div align="center"><a href="link-exchange-categories.php?id=<?=$row->id;?>&action=delete&p=<?=$page+1;?>" class="delete"> <?php _e("Удалить"); ?> </a></div></td>
  </tr>
  <?php
  }
}
  ?>
</table>
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
		$pageboxstyle = "selected";
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
<a href="<?php echo("?p="); ?>" class="pagebox" id="gotopage" onclick="return getpage()">Go to page</a>
</div>
<?php
}
?>
<form action="" method="post" name="categoryadd">
<p>  <br/>
  <h2><?php _e('Создать новую категорию'); ?></h2>&nbsp;</p>
<table class="optiontable">
  <tr valign="top">
    <th scope="row"><?php _e('Название:') ?></th>
    <td><input name="title" type="text" id="title" size="40" value="<?php if ($noticemsg!="") {echo($l_title);} ?>"/></td>
  </tr>
  <tr valign="top">
    <th scope="row"><?php _e('Описание:') ?></th>
    <td><textarea name="description" cols="42" rows="3" id="description"><?php if ($noticemsg!="") {echo($l_description);} ?></textarea>
      <br />
        <?php _e('Расскажите в двух словах какие ссылки размещены в этой категории') ?></td>
  </tr>
  <tr valign="top">
    <th scope="row"><?php _e('Keywords:') ?></th>
    <td><input name="keywords" type="text" id="keywords"<?php if ($e_validated != -1 && $e_validated != 0) {echo(" value=\"$e_keywords\"");} ?> size="40" class="code" />
        <br/>
        <?php _e("Введите ключевые слова, через запятую. Они будут отбражаться в мета-теге &quot;keywords&quot;."); ?></td>
  </tr>
  <tr valign="top">
    <th scope="row"><?php _e('Видимость:') ?>    </th>
    <td><input name="visibility" type="checkbox" id="visibility" value="yes"<?php if ($noticemsg!="" && $l_visibility=="yes") {echo(" checked");} else {if ($noticemsg=="") {echo(" checked");}} ?>/>
      <br />
        <?php _e('Если не выбрано, то категория будет закрыта от просмотра.') ?></td>
  </tr>
  <tr valign="top">
    <th scope="row"><?php _e('Порядок сортировки:') ?></th>
    <td><label for="users_can_register">
      <input name="priorityindex" type="text" class="code" id="priorityindex" size="5" value="<?php if ($noticemsg!="") {echo($l_priorityindex);} else {echo("0");} ?>"/>
    </label></td>
  </tr>
</table>
<p class="submit">
<input type="submit" value="<?php _e('Создать категорию &raquo;') ?>" name="submit" />
</p>
</form>
<br/>
<br/>
</div>
<?php
require('./admin-footer.php');
?>
