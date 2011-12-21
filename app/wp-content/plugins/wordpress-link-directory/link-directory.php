<?php
/*
Plugin Name: WordPress Link Directory
Version: 1.8.2
Plugin URI: http://www.seanbluestone.com/wp-link-directory
Author: Sean Bluestone
Author URI: http://www.seanbluestone.com
Description: A Links Directory for WordPress

Copyright 2008  Sean Bluestone  (email : thedux0r@gmail.com)

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Permalinks

require_once(dirname(__FILE__).'/../../../wp-config.php');
require_once(dirname(__FILE__).'/../../../wp-admin/upgrade-functions.php');

class wpld_permalinks {
	function wpld_permalinks(){
		if (isset($this)){
			add_action('init', array(&$this, 'permalinkbarebone_init'));
			add_filter('rewrite_rules_array', array(&$this, 'permalinkbarebone_influence_rewrite_rules'));
			add_filter('query_vars', array(&$this, 'permalinkbarebone_query_vars'));
			add_action('parse_query', array(&$this, 'permalinkbarebone_parse_query'));
		}
	}

	function permalinkbarebone_init(){
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}

	function permalinkbarebone_influence_rewrite_rules($rules){
		$PermalinkBase=get_option('wplinkdir_permalinks');
		$newrules = array();
		$newrules[$PermalinkBase.'/(.+)/([0-9]+)/go/?$']='index.php?wpldgo=$matches[2]';
		$newrules[$PermalinkBase.'/(.+)/([0-9]+)/?$']='index.php?wpldpage=$matches[1]&wpldlink=$matches[2]';
		$newrules[$PermalinkBase.'/(.+)/?$']='index.php?wpldpage=$matches[1]';
		$newrules[$PermalinkBase.'/?$']='index.php?wpldpage=home';
		return $newrules+$rules;
	}

	function permalinkbarebone_query_vars($vars){
		array_push($vars, 'wpldpage','wpldlink','wpldgo');
		return $vars;
	}

	function permalinkbarebone_parse_query($query){
		global $Page,$Go;
		if(isset($query->query_vars['wpldlink'])) {
			$Page=$query->query_vars['wpldlink'];
		}else{
			$query->query_vars['wpldpage'];
		}
	}
}

if(get_option('wplinkdir_permalinks')!=''){
	$Permalinks=new wpld_permalinks();
}

register_activation_hook(__FILE__, 'wplinkdir_init');
//register_deactivation_hook(__FILE__, 'wplinkdir_uninstall');

add_action('init', 'wplinkdir_header');
add_action('init', 'wplinkdir_flag');
add_action('admin_menu', 'wplinkdir_menu');
add_action('wp_head', 'wplinkdir_styling');

global $wpdb;
define("WPLD_LINKS_TABLE",$wpdb->prefix."wplinkdir_links");
define("WPLD_CATS_TABLE",$wpdb->prefix."wplinkdir_cats");
if(strpos('Pro',get_option('wplinkdir_version'))){
	define('WPLD_PRO',TRUE);
}
$Currencies=array('AUD'=>'$','CAD'=>'$','CHF'=>'Fr','CNY'=>'&#165;','EUR'=>'&#128;','GBP'=>'&#163;','JPY'=>'&#165;','MXN'=>'$','RUB'=>'p.','USD'=>'$','ZAR'=>'R');


if(get_option('wplinkdir_track_hitsin')=='Yes' && !empty($_SERVER['HTTP_REFERER']) && !strpos($_SERVER['HTTP_REFERER'],$_SERVER['HTTP_HOST'])){

	$Parts=parse_url($_SERVER['HTTP_REFERER']);
	$Search=$Parts['host'];

	mysql_query("UPDATE ".WPLD_LINKS_TABLE." SET hitsin = hitsin + 1 WHERE url LIKE '%$Search%'");
}


function wplinkdir_header(){
	if(get_option('wplinkdir_track_hitsout')=='Yes' && is_numeric($_GET['go'])){
		if(!is_numeric($_GET['go'])){
			echo __('Invalid request.',$WPLD_Domain);
		}else{
			$getURL=mysql_query("SELECT url FROM ".WPLD_LINKS_TABLE." WHERE id = {$_GET['go']}")or die(mysql_error());
			if(($URL=@mysql_result($getURL,0,0))!=''){
				mysql_query("UPDATE ".WPLD_LINKS_TABLE." SET hitsout = hitsout + 1 WHERE id = {$_GET['go']}");
				header("Location: $URL");
				exit;
			}else{
				echo __("The link couldn't be found or no longer exists.",$WPLD_Domain);
			}
		}
	}
}


if(!function_exists('wplinkdir_wplink_shortcode') && get_option('wplinkdir_tagging')=='Yes'){

function wplinkdir_wplink_shortcode($atts,$content=null){
	// Controls shortcodes for [link]. Example: [link url="http://www.url.com" title="Title"]Description[/link]
	// The minimum requirement is a URL so [link url="http://www.url.com"] with or without a description will work but [link title="Title"] will not

	global $wpdb,$isFlagged;
	$table_links = WPLD_LINKS_TABLE;
	$table_cats = WPLD_CATS_TABLE;

	extract(shortcode_atts(array(
		'url' => '',
		'title' => '',
		'description' => '',
	), $atts));

	if(!isset($atts['description'])){
		$atts['description']=$content;
	}

	$ShowDescription=get_option('wplinkdir_show_description');
	$ShowBy=get_option('wplinkdir_show_byline');
	$Flagging=get_option('wplinkdir_flagging');

	$URL=mysql_real_escape_string($atts['url']);
	$Title=mysql_real_escape_string($atts['title']);
	$Description=mysql_real_escape_string($atts['description']);

	// Minimum requirement: [link url="http://www.site.com"]

	if(!empty($URL)){
		// Add option to view links as unselected from db
		global $userdata;

		if(empty($Title)){
			$Title=$URL;
		}

		$Now=time();
		$getLink=mysql_query("SELECT * FROM $table_links WHERE url = '$URL' AND tagged = 1 LIMIT 1");

		if(@mysql_num_rows($getLink)==0){
			get_currentuserinfo();
			$Name=$userdata->display_name;
			$Email=$userdata->user_email;

			$Rank=getpagerank($URL);
			if(!is_numeric($Rank)){
				$Rank=0;
			}
			mysql_query("INSERT INTO $table_links VALUES ('','$Name','$Email','$Title','$URL','$Description','','',$Rank,'',$Now,'',0,1,0,0)");
			$Ins=TRUE;
		}else{
			$Link=@mysql_fetch_assoc($getLink);
			$URL=$Link['url'];
			$Title=$Link['title'];
			$Rank=$Link['rank'];
			$Name=$Link['name'];
			$Email=$Link['email'];
			if(empty($Link['description']) && !empty($Description)){
				mysql_query("UPDATE $table_links SET description = '$Description' WHERE id = {$Link['id']}");
			}
		}


		if($Flagging=='Yes'){
			if($isFlagged){
				return $isFlagged;
			}
			$Flag='<a href="'.$_SERVER['REQUEST_URI'].(empty($_GET) ? '?' : '&').'flag='.$Link['id'].'"><sup>Flag</sup></a>';
		}

		return '<a href="'.$URL.'">'.$Title.'</a>'.$Flag.($ShowBy && !empty($Name)? ' by '.$Name : '').($ShowDescription && !empty($Description) ? ' - '.$Description : '');
	}else{
		return 'Link missing a URL';
	}
}
}

function wplinkdir_shortcodes($atts){
	extract(shortcode_atts(array(
		'page' => 'Home',
		'category' => '',
	), $atts));

	if($atts['category']){
		return wplinkdir_displaydir(mysql_real_escape_string($category));
	}

	if(strtolower($page)==strtolower('home')){
		return wplinkdir_displaydir();
	}elseif(strtolower($page)=='add url'){
		return wplinkdir_addsite();
	}elseif(strtolower($page)=='link to us'){
		return wplinkdir_linkus();
	}elseif(strtolower($page)=='search'){
		return wplinkdir_search();
	}
}

add_shortcode('wplinkdir', 'wplinkdir_shortcodes');
add_shortcode('wplink', 'wplinkdir_wplink_shortcode');


function wplinkdir_flag(){
	// Handles flagging of broken or innappropriate links
	global $isFlagged;

	if(is_numeric($_GET['flag'])){
		global $wpdb,$WPLD_Domain,$isFlagged;
		$table_links = WPLD_LINKS_TABLE;;
		$table_cats = WPLD_CATS_TABLE;;

		mysql_query("UPDATE $table_links SET flagged = flagged + 1 WHERE id = {$_GET['flag']}");
		$getAffected=mysql_affected_rows();
		if($getAffected>0){
			$isFlagged=__('The link has been flagged and will be checked by an admin soon, thanks!',$WPLD_Domain);
		}else{
			$isFlagged=__("The link you tried to flag doesn't exist or couldn't be found.",$WPLD_Domain);
		}
	}
	return;
}


// Translation functions used to set up localization and define some translation variables

$WPLD_Domain = 'wplinkdir';
$WPLD_Translation_isSetup = 0;

function wplinkdir_translation_setup(){
	global $WPLD_Domain, $WPLD_Translation_isSetup;

	if($WPLD_Translation_isSetup) {
		return;
	} 
	load_plugin_textdomain($WPLD_Domain, PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)), dirname(plugin_basename(__FILE__)));
}

function wplinkdir_translation(){
	// Defines some translation variables which are used in more than one place

	global $WPLD_Trans,$WPLD_Domain;

	$WPLD_Trans['Yes']=__('Yes',$WPLD_Domain);	// Only used in the admin section when displaying Yes/No menu selections
	$WPLD_Trans['No']=__('No',$WPLD_Domain);

	$WPLD_Trans['Update']=__('Update',$WPLD_Domain);	// Used in the edit links admin page. These two options are checked and shouldn't contain special characters
	$WPLD_Trans['Delete']=__('Delete',$WPLD_Domain);

	$WPLD_Trans['Hidden']=__('Hidden',$WPLD_Domain);	// Used in the menu on the admin page for the Extra Fields option
	$WPLD_Trans['Optional']=__('Optional',$WPLD_Domain);
	$WPLD_Trans['Required']=__('Required',$WPLD_Domain);

	$WPLD_Trans['WebsiteTitle']=__('Website Title:',$WPLD_Domain);		// These options are displayed to the admin and front end user
	$WPLD_Trans['WebsiteURL']=__('Website URL:',$WPLD_Domain);
	$WPLD_Trans['Category']=__('Category:',$WPLD_Domain);
	$WPLD_Trans['Description']=__('Description:',$WPLD_Domain);

	$WPLD_Trans['Captcha']=__('You entered the wrong captcha code. Aren\'t you human? Please use back button and reload.',$WPLD_Domain);	// Shown when the user enters an incorrect captcha code

	$WPLD_Trans['AddedBy']=__('Added By:',$WPLD_Domain);		// Shown on admin screen and detailed information screen
	$WPLD_Trans['DateAdded']=__('Date Added:',$WPLD_Domain);

	$WPLD_Trans['Import']=__('Import Links',$WPLD_Domain);		// Used as menu headers and Submit buttons on the Functions page
	$WPLD_Trans['Export']=__('Export Links',$WPLD_Domain);
	$WPLD_Trans['Cleanse']=__('Cleanse Links',$WPLD_Domain);

	$WPLD_Trans['WPLD']=__('WP Link Directory',$WPLD_Domain);	// Used to display the main menu.
	$WPLD_Trans['WordPressLinkDirectory']=__('WordPress Link Directory',$WPLD_Domain);	// Full name of the script, used in the 'powered by' link
	$WPLD_Trans['WPLD_EditLinks']=__('Edit Links',$WPLD_Domain);		// Used in the sub-menus
	$WPLD_Trans['WPLD_EditCategories']=__('Edit Categories',$WPLD_Domain);
	$WPLD_Trans['WPLD_PremiumLinks']=__('Premium Links',$WPLD_Domain);
	$WPLD_Trans['WPLD_TaggingOptions']=__('Tagging Options',$WPLD_Domain);
	$WPLD_Trans['WPLD_Functions']=__('Functions',$WPLD_Domain);

	$WPLD_Trans['Nobody']=__('Nobody',$WPLD_Domain);		// Used in the options menu for reciprocal requirements
	$WPLD_Trans['Everybody']=__('Everybody',$WPLD_Domain);		// Used as above and below
	$WPLD_Trans['SitesPR']=__('Sites < PR',$WPLD_Domain);		// Displayed to the admin and is directly proceeded by the PR value ie 'Sites < PR3'
}

wplinkdir_translation_setup();
wplinkdir_translation();


function wplinkdir_uninstall(){

	// The uninstall function is no longer used as of v1.2 but if you wish to do a clean uninstall and remove everything
	// Then uncomment the line '// register_deactivation_hook(__FILE__, 'wplinkdir_uninstall');' above then deactivate the plugin.

	global $wpdb;
	$table_links = WPLD_LINKS_TABLE;
	$table_cats = WPLD_CATS_TABLE;

	$wpdb->query("DROP TABLE {$table_links}, {$table_cats}");
	$wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE 'wplinkdir%'");
}


function wplinkdir_upgrade($Version){
	$table_links = WPLD_LINKS_TABLE;
	$table_cats = WPLD_CATS_TABLE;

	if($Version=='1.7.2' || $Version=='1.7.3'){
		mysql_query("ALTER TABLE $table_links ADD `mailed` TINYINT(1) NOT NULL, ADD `premium` INT NOT NULL, ADD `hitsin` INT NOT NULL, ADD `hitsout` INT NOT NULL")or die(mysql_error());
		add_option('wplinkdir_pl_enable','No');
		add_option('wplinkdir_pl_email',get_option('admin_email'));
		add_option('wplinkdir_pl_duration',365);
		add_option('wplinkdir_pl_currency','USD');
		add_option('wplinkdir_pl_cost','5.00');
		add_option('wplinkdir_pl_recip','No');
	}

	$getCats=mysql_query("SELECT * FROM $table_cats");
	while($Cat=mysql_fetch_assoc($getCats)){
		$pretty_name=strtolower(str_replace(' ','-',preg_replace('/[^a-zA-Z0-9 ]/','',$Cat['title'])));
		$pretty_name=str_replace('--','-',str_replace('--','-',$pretty_name));
		mysql_query("UPDATE $table_cats SET title_pretty = '$pretty_name' WHERE id = {$Cat['id']}");
	}
	update_option('wplinkdir_version','1.8');
}


function wplinkdir_init(){
	// The installation function used to set up and activate WP Link Directory. It may also make a call to wplinkdir_upgrade() above.

	global $wpdb,$wp_rewrite;
	$table_cats=WPLD_CATS_TABLE;

	$getTable=mysql_query("SHOW TABLES LIKE '".WPLD_LINKS_TABLE."'");
	$Version=get_option('wplinkdir_version');

	// Check for existing installation and run upgrade script if found else create tables and options
	if(($Version!='' && $Version!='1.8') || @mysql_result($getTable,0,0)){
		wplinkdir_upgrade($Version);
	}else{
		$wpdb->query("CREATE TABLE ".WPLD_LINKS_TABLE." (
			`id` INT NOT NULL AUTO_INCREMENT,
			`name` VARCHAR( 250 ) NOT NULL ,
			`email` VARCHAR( 250 ) NOT NULL ,
			`title` VARCHAR( 250 ) NOT NULL ,
			`url` VARCHAR( 250 ) NOT NULL ,
			`description` VARCHAR( 250 ) NOT NULL ,
			`recip_url` VARCHAR( 250 ) NOT NULL ,
			`category` VARCHAR( 100 ) NOT NULL,
			`pr` INT NOT NULL,
			`pr_recip` INT NOT NULL,
			`date_added` INT NOT NULL,
			`date_modified` INT NOT NULL,
			`flagged` INT NOT NULL,
			`tagged` TINYINT NOT NULL,
			`pending` TINYINT NOT NULL,
			`mailed` TINYINT NOT NULL,
			`premium` INT NOT NULL,
			`hitsin` INT NOT NULL,
			`hitsout` INT NOT NULL,
			PRIMARY KEY id (id),
			UNIQUE KEY url (url))");

		$Now=time();
		$wpdb->query("INSERT INTO ".WPLD_LINKS_TABLE." VALUES('','Sean Bluestone','','SeanBluestone.com','http://www.seanbluestone.com','Resources and articles on SEO, Link Building, Self Improvement and more.','','Blogs & Blogging','3','','$Now','',0,0,0,1,0,0,0)");

		$wpdb->query("CREATE TABLE $table_cats (
			`id` INT NOT NULL AUTO_INCREMENT ,
			`title` VARCHAR( 250 ) NOT NULL ,
			`title_pretty` VARCHAR( 250 ) NOT NULL ,
			`parent` VARCHAR( 250 ) NOT NULL ,
			`description` VARCHAR( 250 ) NOT NULL,
			UNIQUE KEY id (id))");

		$wpdb->query("INSERT INTO $table_cats VALUES('','Blogs & Blogging','blogs-blogging','','All about the fine art of writing nonsense.')");
		$wpdb->query("INSERT INTO $table_cats VALUES('','Games','games','','Video gaming, online games and more.')");

		$TempTitle=ucfirst(str_replace('www.','',$_SERVER['HTTP_HOST']));
		add_option('wplinkdir_captcha','Securimage');
		add_option('wplinkdir_catsperrow',2);
		add_option('wplinkdir_description_size','250');
		add_option('wplinkdir_emailme','Yes');
		add_option('wplinkdir_extended_info','more info');
		add_option('wplinkdir_extrafields','Optional');
		add_option('wplinkdir_flagging','No');
		add_option('wplinkdir_htmlcode','<a href="http://www.'.$TempTitle.'">'.$TempTitle.'</a> - '.get_option('blogdescription'));
		add_option('wplinkdir_htmltags','<b><i><u>');
		add_option('wplinkdir_nofollow','No');
		add_option('wplinkdir_orderby','Pagerank Highest to Lowest');
		if($wp_rewrite->using_permalinks()==true){
			add_option('wplinkdir_permalinks','links');
		}
		add_option('wplinkdir_pl_enable','No');
		add_option('wplinkdir_pl_email',get_option('admin_email'));
		add_option('wplinkdir_pl_duration',365);
		add_option('wplinkdir_pl_currency','USD');
		add_option('wplinkdir_pl_cost','5.00');
		add_option('wplinkdir_pl_recip','No');
		add_option('wplinkdir_poweredby','Yes');
		add_option('wplinkdir_recip_requirement','Nobody');
		add_option('wplinkdir_show_description','No');
		add_option('wplinkdir_show_byline','No');
		add_option('wplinkdir_showpr','Yes');
		add_option('wplinkdir_style','original.css');
		add_option('wplinkdir_tagging','No');
		add_option('wplinkdir_target','_blank');
		add_option('wplinkdir_version','1.8');
		add_option('wplinkdir_your_url','http://'.$_SERVER['HTTP_HOST']);
	}
}


function wplinkdir_styling($Style='original.css'){

	if($Style==''){ $Style=get_option('wplinkdir_style'); }

	$StyleFile=dirname(__FILE__).'/styles/'.$Style;

	if(file_exists($StyleFile)){
		include_once($StyleFile);
	}
}


function wplinkdir_menu(){
	global $WPLD_Trans;
	$links_table = WPLD_LINKS_TABLE;

	$getNewLinks=mysql_query("SELECT COUNT(*) FROM $links_table WHERE pending = 1");
	$NewLinks=mysql_result($getNewLinks,0,0);

	$Tagging=get_option('wplinkdir_tagging');
	add_menu_page($WPLD_Trans['WPLD'], $WPLD_Trans['WPLD'].($NewLinks>0 ? "<span class='update-plugins count-{$NewLinks}'><span class='plugin-count'>{$NewLinks}</span></span>" : ''), 8, __FILE__, 'wplinkdir_admin_page');
	add_submenu_page(__FILE__, $WPLD_Trans['WPLD'], $WPLD_Trans['WPLD'], 8, __FILE__, 'wplinkdir_admin_page'); 
	add_submenu_page(__FILE__, $WPLD_Trans['WPLD'].' - '.$WPLD_Trans['WPLD_EditLinks'],$WPLD_Trans['WPLD_EditLinks'].($NewLinks>0 ? "<span class='update-plugins count-{$NewLinks}'><span class='plugin-count'>{$NewLinks}</span></span>" : ''), 8, 'wplinkdir_editlinks_page', 'wplinkdir_editlinks_page'); 
	if(WPLD_PRO){
		add_submenu_page(__FILE__, $WPLD_Trans['WPLD'].' - '.$WPLD_Trans['WPLD_PremiumLinks'],$WPLD_Trans['WPLD_PremiumLinks'], 8, 'wplinkdir_premiumlinks_page', 'wplinkdir_premiumlinks_page'); 
	}
	add_submenu_page(__FILE__, $WPLD_Trans['WPLD'].' - '.$WPLD_Trans['WPLD_EditCategories'],$WPLD_Trans['WPLD_EditCategories'], 8, 'wplinkdir_category_page', 'wplinkdir_category_page'); 
	add_submenu_page(__FILE__, $WPLD_Trans['WPLD'].' - '.$WPLD_Trans['WPLD_TaggingOptions'],$WPLD_Trans['WPLD_TaggingOptions'], 8, 'wplinkdir_admin_tagging_options', 'wplinkdir_admin_tagging_options'); 
	if($Tagging=='Yes'){
		add_submenu_page(__FILE__, $WPLD_Trans['WPLD'].' - Tagged Links','Tagged Links', 8, 'wplinkdir_admin_tagged_links', 'wplinkdir_admin_tagged_links'); 
	}
	add_submenu_page(__FILE__, $WPLD_Trans['WPLD'].' - '.$WPLD_Trans['WPLD_Functions'],$WPLD_Trans['WPLD_Functions'], 8, 'wplinkdir_page_functions', 'wplinkdir_page_functions'); 
}


function wplinkdir_importexport($Type='import'){
	global $wpdb;

	$getPorts=mysql_query("SELECT * FROM ".($Type=='import' ? $wpdb->prefix."links" : WPLD_LINKS_TABLE));
	$Target=get_option('wplinkdir_target');
	$Rel=get_option('wplinkdir_target');

	While($Port=mysql_fetch_assoc($getPorts)){
		if($Type='import'){
			$Port['pr']=getpagerank($Port['link_url']);
			if($Port['link_visible']=='Y'){ $Port['link_pending']=0; $Port['link_mailed']=1; }else{ $Port['link_pending']=1; $Port['link_mailed']=0; }
			mysql_query("INSERT INTO ".WPLD_LINKS_TABLE." VALUES ('','{$Port['link_owner']}','{$Port['link_email']}','{$Port['link_name']}','{$Port['link_url']}','{$Port['link_description']}','','{$Port['link_category']}','{$Port['pr']}','0','{$Port['link_updated']}','{$Port['link_updated']}','{$Port['link_rating']}','0','{$Port['link_pending']}','{$Port['link_mailed']}','0','0','0')")or $Duplicate=TRUE;
		}else{
			$Port['target']=$Target;
			$Port['rel']=$Rel;
			if($Port['pending']==1){ $Port['pending']='Y'; }else{ $Port['pending']='N'; }
			mysql_query("INSERT INTO ".$wpdb->prefix."links VALUES ('','{$Port['url']}','{$Port['title']}','','{$Port['target']}','{$Port['category']}','{$Port['description']}','{$Port['pending']}','{$Port['name']}','0','{$Port['date_added']}','{$Port['rel']}','','')")or $Duplicate=TRUE;
		}
	}

	echo '<div id="message" class="updated fade"><strong>'.( $Duplicate==TRUE ? __('One or more links were already imported and ') : '' ).mysql_num_rows($getPorts).' Links '.( $Type=='import' ? 'im' : 'ex' ).'ported.</strong></div>';
}


function wplinkdir_page_functions(){
	global $wpdb,$WPLD_Domain,$WPLD_Trans;

	$_Yes=$WPLD_Trans['Yes'];
	$_No=$WPLD_Trans['No'];

	if($_POST['Submit']==$WPLD_Trans['Import']){
		wplinkdir_importexport('import');
	}elseif($_POST['Submit']==$WPLD_Trans['Export']){
		wplinkdir_importexport('export');
	}elseif($_POST['Submit']==$WPLD_Trans['Cleanse']){
		// Perform The Cleanse Action by Checking the Reciprocal URLs for Each Link

		$CleanseDel=$_POST['wplinkdir_cleansedelete'];
		$LinkCountA=$LinkCountB=$DelCount=0;
		$getLinks=mysql_query("SELECT * FROM $links_table WHERE tagged = 0");
		$searchfor='/\<a.*href\="'.preg_quote(get_option('wplinkdir_your_url'),'/').'".*\>.+\<\/a\>/';

		if($recip_option=='Nobody'){
			echo '<div id="message" class="updated fade"><br /><strong>'.__('Your Reciprocal Requirements option is set to Nobody. This means no sites are required to link back to you and therefor there is nothing to check/cleanse.',$WPLD_Domain).'</strong></div>';
		}else{
			if($recip_option=='Everybody'){
				$recip_option=11;
			}

			while($Link=mysql_fetch_assoc($getLinks)){
				$LinkCountA++;

				if($Link['pr']<$recip_option){
					$LinkCountB++;
					$handle=FALSE;
					$handle=fopen($Link['recip_url'],'r');

					if($handle){
						while(!feof($handle)){
							$contents.=fgets($handle, 4096);
						}
						fclose($handle);

						if(!preg_match($searchfor,$contents)){
							$Output.=sprintf(__('We couldn\'t find a reciprocal link on the page %1$s which is the reciprocal URL supplied for %2$s (%3$s).',$WPLD_Domain),$Link['recip_url'],$Link['title'],$Link['url']);
							if($CleanseDel=='Yes'){
								$DelCount++;
								mysql_query("DELETE FROM $links_page WHERE id = {$Link['id']}");
								$Output.=' '.__('The link has been deleted.',$WPLD_Domain);
							}
							$Output.='<br />';
						}
					}else{
						$Output.=sprintf(__('The reciprocal page for %1$s (%2$s) which was at %3$s no longer exists.',$WPLD_Domain),$Link['title'],$Link['url'],$Link['recip_url']);
						if($CleanseDel=='Yes'){
							$Output.=' '.__('The link has been deleted.',$WPLD_Domain);
							$DelCount++;
							mysql_query("DELETE FROM $links_page WHERE id = {$Link['id']}");
						}
						$Output.='<br />';
					}
				}
			}
			echo '<div id="message" class="updated fade">'.$Output."<br /><strong>$LinkCountB out of a total of $LinkCountA links were checked and $DelCount link(s) were deleted.</strong></div>";
		}
	}

	// End cleanse section

	$getImports=mysql_query("SELECT COUNT(*) FROM ".$wpdb->prefix."links");
	$Imports=mysql_result($getImports,0,0);
	$getExports=mysql_query("SELECT COUNT(*) FROM ".WPLD_LINKS_TABLE);
	$Exports=mysql_result($getExports,0,0);

	echo '<div class="wrap"><h2>'.$WPLD_Trans['WPLD_Functions'].'</h2>

	<h2>'.$WPLD_Trans['Import'].'</h2>
	<form method="POST" action="" name="wplinkdir_functions">
	<table class="form-table">
	<tr valign="top">
	<td><br /><input type="submit" name="Submit" value="'.$WPLD_Trans['Import'].'" onclick="return confirmation();"><br />('.$Imports.')</td>
	<td width="70%">'.__('WPLD does not use your existing links stored in WordPress, instead it creates its own table in the database. Using this function you can import all the links you already have in WordPress into WPLD.',$WPLD_Domain).'</td>
	</tr>
	</table>

	<h2>'.$WPLD_Trans['Export'].'</h2>
	<table class="form-table">
	<tr valign="top">
	<td><br /><input type="submit" name="Go" value="'.$WPLD_Trans['Export'].'" onclick="return confirmation();"><br />('.$Exports.')</td>
	<td width="70%">'.__('This performs the opposite action as to above and exports all your links from WPLD into WordPress\' links table.',$WPLD_Domain).'</td>
	</tr>
	</table>

	<h2>'.$WPLD_Trans['Cleanse'].'</h2>
	<table class="form-table">
	<tr valign="top">
	<td><b>'.__('Delete Links?',$WPLD_Domain).'</b><br /><select name="wplinkdir_cleansedelete"><option value="No">'.$_No.'</option><option value="Yes">'.$_Yes.'</option></select></td>
	<td><br /><input type="submit" name="Submit" value="'.$WPLD_Trans['Cleanse'].'" onclick="return confirmation();"></td>
	<td width="70%">'.__('Every now and then you should perform a cleanse which checks all the links in your directory to make sure they comply with your Reciprocal Link requirements. If your Reciprocal Link requires All Sites < PR 4, for example, any sites with < PR 4 will be checked.<br />If you set Delete Links to Yes then links which don\'t meet requirements will be deleted, otherwise they will simply be flagged up for you to check and/or delete manually.',$WPLD_Domain).'</td>
	</tr>
	</table>
	</form>
	</div>';
}


function wplinkdir_premiumlinks_page(){
	global $wpdb,$WPLD_Domain,$WPLD_Trans,$Currencies;

	$cat_table = WPLD_CATS_TABLE;
	$links_table = WPLD_LINKS_TABLE;

	$_Yes=$WPLD_Trans['Yes'];
	$_No=$WPLD_Trans['No'];

	echo '<div class="wrap"><h2>'.$WPLD_Trans['WPLD_PremiumLinks'].'</h2>
	<form method="post" action="options.php" name="premium_links">';

	wp_nonce_field('update-options');

	echo '<table class="form-table"><tr valign="top">
	<tr valign="top"><td><b>'.__('Enable Premium Links?',$WPLD_Domain).'</b></td>';

	if(get_option('wplinkdir_pl_enable')=='No'){
		$No=' SELECTED';
	}else{
		$Yes=' SELECTED';
	}

	echo '<td><select name="wplinkdir_pl_enable"><option value="Yes"'.$Yes.'>'.$_Yes.'</option><option value="No"'.$No.'>'.$_No.'</option></select></td>
	<td>'.__('Premium Links offer the user the option to pay a fixed amount to have their link displayed at the top of their selected page, optionally without the need for a reciprocal link, and/or with a dofollow link if your directory is nofollow.',$WPLD_Domain).'</td>
	</tr>

	<tr valign="top"><td><b>'.__('PayPal Email Address',$WPLD_Domain).'</b></td>
	<td><input type="text" name="wplinkdir_pl_email" value="'.get_option('wplinkdir_pl_email').'"></td>
	<td>'.__('The PayPal email address that people should send payment to.',$WPLD_Domain).'</td>
	</tr>

	<tr valign="top"><td><b>'.__('Return Page',$WPLD_Domain).'</b></td>
	<td><input type="text" name="wplinkdir_pl_returnpage" value="'.get_option('wplinkdir_pl_returnpage').'"></td>
	<td>'.__('Enter the full URL of the page where your link directory is hosted (i.e. the post or page which includes [wplinkdir]).',$WPLD_Domain).'</td>
	</tr>

	<tr valign="top"><td><b>'.__('Test Mode?',$WPLD_Domain).'</b></td>';

	unset($Yes,$No);
	if(get_option('wplinkdir_pl_testmode')=='No'){
		$No=' SELECTED';
	}else{
		$Yes=' SELECTED';
	}

	echo '<td><select name="wplinkdir_pl_testmode"><option value="Yes"'.$Yes.'>'.$_Yes.'</option><option value="No"'.$No.'>'.$_No.'</option></select></td>
	<td>'.__('If set to yes the payment button will direct to https://sandbox.paypal.com enabling you to test and verify that payments will work.',$WPLD_Domain).'</td>
	</tr>

	<tr valign="top"><td><b>'.__('Duration',$WPLD_Domain).'</b></td>
	<td><input type="text" name="wplinkdir_pl_duration" value="'.get_option('wplinkdir_pl_duration').'"> Days</td>
	<td>'.__('Enter the number of days premium links will last until they are demoted to regular links.',$WPLD_Domain).'</td>
	</tr>

	<tr><td><b>'.__('Default Currency',$WPLD_Domain).'</b></td>
	<td><select name="wplinkdir_pl_currency">';

	$Currency=get_option('wplinkdir_pl_currency');

	foreach($Currencies as $Value => $Display){
		echo '<option value="'.$Value.'"'.($Currency==$Value ? ' SELECTED' : '').'>'.$Display.' '.$Value.'</option>';
	}

	echo '</select></td>
	<td>'.__('Your default currency.',$WPLD_Domain).'</td>
	</tr>

	<tr valign="top"><td><b>'.__('Cost',$WPLD_Domain).'</b></td>
	<td><input type="text" name="wplinkdir_pl_cost" value="'.get_option('wplinkdir_pl_cost').'"></td>
	<td>'.__('Enter the cost of a premium link.',$WPLD_Domain).'</td>
	</tr>

	<tr valign="top"><td><b>'.__('Reciprocal Link Still Required?',$WPLD_Domain).'</b></td>';

	unset($Yes,$No);
	if(get_option('wplinkdir_pl_reciprocal')=='No'){
		$No=' SELECTED';
	}else{
		$Yes=' SELECTED';
	}

	echo '<td><select name="wplinkdir_pl_reciprocal"><option value="Yes"'.$Yes.'>'.$_Yes.'</option><option value="No"'.$No.'>'.$_No.'</option></select></td>
	<td>'.__('If set to Yes then a reciprocal link will still be required for Premium Links.',$WPLD_Domain).'</td>
	</tr>

	<tr><td colspan="3"><input type="hidden" name="action" value="update" /><input type="submit" name="Submit" value="'.__('Save Changes',$WPLD_Domain).'" /></td></tr>

	<input type="hidden" name="page_options" value="wplinkdir_pl_enable,wplinkdir_pl_email,wplinkdir_pl_returnpage,wplinkdir_pl_testmode,wplinkdir_pl_duration,wplinkdir_pl_currency,wplinkdir_pl_cost,wplinkdir_pl_reciprocal" />
	</table>
	</form>
	</div>';

}


function wplinkdir_admin_tagging_options(){
	global $wpdb,$WPLD_Domain,$WPLD_Trans;

	$cat_table = WPLD_CATS_TABLE;
	$links_table = WPLD_LINKS_TABLE;

	$_Yes=$WPLD_Trans['Yes'];
	$_No=$WPLD_Trans['No'];

	$Tagging=get_option('wplinkdir_tagging');
	$Flagging=get_option('wplinkdir_flagging');
	$ShowDescription=get_option('wplinkdir_show_description');
	$ShowByline=get_option('wplinkdir_show_byline');

	echo '<div class="wrap"><h2>'.$WPLD_Trans['WPLD_TaggingOptions'].'</h2>
	<form method="post" action="options.php" name="tagged_links_options">';

	wp_nonce_field('update-options');

	echo '<table class="form-table"><tr valign="top">
	<tr valign="top"><td><b>'.__('Enable tagging?',$WPLD_Domain).'</b></td>';

	if($Tagging=='No'){
		$No=' SELECTED';
	}else{
		$Yes=' SELECTED';
	}

	echo '<td><select name="wplinkdir_tagging"><option value="Yes"'.$Yes.'>'.$_Yes.'</option><option value="No"'.$No.'>'.$_No.'</option></select></td>
	<td>'.__('Tagging is an option to allow users who can create posts on your WordPress blog to enter links like [link url="http://www.google.com"] which would convert to &#60;a href="http://www.google.com"&#62;http://www.google.com&#60;/a&#62;. See below for more information.',$WPLD_Domain).'</td>
	</tr>
	<tr valign="top"><td><b>'.__('Enable flagging?',$WPLD_Domain).'</b></td>';

	unset($Yes,$No);
	if($Flagging=='No'){
		$No=' SELECTED';
	}else{
		$Yes=' SELECTED';
	}

	echo '<td><select name="wplinkdir_flagging"><option value="Yes"'.$Yes.'>'.$_Yes.'</option><option value="No"'.$No.'>'.$_No.'</option></select></td>
	<td>'.__('If tagging is enabled then flagging can also be enabled. If so it will show a <sup>Flag</sup> link which users can click. Flagged links show up in the admin panel with red highlighting.',$WPLD_Domain).'</td>
	</tr>
	<tr valign="top"><td><b>'.__('Show Description?',$WPLD_Domain).'</b></td>';

	unset($Yes,$No);
	if($ShowDescription=='No'){
		$No=' SELECTED';
	}else{
		$Yes=' SELECTED';
	}

	echo '<td><select name="wplinkdir_show_description"><option value="Yes"'.$Yes.'>'.$_Yes.'</option><option value="No"'.$No.'>'.$_No.'</option></select></td>
	<td>'.__('If set to yes then the description, where set, will be showed next to the link in the style \' - description\'',$WPLD_Domain).'</td>
	</tr>
	<tr valign="top"><td><b>'.__('Show By Line?',$WPLD_Domain).'</b></td>';

	unset($Yes,$No);
	if($ShowByline=='No'){
		$No=' SELECTED';
	}else{
		$Yes=' SELECTED';
	}

	echo '<td><select name="wplinkdir_show_byline"><option value="Yes"'.$Yes.'>'.$_Yes.'</option><option value="No"'.$No.'>'.$_No.'</option></select></td>
	<td>'.__('If set to yes a by-line will be inserted with each tagged link. This simply shows \'by xxx\' next to the link where xxx is the name of the person who created the [link] tag.',$WPLD_Domain).'</td>
	</tr>
	<tr><td colspan="3"><input type="hidden" name="action" value="update" /><input type="submit" name="Submit" value="'.__('Save Changes',$WPLD_Domain).'" /></td></tr>
	</table>
	<input type="hidden" name="page_options" value="wplinkdir_show_byline,wplinkdir_show_description,wplinkdir_flagging,wplinkdir_tagging" /><br /><br />
	</form>

	<table class="form-table"><tr><td>
	WP Link Directory also offers [link] tags which are a customized way of handling links submitted by anyone who has access to writing their own posts on your blog.<br />
	It works by converting a [link] to an actual link. For example [link url="http://www.google.com"] would convert to &#60;a href="http://www.google.com"&#62;http://www.google.com&#60;/a&#62;<br/><br />

	As a minimum the [link] tag must contain the url attribute. If a url isn\'t supplied then the [link] tag wont be converted. However, you can also use the title attribute like [link url="http://www.google.com" title="Google"] which will convert to &#60;a href="http://www.google.com"&#62;Google&#60;/a&#62;<br /><br />

	Finally, your users can also create a description for the tag using the description attribute. You can do this two ways.<br />
	Example A: [link url="http://www.google.com" title="Google" description="A very quick search engine."].<br />
	Example B: [link url="http://www.google.com" title="Google"]A very quick search engine.[/link].<br />
	Both of these examples would convert to &#60;a href="http://www.google.com"&#62;Google&#60;/a&#62; - A very fast search engine.<br /><br />

	When someone creates a [link] tag for the first time the link is checked to see if it exists and added to the directory. If it doesn\'t exist it will be flagged up for the admin to check later. The pagerank of the site is also collected along with the username and email of the person who created the [link] tag. All of this information is available from the Tagged Links section.<br />
	Finally if a person creates a post with a [link] tag but doesn\'t supply a description another user can come along later, edit the post and add a description. When they save the page the description will be added.
	</td></tr></table></div>';
}



function wplinkdir_admin_tagged_links(){
	// Admin page for Tagged Links

	global $wpdb,$WPLD_Domain,$WPLD_Trans;

	$cat_table = WPLD_CATS_TABLE;
	$links_table = WPLD_LINKS_TABLE;

	wplinkdir_styling(get_option('wplinkdir_style'));
	$page='<div class="wrap">';

	if($_POST['Submit']=='Delete'){
		$id=$_POST['entry_id'];
		if(!empty($id)){
			mysql_query("DELETE FROM $links_table WHERE id = $id");
			$page.='<div id="message" class="updated fade"><b>Link deleted</b></div>';
		}
	}elseif($_POST['Submit']=='Update'){

		$id=$_POST['entry_id'];
		$oldurl=$_POST['entry_oldurl'];
		$url=$_POST['entry_url'];
		$title=$_POST['entry_title'];
		$description=$_POST['entry_description'];
		$Now=time();

		if(strtolower($oldurl)!=strtolower($url)){
			$Flag=', flagged = 0';
		}else{
			$Flag='';
		}

		if(!empty($id) && !empty($url)){
			$page.='<div id="message" class="updated fade"><b>'.$title.' Link Updated</b></div>';
			mysql_query("UPDATE $links_table SET url = '$url', title = '$title', description = '$description', date_modified = $Now{$Flag} WHERE id = $id")or die(mysql_error());
		}else{
			$page.='<div id="message" class="updated fade"><b>Unable to update link.</b></div>';
		}
	}

	$getLinks=mysql_query("SELECT * FROM $links_table WHERE tagged = 1 ORDER BY flagged DESC LIMIT 500");


	$page.='<h2>All Links</h2>'.confirm_request(__('Are you sure?',$WPLD_Domain)).'
	<table class="form-table"><tr><th>Title</th><th>URL</th><th>Description</th><th>Flags<br />PR</th><th>Added</th><th>Modified</th>';

	while($Link=@mysql_fetch_assoc($getLinks)){
		$x++;

		$Width=$Link['pr']*4;

		if($Link['flagged']>=2){
			if($Link['flagged']>=4){
				$b='<b>'; $eb='</b>';
			}else{
				$b=$eb='';
			}
			$Link['flagged']='<font color="red">'.$b.$Link['flagged'].$eb.'</font>';
		}

		$page.='<tr><td><form method="POST" action="" name="update_link_form_'.$x.'">
		<input type="hidden" name="entry_id" value="'.$Link['id'].'"><input type="hidden" name="entry_oldurl" value="'.$Link['url'].'">

		#'.$x.'. <b><a target="_blank" href="'.$Link['url'].'">'.$Link['title'].'</a></td>
		<td>'.$Link['url'].'</td>
		<td rowspan="2"><textarea rows="4" name="entry_description">'.$Link['description'].'</textarea></td>
		<td>Flags: '.$Link['flagged'].'</td>
		<td>'.date('jS F y',$Link['date_modified']).(!empty($Link['name']) ? ' by '.$Link['name'].' '.$Link['email'] : '').'<br /></td>
		<td>'.date('jS F y',$Link['date_added']).'</td>
		<tr>
		<td><input type="text" name="entry_title" value="'.$Link['title'].'"></td>
		<td><input type="text" name="entry_url" value="'.$Link['url'].'"></td>
		<td><div class="wpld_pr">PR: '.$Link['pr'].'<div class="wpld_prg"><div class="wpld_prb" style="width: '.$Width.'px"></div></div></div></td>
		<td><input type="submit" name="Submit" value="Delete" onclick="return confirmation();"></td>
		<td><input type="submit" name="Submit" value="Update"></form></td>
		</tr>';
	}

	$page.='</table>';

	echo $page;
}


function wplinkdir_admin_page(){
	global $wpdb,$WPLD_Domain,$WPLD_Trans;

	$_Yes=$WPLD_Trans['Yes'];
	$_No=$WPLD_Trans['No'];

	$cat_table = WPLD_CATS_TABLE;
	$links_table = WPLD_LINKS_TABLE;
	$recip_option=get_option('wplinkdir_recip_requirement');

	if(get_option('wplinkdir_emailme')=='No'){
		$No=' SELECTED';
	}else{
		$Yes=' SELECTED';
	}

	echo '<div class="wrap"><h2>'.$WPLD_Trans['WPLD'].'</h2>

	<form method="post" action="options.php">';

	wp_nonce_field('update-options');

	echo '<table class="form-table">
	<tr><td colspan="3"><h2>Reciprocal Options</h2></td></tr>
	<tr valign="top"><td><b>'.__('Your URL',$WPLD_Domain).'</b></td>
	<td><input type="text" name="wplinkdir_your_url" value="'.get_option('wplinkdir_your_url').'" /></td>
	<td>'.__('Your homepage or the URL you want people to link to',$WPLD_Domain).'</td>
	</tr>
	<tr><td><b>'.__('Require Reciprocal Links From',$WPLD_Domain).'</b></td>
	<td><select name="wplinkdir_recip_requirement">';

	$recip_options=array($WPLD_Trans['Nobody']=>'Nobody','1','2','3','4','5','6','7','8','9',$WPLD_Trans['Everybody']=>'Everybody');

	$current_recip_option=get_option('wplinkdir_recip_requirement');

	foreach($recip_options as $option => $value){
		if($current_recip_option==$value){
			$Extra=' SELECTED';
		}else{
			$Extra='';
		}

		if(is_numeric($option)){
			$optionN=$WPLD_Trans['SitesPR'].$option;
		}else{
			$optionN=$WPLD_Trans[$option];
		}
		echo "<option value=\"{$option}\"{$Extra}>{$optionN}</option>";
	}

	echo '</select></td>
	<td>'.__('Sites of these specifications will be required to link back to you to add themselves to your directory.',$WPLD_Domain).'</td>
	</tr>
	<tr valign="top"><td><b>'.__('Reciprocal Link HTML',$WPLD_Domain).'</b></td>
	<td><textarea rows="3" cols="45" name="wplinkdir_htmlcode">'.get_option('wplinkdir_htmlcode').'</textarea></td>
	<td>'.__('Enter the HTML people should use to link to your site. This will be displayed on the Link To Us page.',$WPLD_Domain).'</td>
	</tr>
	<tr valign="top"><td><b>'.__('Allow HTML in Description',$WPLD_Domain).'</b></td>
	<td><input type="text" name="wplinkdir_htmltags" value="'.get_option('wplinkdir_htmltags').'"></td>
	<td>'.__('People adding links to your directory will be able to use these HTML tags in their description. If you want to enable bold text, images and linking, for example, you would enter \'&#60;b&#62;&#60;a&#62;&#60;img&#62;\'',$WPLD_Domain).'</td>
	</tr>

	<tr><td colspan="3"><h2>Link Options</h2></td></tr>
	<tr><td><b>'.__('Order Links By',$WPLD_Domain).'</b></td>
	<td><select name="wplinkdir_orderby">';

	$WPLD_Trans['OrderA']='Newest First';
	$WPLD_Trans['OrderB']='Oldest First';
	$WPLD_Trans['OrderC']='Pagerank Highest to Lowest';
	$WPLD_Trans['OrderD']='Pagerank Lowest to Highest';
	$WPLD_Trans['OrderE']='Alphabetical A to Z';
	$WPLD_Trans['OrderF']='Alphabetical Z to A';

	$OrderBy=array($WPLD_Trans['OrderA']=>'date_added DESC',$WPLD_Trans['OrderB']=>'date_added ASC',$WPLD_Trans['OrderC']=>'pr DESC',$WPLD_Trans['OrderD']=>'pr ASC',$WPLD_Trans['OrderE']=>'title ASC',$WPLD_Trans['OrderF']=>'title DESC');

	$Order=get_option('wplinkdir_orderby');

	foreach($OrderBy as $option => $value){
		if($Order==$value){
			$Extra=' SELECTED';
		}else{
			$Extra='';
		}

		echo "<option value=\"{$value}\"{$Extra}>{$option}</option>";
	}

	echo '</select></td>
	<td>'.__('The order in which to display links in your directory.',$WPLD_Domain).'</td>
	</tr>

	<tr valign="top"><td><b>'.__('Detailed Info',$WPLD_Domain).'</b></td>
	<td><input type="text" name="wplinkdir_extended_info" value="'.get_option('wplinkdir_extended_info').'"></td>
	<td>'.__('By default a <i>read more</i> link is shown for each link where more detailed information about the link is shown. You can change the text of this link or leave this field blank to disable it.',$WPLD_Domain).'</td>
	</tr>
	<tr valign="top"><td><b>'.__('Flagging',$WPLD_Domain).'</b></td>
	<td><input type="text" name="wplinkdir_link_flagging" value="'.get_option('wplinkdir_link_flagging').'"></td>
	<td>'.__('By default a <i>flag link</i> link is shown for each link which the reader can click to flag the link (i.e. if the link is broken). You can change the text of this link or leave this field blank to disable it.',$WPLD_Domain).'</td>
	</tr>
	<tr valign="top"><td><b>'.__('Display PageRank',$WPLD_Domain).'</b></td>
	<td><select name="wplinkdir_showpr">';

	unset($Yes,$No);
	if(get_option('wplinkdir_showpr')=='Yes'){
		$Yes=' SELECTED';
	}else{
		$No=' SELECTED';
	}

	echo "<option value=\"Yes\"{$Yes}>{$_Yes}</option><option value=\"No\"{$No}>{$_No}</option>";
	echo '</select></td><td>'.__('If you don\'t want to display the PR bar next to links set this to No.',$WPLD_Domain).'</td></tr>

	<tr valign="top"><td><b>'.__('Link Target',$WPLD_Domain).'</b></td>
	<td><select name="wplinkdir_target">';

	unset($Yes,$No);
	if(get_option('wplinkdir_target')=='_blank'){
		$Blank=' SELECTED';
	}else{
		$Self=' SELECTED';
	}

	echo "<option value=\"_blank\"{$Blank}>_blank</option><option value=\"_self\"{$Self}>_self</option>";

	echo '</td>
	<td>'.__('_blank will open links in a new window whereas _self will open links on the same page. See http://www.w3.org/TR/1999/REC-html401-19991224/types.html#type-frame-target for more information.',$WPLD_Domain).'</td></tr>

	<tr valign="top"><td><b>'.__('Use NoFollow?',$WPLD_Domain).'</b></td>
	<td><select name="wplinkdir_nofollow">';

	unset($Yes,$No);
	if(get_option('wplinkdir_nofollow')=='Yes'){
		$Yes=' SELECTED';
	}else{
		$No=' SELECTED';
	}

	echo "<option value=\"Yes\"{$Yes}>{$_Yes}</option><option value=\"No\"{$No}>{$_No}</option>";

	unset($Yes,$No);

	echo '</td>
	<td>'.__('NoFollow is an HTML attribute that can be used in links. It tells Google and other search engines to ignore the link and not follow it (<a href="http://en.wikipedia.org/wiki/Nofollow">read more</a>). Turning NoFollow on will add rel="nofollow" to all your links.',$WPLD_Domain).'</td></tr>

	<tr valign="top"><td><b>'.__('Multiple Links From Same Domain?',$WPLD_Domain).'</b></td>
	<td><select name="wplinkdir_subdomains">';

	unset($Yes,$No);
	if(get_option('wplinkdir_subdomains')=='Yes'){
		$Yes=' SELECTED';
	}else{
		$No=' SELECTED';
	}

	echo "<option value=\"Yes\"{$Yes}>{$_Yes}</option><option value=\"No\"{$No}>{$_No}</option>";

	unset($Yes,$No);

	echo '</td>
	<td>'.__('If set to Yes then multiple links from the same domain will be allowed. I.e. http://www.google.com http://images.google.com and http://www.google.com/preferences would all be allowed.',$WPLD_Domain).'</td></tr>

	<tr><td colspan="3"><h2>Category Options</h2></td></tr>

	<tr valign="top"><td><b>'.__('Categories Per Row',$WPLD_Domain).'</b></td>
	<td><input type="text" name="wplinkdir_catsperrow" value="'.get_option('wplinkdir_catsperrow').'"></td>
	<td>'.__('When displaying the categories on the front page of your directory, this is how many will be displayed per row.',$WPLD_Domain).'</td>
	</tr>

	<tr valign="top"><td><b>'.__('Show Counts?',$WPLD_Domain).'</b></td>';

	unset($Yes,$No);
	if(get_option('wplinkdir_show_numbers')=='No'){
		$No=' SELECTED';
	}else{
		$Yes=' SELECTED';
	}

	echo '<td><select name="wplinkdir_show_numbers"><option value="No"'.$No.'>'.$_No.'</option><option value="Yes"'.$Yes.'>'.$_Yes.'</option></select></td>
	<td>'.__('On the front page of your directory you can chose to show or hide the number of links in each category. I.e. \'Links (23)\' vs \'Links\'.',$WPLD_Domain).'</td>
	</tr>

	<tr><td colspan="3"><h2>"Add URL" Page Options</h2></td></tr>

	<tr><td><b>'.__('Require Approval?',$WPLD_Domain).'</b></td>
	<td><select name="wplinkdir_approval">';

	$WPLD_Trans['ApproveA']='Require Approval For All Links';
	$WPLD_Trans['ApproveB']="Require Approval For Links That Don't Meet Requirements";
	$WPLD_Trans['ApproveC']='Do Not Require Approval';

	$Approve=array($WPLD_Trans['ApproveA'],$WPLD_Trans['ApproveB'],$WPLD_Trans['ApproveC']);

	$ABC='ABC'; $x=0;
	$Approval=get_option('wplinkdir_approval');

	foreach($Approve as $option){
		if($Approval==$option){
			$Extra=' SELECTED';
		}else{
			$Extra='';
		}

		echo "<option value=\"{$option}\"{$Extra}>{$ABC[$x]}. {$option}</option>";
		$x++;
	}

	echo '</select></td>
	<td>'.__('Links can be held in a pending queue before being displayed to the public. Option B. will mean that if a link is submitted which doesn\'t meet your reciprocal link requirements it will be placed in the queue for approval, otherwise they will be thrown out.',$WPLD_Domain).'</td>
	</tr>

	<tr><td><b>'.__('Extra Fields',$WPLD_Domain).'</b></td>
	<td><select name="wplinkdir_extrafields">';

	$ExtraFieldOptions=array('Hidden'=>$WPLD_Trans['Hidden'],'Optional'=>$WPLD_Trans['Optional'],'Required'=>$WPLD_Trans['Required']);
	$ExtraFields=get_option('wplinkdir_extrafields');

	unset($Extra);
	foreach($ExtraFieldOptions as $option => $value){
		if($ExtraFields==$option){
			$Extra=' SELECTED';
		}else{
			$Extra='';
		}

		echo "<option value=\"{$option}\"{$Extra}>{$value}</option>";
	}

	echo '</select></td>
	<td>'.__('You can include Name and E-Mail fields on your Add URL page. Setting to Hidden will not show these fields, Optional will display but not require them and Required will mean that people cannot add their link without supplying a name and email address.',$WPLD_Domain).'</td>
	</tr>

	<tr valign="top"><td><b>'.__('Description Size',$WPLD_Domain).'</b></td>
	<td><input type="text" name="wplinkdir_description_size" value="'.get_option('wplinkdir_description_size').'"></td>
	<td>'.__('The amount of characters allowed in the description field. Leave blank for no limit.',$WPLD_Domain).'</td>
	</tr>
	<tr valign="top"><td><b>'.__('CAPTCHA?',$WPLD_Domain).'</b></td><td><select name="wplinkdir_captcha">';

	$CaptchaOptions=array('Securimage','Captchas.net','Do Not Use CAPTCHA');
	$Captcha=get_option('wplinkdir_captcha');

	unset($Extra);
	foreach($CaptchaOptions as $option => $value){
		if($Captcha==$value){
			$Extra=' SELECTED';
		}else{
			$Extra='';
		}

		echo "<option value=\"{$value}\"{$Extra}>{$value}</option>";
	}

	echo '</select></td>
	<td>'.__('Captcha is a form of checking that the user is human and not a spam-bot. Enabling captcha displays an image on your Add URL page that the user must enter to have their link accepted.',$WPLD_Domain).'</td>
	</tr>
	<tr valign="top"><td><b>'.__('Email Me?',$WPLD_Domain).'</b></td>';

	unset($Yes,$No);
	if(get_option('wplinkdir_emailme')=='No'){
		$No=' SELECTED';
	}else{
		$Yes=' SELECTED';
	}

	echo '<td><select name="wplinkdir_emailme"><option value="No"'.$No.'>'.$_No.'</option><option value="Yes"'.$Yes.'>'.$_Yes.'</option></select></td>
	<td>'.__('If set to Yes the script will send you an email (to the WordPress admin email address) each time a new link is added to your directory.',$WPLD_Domain).'</td>
	</tr>';

	if(get_option('wplinkdir_approval')!='Do Not Require Approval'){
		echo '<tr valign="top"><td><b>'.__('Email Them?',$WPLD_Domain).'</b></td>';

		unset($Yes,$No);
		if(get_option('wplinkdir_emailthem')=='No'){
			$No=' SELECTED';
		}else{
			$Yes=' SELECTED';
		}

		echo '<td><select name="wplinkdir_emailthem"><option value="No"'.$No.'>'.$_No.'</option><option value="Yes"'.$Yes.'>'.$_Yes.'</option></select></td>
		<td>'.__('If set to Yes the script will send an email to the person who added the link when you approve it for the first time.',$WPLD_Domain).'</td>
		</tr>';
	}

	echo '<tr><td colspan="3"><h2>Other Options</h2></td></tr>
	<tr valign="top"><td><b>'.__('Style File',$WPLD_Domain).'</b></td><td><select name="wplinkdir_style">';

	$Style=get_option('wplinkdir_style');

	if ($handle = opendir(dirname(__FILE__).'/styles')) {
		while (false !== ($file = readdir($handle))) {
			if (strpos($file,'.css')) {
				echo '<option value="'.$file.'"'.($Style == $file ? ' SELECTED' : '' ).'>'.$file.'</option>';
				//$Styles[]=$file;
			}
		}
		closedir($handle);
	}

	echo '</select></td><td>Style files (.css files) are contained in the <i>styles</i> directory and can be edited to change the look of your directory.</td></tr>

	<tr valign="top"><td><b>'.__('Permalinks',$WPLD_Domain).'</b></td>
	<td><input type="text" name="wplinkdir_permalinks" value="'.get_option('wplinkdir_permalinks').'"></td>
	<td>'.__('If you are using custom permalinks in WordPress then WP Link Directory can adopt pretty permalinks to make your link directory look better to search engines. If you use the default "links" option then http://www.yoursite.com/wordpress/links/ will be the directory homepage. You must have custom permalinks enabled for this to work and \'/%postname%\' will not work. \'/%category%/%postname%\' is a good working example. If for some reason you don\'t wish to use permalinks you can leave this blank.',$WPLD_Domain).'</td>
	</tr>
	<tr><td><b>'.__('Track Hits In?',$WPLD_Domain).'</b></td><td><select name="wplinkdir_track_hitsin">';

	unset($Yes,$No);
	if(get_option('wplinkdir_track_hitsin')=='Yes'){
		$Yes=' SELECTED';
	}else{
		$No=' SELECTED';
	}

	echo "<option value=\"Yes\"{$Yes}>Yes</option><option value=\"No\"{$No}>No</option>";
	echo '</select></td><td>'.__('Tracks incoming hits for each site. Note that on sites receiving a large amount of traffic from other sites this may cause things to slow down slightly.',$WPLD_Domain).'</td></tr>

	<tr><td><b>'.__('Track Hits Out?',$WPLD_Domain).'</b></td><td><select name="wplinkdir_track_hitsout">';

	unset($Yes,$No);
	if(get_option('wplinkdir_track_hitsout')=='Yes'){
		$Yes=' SELECTED';
	}else{
		$No=' SELECTED';
	}

	echo "<option value=\"Yes\"{$Yes}>Yes</option><option value=\"No\"{$No}>No</option>";
	echo '</select></td><td>'.__('Tracks outgoing hits for each site. Note that this uses a redirect which means the linked site wont get any pagerank from you.',$WPLD_Domain).'</td></tr>

	<tr><td><b>'.__('Display Powered By?',$WPLD_Domain).'</b></td><td><select name="wplinkdir_poweredby">';

	unset($Yes,$No);
	if(get_option('wplinkdir_poweredby')=='Yes'){
		$Yes=' SELECTED';
	}else{
		$No=' SELECTED';
	}

	echo "<option value=\"Yes\"{$Yes}>Yes</option><option value=\"No\"{$No}>No</option>";
	echo '</select></td><td>'.__('I offer WP Link Directory for free, for everyone, and a link back to my site isn\'t required, but it is appreciated. If selected this will insert a small link on the home page of the directory to the plugins homepage.',$WPLD_Domain).'</td></tr>

	<tr><td colspan="3"><input type="hidden" name="action" value="update" /><input type="submit" name="Submit" value="'.__('Save Changes',$WPLD_Domain).'" /></td></tr>

<input type="hidden" name="page_options" value="wplinkdir_poweredby,wplinkdir_track_hitsout,wplinkdir_track_hitsin,wplinkdir_permalinks,wplinkdir_style,wplinkdir_emailthem,wplinkdir_emailme,wplinkdir_captcha,wplinkdir_description_size,wplinkdir_extrafields,wplinkdir_approval,wplinkdir_show_numbers,wplinkdir_catsperrow,wplinkdir_subdomains,wplinkdir_nofollow,wplinkdir_target,wplinkdir_showpr,wplinkdir_link_flagging,wplinkdir_extended_info,wplinkdir_orderby,wplinkdir_htmltags,wplinkdir_htmlcode,wplinkdir_recip_requirement,wplinkdir_your_url" />
	</table>
	</form><br /><br />'.confirm_request(__('Are you sure?',$WPLD_Domain)).'
	</div>';
}


function wplinkdir_functions(){

	echo '<h2>'.__('Import Linksee',$WPLD_Domain).'</h2>
	<form method="POST" action="" name="wplinkdir_import">
	<table class="form-table">
	<tr valign="top">
	<td><br /><input type="submit" name="action" value="'.__('Import',$WPLD_Domain).'" onclick="return confirmation();"></td>
	<td width="70%">'.__('You can Import all the links currently stored in WordPress\' links table using this button. Your WordPress Links will be copied but will not be deleted.',$WPLD_Domain).'</td>
	</tr>
	</table>
	</form>

	<h2>'.__('Export Links',$WPLD_Domain).'</h2>
	<form method="POST" action="" name="wplinkdir_export">
	<table class="form-table">
	<tr valign="top">
	<td><br /><input type="submit" name="action" value="'.__('Cleanse',$WPLD_Domain).'" onclick="return confirmation();"></td>
	<td width="70%">'.__('This will perform the opposite action to above and copy all your links from WordPress Link Directory into WordPress\' default links table. Again, no links will be deleted.',$WPLD_Domain).'</td>
	</tr>
	</table>
	</form>

	<h2>'.__('Cleanse Links',$WPLD_Domain).'</h2>
	<form method="POST" action="" name="wplinkdir_cleanse">
	<table class="form-table">
	<tr valign="top">
	<td><b>'.__('Delete Links?',$WPLD_Domain).'</b><br /><select name="wplinkdir_cleansedelete"><option value="No">'.$_No.'</option><option value="Yes">'.$_Yes.'</option></select></td>
	<td><br /><input type="submit" name="action" value="'.__('Cleanse',$WPLD_Domain).'" onclick="return confirmation();"></td>
	<td width="70%">'.__('Every now and then you should perform a cleanse which checks all the links in your directory to make sure they comply with your Reciprocal Link requirements. If your Reciprocal Link requires All Sites < PR 4, for example, any sites with < PR 4 will be checked.<br />If you set Delete Links to Yes then links which don\'t meet requirements will be deleted, otherwise they will simply be flagged up for you to check and/or delete manually.',$WPLD_Domain).'</td>
	</tr>
	</table>
	</form>';
}


function wplinkdir_editlinks_page(){
	global $wpdb,$wp_rewrite,$Link,$WPLD_Trans,$WPLD_Domain;

	$cat_table = WPLD_CATS_TABLE;
	$links_table = WPLD_LINKS_TABLE;
	$Tags=get_option('wplinkdir_htmltags');

	// Used for translation purposes. Displayed on the submit buttons
	$_AddLink=__('Add Link',$WPLD_Domain);

	echo '<div class="wrap"><h2>'.$_AddLink.'</h2>';

	if($_POST['Submit']==$_AddLink){

		$category=$wpdb->escape($_POST['entry_category']);
		$url=$wpdb->escape($_POST['entry_url']);

		$handle1=@fopen($_POST['entry_url'],'r');
		if($handle1){
			$url=$wpdb->escape($_POST['entry_url']);
			fclose($handle1);
		}else{
			echo '<div id="message" class="updated fade"><strong>'.__('We couldn\'t open the URL you supplied but the link was added anyway. You may want to check it to ensure it exists.',$WPLD_Domain).'</strong></div>';
		}

		$description=$_POST['entry_description'];
		$title=$wpdb->escape(htmlspecialchars($_POST['entry_title']));

		get_currentuserinfo();
		$name=$userdata->display_name;
		$email=$userdata->user_email;

		$Rank=getpagerank($url);

		$now=time();
		mysql_query("INSERT INTO $links_table VALUES('','$name','$email','$title','$url','$description','','$category','$Rank','','$now','',0,0,0,1,0,0,0)");

		echo '<div id="message" class="updated fade"><strong>'.$title.' '.__('Link Added',$WPLD_Domain).'</strong></div>';
	}

	if($_POST['Submit']==$WPLD_Trans['Update']){

		// Mail the person who submitted the link
		$getMailed=mysql_query("SELECT email,mailed FROM $links_table WHERE id = {$_POST['id']}");
		$Mailed=mysql_result($getMailed,0,1);
		$Email=mysql_result($getMailed,0,0);

		if(get_option('wplinkdir_emailthem')=='Yes' && $Email!='' && $Mailed==0){
			$LinkOut=(get_option('wplinkdir_permalinks')=='' ? '' : ' You can view your link here: '.get_bloginfo('url').'/'.get_option('wplinkdir_permalinks').(get_option('wplinkdir_extended_info')=='' ? '' : '&id='.$_POST['id']));
			$BlogName=get_option('blogname');
			$SiteURL=get_option('siteurl');

			$Subject=sprintf(__('Link Approved At %s',$WPLD_Domain),$BlogName);
			mail($Email,$Subject,sprintf(__('This is just a quick message to let you know that your link has been approved at %1$s and is now live at our site.%2$s'."\n\n".'Link Name: %3$s'."\n".'Link URL: %4$s'."\n\nNote that your email is not available to the public and that this is an automated message, please do not reply to it.",$WPLD_Domain),$BlogName,$LinkOut,$_POST['title'],$_POST['url']));
			$Mailed=1;
		}

		$Rank=getpagerank($_POST['url']);
		$Now=time();
		mysql_query("UPDATE $links_table SET title = '{$_POST['title']}', category = '{$_POST['category']}', url = '{$_POST['url']}', description = '{$_POST['description']}', date_modified = {$Now}, pending = {$_POST['pending']}, mailed = $Mailed WHERE id = {$_POST['id']}");
		echo '<div id="message" class="updated fade"><strong>'.$_POST['title'].' '.__('Link Updated',$WPLD_Domain).'</strong></div>';
		unset($_POST);

	}elseif($_POST['Submit']==$WPLD_Trans['Delete']){

		mysql_query("DELETE FROM $links_table WHERE id = ".$wpdb->escape($_POST['id']));
		echo '<div id="message" class="updated fade"><strong>'.$wpdb->escape($_POST['title']).' '.__('Link Deleted',$WPLD_Domain).'</strong></div>';
		unset($_POST);
	}

	$getCats=mysql_query("SELECT * FROM $cat_table ORDER BY parent ASC");
	$Cats=wplinkdir_sort_categories('Sort',$getCats);

	echo '<form method="POST" action="" name="addsite">
	<table class="form-table">
	<tr><th>'.$WPLD_Trans['WebsiteURL'].'</th><td><input type="text" name="entry_url" value="http://"></td></tr>
	<tr><th>'.$WPLD_Trans['WebsiteTitle'].'</th><td><input type="text" name="entry_title"></td></tr>
	<tr><th>'.$WPLD_Trans['Category'].'</th><td><select name="entry_category">'.wplinkdir_sort_categories('Display',$Cats).'</select></td></tr>
	<tr><th valign="top">'.$WPLD_Trans['Description'].'</th><td><textarea name="entry_description" rows="3" cols="35"></textarea></td></tr>
	<tr><td colspan="2"><input type="submit" name="Submit" value="'.$_AddLink.'"></td></tr>
	</table>
	</form>';

	// JavaScript confirmation request & styling function for PR's
	echo confirm_request(__('Are you sure you want to delete this link?',$WPLD_Domain)).'<h2>'.$WPLD_Trans['WPLD_EditLinks'].'</h2>';
	wplinkdir_styling(get_option('wplinkdir_style'));
	unset($Cats,$getCats);

	$getLinks=mysql_query("SELECT * FROM $links_table WHERE tagged = 0 ORDER BY pending DESC, flagged DESC");
	$getCats=mysql_query("SELECT * FROM $cat_table ORDER BY parent ASC");

	$Cats=wplinkdir_sort_categories('Sort',$getCats);

	echo '<table class="form-table"><tr><th>Title / Description</th><th>URL</th><th>Category</th><th>Action</th></tr>';
	while($Link=mysql_fetch_assoc($getLinks)){
		$Width=$Link['pr']*4;
		$x++;

		if($Link['pending']==1){
			$Pending=' SELECTED';
			$Approved='';
			$Class=' class="updated fade"';
		}else{
			$Pending='';
			$Approved=' SELECTED';
			$Class='';
		}

		if($Link['flagged']>=2){
			if($Link['flagged']>=4){
				$b='<b>'; $eb='</b>';
			}else{
				$b=$eb='';
			}
			$Link['flagged']='<font color="red">'.$b.$Link['flagged'].$eb.'</font>';
		}

		echo '<form method="POST" action="" name="form_'.$x.'">
		<input type="hidden" name="id" value="'.$Link['id'].'">
		
		<tr'.$Class.'><td><div class="wpld_pr">PR: '.$Link['pr'].'<div class="wpld_prg"><div class="wpld_prb" style="width: '.$Width.'px"></div></div></div>#'.$x.'. <b><a href="'.$Link['url'].'">'.htmlspecialchars($Link['title']).'</a></b>
		<br /><input type="text" name="title" value="'.htmlspecialchars($Link['title']).'"></td>
		<td>'.$WPLD_Trans['WebsiteURL'].' <input type="text" name="url" value="'.$Link['url'].'" size="40"></td>
		<td>'.$WPLD_Trans['Category'].': <select name="category">'.wplinkdir_sort_categories('Display',$Cats).'</select></td>
		<td><input type="submit" name="Submit" value="'.$WPLD_Trans['Delete'].'" onclick="return confirmation();"></td></tr>

		<tr'.$Class.'><td><b>'.$WPLD_Trans['Description'].'</b><br /><textarea rows="2" cols="40" name="description">'.$Link['description'].'</textarea></td>
		<td>'.$WPLD_Trans['AddedBy'].' '.$Link['name'].' &#60;'.$Link['email'].'&#62;<br />'.$WPLD_Trans['DateAdded'].' '.date('jS M y',$Link['date_added']).'<br />';

		if(!empty($Link['recip_url']) && $Link['recip_url']!='http://'){
			echo __('Reciprocal Page:',$WPLD_Domain).' <a href="'.$Link['recip_url'].'">'.$Link['recip_url'].'</a>';
		}

		echo '</td><td>'.__('Flags:',$WPLD_Domain).' '.$Link['flagged'].'<br />'.__('Status:',$WPLD_Domain).' <select name="pending"><option value="0"'.$Approved.'>Approved</option><option value="1"'.$Pending.'>Pending</option></select></td>
		<td><input type="submit" name="Submit" value="'.$WPLD_Trans['Update'].'"></form></td>
		</tr>';
	}
	echo '</table><br /><br /></div>';
}


function wplinkdir_sort_categories($Mode='Display',$getCats){

	// This function is used to show a list of categories in a select menu. It's used to so the user can select a category for adding a link on the Add URL page and on the Edit Links page where the admin must select a category for new or existing links.

	global $Link;

	if($Mode!='Display'){
		while($Cat=mysql_fetch_assoc($getCats)){
			if($Cat['parent']==''){
				$Cats[$Cat['title']]=array();
			}else{
				if(count($Cats[$Cat['parent']])<1){
					$Cats[$Cat['parent']]=array();
				}
				$Cats[$Cat['parent']][$Cat['title']]=$Cat['title'];
			}
		}
		return $Cats;
	}else{
		foreach($getCats as $Title => $Value){

			$entry.='<option'.($Link['category'] == $Title ? ' SELECTED' : '').'>'.$Title.'</option>';
			$Echoed[$Title]=TRUE;
			@asort($Value);

			foreach($Value as $SubTitle => $SubValue){
				$entry.='<option '.($Link['category'] == $SubTitle ? ' SELECTED' : '').' value="'.$SubTitle.'"> - '.$SubTitle.'</option>';
			}
		}
		return $entry;
	}
}


function wplinkdir_category_page(){
	global $wpdb,$WPLD_Trans,$WPLD_Domain;

	$cat_table = WPLD_CATS_TABLE;
	$links_table = WPLD_LINKS_TABLE;

	$_AddCategory=__('Add Category',$WPLD_Domain);

	echo confirm_request().'<div class="wrap"><h2>'.$WPLD_Trans['WPLD_EditCategories'].'</h2>';

	if($_POST['submit']==$_AddCategory){

		$name=$_POST['cat_name'];
		$parent=$_POST['parent'];
		$description=$_POST['description'];

		$pretty_name=strtolower(str_replace(' ','-',preg_replace('/[^a-zA-Z0-9 ]/','',$name)));
		$pretty_name=str_replace('--','-',str_replace('--','-',$pretty_name));

		mysql_query("INSERT INTO $cat_table VALUES ('','$name','$pretty_name','$parent','$description')");
		echo '<div id="message" class="updated fade"><strong>'.$name.' '.__('Category Added',$WPLD_Domain).'</strong></div>';
		unset($_POST);

	}elseif($_POST['submit']==$WPLD_Trans['Delete']){

		$name=$_POST['oldtitle'];
		mysql_query("DELETE FROM $cat_table WHERE title = '{$name}'");
		mysql_query("UPDATE $links_table SET category = '' WHERE category = '{$name}'");
		echo '<div id="message" class="updated fade"><strong>'.$name.' '.__('Category Deleted',$WPLD_Domain).'</strong></div>';
		unset($_POST);

	}elseif($_POST['submit']==$WPLD_Trans['Update']){

		$oldname=$_POST['oldtitle'];
		$name=$_POST['title'];
		$oldparent=$_POST['oldparent'];
		$parent=$_POST['parent'];
		$description=$_POST['description'];

		$pretty_name=strtolower(str_replace(' ','-',preg_replace('/[^a-zA-Z0-9 ]/','',$name)));
		$pretty_name=str_replace('--','-',str_replace('--','-',$pretty_name));

		mysql_query("UPDATE $cat_table SET title = '$name', title_pretty = '$pretty_name', parent = '$parent', description = '$description' WHERE title = '{$oldname}'");

		// If category has gone from being a top category to a sub category, change previous sub-categories of the parent to top categories themselves
		if($parent!=$oldparent && $oldparent==''){
			mysql_query("UPDATE $cat_table SET parent = '' WHERE parent = '$oldname'");
		}

		if($name!=$oldname){
			mysql_query("UPDATE $links_table SET category = '$name' WHERE category = '{$oldname}'");
		}

		echo '<div id="message" class="updated fade"><strong>'.$oldname.' '.__('Category Updated',$WPLD_Domain).'</strong></div>';
		unset($_POST);
	}

	$getCats=mysql_query("SELECT * FROM $cat_table ORDER BY title ASC");

	if(@mysql_num_rows($getCats)>0){
		$x=0;
		echo '<table class="form-table">';

		$getTopCats=mysql_query("SELECT * FROM $cat_table WHERE parent = '' ORDER BY title ASC");

		$TopCats['None']='';
		while($TopCat=mysql_fetch_assoc($getTopCats)){
			$TopCats[$TopCat['title']]=$TopCat['title'];
		}
		unset($TopCat);

		while($Cat=mysql_fetch_assoc($getCats)){
			$getCount=mysql_query("SELECT COUNT(*) as count FROM $links_table WHERE category = {$Cat['title']}'");
			if(!$numlinks=@mysql_result($getCount,0,0)){
				$numlinks=0;
			}

			echo '<form method="POST" action="" name="edit_category_'.$x.'">
			<input type="hidden" name="oldtitle" value="'.$Cat['title'].'">
			<input type="hidden" name="oldparent" value="'.$Cat['parent'].'">
			<tr valign="top"><td><b>'.__('Title:',$WPLD_Domain).'</b><br /><input type="text" name="title" value="'.$Cat['title'].'"></td>
			<td><b>'.__('Sub-Category of: ',$WPLD_Domain).'</b><br /><select name="parent">';

			if($Cat['parent']==''){
				$Cat['parent']='None';
			}

			foreach($TopCats as $Title => $Value){
				if($Title==$Cat['parent']){
					$Selected=' SELECTED';
				}else{
					$Selected='';
				}
				echo "<option{$Selected} value=\"{$Value}\">{$Title}</option>";
			}

			echo '</select></td>
			<td><b>'.__('Description:',$WPLD_Domain).'</b><br /><textarea name="description" rows="3" cols="45">'.$Cat['description'].'</textarea></td>
			<td><input type="submit" name="submit" value="'.$WPLD_Trans['Update'].'"><br /><input type="submit" name="submit" value="'.$WPLD_Trans['Delete'].'" onclick="return confirmation();"></td></tr>
			</form>';
		}
		echo '</table><br /><br />';
	}

	echo '<h2>'.$_AddCategory.'</h2><br /><br />
	<form method="POST" action="" name="new_category">
	<table class="form-table">
	<tr valign="top"><td><b>'.__('Title:',$WPLD_Domain).'</b><br /><input type="text" name="cat_name"></td>
	<td><b>'.__('Sub-Category of:',$WPLD_Domain).'</b><br /><select name="parent">';

	foreach($TopCats as $TopCat){
		echo "<option>{$TopCat}</option>";
	}

	echo '</select></td>
	<td><b>'.__('Description:',$WPLD_Domain).'</b><br /><textarea name="description" rows="3" cols="45">'.$Cat['description'].'</textarea></td>
	<td><input type="submit" name="submit" value="'.$_AddCategory.'"></td></tr>
	</table>
	</form>
	</div>';
}


function wplinkdir_premium_links_page($url='None',$Mode='Display'){
	global $WPLD_Domain,$PLinks,$Currencies;

	$DefaultCurrency=get_option('wplinkdir_pl_currency');
	$PLCost=get_option('wplinkdir_pl_cost');
	$TestMode=get_option('wplinkdir_pl_testmode');

	if($Mode!='Display'){
		// Setup class
		require_once('paypal.class.php');  // include the class file
		$p = new paypal_class; // initiate an instance of the class

		$getCat=mysql_query("SELECT category FROM ".WPLD_LINKS_TABLE." WHERE url = {$url}");
		if(@mysql_num_rows($getCat)>0){
			$Cat=mysql_result($getCat,0,0);
			if($PLinks){
				$this_script=get_option('wplinkdir_pl_returnpage').'?url='.$url;
			}
		}else{
			return __("Payment could not be initiated because the link doesn't seem to exist.",$WPLD_Domain);
		}

		if($TestMode=='Yes'){
			$p->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';   // testing paypal url
		}else{
			$p->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
		}

		switch ($_GET['action']) {
		case 'process':
			$p->add_field('on0', 'Site URL');
			$p->add_field('os0', $_POST['os0']);
			$p->add_field('business',get_option('wplinkdir_pl_email'));
			$p->add_field('return', $this_script.'&action=success');
			$p->add_field('cancel_return', $this_script.'&action=cancel');
			$p->add_field('notify_url', $this_script.'&action=ipn');
			$p->add_field('no_shipping', 1);
			$p->add_field('item_name', 'Premium Link');
			$p->add_field('amount', get_option('wplinkdir_pl_cost'));
			$p->add_field('currency_code', get_option('wplinkdir_pl_currency'));

			$p->submit_paypal_post(); // submit the fields to paypal
			break;

		case 'success':// Order was successful... 

			if($_POST['mc_gross']>=$PLCost && $_POST['mc_currency'] && $_POST['payer_email']){
				$now=time();
				mysql_query("UPDATE ".WPLD_LINKS_TABLE." SET premium = $now WHERE id = $id");

				$page.='<h3>'.__('Thank you!',$WPLD_Domain).'</h3>'
				.__('Thank you for your payment. Your site is now listed as a premium site and will be shown above all regular links.',$WPLD_Domain).'<br />';
			}
			break;

		case 'cancel': // Order was canceled...

			// The order was canceled before being completed.
			break;

		case 'ipn':    // Paypal is calling page for IPN validation...

			if ($p->validate_ipn()) {
				$subject='Premium Link Payment';
				$to=get_option('admin_email');
				$body=__("A Premium Link has been paid for at ".bloginfo('name')."\nPayment is from ".$p->ipn_data['payer_email']." on ".date('m/d/Y')." at ".date('g:i A')."\n\nFull Details:\n",$WPLD_Domain);

				foreach ($p->ipn_data as $key => $value) { $body .= "\n$key: $value"; }
				mail($to, $subject, $body);
				}
				break;
		}

	}elseif($Mode=='Display'){

		$page.='<div align="center" class="wpld_panel" border="1">
		'.__("Want to see your site listed above all the rest and get rid of this annoying message? Buy a premium link for just {$Currencies[$DefaultCurrency]}{$PLCost} {$DefaultCurrency} now!",$WPLD_Domain).'
		<br /><br /><form action="'.$_SERVER['REQUEST_URI'].( $id=='None' ? '' : '?id='.$id.'&action=process' ).'" method="post">
		<table>';

		if($url=='None'){
			$page.='<tr><td colspan="2">Please enter the URL of your site. We recommend you browse to the category you submitted to and copy then paste the URL from there.</td></tr>';
		}

		$page.='<tr><td><input type="hidden" name="on0" value="URL"/>'.__('Site URL:',$WPLD_Domain).' <input type="text" name="os0" value="'.$url.'"></td>
		<td>
		<input type="hidden" name="no_shipping" value="1"/>
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="1148120">

		<input type="image" src="https://www.paypal.com/en_GB/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="">
		<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1">
		</td></tr>
		</table>
		</form>
		</div><br />';
	}
	return $page;
}


function wplinkdir_addsite(){

	// This function displays the Add URL page and handles the submitting and adding of a new link

	global $wpdb,$Link,$WPLD_Trans,$WPLD_Domain;
	$table_links = WPLD_LINKS_TABLE;
	$table_cats = WPLD_CATS_TABLE;

	$page='';
	$recip_requirement=get_option('wplinkdir_recip_requirement');

	include 'captcha.php';

	if($_POST['entry_url']!=''){

		// Check the captcha code
		if(get_option('wplinkdir_captcha')=='Securimage'){

			include("securimage.php");
			$img = new Securimage();
			$valid = $img->check($_POST['code']);

			if($valid != true){
				return $WPLD_Trans['Captcha'];
			}

		}elseif(get_option('wplinkdir_captcha')=='Captchas.net'){
			$captchas = new CaptchasDotNet ('Seans0n', 'FXreSBkiqzBwFajyshKCupz3yzoTyfu5wdVIaFZx','/tmp/captchasnet-random-strings','3600','abcdefghkmnopqrstuvwxyz','6','240','80');

			// Read the form values
			$password=$_REQUEST['entry_captcha'];
			$random_string=$_REQUEST['entry_random'];

			// Check the random string to be valid and return an error message otherwise.
			if (!$captchas->validate ($random_string)){
				return __('Every CAPTCHA can only be used once. The current CAPTCHA has already been used. Please click back, refresh the page and try again.',$WPLD_Domain);
			}elseif (!$captchas->verify ($password)){
				return $WPLD_Trans['Captcha'];
			}
		}

		$Tags=get_option('wplinkdir_htmltags');
		$ExtraFields=get_option('wplinkdir_extrafields');
		$category=$wpdb->escape($_POST['entry_category']);
		$recip_page=$wpdb->escape($_POST['entry_recip_page']);
		$description=strip_tags($_POST['entry_description'],$Tags);
		$title=htmlspecialchars($_POST['entry_title']);

		// Check extrafields option and contents of the fields if required
		if($ExtraFields=='Required'){

			if(!isset($_POST['entry_name'])){
				return $page.__("You didn't enter a name. You will need to fill this in to add your link.",$WPLD_Domain);
			}else{
				$name=trim($_POST['entry_name']);
			}

			if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $_POST['entry_email'])){
				$email=trim($_POST['entry_email']);
			}else{
				return $page.sprintf(__('The E-Mail address you entered (%s) doesn\'t appear to be valid. Your link was not added.',$WPLD_Domain),$_POST['entry_email']);
			}

		}elseif($ExtraFields=='Optional'){
			$email=trim($_POST['entry_email']);
			$name=trim($_POST['entry_name']);
		}else{
			$email='';
			$name='';
		}

		// Check URL Exists With fopen
		$handle1=@fopen($_POST['entry_url'],'r');
		if($handle1){
			$url=$wpdb->escape($_POST['entry_url']);
			fclose($handle1);
		}else{
			return $page.__("The URL you supplied doesn't seem to be valid.",$WPLD_Domain);
		}

		// Get Hostname (http://www.example.com/dir/file.htm becomes example.com) To Check For Duplicate Links
		preg_match('@^(?:http://)?([^/]+)@i',$url,$matches);
		$host=$matches[1];
		preg_match('/[^.]+\.[^.]+$/',$host,$matches);
		$shortened_url=$matches[0];

		if(get_option('wplinkdir_subdomains')=='Yes'){
			$getSame=mysql_query("SELECT category FROM $table_links WHERE url LIKE '%{$url}%'"); $wtf='yes';
		}else{
			$getSame=mysql_query("SELECT category FROM $table_links WHERE url LIKE '%{$shortened_url}%'"); $wtf='no';
		}

		if(@mysql_result($getSame,0,0)!=''){
			$page.=sprintf(__('Your site (%1$s) is already in our directory in the %2$s category.',$WPLD_Domain),$url,mysql_result($getSame,0,0));
			return $page;
		}

		$Rank=getpagerank($url);
		$Approval=( get_option('wplinkdir_approval')=="Require Approval For Links That Don't Meet Requirements" || get_option('wplinkdir_approval')=="Require Approval For All Links" ? TRUE : FALSE );

		// Check Reciprocal Link Requirements Are Met
		if($recip_requirement!='Nobody'){

			// Only Check For Reciprocal Link if recip_requirement is 'Everybody' or the Pages Rank is Less Than recip_requirement
			if($recip_requirement=='Everybody' || $Rank<$recip_requirement){

				// Check recip page URL is on the same site as the main link using the hostname
				if($recip_requirement!='Nobody' && !stripos($recip_page,$shortened_url)){
					if($Approval){
						$Pending=1;
					}else{
						$page.=__('The reciprocal link page must be somewhere on the domain you are adding.',$WPLD_Domain);
						return $page;
					}
				}

				$searchfor='/\<a.*href\="'.preg_quote(get_option('wplinkdir_your_url'),'/').'".*\>.+\<\/a\>/';

				$handle=@fopen($recip_page,'r');
				if($handle){
					while(!feof($handle)){
						$contents.=fgets($handle, 4096);
					}
					fclose($handle);
				}else{
					if($Approval){
						$Pending=1;
					}else{
						$page.=__('The reciprocal URL you supplied doesn\'t seem to exist.',$WPLD_Domain);
						return $page;
					}
				}

				if(!preg_match($searchfor,$contents)){
					if($Approval){
						$Pending=1;
					}else{
						$page.=sprintf(__('We couldn\'t find a reciprocal link on the page %s.',$WPLD_Domain),$recip_page);
						return $page;
					}
				}
			}
		}

		if($Approval || $Pending==1){
			$Pending=1;
			$Mailed=0;
		}else{
			$Pending=0;
			$Mailed=1;
		}

		// Insert New Link Into Database
		$now=time();
		mysql_query("INSERT INTO $table_links VALUES('','$name','$email','$title','$url','$description','$recip_page','$category','$Rank','','$now','',0,0,$Pending,$Mailed,0,0,0)");

		if($Pending==0){
			$page.=sprintf(__('Your link (%s) has been added successfully, thanks!',$WPLD_Domain),$title);
		}else{
			$page.=sprintf(__('Your link (%s) will be checked by an admin for approval and added soon, thanks!',$WPLD_Domain),$title);
		}

		if(get_option('wplinkdir_premium')=='Yes'){
			$getID=mysql_query("SELECT id FROM $table_links WHERE url = '$url' ORDER BY date_added DESC LIMIT 1");
			$ID=mysql_result($getID,0,0);

			$page.='<br /><br />'.wplinkdir_premiumlinks_page($ID);
		}

		// Email Admin New Link Details (If Selected)
		if(get_option('wplinkdir_emailme')=='Yes'){
			$BlogName=get_option('blogname');
			$SiteURL=get_option('siteurl');
			$Subject=sprintf(__('%s - New Link Added',$WPLD_Domain),$BlogName);
			mail(get_option('admin_email'),$Subject,sprintf(__('This is just a quick message to let you know that someone has added a link to your link directory on %1$s (%2$s).'."\n\n".'Link Name: %3$s (PR %4$s)'."\n".'Site URL: %5$s'."\n\n".'You can edit or delete this link by logging into your admin section. You can turn off email updates by turning the Email Me option in the admin section.',$WPLD_Domain),$BlogName,$SiteURL,$title,$Rank,$url));
		}
	}else{
		// Edit Below Here To Change The Add URL Page

		$getCats=mysql_query("SELECT * FROM $table_cats ORDER BY parent ASC");
		$Cats=wplinkdir_sort_categories('Sort',$getCats);
		$Tags=get_option('wplinkdir_htmltags');
		$ExtraFields=get_option('wplinkdir_extrafields');

		$Link['category']=$_GET['cat'];

		if($recip_requirement!='Nobody'){

			if($recip_requirement=='Everybody'){
				$page.=__('You must link to our site first before you can add yourself to our directory.',$WPLD_Domain);
			}elseif($recip_requirement!='Nobody'){
				$page.=sprintf(__('Sites with a PageRank of less than %s must first supply a link back to our site.',$WPLD_Domain),get_option('wplinkdir_recip_requirement'));
			}
			$page.=' '.__('You can find out how to do that',$WPLD_Domain).' <a href="'.str_replace('addsite','linkus',$_SERVER['REQUEST_URI']).'">'.__('here',$WPLD_Domain).'</a><br /><br />';
		}

		$Tags=get_option('wplinkdir_htmltags');
		if($Tags!=''){
			$Tags='<br /><sup>'.__('HTML tags allowed:',$WPLD_Domain).' '.htmlspecialchars($Tags);
		}

		$DescriptionSize=get_option('wplinkdir_description_size');
		if($DescriptionSize!=''){
			$Tags.='<br />'.sprintf(__('Maximum: %s characters',$WPLD_Domain),$DescriptionSize).'</sup>';
		}else{
			$Tags.='</sup>';
		}

		if($ExtraFields!='Hidden'){
			$Fields='<tr align="left"><td><b>'.__('Your Name:',$WPLD_Domain).'</b></td><td><input type="text" name="entry_name"></td></tr>
			<tr align="left"><td><b>'.__('Your E-Mail:',$WPLD_Domain).'</b></td><td><input type="text" name="entry_email"></td></tr>';
		}

		$page.='<form method="POST" action="" name="entryform">
		<table>'.$Fields.'
		<tr align="left"><td><b>'.$WPLD_Trans['WebsiteURL'].'</b></td><td><input type="text" name="entry_url" value="http://"></td></tr>
		<tr align="left"><td><b>'.$WPLD_Trans['WebsiteTitle'].'</b></td><td><input type="text" name="entry_title"></td></tr>
		<tr align="left"><td><b>'.$WPLD_Trans['Category'].'</b></td><td><select name="entry_category">'.wplinkdir_sort_categories('Display',$Cats).'</select></td></tr>
		<tr align="left"><td valign="top"><b>'.$WPLD_Trans['Description'].'</b>'.$Tags.'</td><td><textarea '.(is_numeric($DescriptionSize) ? 'onkeydown="if (entry_description.value.length > '.$DescriptionSize.'){ entry_description.value = entry_description.value.substring(0, '.$DescriptionSize.'); }" onkeyup="if (entry_description.value.length > '.$DescriptionSize.'){ entry_description.value = entry_description.value.substring(0, '.$DescriptionSize.'); }"' : '' ).' name="entry_description" rows="3" cols="35"></textarea></td></tr>';

		if($recip_requirement!='Nobody'){
			$page.='<tr align="left"><td><b>'.__('Reciprocal URL:',$WPLD_Domain).'</b></td><td><input type="text" name="entry_recip_page" value="http://"></td></tr>';
		}

		if(get_option('wplinkdir_captcha')=='Securimage'){

			$page.='<tr valign="top">
			<td><b>'.__('Captcha:',$WPLD_Domain).'</b></td><td>
			<img src="'.get_bloginfo('url').'/wp-content/plugins/wordpress-link-directory/securimage_show.php?sid='.md5(uniqid(time())).'">
			<sup><a href="'.get_bloginfo('url').'/wp-content/plugins/wordpress-link-directory/securimage_play.php">Audio</a> <a href="#" onclick="document.getElementById(\'image\').src = \''.get_bloginfo('url').'/wp-content/plugins/wordpress-link-directory/securimage_show.php?sid=\' + Math.random(); return false">Reload Image</a></sup>
			</td></tr>
			<tr><td><b>'.__('Enter Code:',$WPLD_Domain).'</b></td><td><input name="code" /></td></tr>';

		}elseif(get_option('wplinkdir_captcha')=='Captchas.net'){
			$captchas = new CaptchasDotNet ('Seans0n', 'FXreSBkiqzBwFajyshKCupz3yzoTyfu5wdVIaFZx', '/tmp/captchasnet-random-strings','3600', 'abcdefghkmnopqrstuvwxyz','6', '180','70');

			$page.='<tr valign="top">
			<td><b>'.__('Captcha:',$WPLD_Domain).'</b></td><td><input type="hidden" name="entry_random" value="'.$captchas->random().'" />
			'.$captchas->image().'<br />
			<sup><a rel="nofollow" href="'.$captchas->audio_url().'">'.__('Phonetic spelling (mp3)',$WPLD_Domain).'</a></sup></td></tr>
			<tr><td><b>'.__('Enter Code:',$WPLD_Domain).'</b></td><td><input name="entry_captcha" /></td></tr>';
		}

		$page.='<tr><td colspan="2" align="right"><input type="submit" name="Submit" value="'.__('Add Link',$WPLD_Domain).'"></td></tr>
		</table>
		</form>';
	}
	return $page;
}



function wplinkdir_search(){
	global $wpdb,$Ex,$Home,$WPLD_Trans,$WPLD_Domain,$PLinks;

	// This function displays and handles the Search page

	$table_cats = WPLD_CATS_TABLE;
	$table_links = WPLD_LINKS_TABLE;

	$ShowPR=get_option('wplinkdir_showpr');
	$Target=get_option('wplinkdir_target');
	$DetailedInfo=get_option('wplinkdir_extended_info');

	if(get_option('wplinkdir_nofollow')=='Yes'){
		$NoFollow=' rel="nofollow"';
	}else{
		$NoFollow='';
	}

	// Display the search box.

	$page.='<form method="POST" action="'.$_SERVER['REQUEST_URI'].'" name="searchform">
	<table class="wpld_panel"><tr><td><b>'.__('Search For:',$WPLD_Domain).' </b></td>
	<td><input type="text" name="Search" value="'.stripslashes($_POST['Search']).'"></td>
	<td><b>'.__('Search In:',$WPLD_Domain).' </b></td>
	<td><select name="type"><option value="All">'.__('All',$WPLD_Domain).'</option><option value="Title">'.__('Title',$WPLD_Domain).'</option><option value="URL">'.__('URL',$WPLD_Domain).'</option><option value="Description">'.__('Description',$WPLD_Domain).'</option></select></td></tr>
	<tr><td colspan="4" align="right"><input type="submit" name="submit" value="'.__('Search',$WPLD_Domain).'"></td></tr>
	</table>
	</form>
	<br /><br /><br />';

	// Search in URL, title, description or all 3

	if($_POST['Search']){

		$Search=mysql_real_escape_string(trim($_POST['Search']));

		if($_POST['type']=='URL'){
			$getSearchResults=mysql_query("SELECT * FROM $table_links WHERE url LIKE '%$Search%' AND tagged = 0 AND pending = 0 ORDER BY pr DESC LIMIT 50");
		}elseif($_POST['type']=='Title'){
			$getSearchResults=mysql_query("SELECT * FROM $table_links WHERE title LIKE '%$Search%' AND tagged = 0 AND pending = 0 ORDER BY pr DESC LIMIT 50");
		}elseif($_POST['type']=='Description'){
			$getSearchResults=mysql_query("SELECT * FROM $table_links WHERE description LIKE '%$Search%' AND tagged = 0 AND pending = 0 ORDER BY pr DESC LIMIT 50");
		}elseif($_POST['type']=='All'){
			$getSearchResults=mysql_query("SELECT * FROM $table_links WHERE (url LIKE '%$Search%' OR title LIKE '%$Search%' OR description LIKE '%$Search%') AND tagged = 0 AND pending = 0 ORDER BY pr DESC LIMIT 50");
		}

		if(@mysql_num_rows($getSearchResults)==0){
			$page.='<b>'.__('No results were found in your search.',$WPLD_Domain).'</b>';
		}

		while($SearchResult=mysql_fetch_assoc($getSearchResults)){
			$x++;

			$page.="<table><tr>";

			if($ShowPR=='Yes'){
				$Width=$SearchResult['pr']*4;
				$page.='<td><div class="wpld_pr">PR: '.$SearchResult['pr'].'
				<div class="wpld_prg"><div class="wpld_prb" style="width: '.$Width.'px"></div></div>
				</td>';
			}

			if($DetailedInfo!=''){
				if($PLinks){
					$getPrettyTitle=mysql_query("SELECT title_pretty FROM $table_cats WHERE title = '{$SearchResult['category']}'")or die(mysql_error());
					$PrettyTitle=mysql_result($getPrettyTitle,0,0);
					$DetailedLink=' - <i><a href="'.$Home.$PrettyTitle.'/'.$SearchResult['id'].'">'.$DetailedInfo.'</a></i>';
				}else{
					$DetailedLink=' - <i><a href="'.$Home.$Ex.'id='.$SearchResult['id'].'">'.$DetailedInfo.'</a></i>';
				}
			}

			$page.='<td>'.$x.'. <a target="'.$Target.'" href="'.$SearchResult['url'].'"'.$NoFollow.'>'.stripslashes($SearchResult['title']).'</a> - '.$SearchResult['description'].$DetailedLink.'</td>
			</tr></table>';
		}
	}
	return $page;
}


function wplinkdir_linktosite($url,$title,$id){
	$Permalinks=get_option('wplinkdir_permalinks');
	$Target=get_option('wplinkdir_target');
	$HitsOut=get_option('wplinkdir_track_hitsout');
	if($HitsOut=='No'){
		$Target=get_option('wplinkdir_target');
	}else{
		$Target='_self';
	}

	if(get_option('wplinkdir_nofollow')=='Yes'){
		$NoFollow=' rel="nofollow"';
	}else{
		$NoFollow='';
	}

	$Temp=$_GET['id'];
	unset($_GET['id']);
	if($Permalinks!=''|| 2<3){
		if($_GET){
			$E='&';
		}else{
			$E='?';
		}
		$EE="={$id}";
	}

	$_GET['id']=$Temp;
	return '<a href="'.( $HitsOut=='Yes' ? $_SERVER['REQUEST_URI'].$E.'go'.$EE : $url ).'" target="'.$Target.'"'.$NoFollow.'>'.$title.'</a>';
}


function wplinkdir_displaydir($ShortcodeCat=''){

	// This is the main function which handles processing and displaying the directory to the outside world

	global $wpdb,$Ex,$Home,$WPLD_Trans,$WPLD_Domain,$wp_rewrite,$Page,$PLinks,$isFlagged;
	$cat_table = WPLD_CATS_TABLE;
	$links_table = WPLD_LINKS_TABLE;

	// If the directory is being displayed using a category shortcode like [wplinkdir category="Blogs & Blogging"] then this will process the category for later use
	if($ShortcodeCat!=''){
		$_GET['cat']=$ShortcodeCat;
	}

	// WordPress has some funny issues with permalinks being confused as $_GET vars.
	// This section is to sort them out and display the 'Home | Add URL | Link To Us' links correctly

	$PremiumLinks=get_option('wplinkdir_pl_enable');
	$Permalinks=get_option('wplinkdir_permalinks');

	// Permalinks are enabled
	if($wp_rewrite->using_permalinks()==true && $Permalinks!=''){
		$PLinks=TRUE;
		if(is_numeric($Page)){
			$_GET['id']=$Page;
		}
		$Home=get_bloginfo('url').'/'.$Permalinks.'/';
		$AddSite=$Home.'addsite';
		$LinkUs=$Home.'linkus';
		$Search=$Home.'search';
		$Premium=$Home.'premium';

		if($Page=='addsite'){
			$_GET['act']='addsite';
		}elseif($Page=='linkus'){
			$_GET['act']='linkus';
		}elseif($Page=='search'){
			$_GET['act']='search';
		}elseif($Page=='premium'){
			$_GET['act']='premium';
		}elseif($Page!='addsite' && $Page!='linkus' && $Page!='search' && !is_numeric($Page) && $Page!='home'){
			$_GET['cat']=$Page;
		}
	}else{
	// Permalinks are not enabled, use $_GET
		$PLinks=FALSE;
		$Ex='?';
		foreach($_GET as $opt => $val){
			if($opt!='cat' && $opt!='act' && $opt!='id' && $opt!='flag'){
				$Ex='&';
			}
		}

		$_SERVER['REQUEST_URI']=preg_replace('/(&|\?)(flag|id)\=[0-9]*/','',$_SERVER['REQUEST_URI']);
		$Home=preg_replace('/(\&|\?)act\=(addsite|linkus|search|premium)/','',$_SERVER['REQUEST_URI']);
		if($_GET['cat']){
			$Temp=explode('cat=',$Home);
			$Home=substr($Temp[0],0,-1);
		}

		if($_GET['act']=='addsite'){
			$AddSite=$_SERVER['REQUEST_URI'];
			$LinkUs=str_replace('addsite','linkus',$_SERVER['REQUEST_URI']);
			$Search=str_replace('addsite','search',$_SERVER['REQUEST_URI']);
			$Premium=( $PremiumLinks=='Yes' ? str_replace('addsite','premium',$_SERVER['REQUEST_URI']) : '');
		}elseif($_GET['act']=='linkus'){
			$AddSite=str_replace('linkus','addsite',$_SERVER['REQUEST_URI']);
			$Search=str_replace('linkus','search',$_SERVER['REQUEST_URI']);
			$LinkUs=$_SERVER['REQUEST_URI'];
			$Premium=( $PremiumLinks=='Yes' ? str_replace('linkus','premium',$_SERVER['REQUEST_URI']) : '');
		}elseif($_GET['act']=='search'){
			$AddSite=str_replace('search','addsite',$_SERVER['REQUEST_URI']);
			$LinkUs=str_replace('search','linkus',$_SERVER['REQUEST_URI']);
			$Search=$_SERVER['REQUEST_URI'];
			$Premium=( $PremiumLinks=='Yes' ? str_replace('search','premium',$_SERVER['REQUEST_URI']) : '');
		}elseif($_GET['act']=='premium'){
			$AddSite=str_replace('premium','addsite',$_SERVER['REQUEST_URI']);
			$LinkUs=str_replace('premium','linkus',$_SERVER['REQUEST_URI']);
			$Search=str_replace('premium','search',$_SERVER['REQUEST_URI']);
			$Premium=$_SERVER['REQUEST_URI'];
		}elseif(!isset($_GET['act']) || $_GET['act']==''){

			if($_GET['cat']){
				$Temp=explode('cat=',str_replace('act=','',$_SERVER['REQUEST_URI']));
				$Home=substr($Temp[0],0,-1);
				$CEx='&';
			}else{
				$Home=$_SERVER['REQUEST_URI'];
				$CEx=$Ex;
			}

			$AddSite=$_SERVER['REQUEST_URI'].$CEx.'act=addsite';
			$LinkUs=$_SERVER['REQUEST_URI'].$CEx.'act=linkus';
			$Search=$_SERVER['REQUEST_URI'].$CEx.'act=search';
			$Premium=( $PremiumLinks=='Yes' ? $_SERVER['REQUEST_URI'].$CEx.'act=premium' : '');
		}
	}

	$Target=get_option('wplinkdir_target');

	// Display the links bar, feel free to edit this to alter how the link bar is shown
	if(!isset($ShortcodeCat) || $ShortcodeCat==''){
		$page.='<div class="wpld_page"><span class="wpld_navbar"><a href="'.$Home.'">'.__('Home',$WPLD_Domain).'</a> | <a href="'.$AddSite.'">'.__('Add URL',$WPLD_Domain).'</a> | <a href="'.$LinkUs.'">'.__('Link To Us',$WPLD_Domain).'</a> | <a rel="nofollow" href="'.$Search.'">'.__('Search',$WPLD_Domain).'</a>'.($PremiumLinks=='Yes' ? ' | <a rel="nofollow" href="'.$Premium.'">Premium</a>' : '').'</span><br /><br />';
	}

	// If a link has been flagged, display a message saying so
	if(isset($isFlagged)){
		$page.='<br />'.$isFlagged;
	}

	// Now figure out which page the user is viewing and display it

	if($_GET['act']=='addsite'){

		// The Add URL page. The contents of this page are handled by wplinkdir_addsite() function (the previous function, above).
		return $page.wplinkdir_addsite().'</div>';

	}elseif($_GET['act']=='search'){

		// The Search page, also handled by another function: wplinkdir_search().
		return $page.wplinkdir_search().'</div>';

	}elseif($_GET['act']=='linkus'){

		// The Link To Us page. This is a pretty simple page so it doesn't have its own function

		$page.=__('Step 1: Add our URL to your page using this HTML code:',$WPLD_Domain).'<br /><br />
		<textarea rows="4" cols="50">'.get_option('wplinkdir_htmlcode').'</textarea><br /><br />

		'.__('This will generate a link like this:',$WPLD_Domain).'<br /><br />
		<div class="wpld_panel">'.get_option('wplinkdir_htmlcode').'</div><br /><br />

		'.__("Step 2: Add your link to our site using <a href=\"{$AddSite}\">this page</a>. You will be added instantly and we will begin sending traffic back to you immediately.",$WPLD_Domain);
		return $page.'</div>';

	}elseif($_GET['act']=='premium'){

		return $page.wplinkdir_premium_links_page('None').'</div>';
	}

	if($_GET['id']){

		// Display detailed info on a requested link

		if(!is_numeric($_GET['id'])){
			return $page.__('Invalid Request',$WPLD_Domain);
		}

		if(isset($_GET['action'])){
			$page.=wplinkdir_premium_links_page($_GET['id'],'Capture');
		}

		if(get_option('wplinkdir_extended_info')==''){
			return $page__('Detailed information is not available.',$WPLD_Domain);
		}

		$getInfo=mysql_query("SELECT * FROM $links_table WHERE id = ".$wpdb->escape($_GET['id'])." AND pending = 0");
		if(@mysql_num_rows($getInfo)<1){
			return $page.__('This link has been deleted or does not exist.',$WPLD_Domain);
		}
		$Info=mysql_fetch_assoc($getInfo);
		$getPrettyTitle=mysql_query("SELECT title_pretty FROM $cat_table WHERE title = '{$Info['category']}'");
		$PrettyTitle=mysql_result($getPrettyTitle,0,0);

		if($PLinks){
			$CatLink=$Home.$PrettyTitle;
		}else{
			$Temp=explode('id=',$_SERVER['REQUEST_URI']);
			$CatLink=$Temp[0].$Ex.'cat='.urlencode($Info['category']);
		}

		$page.='<br />'.( $PremiumLinks=='Yes' && get_option('wplinkdir_pl_email')!='' && get_option('wplinkdir_pl_returnpage')!='' && $Info['premium']==0 ? wplinkdir_premium_links_page($Info['url'],'Display') : '' ).'
		<table width="100%"'.( $Info['premium']>0 && $PremiumLinks=='Yes' ? ' class="wpld_premium_links"' : '').'>
		<tr><td width="125" align="center" valign="top"><img src="http://open.thumbshots.org/image.aspx?url='.$Info['url'].'" width="120" height="90" border="1"></td>
		<td valign="top">'.wplinkdir_linktosite($Info['url'],$Info['title'],$Info['id']).'<br />
		<font size="-2">'.$Info['url'].'</font><br />'.
		$Info['description'].'</td></tr>
		<tr><td>'.$WPLD_Trans['Category'].'</td><td><a href="'.$CatLink.'">'.$Info['category'].'</a></td></tr>
		<tr><td>'.__('Pagerank:',$WPLD_Domain).'</td><td>'.$Info['pr'].'</td></tr>';

		if($Info['name']){
			$page.='<tr><td>'.__('Added By:',$WPLD_Domain)."</td><td>{$Info['name']}</td></tr>";
		}

		$page.='<tr><td>'.__('Date Added:',$WPLD_Domain).'</td><td>'.date('F jS Y',$Info['date_added']).'</td></tr>';

		if($Info['date_modified']){
			$page.='<tr><td>'.__('Last Updated:',$WPLD_Domain).'</td><td>'.date('F jS Y',$Info['date_modified']).'</td></tr>';
		}

		if(get_option('wplinkdir_track_hitsin')=='Yes'){
			$page.='<tr><td>'.__('Hits In:',$WPLD_Domain).'</td><td>'.$Info['hitsin'].'</td></tr>';
		}
		if(get_option('wplinkdir_track_hitsout')=='Yes'){
			$page.='<tr><td>'.__('Hits Out:',$WPLD_Domain).'</td><td>'.$Info['hitsout'].'</td></tr>';
		}

		$page.='<tr><td colspan="2"><br /><br /></td></tr>
		<tr><td>'.__('Cached Pages:',$WPLD_Domain).'</td>
		<td><a target="_blank" href="http://www.google.com/search?ie=UTF-8&q=site:'.$Info['url'].'">Google</a>, <a target="_blank" href="http://search.yahoo.com/search?p=site:'.$Info['url'].'">Yahoo!</a>, <a target="_blank" href="http://search.msn.com/results.aspx?q=site:'.$Info['url'].'">MSN</a>, <a target="_blank" href="http://www.altavista.com/web/results?q=site:'.$Info['url'].'">AltaVista</a></td>
		</tr>
		<tr><td>'.__('Backlinks:',$WPLD_Domain).'</td>
		<td><a target="_blank" href="http://www.google.com/search?ie=UTF-8&q=link:'.$Info['url'].'">Google</a>, <a target="_blank" href="http://search.yahoo.com/search?p=link:'.$Info['url'].'">Yahoo!</a>, <a target="_blank" href="http://search.msn.com/results.aspx?q=link:'.$Info['url'].'">MSN</a>, <a target="_blank" href="http://www.altavista.com/web/results?q=links:'.$Info['url'].'">AltaVista</a></td>
		</tr>';

		$AlexaLink=str_replace('http://','',str_replace('www.','',strtolower($Info['url'])));
		$Pieces=explode('.',$AlexaLink);
		$TLDPos=count($Pieces)-1;
		$DomainPos=$TLDPos-1;
		$TLD=$Pieces[$TLDPos];
		$Domain=$Pieces[$DomainPos];

		$page.='<tr><td>'.__('Other Links:',$WPLD_Domain).'</td>
		<td><a target="_blank" href="http://www.alexa.com/data/details/main/'.$AlexaLink.'">Alexa.com</a>, <a target="_blank" href="http://whois.net/whois_new.cgi?d='.$Domain.'&tld='.$TLD.'">Whois.net</a></td>
		</tr>
		</table>';
	}

	if(isset($_GET['cat'])){

		// A category has been selected, display links in this category.

		$CatsPerRow=get_option('wplinkdir_catsperrow');
		$ShowNums=get_option('wplinkdir_show_numbers');
		$ShowPR=get_option('wplinkdir_showpr');
		$DetailedInfo=get_option('wplinkdir_extended_info');
		$LinkFlagging=get_option('wplinkdir_link_flagging');

		if(!$Order=get_option('wplinkdir_orderby')){
			$Order=',pr DESC';
		}else{
			$Order=','.$Order;
		}

		$Cat=html_entity_decode($_GET['cat']);
		if(!empty($ShortcodeCat)){
			extract(shortcode_atts(array('category'=>$Cat),$atts));
		}

		if($PLinks){
			$getCat=mysql_query("SELECT title,parent,description FROM $cat_table WHERE title_pretty = '$Cat'");
		}else{
			$getCat=mysql_query("SELECT title,parent,description FROM $cat_table WHERE title = '$Cat'");
		}

		$Cat=mysql_result($getCat,0,0);
		$Desc=mysql_result($getCat,0,2);
		$getLinks=mysql_query("SELECT * FROM $links_table WHERE category = '$Cat' AND tagged = 0 AND pending = 0 ORDER BY premium DESC{$Order}");

		$page.="<h2>$Cat".($ShowNums == 'Yes' ? ' ('.@mysql_num_rows($getLinks).')' : '')."</h2>$Desc<br /><br />";

		// Display sub-categories
		if(@mysql_result($getCat,0,1)==''){
			$getSubCats=mysql_query("SELECT title,description,title_pretty FROM $cat_table WHERE parent = '$Cat' ORDER BY title ASC");

			if(mysql_num_rows($getSubCats)>0){
				$x=0;
				$page.='<table width="100%"><tr valign="top">';
				while($SubCat=mysql_fetch_assoc($getSubCats)){

					if($x==$CatsPerRow){
						$x=1;
						$page.='</tr><tr valign="top"><td class="wpld_panel" width="33%">';
					}else{
						$x++;
						$page.='<td class="wpld_panel" width="33%">';
					}

					$getNumLinks=mysql_query("SELECT COUNT(*) FROM $links_table WHERE category = '{$SubCat['title']}' AND tagged = 0 AND pending = 0");
					$page.='<a href="'.$Home.($PLinks ? $SubCat['title_pretty'] : $Ex.'cat='.urlencode($SubCat['title'])).'">'.$SubCat['title'].($ShowNums == 'Yes' ? ' ('.mysql_result($getNumLinks,0,0).')' : '').'</a></b><br />'.$SubCat['description'].'</td>';
				}
				$page.='</tr></table><br /><br />';
			}
		}

		if(get_option('wplinkdir_nofollow')=='Yes'){
			$NoFollow=' rel="nofollow"';
		}else{
			$NoFollow='';
		}

		if($LinkFlagging!=''){
			$DeFlagged=preg_replace('/[&\?]flag\=[0-9]+/','',$_SERVER['REQUEST_URI']);
			$PartOne='<a rel="nofollow" href="'.preg_replace('/[&\?]flag\=[0-9]+/','',$_SERVER['REQUEST_URI']);
			$PartTwo=( $PLinks ? '?flag=' : ( empty($_GET) ? '?flag=' : '&flag=' ) );
			$PartThree='">'.$LinkFlagging.'</a>';
		}

		while($Link=@mysql_fetch_assoc($getLinks)){
			$page.='<table class="'.( $Link['premium']>0 ? 'wpld_premium_links' : 'wpld_links' ).'"><tr>';

			if($ShowPR=='Yes'){
				$Width=$Link['pr']*4;
				$page.='<td><div class="wpld_pr">PR: '.$Link['pr'].'
				<div class="wpld_prg"><div class="wpld_prb" style="width: '.$Width.'px"></div></div>
				</td>';
			}

			if($LinkFlagging!=''){
				$LinkFlagging=$PartOne.$PartTwo.$Link['id'].$PartThree;
			}

			if($DetailedInfo!=''){
				if($PLinks){
					$DetailedLink=' - <i><a href="'.$DeFlagged.$Link['id'].'">'.$DetailedInfo.'</a></i>';
				}else{
					$DetailedLink=' - <i><a href="'.$Home.$Ex.'id='.$Link['id'].'">'.$DetailedInfo.'</a></i>';
				}
			}

			$page.='<td><a target="'.$Target.'" href="'.$Link['url'].'"'.$NoFollow.'>'.stripslashes($Link['title']).'</a> - '.$Link['description'].$DetailedLink.' '.$LinkFlagging.'</td>
			</tr></table>';
		}
	}

	if(!isset($_GET['cat']) && !isset($_GET['id']) && !isset($_GET['act'])){
		// Display all the categories, 3 per row is default option value

		$CatsPerRow=get_option('wplinkdir_catsperrow');
		$ShowNums=get_option('wplinkdir_show_numbers');
		$getCats=mysql_query("SELECT * FROM $cat_table WHERE parent = '' ORDER BY title ASC");
		$x=0;

		if(mysql_num_rows($getCats)>0){

			$page.='<table width="100%"><tr valign="top">';

			while($Cat=mysql_fetch_assoc($getCats)){
				$getSubCats=mysql_query("SELECT * FROM $cat_table WHERE parent = '{$Cat['title']}' ORDER BY title ASC LIMIT 3");

				$getLinkCount=mysql_query("SELECT COUNT(*) as count FROM $links_table WHERE category = '{$Cat['title']}' AND tagged = 0 AND pending = 0");
				$LinkCount=mysql_result($getLinkCount,0,0);

				if($x==$CatsPerRow){
					$x=1;
					$page.='</tr><tr valign="top"><td class="wpld_panel" width="33%">';
				}else{
					$x++;
					$page.='<td class="wpld_panel" width="33%">';
				}

				if($_GET){
					$E='&';
				}else{
					$E='?';
				}

				if($PLinks){
					$page.='<b><a href="'.get_bloginfo('url').'/'.$Permalinks.'/'.$Cat['title_pretty'].'">'.$Cat['title'].($ShowNums == 'Yes' ? ' ('.$LinkCount.')' : '').'</a></b>';
				}else{
					$page.='<b><a href="'.$_SERVER['REQUEST_URI'].$E.'cat='.urlencode($Cat['title']).'">'.$Cat['title'].($ShowNums == 'Yes' ? ' ('.$LinkCount.')' : '').'</a></b>';
				}
				// Display sub-categories too
				if(mysql_num_rows($getSubCats)>0){
					$page.='<br /><span style="font-size:smaller">';
					while($SubCat=mysql_fetch_assoc($getSubCats)){
						if($ShowNums=='Yes'){
							$getNumSubLinks=mysql_query("SELECT COUNT(*) FROM $links_table WHERE category = '{$SubCat['title']}' AND tagged = 0 AND pending = 0");
							$NumLinks=' ('.mysql_result($getNumSubLinks,0,0).')';
						}else{
							$NumLinks='';
						}
						if($PLinks){
							$page.='<a href="'.$_SERVER['REQUEST_URI'].urlencode($SubCat['title']).'">'.$SubCat['title'].$NumLinks.'</a>, ';
						}else{
							$page.='<a href="'.$_SERVER['REQUEST_URI'].$E.'cat='.urlencode($SubCat['title']).'">'.$SubCat['title'].$NumLinks.'</a>, ';
						}
					}
					$page=substr($page,0,-2).'</span>';
				}

				$page.='<br />'.$Cat['description'].'</td>';
			}

			$page.='</tr></table>';
		}

		// Cheeky link back to the plugin
		if(get_option('wplinkdir_poweredby')=='Yes'){
			$page.='<div align="right"><font size="-2">'.__('Powered by',$WPLD_Domain).' <a target="'.$Target.'" href="http://www.seanbluestone.com/wp-link-directory">'.$WPLD_Trans['WordPressLinkDirectory'].'</a></font></div>';
		}
	}
	return $page.'</div>';
}


function wplinkdir_linkus(){
	// The Link To Us page. This function isn't used to display the link to us page unless it's requested through a shortcode i.e. [wplinkdir page="Link To Us"]

	$page.='<div class="wpld_page">'.__('Step 1: Add our URL to your page using this HTML code:',$WPLD_Domain).'<br /><br />
	<textarea rows="4" cols="50">'.get_option('wplinkdir_htmlcode').'</textarea><br /><br />

	'.__('This will generate a link like this:',$WPLD_Domain).'<br /><br />
	<div class="wplinkdir_panel">'.get_option('wplinkdir_htmlcode').'</div><br /><br />

	'.__("Step 2: Add your link to our site using <a href=\"{$AddSite}\">this page</a>. You will be added instantly and we will begin sending traffic back to you immediately.",$WPLD_Domain);
	return $page.'</div>';
}


function StrToNum($Str, $Check, $Magic){
	$Int32Unit = 4294967296; // 2^32

	$length = strlen($Str);
	for ($i = 0; $i < $length; $i++) {
		$Check *= $Magic; 	
		// If the float is beyond the boundaries of integer (usually +/- 2.15e+9 = 2^31), 
		// the result of converting to integer is undefined
		// refer to http://www.php.net/manual/en/language.types.integer.php
		if ($Check >= $Int32Unit) {
			$Check = ($Check - $Int32Unit * (int) ($Check / $Int32Unit));
			//if the check less than -2^31
			$Check = ($Check < -2147483648) ? ($Check + $Int32Unit) : $Check;
		}
		$Check += ord($Str{$i}); 
	}
	return $Check;
}

// Genearate a hash for a url

function HashURL($String){
	$Check1 = StrToNum($String, 0x1505, 0x21);
	$Check2 = StrToNum($String, 0, 0x1003F);

	$Check1>>=2; 	
	$Check1=(($Check1 >> 4) & 0x3FFFFC0 ) | ($Check1 & 0x3F);
	$Check1=(($Check1 >> 4) & 0x3FFC00 ) | ($Check1 & 0x3FF);
	$Check1=(($Check1 >> 4) & 0x3C000 ) | ($Check1 & 0x3FFF);	

	$T1 = (((($Check1 & 0x3C0) << 4) | ($Check1 & 0x3C)) <<2 ) | ($Check2 & 0xF0F );
	$T2 = (((($Check1 & 0xFFFFC000) << 4) | ($Check1 & 0x3C00)) << 0xA) | ($Check2 & 0xF0F0000 );

	return ($T1 | $T2);
}

// genearate a checksum for the hash string

function CheckHash($Hashnum){
	$CheckByte=0;
	$Flag=0;

	$HashStr=sprintf('%u', $Hashnum);
	$length=strlen($HashStr);

	for($i=$length-1; $i>=0; $i--) {
		$Re=$HashStr{$i};
		if(1===($Flag % 2)){
			$Re+=$Re;
			$Re=(int)($Re/10)+($Re%10);
		}
		$CheckByte+=$Re;
		$Flag++;
	}

	$CheckByte %= 10;
	if (0 !== $CheckByte){
		$CheckByte=10-$CheckByte;
		if (1 === ($Flag % 2) ) {
			if (1 === ($CheckByte % 2)) {
				$CheckByte += 9;
			}
			$CheckByte >>= 1;
		}
	}
	return '7'.$CheckByte.$HashStr;
}

function getpagerank($url){

	$fp = fsockopen("toolbarqueries.google.com", 80, $errno, $errstr, 30);
	if(!$fp){
		echo "$errstr ($errno)<br />\n";
	}else{
		$out="GET /search?client=navclient-auto&ch=".CheckHash(HashURL($url))."&features=Rank&q=info:".$url."&num=100&filter=0 HTTP/1.1\r\n";
		$out.="Host: toolbarqueries.google.com\r\n";
		$out.="User-Agent: Mozilla/4.0 (compatible; GoogleToolbar 2.0.114-big; Windows XP 5.1)\r\n";
		$out.="Connection: Close\r\n\r\n";

		fwrite($fp, $out);

		while(!feof($fp)){
			$data=fgets($fp, 128);
			$pos=strpos($data, "Rank_");
			if($pos===false){} else{
				$pagerank = substr($data, $pos + 9);
				return $pagerank;
			}
		}
		fclose($fp);
	}
}

function confirm_request($Alert='Are you sure?'){

	// This function just creates a jS confirmation request. You call it by having onclick="return confirmation();" in a submit button and calling this function somewhere before it

	global $WPLD_Domain,$WPLD_Trans;

	$back='<script type="text/javascript"><!--
	function confirmation(){
		var answer = confirm("'.$Alert.'");
		if(answer){
			window.location = "'.$_SERVER['RQUEST_URI'].'";
		}else{
			alert(\''.__('Action Cancelled',$WPLD_Domain).'\');
			return false;
		}
	}
	//--></script>';

	return $back;
}

?>