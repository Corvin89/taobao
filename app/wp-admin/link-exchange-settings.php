<?php
require_once('admin.php');
$title = __('Каталог - Настройки');
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
// -------------------------- UPDATE SETTINGS ----------------------------
$sp_linksperpage = $_POST['lpp'];
$sp_maxdescsize = $_POST['maxdescsize'];
if (!is_numeric($sp_linksperpage) || $sp_linksperpage < 2 || $sp_linksperpage > 3000) {
    if ($sp_linksperpage != "") {
        $noticemsg = "Неправильный номер страницы!";
    } 
} else {
    if (!is_numeric($sp_maxdescsize) || $sp_maxdescsize < 0 || $sp_maxdescsize > 100000) {
        $noticemsg = "Неправильное количество символов в описании!";
    } else {
        if ($_POST['seo'] == "yes") {
            $sp_seofriendly = "1";
        } else {
            $sp_seofriendly = "0";
        } 
        if ($_POST['linkdescription'] == "yes") {
            $sp_description = "1";
        } else {
            $sp_description = "0";
        } 
        if ($_POST['linkurl'] == "yes") {
            $sp_url = "1";
        } else {
            $sp_url = "0";
        } 
        if ($_POST['linksapproval'] == "yes") {
            $sp_approval = "1";
        } else {
            $sp_approval = "0";
        } 
        if ($_POST['nonreciprocal'] == "yes") {
            $sp_nonreciprocal = "1";
        } else {
            $sp_nonreciprocal = "0";
        } 
        if ($_POST['validate'] == "yes") {
            $sp_validate = "1";
        } else {
            $sp_validate = "0";
        } 
        if ($_POST['deactivate'] == "yes") {
            $sp_deactivate = "1";
        } else {
            $sp_deactivate = "0";
        } 
        if ($_POST['email'] == "yes") {
            $sp_email = "1";
        } else {
            $sp_email = "0";
        } 
        if ($_POST['spoof'] == "yes") {
            $sp_spoof = "1";
        } else {
            $sp_spoof = "0";
        } 
        if ($_POST['email2'] == "yes") {
            $sp_email2 = "1";
        } else {
            $sp_email2 = "0";
        } 
        if ($_POST['email3'] == "yes") {
            $sp_email3 = "1";
        } else {
            $sp_email3 = "0";
        } 
        if ($_POST['showcategorydescription'] == "yes") {
            $sp_showcategorydescription = "1";
        } else {
            $sp_showcategorydescription = "0";
        } 
        if ($_POST['captcha'] == "yes") {
            $sp_captcha = "1";
        } else {
            $sp_captcha = "0";
        } 
        $sp_is_active = "1";
        $sp_emailto = eblex_fixinput($_POST['emailto']);
        $sp_reciprocalurl = eblex_fixinput($_POST['reciprocalurl']);
        $sp_emailt1 = eblex_fixinput($_POST['emailt1']);
        $sp_emailt2 = eblex_fixinput($_POST['emailt2']);
        $sp_keywords = eblex_fixinput($_POST['keywords']);
        $sp_pagedescription = eblex_fixinput($_POST['pagedescription']);
        $sp_name = eblex_fixinput($_POST['mname']);
        $sp_emailfrom = eblex_fixinput($_POST['emailfrom']);

        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_seofriendly' WHERE `option`='seofriendly'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_description' WHERE `option`='description'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_url' WHERE `option`='url'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_linksperpage' WHERE `option`='linksperpage'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_approval' WHERE `option`='approval'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_nonreciprocal' WHERE `option`='nonreciprocal'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_validate' WHERE `option`='validate'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_is_active' WHERE `option`='is_active'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_deactivate' WHERE `option`='deactivate'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_maxdescsize' WHERE `option`='maxdescsize'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_spoof' WHERE `option`='spoof'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_email' WHERE `option`='email'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_emailto' WHERE `option`='emailto'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_reciprocalurl' WHERE `option`='reciprocalurl'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_email2' WHERE `option`='email2'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_email3' WHERE `option`='email3'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_emailt1' WHERE `option`='emailt1'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_emailt2' WHERE `option`='emailt2'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_showcategorydescription' WHERE `option`='showcategorydescription'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_keywords' WHERE `option`='keywords'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_pagedescription' WHERE `option`='pagedescription'");
        $wpdb->query("UPDATE `$eblex_settings` SET `value`='$sp_captcha' WHERE `option`='captcha'");
        $wpdb->query("UPDATE $eblex_settings SET `value`='$sp_name' WHERE `option`='name'");
        $wpdb->query("UPDATE $eblex_settings SET `value`='$sp_emailfrom' WHERE `option`='emailfrom'");

        $noticemsg = "Settings were updated!";
    } 
} 
// --------------------------- GET SETTINGS -------------------------------
$s_seofriendly = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='seofriendly'");
$s_description = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='description'");
$s_url = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='url'");
$s_linksperpage = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='linksperpage'");
$s_approval = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='approval'");
$s_nonreciprocal = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='nonreciprocal'");
$s_validate = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='validate'");
$s_is_active = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='is_active'");
$s_deactivate = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='deactivate'");
$s_maxdescsize = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='maxdescsize'");
$s_email = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='email'");
$s_emailto = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='emailto'");
$s_spoof = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='spoof'");
$s_reciprocalurl = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='reciprocalurl'");
$s_email2 = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='email2'");
$s_email3 = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='email3'");
$s_emailt1 = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='emailt1'");
$s_emailt2 = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='emailt2'");
$s_showcategorydescription = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='showcategorydescription'");
$s_keywords = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='keywords'");
$s_pagedescription = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='pagedescription'");
$s_captcha = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='captcha'");
$s_name = $wpdb->get_var("SELECT `value` FROM $eblex_settings WHERE `option`='name'");
$s_emailfrom = $wpdb->get_var("SELECT `value` FROM $eblex_settings WHERE `option`='emailfrom'");

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
  <form id="form1" name="form1" method="post" action="">
  <h2>Настройки каталога ссылок</h2>
  <label>Моя ссылка:</label>
  <input name="reciprocalurl" type="text" id="reciprocalurl" value="<?php echo($s_reciprocalurl); ?>" size="45" /> e.g. &quot;http://www.google.com&quot;
  <br/>
  <label>Название каталога:</label>
  <input name="mname" type="text" id="mname" value="<?php echo($s_name); ?>" size="45" /> e.g. &quot;Мой каталог&quot;
  <br/><br/>
  <h2>SEO настройки</h2>
  <label>
  <input name="seo" type="checkbox" id="seo" value="yes" <?php if ($s_seofriendly == "1") {echo("checked");} ?>/>
  Использовать ЧПУ (требуется mod_rewrite)</label>
  <br/><br/>
  Ключевые слова по умолчанию (будут прописаны на главной странице каталога и там, где не указаны другие):
  <input name="keywords" type="text" id="keywords" value="<?php echo($s_keywords); ?>" size="60" />
(через запятую) <br/>
  <br/>
 Описание по умолчанию (будет прописано на главной странице каталога и там, где не указано другое): <br/>
  <textarea name="pagedescription" cols="70" rows="6" id="pagedescription"><?php echo($s_pagedescription); ?></textarea>
  <br/><br/>
  <h2>Настройки отображения категорий и ссылок</h2>
  <label>
  <input name="linkdescription" type="checkbox" id="linkdescription" value="yes" <?php if ($s_description == "1") {echo("checked");} ?>/>
  Отображать описание ссылок</label>
  <br/>
  <label>
  <input name="showcategorydescription" type="checkbox" id="showcategorydescription" value="yes" <?php if ($s_showcategorydescription == "1") {echo("checked");} ?>/>
  Отображать описание категорий</label>
  <br/>
  <label>
  <input name="linkurl" type="checkbox" id="linkurl" value="yes" <?php if ($s_url == "1") {echo("checked");} ?>/>
  Отображать URL ссылки</label>
  <br/><br/>
  Links per page: 
  <input name="lpp" type="text" class="code" id="lpp" size="5" value="<?php echo($s_linksperpage); ?>"/>
  <br/>
  <br/>
  <h2>Добавление ссылок</h2>
  <label>
  <input name="nonreciprocal" type="checkbox" id="nonreciprocal" value="yes" <?php if ($s_nonreciprocal == "1") {echo("checked");} ?>/>
  Разрешить добавление без обратной ссылки?</label>
  <br/>
  <label></label>
  <label>
  <input name="linksapproval" type="checkbox" id="linksapproval" value="yes" <?php if ($s_approval == "1") {echo("checked");} ?>/>
  Ссылки должны быть проверены</label><br/>
  <label><input name="captcha" type="checkbox" id="captcha" value="yes" <?php if ($s_captcha == "1") {echo("checked");} ?>/> Использовать CAPTCHA для защиты от авторегистраций</label><br/><br/>
  Максимальное количество символов в описании:
  <input name="maxdescsize" type="text" class="code" id="maxdescsize" size="5" value="<?php echo($s_maxdescsize); ?>"/>
<br/>
  <h2>Проверка обратных ссылок</h2>
    <label><input name="spoof" type="checkbox" id="spoof" value="yes" <?php if ($s_spoof == "1") {echo("checked");} ?>/>
  Во время проверки обратных ссылок представляться как браузер </label>
    (рекомендуется)<br/><br/>
	  <h2><?php _e('Настройки E-mail'); ?></h2>
  <label><input name="email" type="checkbox" id="email" value="yes" <?php if ($s_email == "1") {echo("checked");} ?>/>
  Отсылать письмо, когда ссылки ожидают одобрения во входящих</label>
  <br/>
  <label><input name="email2" type="checkbox" id="email2" value="yes" <?php if ($s_email2 == "1") {echo("checked");} ?>/>
  Отсылать письмо автору, когда я подтверждаю его ссылку</label>
  <br/>
  <label><input name="email3" type="checkbox" id="email3" value="yes" <?php if ($s_email3 == "1") {echo("checked");} ?>/>
  Отсылать письмо автору, когда его ссылка удалена</label>
  <br/>
  <br/>
  <label>Адрес администратора каталога e-mail:</label>
  <input name="emailfrom" type="text" id="emailfrom" value="<?php echo($s_emailfrom); ?>" size="45" /><br/><br/>
  <label>Мой e-mail:</label>
  <input name="emailto" type="text" id="emailto" value="<?php echo($s_emailto); ?>" size="45" /><br/><br/>
  <label>Шаблон письма, когда ссылка подтверждена:<br/>
  <textarea name="emailt1" cols="70" rows="6" id="emailt1"><?php echo($s_emailt1); ?></textarea>
  </label>
  <br/>
  <em>Используйте {LINK} для отображения ссылки. </em><br/><br/>
  <label>Шаблон письма, когда ссылка удалена:<br/>
  <textarea name="emailt2" cols="70" rows="6" id="emailt2"><?php echo($s_emailt2); ?></textarea>
  </label>
  <br/>
  <em>Используйте {LINK} для отображения ссылки.  </em><br/>
  <br/>
  <h2>Опции удаления</h2>
  <label>
  <input name="deactivate" type="checkbox" id="deactivate" value="yes" <?php if ($s_deactivate == "1") {echo("checked");} ?>/>
  После деактивации плагина удалить все данные из базы данных, включая категории и ссылки.</label>
    <p class="submit">
	<input type="submit" value="<?php _e('Save settings') ?>" name="ok" style="float:right;"/>
	</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
  </form>
<br/>
</div>
<?php
require('./admin-footer.php');
?>