<?php
/*
Plugin Name: Каталог ссылок
Plugin URI: http://wpwiki.ru/wiki/WP_Link_Directory
Description: Этот плагин позволяет создать каталог ссылок в вашем блоге на WordPress.
Author: eBrandMarketing & WPWiki.ru
Version: 1.4ru
Author URI: http://wpwiki.ru/
*/ 
// install functions
function eblex_executequery($query)
{
    global $wpdb;
    $wpdb->query($query);
} 

function eblex_install()
{
    global $table_prefix, $wpdb;
    $eblex_settings = $table_prefix . "eblex_settings";
    $eblex_categories = $table_prefix . "eblex_categories";
    $eblex_links = $table_prefix . "eblex_link";
	$eblex_captcha = $table_prefix . "eblex_captcha";

	if ($wpdb->get_var("show tables like '$eblex_captcha'") != $eblex_captcha)
	{
		 $sql = 'CREATE TABLE `' . $table_prefix . 'eblex_captcha` ('
         . ' `id` VARCHAR(32) NOT NULL, '
         . ' `text` TINYTEXT NOT NULL, '
         . ' `time` BIGINT UNSIGNED NOT NULL,'
         . ' INDEX (`id`)'
         . ' )'
         . ' ENGINE = myisam;';
        $results = $wpdb->query($sql);
	}

    if ($wpdb->get_var("show tables like '$eblex_settings'") != $eblex_settings && $wpdb->get_var("show tables like '$eblex_categories'") != $eblex_categories && $wpdb->get_var("show tables like '$eblex_links'") != $eblex_links) {
        $sql = 'CREATE TABLE `' . $table_prefix . 'eblex_settings` ('
         . '`option` TINYTEXT NOT NULL ,'
         . '`value` MEDIUMTEXT NOT NULL ,'
         . '`id` INT NOT NULL ,'
         . 'INDEX ( `id` )'
         . ') ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
        $results = $wpdb->query($sql);

        $sql = 'CREATE TABLE `' . $table_prefix . 'eblex_categories` ('
         . ' `id` VARCHAR(32) NOT NULL, '
         . ' `parent` VARCHAR(32) NOT NULL, '
         . ' `title` MEDIUMTEXT NOT NULL, '
         . ' `description` MEDIUMTEXT NOT NULL, '
         . ' `keywords` MEDIUMTEXT NOT NULL, '
         . ' `nicename` MEDIUMTEXT NOT NULL, '
         . ' `time` BIGINT UNSIGNED NOT NULL, '
         . ' `visible` BOOL NOT NULL, '
         . ' `zindex` BIGINT NOT NULL,'
         . ' INDEX (`id`)'
         . ' )'
         . ' ENGINE = myisam'
         . ' CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
        $results = $wpdb->query($sql);

        $sql = 'INSERT INTO `' . $table_prefix . 'eblex_categories` (`id`, `parent`, `title`, `description`, `keywords`, `nicename`, `time`, `visible`, `zindex`) VALUES (\'0\', \'\', \'Root\', \'Root directory\', \'\', \'root\', \'1\', \'1\', \'0\');';
        $results = $wpdb->query($sql);

        $sql = 'CREATE TABLE `' . $table_prefix . 'eblex_links` ('
         . ' `title` MEDIUMTEXT NOT NULL, '
         . ' `active` TINYINT NOT NULL, '
         . ' `nonreciprocal` TINYINT NOT NULL, '
         . ' `url` MEDIUMTEXT NOT NULL, '
         . ' `category` VARCHAR(32) NOT NULL, '
         . ' `description` MEDIUMTEXT NOT NULL, '
         . ' `email` MEDIUMTEXT NOT NULL, '
         . ' `reciprocalurl` MEDIUMTEXT NOT NULL, '
         . ' `status` TINYTEXT NOT NULL, '
         . ' `time` BIGINT UNSIGNED NOT NULL, '
         . ' `administratorcomment` MEDIUMTEXT NOT NULL, '
         . ' `zindex` BIGINT NOT NULL, '
         . ' `id` VARCHAR(32) NOT NULL,'
         . ' INDEX (`id`)'
         . ' )'
         . ' ENGINE = myisam'
         . ' CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
        $results = $wpdb->query($sql);
    } 
    // Add options
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('seofriendly', '1', '0');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('description', '1', '1');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('url', '1', '2');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('linksperpage', '10', '3');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('approval', '1', '4');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('nonreciprocal', '0', '5');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('validate', '1', '6');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('deactivate', '0', '7');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('is_active', '1', '8');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('maxdescsize', '350', '9');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('reciprocalurl', 'http://www.google.com', '10');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('spoof', '1', '11');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('email', '1', '12');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('emailto', 'do-not-reply@ebrandmarketing.com.au', '13');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('email2', '1', '14');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('email3', '1', '15');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('emailt1', 'Your link ({LINK}) has been approved to be displayed in our link directory!\n\nThank you for your submission!', '16');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('emailt2', 'We regret to inform you that your link ({LINK}) has not been approved to be displayed in our link directory due to incompatibility with our policies.\n\nYou may have submitted an invalid reciprocal URL, placed your link in a wrong category, or maybe you skipped on writing an adequate description for it. If this is the case, you may try and submit your link again.\n\nThank you for your submission.', '17');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('showcategorydescription', '0', '18');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('keywords', '', '19'), ('pagedescription', 'Link Directory', '20');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('captcha', '1', '21');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('name', 'Link Directory', '22');");
    eblex_executequery("INSERT INTO `" . $table_prefix . "eblex_settings` (`option`, `value`, `id`) VALUES ('emailfrom', 'do-not-reply@ebrandmarketing.com.au', '23');");
} 
// UNINSTALL
function eblex_uninstall()
{
    global $table_prefix, $wpdb;
    $eblex_settings = $table_prefix . "eblex_settings";
    $eblex_categories = $table_prefix . "eblex_categories";
    $eblex_links = $table_prefix . "eblex_links";
	$eblex_captcha = $table_prefix . "eblex_captcha";
    $eblex_deactivate = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='deactivate'");

    if ($eblex_deactivate == "1") {
        eblex_executequery('DROP TABLE `' . $table_prefix . 'eblex_settings`');
        eblex_executequery('DROP TABLE `' . $table_prefix . 'eblex_categories`');
        eblex_executequery('DROP TABLE `' . $table_prefix . 'eblex_links`');
        eblex_executequery('DROP TABLE `' . $table_prefix . 'eblex_captcha`');
    } else {
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='seofriendly'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='description'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='url'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='linksperpage'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='approval'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='nonreciprocal'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='validate'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='deactivate'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='is_active'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='maxdescsize'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='reciprocalurl'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='spoof'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='email'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='emailto'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='email2'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='email3'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='emailt1'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='emailt2'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='showcategorydescription'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='keywords'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='pagedescription'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='captcha'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='name'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='emailfrom'"); 
        // Patch
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='lic'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='seo1'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='seo2'");
        eblex_executequery("DELETE FROM `" . $table_prefix . "eblex_settings` WHERE `option`='seo3'");
    } 
} 
// Create the button for the plugin
function eblex_put_button()
{
    global $menu, $submenu, $wpdb, $table_prefix;
    $menu[50] = array(__('Каталог ссылок'), 'manage_links', 'link-exchange.php');
    $inbox = $wpdb->get_var("SELECT count(*) FROM `" . $table_prefix . "eblex_links` WHERE `active`='0'");

    if ($_GET['action'] == "delete") {
        $confirmation = $wpdb->get_var("SELECT `id` FROM `" . $table_prefix . "eblex_links` WHERE `id`='" . $_GET['id'] . "'");
        if ($confirmation != "") {
            $inbox--;
        } 
    } 

    if ($_GET['action'] == "approve") {
        $confirmation = $wpdb->get_var("SELECT `id` FROM `" . $table_prefix . "eblex_links` WHERE `id`='" . $_GET['id'] . "' AND `active`='0'");
        if ($confirmation != "") {
            $inbox--;
        } 
    } 

    $submenu['link-exchange.php'][0] = array(__('Home'), 'manage_links', 'link-exchange.php');
    $submenu['link-exchange.php'][5] = array(__('Входящие (' . $inbox . ')'), 'manage_links', 'link-exchange-inbox.php');
    $submenu['link-exchange.php'][10] = array(__('Добавить'), 'manage_links', 'link-exchange-qa.php');
    $submenu['link-exchange.php'][15] = array(__('Категории'), 'manage_links', 'link-exchange-categories.php');
    $submenu['link-exchange.php'][20] = array(__('Просмотр ссылок'), 'manage_links', 'link-exchange-browse.php');
    $submenu['link-exchange.php'][25] = array(__('Поиск'), 'manage_links', 'link-exchange-search.php');
    $submenu['link-exchange.php'][30] = array(__('Проверка обратных ссылок'), 'manage_links', 'link-exchange-clean.php');
    $submenu['link-exchange.php'][35] = array(__('Статистика'), 'manage_links', 'link-exchange-stats.php');
    $submenu['link-exchange.php'][40] = array(__('Настройки'), 'manage_links', 'link-exchange-settings.php');
    $submenu['link-exchange.php'][45] = array(__('Помощь'), 'manage_links', 'link-exchange-help.php');
} 

add_action('admin_head', 'eblex_put_button');
add_action('activate_linkexchange/plugin.php', 'eblex_install');
add_action('deactivate_linkexchange/plugin.php', 'eblex_uninstall');

?>