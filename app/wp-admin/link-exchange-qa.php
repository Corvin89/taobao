<?php
require_once('admin.php');
$title = __('Каталог - Добавить');
$parent_file = 'link-exchange.php';
$today = current_time('mysql', 1);
require_once('admin-header.php');

$eblex_settings = $table_prefix . "eblex_settings";
$eblex_categories = $table_prefix . "eblex_categories";
$eblex_links = $table_prefix . "eblex_links";

$cat = $wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='' ORDER BY `zindex` DESC");

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
    $l_title = $_POST['title'];
    $l_description = $_POST['description'];
    if ($_POST['nonreciprocal'] == "yes") {
        $l_nonreciprocal = "1";
    } else {
        $l_nonreciprocal = "0";
    } 
    $l_url = $_POST['url'];
    $l_rurl = $_POST['rurl'];
    $l_email = $_POST['email'];
    $l_priorityindex = $_POST['priorityindex'];
    $l_acomment = $_POST['acomment'];
    $l_status = $_POST['status'];
    $l_category = $_POST['category'];
    $l_id = md5(uniqid(rand(), true) . $l_title);
    $l_time = time();

    if ($l_url != "" && $l_url != "http://") {
        if ($l_priorityindex != "" && is_numeric($l_priorityindex)) {
            if ($l_category != "") {
                foreach ($cat as $row) {
                    if ($row->id != $l_parent) {
                        $validated = 1;
                    } 
                } 

                if ($validated == 1) {
                    if ($l_email != "" && check_email($l_email) == 1) {
                        if ($l_status == "0" || $l_status == "1") {
                            $wpdb->query("INSERT INTO `$eblex_links` (`title`, `active`, `nonreciprocal`, `url`, `category`, `description`, `email`, `reciprocalurl`, `status`, `time`, `administratorcomment`, `zindex`, `id`) VALUES ('$l_title', '1', '$l_nonreciprocal', '$l_url', '$l_category', '$l_description', '$l_email', '$l_rurl', '$l_status', '$l_time', '$l_acomment', '$l_priorityindex', '$l_id');");
                            $noticemsg = "Ссылка добавлена!";
                            $validated = 2;
                        } else {
                            $noticemsg = "Неправильный статус!";
                        } 
                    } else {
                        $noticemsg = "Неправильный e-mail!";
                    } 
                } else {
                    $noticemsg = "Несуществующая категория!";
                } 
            } else {
                $noticemsg = "Выберите категорию!";
            } 
        } else {
            $noticemsg = "Порядок сортировки некорректный!";
        } 
    } else {
        $noticemsg = "URL неверный!";
    } 
} 

?>
<?php
if ($noticemsg!="")
{
?>
<div id="message1" class="updated fade"><p><?php _e($noticemsg); ?></p></div>
<?php 
}
?>
<div class="wrap">

<h2><?php _e('Добавить ссылку'); ?></h2>

<form action="" method="post" name="form">
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
  <td><select name="category" size="6" id="category">
    <option value="0" style="font-weight:bold; font-size:18px;"<?php if ($l_category == "0" || $l_category == "") echo(" selected"); ?>>Root</option>
    <?php
	foreach ($cat as $row)
  	{
	if ($row->id != "0")
	{
	?>
	<option value="<?=$row->id;?>" style="font-size:14px;"<?php if ($l_category == $row->id) echo(" selected"); ?>>&nbsp;&nbsp;<?=$row->title;?></option>
	<?php
	$subcat=$wpdb->get_results("SELECT * FROM `$eblex_categories` WHERE `parent`='".$row->id."' ORDER BY `zindex` DESC");
	
		foreach ($subcat as $subrow)
  		{
		?>
		<option value="<?php if ($subrow->parent!="") {echo($subrow->id);} ?>" style="font-style:italic; font-size:12px;"<?php if ($l_category == $subrow->id) echo(" selected"); ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$subrow->title;?></option>
		<?php
		}
	  }
	}
	?>
    </select></td>
</tr> 
<tr valign="top"> 
<th scope="row"><?php _e('Описание:') ?></th> 
<td><textarea name="description" cols="40" rows="4" class="code" id="description"><?php if ($validated != -1 && $validated != 0) {echo("$l_description");} ?></textarea></td> 
</tr> 
<tr valign="top">
<th scope="row"><?php _e('E-mail:') ?></th>
<td><input name="email" type="text" id="email"<?php if ($validated != -1 && $validated != 0) {echo(" value=\"$l_email\"");} ?> size="40" class="code" />
<br />
<?php _e('E-mail автора ссылки'); ?></td>
</tr>
<tr valign="top"> 
<th scope="row"><?php _e('Обратная ссылка, URL:') ?> </th> 
<td><input name="rurl" type="text" class="code" id="rurl"<?php if ($validated != -1 && $validated != 0) {echo(" value=\"$l_rurl\"");} else {echo(" value=\"http://\"");} ?> style="width: 95%" size="45" onfocus="document.getElementById('rurl').select()"/><br/>
  <label>
  <input name="nonreciprocal" type="checkbox" id="nonreciprocal" value="yes" />
  Без обратной ссылки</label></td> 
</tr>
<tr valign="top"> 
<th scope="row"><?php _e('Комментарий администратора:') ?></th> 
<td> <label for="users_can_register">
  <textarea name="acomment" cols="40" rows="4" class="code" id="acomment"><?php if ($validated != -1 && $validated != 0) {echo("$l_acomment");} ?></textarea>
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
    Активная </label>
	<label>
    <input name="status" type="radio" value="0"<?php if ($l_status == "0") {echo(" checked=\"checked\"");} ?>/>
    Скрытая</label></td>
</tr> 
</table>
<p class="submit">
<input type="reset" value="<?php _e('Очистить') ?>" name="reset" style="float:left;"/>
<input type="submit" value="<?php _e('Добавить ссылку &raquo;') ?>" name="submit" />
</p>
</form>
<br/>
</div>
<?php
require('./admin-footer.php');
?>
