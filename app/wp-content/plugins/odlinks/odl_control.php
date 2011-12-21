<?php

/*
Plugin Name: ODLinks
Plugin URI: http://www.forgani.com
Description: Open Directory Links *** Check the ODLINKS settings after an upgrade! ***
Author: Mohammad forgani
Version: 1.3.0-a
Author URI: http://www.forgani.com

Changes 1.0.2-a & b - Oct 25-26 2009
- Fixed bug with auto-install on wordpress 2.8.5

Changes 1.0.1-a - Mar 17/03/2009
- Implement the search function.

Changes 1.0.0-a - Jan 25/01/2009
-It covers changes between WordPress Version 2.6 and Version 2.7

Changes Nov 23/2008
- implement the banned list

- added google pagerank

Changes Oct 17/2008
 - implement the conformaion code (captcha)

Changes Oct 25/2008
- Update the methods and tools of admin

Changes Nov 12/2008
- implement the bookmark & send to your friends service
- edit/replace categories

Changes 1.1-a - Jan 19/01/2010
- update the search process and templates

Changes 1.1.1-a - Jan 20/01/2010
- implemented english language file

Changes 1.1.2-a - May 25/05/2010
- new captcha routine. The previous methods have got problem with firefox
- updated to show ComboBox with subcategory names


Changes 1.1.2-c - May 25/05/2010
- fixed for wordpress 3.0


Changes 1.1.2-d - Aug 29/08/2010
- implement category's link in footer


Last Changes: Mar 30/03/2011
- added/changed new skin theme & added some further admin interface 
- made some tiny changes to fixe for wp 3.1 problems..

*/



global $table_prefix, $wpdb;
$odlinkssettings = get_option('odlinksdata');

// Sets the version number.
$odlinksversion = "1.3.0-a";
// Sets the required user level.
$odlinksuser_level = 8;
// Sets the Management page tab name in WordPress Admin area.
$odlinksadmin_page_name = 'ODLinks';
// Sets the odlinks page link name in WordPress.
$odlinks_name = 'odlinks';
$odlinksuser_field = false;
// Sets $odlinkswp_mainversion to false.
$odlinkswp_mainversion = false;
// Sets $odlinkswp_pageinfo to false.
$odlinkswp_pageinfo = false;
// Admin links and their url args.
$odlinksadmin_links = array(
	array('name'=>'ODLINKS Settings','arg'=>'odlinkssettings','prg'=>'process_odlinkssettings'),
	array('name'=>'ODLINKS Structure','arg'=>'odlinksstructure','prg'=>'process_odlinksstructure'),
	array('name'=>'ODLINKS Links','arg'=>'odlinksposts','prg'=>'process_odlinksposts'),
	array('name'=>'ODLINKS Utilities','arg'=>'odlinksutilities','prg'=>'process_odlinksutilities'),
);

if (!$table_prefix) $table_prefix = $wpdb->prefix;

define('ODL_PLUGIN_URL', get_bloginfo('wpurl') . '/wp-content/plugins/odlinks');
define('ODL_PLUGIN_DIR', ABSPATH  . 'wp-content/plugins/odlinks');
define('ODL', 'wp-content/plugins/odlinks');
define('ODLADMIN', 'wp-content/plugins/odlinks/admin/');
define('ODLINC', 'wp-content/plugins/odlinks/includes/');
define('ODLADMINTHEME', 'wp-content/plugins/odlinks/themes');
define('ODLTHEME', 'wp-content/plugins/odlinks/themes/default');
define('ODLSMARTY', 'wp-content/plugins/odlinks/includes/Smarty');
define('ODLANG', 'wp-content/plugins/odlinks/languages/');

$smarty_template_dir =  ABSPATH . ODLTHEME;
$smarty_compile_dir = ABSPATH . ODLSMARTY . '/templates_c';
$smarty_cache_dir = ABSPATH . ODLSMARTY . '/cache';
$smarty_config_dir = ABSPATH . ODLSMARTY . '/configs';

require_once(ABSPATH . ODLINC . '/odl_functions.php');
require_once(ABSPATH . ODLADMIN . '/odl_admin_functions.php');
require_once(ABSPATH . ODLADMIN . '/odl_admin.php');
require_once(ABSPATH . ODLADMIN . '/odl_admin_settings.php');
require_once(ABSPATH . ODLADMIN . '/odl_admin_structure.php');
require_once(ABSPATH . ODLADMIN . '/odl_admin_utilities.php');
require_once(ABSPATH . ODL . '/odl_posts.php');
require_once(ABSPATH . ODL . '/odl_search.php');
require_once(ABSPATH . ODL . '/odl_main.php');
require_once(dirname(__FILE__) . '/includes/odl_securimage.php');

add_filter("the_title", "odlinkspage_handle_title");
add_filter("wp_list_pages", "odlinkspage_handle_titlechange");
add_filter("single_post_title", "odlinkspage_handle_pagetitle");
add_filter("query_vars", "odlinksquery_vars");
add_filter('the_generator', 'rm_generator_filter');

function rm_generator_filter() { return ''; }

add_action("the_content", "odlinkspage_handle_content");
add_action('wp_head', 'add_head');
add_action('admin_menu', 'odlinks_admin_page');

// Assigns each respective variable.
if (!isset($_GET)) $_GET = $HTTP_GET_VARS;
if (!isset($_POST)) $_POST = $HTTP_POST_VARS;
if (!isset($_SERVER)) $_SERVER = $HTTP_SERVER_VARS;
if (!isset($_COOKIE)) $_COOKIE = $HTTP_COOKIE_VARS;

// Format any data sent to odlinks.
if (isset($_REQUEST["odlinksaction"])){
	$_SERVER["REQUEST_URI"] = dirname(dirname($_SERVER["PHP_SELF"]))."/".$odlinkssettings['odlinksslug']."/";
	$_SERVER["REQUEST_URI"] = stripslashes($_SERVER["REQUEST_URI"]);
}

function add_admin_head() {
	echo '<link rel="stylesheet" href="' . plugins_url('odlinks') . '/themes/default/css/admin.css" type="text/css" />';
}

function add_head() {
	echo '<link rel="stylesheet" href="' . plugins_url('odlinks') . '/themes/default/css/odlinks.css" type="text/css" />';
}

/**
 * get_language() - Get HTTP header accept languages
*/
$locale = get_locale();
if(!empty($locale)) {
	$lng = preg_split ('/_/', $locale );
	$languageFile = ODL_PLUGIN_DIR . '/language/lang_'. $lng[0] . '.php';
}
if (!empty($languageFile) && file_exists($languageFile)) require_once($languageFile);
 else require_once(ODL_PLUGIN_DIR . '/language/lang_en.php');

?>