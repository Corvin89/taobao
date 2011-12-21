<?php

/*
 * odl_functions.php (eclipse)
 * wordpress plugin open directory project
 * @author Mohammad Forgani
 * @copyright Copyright 2008, Oh Jung-Su
 * @version 1.1.0
 * 2009-10-23 fixed for wp 2.8.5
 * @link http://www.forgani.com
 * last changes: 28.03.2011
 */

//if (!isset($_SESSION)) session_start();
require_once(ABSPATH . ODLSMARTY . '/Smarty.class.php');

class ODLTemplate extends Smarty {
	function ODLTemplate($cache = true, $cache_lifetime = 0){
		global $smarty_template_dir, $smarty_compile_dir, $smarty_cache_dir, $smarty_config_dir;
		$this->Smarty();
		$this->template_dir = $smarty_template_dir;
		$this->compile_dir = $smarty_compile_dir;
		$this->config_dir = $smarty_config_dir;
		$this->cache_dir = $smarty_cache_dir;
		$this->caching = $cache;
		$this->cache_lifetime = $cache_lifetime;
	}
}

function odlinksget_wp_mainversion(){
	global $odlinkswp_mainversion;
	if ($odlinkswp_mainversion==false){
		odlinksget_namefield();
	}
	return $odlinkswp_mainversion;
}

function odlinksget_pageinfo(){
	global $wpdb, $odlinkswp_pageinfo, $table_prefix;

	if ($odlinkswp_pageinfo==false){
		$odlinkswp_pageinfo = $wpdb->get_row("SELECT * FROM {$table_prefix}posts WHERE post_title = '[[ODLINKS]]'", ARRAY_A);
	}
	return $odlinkswp_pageinfo;
}

function odlinkscreate_page(){
	global $wpdb, $table_prefix, $wp_version;
	$dt = date("Y-m-d");
	$wpdb->query("INSERT INTO {$table_prefix}posts (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order, post_type) VALUES ('1', '$dt', '$dt', '[[ODLINKS]]', '[[ODLINKS]]',  '[[ODLINKS]]', 'publish', 'closed', 'closed', '', 'odlinks', '', '', '$dt', '$dt', '[[ODLINKS]]', '0', '', '0', 'page')");

	return $wpdb->get_row("SELECT * FROM {$table_prefix}posts WHERE post_title = '[[ODLINKS]]'", ARRAY_A);
}

function odlinks_admin_page(){
	global $odlinksadmin_links, $odlinks_name, $odlinksadmin_page_name, $odlinksuser_level;
	add_menu_page($odlinksadmin_page_name,$odlinksadmin_page_name,$odlinksuser_level,__FILE__,'process_odlinkssettings','../wp-content/plugins/odlinks/images/odl.gif');
	for ($i=0; $i<count($odlinksadmin_links); $i++){
		$tlink = $odlinksadmin_links[$i];
		add_submenu_page(__FILE__,$tlink['name'],$tlink['name'],$odlinksuser_level,$tlink['arg'],$tlink['prg']);
	}
}

function odlinksrewrite_rules_wp($wp_rewrite){
	global $wp_rewrite;
	$odlinkssettings = get_option('odlinksdata');
	$odlinksslug = $odlinkssettings['odlinksslug'];
	$odlinksrules = array(
	$odlinksslug.'/([^/\(\)]*)/?([^/\(\)]*)/?([^/\(\)]*)/?' => '/'.$odlinksslug.'/index.php?pagename='.$odlinksslug.'&_action=$matches[1]&id=$matches[2]&parent=$matches[3]');
	$wp_rewrite->rules = $odlinksrules + $wp_rewrite->rules;
}

function odlinksquery_vars($vars){
	$vars[] = '_action';
	$vars[] = 'id';
	$vars[] = 'orderby';
	$vars[] = 'who';
	return $vars;
}

function odlinks_excerpt_text($length, $text){
	$text = strip_tags(odlinkscreate_post_html($text));
	if(strlen($text)>$length){
		$ret_strpos = strpos($text, ' ', $length);
		$ret = substr($text, 0, $ret_strpos)." ...";
	}else{
		$ret = $text;
	}
	return $ret;
}

function odlinkspage_handle_title($title){
	global $odl_breadcrumbs;
	if ($odl_breadcrumbs==""){
		$sidebar = 0;
		$odl_breadcrumbs = odlinksget_breadcrumbs($sidebar);
	}
	return str_replace("[[ODLINKS]]", $odl_breadcrumbs, $title);
}

function odlinkspage_handle_pagetitle($title){
	global $odl_pagetitle;
	return str_replace("[[ODLINKS]]", "ODLinks  &raquo; ", $title);
}

function odlinkspage_handle_content($content){
	if (preg_match('/\[\[ODLINKS\]\]/', $content)){
		odlinksprocess();
		return "";
	} else {
		return $content;
	}
}

function odlinkspage_handle_titlechange($title){
	global $odl_breadcrumbs;
	$sidebar = 0;
	$odl_breadcrumbs = odlinksget_breadcrumbs($sidebar);
	$odlinkssettings = get_option('odlinksdata');
	$title = str_replace($odl_breadcrumbs, $odlinkssettings["page_link_title"], $title);
	$title = str_replace("[[ODLINKS]]", $odlinkssettings["page_link_title"], $title);
	return $title;
}

function odlinksget_breadcrumbs($sidebar){
	global $_GET, $_POST, $_SERVER, $wp_version;
	$g__action = get_query_var("_action");
	$id = get_query_var("id");
	$parent = get_query_var("parent");
	if (basename($_SERVER['PHP_SELF'])!='index.php'){
		return "[[ODLINKS]]";
	} else {
		$odlinkssettings = get_option('odlinksdata');
		if (!isset($_POST['search_terms']) && $sidebar=0) {
			$g__action = "sidebar";
		} elseif (!isset($_POST['search_terms'])) {
			$g__action = $g__action;
		} else {
			$g__action = "search";
		}
		switch ($g__action){
			default:
			case "index":
				return '<strong class="odl_breadcrumb">'.$odlinkssettings['page_link_title'].'</strong>';
				break;
		}
	}
}

function odlinkscreate_link($action, $vars){
	global $wp_rewrite;
	$odlinkssettings = get_option('odlinksdata');
	$pageinfo = odlinksget_pageinfo();
	if($wp_rewrite->using_permalinks()) $delim = "?";
	else $delim = "&amp;";
	$perm = get_permalink($pageinfo['ID']);
	$main_link = $perm.$delim;
	if ( isset($vars['name']) ) {
		// currently not used
		$odl_vars_name = $vars['name'];
		$odl_vars_name = preg_replace('/[^A-Za-z0-9\s\.]/', "", $odl_vars_name);
		$odl_vars_name = preg_replace('/\s/', '-', $odl_vars_name);
		$odl_vars_name = preg_replace('/\./', '-', $odl_vars_name);
	} else $vars['name'] = '';

	$name = trim($vars['name']);

	switch ($action){
		case "index":
			return "<a href=\"".$main_link."_action=index\">".$name."</a>";
			break;
		case "category":
			return "<a href=\"".$main_link."_action=main&id=".$vars["id"]."&parent=".$vars['parent']."\">".$name."</a>";
			break;
		case "postlink":
			return "<a href=\"".$main_link."_action=postlink&id=".$vars["id"]."&amp;parent=".$vars['parent'] ."\">".$name."</a>";
			break;
		case "searchlink":
			return "<a href=\"".$main_link."_action=searchlink" ."\">".$name."</a>";
			break;
		case "searchform":
			return $main_link."_action=searchlink";
			break;
		case "sendform":
			return $main_link."_action=sendlink";
			break;
		case "sendlink":
			return "<a style=\"color:green\" href=\"".$main_link."_action=sendlink&amp;id=".$vars["id"]."\">".$name."</a>";
			break;
	}
}

function odlinksprocess(){
	global $_GET, $_POST, $wp_version;
	if (!isset($msg)) $msg='';
	if (!isset($confirm)) $confirm='';
	$action = get_query_var("_action");
	$odlinkssettings = get_option('odlinksdata');
	switch ($action){
		default:
		case "main":
			odlinksdisplay_index($msg);
			break;
		case "searchlink":
			odlinksdisplay_search();
			break;
		case "postlink":
			odlinkspost_link($confirm);
			break;
		case "sendlink":
			odlinkssend_link($confirm);
			break;
		case "install";
		echo "Please install ODLinks by saving the Settings in the ODLinks Admin area.";
		break;
	}
}


?>
