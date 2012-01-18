<?php
/*
Plugin Name: WP-Options
Plugin URI: http://nstanke.at/plugins/
Description: With this Plugin, you can see the hidden settings page. WP-Sitemap is a little Admin Sitemap. WP-PHP Include a PHP Info Page. WP-Encoder Include a PHP Encoder (german). WP-Mail From Changes the email address
Author: Norman Stanke
Version: 0.7
Author URI: http://nstanke.at/
*/
function site_mail_from_option_page() {
  ?>
  <div class="wrap">
  <h2>WP-Mail From</h2>
  <i>These 2 options will override the email address and name in the From: header on all sent emails</i>
  <form method="post" action="options.php">
  <?php wp_nonce_field('update-options'); ?>
  <table class="form-table">
  <tr><th scope="row">From Email</th>
  <td><input type="text" name="site_mail_from_email" value="<?php echo get_option('site_mail_from_email'); ?>" /></td></tr>
  <tr><th scope="row">Email Name</th>
  <td><input type="text" name="site_mail_from_name" value="<?php echo get_option('site_mail_from_name'); ?>" /></td></tr>
  </table>
  <input type="hidden" name="action" value="update" />
  <input type="hidden" name="page_options" value="site_mail_from_email,site_mail_from_name" />
  <p class="submit">
  <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
  </p>
  </form>
  </div>
  <?php
}

function site_mail_from_menu () {
  add_options_page('mailfrom plugin', 'wp mail from', 9, __FILE__, 'site_mail_from_option_page');
}

function site_mail_from ($mail_from_email) {
  $site_mail_from_email = get_option('site_mail_from_email');
  if(empty($site_mail_from_email)) {
    return $mail_from_email;
  }
  else {
    return $site_mail_from_email;
  }
}

function site_mail_from_name ($mail_from_name) {
  $site_mail_from_name = get_option('site_mail_from_name');
  if(empty($site_mail_from_name)) {
    return $mail_from_name;
  }
  else {
    return $site_mail_from_name;
  }
}
function encoder() {

include 'phpencoder.php';

}
function info() {

phpinfo();

}

function options() {
  ?>
<meta http-equiv="refresh" content="0; URL=options.php"> 
  <?PHP
}
function sitemap() {
  ?>
  require_once('./admin.php');
  
    <h1>Posts</h1>
	<a href="post-new.php"><?php _e("Add New"); ?></a><br />
	<a href="edit.php"><?php _e("Edit"); ?></a><br />
	<a href="edit-tags.php?taxonomy=category"><?php _e("Categories"); ?></a><br />
	<h1>Media</h1>
	<a href="upload.php"><?php _e("Libary"); ?></a><br />
	<a href="media-new.php"><?php _e("Add New"); ?></a><br />
	<h1>Links</h1>
	<a href="link-manager.php"><?php _e("Edit"); ?></a><br />
	<a href="link-add.php"><?php _e("Add New"); ?></a><br />
	<a href="edit-link-categories.php"><?php _e("Link Categories"); ?></a><br />
	<h1>Pages</h1>
	<a href="edit.php?post_type=page"><?php _e("Edit"); ?></a><br />
	<a href="post-new.php?post_type=page"><?php _e("Add New"); ?></a><br />
	<h1>Comments</h1>
	<a href="edit-comments.php"><?php _e("Comments"); ?></a><br />
	<h1>Appearance</h1>
	<a href="themes.php"><?php _e("Themes"); ?></a><br />
	<a href="theme-install.php"><?php _e("Install"); ?></a><br />
	<a href="widgets.php"><?php _e("Widgets"); ?></a><br />
	<a href="nav-menus.php"><?php _e("Menus (3.0)"); ?></a><br />
	<a href="themes.php?page=custom-header"><?php _e("Header (3.0)"); ?></a><br />
	<a href="themes.php?page=custom-background"><?php _e("Background (3.0)"); ?></a><br />
	<a href="theme-editor.php"><?php _e("Editor"); ?></a><br />
	<h1>Plugins</h1>
	<a href="plugins.php"><?php _e("Installed"); ?></a><br />
	<a href="plugin-install.php"><?php _e("Add New"); ?></a><br />
	<a href="plugin-editor.php"><?php _e("Editor"); ?></a><br />
	<h1>Users</h1>
	<a href="users.php"><?php _e("Authors & Users"); ?></a><br />
	<a href="user-new.php"><?php _e("Add New"); ?></a><br />
	<a href="profile.php"><?php _e("Your Profile"); ?></a><br />
	<h1>Tools</h1>
	<a href="tools.php"><?php _e("Tools"); ?></a><br />
	<a href="import.php"><?php _e("Import"); ?></a><br />
	<a href="export.php"><?php _e("Export"); ?></a><br />
	<h1>Settings</h1>
	<a href="options-general.php"><?php _e("General"); ?></a><br />
	<a href="options-writing.php"><?php _e("Writing"); ?></a><br />
	<a href="options-reading.php"><?php _e("Reading"); ?></a><br />
	<a href="options-discussion.php"><?php _e("Discussion"); ?></a><br />
	<a href="options-media.php"><?php _e("Media"); ?></a><br />
	<a href="options-privacy.php"><?php _e("Privacy"); ?></a><br />
	<a href="options-permalink.php"><?php _e("Permalinks"); ?></a><br />
	<a href="options-misc.php"><?php _e("Miscellaneous"); ?></a><br />
	<h1>MU</h1>
	<a href="ms-admin.php"><?php _e("Admin"); ?></a><br />
	<a href="ms-sites.php"><?php _e("Blogs"); ?></a><br />
	<a href="ms-users.php"><?php _e("Users"); ?></a><br />
	<a href="ms-themes.php"><?php _e("Themes"); ?></a><br />
	<a href="ms-options.php"><?php _e("Settings"); ?></a><br />
	<a href="ms-upgrade-network.php"><?php _e("Update"); ?></a><br />
	
  <?PHP

}


function profileAddMenu() {
  add_menu_page('WP-Options', 'WP-Options', 10, __FILE__, 'options');
  add_submenu_page(__FILE__, 'WP-Sitemap', 'WP-Sitemap', 10, 'sitemap', 'sitemap');
  add_submenu_page(__FILE__, 'WP-PHP', 'WP-PHP', 10, 'info', 'info');
  add_submenu_page(__FILE__, 'WP-Encoder', 'WP-Encoder', 10, 'encoder', 'encoder');
  add_submenu_page(__FILE__, 'WP-Mail From', 'WP-Mail From', 10, 'site_mail_from_option_page', 'site_mail_from_option_page');

}
 
add_action('admin_menu', 'profileAddMenu');
?>