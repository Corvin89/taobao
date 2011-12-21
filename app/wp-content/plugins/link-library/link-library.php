<?php
/*
Plugin Name: Link Library
Plugin URI: http://wordpress.org/extend/plugins/link-library/
Description: Display links on pages with a variety of options
Version: 4.9.7
Author: Yannick Lefebvre
Author URI: http://yannickcorner.nayanna.biz/

A plugin for the blogging MySQL/PHP-based WordPress.
Copyright 2011 Yannick Lefebvre

Translations:
Danish Translation Courtesy of GeorgWP (http://wordpress.blogos.dk)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

You can also view a copy of the HTML version of the GNU General Public
License at http://www.gnu.org/copyleft/gpl.html

I, Yannick Lefebvre, can be contacted via e-mail at ylefebvre@gmail.com
*/

define('LINK_LIBRARY_ADMIN_PAGE_NAME', 'link-library');

if (is_file(trailingslashit(ABSPATH.PLUGINDIR).'link-library.php')) {
	define('LL_FILE', trailingslashit(ABSPATH.PLUGINDIR).'link-library.php');
}
else if (is_file(trailingslashit(ABSPATH.PLUGINDIR).'link-library/link-library.php')) {
	define('LL_FILE', trailingslashit(ABSPATH.PLUGINDIR).'link-library/link-library.php');
}

define('LLDIR', dirname(__FILE__) . '/');

if ( !defined('WP_ADMIN_URL') )
	define( 'WP_ADMIN_URL', get_option('siteurl') . '/wp-admin');

require_once(ABSPATH . '/wp-admin/includes/bookmark.php');
require_once(ABSPATH . '/wp-admin/includes/taxonomy.php');
require_once(ABSPATH . '/wp-admin/includes/template.php');

$rss_settings = "";
$llpluginpath = "";
$pagehooktop = "";
$pagehookmoderate = "";
$pagehooksettingssets = "";
$pagehookstylesheet = "";
$pagehookreciprocal = "";

/*********************************** Link Library Class *****************************************************************************/
class link_library_plugin {

	//constructor of class, PHP4 compatible construction for backward compatibility
	function link_library_plugin() {
	
		$newoptions = get_option('LinkLibraryPP1', "");

		if ($newoptions == "")
		{
			$this->ll_reset_options(1, 'list');
			$this->ll_reset_gen_settings();
		}

		// Functions to be called when plugin is activated and deactivated
		register_activation_hook(LL_FILE, array($this, 'll_install'));
		register_deactivation_hook(LL_FILE, array($this, 'll_uninstall'));

		//add filter for WordPress 2.8 changed backend box system !
		add_filter('screen_layout_columns', array($this, 'on_screen_layout_columns'), 10, 2);
		//register callback for admin menu  setup
		add_action('admin_menu', array($this, 'on_admin_menu'));
		//add_action('admin_init', array($this, 'action_admin_init' ));
		
		// wp_ajax_... is only run for logged usrs
		//add_action( 'wp_ajax_scn_check_url_action', array( &$this, 'ajax_action_check_url' ) );
		
		//register the callback been used if options of page been submitted and needs to be processed
		add_action('admin_post_save_link_library_general', array($this, 'on_save_changes_general'));
		add_action('admin_post_save_link_library_settingssets', array($this, 'on_save_changes_settingssets'));
		add_action('admin_post_save_link_library_moderate', array($this, 'on_save_changes_moderate'));
		add_action('admin_post_save_link_library_stylesheet', array($this, 'on_save_changes_stylesheet'));
		add_action('admin_post_save_link_library_reciprocal', array($this, 'on_save_changes_reciprocal'));

		// Add short codes
		add_shortcode('link-library-cats', array($this, 'link_library_cats_func'));
		add_shortcode('link-library-search', array($this, 'link_library_search_func'));
		add_shortcode('link-library-addlink', array($this, 'link_library_addlink_func'));
		add_shortcode('link-library-addlinkcustommsg', array($this, 'link_library_addlink_func'));
		add_shortcode('link-library', array($this, 'link_library_func'));

		// Function to print information in page header when plugin present
		add_action('wp_head', array($this, 'll_rss_link'));

		// Function to determine if Link Library is used on a page before printing headers
		add_filter('the_posts', array($this, 'conditionally_add_scripts_and_styles')); // the_posts gets triggered before wp_head

		add_filter('wp_title', array($this, 'll_title_creator'));

		// Add addition section to Link Edition page
		add_meta_box ('linklibrary_meta_box', __('Link Library - Additional Link Parameters', 'link-library'), array($this, 'll_link_edit_extra'), 'link', 'normal', 'high');

		// Capture and process user submissions for custom fields in Link Edition page
		add_action('add_link', array($this, 'add_link_field'));
		add_action('edit_link', array($this, 'add_link_field'));
		add_action('delete_link', array($this, 'delete_link_field'));

		// Re-write rules filters to allow for custom permalinks
		add_filter('rewrite_rules_array', array($this, 'll_insertMyRewriteRules'));
		add_filter('query_vars', array($this, 'll_insertMyRewriteQueryVars'));

		// Under development, trying to display extra columns in link list page
		add_filter('manage_link-manager_columns', array($this, 'll_linkmanager_addcolumn'));
		add_action('manage_link_custom_column', array($this, 'll_linkmanager_populatecolumn'), 10, 2);

		global $llpluginpath;
		$llpluginpath = WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)).'/';

		// Load text domain for translation of admin pages and text strings
		load_plugin_textdomain( 'link-library', $llpluginpath . '/languages', 'link-library/languages');
	}
	
	function db_prefix() {
		global $wpdb;
		if (method_exists($wpdb, "get_blog_prefix"))
			return $wpdb->get_blog_prefix();
		else
			return $wpdb->prefix;
	}

	/************************** Link Library Installation Function **************************/
	function ll_install() {
		global $wpdb;

		$charset_collate = '';
		if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') ) {
			if (!empty($wpdb->charset)) {
				$charset_collate .= " DEFAULT CHARACTER SET $wpdb->charset";
			}
			if (!empty($wpdb->collate)) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}
		}

		$wpdb->links_extrainfo = $this->db_prefix().'links_extrainfo';

		$result = $wpdb->query("
				CREATE TABLE IF NOT EXISTS `$wpdb->links_extrainfo` (
				`link_id` bigint(20) NOT NULL DEFAULT '0',
				`link_second_url` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
				`link_telephone` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
				`link_email` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
				`link_visits` bigint(20) DEFAULT '0',
				`link_reciprocal` varchar(255) DEFAULT NULL,
				`link_submitter` varchar(255) DEFAULT NULL,
				`link_submitter_name` VARCHAR( 128 ) NULL,
				`link_submitter_email` VARCHAR( 128 ) NULL,
				`link_textfield` TEXT NULL,
				`link_no_follow` VARCHAR(1) NULL,
				`link_featured` VARCHAR(1) NULL,
				PRIMARY KEY (`link_id`)
				) $charset_collate"); 

		$genoptions = get_option('LinkLibraryGeneral');

		if ($genoptions != '')
		{
			if ($genoptions['schemaversion'] == '' || floatval($genoptions['schemaversion']) < 3.5)
			{
				$genoptions['schemaversion'] = "3.5";
				update_option('LinkLibraryGeneral', $genoptions);
			}
			elseif (floatval($genoptions['schemaversion']) < "4.6")
			{
				$genoptions['schemaversion'] = "4.6";
				$wpdb->get_results("ALTER TABLE `" . $this->db_prefix() . "links_extrainfo` ADD `link_submitter_name` VARCHAR( 128 ) NULL, ADD `link_submitter_email` VARCHAR( 128 ) NULL , ADD `link_textfield` TEXT NULL ;");
				
				update_option('LinkLibraryGeneral', $genoptions);
			}
			elseif (floatval($genoptions['schemaversion']) < "4.7")
			{
				$genoptions['schemaversion'] = "4.7";
				$wpdb->get_results("ALTER TABLE `" . $this->db_prefix() . "links_extrainfo` ADD `link_no_follow` VARCHAR( 1 ) NULL;");
				
				update_option('LinkLibraryGeneral', $genoptions);
			}
			elseif (floatval($genoptions['schemaversion']) < "4.9")
			{
				$genoptions['schemaversion'] = "4.9";
				$wpdb->get_results("ALTER TABLE `" . $this->db_prefix() . "links_extrainfo` ADD `link_featured` VARCHAR( 1 ) NULL;");
				
				update_option('LinkLibraryGeneral', $genoptions);
			}

			for ($i = 1; $i <= $genoptions['numberstylesets']; $i++) {
				$settingsname = 'LinkLibraryPP' . $i;
				$options = get_option($settingsname);

				if ($options != '')
				{
					if ($options['showname'] == '')
						$options['showname'] = true;

					if ($options['show_image_and_name'] == true)
					{
						$options['showname'] = true;
						$options['show_images'] = true;
					}

					if ($options['sourcename'] == '')
						$options['sourcename'] = 'primary';

					if ($options['sourceimage'] == '')
						$options['sourceimage'] = 'primary';

					if ($options['dragndroporder'] == '')
					{
						if ($options['imagepos'] == 'beforename')
							$options['dragndroporder'] = '1,2,3,4,5,6,7,8,9,10';
						elseif ($options['imagepos'] == 'aftername')
							$options['dragndroporder'] = '2,1,3,4,5,6,7,8,9,10';
						elseif ($options['imagepos'] == 'afterrssicons')
							$options['dragndroporder'] = '2,3,4,5,6,1,7,8,9,10';
					}
					else if ($options['dragndroporder'] != '')
						$elementarray = explode(',', $options['dragndroporder']);
					{
						$allelements = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12');
						foreach ($allelements as $element)
						{
							if (!in_array($element, $elementarray))
							{
								$elementarray[] = $element;
								$options['dragndroporder'] = implode(",", $elementarray);
							}
						}
					}
					
				}

				update_option($settingsname, $options);
			}
		}
	}
	
	/************************** Link Library Uninstall Function **************************/
	function ll_uninstall() {
		$genoptions = get_option('LinkLibraryGeneral');
		
		if ($genoptions != '')
		{
			if ($genoptions['stylesheet'] != '' && $genoptions['fullstylesheet'] == '')
			{
				$stylesheetlocation = LLDIR . $genoptions['stylesheet'];
				if (file_exists($stylesheetlocation))
					$genoptions['fullstylesheet'] = file_get_contents($stylesheetlocation);

				update_option('LinkLibraryGeneral', $genoptions);
			}
		}
	}
	
	/************************** Function called to create default settings or to reset them on user request **************************/
	function ll_reset_options($settings = 1, $layout = 'list')
	{
		$uploads = wp_upload_dir();
		
		if ($layout == "list")
		{
			$options['num_columns'] = 1;
			$options['showdescription'] = false;
			$options['shownotes'] = false;
			$options['beforenote'] = '<br />';
			$options['afternote'] = '';
			$options['beforeitem'] = '<li>';
			$options['afteritem'] = '</li>';
			$options['beforedesc'] = '';
			$options['afterdesc'] = '';
			$options['displayastable'] = false;
			$options['beforelink'] = '';
			$options['afterlink'] = '';
			$options['showcolumnheaders'] = false;
			$options['linkheader'] = '';
			$options['descheader'] = '';
			$options['notesheader'] = '';
			$options['beforerss'] = '';
			$options['afterrss'] = '';
			$options['beforedate'] = '';
			$options['afterdate'] = '';
			$options['beforeimage'] = '';
			$options['afterimage'] = '';
			$options['beforeweblink'] = '';
			$options['afterweblink'] = '';
			$options['beforetelephone'] = '';
			$options['aftertelephone'] = '';
			$options['beforeemail'] = '';
			$options['afteremail'] = '';
			$options['beforelinkhits'] = '';
			$options['afterlinkhits'] = '';
		}
		elseif ($layout == "table")
		{
			$options['num_columns'] = 3;
			$options['showdescription'] = true;
			$options['shownotes'] = true;
			$options['beforenote'] = '<td>';
			$options['afternote'] = '</td>';
			$options['beforeitem'] = '<tr>';
			$options['afteritem'] = '</tr>';
			$options['beforedesc'] = '<td>';
			$options['afterdesc'] = '</td>';
			$options['displayastable'] = true;
			$options['beforelink'] = '<td>';
			$options['afterlink'] = '</td>';
			$options['showcolumnheaders'] = true;
			$options['linkheader'] = 'Application';
			$options['descheader'] = 'Description';
			$options['notesheader'] = 'Similar to';
			$options['beforerss'] = '<td>';
			$options['afterrss'] = '</td>';
			$options['beforedate'] = '<td>';
			$options['afterdate'] = '</td>';
			$options['beforeimage'] = '<td>';
			$options['afterimage'] = '</td>';
			$options['beforeweblink'] = '<td>';
			$options['afterweblink'] = '</td>';	
			$options['beforetelephone'] = '<td>';
			$options['aftertelephone'] = '</td>';
			$options['beforeemail'] = '<td>';
			$options['afteremail'] = '</td>';
			$options['beforelinkhits'] = '<td>';
			$options['afterlinkhits'] = '</td>';
		}

		$options['order'] = 'name';
		$options['hide_if_empty'] = true;
		$options['table_width'] = 100;
		$options['catanchor'] = true;
		$options['flatlist'] = false;
		$options['categorylist'] = null;
		$options['excludecategorylist'] = null;
		$options['showrating'] = false;
		$options['showupdated'] = false;
		$options['show_images'] = false;
		$options['use_html_tags'] = false;
		$options['show_rss'] = false;
		$options['nofollow'] = false;
		$options['catlistwrappers'] = 1;
		$options['beforecatlist1'] = '';
		$options['beforecatlist2'] = '';
		$options['beforecatlist3'] = '';
		$options['divorheader'] = false;
		$options['catnameoutput'] = 'linklistcatname';
		$options['show_rss_icon'] = false;
		$options['linkaddfrequency'] = 0;
		$options['addbeforelink'] = '';
		$options['addafterlink'] = '';
		$options['linktarget'] = '';
		$options['showcategorydescheaders'] = false;
		$options['showcategorydesclinks'] = false;
		$options['settingssetname'] = 'Default';
		$options['showadmineditlinks'] = true;
		$options['showonecatonly'] = false;
		$options['loadingicon'] = '/icons/Ajax-loader.gif';
		$options['defaultsinglecat'] = '';
		$options['rsspreview'] = false;
		$options['rsspreviewcount'] = 3;
		$options['rssfeedinline'] = false;
		$options['rssfeedinlinecontent'] = false;
		$options['rssfeedinlinecount'] = 1;
		$options['rsscachedir'] = ABSPATH . 'wp-content/cache/link-library';
		$options['direction'] = 'ASC';
		$options['linkdirection'] = 'ASC';
		$options['linkorder'] = 'name';
		$options['pagination'] = false;
		$options['linksperpage'] = 5;
		$options['hidecategorynames'] = false;
		$options['showinvisible'] = false;
		$options['showdate'] = false;
		$options['catdescpos'] = 'right';
		$options['catlistdescpos'] = 'right';
		$options['showuserlinks'] = false;
		$options['addnewlinkmsg'] = __('Add new link', 'link-library');
		$options['linknamelabel'] = __('Link name', 'link-library');
		$options['linkaddrlabel'] = __('Link address', 'link-library');
		$options['linkrsslabel'] = __('Link RSS', 'link-library');
		$options['linkcatlabel'] = __('Link Category', 'link-library');
		$options['linkdesclabel'] = __('Link Description', 'link-library');
		$options['linknoteslabel'] = __('Link Notes', 'link-library');
		$options['addlinkbtnlabel'] = __('Add Link', 'link-library');
		$options['newlinkmsg'] = __('New link submitted', 'link-library');
		$options['moderatemsg'] = __('it will appear in the list once moderated. Thank you.', 'link-library');
		$options['rsspreviewwidth'] = 900;
		$options['rsspreviewheight'] = 700;
		$options['imagepos'] = 'beforename';
		$options['imageclass'] = '';
		$options['emailnewlink'] = false;
		$options['showaddlinkrss'] = false;
		$options['showaddlinkdesc'] = false;
		$options['showaddlinkcat'] = false;
		$options['showaddlinknotes'] = false;
		$options['usethumbshotsforimages'] = false;
		$options['addlinkreqlogin'] = false;
		$options['showcatlinkcount'] = false;
		$options['publishrssfeed'] = false;
		$options['numberofrssitems'] = 10;
		$options['rssfeedtitle'] = __('Link Library-Generated RSS Feed', 'link-library');
		$options['rssfeeddescription'] = __('Description of Link Library-Generated Feed', 'link-library');
		$options['showonecatmode'] = 'AJAX';
		$options['addlinkcustomcat'] = false;
		$options['linkcustomcatlabel'] = __('User-submitted category', 'link-library');
		$options['linkcustomcatlistentry'] = __('User-submitted category (define below)', 'link-library');
		$options['searchlabel'] = 'Search';
		$options['dragndroporder'] = '1,2,3,4,5,6,7,8,9,10';
		$options['showname'] = true;
		$options['cattargetaddress'] = '';
		$options['displayweblink'] = 'false';
		$options['sourceweblink'] = 'primary';
		$options['showtelephone'] = 'false';
		$options['sourcetelephone'] = 'primary';
		$options['showemail'] = 'false';
		$options['showlinkhits'] = false;
		$options['weblinklabel'] = '';
		$options['telephonelabel'] = '';
		$options['emaillabel'] = '';
		$options['showaddlinkreciprocal'] = false;
		$options['linkreciprocallabel'] = __('Reciprocal Link', 'link-library');
		$options['showaddlinksecondurl'] = false;
		$options['linksecondurllabel'] = __('Secondary Address', 'link-library');
		$options['showaddlinktelephone'] = false;
		$options['linktelephonelabel'] = __('Telephone', 'link-library');
		$options['showaddlinkemail'] = false;
		$options['linkemaillabel'] = __('E-mail', 'link-library');
		$options['emailcommand'] = '';
		$options['sourceimage'] = 'primary';
		$options['sourcename'] = 'primary';
		$options['enablerewrite'] = false;
		$options['rewritepage'] = '';
		$options['storelinksubmitter'] = false;
		$options['maxlinks'] = '';
		$options['showcaptcha'] = false;
		$options['beforelinkrating'] = '';
		$options['afterlinkrating'] = '';
		$options['linksubmitternamelabel'] = __('Submitter Name', 'link-library');
		$options['showlinksubmittername'] = false;
		$options['linksubmitteremaillabel'] = __('Submitter E-mail', 'link-library');
		$options['showaddlinksubmitteremail'] = false;
		$options['linksubmittercommentlabel'] = __('Submitter Comment', 'link-library');
		$options['showlinksubmittercomment'] = false;
		$options['addlinkcatlistoverride'] = '';
		$options['showlargedescription'] = false;
		$options['beforelargedescription'] = '';
		$options['afterlargedescription'] = '';
		$options['showcustomcaptcha'] = false;
		$options['customcaptchaquestion'] = __('Is boiling water hot or cold?', 'link-library');
		$options['customcaptchaanswer'] = __('hot','link-library');
		$options['rssfeedaddress'] = '';
		$options['addlinknoaddress'] = false;
		$options['featuredfirst'] = false;
		$options['linklargedesclabel'] = __('Large Description', 'link-library');
		$options['showuserlargedescription'] = false;

		$settingsname = 'LinkLibraryPP' . $settings;
		update_option($settingsname, $options);	
	}

	// Function used to set initial settings or reset them on user request
	function ll_reset_gen_settings()
	{
		$genoptions['numberstylesets'] = 1;
		$genoptions['includescriptcss'] = '';
		$genoptions['debugmode'] = false;
		$genoptions['schemaversion'] = "4.6";
		$genoptions['pagetitleprefix'] = '';
		$genoptions['pagetitlesuffix'] = '';
		$genoptions['thumbshotscid'] = '';
		$genoptions['emaillinksubmitter'] = false;
		$genoptions['moderatorname'] = '';
		$genoptions['moderatoremail'] = '';	
		$genoptions['approvalemailtitle'] = '';
		$genoptions['approvalemailbody'] = '';
		$genoptions['rejectedemailtitle'] = '';
		$genoptions['rejectedemailbody'] = '';
		$genoptions['moderationnotificationtitle'] = '';
		$genoptions['linksubmissionthankyouurl'] = '';
		$genoptions['usefirstpartsubmittername'] = '';
		$genoptions['recipcheckaddress'] = get_bloginfo('wpurl');
		
		$stylesheetlocation = get_bloginfo('wpurl') . '/wp-content/plugins/link-library/stylesheettemplate.css';
		$genoptions['fullstylesheet'] = file_get_contents($stylesheetlocation);

		update_option('LinkLibraryGeneral', $genoptions);
	}

	/* the function */
	function remove_querystring_var($url, $key) { 
	
		$keypos = strpos($url, $key);
		if ($keypos)
		{
			$ampersandpos = strpos($url, '&', $keypos);
			$newurl = substr($url, 0, $keypos - 1);
			
			if ($ampersandpos)
				$newurl .= substr($url, $ampersandpos);
		}
		else
			$newurl = $url;
		
		return $newurl; 
	}
	
	function ReciprocalLinkChecker($RecipCheckAddress = '') {
		global $wpdb;
		
		if ($RecipCheckAddress != '')
		{		
			$linkquery = "SELECT distinct *, l.link_id as proper_link_id, UNIX_TIMESTAMP(l.link_updated) as link_date, ";
			$linkquery .= "IF (DATE_ADD(l.link_updated, INTERVAL " . get_option('links_recently_updated_time') . " MINUTE) >= NOW(), 1,0) as recently_updated ";
			$linkquery .= "FROM " . $this->db_prefix() . "terms t ";
			$linkquery .= "LEFT JOIN " . $this->db_prefix() . "term_taxonomy tt ON (t.term_id = tt.term_id) ";
			$linkquery .= "LEFT JOIN " . $this->db_prefix() . "term_relationships tr ON (tt.term_taxonomy_id = tr.term_taxonomy_id) ";
			$linkquery .= "LEFT JOIN " . $this->db_prefix() . "links l ON (tr.object_id = l.link_id) ";
			$linkquery .= "LEFT JOIN " . $this->db_prefix() . "links_extrainfo le ON (l.link_id = le.link_id) ";	
			$linkquery .= "WHERE tt.taxonomy = 'link_category' ";
			$linkquery .= "AND le.link_reciprocal <> '' ";
			$linkquery .= "order by l.link_name ASC";
			
			$links = $wpdb->get_results($linkquery);
			$output = "<strong>Reciprocal Link Checker Report</strong><br /><br />";

			if ($links)
			{
				foreach($links as $link)
				{
					$sitecontent = file_get_contents($link->link_reciprocal);
					$output .= "<a href='" . $link->link_url . "'>" . $link->link_name . "</a>: ";
					if (strpos($sitecontent, $RecipCheckAddress) === false)
					{
						$output .= "<span style='color: #FF0000'>Not Found</span><br />";
					}
					elseif (strpos($sitecontent, $RecipCheckAddress) !== false)
					{
						$output .= "<span style='color: #00FF00'>OK</span><br />";
					}
				}
			}
			else
			{
				$output = "There are no links with reciprocal links associated with them.<br />";
			}			
			
			return $output;
		}
	}

	function ll_get_link_image($url, $name, $mode, $linkid, $cid, $filepath)
	{
		if ($url != "" && $name != "")
		{
			if ($mode == 'thumb' || $mode == 'thumbonly')
			{
				if ($cid == '')
					$genthumburl = "http://open.thumbshots.org/image.aspx?url=" . wp_specialchars($url);
				elseif ($cid != '')
					$genthumburl = "http://images.thumbshots.com/image.aspx?cid=" . $cid . "&v1=w=120&h=90&url=" . wp_specialchars($url);
			}
			elseif ($mode == 'favicon' || $mode == 'favicononly')
			{
				$strippedurl = str_replace("http://", "", wp_specialchars($url));
				$genthumburl = "http://www.getfavicon.org/?url=" . $strippedurl . "/favicon.png";
			}
			
			$uploads = wp_upload_dir();
			
			if (!file_exists($uploads['basedir']))
			{
				return __('Please create a folder called uploads under your Wordpress /wp-content/ directory with write permissions to use this functionality.', 'link-library');				
			}
			elseif (!is_writable($uploads['basedir']))
			{
				return __('Please make sure that the /wp-content/uploads/ directory has write permissions to use this functionality.', 'link-library');				
			}
			else
			{
				if (!file_exists($uploads['basedir'] . '/' . $filepath))
					mkdir($uploads['basedir'] . '/' . $filepath);
			}			
			
			$img = $uploads['basedir'] . "/" . $filepath. "/" . $linkid . ".jpg";
			$status = file_put_contents($img, file_get_contents($genthumburl));

			if ($status !== false)
			{
				$newimagedata = array("link_id" => $linkid, "link_image" => $uploads['baseurl'] . "/" . $filepath . "/" . $linkid . ".jpg");

				if ($mode == 'thumb' || $mode == 'favicon')
					wp_update_link($newimagedata);

				return $newimagedata['link_image'];
			}
			else
				return "";
		}
		return "Parameters are missing";
	}

	//for WordPress 2.8 we have to tell, that we support 2 columns !
	function on_screen_layout_columns($columns, $screen) {
		return $columns;
	}
	
	/**
	 * Returns the full URL of this plugin including trailing slash.
	 */
	function plugin_url() {
		
		return WP_PLUGIN_URL . '/' . str_replace( basename( __FILE__ ), "", plugin_basename( __FILE__ ) );
	}
	
	function action_admin_init() {
		
		if ( current_user_can( 'edit_posts' ) 
		  && current_user_can( 'edit_pages' ) 
		  && get_user_option('rich_editing') == 'true' )  {
		  	
			add_filter( 'mce_buttons',          array( $this, 'filter_mce_buttons'          ) );
			add_filter( 'mce_external_plugins', array( $this, 'filter_mce_external_plugins' ) );
						
			wp_register_style('scnStyles', $this->plugin_url() . 'css/styles.css');
			wp_enqueue_style('scnStyles');
		}
	}
	
	function filter_mce_buttons( $buttons ) {
		
		array_push( $buttons, '|', 'scn_button');
		return $buttons;
	}
	
	function filter_mce_external_plugins( $plugins ) {
		
        $plugins['LinkLibraryPlugin'] = $this->plugin_url() . 'tinymce/editor_plugin.js';
        return $plugins;
	}
	
	function ajax_action_check_url() {

		$hadError = true;

		$url = isset( $_REQUEST['url'] ) ? $_REQUEST['url'] : '';

		if ( strlen( $url ) > 0  && function_exists( 'get_headers' ) ) {
				
			$file_headers = @get_headers( $url );
			$exists       = $file_headers && $file_headers[0] != 'HTTP/1.1 404 Not Found';
			$hadError     = false;
		}

		echo '{ "exists": '. ($exists ? '1' : '0') . ($hadError ? ', "error" : 1 ' : '') . ' }';

		die();
	}


	//extend the admin menu
	function on_admin_menu() {
		//add our own option page, you can also add it to different sections or use your own one
		global $wpdb, $llpluginpath, $pagehooktop, $pagehookmoderate, $pagehooksettingssets, $pagehookstylesheet, $pagehookreciprocal;
		
		$linkmoderatecount = 0;
		
		$linkmoderatequery = "SELECT count(*) ";
		$linkmoderatequery .= "FROM " . $this->db_prefix() . "links l ";
		$linkmoderatequery .= "WHERE l.link_description like '%LinkLibrary:AwaitingModeration:RemoveTextToApprove%' ";
		$linkmoderatequery .= " ORDER by link_name ASC";

		$linkmoderatecount = $wpdb->get_var($linkmoderatequery);
		
		$pagehooktop = add_menu_page(__('Link Library General Options', 'link-library'), "Link Library", 'manage_options', LINK_LIBRARY_ADMIN_PAGE_NAME, array($this, 'on_show_page'), $llpluginpath . '/icons/folder-beige-internet-icon.png');
		
		$pagehooksettingssets = add_submenu_page( LINK_LIBRARY_ADMIN_PAGE_NAME, __('Link Library Settings', 'link-library') , __('Library Settings', 'link-library'), 'manage_options', 'link-library-settingssets', array($this,'on_show_page'));
		
		if ($linkmoderatecount == 0)
			$pagehookmoderate = add_submenu_page( LINK_LIBRARY_ADMIN_PAGE_NAME, __('Link Library - Moderate', 'link-library') , __('Moderate', 'link-library'), 'manage_options', 'link-library-moderate', array($this,'on_show_page'));
		else
			$pagehookmoderate = add_submenu_page( LINK_LIBRARY_ADMIN_PAGE_NAME, __('Link Library - Moderate', 'link-library') , sprintf( __('Moderate', 'link-library') . ' %s', "<span class='update-plugins count-" . $linkmoderatecount . "'><span class='plugin-count'>" . number_format_i18n($linkmoderatecount) . "</span></span>"), 'manage_options', 'link-library-moderate', array($this,'on_show_page'));	
		
		$pagehookstylesheet = add_submenu_page( LINK_LIBRARY_ADMIN_PAGE_NAME, __('Link Library - Stylesheet', 'link-library') , __('Stylesheet', 'link-library'), 'manage_options', 'link-library-stylesheet', array($this,'on_show_page')); 
		
		$pagehookreciprocal = add_submenu_page( LINK_LIBRARY_ADMIN_PAGE_NAME, __('Link Library - Reciprocal Checker', 'link-library') , __('Reciprocal Check', 'link-library'), 'manage_options', 'link-library-reciprocal', array($this,'on_show_page')); 
		
		//register  callback gets call prior your own page gets rendered
		add_action('load-'.$pagehooktop, array($this, 'on_load_page'));
		add_action('load-'.$pagehooksettingssets, array($this, 'on_load_page'));
		add_action('load-'.$pagehookmoderate, array($this, 'on_load_page'));
		add_action('load-'.$pagehookstylesheet, array($this, 'on_load_page'));
		add_action('load-'.$pagehookreciprocal, array($this, 'on_load_page'));
	}

	//will be executed if wordpress core detects this page has to be rendered
	function on_load_page() {
	
		global $pagehooktop, $pagehookmoderate, $pagehooksettingssets, $pagehookstylesheet, $pagehookreciprocal;
		
		//ensure, that the needed javascripts been loaded to allow drag/drop, expand/collapse and hide/show of boxes
		wp_enqueue_script('tiptip', get_bloginfo('wpurl').'/wp-content/plugins/link-library/tiptip/jquery.tipTip.minified.js', "jQuery", "1.0rc3");
		wp_enqueue_style('tiptipstyle', get_bloginfo('wpurl').'/wp-content/plugins/link-library/tiptip/tipTip.css');
		add_thickbox();
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');

		//add several metaboxes now, all metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
		add_meta_box('linklibrary_general_meta_box', __('General Settings', 'link-library'), array($this, 'general_meta_box'), $pagehooktop, 'normal', 'high');
		add_meta_box('linklibrary_general_moderation_meta_box', __('General Moderation Options', 'link-library'), array($this, 'general_moderation_meta_box'), $pagehooktop, 'normal', 'high');
		add_meta_box('linklibrary_general_save_meta_box', __('Save', 'link-library'), array($this, 'general_save_meta_box'), $pagehooktop, 'normal', 'high');
		add_meta_box('linklibrary_moderation_meta_box', __('Links awaiting moderation', 'link-library'), array($this, 'moderate_meta_box'), $pagehookmoderate, 'normal', 'high');
		add_meta_box('linklibrary_stylesheet_meta_box', __('Editor', 'link-library'), array($this, 'stylesheet_meta_box'), $pagehookstylesheet, 'normal', 'high');
		add_meta_box('linklibrary_settingssets_usage_meta_box', __('Library Selection and Usage Instructions', 'link-library'), array($this, 'settingssets_usage_meta_box'), $pagehooksettingssets, 'normal', 'high');
		add_meta_box('linklibrary_settingssets_side_meta_box', __('Save', 'link-library'), array($this, 'settingssets_save_meta_box'), $pagehooksettingssets, 'normal', 'high');				
		add_meta_box('linklibrary_settingssets_common_meta_box', __('Common Parameters', 'link-library'), array($this, 'settingssets_common_meta_box'), $pagehooksettingssets, 'normal', 'high');
 		add_meta_box('linklibrary_settingssets_categories_meta_box', __('Link Categories Settings', 'link-library'), array($this, 'settingssets_categories_meta_box'), $pagehooksettingssets, 'normal', 'high');
		add_meta_box('linklibrary_settingssets_linkelement_meta_box', __('Link Element Settings', 'link-library'), array($this, 'settingssets_linkelement_meta_box'), $pagehooksettingssets, 'normal', 'high');
		add_meta_box('linklibrary_settingssets_subfieldtable_meta_box', __('Link Sub-Field Configuration Table', 'link-library'), array($this, 'settingssets_subfieldtable_meta_box'), $pagehooksettingssets, 'normal', 'high');
		add_meta_box('linklibrary_settingssets_rssconfig_meta_box', __('RSS Field Configuration', 'link-library'), array($this, 'settingssets_rssconfig_meta_box'), $pagehooksettingssets, 'normal', 'high');
		add_meta_box('linklibrary_settingssets_thumbnails_meta_box', __('Thumbnail Generation and Use', 'link-library'), array($this, 'settingssets_thumbnails_meta_box'), $pagehooksettingssets, 'normal', 'high');
		add_meta_box('linklibrary_settingssets_rssgen_meta_box', __('RSS Generation', 'link-library'), array($this, 'settingssets_rssgen_meta_box'), $pagehooksettingssets, 'normal', 'high');
		add_meta_box('linklibrary_settingssets_search_meta_box', __('Search Form Configuration', 'link-library'), array($this, 'settingssets_search_meta_box'), $pagehooksettingssets, 'normal', 'high');
		add_meta_box('linklibrary_settingssets_linksubmission_meta_box', __('Link User Submission', 'link-library'), array($this, 'settingssets_linksubmission_meta_box'), $pagehooksettingssets, 'normal', 'high');		
		add_meta_box('linklibrary_settingssets_importexport_meta_box', __('Import / Export', 'link-library'), array($this, 'settingssets_importexport_meta_box'), $pagehooksettingssets, 'normal', 'high');		
		add_meta_box('linklibrary_settingssets_side_meta_box_2', __('Save', 'link-library'), array($this, 'settingssets_save_meta_box'), $pagehooksettingssets, 'normal', 'high');
		add_meta_box('linklibrary_reciprocal_meta_box', __('Reciprocal Link Checker', 'link-library'), array($this, 'reciprocal_meta_box'), $pagehookreciprocal, 'normal', 'high');
		add_meta_box('linklibrary_reciprocal_save_meta_box', __('Save', 'link-library'), array($this, 'general_save_meta_box'), $pagehookreciprocal, 'normal', 'high');		
	}

	//executed to show the plugins complete admin page
	function on_show_page() {
		//we need the global screen column value to beable to have a sidebar in WordPress 2.8
		global $screen_layout_columns, $llpluginpath;

		// Retrieve general options
		$genoptions = get_option('LinkLibraryGeneral');

		// If general options don't exist, create them
		if ($genoptions == FALSE)
		{
			$this->ll_reset_gen_settings();
		}
		elseif ($genoptions['schemaversion'] == '' || floatval($genoptions['schemaversion']) < "4.6") // If they exist, make sure they are up to date
		{
			$this->ll_install();
			$genoptions = get_option('LinkLibraryGeneral');

			if ($settings == FALSE)
				$options = get_option('LinkLibraryPP1');
			else
			{
				$settingsname = 'LinkLibraryPP' . $settings;
				$options = get_option($settingsname);
			}
		}

		if ( isset($_GET['genthumbs']) || isset($_GET['genfavicons']) || isset($_GET['genthumbsingle']) || isset($_GET['genfaviconsingle'])) {
			global $wpdb;

			if (isset($_GET['genthumbs']) || isset($_GET['genthumbsingle']))
				$filepath = "link-library-images";
			elseif (isset($_GET['genfavicons']) || isset($_GET['genfaviconsingle']))
				$filepath = "link-library-favicons";

			$uploads = wp_upload_dir();
			
			if (!file_exists($uploads['basedir']))
			{
				echo "<div id='message' class='updated fade'><p><strong>" . __('Please create a folder called uploads under your Wordpress /wp-content/ directory with write permissions to use this functionality.', 'link-library') . "</strong></p></div>";				
			}
			elseif (!is_writable($uploads['basedir']))
			{
				echo "<div id='message' class='updated fade'><p><strong>" . __('Please make sure that the /wp-content/uploads/ directory has write permissions to use this functionality.', 'link-library') . "</strong></p></div>";				
			}
			else
			{
				if (!file_exists($uploads['basedir'] . '/' . $filepath))
					mkdir($uploads['basedir'] . '/' . $filepath);
					
				if (isset($_GET['genthumbs']) || isset($_GET['genthumbsingle']))
				{
					$settings = $_GET['genthumbs'];
					$genmode = 'thumb';
				}
				elseif (isset($_GET['genfavicons']) || isset($_GET['genfaviconsingle']))
				{
					$settings = $_GET['genfavicons'];
					$genmode = 'favicon';
				}

				$settingsname = 'LinkLibraryPP' . $settings;
				$options = get_option($settingsname);

				$genoptions = get_option('LinkLibraryGeneral');

				$linkquery = "SELECT distinct * ";
				$linkquery .= "FROM " . $this->db_prefix() . "terms t ";
				$linkquery .= "LEFT JOIN " . $this->db_prefix() . "term_taxonomy tt ON (t.term_id = tt.term_id) ";
				$linkquery .= "LEFT JOIN " . $this->db_prefix() . "term_relationships tr ON (tt.term_taxonomy_id = tr.term_taxonomy_id) ";
				$linkquery .= "LEFT JOIN " . $this->db_prefix() . "links l ON (tr.object_id = l.link_id) ";
				$linkquery .= "WHERE tt.taxonomy = 'link_category' ";

				if ($options['categorylist'] != "" && !isset($_GET['genthumbsingle']) && !isset($_GET['genfaviconsingle']))
					$linkquery .= " AND t.term_id in (" . $options['categorylist'] . ")";

				if (isset($_GET['genthumbsingle']) || isset($_GET['genfaviconsingle']))
					$linkquery .= " AND l.link_id = " . $_GET['linkid'];

				$linkitems = $wpdb->get_results($linkquery);

				if ($linkitems)
				{
					$filescreated = 0;
					$totallinks = count($linkitems);
					foreach($linkitems as $linkitem)
					{
						$this->ll_get_link_image($linkitem->link_url, $linkitem->link_name, $genmode, $linkitem->link_id, $genoptions['thumbshotscid'], $filepath);
						$linkname = $linkitem->link_name;
					}

					if (isset($_GET['genthumbs']))
						echo "<div id='message' class='updated fade'><p><strong>" . __('Thumbnails successfully generated!', 'link-library') . "</strong></p></div>";
					elseif (isset($_GET['genfavicons']))
						echo "<div id='message' class='updated fade'><p><strong>" . __('Favicons successfully generated!', 'link-library') . "</strong></p></div>";
					elseif (isset($_GET['genthumbsingle']))
						echo "<div id='message' class='updated fade'><p><strong>" . __('Thumbnail successfully generated for', 'link-library') . " " . $linkname . ".</strong></p></div>";
					elseif (isset($_GET['genfaviconsingle']))
						echo "<div id='message' class='updated fade'><p><strong>" . __('Favicon successfully generated for', 'link-library') . " " . $linkname . ".</strong></p></div>";
				}
			}
		}

		// Check for current page to set some page=specific variables
		if ($_GET['page'] == 'link-library')
		{
			if ($_GET['message'] == '1')
				echo "<div id='message' class='updated fade'><p><strong>" . __('General Settings Saved', 'link-library') . ".</strong></p></div>";
			$formvalue = 'save_link_library_general';
			$pagetitle = "Link Library General Settings";
		}
		elseif ($_GET['page'] == 'link-library-settingssets')
		{
			$formvalue = 'save_link_library_settingssets';

			if ( isset($_GET['reset'])) {
				$settings = $_GET['reset'];
				$this->ll_reset_options($settings, 'list');
			}

			if ( isset($_GET['resettable']) ) {
				$settings = $_GET['resettable'];
				$this->ll_reset_options($settings, 'table');
			}

			if ( isset($_GET['settings']) && $settings == '') {
				$settings = $_GET['settings'];
			}
			else
			{
				$settings = 1;
			}

			if ( isset($_GET['copy']))
			{
				$destination = $_GET['copy'];
				$source = $_GET['source'];

				$sourcesettingsname = 'LinkLibraryPP' . $source;
				$sourceoptions = get_option($sourcesettingsname);

				$destinationsettingsname = 'LinkLibraryPP' . $destination;
				update_option($destinationsettingsname, $sourceoptions);

				$settings = $destination;
			}

			if ( isset($_GET['deletesettings']) ) {
				$settings = $_GET['deletesettings'];
				$settingsname = 'LinkLibraryPP' . $settings;
				$options = delete_option($settingsname);
				$settings = 1;
			}

			if ($settings == '')
			{
				$options = get_option('LinkLibraryPP1');
				$settings = 1;
			}
			else
			{
				$settingsname = 'LinkLibraryPP' . $settings;
				$options = get_option($settingsname);
			}
			
			if ($options == "")
			{
				$this->ll_reset_options($settings, 'list');
				$options = get_option($settingsname);
			}
			
			$pagetitle = "Link Library #" . $settings . " - " . $options['settingssetname'];

			if (isset($_GET['messages']))
			{
				$messagelist = explode(",", $_GET['messages']);

				foreach ($messagelist as $message)
				{
					switch($message) {

						case '1':
							echo "<div id='message' class='updated fade'><p><strong>" . __('Library #', 'link-library') . $settings . " " . __('Updated', 'link-library') . "!</strong></p></div>";				
							break;

						case '2':
							echo '<br /><br />' . __('Included Category ID', 'link-library') . ' ' . $categoryid . ' ' . __('is invalid. Please check the ID in the Link Category editor.', 'link-library');						
							break;

						case '3':
							echo '<br /><br />' . __('Excluded Category ID', 'link-library') . ' ' . $categoryid . ' ' . __('is invalid. Please check the ID in the Link Category editor.', 'link-library');						
							break;

						case '4':
							echo "<div id='message' class='updated fade'><p><strong>" . __('Invalid column count for link on row', 'link-library') . "</strong></p></div>";
							break;

						case '5':
							echo "<div id='message' class='updated fade'><p><strong>" . __('Library Settings Exported', 'link-library') . ". <a href='" . $llpluginpath . "SettingSet" . $settings . "Export.csv'>" . __('Download here', 'link-library') . "</a>.</strong></p></div>";
							break;

						case '6':
							echo "<div id='message' class='updated fade'><p><strong>" . __('Link Library plugin directory needs to be writable to perform this action', 'link-library') . ".</strong></p></div>";
							break;

						case '7':
							echo "<div id='message' class='updated fade'><p><strong>" . __('Library Settings imported successfully', 'link-library') . ".</strong></p></div>";
							break;

						case '8':
							echo "<div id='message' class='updated fade'><p><strong>" . __('Library Settings Upload Failed', 'link-library') . "</strong></p></div>";
							break;

						case '9':
							echo "<div id='message' class='updated fade'><p><strong>" . $_GET['importrowscount'] . " " . __('row(s) found', 'link-library') . ". " . $_GET['successimportcount'] . " " . __('link(s) imported successfully', 'link-library') . ".</strong></p></div>";						

					}

				}

			}
		}
		elseif ($_GET['page'] == 'link-library-moderate')
		{
			$formvalue = 'save_link_library_moderate';
			$pagetitle = "Link Library Link Moderation";

			if ($_GET['message'] == '1')
				echo "<div id='message' class='updated fade'><p><strong>" . __('Link(s) Approved', 'link-library') . "</strong></p></div>";
			elseif ($_GET['message'] == '2')
				echo "<div id='message' class='updated fade'><p><strong>" . __('Link(s) Deleted', 'link-library') . "</strong></p></div>";

			?>

			<?php
		}
		elseif ($_GET['page'] == 'link-library-stylesheet')
		{
			$formvalue = 'save_link_library_stylesheet';
			$pagetitle = "Link Library Stylesheet Editor";

			if ($_GET['message'] == '1')
				echo "<div id='message' class='updated fade'><p><strong>" . __('Stylesheet updated', 'link-library') . ".</strong></p></div>";
			elseif ($_GET['message'] == '2')
				echo "<div id='message' class='updated fade'><p><strong>" . __('Stylesheet reset to original state', 'link-library') . ".</strong></p></div>";			
		}
		elseif ($_GET['page'] == 'link-library-reciprocal')
		{
			$formvalue = 'save_link_library_reciprocal';
			$pagetitle = "Link Library Reciprocal Link Checker";
			
			if ($_GET['message'] == '1')
				echo "<div id='message' class='updated fade'><p><strong>" . __('Settings updated', 'link-library') . ".</strong></p></div>";
			elseif ($_GET['message'] == '2')
			{
				echo "<div id='message' class='updated fade'><p>";
				echo $this->ReciprocalLinkChecker($genoptions['recipcheckaddress']);
				echo "</p></div>";
			}
		}		

		$data = array();
		$data['settings'] = $settings;
		$data['options'] = $options;
		$data['genoptions'] = $genoptions;
		global $pagehooktop, $pagehookmoderate, $pagehookstylesheet, $pagehooksettingssets, $pagehookreciprocal;
		?>
		<div id="link-library-general" class="wrap">
		<div class='icon32'><img src="<?php echo $llpluginpath . '/icons/folder-beige-internet-icon32.png'; ?>" /></div>
		<div ><h2><?php echo $pagetitle; ?><span style='padding-left: 50px'><a href="http://yannickcorner.nayanna.biz/wordpress-plugins/link-library/" target="linklibrary"><img src="<?php echo $llpluginpath; ?>/icons/btn_donate_LG.gif" /></a></span></h2></div>
		<div><form name='linklibrary' enctype="multipart/form-data" action="admin-post.php" method="post">
			<input type="hidden" name="MAX_FILE_SIZE" value="100000" />

			<?php wp_nonce_field('link-library'); ?>
			<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
			<input type="hidden" name="action" value="<?php echo $formvalue; ?>" />

			<style type="text/css">
				#sortable { list-style-type: none; margin: 0; padding: 0; white-space:nowrap; list-style-type:none;}
				#sortable li { list-style: none; margin: 0 6px 4px 6px; padding: 10px 15px 10px 15px; border: #CCCCCC solid 1px; color:#fff; display: inline; width:100px;height: 30px;cursor:move}
				#sortable li span { position: absolute; margin-left: -1.3em; }
			</style>

			<div id="poststuff" class="metabox-holder">
				<div id="post-body" class="has-sidebar">
					<div id="post-body-content" class="has-sidebar-content">
						<?php 
							if ($_GET['page'] == 'link-library')
								do_meta_boxes($pagehooktop, 'normal', $data); 
							elseif ($_GET['page'] == 'link-library-settingssets')
								do_meta_boxes($pagehooksettingssets, 'normal', $data); 
							elseif ($_GET['page'] == 'link-library-moderate')
								do_meta_boxes($pagehookmoderate, 'normal', $data); 
							elseif ($_GET['page'] == 'link-library-stylesheet')
								do_meta_boxes($pagehookstylesheet, 'normal', $data);
							elseif ($_GET['page'] == 'link-library-reciprocal')
								do_meta_boxes($pagehookreciprocal, 'normal', $data); 
						?>
					</div>
				</div>
				<br class="clear"/>
			</div>
		</form>
		</div></div>
	<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function($) {
			// close postboxes that should be closed
			$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
			// postboxes setup
			postboxes.add_postbox_toggles('<?php 
				if ($_GET['page'] == 'link-library')
					echo $pagehooktop;
				elseif ($_GET['page'] == 'link-library-settingssets')
					echo $pagehooksettingssets;
				elseif ($_GET['page'] == 'link-library-moderate')
					echo $pagehookmoderate;
				elseif ($_GET['page'] == 'link-library-stylesheet')
					echo $pagehookstylesheet;
				elseif ($_GET['page'] == 'link-library-reciprocal')
					echo $pagehookreciprocal;
				?>');
		});
		//]]>

		// Create the tooltips only on document load
		jQuery(document).ready(function()
			{
				jQuery('.lltooltip').each(function()
						{
						jQuery(this).tipTip();
						}
				);

				jQuery("#sortable").sortable({ opacity: 0.6, cursor: 'move', update: function() {
						var order = jQuery("#sortable").sortable('toArray');
						stringorder = order.join(',') 
						document.getElementById('dragndroporder').value = stringorder;
					}
				});

		});
		</script>

		<?php
	}

		//executed if the post arrives initiated by pressing the submit button of form
	function on_save_changes_general() {
		//user permission check
		if ( !current_user_can('manage_options') )
			wp_die( __('Not allowed', 'link-library') );			
		//cross check the given referer
		check_admin_referer('link-library');

		$genoptions = get_option('LinkLibraryGeneral');

		foreach (array('numberstylesets', 'includescriptcss', 'pagetitleprefix', 'pagetitlesuffix', 'schemaversion', 'thumbshotscid', 'approvalemailtitle',
						'moderatorname', 'moderatoremail', 'rejectedemailtitle', 'approvalemailbody', 'rejectedemailbody', 'moderationnotificationtitle',
						'linksubmissionthankyouurl', 'recipcheckaddress') as $option_name) {
			if (isset($_POST[$option_name])) {
				$genoptions[$option_name] = $_POST[$option_name];
			}
		}

		foreach (array('debugmode', 'emaillinksubmitter', 'usefirstpartsubmittername') as $option_name) {
			if (isset($_POST[$option_name])) {
				$genoptions[$option_name] = true;
			} else {
				$genoptions[$option_name] = false;
			}
		}

		update_option('LinkLibraryGeneral', $genoptions);

		//lets redirect the post request into get request (you may add additional params at the url, if you need to show save results
		wp_redirect($this->remove_querystring_var($_POST['_wp_http_referer'], 'message') . "&message=1");
	}

		//executed if the post arrives initiated by pressing the submit button of form
	function on_save_changes_settingssets() {
		//user permission check
		if ( !current_user_can('manage_options') )
			wp_die( __('Not allowed', 'link-library') );	
		//cross check the given referer
		check_admin_referer('link-library');

		$messages = array();
		$row = 0;
		$successfulimport = 0;

		if (isset($_POST['importlinks']))
		{
			global $wpdb;

			$handle = fopen($_FILES['linksfile']['tmp_name'], "r");

			if ($handle)
			{
				$skiprow = 1;
 
				while (($data = fgetcsv($handle, 5000, ",")) !== FALSE) {
					$row += 1;
					if ($skiprow == 1 && isset($_POST['firstrowheaders']) && $row >= 2)
						$skiprow = 0;
					elseif (!isset($_POST['firstrowheaders']))
						$skiprow = 0;

					if (!$skiprow)
					{
						if (count($data) == 14)
						{
							$existingcatquery = "SELECT t.term_id FROM " . $this->db_prefix() . "terms t, " . $this->db_prefix() . "term_taxonomy tt ";
							$existingcatquery .= "WHERE t.name = '" . $data[5] . "' AND t.term_id = tt.term_id AND tt.taxonomy = 'link_category'";
							$existingcat = $wpdb->get_var($existingcatquery);

							if (!$existingcat)
							{
								$newlinkcatdata = array("cat_name" => $data[5], "category_description" => "", "category_nicename" => $wpdb->escape($data[5]));
								$newlinkcat = wp_insert_category($newlinkcatdata);

								$newcatarray = array("term_id" => $newlinkcat);

								$newcattype = array("taxonomy" => 'link_category');

								$wpdb->update( $this->db_prefix().'term_taxonomy', $newcattype, $newcatarray);

								$newlinkcat = array($newlinkcat);
							}
							else
							{
								$newlinkcat = array($existingcat);
							}

							$newlink = array("link_name" => wp_specialchars(stripslashes($data[0])), 
											"link_url" => wp_specialchars(stripslashes($data[1])),
											"link_rss" => wp_specialchars(stripslashes($data[2])),
											"link_description" => wp_specialchars(stripslashes($data[3])),
											"link_notes" => wp_specialchars(stripslashes($data[4])),
											"link_category" => $newlinkcat,
											"link_visible" => $data[6],
											"link_image" => $data[11]);

							$newlinkid = wp_insert_link($newlink);

							if ($newlinkid != 0)
							{
								$extradatatable = $this->db_prefix() . "links_extrainfo";
								$nofollowvalue = ($data[13] == 'Y' ? true : false);
								$wpdb->update( $extradatatable, array( 'link_second_url' => $data[7], 'link_telephone' => $data[8], 'link_email' => $data[9], 'link_reciprocal' => $data[10], 'link_textfield' => $data[12], 'link_no_follow' => $nofollowvalue ), array( 'link_id' => $newlinkid ));

								$successfulimport += 1;
							}
						}
						else
						{
							$messages[] = '4';
						}
					}
				}
			}

			if (isset($_POST['firstrowheaders']))
				$row -= 1;
			
			$messages[] = '9';
		}
		elseif (isset($_POST['exportsettings']))
		{
			if (is_writable(ABSPATH.PLUGINDIR . '/link-library'))
			{
				$myFile = ABSPATH.PLUGINDIR . "/link-library/SettingSet" . $_POST['settingsetid'] . "Export.csv";
				$fh = fopen($myFile, 'w') or die("can't open file");

				$sourcesettingsname = 'LinkLibraryPP' . $_POST['settingsetid'];
				$sourceoptions = get_option($sourcesettingsname);

				$headerrow = array();

				foreach ($sourceoptions as $key => $option)
				{
					$headerrow[] = '"' . $key . '"';
				}

				$headerdata .= join(',', $headerrow)."\n";
				fwrite($fh, $headerdata);

				$datarow = array();

				foreach ($sourceoptions as $key => $option)
				{
					$datarow[] = '"' . $option . '"';
				}

				$data .= join(',', $datarow)."\n";
				fwrite($fh, $data);

				fclose($fh);

				$messages[] = '5';
			}
			else
				$messages[] = '6';
		}
		elseif (isset($_POST['importsettings']))
		{
			global $wpdb;

			if ($_FILES['settingsfile']['tmp_name'] != "")
			{
				$handle = fopen($_FILES['settingsfile']['tmp_name'], "r");

				$row = 1;
				$optionnames = "";
				$options = "";

				while (($data = fgetcsv($handle, 5000, ",")) !== FALSE) {
					if ($row == 1)
					{
						$optionnames = $data;
						$row++;
					}
					else if ($row == 2)
					{
						for ($counter = 0; $counter <= count($data) - 1; $counter++)
							$options[$optionnames[$counter]] = $data[$counter];
						$row++;
					}
				}

				if ($options != "")
				{
					$settingsname = 'LinkLibraryPP' . $_POST['settingsetid'];

					update_option($settingsname, $options);
					
					$messages[] = '7';
				}

				fclose($handle);
			}
			else 
			{
				$messages[] = '8';
			}
		}
		else
		{
			$settingsetid = $_POST['settingsetid'];
			$settings = $_POST['settingsetid'];

			$settingsname = 'LinkLibraryPP' . $settingsetid;
			
			$options = get_option($settingsname);
			
			foreach (array('order', 'table_width', 'num_columns', 'categorylist', 'excludecategorylist', 'position',
						   'beforecatlist1', 'beforecatlist2', 'beforecatlist3','catnameoutput', 'linkaddfrequency', 
						   'defaultsinglecat', 'rsspreviewcount', 'rssfeedinlinecount', 'linksperpage', 'catdescpos',
						   'catlistdescpos', 'rsspreviewwidth', 'rsspreviewheight', 'numberofrssitems',
						   'displayweblink', 'sourceweblink', 'showtelephone', 'sourcetelephone', 'showemail', 'sourceimage', 'sourcename') 
						   as $option_name) {
				if (isset($_POST[$option_name])) {
					$options[$option_name] = str_replace("\"", "'", strtolower($_POST[$option_name]));
				}
			}

			foreach (array('linkheader', 'descheader', 'notesheader','linktarget', 'settingssetname', 'loadingicon','rsscachedir',
							'direction', 'linkdirection', 'linkorder', 'addnewlinkmsg', 'linknamelabel', 'linkaddrlabel', 'linkrsslabel',
							'linkcatlabel', 'linkdesclabel', 'linknoteslabel', 'addlinkbtnlabel', 'newlinkmsg', 'moderatemsg', 'imagepos',
							'imageclass', 'rssfeedtitle', 'rssfeeddescription', 'showonecatmode', 'linkcustomcatlabel', 'linkcustomcatlistentry',
							'searchlabel', 'dragndroporder', 'cattargetaddress', 'beforeweblink', 'afterweblink', 'weblinklabel', 'beforetelephone',
							'aftertelephone', 'telephonelabel', 'beforeemail', 'afteremail', 'emaillabel', 'beforelinkhits', 'afterlinkhits',
							'linkreciprocallabel', 'linksecondurllabel', 'linktelephonelabel', 'linkemaillabel', 'emailcommand', 'rewritepage',
							'maxlinks', 'beforedate', 'afterdate', 'beforeimage', 'afterimage', 'beforerss','afterrss', 'beforenote', 'afternote',
							'beforelink','afterlink', 'beforeitem', 'afteritem', 'beforedesc', 'afterdesc', 'addbeforelink', 'addafterlink',
							'beforelinkrating', 'afterlinkrating', 'linksubmitternamelabel', 'linksubmitteremaillabel', 'linksubmittercommentlabel',
							'addlinkcatlistoverride', 'beforelargedescription', 'afterlargedescription', 'customcaptchaquestion', 'customcaptchaanswer',
							'rssfeedaddress', 'linklargedesclabel') as $option_name) {
				if (isset($_POST[$option_name])) {
					$options[$option_name] = str_replace("\"", "'", $_POST[$option_name]);
				}
			}

			foreach (array('hide_if_empty', 'catanchor', 'showdescription', 'shownotes', 'showrating', 'showupdated', 'show_images', 
							'use_html_tags', 'show_rss', 'nofollow','showcolumnheaders','show_rss_icon', 'showcategorydescheaders',
							'showcategorydesclinks', 'showadmineditlinks', 'showonecatonly', 'rsspreview', 'rssfeedinline', 'rssfeedinlinecontent',
							'pagination', 'hidecategorynames', 'showinvisible', 'showdate', 'showuserlinks', 'emailnewlink', 'usethumbshotsforimages',
							'addlinkreqlogin', 'showcatlinkcount', 'publishrssfeed', 'showname', 'enablerewrite', 'storelinksubmitter', 'showlinkhits', 'showcaptcha',
							'showlargedescription', 'addlinknoaddress', 'featuredfirst')
							as $option_name) {
				if (isset($_POST[$option_name])) {
					$options[$option_name] = true;
				} else {
					$options[$option_name] = false;
				}
			}

			foreach(array('flatlist', 'displayastable', 'divorheader','showaddlinkrss', 'showaddlinkdesc', 'showaddlinkcat', 'showaddlinknotes','addlinkcustomcat',
						  'showaddlinkreciprocal', 'showaddlinksecondurl', 'showaddlinktelephone', 'showaddlinkemail', 'showcustomcaptcha', 'showlinksubmittername',
						  'showaddlinksubmitteremail', 'showlinksubmittercomment', 'showuserlargedescription') as $option_name) {
				if ($_POST[$option_name] == 'true')
					$options[$option_name] = true;
				elseif ($_POST[$option_name] == 'false')
					$options[$option_name] = false;
			}

			foreach (array('catlistwrappers') as $option_name)
			{
				if (isset($_POST[$option_name])) {
					$options[$option_name] = (int)($_POST[$option_name]);
				}
			}

			update_option($settingsname, $options);
			$messages[] = "1";

			global $wpdb;

			if ($options['categorylist'] != '')
			{
				$categoryids = explode(',', $options['categorylist']);

				foreach($categoryids as $categoryid)
				{
					$linkcatquery = "SELECT distinct t.name, t.term_id, t.slug as category_nicename, tt.description as category_description ";
					$linkcatquery .= "FROM " . $this->db_prefix() . "terms t, " . $this->db_prefix(). "term_taxonomy tt ";

					if ($hide_if_empty)
						$linkcatquery .= ", " . $this->db_prefix() . "term_relationships tr, " . $this->db_prefix() . "links l ";

					$linkcatquery .= "WHERE t.term_id = tt.term_id AND tt.taxonomy = 'link_category'";

					$linkcatquery .= " AND t.term_id = " . $categoryid;

					$catnames = $wpdb->get_results($linkcatquery);

					if (!$catnames)
					{
						$messages[] = '2';
					}
				}
			}
			
			if ($options['excludecategorylist'] != '')
			{
				$categoryids = explode(',', $options['excludecategorylist']);

				foreach($categoryids as $categoryid)
				{
					$linkcatquery = "SELECT distinct t.name, t.term_id, t.slug as category_nicename, tt.description as category_description ";
					$linkcatquery .= "FROM " . $this->db_prefix() . "terms t, " . $this->db_prefix(). "term_taxonomy tt ";

					if ($hide_if_empty)
						$linkcatquery .= ", " . $this->db_prefix() . "term_relationships tr, " . $this->db_prefix() . "links l ";

					$linkcatquery .= "WHERE t.term_id = tt.term_id AND tt.taxonomy = 'link_category'";

					$linkcatquery .= " AND t.term_id = " . $categoryid;

					$catnames = $wpdb->get_results($linkcatquery);

					if (!$catnames)
					{
						$messages[] = '3';
					}
				}
			}
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
		}

		//lets redirect the post request into get request (you may add additional params at the url, if you need to show save results
		$messagelist = implode(",", $messages);
		$cleanredirecturl = $this->remove_querystring_var($_POST['_wp_http_referer'], 'messages');
		$cleanredirecturl = $this->remove_querystring_var($cleanredirecturl, 'importrowscount');
		$cleanredirecturl = $this->remove_querystring_var($cleanredirecturl, 'successimportcount');
		$cleanredirecturl = $this->remove_querystring_var($cleanredirecturl, 'copy');
		$cleanredirecturl = $this->remove_querystring_var($cleanredirecturl, 'source');
		$redirecturl = $cleanredirecturl;

		if (!empty($messages))
			$redirecturl = $cleanredirecturl . "&messages=" . $messagelist;

		if ($row != 0)
			$redirecturl .= "&importrowscount=" . $row;

		if ($successfulimport != 0)
			$redirecturl .= "&successimportcount=" . $successfulimport;

		wp_redirect( $redirecturl );
	}

			//executed if the post arrives initiated by pressing the submit button of form
	function on_save_changes_moderate() {
		//user permission check
		if ( !current_user_can('manage_options') )
			wp_die( __('Not allowed', 'link-library') );	
		//cross check the given referer
		check_admin_referer('link-library');

		$message = '';
		
		$genoptions = get_option('LinkLibraryGeneral');

		if (isset($_POST['approvelinks']))
		{
			global $wpdb;

			$section = 'moderate';

			foreach ($_POST['links'] as $approved_link)
			{
				$linkdescquery = "SELECT link_description, link_name ";
				$linkdescquery .= "FROM " . $this->db_prefix() . "links l ";
				$linkdescquery .= "WHERE link_id = " . $approved_link;

				$linkdata = $wpdb->get_row($linkdescquery, ARRAY_A); 

				$modpos = strpos($linkdata['link_description'], "LinkLibrary:AwaitingModeration:RemoveTextToApprove");

				if ($modpos)
				{
					$startpos = $modpos + 51;
					$newlinkdesc = substr($linkdata['link_description'], $startpos);

					$id = array("id" => $linkdescquery);
					$newdesc = array ("link_description", $newlinkdesc);

					$tablename = $this->db_prefix() . "links";
					$wpdb->update( $tablename, array( 'link_description' => $newlinkdesc, 'link_visible' => 'Y' ), array( 'link_id' => $approved_link ));
				}
				
				$linkextradata = $wpdb->get_row("select * from " . $this->db_prefix() . "links_extrainfo where link_id = " . $approved_link, ARRAY_A);
				
				if ($genoptions['emaillinksubmitter'] == true && $linkextradata['link_submitter_email'] != '')
				{
					if ($genoptions['usefirstpartsubmittername'] == true)
					{
						$spacepos = strpos($linkextradata['link_submitter_name'], " ");
						if ($spacepos !== false)
							$linkextradata['link_submitter_name'] = substr($linkextradata['link_submitter_name'], 0, $spacepos);
					}
					
					$emailtitle = str_replace('%linkname%', $linkdata['link_name'], $genoptions['approvalemailtitle']);
					$emailbody = nl2br($genoptions['approvalemailbody']);
					$emailbody = str_replace('%submittername%', stripslashes($linkextradata['link_submitter_name']), stripslashes($emailbody));
					$emailbody = str_replace('%linkname%', $linkdata['link_name'], $emailbody);
					
					$headers = "MIME-Version: 1.0\r\n";
					$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
					
					if ($genoptions['moderatorname'] != '' && $genoptions['moderatoremail'] != '')
						$headers .= "From: \"" . $genoptions['moderatorname'] . "\" <" . $genoptions['moderatoremail'] . ">\n";
					
					$message = $emailbody;
						
					$message .= "<br /><br />" . __('Message generated by', 'link-library') . " <a href='http://yannickcorner.nayanna.biz/wordpress-plugins/link-library/'>Link Library</a> for Wordpress";
					
					wp_mail($linkextradata['link_submitter_email'], $emailtitle, $message, $headers);
				}
			}

			$message = '1';
		}
		elseif (isset($_POST['deletelinks']))
		{
			global $wpdb;

			$section = 'moderate';

			foreach ($_POST['links'] as $approved_link)
			{
				$linkdescquery = "SELECT link_description, link_name ";
				$linkdescquery .= "FROM " . $this->db_prefix() . "links l ";
				$linkdescquery .= "WHERE link_id = " . $approved_link;

				$linkdata = $wpdb->get_row($linkdescquery, ARRAY_A);
				
				$linkextradata = $wpdb->get_row("select * from " . $this->db_prefix() . "links_extrainfo where link_id = " . $approved_link, ARRAY_A);
				
				if ($genoptions['emaillinksubmitter'] == true && $linkextradata['link_submitter_email'] != '')
				{					
					$emailtitle = str_replace('%linkname%', $linkdata['link_name'], $genoptions['rejectedemailtitle']);
					$emailbody = nl2br($genoptions['rejectedemailbody']);
					$emailbody = str_replace('%submittername%', stripslashes($linkextradata['link_submitter_name']), stripslashes($emailbody));
					$emailbody = str_replace('%linkname%', $linkdata['link_name'], $emailbody);
					
					$headers = "MIME-Version: 1.0\r\n";
					$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
					
					if ($genoptions['moderatorname'] != '' && $genoptions['moderatoremail'] != '')
						$headers .= "From: \"" . $genoptions['moderatorname'] . "\" <" . $genoptions['moderatoremail'] . ">\n";
					
					$message = $emailbody;
						
					$message .= "<br /><br />" . __('Message generated by', 'link-library') . " <a href='http://yannickcorner.nayanna.biz/wordpress-plugins/link-library/'>Link Library</a> for Wordpress";
					
					wp_mail($linkextradata['link_submitter_email'], $emailtitle, $message, $headers);
				}
			
				$wpdb->query("DELETE FROM " . $this->db_prefix() . "links WHERE link_id = " . $approved_link);
			}

			$message = '2';
		}

		$cleanredirecturl = $this->remove_querystring_var($_POST['_wp_http_referer'], 'message');

		if ($message != '')
			$redirecturl = $cleanredirecturl . "&message=" . $message;
		else
			$redirecturl = $cleanredirecturl;

		//lets redirect the post request into get request (you may add additional params at the url, if you need to show save results
		wp_redirect($redirecturl);
	}

	function on_save_changes_stylesheet() {
		//user permission check
		if ( !current_user_can('manage_options') )
			wp_die( __('Not allowed', 'link-library') );	
		//cross check the given referer
		check_admin_referer('link-library');

		if (isset($_POST['submitstyle']))
		{
			$genoptions = get_option('LinkLibraryGeneral');

			$genoptions['fullstylesheet'] = $_POST['fullstylesheet'];

			update_option('LinkLibraryGeneral', $genoptions);
			$message = 1;
		}
		elseif (isset($_POST['resetstyle']))
		{
			$genoptions = get_option('LinkLibraryGeneral');

			$stylesheetlocation = LLDIR . '/stylesheettemplate.css';
			if (file_exists($stylesheetlocation))
				$genoptions['fullstylesheet'] = file_get_contents($stylesheetlocation);

			update_option('LinkLibraryGeneral', $genoptions);

			$message = 2;
		}

		//lets redirect the post request into get request (you may add additional params at the url, if you need to show save results
		wp_redirect($this->remove_querystring_var($_POST['_wp_http_referer'], 'message') . "&message=" . $message);
	}
	
	function on_save_changes_reciprocal() {
		//user permission check
		if ( !current_user_can('manage_options') )
			wp_die( __('Not allowed', 'link-library') );	
		//cross check the given referer
		check_admin_referer('link-library');
		
		$message = -1;
		
		$genoptions = get_option('LinkLibraryGeneral');

		$genoptions['recipcheckaddress'] = $_POST['recipcheckaddress'];

		update_option('LinkLibraryGeneral', $genoptions);
		
		if (!isset($_POST['recipcheck']))
		{
			$message = 1;
		}
		elseif (isset($_POST['recipcheck']))
		{
			$message = 2;
		}
		
		if ($message != -1)
			$messageend = "&message=" . $message;
		else
			$messageend = '';

		//lets redirect the post request into get request (you may add additional params at the url, if you need to show save results
		wp_redirect($this->remove_querystring_var($_POST['_wp_http_referer'], 'message') . $messageend);
	}

	function general_meta_box($data) {
		$genoptions = $data['genoptions'];
		?>
			<table>
			<tr>
			<td>
				<input type='hidden' value='<?php echo $genoptions['schemaversion']; ?>' name='schemaversion' id='schemaversion' />
				<table>
				<tr>
				<td class='lltooltip' title='<?php _e('The stylesheet is now defined and stored using the Link Library admin interface. This avoids problems with updates from one version to the next.', 'link-library'); ?>' style='width:200px'><?php _e('Stylesheet','link-library'); ?></td>
				<td class='lltooltip' title='<?php _e('The stylesheet is now defined and stored using the Link Library admin interface. This avoids problems with updates from one version to the next.', 'link-library'); ?>'><a href="<?php echo WP_ADMIN_URL ?>/admin.php?page=link-library-stylesheet&section=stylesheet"><?php _e('Editor', 'link-library'); ?></a></td>
				</tr>
				<tr>
				<td><?php _e('Number of Libraries','link-library'); ?></td>
				<td><input type="text" id="numberstylesets" name="numberstylesets" size="5" value="<?php if ($genoptions['numberstylesets'] == '') echo '1'; echo $genoptions['numberstylesets']; ?>"/></td>
				</tr>
				<tr>
				<td class="lltooltip" title="<?php _e('Enter comma-separate list of pages on which the Link Library stylesheet and scripts should be loaded. Primarily used if you display Link Library using the API','link-library'); ?>"><?php _e('Additional pages to load styles and scripts','link-library'); ?></td>
				<td class="lltooltip" title="<?php _e('Enter comma-separate list of pages on which the Link Library stylesheet and scripts should be loaded. Primarily used if you display Link Library using the API','link-library'); ?>"><input type="text" id="includescriptcss" name="includescriptcss" size="40" value="<?php echo $genoptions['includescriptcss']; ?>"/></td>
				</tr>
				<tr>
				<td><?php _e('Debug Mode', 'link-library'); ?></td>
				<td><input type="checkbox" id="debugmode" name="debugmode" <?php if ($genoptions['debugmode']) echo ' checked="checked" '; ?>/></td>
				</tr>
				<tr>
				<td class="lltooltip" title="<?php _e('This function is only possible when showing one category at a time and while the default category is not shown.', 'link-library'); ?>"><?php _e('Page Title Prefix','link-library'); ?></td>
				<td class="lltooltip" title="<?php _e('This function is only possible when showing one category at a time and while the default category is not shown.', 'link-library'); ?>"><input type="text" id="pagetitleprefix" name="pagetitleprefix" size="10" value="<?php echo $genoptions['pagetitleprefix']; ?>"/></td>
				</tr>
				<tr>
				<td class="lltooltip" title="<?php _e('This function is only possible when showing one category at a time and while the default category is not shown.', 'link-library'); ?>"><?php _e('Page Title Suffix','link-library'); ?></td>
				<td class="lltooltip" title="<?php _e('This function is only possible when showing one category at a time and while the default category is not shown.', 'link-library'); ?>"><input type="text" id="pagetitlesuffix" name="pagetitlesuffix" size="10" value="<?php echo $genoptions['pagetitlesuffix']; ?>"/></td>
				</tr>
				<tr>
					<td class='lltooltip' title='<?php _e('CID provided with paid Thumbshots.org accounts', 'link-library'); ?>'><?php _e('Thumbshots CID', 'link-library'); ?></td>
					<td colspan='4' class='lltooltip' title='<?php _e('CID provided with paid Thumbshots.org accounts', 'link-library'); ?>'><input type="text" id="thumbshotscid" name="thumbshotscid" size="20" value="<?php echo $genoptions['thumbshotscid']; ?>"/></td>
				</tr>
				
				</table>
			</td>
			<td style='padding: 8px; border: 1px solid #cccccc;'>
			<div><h3>ThemeFuse Original WP Themes</h3><br />If you are looking to buy an original WP theme, take a look at <a href="https://www.e-junkie.com/ecom/gb.php?cl=136641&c=ib&aff=153522" target="ejejcsingle">ThemeFuse</a><br />They have a nice 1-click installer, great support and good-looking themes.</div><div style='text-align: center; padding-top: 10px'><a href="https://www.e-junkie.com/ecom/gb.php?cl=136641&c=ib&aff=153522" target="ejejcsingle"><img src='http://themefuse.com/wp-content/themes/themefuse/images/campaigns/themefuse.jpg' /></a></div>
			</td>
			</table>
		<?php }
		
	function general_moderation_meta_box($data) {
		$genoptions = $data['genoptions'];
	?>
		<table>
			<tr>
				<td class='lltooltip' title='<?php _e('URL that user will be redirected to after submitting new link. When used, the short code [link-library-addlinkcustommsg] should be placed on the destination page.', 'link-library'); ?>.' style='width:250px'><?php _e('Link Acknowledgement URL', 'link-library'); ?></td>
				<td style='width:75px;padding-right:20px'><input type="text" id="linksubmissionthankyouurl" name="linksubmissionthankyouurl" size="60" value='<?php echo $genoptions['linksubmissionthankyouurl']; ?>' /></td>
			</tr>
			<tr>
				<td class='lltooltip' title='<?php _e('Title of e-mail sent to site admin when new links are submitted. Use %linkname% as a variable to be replaced by the actual link name', 'link-library'); ?>.' style='width:250px'><?php _e('Moderation Notification Title', 'link-library'); ?></td>
				<td style='width:75px;padding-right:20px'><input type="text" id="moderationnotificationtitle" name="moderationnotificationtitle" size="60" value='<?php echo $genoptions['moderationnotificationtitle']; ?>' /></td>
			</tr>		
			<tr>
				<td class='lltooltip' title='<?php _e('Will send a confirmation e-mail to link submitter if they provided their contact information', 'link-library'); ?>.' style='width:250px'><?php _e('E-mail submitter on link approval or rejection', 'link-library'); ?></td>
				<td style='width:75px;padding-right:20px'><input type="checkbox" id="emaillinksubmitter" name="emaillinksubmitter" <?php if ($genoptions['emaillinksubmitter']) echo ' checked="checked" '; ?>/></td>
			</tr>
			<tr>
				<td style='width:250px'><?php _e('Only use first part of submitter name', 'link-library'); ?></td>
				<td style='width:75px;padding-right:20px'><input type="checkbox" id="usefirstpartsubmittername" name="usefirstpartsubmittername" <?php if ($genoptions['usefirstpartsubmittername']) echo ' checked="checked" '; ?>/></td>
			</tr>			
			<tr>
				<td class='lltooltip' title='<?php _e('The name of the e-mail account that the approval e-mail will be sent from', 'link-library'); ?>'><?php _e('Moderator Name', 'link-library'); ?></td>
				<td><input type="text" id="moderatorname" name="moderatorname" size="60" value="<?php echo $genoptions['moderatorname']; ?>"/></td>
			</tr>
			<tr>
				<td class='lltooltip' title='<?php _e('The e-mail address that the approval e-mail will be sent from', 'link-library'); ?>'><?php _e('Moderator E-mail', 'link-library'); ?></td>
				<td><input type="text" id="moderatoremail" name="moderatoremail" size="60" value="<?php echo $genoptions['moderatoremail']; ?>"/></td>
			</tr>
			<tr>
				<td class='lltooltip' title='<?php _e('Title of approval e-mail. Use %linkname% as a variable to be replaced by the actual link name', 'link-library'); ?>'><?php _e('Approval e-mail title', 'link-library'); ?></td>
				<td><input type="text" id="approvalemailtitle" name="approvalemailtitle" size="60" value="<?php echo $genoptions['approvalemailtitle']; ?>"/></td>
			</tr>
			<tr>
				<td class='lltooltip' title='<?php _e('Body of approval e-mail. Use %linkname% as a variable to be replaced by the actual link name and %submittername% for the submitter name', 'link-library'); ?>'><?php _e('Approval e-mail body', 'link-library'); ?></td>
				<td><textarea id="approvalemailbody" name="approvalemailbody" cols="60"><?php echo stripslashes($genoptions['approvalemailbody']); ?></textarea></td>
			</tr>
			<tr>
				<td class='lltooltip' title='<?php _e('Title of rejection e-mail. Use %linkname% as a variable to be replaced by the actual link name', 'link-library'); ?>'><?php _e('Rejection e-mail title', 'link-library'); ?></td>
				<td><input type="text" id="rejectedemailtitle" name="rejectedemailtitle" size="60" value="<?php echo $genoptions['rejectedemailtitle']; ?>"/></td>
			</tr>
			<tr>
				<td class='lltooltip' title='<?php _e('Body of rejection e-mail. Use %linkname% as a variable to be replaced by the actual link name and %submittername% for the submitter name', 'link-library'); ?>'><?php _e('Rejection e-mail body', 'link-library'); ?></td>
				<td><textarea id="rejectedemailbody" name="rejectedemailbody" cols="60"><?php echo stripslashes($genoptions['rejectedemailbody']); ?></textarea></td>
			</tr>			
		</table>
	<?php }
	
	function general_save_meta_box() {
	?>
		<div class="submitbox">
		<input type="submit" name="submit" class="button-primary" value="<?php _e('Save','link-library'); ?>" />
		</div>
	<?php
	}

	function settingssets_save_meta_box() {
	?>

		<div class="submitbox">
		<input type="submit" name="submit" class="button-primary" value="<?php _e('Update Settings','link-library'); ?>" />
		</div>
	<?php
	}

	function moderate_meta_box() {
	?>
			<table class='widefat' style='clear:none;width:100%;background: #DFDFDF url(/wp-admin/images/gray-grad.png) repeat-x scroll left top;'>
				<tr>
					<th style='width: 30px'></th>
					<th style='width: 200px'><?php _e('Link Name', 'link-library'); ?></th>
					<th style='width: 300px'><?php _e('Link URL', 'link-library'); ?></th>
					<th><?php _e('Link Description', 'link-library'); ?></th>
				</tr>
			<?php global $wpdb;

				$linkquery = "SELECT distinct * ";
				$linkquery .= "FROM " . $this->db_prefix() . "links l ";
				$linkquery .= "WHERE l.link_description like '%LinkLibrary:AwaitingModeration:RemoveTextToApprove%' ";
				$linkquery .= " ORDER by link_name ASC";

				$linkitems = $wpdb->get_results($linkquery);

				if ($linkitems) {
					foreach($linkitems as $linkitem) {

					$modpos = strpos($linkitem->link_description, "LinkLibrary:AwaitingModeration:RemoveTextToApprove");

					if ($modpos)
					{
						$startpos = $modpos + 51;
						$newlinkdesc = substr($linkitem->link_description, $startpos);
					}
			?>
					<tr style='background: #FFF'>
						<td><input type="checkbox" name="links[]" value="<?php echo $linkitem->link_id; ?>" /></td>
						<td><?php echo "<a title='Edit Link: " . $linkitem->link_name . "' href='" . WP_ADMIN_URL . "/link.php?action=edit&link_id=" . $linkitem->link_id. "'>" . $linkitem->link_name . "</a>"; ?></td>
						<td><?php echo "<a href='" . $linkitem->link_url . "'>" . $linkitem->link_url . "</a>"; ?></td>
						<td><?php echo $newlinkdesc; ?></td>
					</tr>
			<?php	}
				}
				else { ?>
					<tr>
						<td></td>
						<td><?php _e('No Links Found to Moderate', 'link-library'); ?></td>
						<td></td>
						<td></td>
					</tr>
			<?php } ?>

			</table><br />
			<input type="button" id="CheckAll" value="<?php _e('Check All','link-library'); ?>">
			<input type="button" id="UnCheckAll" value="<?php _e('Uncheck All', 'link-library'); ?>">

			<input type="submit" name="approvelinks" value="<?php _e('Approve Selected Items','link-library'); ?>" />
			<input type="submit" name="deletelinks" value="<?php _e('Delete Selected Items', 'link-library'); ?>" />
			
			<script type="text/javascript">
				jQuery(document).ready(function()
				{
					jQuery('#CheckAll').click(function() {
						jQuery("INPUT[type='checkbox']").attr('checked', true);
					});
					
					jQuery('#UnCheckAll').click(function() {
						jQuery("INPUT[type='checkbox']").attr('checked', false);
					});
				});
			</script>
			
		</div>

	<?php
	}

	function stylesheet_meta_box($data) {
		$genoptions = $data['genoptions'];
	?>

		<?php _e('If the stylesheet editor is empty after upgrading, reset to the default stylesheet using the button below or copy/paste your backup stylesheet into the editor.', 'link-library'); ?><br /><br />

		<textarea name='fullstylesheet' id='fullstylesheet' style='font-family:Courier' rows="30" cols="100">
<?php echo stripslashes($genoptions['fullstylesheet']);?>
</textarea>
		<div><input type="submit" name="submitstyle" value="<?php _e('Submit','link-library'); ?>" /><span style='padding-left: 650px'><input type="submit" name="resetstyle" value="<?php _e('Reset to default','link-library'); ?>" /></span></div>
	<?php
	}

	function settingssets_usage_meta_box($data) {
		$options = $data['options'];
		$settings = $data['settings'];
		$genoptions = $data['genoptions'];
	?>
			<div>
						<?php _e('Select Current Library Settings', 'link-library'); ?> : 
						<SELECT id="settingsetlist" name="settingsetlist" style='width: 300px'>
						<?php if ($genoptions['numberstylesets'] == '') $numberofsets = 1; else $numberofsets = $genoptions['numberstylesets'];
							for ($counter = 1; $counter <= $numberofsets; $counter++): ?>
								<?php $tempoptionname = "LinkLibraryPP" . $counter;
								   $tempoptions = get_option($tempoptionname); ?>
								   <option value="<?php echo $counter ?>" <?php if ($settings == $counter) echo 'SELECTED';?>><?php _e('Library', 'link-library'); ?> <?php echo $counter ?><?php if ($tempoptions != "") echo " (" . $tempoptions['settingssetname'] . ")"; ?></option>
							<?php endfor; ?>
						</SELECT>
						<INPUT type="button" name="go" value="<?php _e('Go', 'link-library'); ?>!" onClick="window.location= 'admin.php?page=link-library-settingssets&amp;settings=' + jQuery('#settingsetlist').val()">
						<?php if ($numberofsets > 1): ?>
						<?php _e('Copy from:', 'link-library'); ?> 
						<SELECT id="copysource" name="copysource" style='width: 300px'>
						<?php for ($counter = 1; $counter <= $numberofsets; $counter++): ?>
								<?php $tempoptionname = "LinkLibraryPP" . $counter;
								   $tempoptions = get_option($tempoptionname); 
								   if ($counter != $settings):?>
								   <option value="<?php echo $counter ?>" <?php if ($settings == $counter) echo 'SELECTED';?>><?php _e('Library', 'link-library'); ?> <?php echo $counter ?><?php if ($tempoptions != "") echo " (" . $tempoptions['settingssetname'] . ")"; ?></option>
								   <?php endif; 
								endfor;
							?>
						</SELECT>
						<INPUT type="button" name="copy" value="<?php _e('Copy', 'link-library'); ?>!" onClick="window.location= 'admin.php?page=link-library-settingssets&amp;settings=<?php echo $settings; ?>&amp;copy=<?php echo $settings; ?>&source=' + jQuery('#copysource').val()">
						<?php endif; ?>
				<br />
				<br />
				<table class='widefat' style='clear:none;width:100%;background: #DFDFDF url(/wp-admin/images/gray-grad.png) repeat-x scroll left top;'>
					<thead>
					<tr>
						<th style='width:40px' class="lltooltip" title='<?php _e('Link Library Supports the Creation of an unlimited number of configurations to display link lists on your site', 'link-library'); ?>'>
							<?php _e('Library #', 'link-library'); ?>
						</th>
						<th style='width:130px' class="lltooltip" title='<?php _e('Link Library Supports the Creation of an unlimited number of configurations to display link lists on your site', 'link-library'); ?>'>
							<?php _e('Library Name', 'link-library'); ?>
						</th>
						<th style='width: 230px'><?php _e('Feature', 'link-library'); ?></th>
						<th class="lltooltip" title='<?php _e('Link Library Supports the Creation of an unlimited number of configurations to display link lists on your site', 'link-library'); ?>'>
							<?php _e('Code to insert on a Wordpress page', 'link-library'); ?>
						</th>
					</tr>
					</thead>
					<tr>
						<td style='background: #FFF'><?php echo $settings; ?></td><td style='background: #FFF'><?php echo $options['settingssetname']; ?></a></td><td style='background: #FFF'><?php _e('Display basic link library', 'link-library'); ?></td><td style='background: #FFF'><?php echo "[link-library settings=" . $settings . "]"; ?></td>
					</tr>
					<tr>
						<td style='background: #FFF'></td><td style='background: #FFF'></td><td style='background: #FFF'><?php _e('Display list of link categories', 'link-library'); ?></td><td style='background: #FFF'><?php echo "[link-library-cats settings=" . $settings . "]"; ?></td>
					</tr>
					<tr>
						<td style='background: #FFF'></td><td style='background: #FFF'></td><td style='background: #FFF'><?php _e('Display search box', 'link-library'); ?></td><td style='background: #FFF'><?php echo "[link-library-search settings=" . $settings . "]"; ?></td>						
					</tr>
					<tr>
						<td style='background: #FFF'></td><td style='background: #FFF'></td><td style='background: #FFF'><?php _e('Display link submission form', 'link-library'); ?></td><td style='background: #FFF'><?php echo "[link-library-addlink settings=" . $settings . "]"; ?></td>						
					</tr>
				</table> 
				<br />
			</div>

			<div>
				<table>
				<tr>
					<td style='text-align:right'>
						<span><a href='admin.php?page=link-library-settingssets&amp;deletesettings=<?php echo $settings ?>' <?php echo "onclick=\"if ( confirm('" . esc_js(sprintf( __("You are about to Delete Library #'%s'\n  'Cancel' to stop, 'OK' to delete.", "link-library"), $settings )) . "') ) { return true;}return false;\""; ?>><?php _e('Delete Library', 'link-library'); ?> <?php echo $settings ?></a></span>
						<span><a href='admin.php?page=link-library-settingssets&amp;settings=<?php echo $settings ?>&reset=<?php echo $settings; ?>' <?php echo "onclick=\"if ( confirm('" . esc_js(sprintf( __("You are about to reset Library '%s'\n  'Cancel' to stop, 'OK' to reset.", "link-library"), $settings )) . "') ) { return true;}return false;\""; ?>><?php _e('Reset current Library', 'link-library'); ?></a></span>
						<span><a href='admin.php?page=link-library-settingssets&amp;settings=<?php echo $settings ?>&resettable=<?php echo $settings; ?>' <?php echo "onclick=\"if ( confirm('" . esc_js(sprintf( __("You are about to reset Library '%s' for a table layout\n  'Cancel' to stop, 'OK' to reset.", "link-library"), $settings )) . "') ) { return true;}return false;\""; ?>><?php _e('Reset current Library for table layout', 'link-library'); ?></a></span>
					</td>
				</tr>
				</table>
			</div>
	<?php }
		
	function settingssets_common_meta_box($data) { 
		$options = $data['options'];
		$settings = $data['settings'];
	?>

		<div style='padding-top: 15px'>
		<input type='hidden' value='<?php echo $settings; ?>' name='settingsetid' id='settingsetid' />
		<table>

		<tr>
			<td style='width: 300px;padding-right: 50px'>
				<?php _e('Current Library Name', 'link-library'); ?>
			</td>
			<td>
				<input type="text" id="settingssetname" name="settingssetname" size="40" value="<?php echo $options['settingssetname']; ?>"/>
			</td>
		</tr>
		<tr>
			<td class="lltooltip" title="<?php _e('Leave Empty to see all categories', 'link-library'); ?><br /><br /><?php _e('Enter list of comma-separated', 'link-library'); ?><br /><?php _e('numeric category IDs', 'link-library'); ?><br /><br /><?php _e('To find the IDs, go to the Link Categories admin page, place the mouse above a category name and look for its ID in the address shown in your browsers status bar. For example', 'link-library'); ?>: 2,4,56">
				<?php _e('Categories to be displayed (Empty=All)', 'link-library'); ?>
			</td>
			<td class="lltooltip" title="<?php _e('Leave Empty to see all categories', 'link-library'); ?><br /><br /><?php _e('Enter list of comma-separated', 'link-library'); ?><br /><?php _e('numeric category IDs', 'link-library'); ?><br /><br /><?php _e('For example', 'link-library'); ?>: 2,4,56">
				<input type="text" id="categorylist" name="categorylist" size="40" value="<?php echo $options['categorylist']; ?>"/>
			</td>
		</tr>
		<tr>
			<td class="lltooltip" title="<?php _e('Enter list of comma-separated', 'link-library'); ?><br /><?php _e('numeric category IDs that should not be shown', 'link-library'); ?><br /><br /><?php _e('For example', 'link-library'); ?>: 5,34,43">
				<?php _e('Categories to be excluded', 'link-library'); ?>
			</td>
			<td class="lltooltip" title="<?php _e('Enter list of comma-separated', 'link-library'); ?><br /><?php _e('numeric category IDs that should not be shown', 'link-library'); ?><br /><br /><?php _e('For example', 'link-library'); ?>: 5,34,43">
				<input type="text" id="excludecategorylist" name="excludecategorylist" size="40" value="<?php echo $options['excludecategorylist']; ?>"/>
			</td>
		</tr>
		<tr>
			<td class="lltooltip" title="<?php _e('Only show one category of links at a time', 'link-library'); ?>">
				<?php _e('Only show one category at a time', 'link-library'); ?>
			</td>
			<td class="lltooltip" title="<?php _e('Only show one category of links at a time', 'link-library'); ?>">
				<input type="checkbox" id="showonecatonly" name="showonecatonly" <?php if ($options['showonecatonly']) echo ' checked="checked" '; ?>/>
			</td>
			<td class="lltooltip" title="<?php _e('Select if AJAX should be used to only reload the list of links without reloading the whole page or HTML GET to reload entire page with a new link. The Permalinks option must be enabled for HTML GET + Permalink to work correctly.', 'link-library'); ?>"><?php _e('Switching Method', 'link-library'); ?></td>
			<td>
				<select name="showonecatmode" id="showonecatmode" style="width:200px;">
					<option value="AJAX"<?php if ($options['showonecatmode'] == 'AJAX' || $options['showonecatmode'] == '') { echo ' selected="selected"';} ?>>AJAX</option>
					<option value="HTMLGET"<?php if ($options['showonecatmode'] == 'HTMLGET') { echo ' selected="selected"';} ?>>HTML GET</option>
					<option value="HTMLGETPERM"<?php if ($options['showonecatmode'] == 'HTMLGETPERM') { echo ' selected="selected"';} ?>>HTML GET + Permalink</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Default category to be shown when only showing one at a time (numeric ID)', 'link-library'); ?>
			</td>
			<td>
				<input type="text" id="defaultsinglecat" name="defaultsinglecat" size="4" value="<?php echo $options['defaultsinglecat']; ?>"/>
			</td>
		</tr>
		<tr>
			<td class="lltooltip" title="<?php _e('File path is relative to Link Library plugin directory', 'link-library'); ?>">
				<?php _e('Icon to display when performing AJAX queries', 'link-library'); ?>
			</td>
			<td class="lltooltip" title="<?php _e('File path is relative to Link Library plugin directory', 'link-library'); ?>">
				<input type="text" id="loadingicon" name="loadingicon" size="40" value="<?php if ($options['loadingicon'] == '') {echo '/icons/Ajax-loader.gif';} else {echo strval($options['loadingicon']);} ?>"/>
			</td>
		</tr>
		<tr>
			<td class="lltooltip" title='<?php _e('Only show a limited number of links and add page navigation links', 'link-library'); ?>'>
				<?php _e('Paginate Results', 'link-library'); ?>
			</td>
			<td class="lltooltip" title='<?php _e('Only show a limited number of links and add page navigation links', 'link-library'); ?>'>
				<input type="checkbox" id="pagination" name="pagination" <?php if ($options['pagination']) echo ' checked="checked" '; ?>/>
			</td>
			<td class="lltooltip" title="<?php _e('Number of Links to be Displayed per Page in Pagination Mode', 'link-library'); ?>">
				<?php _e('Links per Page', 'link-library'); ?>
			</td>
			<td class="lltooltip" title="<?php _e('Number of Links to be Displayed per Page in Pagination Mode', 'link-library'); ?>">
				<input type="text" id="linksperpage" name="linksperpage" size="3" value="<?php echo $options['linksperpage']; ?>"/>
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Hide Results if Empty', 'link-library'); ?>
			</td>
			<td>
				<input type="checkbox" id="hide_if_empty" name="hide_if_empty" <?php if ($options['hide_if_empty']) echo ' checked="checked" '; ?>/>
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Enable Permalinks', 'link-library'); ?>
			</td>
			<td>
				<input type="checkbox" id="enablerewrite" name="enablerewrite" <?php if ($options['enablerewrite']) echo ' checked="checked" '; ?>/>
			</td>
			<td>
				<?php _e('Permalinks Page', 'link-library'); ?>
			</td>
			<td>
				<input type="text" id="rewritepage" name="rewritepage" size="40" value="<?php echo $options['rewritepage']; ?>"/>
			</td>
		</tr>
		</table>
		</div>

	<?php }

	function settingssets_categories_meta_box($data) 
	{ 
		$options = $data['options'];
		$settings = $data['settings'];
	?>
		<div style='padding-top:15px'>
		<table>
		<tr>
			<td>
				<?php _e('Results Order', 'link-library'); ?>
			</td>
			<td>
				<select name="order" id="order" style="width:200px;">
					<option value="name"<?php if ($options['order'] == 'name') { echo ' selected="selected"';} ?>><?php _e('Order by Name', 'link-library'); ?></option>
					<option value="id"<?php if ($options['order'] == 'id') { echo ' selected="selected"';} ?>><?php _e('Order by ID', 'link-library'); ?></option>
					<option value="catlist"<?php if ($options['order'] == 'catlist') { echo ' selected="selected"';} ?>><?php _e('Order of categories based on included category list', 'link-library'); ?></option>
					<option value="order"<?php if ($options['order'] == 'order') { echo ' selected="selected"';} ?>><?php _e('Order by', 'link-library'); ?> 'My Link Order' <?php _e('Wordpress Plugin', 'link-library'); ?></option>
				</select>
			</td>
			<td style='width:100px'></td>
			<td style='width:200px'>
				<?php _e('Link Categories Display Format', 'link-library'); ?>
			</td>
			<td>
				<select name="flatlist" id="flatlist" style="width:200px;">
					<option value="false"<?php if ($options['flatlist'] == false) { echo ' selected="selected"';} ?>><?php _e('Table', 'link-library'); ?></option>
					<option value="true"<?php if ($options['flatlist'] == true) { echo ' selected="selected"';} ?>><?php _e('Unordered List', 'link-library'); ?></option>
				</select>
			</td>
		</tr>	
		<tr>
			<td>
				<?php _e('Display link counts', 'link-library'); ?>
			</td>
			<td>
				<input type="checkbox" id="showcatlinkcount" name="showcatlinkcount" <?php if ($options['showcatlinkcount']) echo ' checked="checked" '; ?>/>
			</td>
			<td style='width:100px'></td>
			<td style='width:200px'>
			</td>
			<td>
			</td>
		</tr>
		<tr>
			<td class="lltooltip" title="<?php _e('This setting does not apply when selecting My Link Order for the order', 'link-library'); ?>">
				<?php _e('Direction', 'link-library'); ?>
			</td>
			<td class="lltooltip" title="<?php _e('This setting does not apply when selecting My Link Order for the order', 'link-library'); ?>">
				<select name="direction" id="direction" style="width:100px;">
					<option value="ASC"<?php if ($options['direction'] == 'ASC') { echo ' selected="selected"';} ?>><?php _e('Ascending', 'link-library'); ?></option>
					<option value="DESC"<?php if ($options['direction'] == 'DESC') { echo ' selected="selected"';} ?>><?php _e('Descending', 'link-library'); ?></option>
				</select>
			</td>
			<td></td>
			<td class="lltooltip" title="<?php _e('Use [ and ] in the description to perform special actions using HTML such as inserting images instead of < and >', 'link-library'); ?>">
				<?php _e('Show Category Description', 'link-library'); ?>
			</td>
			<td class="lltooltip" title="<?php _e('Use [ and ] in the description to perform special actions using HTML such as inserting images instead of < and >', 'link-library'); ?>">
				<input type="checkbox" id="showcategorydescheaders" name="showcategorydescheaders" <?php if ($options['showcategorydescheaders']) echo ' checked="checked" '; ?>/>
				<span style='margin-left: 17px'><?php _e('Position', 'link-library'); ?>:</span>							
				<select name="catlistdescpos" id="catlistdescpos" style="width:100px;">
					<option value="right"<?php if ($options['catlistdescpos'] == 'right') { echo ' selected="selected"';} ?>><?php _e('Right', 'link-library'); ?></option>
					<option value="left"<?php if ($options['catlistdescpos'] == 'left') { echo ' selected="selected"';} ?>><?php _e('Left', 'link-library'); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Width of Categories Table in Percents', 'link-library'); ?>
			</td>
			<td>
				<input type="text" id="table_width" name="table_width" size="10" value="<?php echo strval($options['table_width']); ?>"/>
			</td>
			<td></td>
			<td class="lltooltip" title='<?php _e('Determines the number of alternating div tags that will be placed before and after each link category', 'link-library'); ?>.<br /><br /><?php _e('These div tags can be used to style of position link categories on the link page', 'link-library'); ?>.'>
				<?php _e('Number of alternating div classes', 'link-library'); ?>
			</td>
			<td class="lltooltip" title='<?php _e('Determines the number of alternating div tags that will be placed before and after each link category', 'link-library'); ?>.<br /><br /><?php _e('These div tags can be used to style of position link categories on the link page', 'link-library'); ?>.'>
				<select name="catlistwrappers" id="catlistwrappers" style="width:200px;">
					<option value="1"<?php if ($options['catlistwrappers'] == 1) { echo ' selected="selected"';} ?>>1</option>
					<option value="2"<?php if ($options['catlistwrappers'] == 2) { echo ' selected="selected"';} ?>>2</option>
					<option value="3"<?php if ($options['catlistwrappers'] == 3) { echo ' selected="selected"';} ?>>3</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Number of columns in Categories Table', 'link-library'); ?>
			</td>
			<td>
				<input type="text" id="num_columns" name="num_columns" size="10" value="<?php echo strval($options['num_columns']); ?>">
			</td>
			<td></td>
			<td>
				<?php _e('First div class name', 'link-library'); ?>
			</td>
			<td>
				<input type="text" id="beforecatlist1" name="beforecatlist1" size="40" value="<?php echo $options['beforecatlist1']; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Use Div Class or Heading tag around Category Names', 'link-library'); ?>
			</td>
			<td>
				<select name="divorheader" id="divorheader" style="width:200px;">
					<option value="false"<?php if ($options['divorheader'] == false) { echo ' selected="selected"';} ?>><?php _e('Div Class', 'link-library'); ?></option>
					<option value="true"<?php if ($options['divorheader'] == true) { echo ' selected="selected"';} ?>><?php _e('Heading Tag', 'link-library'); ?></option>
				</select>
			</td>
			<td></td>
			<td>
				<?php _e('Second div class name', 'link-library'); ?>
			</td>
			<td>
				<input type="text" id="beforecatlist2" name="beforecatlist2" size="40" value="<?php echo $options['beforecatlist2']; ?>" />
			</td>
		</tr>
		<tr>
			<td class="lltooltip" title="<?php _e('Example div class name: linklistcatname, Example Heading Label: h3', 'link-library'); ?>">
				<?php _e('Div Class Name or Heading label', 'link-library'); ?>
			</td>
			<td  class="lltooltip" title="<?php _e('Example div class name: linklistcatname, Example Heading Label: h3', 'link-library'); ?>">
				<input type="text" id="catnameoutput" name="catnameoutput" size="30" value="<?php echo strval($options['catnameoutput']); ?>"/>
			</td>
			<td></td>
			<td>
				<?php _e('Third div class name', 'link-library'); ?>
			</td>
			<td>
				<input type="text" id="beforecatlist3" name="beforecatlist3" size="40" value="<?php echo $options['beforecatlist3']; ?>" />
			</td>
		</tr>
		<tr>
		<td class="lltooltip" title="<?php _e('Set this address to a page running Link Library to place categories on a different page. Should always be used with the Show One Category at a Time and HTMLGET fetch method.', 'link-library'); ?>">
			<?php _e('Category Target Address', 'link-library'); ?>
		</td>
		<td colspan="4" class="lltooltip" title="<?php _e('Set this address to a page running Link Library to place categories on a different page. Should always be used with the Show One Category at a Time and HTMLGET fetch method.', 'link-library'); ?>">
			<input type="text" id="cattargetaddress" name="cattargetaddress" size="120" value="<?php echo $options['cattargetaddress']; ?>" /></td>
		</tr>
		</table>
		</div>
	<?php }
	
	function settingssets_linkelement_meta_box($data) { 
		$options = $data['options'];
		$settings = $data['settings'];
	?>

		<table>
		<tr>
			<td>
				<?php _e('Link Results Order', 'link-library'); ?>
			</td>
			<td>
				<select name="linkorder" id="linkorder" style="width:250px;">
					<option value="name"<?php if ($options['linkorder'] == 'name') { echo ' selected="selected"';} ?>><?php _e('Order by Name', 'link-library'); ?></option>
					<option value="id"<?php if ($options['linkorder'] == 'id') { echo ' selected="selected"';} ?>><?php _e('Order by ID', 'link-library'); ?></option>
					<option value="order"<?php if ($options['linkorder'] == 'order') { echo ' selected="selected"';} ?>><?php _e('Order set by ', 'link-library'); ?>'My Link Order' <?php _e('Wordpress Plugin', 'link-library'); ?></option>
					<option value="random"<?php if ($options['linkorder'] == 'random') { echo ' selected="selected"';} ?>><?php _e('Order randomly', 'link-library'); ?></option>                                                                
				</select>
			</td>
			<td style='width:100px'></td>
			<td class="lltooltip" title="<?php _e('Use [ and ] in the description to perform special actions using HTML such as inserting images instead of < and >', 'link-library'); ?>">
				<?php _e('Show Category Description', 'link-library'); ?>
			</td>
			<td class="lltooltip" title="<?php _e('Use [ and ] in the description to perform special actions using HTML such as inserting images instead of < and >', 'link-library'); ?>">
				<input type="checkbox" id="showcategorydesclinks" name="showcategorydesclinks" <?php if ($options['showcategorydesclinks']) echo ' checked="checked" '; ?>/>
				<span style='margin-left: 17px'><?php _e('Position', 'link-library'); ?>:</span>
				<select name="catdescpos" id="catdescpos" style="width:100px;">
					<option value="right"<?php if ($options['catdescpos'] == 'right') { echo ' selected="selected"';} ?>><?php _e('Right', 'link-library'); ?></option>
					<option value="left"<?php if ($options['catdescpos'] == 'left') { echo ' selected="selected"';} ?>><?php _e('Left', 'link-library'); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php _e('List Featured Links ahead of Regular Links', 'link-library'); ?></td>
			<td><input type="checkbox" id="featuredfirst" name="featuredfirst" <?php if ($options['featuredfirst']) echo ' checked="checked" '; ?>/></td>
		</tr>
		<tr>
			<td class="lltooltip" title='<?php _e('Except for My Link Order mode', 'link-library'); ?>'>
				<?php _e('Direction', 'link-library'); ?>
			</td>
			<td class="lltooltip" title='<?php _e('Except for My Link Order mode', 'link-library'); ?>'>
				<select name="linkdirection" id="linkdirection" style="width:200px;">
					<option value="ASC"<?php if ($options['linkdirection'] == 'ASC') { echo ' selected="selected"';} ?>><?php _e('Ascending', 'link-library'); ?></option>
					<option value="DESC"<?php if ($options['linkdirection'] == 'DESC') { echo ' selected="selected"';} ?>><?php _e('Descending', 'link-library'); ?></option>
				</select>
			</td>
			<td></td>
			<td class="lltooltip" title="<?php _e('Leave empty to show all results', 'link-library'); ?>">
				<?php _e('Max number of links to display', 'link-library'); ?>
			</td>
			<td class="lltooltip" title="<?php _e('Leave empty to show all results', 'link-library'); ?>">
				<input type="text" id="maxlinks" name="maxlinks" size="4" value="<?php echo $options['maxlinks']; ?>"/>
			</td>
		</tr>	
		<tr>
			<td class="lltooltip" title="<?php _e('Sets default link target window, does not override specific targets set in links', 'link-library'); ?>">
				<?php _e('Link Target', 'link-library'); ?>
			</td>
			<td class="lltooltip" title="<?php _e('Sets default link target window, does not override specific targets set in links', 'link-library'); ?>">
				<input type="text" id="linktarget" name="linktarget" size="40" value="<?php echo $options['linktarget']; ?>"/>
			</td>
			<td></td>
			<td>
				<?php _e('Link Display Format', 'link-library'); ?>
			</td>
			<td>
				<select name="displayastable" id="displayastable" style="width:200px;">
					<option value="true"<?php if ($options['displayastable'] == true) { echo ' selected="selected"';} ?>><?php _e('Table', 'link-library'); ?></option>
					<option value="false"<?php if ($options['displayastable'] == false) { echo ' selected="selected"';} ?>><?php _e('Unordered List', 'link-library'); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Show Column Headers', 'link-library'); ?>
			</td>
			<td>
				<input type="checkbox" id="showcolumnheaders" name="showcolumnheaders" <?php if ($options['showcolumnheaders']) echo ' checked="checked" '; ?>/>
			</td>
			<td></td>
			<td>
				<?php _e('Link Column Header', 'link-library'); ?>
			</td>
			<td>
				<input type="text" id="linkheader" name="linkheader" size="40" value="<?php echo $options['linkheader']; ?>"/>
			</td>
		</tr>	
		<tr>
			<td>
				<?php _e('Description Column Header', 'link-library'); ?>
			</td>
			<td>
				<input type="text" id="descheader" name="descheader" size="40" value="<?php echo $options['descheader']; ?>"/>
			</td>
			<td></td>
			<td>
				<?php _e('Notes Column Header', 'link-library'); ?>
			</td>
			<td>
				<input type="text" id="notesheader" name="notesheader" size="40" value="<?php echo $options['notesheader']; ?>"/>
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Hide Category Names', 'link-library'); ?>
			</td>
			<td>
				<input type="checkbox" id="hidecategorynames" name="hidecategorynames" <?php if ($options['hidecategorynames'] == true) echo ' checked="checked" '; ?>/>
			</td>
			<td></td>
			<td>
				<?php _e('Show Hidden Links', 'link-library'); ?>
			</td>
			<td>
				<input type="checkbox" id="showinvisible" name="showinvisible" <?php if ($options['showinvisible'] == true) echo ' checked="checked" '; ?>/>
			</td>
		</tr>	
		<tr>
			<td class="lltooltip" title='<?php _e('Need to be active for Link Categories to work', 'link-library'); ?>'>
				<?php _e('Embed HTML anchors', 'link-library'); ?>
			</td>
			<td class="lltooltip" title='<?php _e('Need to be active for Link Categories to work', 'link-library'); ?>'>
				<input type="checkbox" id="catanchor" name="catanchor" <?php if ($options['catanchor']) echo ' checked="checked" '; ?>/>
			</td>
		</tr>
		</table>
	<?php }

	function settingssets_subfieldtable_meta_box($data) { 
		$options = $data['options'];
		$settings = $data['settings'];
	?>

			<?php _e('Arrange the items below via drag-and-drop to order the various Link Library elements.', 'link-library'); ?><br /><br />
			<ul id="sortable">
			<?php if ($options['dragndroporder'] == '') $dragndroporder = '1,2,3,4,5,6,7,8,9,10'; else $dragndroporder = $options['dragndroporder'];
				  $dragndroparray = explode(',', $dragndroporder);
				  if ($dragndroparray)
				  {
					foreach ($dragndroparray as $arrayelements) {
						switch ($arrayelements) {
							case 1: ?>
								<li id="1" style='background-color: #1240ab'><?php _e('Image', 'link-library'); ?></li>
							<?php break;
							case 2: ?>
								<li id="2" style='background-color: #4671d5'><?php _e('Name', 'link-library'); ?></li>
							<?php break;
							case 3: ?>
								<li id="3" style='background-color: #39e639'><?php _e('Date', 'link-library'); ?></li>
							<?php break;
							case 4: ?>
								<li id="4" style='background-color: #009999'><?php _e('Desc', 'link-library'); ?></li>
							<?php break;
							case 5: ?>
								<li id="5" style='background-color: #00cc00'><?php _e('Notes', 'link-library'); ?></li>
							<?php break;
							case 6: ?>
								<li id="6" style='background-color: #008500'><?php _e('RSS', 'link-library'); ?></li>
							<?php break;
							case 7: ?>
								<li id="7" style='background-color: #5ccccc'><?php _e('Web Link', 'link-library'); ?></li>
							<?php break;
							case 8: ?>
								<li id="8" style='background-color: #6c8cd5'><?php _e('Phone', 'link-library'); ?></li>
							<?php break;
							case 9: ?>
								<li id="9" style='background-color: #67e667'><?php _e('E-mail', 'link-library'); ?></li>
							<?php break;
							case 10: ?>
								<li id="10" style='background-color: #33cccc'><?php _e('Hits', 'link-library'); ?></li>
							<?php break;
							case 11: ?>
								<li id="11" style='background-color: #33cc00'><?php _e('Rating', 'link-library'); ?></li>
							<?php break;
							case 12: ?>
								<li id="12" style='background-color: #33ccff'><?php _e('Large Desc', 'link-library'); ?></li>
							<?php break;
						}
					}
				}
			?>
			</ul>
			<input type="hidden" id="dragndroporder" name="dragndroporder" size="60" value="<?php echo $options['dragndroporder']; ?>"/>
			<br />
			<table class='widefat' style='width: 1000px;margin:15px 5px 10px 0px;clear:none;background: #DFDFDF url(/wp-admin/images/gray-grad.png) repeat-x scroll left top;'>
				<thead>
					<th style='width: 100px'></th>
					<th style='width: 40px'><?php _e('Display', 'link-library'); ?></th>
					<th style='width: 80px'><?php _e('Before', 'link-library'); ?></th>
					<th style='width: 80px'><?php _e('After', 'link-library'); ?></th>
					<th style='width: 80px'><?php _e('Additional Details', 'link-library'); ?></th>
					<th style='width: 80px'><?php _e('Link Source', 'link-library'); ?></th>
				</thead>
				<tr>
					<td class="lltooltip" title='<?php _e('This column allows for the output of text/code before a number of links determined by the Display field', 'link-library'); ?>'><?php _e('Intermittent Before Link', 'link-library'); ?></td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Frequency of additional output before and after complete link group', 'link-library'); ?>'>
						<input type="text" id="linkaddfrequency" name="linkaddfrequency" size="10" value="<?php echo strval($options['linkaddfrequency']); ?>"/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Output before complete link group (link, notes, desc, etc...)', 'link-library'); ?>'>
						<input type="text" id="addbeforelink" name="addbeforelink" size="22" value="<?php echo stripslashes($options['addbeforelink']); ?>"/>
					</td>
					<td style='background: #FFF'></td>
					<td style='background: #FFF'></td>
					<td style='background: #FFF'></td>
				</tr>
				<tr>
					<td class="lltooltip" title='<?php _e('This column allows for the output of text/code before each link', 'link-library'); ?>'><?php _e('Before Link', 'link-library'); ?></td>
					<td style='background: #FFF'></td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Output before complete link group (link, notes, desc, etc...)', 'link-library'); ?>'>
						<input type="text" id="beforeitem" name="beforeitem" size="22" value="<?php echo stripslashes($options['beforeitem']); ?>"/>
					</td>
					<td style='background: #FFF'></td>
					<td style='background: #FFF'></td>
					<td style='background: #FFF'></td>
				</tr>
				<?php if ($options['dragndroporder'] == '') $dragndroporder = '1,2,3,4,5,6,7,8,9,10'; else $dragndroporder = $options['dragndroporder'];
				  $dragndroparray = explode(',', $dragndroporder);
				  if ($dragndroparray)
				  {
					foreach ($dragndroparray as $arrayelements) {
						switch ($arrayelements) {
							case 1: /* -------------------------------- Link Image -------------------------------------------*/ ?>	
				<tr>
					<td style='background-color: #1240ab; color: #fff' class="lltooltip" title='<?php _e('This column allows for the output of text/code before each link image', 'link-library'); ?>'><?php _e('Image', 'link-library'); ?></td>
					<td style='text-align:center;background: #FFF'>
						<input type="checkbox" id="show_images" name="show_images" <?php if ($options['show_images']) echo ' checked="checked" '; ?>/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed before each link image', 'link-library'); ?>'>
						<input type="text" id="beforeimage" name="beforeimage" size="22" value="<?php echo stripslashes($options['beforeimage']); ?>"/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed after each link image', 'link-library'); ?>'>
						<input type="text" id="afterimage" name="afterimage" size="22" value="<?php echo stripslashes($options['afterimage']); ?>"/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('CSS Class to be assigned to link image', 'link-library'); ?>'>
						<input type="text" id="imageclass" name="imageclass" size="22" value="<?php echo $options['imageclass']; ?>"/>
					</td>
					<td style='background: #FFF'>
						<select name="sourceimage" id="sourceimage" style="width:200px;">
							<option value="primary"<?php if ($options['sourceimage'] == "primary") { echo ' selected="selected"';} ?>><?php _e('Primary', 'link-library'); ?></option>
							<option value="secondary"<?php if ($options['sourceimage'] == "secondary") { echo ' selected="selected"';} ?>><?php _e('Secondary', 'link-library'); ?></option>
						</select>
					</td>
				</tr>
				<?php break;
				case 2: /* -------------------------------- Link Name -------------------------------------------*/ ?>
				<tr>
					<td style='background-color: #4671d5; color: #fff' class="lltooltip" title='<?php _e('This column allows for the output of text/code before and after each link name', 'link-library'); ?>'><?php _e('Link Name', 'link-library'); ?></td>
					<td style='text-align:center;background: #FFF'>
						<input type="checkbox" id="showname" name="showname" <?php if ($options['showname'] == true) echo ' checked="checked" '; ?>/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed before each link', 'link-library'); ?>'>
						<input type="text" id="beforelink" name="beforelink" size="22" value="<?php echo stripslashes($options['beforelink']); ?>"/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed after each link', 'link-library'); ?>'>
						<input type="text" id="afterlink" name="afterlink" size="22" value="<?php echo stripslashes($options['afterlink']); ?>"/>
					</td>
					<td style='background: #FFF'></td>
					<td style='background: #FFF'>
						<select name="sourcename" id="sourcename" style="width:200px;">
							<option value="primary"<?php if ($options['sourcename'] == "primary") { echo ' selected="selected"';} ?>><?php _e('Primary', 'link-library'); ?></option>
							<option value="secondary"<?php if ($options['sourcename'] == "secondary") { echo ' selected="selected"';} ?>><?php _e('Secondary', 'link-library'); ?></option>
						</select>
					</td>
				</tr>
				<?php break;
				case 3: /* -------------------------------- Link Date -------------------------------------------*/ ?>
				<tr>
					<td style='background-color: #39e639; color:#fff' class="lltooltip" title='<?php _e('This column allows for the output of text/code before and after each link date stamp', 'link-library'); ?>'><?php _e('Link Date', 'link-library'); ?></td>
					<td style='background: #FFF;text-align:center' class="lltooltip" title='<?php _e('Check to display link date', 'link-library'); ?>'>
						<input type="checkbox" id="showdate" name="showdate" <?php if ($options['showdate']) echo ' checked="checked" '; ?>/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed before each date', 'link-library'); ?>'>
						<input type="text" id="beforedate" name="beforedate" size="22" value="<?php echo stripslashes($options['beforedate']); ?>"/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed after each date', 'link-library'); ?>'>
						<input type="text" id="afterdate" name="afterdate" size="22" value="<?php echo stripslashes($options['afterdate']); ?>"/>
					</td>
					<td style='background: #FFF'></td>
					<td style='background: #FFF'></td>
				</tr>
				<?php break;
				case 4: /* -------------------------------- Link Description -------------------------------------------*/ ?>
				<tr>
					<td style='background-color: #009999;color:#fff' class="lltooltip" title='<?php _e('This column allows for the output of text/code before and after each link description', 'link-library'); ?>'><?php _e('Link Description', 'link-library'); ?></td>
					<td style='background: #FFF;text-align: center' class="lltooltip" title='<?php _e('Check to display link descriptions', 'link-library'); ?>'>
						<input type="checkbox" id="showdescription" name="showdescription" <?php if ($options['showdescription']) echo ' checked="checked" '; ?>/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed before each description', 'link-library'); ?>'>
						<input type="text" id="beforedesc" name="beforedesc" size="22" value="<?php echo stripslashes($options['beforedesc']); ?>"/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed after each description', 'link-library'); ?>'>
						<input type="text" id="afterdesc" name="afterdesc" size="22" value="<?php echo stripslashes($options['afterdesc']); ?>"/>
					</td>
					<td style='background: #FFF'></td>
					<td style='background: #FFF'></td>
				</tr>
				<?php break;
				case 5: /* -------------------------------- Link Notes -------------------------------------------*/ ?>
				<tr>
					<td style='background-color: #00cc00;color:#fff' class="lltooltip" title='<?php _e('This column allows for the output of text/code before and after each link notes', 'link-library'); ?>'><?php _e('Link Notes', 'link-library'); ?></td>
					<td style='background: #FFF;text-align: center' class="lltooltip" title='<?php _e('Check to display link notes', 'link-library'); ?>'>
						<input type="checkbox" id="shownotes" name="shownotes" <?php if ($options['shownotes']) echo ' checked="checked" '; ?>/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed before each note', 'link-library'); ?>'>
						<input type="text" id="beforenote" name="beforenote" size="22" value="<?php echo stripslashes($options['beforenote']); ?>"/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed after each note', 'link-library'); ?>'>
						<input type="text" id="afternote" name="afternote" size="22" value="<?php echo stripslashes($options['afternote']); ?>"/>
					</td>
					<td style='background: #FFF'></td>
					<td style='background: #FFF'></td>
				</tr>
				<?php break;
				case 6: /* -------------------------------- Link RSS Icons -------------------------------------------*/ ?>
				<tr>
					<td style='background-color: #008500;color:#fff' class="lltooltip" title='<?php _e('This column allows for the output of text/code before and after the RSS icons', 'link-library'); ?>'><?php _e('RSS Icons', 'link-library'); ?></td>
					<td style='text-align:center;background: #FFF'>
						<?php _e('See below', 'link-library'); ?>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed before RSS Icons', 'link-library'); ?>'>
						<input type="text" id="beforerss" name="beforerss" size="22" value="<?php echo stripslashes($options['beforerss']); ?>"/>
					</td>
					<td  style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed after RSS Icons', 'link-library'); ?>'>
						<input type="text" id="afterrss" name="afterrss" size="22" value="<?php echo stripslashes($options['afterrss']); ?>"/>
					</td>
					<td style='background: #FFF'></td>
					<td style='background: #FFF'></td>
				</tr>
				<?php break;
				case 7: /* -------------------------------- Web Link -------------------------------------------*/ ?>
				<tr>
					<td style='background-color: #5ccccc;color:#fff' class="lltooltip" title='<?php _e('This column allows for the output of text/code before and after the Web Link', 'link-library'); ?>'><?php _e('Web Link', 'link-library'); ?></td>
					<td style='text-align:center;background: #FFF'>
						<select name="displayweblink" id="displayweblink" style="width:80px;">
							<option value="false"<?php if ($options['displayweblink'] == "false") { echo ' selected="selected"';} ?>><?php _e('False', 'link-library'); ?></option>
							<option value="address"<?php if ($options['displayweblink'] == "address") { echo ' selected="selected"';} ?>><?php _e('Web Address', 'link-library'); ?></option>
							<option value="label"<?php if ($options['displayweblink'] == "label") { echo ' selected="selected"';} ?>><?php _e('Label', 'link-library'); ?></option>
						</select>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed before Web Link', 'link-library'); ?>'>
						<input type="text" id="beforeweblink" name="beforeweblink" size="22" value="<?php echo stripslashes($options['beforeweblink']); ?>"/>
					</td>
					<td  style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed after Web Link', 'link-library'); ?>'>
						<input type="text" id="afterweblink" name="afterweblink" size="22" value="<?php echo stripslashes($options['afterweblink']); ?>"/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Text Label that the web link will be assigned to.', 'link-library'); ?>'>
						<input type="text" id="weblinklabel" name="weblinklabel" size="22" value="<?php echo stripslashes($options['weblinklabel']); ?>"/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Select which link address will be displayed / used for link', 'link-library'); ?>'>
						<select name="sourceweblink" id="sourceweblink" style="width:200px;">
							<option value="primary"<?php if ($options['sourceweblink'] == "primary") { echo ' selected="selected"';} ?>><?php _e('Primary', 'link-library'); ?></option>
							<option value="secondary"<?php if ($options['sourceweblink'] == "secondary") { echo ' selected="selected"';} ?>><?php _e('Secondary', 'link-library'); ?></option>
						</select>
					</td>
				</tr>
				<?php break;
				case 8: /* -------------------------------- Telephone -------------------------------------------*/ ?>
				<tr>
					<td style='background-color: #6c8cd5;color:#fff' class="lltooltip" title='<?php _e('This column allows for the output of text/code before and after the Telephone Number', 'link-library'); ?>'><?php _e('Telephone', 'link-library'); ?></td>
					<td style='text-align:center;background: #FFF'>
						<select name="showtelephone" id="showtelephone" style="width:80px;">
							<option value="false"<?php if ($options['showtelephone'] == "false") { echo ' selected="selected"';} ?>><?php _e('False', 'link-library'); ?></option>
							<option value="plain"<?php if ($options['showtelephone'] == "plain") { echo ' selected="selected"';} ?>><?php _e('Plain Text', 'link-library'); ?></option>
							<option value="link"<?php if ($options['showtelephone'] == "link") { echo ' selected="selected"';} ?>><?php _e('Link', 'link-library'); ?></option>
							<option value="label"<?php if ($options['showtelephone'] == "label") { echo ' selected="selected"';} ?>><?php _e('Label', 'link-library'); ?></option>
						</select>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed before Telephone Number', 'link-library'); ?>'>
						<input type="text" id="beforetelephone" name="beforetelephone" size="22" value="<?php echo stripslashes($options['beforetelephone']); ?>"/>
					</td>
					<td  style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed after Telephone Number', 'link-library'); ?>'>
						<input type="text" id="aftertelephone" name="aftertelephone" size="22" value="<?php echo stripslashes($options['aftertelephone']); ?>"/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Text Label that the telephone will be assigned to.', 'link-library'); ?>'>
						<input type="text" id="telephonelabel" name="telephonelabel" size="22" value="<?php echo stripslashes($options['telephonelabel']); ?>"/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Select which link address will be displayed / used for link', 'link-library'); ?>'>
						<select name="sourcetelephone" id="sourcetelephone" style="width:200px;">
							<option value="primary"<?php if ($options['sourcetelephone'] == "primary") { echo ' selected="selected"';} ?>><?php _e('Primary', 'link-library'); ?></option>
							<option value="secondary"<?php if ($options['sourcetelephone'] == "secondary") { echo ' selected="selected"';} ?>><?php _e('Secondary', 'link-library'); ?></option>
						</select>
					</td>
				</tr>
				<?php break;
				case 9: /* -------------------------------- E-mail -------------------------------------------*/ ?>
				<tr>
					<td style='background-color: #67e667;color:#fff' class="lltooltip" title='<?php _e('This column allows for the output of text/code before and after the E-mail', 'link-library'); ?>'><?php _e('E-mail', 'link-library'); ?></td>
					<td style='text-align:center;background: #FFF'>
						<select name="showemail" id="showemail" style="width:80px;">
							<option value="false"<?php if ($options['showemail'] == "false") { echo ' selected="selected"';} ?>><?php _e('False', 'link-library'); ?></option>
							<option value="plain"<?php if ($options['showemail'] == "plain") { echo ' selected="selected"';} ?>><?php _e('Plain Text', 'link-library'); ?></option>
							<option value="mailto"<?php if ($options['showemail'] == "mailto") { echo ' selected="selected"';} ?>><?php _e('MailTo Link', 'link-library'); ?></option>
							<option value="mailtolabel"<?php if ($options['showemail'] == "mailtolabel") { echo ' selected="selected"';} ?>><?php _e('MailTo Link with Label', 'link-library'); ?></option>
							<option value="command"<?php if ($options['showemail'] == "command") { echo ' selected="selected"';} ?>><?php _e('Formatted Command', 'link-library'); ?></option>
							<option value="commandlabel"<?php if ($options['showemail'] == "commandlabel") { echo ' selected="selected"';} ?>><?php _e('Formatted Command with Labels', 'link-library'); ?></option>
						</select>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed before E-mail', 'link-library'); ?>'>
						<input type="text" id="beforeemail" name="beforeemail" size="22" value="<?php echo stripslashes($options['beforeemail']); ?>"/>
					</td>
					<td  style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed after E-mail', 'link-library'); ?>'>
						<input type="text" id="afteremail" name="afteremail" size="22" value="<?php echo stripslashes($options['afteremail']); ?>"/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Text Label that the e-mail will be assigned to represent the e-mail link.', 'link-library'); ?>'>
						<input type="text" id="emaillabel" name="emaillabel" size="22" value="<?php echo stripslashes($options['emaillabel']); ?>"/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Command that the e-mail will be embedded in. In the case of a command, use the symbols #email and #company to indicate the position where these elements should be inserted.', 'link-library'); ?>'>
						<input type="text" id="emailcommand" name="emailcommand" size="22" value="<?php echo stripslashes($options['emailcommand']); ?>"/>
					</td>
				</tr>
				<?php break;
				case 10: /* -------------------------------- Link Hits -------------------------------------------*/ ?>
				<tr>
					<td style='background-color: #33cccc;color:#fff' class="lltooltip" title='<?php _e('This column allows for the output of text/code before and after Link Hits', 'link-library'); ?>'><?php _e('Link Hits', 'link-library'); ?></td>
					<td style='text-align:center;background: #FFF'>
						<input type="checkbox" id="showlinkhits" name="showlinkhits" <?php if ($options['showlinkhits']) echo ' checked="checked" '; ?>/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed before Link Hits', 'link-library'); ?>'>
						<input type="text" id="beforelinkhits" name="beforelinkhits" size="22" value="<?php echo stripslashes($options['beforelinkhits']); ?>"/>
					</td>
					<td  style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed after Link Hits', 'link-library'); ?>'>
						<input type="text" id="afterlinkhits" name="afterlinkhits" size="22" value="<?php echo stripslashes($options['afterlinkhits']); ?>"/>
					</td>
					<td style='background: #FFF'></td>
					<td style='background: #FFF'></td>
				</tr>
				<?php break;
				case 11: /* -------------------------------- Link Rating -------------------------------------------*/ ?>
				<tr>
					<td style='background-color: #33cc00;color:#fff' class="lltooltip" title='<?php _e('This column allows for the output of text/code before and after the Link Rating', 'link-library'); ?>'><?php _e('Link Rating', 'link-library'); ?></td>
					<td style='text-align:center;background: #FFF'>
						<input type="checkbox" id="showrating" name="showrating" <?php if ($options['showrating']) echo ' checked="checked" '; ?>/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed before Link Rating', 'link-library'); ?>'>
						<input type="text" id="beforelinkrating" name="beforelinkrating" size="22" value="<?php echo stripslashes($options['beforelinkrating']); ?>"/>
					</td>
					<td  style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed after Link Rating', 'link-library'); ?>'>
						<input type="text" id="afterlinkrating" name="afterlinkrating" size="22" value="<?php echo stripslashes($options['afterlinkrating']); ?>"/>
					</td>
					<td style='background: #FFF'></td>
					<td style='background: #FFF'></td>
				</tr>
				<?php break;
				case 12: /* -------------------------------- Large Description -------------------------------------------*/ ?>
				<tr>
					<td style='background-color: #33ccff;color:#fff' class="lltooltip" title='<?php _e('This column allows for the output of text/code before and after the Link Large Description', 'link-library'); ?>'><?php _e('Link Large Description', 'link-library'); ?></td>
					<td style='text-align:center;background: #FFF'>
						<input type="checkbox" id="showlargedescription" name="showlargedescription" <?php if ($options['showlargedescription']) echo ' checked="checked" '; ?>/>
					</td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed before Link Large Description', 'link-library'); ?>'>
						<input type="text" id="beforelargedescription" name="beforelargedescription" size="22" value="<?php echo stripslashes($options['beforelargedescription']); ?>"/>
					</td>
					<td  style='background: #FFF' class="lltooltip" title='<?php _e('Code/Text to be displayed after Link Large Description', 'link-library'); ?>'>
						<input type="text" id="afterlinklargedescription" name="afterlinklargedescription" size="22" value="<?php echo stripslashes($options['afterlinklargedescription']); ?>"/>
					</td>
					<td style='background: #FFF'></td>
					<td style='background: #FFF'></td>
				</tr>
				<?php break;				
						}
					}
				}
				?>
				<tr>
					<td class="lltooltip" title='<?php _e('This column allows for the output of text/code after each link', 'link-library'); ?>'><?php _e('After Link Block', 'link-library'); ?></td>
					<td style='background: #FFF'></td>
					<td style='background: #FFF'></td>
					<td style='background: #FFF' class="lltooltip" title='<?php _e('Output after complete link group (link, notes, desc, etc...)', 'link-library'); ?>'>
						<input type="text" id="afteritem" name="afteritem" size="22" value="<?php echo stripslashes($options['afteritem']); ?>"/>
					</td>
					<td style='background: #FFF'></td>
					<td style='background: #FFF'></td>
				</tr>
				<tr>
					<td class="lltooltip" title='<?php _e('This column allows for the output of text/code after a number of links determined in the first column', 'link-library'); ?>'><?php _e('Intermittent After Link', 'link-library'); ?></td>
					<td style='background: #FFF'></td>
					<td style='background: #FFF'></td>
					<td style='background: #FFF'>
						<input type="text" id="addafterlink" name="addafterlink" size="22" value="<?php echo stripslashes($options['addafterlink']); ?>"/>
					</td>
					<td style='background: #FFF'></td>
					<td style='background: #FFF'></td>
				</tr>
			</table>
		</table>
		<br />
		<table>
		<tr>
			<td style='width=150px'>
				<?php _e('Show Link Updated Flag', 'link-library'); ?>
			</td>
			<td style='width=75px;padding:0px 20px 0px 20px'>
				<input type="checkbox" id="showupdated" name="showupdated" <?php if ($options['showupdated']) echo ' checked="checked" '; ?>/>
			</td>
			<td style='width=20px'>
			</td>
			<td>
				<?php _e('Convert [] to &lt;&gt; in Link Description and Notes', 'link-library'); ?>
			</td>
			<td style='width=75px;padding:0px 20px 0px 20px'>
				<input type="checkbox" id="use_html_tags" name="use_html_tags" <?php if ($options['use_html_tags']) echo ' checked="checked" '; ?>/>
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Add nofollow tag to outgoing links', 'link-library'); ?>
			</td>
			<td style='width=75px;padding:0px 20px 0px 20px'>
				<input type="checkbox" id="nofollow" name="nofollow" <?php if ($options['nofollow']) echo ' checked="checked" '; ?>/>
			</td>
			<td></td>
			<td>
				<?php _e('Show edit links when logged in as editor or administrator', 'link-library'); ?>
			</td>
			<td style='width=75px;padding:0px 20px 0px 20px'>
				<input type="checkbox" id="showadmineditlinks" name="showadmineditlinks" <?php if ($options['showadmineditlinks']) echo ' checked="checked" '; ?>/>
			</td>
		</tr>
		</table>

	<?php }

	function settingssets_rssconfig_meta_box($data) { 
		$options = $data['options'];
		$settings = $data['settings'];
	?>

		<table>
		<tr>
			<td>
				<?php _e('Show RSS Link using Text', 'link-library'); ?>
			</td>
			<td style='width=75px;padding-right:20px'>
				<input type="checkbox" id="show_rss" name="show_rss" <?php if ($options['show_rss']) echo ' checked="checked" '; ?>/>
			</td>
			<td>
				<?php _e('Show RSS Link using Standard Icon', 'link-library'); ?>
			</td>
			<td style='width=75px;padding-right:20px'>
				<input type="checkbox" id="show_rss_icon" name="show_rss_icon" <?php if ($options['show_rss_icon']) echo ' checked="checked" '; ?>/>
			</td>
			<td></td><td style='width=75px;padding-right:20px'></td>
		</tr>
		<tr>
			<td colspan='1' class="lltooltip" title='<?php _e('Used for RSS Preview and RSS Inline Articles options below. Must have write access to directory', 'link-library'); ?>.'>
				<?php _e('RSS Cache Directory', 'link-library'); ?>
			</td>
			<td colspan='5' class="lltooltip" title='<?php _e('Used for RSS Preview and RSS Inline Articles options below. Must have write access to directory', 'link-library'); ?>.'>
				<input type="text" id="rsscachedir" name="rsscachedir" size="80" value="<?php if ($options['rsscachedir'] == '') echo ABSPATH . 'wp-content/cache/link-library'; else echo $options['rsscachedir']; ?>"/>
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Show RSS Preview Link', 'link-library'); ?>
			</td>
			<td>
				<input type="checkbox" id="rsspreview" name="rsspreview" <?php if ($options['rsspreview']) echo ' checked="checked" '; ?>/>
			</td>
			<td>
				<?php _e('Number of articles shown in RSS Preview', 'link-library'); ?>
			</td>
			<td>
				<input type="text" id="rsspreviewcount" name="rsspreviewcount" size="2" value="<?php if ($options['rsspreviewcount'] == '') echo '3'; else echo strval($options['rsspreviewcount']); ?>"/>
			</td>
			<td>
				<?php _e('Show RSS Feed Headers in Link Library output', 'link-library'); ?>
			</td>
			<td>
				<input type="checkbox" id="rssfeedinline" name="rssfeedinline" <?php if ($options['rssfeedinline']) echo ' checked="checked" '; ?>/>
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Show RSS Feed Content in Link Library output', 'link-library'); ?>
			</td>
			<td>
				<input type="checkbox" id="rssfeedinlinecontent" name="rssfeedinlinecontent" <?php if ($options['rssfeedinlinecontent']) echo ' checked="checked" '; ?>/>
			</td>
			<td>
				<?php _e('Number of RSS articles shown in Link Library Output', 'link-library'); ?>
			</td>
			<td>
				<input type="text" id="rssfeedinlinecount" name="rssfeedinlinecount" size="2" value="<?php if ($options['rssfeedinlinecount'] == '') echo '1'; else echo strval($options['rssfeedinlinecount']); ?>"/>
			</td>
			<td></td><td></td>
		</tr>
		<tr>
			<td><?php _e('RSS Preview Width', 'link-library'); ?></td>
			<td><input type="text" id="rsspreviewwidth" name="rsspreviewwidth" size="5" value="<?php if ($options['rsspreviewwidth'] == '') echo '900'; else echo strval($options['rsspreviewwidth']); ?>"/></td>
			<td><?php _e('RSS Preview Height', 'link-library'); ?></td>
			<td><input type="text" id="rsspreviewheight" name="rsspreviewheight" size="5" value="<?php if ($options['rsspreviewheight'] == '') echo '700'; else echo strval($options['rsspreviewheight']); ?>"/></td>
			<td></td><td></td>
		</tr>
		</table>

	<?php }

	function settingssets_thumbnails_meta_box($data) {
		$options = $data['options'];
		$settings = $data['settings'];
	?>

		<table>
		<tr>
			<td style='width: 400px' class='lltooltip' title='<?php _e('Checking this option will get images from the thumbshots web site every time', 'link-library'); ?>.'>
				<?php _e('Use Thumbshots.org for dynamic link images', 'link-library'); ?>
			</td>
			<td colspan='2' class='lltooltip' title='<?php _e('Checking this option will get images from the thumbshots web site every time', 'link-library'); ?>.' style='width=75px;padding-right:20px'>
				<input type="checkbox" id="usethumbshotsforimages" name="usethumbshotsforimages" <?php if ($options['usethumbshotsforimages']) echo ' checked="checked" '; ?>/>
			</td>
		</tr>
		<tr>
			<td><?php _e('Generate Images / Favorite Icons', 'link-library'); ?></td>
			<td><INPUT type="button" name="genthumbs" value="<?php _e('Generate Thumbnails and Store locally', 'link-library'); ?>" onClick="window.location= 'admin.php?page=link-library-settingssets&amp;settings=<?php echo $settings; ?>&amp;genthumbs=<?php echo $settings; ?>'"></td>
			<td><INPUT type="button" name="genfavicons" value="<?php _e('Generate Favorite Icons and Store locally', 'link-library'); ?>" onClick="window.location= 'admin.php?page=link-library-settingssets&amp;settings=<?php echo $settings; ?>&amp;genfavicons=<?php echo $settings; ?>'"></td><td style='width=75px;padding-right:20px'></td>
		</tr>
		</table>
	<?php }

	function settingssets_rssgen_meta_box($data) {
		$options = $data['options'];
		$settings = $data['settings'];
	?>

		<table>
		<tr>
			<td>
				<?php _e('Publish RSS Feed', 'link-library'); ?>
			</td>
			<td style='width=75px;padding-right:20px'>
				<input type="checkbox" id="publishrssfeed" name="publishrssfeed" <?php if ($options['publishrssfeed']) echo ' checked="checked" '; ?>/>
			</td>
			<td><?php _e('Number of items in RSS feed', 'link-library'); ?></td><td style='width=75px;padding-right:20px'><input type="text" id="numberofrssitems" name="numberofrssitems" size="3" value="<?php if ($options['numberofrssitems'] == '') echo '10'; else echo strval($options['numberofrssitems']); ?>"/></td>
		</tr>	
		<tr>
			<td><?php _e('RSS Feed Title', 'link-library'); ?></td><td colspan=3><input type="text" id="rssfeedtitle" name="rssfeedtitle" size="80" value="<?php echo strval(wp_specialchars(stripslashes($options['rssfeedtitle']))); ?>"/></td>
		</tr>
		<tr>
			<td><?php _e('RSS Feed Description', 'link-library'); ?></td><td colspan=3><input type="text" id="rssfeeddescription" name="rssfeeddescription" size="80" value="<?php echo strval(wp_specialchars(stripslashes($options['rssfeeddescription']))); ?>"/></td>
		</tr>
		<tr>
			<td><?php _e('RSS Feed Web Address (default /linkrss/1)', 'link-library'); ?></td><td colspan=3><input type="text" id="rssfeedaddress" name="rssfeedaddress" size="80" value="<?php echo strval(wp_specialchars(stripslashes($options['rssfeedaddress']))); ?>"/></td>
		</tr>
		</table>

	<?php }

	function settingssets_search_meta_box($data) {
		$options = $data['options'];
		$settings = $data['settings'];
	?>

		<div>
		<table>
			<tr>
				<td style='width:200px'><?php _e('Search Label', 'link-library'); ?></td>
				<?php if ($options['searchlabel'] == "") $options['searchlabel'] =  __('Search', 'link-library'); ?>
				<td><input type="text" id="searchlabel" name="searchlabel" size="30" value="<?php echo $options['searchlabel']; ?>"/></td>
			</tr>
		</table>
		</div>

	<?php }

	function settingssets_linksubmission_meta_box($data) {
		$options = $data['options'];
		$settings = $data['settings'];
	?>
		<div>
		<table>
			<tr>
				<td colspan=5 class="lltooltip" title='<?php _e('Following this link shows a list of all links awaiting moderation', 'link-library'); ?>.'><a href="<?php echo WP_ADMIN_URL ?>/link-manager.php?s=LinkLibrary%3AAwaitingModeration%3ARemoveTextToApprove"><?php _e('View list of links awaiting moderation', 'link-library'); ?></a></td>
			</tr>
			<tr>
				<td style='width:200px'><?php _e('Show user links immediately', 'link-library'); ?></td>
				<td style='width:75px;padding-right:20px'><input type="checkbox" id="showuserlinks" name="showuserlinks" <?php if ($options['showuserlinks']) echo ' checked="checked" '; ?>/></td>
				<td style='width: 20px'></td>
				<td style='width: 20px'></td>
				<td style='width:250px'><?php _e('E-mail admin on link submission', 'link-library'); ?></td>
				<td style='width:75px;padding-right:20px'><input type="checkbox" id="emailnewlink" name="emailnewlink" <?php if ($options['emailnewlink']) echo ' checked="checked" '; ?>/></td>
				<td style='width: 20px'></td>
			</tr>
			<tr>
				<td style='width:200px'><?php _e('Require login to display form', 'link-library'); ?></td>
				<td style='width:75px;padding-right:20px'><input type="checkbox" id="addlinkreqlogin" name="addlinkreqlogin" <?php if ($options['addlinkreqlogin']) echo ' checked="checked" '; ?>/></td>
<td style='width: 20px'></td>
				<td style='width: 20px'></td>
				<td style='width:250px'><?php _e('Allow link submission with empty link', 'link-library'); ?></td>
				<td style='width:75px;padding-right:20px'><input type="checkbox" id="addlinknoaddress" name="addlinknoaddress" <?php if ($options['addlinknoaddress']) echo ' checked="checked" '; ?>/></td>
				<td style='width: 20px'></td>
			</tr>
			<tr>
				<td style='width:200px'><?php _e('Display captcha', 'link-library'); ?></td>
				<td style='width:75px;padding-right:20px'><input type="checkbox" id="showcaptcha" name="showcaptcha" <?php if ($options['showcaptcha']) echo ' checked="checked" '; ?>/></td>
<td style='width: 20px'></td>
				<td style='width: 20px'></td>
				<td class='lltooltip' title='<?php _e('This function will only store data when users are logged in to Wordpress', 'link-library'); ?>.' style='width:250px'><?php _e('Store login name on link submission', 'link-library'); ?></td>
				<td style='width:75px;padding-right:20px'><input type="checkbox" id="storelinksubmitter" name="storelinksubmitter" <?php if ($options['storelinksubmitter']) echo ' checked="checked" '; ?>/></td>
				<td style='width: 20px'></td>				
			</tr>
			<tr>
				<td style='width:200px'><?php _e('Add new link label', 'link-library'); ?></td>
				<?php if ($options['addnewlinkmsg'] == "") $options['addnewlinkmsg'] = __('Add new link', 'link-library'); ?>
				<td><input type="text" id="addnewlinkmsg" name="addnewlinkmsg" size="30" value="<?php echo $options['addnewlinkmsg']; ?>"/></td>
				<td style='width: 20px'></td>
				<td style='width: 20px'></td>
				<td style='width:200px'><?php _e('Link name label', 'link-library'); ?></td>
				<?php if ($options['linknamelabel'] == "") $options['linknamelabel'] = __('Link Name', 'link-library'); ?>
				<td><input type="text" id="linknamelabel" name="linknamelabel" size="30" value="<?php echo $options['linknamelabel']; ?>"/></td>
				<td style='width: 20px'></td>
			</tr>
			<tr>
				<td style='width:200px'><?php _e('Link address label', 'link-library'); ?></td>
				<?php if ($options['linkaddrlabel'] == "") $options['linkaddrlabel'] = __('Link Address', 'link-library'); ?>
				<td><input type="text" id="linkaddrlabel" name="linkaddrlabel" size="30" value="<?php echo $options['linkaddrlabel']; ?>"/></td>
				<td style='width: 20px'></td>
				<td style='width: 20px'></td>
				<td style='width:200px'><?php _e('Link RSS label', 'link-library'); ?></td>
				<?php if ($options['linkrsslabel'] == "") $options['linkrsslabel'] = __('Link RSS', 'link-library'); ?>
				<td><input type="text" id="linkrsslabel" name="linkrsslabel" size="30" value="<?php echo $options['linkrsslabel']; ?>"/></td>
				<td>
					<select name="showaddlinkrss" id="showaddlinkrss" style="width:60px;">
						<option value="false"<?php if ($options['showaddlinkrss'] == false) { echo ' selected="selected"';} ?>><?php _e('Hide', 'link-library'); ?></option>
						<option value="true"<?php if ($options['showaddlinkrss'] == true) { echo ' selected="selected"';} ?>><?php _e('Show', 'link-library'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td style='width:200px'><?php _e('Link category label', 'link-library'); ?></td>
				<?php if ($options['linkcatlabel'] == "") $options['linkcatlabel'] = __('Link Category', 'link-library'); ?>
				<td><input type="text" id="linkcatlabel" name="linkcatlabel" size="30" value="<?php echo $options['linkcatlabel']; ?>"/></td>
				<td>
					<select name="showaddlinkcat" id="showaddlinkcat" style="width:60px;">
						<option value="false"<?php if ($options['showaddlinkcat'] == false) { echo ' selected="selected"';} ?>><?php _e('Hide', 'link-library'); ?></option>
						<option value="true"<?php if ($options['showaddlinkcat'] == true) { echo ' selected="selected"';} ?>><?php _e('Show', 'link-library'); ?></option>
					</select>
				</td>
				<td style='width: 20px'></td>
				<td style='width:200px' class='lltooltip' title='<?php _e('Comma-seperated list of categories to be displayed in category selection box (e.g. 1,5,4)', 'link-library'); ?>'><?php _e('Link category selection list', 'link-library'); ?></td>
				<td colspan=3 class='lltooltip' title='<?php _e('Comma-seperated list of categories to be displayed in category selection box (e.g. 1,5,4)', 'link-library'); ?>'><input type="text" id="addlinkcatlistoverride" name="addlinkcatlistoverride" size="50" value="<?php echo $options['addlinkcatlistoverride']; ?>" />
				<td style='width:200px'></td>
			</tr>
			<tr>
				<td style='width:200px'><?php _e('User-submitted category', 'link-library'); ?></td>
				<?php if ($options['linkcustomcatlabel'] == "") $options['linkcustomcatlabel'] = __('User-submitted category', 'link-library'); ?>
				<td><input type="text" id="linkcustomcatlabel" name="linkcustomcatlabel" size="30" value="<?php echo $options['linkcustomcatlabel']; ?>"/></td>
				<td>
					<select name="addlinkcustomcat" id="addlinkcustomcat" style="width:60px;">
						<option value="false"<?php if ($options['addlinkcustomcat'] == false) { echo ' selected="selected"';} ?>><?php _e('No', 'link-library'); ?></option>
						<option value="true"<?php if ($options['addlinkcustomcat'] == true) { echo ' selected="selected"';} ?>><?php _e('Allow', 'link-library'); ?></option>
					</select>
				</td>			
				<td></td>				
				<td style='width:200px'><?php _e('User-submitted category prompt', 'link-library'); ?></td>
				<?php if ($options['linkcustomcatlistentry'] == "") $options['linkcustomcatlistentry'] = __('User-submitted category (define below)', 'link-library'); ?>
				<td colspan=3><input type="text" id="linkcustomcatlistentry" name="linkcustomcatlistentry" size="50" value="<?php echo $options['linkcustomcatlistentry']; ?>"/></td>
				<td style='width:200px'></td>
			</tr>
			<tr>
				<td style='width:200px'><?php _e('Link description label', 'link-library'); ?></td>
				<?php if ($options['linkdesclabel'] == "") $options['linkdesclabel'] = __('Link Description', 'link-library'); ?>
				<td><input type="text" id="linkdesclabel" name="linkdesclabel" size="30" value="<?php echo $options['linkdesclabel']; ?>"/></td>
				<td>
					<select name="showaddlinkdesc" id="showaddlinkdesc" style="width:60px;">
						<option value="false"<?php if ($options['showaddlinkdesc'] == false) { echo ' selected="selected"';} ?>><?php _e('Hide', 'link-library'); ?></option>
						<option value="true"<?php if ($options['showaddlinkdesc'] == true) { echo ' selected="selected"';} ?>><?php _e('Show', 'link-library'); ?></option>
					</select>
				</td>
				<td style='width: 20px'></td>
				<td style='width:200px'><?php _e('Link notes label', 'link-library'); ?></td>
				<?php if ($options['linknoteslabel'] == "") $options['linknoteslabel'] = __('Link Notes', 'link-library'); ?>
				<td><input type="text" id="linknoteslabel" name="linknoteslabel" size="30" value="<?php echo $options['linknoteslabel']; ?>"/></td>
				<td>
					<select name="showaddlinknotes" id="showaddlinknotes" style="width:60px;">
						<option value="false"<?php if ($options['showaddlinknotes'] == false) { echo ' selected="selected"';} ?>><?php _e('Hide', 'link-library'); ?></option>
						<option value="true"<?php if ($options['showaddlinknotes'] == true) { echo ' selected="selected"';} ?>><?php _e('Show', 'link-library'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td style='width:200px'><?php _e('Reciprocal Link label', 'link-library'); ?></td>
				<?php if ($options['linkreciprocallabel'] == "") $options['linkreciprocallabel'] = __('Reciprocal Link', 'link-library'); ?>
				<td><input type="text" id="linkreciprocallabel" name="linkreciprocallabel" size="30" value="<?php echo $options['linkreciprocallabel']; ?>"/></td>
				<td>
					<select name="showaddlinkreciprocal" id="showaddlinkreciprocal" style="width:60px;">
						<option value="false"<?php if ($options['showaddlinkreciprocal'] == false) { echo ' selected="selected"';} ?>><?php _e('Hide', 'link-library'); ?></option>
						<option value="true"<?php if ($options['showaddlinkreciprocal'] == true) { echo ' selected="selected"';} ?>><?php _e('Show', 'link-library'); ?></option>
					</select>
				</td>
				<td style='width: 20px'></td>
				<td style='width:200px'><?php _e('Secondary Address label', 'link-library'); ?></td>
				<?php if ($options['linksecondurllabel'] == "") $options['linksecondurllabel'] = __('Secondary Address', 'link-library'); ?>
				<td><input type="text" id="linksecondurllabel" name="linksecondurllabel" size="30" value="<?php echo $options['linksecondurllabel']; ?>"/></td>
				<td>
					<select name="showaddlinksecondurl" id="showaddlinksecondurl" style="width:60px;">
						<option value="false"<?php if ($options['showaddlinksecondurl'] == false) { echo ' selected="selected"';} ?>><?php _e('Hide', 'link-library'); ?></option>
						<option value="true"<?php if ($options['showaddlinksecondurl'] == true) { echo ' selected="selected"';} ?>><?php _e('Show', 'link-library'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td style='width:200px'><?php _e('Link Telephone label', 'link-library'); ?></td>
				<?php if ($options['linktelephonelabel'] == "") $options['linktelephonelabel'] = __('Telephone', 'link-library'); ?>
				<td><input type="text" id="linktelephonelabel" name="linktelephonelabel" size="30" value="<?php echo $options['linktelephonelabel']; ?>"/></td>
				<td>
					<select name="showaddlinktelephone" id="showaddlinktelephone" style="width:60px;">
						<option value="false"<?php if ($options['showaddlinktelephone'] == false) { echo ' selected="selected"';} ?>><?php _e('Hide', 'link-library'); ?></option>
						<option value="true"<?php if ($options['showaddlinktelephone'] == true) { echo ' selected="selected"';} ?>><?php _e('Show', 'link-library'); ?></option>
					</select>
				</td>
				<td style='width: 20px'></td>
				<td style='width:200px'><?php _e('Link E-mail label', 'link-library'); ?></td>
				<?php if ($options['linkemaillabel'] == "") $options['linkemaillabel'] = __('E-mail', 'link-library'); ?>
				<td><input type="text" id="linkemaillabel" name="linkemaillabel" size="30" value="<?php echo $options['linkemaillabel']; ?>"/></td>
				<td>
					<select name="showaddlinkemail" id="showaddlinkemail" style="width:60px;">
						<option value="false"<?php if ($options['showaddlinkemail'] == false) { echo ' selected="selected"';} ?>><?php _e('Hide', 'link-library'); ?></option>
						<option value="true"<?php if ($options['showaddlinkemail'] == true) { echo ' selected="selected"';} ?>><?php _e('Show', 'link-library'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td style='width:200px'><?php _e('Link Submitter Name label', 'link-library'); ?></td>
				<?php if ($options['linksubmitternamelabel'] == "") $options['linksubmitternamelabel'] = __('Submitter Name', 'link-library'); ?>
				<td><input type="text" id="linksubmitternamelabel" name="linksubmitternamelabel" size="30" value="<?php echo $options['linksubmitternamelabel']; ?>"/></td>
				<td>
					<select name="showlinksubmittername" id="showlinksubmittername" style="width:60px;">
						<option value="false"<?php if ($options['showlinksubmittername'] == false) { echo ' selected="selected"';} ?>><?php _e('Hide', 'link-library'); ?></option>
						<option value="true"<?php if ($options['showlinksubmittername'] == true) { echo ' selected="selected"';} ?>><?php _e('Show', 'link-library'); ?></option>
					</select>
				</td>
				<td style='width: 20px'></td>
				<td style='width:200px'><?php _e('Link Submitter E-mail label', 'link-library'); ?></td>
				<?php if ($options['linksubmitteremaillabel'] == "") $options['linksubmitteremaillabel'] = __('Submitter E-mail', 'link-library'); ?>
				<td><input type="text" id="linksubmitteremaillabel" name="linksubmitteremaillabel" size="30" value="<?php echo $options['linksubmitteremaillabel']; ?>"/></td>
				<td>
					<select name="showaddlinksubmitteremail" id="showaddlinksubmitteremail" style="width:60px;">
						<option value="false"<?php if ($options['showaddlinksubmitteremail'] == false) { echo ' selected="selected"';} ?>><?php _e('Hide', 'link-library'); ?></option>
						<option value="true"<?php if ($options['showaddlinksubmitteremail'] == true) { echo ' selected="selected"';} ?>><?php _e('Show', 'link-library'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td style='width:200px'><?php _e('Link Submitter Comment Label', 'link-library'); ?></td>
				<?php if ($options['linksubmittercommentlabel'] == "") $options['linksubmittercommentlabel'] = __('Submitter Comment', 'link-library'); ?>
				<td><input type="text" id="linksubmittercommentlabel" name="linksubmittercommentlabel" size="30" value="<?php echo $options['linksubmittercommentlabel']; ?>"/></td>
				<td>
					<select name="showlinksubmittercomment" id="showlinksubmittercomment" style="width:60px;">
						<option value="false"<?php if ($options['showlinksubmittercomment'] == false) { echo ' selected="selected"';} ?>><?php _e('Hide', 'link-library'); ?></option>
						<option value="true"<?php if ($options['showlinksubmittercomment'] == true) { echo ' selected="selected"';} ?>><?php _e('Show', 'link-library'); ?></option>
					</select>
				</td>
				<td style='width: 20px'></td>
				<td style='width:200px'><?php _e('Large Description Label', 'link-library'); ?></td>
				<?php if ($options['linklargedesclabel'] == "") $options['linklargedesclabel'] = __('Large Description', 'link-library'); ?>
				<td><input type="text" id="linklargedesclabel" name="linklargedesclabel" size="30" value="<?php echo $options['linklargedesclabel']; ?>"/></td>
				<td>
					<select name="showuserlargedescription" id="showuserlargedescription" style="width:60px;">
						<option value="false"<?php if ($options['showuserlargedescription'] == false) { echo ' selected="selected"';} ?>><?php _e('Hide', 'link-library'); ?></option>
						<option value="true"<?php if ($options['showuserlargedescription'] == true) { echo ' selected="selected"';} ?>><?php _e('Show', 'link-library'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td style='width:200px'><?php _e('Custom Captcha Question', 'link-library'); ?></td>
				<?php if ($options['customcaptchaquestion'] == "") $options['customcaptchaquestion'] = __('Is boiling water hot or cold?', 'link-library'); ?>
				<td><input type="text" id="customcaptchaquestion" name="customcaptchaquestion" size="30" value="<?php echo $options['customcaptchaquestion']; ?>"/></td>
				<td>
					<select name="showcustomcaptcha" id="showcustomcaptcha" style="width:60px;">
						<option value="false"<?php if ($options['showcustomcaptcha'] == false) { echo ' selected="selected"';} ?>><?php _e('Hide', 'link-library'); ?></option>
						<option value="true"<?php if ($options['showcustomcaptcha'] == true) { echo ' selected="selected"';} ?>><?php _e('Show', 'link-library'); ?></option>
					</select>
				</td>
				<td style='width: 20px'></td>
				<td style='width:200px'><?php _e('Custom Captcha Answer', 'link-library'); ?></td>
				<?php if ($options['customcaptchaanswer'] == "") $options['customcaptchaanswer'] = __('hot', 'link-library'); ?>
				<td><input type="text" id="customcaptchaanswer" name="customcaptchaanswer" size="30" value="<?php echo $options['customcaptchaanswer']; ?>"/></td>
			</tr>
			<tr>
				<td style='width:200px'><?php _e('Add Link button label', 'link-library'); ?></td>
				<?php if ($options['addlinkbtnlabel'] == "") $options['addlinkbtnlabel'] = __('Add Link', 'link-library'); ?>
				<td><input type="text" id="addlinkbtnlabel" name="addlinkbtnlabel" size="30" value="<?php echo $options['addlinkbtnlabel']; ?>"/></td>
				<td style='width: 20px'></td>
				<td style='width: 20px'></td>
				<td style='width:200px'><?php _e('New Link Message', 'link-library'); ?></td>
				<?php if ($options['newlinkmsg'] == "") $options['newlinkmsg'] = __('New link submitted', 'link-library'); ?>
				<td><input type="text" id="newlinkmsg" name="newlinkmsg" size="30" value="<?php echo $options['newlinkmsg']; ?>"/></td>
			</tr>
			<tr>
				<td style='width:200px'><?php _e('New Link Moderation Label', 'link-library'); ?></td>
				<?php if ($options['moderatemsg'] == "") $options['moderatemsg'] = __('it will appear in the list once moderated. Thank you.', 'link-library'); ?>
				<td colspan=6><input type="text" id="moderatemsg" name="moderatemsg" size="90" value="<?php echo $options['moderatemsg']; ?>"/></td>
			</tr>
		</table>
		</div>

	<?php }

	function settingssets_importexport_meta_box($data) {
		$options = $data['options'];
		$settings = $data['settings'];
		global $llpluginpath;
	?>

		<table>
			<tr>
				<td class='lltooltip' title='<?php _e('Allows for links to be added in batch to the Wordpress links database. CSV file needs to follow template for column layout.', 'link-library'); ?>' style='width: 330px'><?php _e('CSV file to upload to import links', 'link-library'); ?> (<a href="<?php echo $llpluginpath . 'importtemplate.csv'; ?>"><?php _e('file template', 'link-library'); ?></a>)</td>
				<td><input size="80" name="linksfile" type="file" /></td>
				<td><input type="submit" name="importlinks" value="<?php _e('Import Links', 'link-library'); ?>" /></td>
			</tr>
			<tr>
				<td><?php _e('First row contains column headers', 'link-library'); ?></td>
				<td><input type="checkbox" id="firstrowheaders" name="firstrowheaders" checked="checked" /></td>
			</tr>
		</table>

		<hr style='color: #CCC; ' />
		<input type='hidden' value='<?php echo $settings; ?>' name='settingsetid' id='settingsetid' />
		<table>
			<tr>
				<td class='lltooltip' title='<?php _e('Overwrites current library settings with contents of CSV file', 'link-library'); ?>' style='width: 330px'><?php _e('Library Settings CSV file to import', 'link-library'); ?></td>
				<td><input size="80" name="settingsfile" type="file" /></td>
				<td><input type="submit" name="importsettings" value="<?php _e('Import Library Settings', 'link-library'); ?>" /></td>
			</tr>
			<tr>
				<td class='lltooltip' style='width: 330px' title='<?php _e('Generates CSV file with current library configuration for download', 'link-library'); ?>'><?php _e('Export current library settings', 'link-library'); ?></td>
				<td><input type="submit" name="exportsettings" value="<?php _e('Export Library Settings', 'link-library'); ?>" /></td>
			</tr>
		</table>

	<?php }
	
	function reciprocal_meta_box($data) {
		$genoptions = $data['genoptions'];
	?>
		<table>
			<tr>
				<td style='width: 250px'>Search string</td>
				<td><input type="text" id="recipcheckaddress" name="recipcheckaddress" size="60" value="<?php echo $genoptions['recipcheckaddress']; ?>"/></td>
				<td><input type='submit' id="recipcheck" name="recipcheck" value="Check Reciprocal Links" /></td>
			</tr>
		</table>
		
	<?php
	}


	/************************************************ Render Custom Meta Box in Link Editor *******************************************/
	function ll_link_edit_extra($link) {
		global $wpdb;

		$genoptions = get_option('LinkLibraryGeneral');

		if (floatval($genoptions['schemaversion']) < "4.6")
		{
			$this->ll_install();
		}

		if ($link->link_id != '')
		{
			$linkextradataquery = "select * from " . $this->db_prefix() . "links_extrainfo where link_id = " . $link->link_id;
			$extradata = $wpdb->get_row($linkextradataquery, ARRAY_A);
			
			if ($extradata['link_visits'] == '') $extradata['link_visits'] = 0;
			
			$originallinkdata = "select * from " . $this->db_prefix() . "links where link_id = " . $link->link_id;
			$originaldata = $wpdb->get_row($originallinkdata, ARRAY_A);
		}
		else
		{
			$extradata = array();
			$originaldata = array();
		}
	?>
		<table>
			<tr>
				<td style='width: 200px'><?php _e('Featured Link', 'link-library'); ?></td>
				<td><input type="checkbox" id="link_featured" name="link_featured" <?php if ($extradata['link_featured']) echo ' checked="checked" '; ?>/></td>
			</tr>
			<tr>
				<td style='width: 200px'><?php _e('No Follow', 'link-library'); ?></td>
				<td><input type="checkbox" id="link_no_follow" name="link_no_follow" <?php if ($extradata['link_no_follow']) echo ' checked="checked" '; ?>/></td>
			</tr>		
			<tr>
				<td style='width: 200px'><?php _e('Secondary Web Address', 'link-library'); ?></td>
				<td><input type="text" id="ll_secondwebaddr" name="ll_secondwebaddr" size="80" value="<?php echo $extradata['link_second_url']; ?>"/> <?php if ($extradata['link_second_url'] != "") echo " <a href=" . $extradata['link_second_url'] . ">" . __('Visit', 'link-library') . "</a>"; ?></td></td>
			</tr>
			<tr>
				<td><?php _e('Telephone', 'link-library'); ?></td>
				<td><input type="text" id="ll_telephone" name="ll_telephone" size="80" value="<?php echo $extradata['link_telephone']; ?>"/></td>
			</tr>
			<tr>
				<td><?php _e('E-mail', 'link-library'); ?></td>
				<td><input type="text" id="ll_email" name="ll_email" size="80" value="<?php echo $extradata['link_email']; ?>"/></td>
			</tr>
			<tr>
				<td><?php _e('Reciprocal Link', 'link-library'); ?></td>
				<td><input type="text" id="ll_reciprocal" name="ll_reciprocal" size="80" value="<?php echo $extradata['link_reciprocal']; ?>"/> <?php if ($extradata['link_reciprocal'] != "") echo " <a href=" . $extradata['link_reciprocal'] . ">" . __('Visit', 'link-library') . "</a>"; ?></td>
			</tr>	
			<tr>
				<td><?php _e('Number of link views', 'link-library'); ?></td>
				<td><input disabled type="text" id="ll_hits" name="ll_hits" size="80" value="<?php echo $extradata['link_visits']; ?>"/></td>
			</tr>
			<tr>
				<td><?php _e('Link Submitter', 'link-library'); ?></td>
				<td><input disabled type="text" id="ll_submitter" name="ll_submitter" size="80" value="<?php echo $extradata['link_submitter']; ?>"/></td>			
			</tr>
			<tr>
				<td><?php _e('Link Submitter Name', 'link-library'); ?></td>
				<td><input disabled type="text" id="link_submitter_name" name="link_submitter_name" size="80" value="<?php echo $extradata['link_submitter_name']; ?>"/>			</td>			
			</tr>
			<tr>
				<td><?php _e('Link Submitter E-mail', 'link-library'); ?></td>
				<td><input disabled type="text" id="link_submitter_email" name="link_submitter_email" size="80" value="<?php echo $extradata['link_submitter_email']; ?>"/>			   </td>			
			</tr>
			<tr>
				<td><?php _e('Link Large Description', 'link-library'); ?></td>
				<td><textarea id="link_textfield" name="link_textfield" cols='66'><?php echo $extradata['link_textfield']; ?></textarea></td>			
			</tr>
			<tr>
				<td><?php _e('Current Link Image', 'link-library'); ?></td>
				<td>
					<div id='current_link_image'>
					<?php if ($originaldata['link_image'] != ''): ?>
						<img src="<?php echo $originaldata['link_image'] ?>" />
					<?php else: ?>
						<?php _e('None Assigned', 'link-library'); ?>
					<?php endif; ?>
					</div>
				</td>
			</tr>
			<?php if ($link->link_id != ''): ?>
			<tr>
				<td><?php _e('Automatic Image Generation', 'link-library'); ?></td>
				<td><INPUT type="button" id="genthumbs" name="genthumbs" value="<?php _e('Generate Thumbnail and Store locally', 'link-library'); ?>">
					<INPUT type="button" id="genfavicons" name="genfavicons" value="<?php _e('Generate Favorite Icon and Store locally', 'link-library'); ?>"></td>
			</tr>
			<?php else: ?>
			<tr>
				<td><?php _e('Automatic Image Generation', 'link-library'); ?></td>
				<td><?php _e('Only available once link is saved', 'link-library'); ?></td>
			</tr>
			<?php endif; ?>
			<tr>
				<td><?php _e('Manual Image Upload', 'link-library'); ?></td>
				<td><input size="80" name="linkimageupload" type="file" /></td>
			</tr>
			<tr>
				<td colspan='2'><p><?php _e('Manual upload requires a wp-content\uploads directory to be present with write permissions', 'link-library'); ?>.</p></td>
			</tr>
		</table>

	<?php $genoptions = get_option('LinkLibraryGeneral'); ?>

		<script type="text/javascript">
			jQuery(document).ready(function()
			{
			    // Using jQuery, set both the enctype and the encoding
				// attributes to be multipart/form-data.
				jQuery( "form#editlink" )
					.attr( "enctype", "multipart/form-data" )
					.attr( "encoding", "multipart/form-data" )
					;
				jQuery('#genthumbs').click(function()
				{
					var linkname = jQuery('#link_name').val();
					var linkurl = jQuery('#link_url').val();
					
					if (linkname != '' && linkurl != '')
					{
						jQuery('#current_link_image').fadeOut('fast');
						var map = { name: linkname, url: linkurl, mode: 'thumbonly', cid: '<?php echo $genoptions['thumbshotscid']; ?>', filepath: 'link-library-images', linkid: <?php echo $link->link_id; ?> };
						jQuery.get('<?php echo WP_PLUGIN_URL; ?>/link-library/link-library-image-generator.php', map, 
							function(data){
								if (data != '')
								{
									jQuery('#current_link_image').replaceWith("<div id='current_link_image'><img src='" + data + "' /></div>");
									jQuery('#current_link_image').fadeIn('fast');
									jQuery('#link_image').val(data);
									alert('<?php _e('Thumbnail successfully generated for', 'link-library'); ?> ' + linkname);
								}
							});
					}
					else
					{
						alert("<?php _e('Cannot generate thumbnail when no name and no web address are specified.', 'link-library'); ?>");
					}
				} );
				
				jQuery('#genfavicons').click(function()
				{
					var linkname = jQuery('#link_name').val();
					var linkurl = jQuery('#link_url').val();
					
					if (linkname != '' && linkurl != '')
					{
						jQuery('#current_link_image').fadeOut('fast');
						var map = { name: linkname, url: linkurl, mode: 'favicononly', cid: '<?php echo $genoptions['thumbshotscid']; ?>', filepath: 'link-library-favicons', linkid: <?php echo $link->link_id; ?> };
						jQuery.get('<?php echo WP_PLUGIN_URL; ?>/link-library/link-library-image-generator.php', map, 
							function(data){
								if (data != '')
								{
									jQuery('#current_link_image').replaceWith("<div id='current_link_image'><img src='" + data + "' /></div>");
									jQuery('#current_link_image').fadeIn('fast');
									jQuery('#link_image').val(data);
									alert('<?php _e('Favicon successfully generated for', 'link-library') ?> ' + linkname);
								}
							});
					}
					else
					{
						alert("<?php _e('Cannot generate favorite icon when no name and no web address are specified.', 'link-library'); ?>");
					}
				} );

			});
	</script>
		
	<?php
	}
	
	/******************************* Store extra field data when link is saved *******************************************/
	function add_link_field($link_id) {
		global $wpdb;
		
		$uploads = wp_upload_dir();
		
		if(array_key_exists('linkimageupload', $_FILES))
		{
			if (!file_exists($uploads['basedir'] . '/link-library-images'))
				mkdir($uploads['basedir'] . '/link-library-images');
			$target_path = $uploads['basedir'] . "/link-library-images/" . $link_id . ".jpg";
			$file_path = $uploads['baseurl'] . "/uploads/link-library-images/" . $link_id . ".jpg";
			if (move_uploaded_file($_FILES['linkimageupload']['tmp_name'], $target_path))
				$withimage = true;
			else
				$withimage = false;
		}
		else
			$withimage = false;
			
		$tablename = $this->db_prefix() . "links";

		if ($withimage == true)
			$wpdb->update( $tablename, array( 'link_updated' => date("Y-m-d H:i"), 'link_image' => $file_path ), array( 'link_id' => $link_id ));
		else
			$wpdb->update( $tablename, array( 'link_updated' => date("Y-m-d H:i") ), array( 'link_id' => $link_id ));
				
		$extradatatable = $this->db_prefix() . "links_extrainfo";
		
		$linkextradataquery = "select * from " . $this->db_prefix() . "links_extrainfo where link_id = " . $link_id;
		$extradata = $wpdb->get_row($linkextradataquery, ARRAY_A);
		
		global $current_user;

		get_currentuserinfo();

		$username = $current_user->user_login;
		
		$updatearray = array();
		
		if (isset($_POST['ll_secondwebaddr']))
			$updatearray['link_second_url'] = $_POST['ll_secondwebaddr'];
			
		if (isset($_POST['ll_telephone']))
			$updatearray['link_telephone'] = $_POST['ll_telephone'];
			
		if (isset($_POST['ll_email']))
			$updatearray['link_email'] = $_POST['ll_email'];
			
		if (isset($_POST['ll_reciprocal']))
			$updatearray['link_reciprocal'] = $_POST['ll_reciprocal'];
		
		if (isset($_POST['link_textfield']))
			$updatearray['link_textfield'] = $_POST['link_textfield'];
			
		if (isset($_POST['link_no_follow']))
			$updatearray['link_no_follow'] = $_POST['link_no_follow'];
			
		if (isset($_POST['link_featured']))
			$updatearray['link_featured'] = $_POST['link_featured'];

		if ($extradata)
			$wpdb->update( $extradatatable, $updatearray, array( 'link_id' => $link_id ));
		else
		{
			$updatearray['link_id'] = $link_id;
			$updatearray['link_submitter'] = $username;
			$wpdb->insert( $extradatatable, $updatearray );
		}
	}

	/************************************************ Delete extra field data when link is deleted ***********************************/
	function delete_link_field($link_id) {
		global $wpdb;
		
		$deletequery = "delete from " . $this->db_prefix() . "links_extrainfo where link_id = " . $link_id;
		$wpdb->get_results($deletequery);
	}
	
	/******************************************** Print style data to header *********************************************/

	function ll_rss_link() {
		global $llstylesheet, $rss_settings, $llpluginpath;
		
		if ($rss_settings != "")
		{
			$settingsname = 'LinkLibraryPP' . $rss_settings;
			$options = get_option($settingsname);

			$feedtitle = ($options['rssfeedtitle'] == "" ? __('Link Library Generated Feed', 'link-library') : $options['rssfeedtitle']);

			echo '<link rel="alternate" type="application/rss+xml" title="' . wp_specialchars(stripslashes($feedtitle)) . '" href="' . $llpluginpath . 'rssfeed.php?settingset=' . $rss_settings . '" />';
		}

		if ($llstylesheet == true)
		{
			$genoptions = get_option('LinkLibraryGeneral');
			
			echo "<style id='LinkLibraryStyle' type='text/css'>\n";
			echo stripslashes($genoptions['fullstylesheet']);
			echo "</style>\n";
		}
	}

	/****************************************** Add Link Category name to page title when option is present ********************************/
	function ll_title_creator($title) {
		global $wp_query;
		global $wpdb;
		
		$genoptions = get_option('LinkLibraryGeneral');
		
		$categoryname = $wp_query->query_vars['cat_name'];
		$catid = $_GET['cat_id'];
		
		$linkcatquery = "SELECT t.name ";
		$linkcatquery .= "FROM " . $this->db_prefix() . "terms t LEFT JOIN " . $this->db_prefix(). "term_taxonomy tt ON (t.term_id = tt.term_id) ";
		$linkcatquery .= "LEFT JOIN " . $this->db_prefix() . "term_relationships tr ON (tt.term_taxonomy_id = tr.term_taxonomy_id) ";
		$linkcatquery .= "WHERE tt.taxonomy = 'link_category' AND ";
		
		if ($categoryname != '')
		{
			$linkcatquery .= "t.slug = '" . $categoryname . "'";
			$nicecatname = $wpdb->get_var($linkcatquery);
			return $title . $genoptions['pagetitleprefix'] . $nicecatname . $genoptions['pagetitlesuffix'];
		}
		elseif ($catid != '')
		{
			$linkcatquery .= "t.term_id = '" . $catid . "'";
			//echo $linkcatquery;
			$nicecatname = $wpdb->get_var($linkcatquery);
			return $title . $genoptions['pagetitleprefix'] . $nicecatname . $genoptions['pagetitlesuffix'];
		}

		return $title;
	}

	/***************************************** Add column to link list view ****************************************/
	function ll_linkmanager_addcolumn($columns) {
		$columns['hits'] = 'Hits';
		return $columns;
	}

	function ll_linkmanager_populatecolumn($arg1, $arg2) {
		global $wpdb;
		
		switch ($arg1) {
			case 'hits':
				$linkextradataquery = "select * from " . $this->db_prefix() . "links_extrainfo where link_id = " . $arg2;
				$extradata = $wpdb->get_row($linkextradataquery, ARRAY_A);
				$hits = $extradata['link_visits'];
				if ($hits == '') $hits = 0;
				echo $hits;
		}
	}

	/************************************* Function to add to rewrite rules for permalink support **********************************/
	function ll_insertMyRewriteRules($rules)
	{
		$newrules = array();

		$genoptions = get_option('LinkLibraryGeneral');

		if ($genoptions != '')
		{
			for ($i = 1; $i <= $genoptions['numberstylesets']; $i++) {
				$settingsname = 'LinkLibraryPP' . $i;
				$options = get_option($settingsname);
				
				if ($options['enablerewrite'] == true && $options['rewritepage'] != '')
					$newrules['(' . $options['rewritepage'] . ')/(.+?)$'] = 'index.php?pagename=$matches[1]&cat_name=$matches[2]';
					
				if ($options['publishrssfeed'] == true)
				{
					if ($options['rssfeedaddress'] != '')
						$newrules['(' . $options['rssfeedaddress'] . ')/(.+?)$'] = '/wp-content/plugins/link-library/rssfeed.php?settingset=$matches[1]';
					elseif ($options['rssfeedaddress'] == '')
						$newrules['(linkrss)/(.+?)$'] = '/wp-content/plugins/link-library/rssfeed.php?settingset=$matches[1]';
				}		
			}
		}
		
		return $newrules + $rules;
	}

	// Adding the id var so that WP recognizes it
	function ll_insertMyRewriteQueryVars($vars)
	{
		array_push($vars, 'cat_name');
		return $vars;
	}

	/*********************************************** Private Link Library Categories Function *************************************/

	function PrivateLinkLibraryCategories($order = 'name', $hide_if_empty = true, $table_width = 100, $num_columns = 1, $catanchor = true, 
				   $flatlist = false, $categorylist = '', $excludecategorylist = '', $showcategorydescheaders = false, 
				   $showonecatonly = false, $settings = '', $loadingicon = '/icons/Ajax-loader.gif', $catlistdescpos = 'right',
				   $debugmode = false, $pagination = false, $linksperpage = 5, $showcatlinkcount = false, $showonecatmode = 'AJAX',
				   $cattargetaddress = '', $rewritepage = '', $showinvisible = false) {

		global $wpdb;

		$output = '';

		if (!isset($_GET['searchll']))
		{
			$countcat = 0;

			$order = strtolower($order);

			$output .= "<!-- Link Library Categories Output -->\n\n";

			if ($showonecatonly == true && ($showonecatmode == 'AJAX' || $showonecatmode == ''))
			{
				$output .= "<SCRIPT LANGUAGE=\"JavaScript\">\n";
				$output .= "var ajaxobject;\n";
				$output .= "function showLinkCat ( _incomingID, _settingsID, _pagenumber) {\n";
				$output .= "if (typeof(ajaxobject) != \"undefined\") { ajaxobject.abort(); }\n";            
				$output .= "var map = {id : _incomingID, settings : _settingsID, linkresultpage: _pagenumber}\n";
				$output .= "\tjQuery('#contentLoading').toggle();jQuery.get('" . WP_PLUGIN_URL . "/link-library/link-library-ajax.php', map, function(data){jQuery('#linklist" . $settings. "').replaceWith(data);jQuery('#contentLoading').toggle();});\n";
				$output .= "}\n";

				$output .= "</SCRIPT>\n\n";
			}

			// Handle link category sorting
			$direction = 'ASC';
			if (substr($order,0,1) == '_') {
				$direction = 'DESC';
				$order = substr($order,1);
			}

			if (!isset($direction)) $direction = '';
			// Fetch the link category data as an array of hashesa
			
			$linkcatquery = "SELECT count(l.link_name) as linkcount, t.name, t.term_id, t.slug as category_nicename, tt.description as category_description ";
			$linkcatquery .= "FROM " . $this->db_prefix() . "terms t LEFT JOIN " . $this->db_prefix(). "term_taxonomy tt ON (t.term_id = tt.term_id)";

			$linkcatquery .= " LEFT JOIN " . $this->db_prefix() . "term_relationships tr ON (tt.term_taxonomy_id = tr.term_taxonomy_id) LEFT JOIN " . $this->db_prefix() . "links l on (tr.object_id = l.link_id) ";

			$linkcatquery .= "WHERE tt.taxonomy = 'link_category'";

			if ($hide_if_empty)
			{
				if (!$showuserlinks)
					$linkcatquery .= " AND l.link_description not like '%LinkLibrary:AwaitingModeration:RemoveTextToApprove%' ";
			}

			if ($showinvisible == false)
				$linkcatquery .= " AND l.link_visible != 'N'";
				
			if ($categorylist != "")
				$linkcatquery .= " AND t.term_id in (" . $categorylist. ")";

			if ($excludecategorylist != "")
				$linkcatquery .= " AND t.term_id not in (" . $excludecategorylist . ")";

			$linkcatquery .= " GROUP BY t.name ";

			if ($order == "name")
				$linkcatquery .= " ORDER by t.name " . $direction;
			elseif ($order == "id")
				$linkcatquery .= " ORDER by t.term_id " . $direction;
			elseif ($order == "order")
				$linkcatquery .= " ORDER by t.term_order " . $direction;
			elseif ($order == "catlist")
				$linkcatquery .= " ORDER by FIELD(t.term_id," . $categorylist . ") ";

			$catnames = $wpdb->get_results($linkcatquery);
			
			if ($debugmode)
			{
				$output .= "\n<!-- Category Query: " . print_r($linkcatquery, TRUE) . "-->\n\n";
				$output .= "\n<!-- Category Results: " . print_r($catnames, TRUE) . "-->\n\n";
			}

			// Display each category

			if ($catnames) {

				$output .=  "<div id=\"linktable\" class=\"linktable\">";

				if (!$flatlist)
					$output .= "<table width=\"" . $table_width . "%\">\n";
				else
					$output .= "<ul class='menu'>\n";

				$linkcount = 0;

				foreach ( (array) $catnames as $catname) {
					// Handle each category.
					// First, fix the sort_order info
					//$orderby = $cat['sort_order'];
					//$orderby = (bool_from_yn($cat['sort_desc'])?'_':'') . $orderby;

					// Display the category name
					$countcat += 1;
					if (!$flatlist and (($countcat % $num_columns == 1) or ($num_columns == 1) )) $output .= "<tr>\n";

					if (!$flatlist)
						$catfront = '	<td>';
					else
						$catfront = '	<li>';

					if ($showonecatonly)
					{
						if ($showonecatmode == 'AJAX' || $showonecatmode == '')
							$cattext = "<a href='#' onClick=\"showLinkCat('" . $catname->term_id. "', '" . $settings . "', 1);return false;\" >";
						elseif ($showonecatmode == 'HTMLGET')
						{
							$cattext = "<a href='";

							if ($cattargetaddress != '' && strpos($cattargetaddress, "?") != false)
							{
								$cattext .= $cattargetaddress;
								$cattext .= "&cat_id=";
							}
							elseif ($cattargetaddress != '' && strpos($cattargetaddress, "?") == false)
							{
								$cattext .= $cattargetaddress;
								$cattext .= "?cat_id=";
							}
							elseif ($cattargetaddress == '')
								$cattext .= "?cat_id=";

							$cattext .= $catname->term_id . "'>";
						}
						elseif ($showonecatmode == 'HTMLGETPERM')
						{
							$cattext = "<a href='/" . $rewritepage . "/" . $catname->category_nicename . "'>";
						}
					}
					else if ($catanchor)
					{
						if (!$pagination)
							$cattext = '<a href="#' . $catname->category_nicename . '">';
						elseif ($pagination)
						{
							if ($linksperpage == 0 && $linksperpage == '')
								$linksperpage = 5;

							$pageposition = $linkcount / $linksperpage;
							$pageposition = ceil($pageposition);
							if ($pageposition == 0 && !isset($_GET['linkresultpage']))
								$cattext = '<a href="' . get_permalink() . '#' . $catname->category_nicename . '">';
							else
								$cattext = '<a href="?linkresultpage=' . ($pageposition == 0 ? 1 : $pageposition) . '#' . $catname->category_nicename . '">';

							$linkcount = $linkcount + $catname->linkcount;
						}
					}
					else
						$cattext = '';

					$catitem = '';
					if ($catlistdescpos == 'right' || $catlistdescpos == '')
					{
						$catitem .= $catname->name;
						if ($showcatlinkcount)
							$catitem .= " (" . $catname->linkcount . ")";
					}

					if ($showcategorydescheaders)
					{
						$catname->category_description = wp_specialchars($catname->category_description);
						$catname->category_description = str_replace("[", "<", $catname->category_description);
						$catname->category_description = str_replace("]", ">", $catname->category_description);
						$catname->category_description = str_replace("&quot;", "\"", $catname->category_description);
						$catitem .= $catname->category_description;
					}

					if ($catlistdescpos == 'left')
					{
						$catitem .= $catname->name;
						if ($showcatlinkcount)
							$catitem .= " (" . $catname->linkcount . ")";
					}

					if ($catanchor || $showonecatonly)
						$catitem .= "</a>";

					$output .= ($catfront . $cattext . $catitem );

					if (!$flatlist)
						$catterminator = "	</td>\n";
					else
					{
						$catterminator = "	</li>\n";
					}

					$output .= ($catterminator);

					if (!$flatlist and ($countcat % $num_columns == 0)) $output .= "</tr>\n";
				}

				if (!$flatlist and ($countcat % $num_columns == 3)) $output .= "</tr>\n";
				if (!$flatlist && $catnames)
					$output .= "</table>\n";
				else if ($catnames)
					$output .= "</ul>\n";
					
				$output .= "</div>\n";

				if ($showonecatonly && ($showonecatmode == 'AJAX' || $showonecatmode == ''))
				{
					if ($loadingicon == '') $loadingicon = '/icons/Ajax-loader.gif';
					$output .= "<div class='contentLoading' id='contentLoading' style='display: none;'><img src='" . WP_PLUGIN_URL . "/link-library" . $loadingicon . "' alt='Loading data, please wait...'></div>\n";
				}
			}
			else
			{
				$output .= "<div>" . __('No categories found', 'link-library') . ".</div>";
			}

			$output .= "\n<!-- End of Link Library Categories Output -->\n\n";
		}
		return $output;
	}

	function ll_highlight_phrase($str, $phrase, $tag_open = '<strong>', $tag_close = '</strong>')
	{
		if ($str == '')
		{
			return '';
		}

		if ($phrase != '')
		{
			return preg_replace('/('.preg_quote($phrase, '/').'(?![^<]*>))/i', $tag_open."\\1".$tag_close, $str);
		}

		return $str;
	}

	function PrivateLinkLibrary($order = 'name', $hide_if_empty = true, $catanchor = true,
									$showdescription = false, $shownotes = false, $showrating = false,
									$showupdated = false, $categorylist = '', $show_images = false, 
									$show_image_and_name = false, $use_html_tags = false, 
									$show_rss = false, $beforenote = '<br />', $nofollow = false, $excludecategorylist = '',
									$afternote = '', $beforeitem = '<li>', $afteritem = '</li>', $beforedesc = '', $afterdesc = '',
									$displayastable = false, $beforelink = '', $afterlink = '', $showcolumnheaders = false, 
									$linkheader = '', $descheader = '', $notesheader = '', $catlistwrappers = 1, $beforecatlist1 = '', 
									$beforecatlist2 = '', $beforecatlist3 = '', $divorheader = false, $catnameoutput = 'linklistcatname',
									$show_rss_icon = false, $linkaddfrequency = 0, $addbeforelink = '', $addafterlink = '', $linktarget = '',
									$showcategorydesclinks = false, $showadmineditlinks = true, $showonecatonly = false, $AJAXcatid = '',
									$defaultsinglecat = '', $rsspreview = false, $rsspreviewcount = 3, $rssfeedinline = false,
									$rssfeedinlinecontent = false, $rssfeedinlinecount = 1, $beforerss = '', $afterrss = '',
									$rsscachedir = '', $direction = 'ASC', $linkdirection = 'ASC', $linkorder = 'name',
									$pagination = false, $linksperpage = 5, $hidecategorynames = false, $settings = '',
									$showinvisible = false, $showdate = false, $beforedate = '', $afterdate = '', $catdescpos = 'right',
									$showuserlinks = false, $rsspreviewwidth = 900, $rsspreviewheight = 700, $beforeimage = '', $afterimage = '',
									$imagepos = 'beforename', $imageclass = '', $AJAXpageid = 1, $debugmode = false, $usethumbshotsforimages = false,
									$showonecatmode = 'AJAX', $dragndroporder = '1,2,3,4,5,6,7,8,9,10', $showname = true, $displayweblink = 'false',
									$sourceweblink = 'primary', $showtelephone = 'false', $sourcetelephone = 'primary', $showemail = 'false', $showlinkhits = false,
									$beforeweblink = '', $afterweblink = '', $weblinklabel = '', $beforetelephone = '', $aftertelephone = '', $telephonelabel = '',
									$beforeemail = '', $afteremail = '', $emaillabel = '', $beforelinkhits = '', $afterlinkhits = '', $emailcommand = '',
									$sourceimage = '', $sourcename = '', $thumbshotscid = '', $maxlinks = '', $beforelinkrating = '', $afterlinkrating = '',
									$showlargedescription = false, $beforelargedescription = '', $afterlargedescription = '', $featuredfirst = false) {

		global $wpdb;
		
		$output = "\n<!-- Beginning of Link Library Output -->\n\n";

		$currentcategory = 1;

		if ($showonecatonly && $showonecatmode == 'AJAX' && $AJAXcatid != '' && $_GET['searchll'] == "")
		{
			$categorylist = $AJAXcatid;
		}
		elseif ($showonecatonly && $showonecatmode == 'HTMLGET' && isset($_GET['cat_id']) && $_GET['searchll'] == "")
		{
			$categorylist = $_GET['cat_id'];
			$AJAXcatid = $categorylist;
		}
		elseif ($showonecatonly && $showonecatmode == 'HTMLGETPERM' && $_GET['searchll'] == "")
		{
			global $wp_query;

			$categoryname = $wp_query->query_vars['cat_name'];
			$AJAXcatid = $categoryname;
		}
		elseif ($showonecatonly && $AJAXcatid == '' && $defaultsinglecat != '' && $_GET['searchll'] == "")
		{
			$categorylist = $defaultsinglecat;
			$AJAXcatid = $categorylist;
		}
		elseif ($showonecatonly && $AJAXcatid == '' && $defaultsinglecat == '' && $_GET['searchll'] == "")
		{
			$catquery = "SELECT distinct t.name, t.term_id ";
			$catquery .= "FROM " . $this->db_prefix() . "terms t ";
			$catquery .= "LEFT JOIN " . $this->db_prefix() . "term_taxonomy tt ON (t.term_id = tt.term_id) ";
			$catquery .= "LEFT JOIN " . $this->db_prefix() . "term_relationships tr ON (tt.term_taxonomy_id = tr.term_taxonomy_id) ";
			$catquery .= "LEFT JOIN " . $this->db_prefix() . "links l ON (tr.object_id = l.link_id) ";
			$catquery .= "WHERE tt.taxonomy = 'link_category' ";

			if ($hide_if_empty)
				$catquery .= "AND l.link_id is not NULL AND l.link_description not like '%LinkLibrary:AwaitingModeration:RemoveTextToApprove%' ";

			if ($categorylist != "")
				$catquery .= " AND t.term_id in (" . $categorylist. ")";

			if ($excludecategorylist != "")
				$catquery .= " AND t.term_id not in (" . $excludecategorylist . ")";

			if ($showinvisible == false)
				$catquery .= " AND l.link_visible != 'N'";

			$mode = "normal";

			$catquery .= " ORDER by ";
			
			if ($featuredfirst == true)
				$catquery .= "link_featured DESC, ";

			if ($order == "name")
				$catquery .= " name " . $direction;
			elseif ($order == "id")
				$catquery .= " t.term_id " . $direction;
			elseif ($order == "order")
				$catquery .= " t.term_order " . $direction;
			elseif ($order == "catlist")
				$catquery .= " FIELD(t.term_id," . $categorylist . ") ";

			if ($linkorder == "name")
				$catquery .= ", link_name " . $linkdirection;
			elseif ($linkorder == "id")
				$catquery .= ", link_id " . $linkdirection;
			elseif ($linkorder == "order")
				$catquery .= ", link_order ". $linkdirection;
				
			$catitems = $wpdb->get_results($catquery);

			if ($debugmode)
			{
				$output .= "\n<!-- AJAX Default Category Query: " . print_r($catquery, TRUE) . "-->\n\n";
				$output .= "\n<!-- AJAX Default Category Results: " . print_r($catitems, TRUE) . "-->\n\n";
			}

			if ($catitems)
			{
				$categorylist = $catitems[0]->term_id;
				$AJAXcatid = $categorylist;
			}
		}
		
		$linkquery = "SELECT distinct *, l.link_id as proper_link_id, UNIX_TIMESTAMP(l.link_updated) as link_date, ";
		$linkquery .= "IF (DATE_ADD(l.link_updated, INTERVAL " . get_option('links_recently_updated_time') . " MINUTE) >= NOW(), 1,0) as recently_updated ";
		$linkquery .= "FROM " . $this->db_prefix() . "terms t ";
		$linkquery .= "LEFT JOIN " . $this->db_prefix() . "term_taxonomy tt ON (t.term_id = tt.term_id) ";
		$linkquery .= "LEFT JOIN " . $this->db_prefix() . "term_relationships tr ON (tt.term_taxonomy_id = tr.term_taxonomy_id) ";
		$linkquery .= "LEFT JOIN " . $this->db_prefix() . "links l ON (tr.object_id = l.link_id) ";
		$linkquery .= "LEFT JOIN " . $this->db_prefix() . "links_extrainfo le ON (l.link_id = le.link_id) ";	
		$linkquery .= "WHERE tt.taxonomy = 'link_category' ";

		if ($hide_if_empty)
			$linkquery .= "AND l.link_id is not NULL AND l.link_description not like '%LinkLibrary:AwaitingModeration:RemoveTextToApprove%' ";

		if ($categorylist != "" || isset($_GET['cat_id']))
			$linkquery .= " AND t.term_id in (" . $categorylist. ")";
		
		if ($categoryname != "" && $showonecatmode == 'HTMLGETPERM')
			$linkquery .= " AND t.slug = '" . $categoryname. "'";

		if ($excludecategorylist != "")
			$linkquery .= " AND t.term_id not in (" . $excludecategorylist . ")";

		if ($showinvisible == false)
			$linkquery .= " AND l.link_visible != 'N'";	

		if ($_GET['searchll'] != "")
		{
			$searchterms = explode(" ", $_GET['searchll']);

			if ($searchterms)
			{
				$mode = "search";
				$termnb = 1;

				foreach($searchterms as $searchterm)
				{
					if ($termnb == 1)
					{
						$linkquery .= " AND (link_name like '%" . $searchterm . "%' ";
						$termnb++;
					}
					else
					{
						$linkquery .= " OR link_name like '%" . $searchterm . "%' ";
					}

					if ($hidecategorynames == false)
						$linkquery .= " OR name like '%" . $searchterm . "%' ";
					if ($shownotes)
						$linkquery .= " OR link_notes like '%" . $searchterm . "%' ";
					if ($showdescription)
						$linkquery .= " OR link_description like '%" . $searchterm . "%' ";
					if ($showlargedescription)
						$linkquery .= " OR link_textfield like '%" . $searchterm . "%' ";
				}

				$linkquery .= ")";
			}
		}
		else
			$mode = "normal";
			
		$linkquery .= " ORDER by ";
			
		if ($featuredfirst == true)
			$linkquery .= "link_featured DESC, ";

		if ($order == "name")
			$linkquery .= " name " . $direction;
		elseif ($order == "id")
			$linkquery .= " t.term_id " . $direction;
		elseif ($order == "order")
			$linkquery .= " t.term_order " . $direction;
		elseif ($order == "catlist")
			$linkquery .= " FIELD(t.term_id," . $categorylist . ") ";

		if ($linkorder == "name" || $linkorder == 'random')
			$linkquery .= ", l.link_name " . $linkdirection;
		elseif ($linkorder == "id")
			$linkquery .= ", l.link_id " . $linkdirection;
		elseif ($linkorder == "order")
			$linkquery .= ", l.link_order ". $linkdirection;

		if ($pagination && $mode != 'search')
		{
			$linkitemsforcount = $wpdb->get_results($linkquery);

			$numberoflinks = count($linkitemsforcount);

			$quantity = $linksperpage + 1;

			if (isset($_GET['linkresultpage']))
			{
				$pagenumber = $_GET['linkresultpage'];
				$startingitem = ($pagenumber - 1) * $linksperpage;
				$linkquery .= " LIMIT " . $startingitem . ", " . $quantity;
			}
			else
			{
				$pagenumber = 1;
				$linkquery .= " LIMIT 0, " . $quantity;
			}
		}
		
		$linkitems = $wpdb->get_results($linkquery, ARRAY_A);

		if ($debugmode)
		{
			$output .= "\n<!-- Link Query: " . print_r($linkquery, TRUE) . "-->\n\n";
			$output .= "\n<!-- Link Results: " . print_r($linkitems, TRUE) . "-->\n\n";
		}

		if ($pagination)
		{
			if ($linksperpage == 0 && $linksperpage == '')
				$linksperpage = 5;

			if (count($linkitems) > $linksperpage)
			{
				array_pop($linkitems);
				$nextpage = true;
			}
			else
				$nextpage = false;
			$preroundpages = $numberoflinks / $linksperpage;
			$numberofpages = ceil( $preroundpages * 1 ) / 1; 
		}
		
		if ($linkorder == 'random')
		{
			shuffle($linkitems);
		}
		
		if ($maxlinks != '')
		{
			if (is_numeric($maxlinks))
			{
				array_splice($linkitems, $maxlinks);
			}
		}

		// Display links
		if ($linkitems) {
			$output .= "<div id='linklist" . $settings . "' class='linklist'>\n";

			if ($mode == "search")
			{
				$output .= "<div class='resulttitle'>" . __('Search Results for', 'link-library') . " '" . $_GET['searchll'] . "'</div>";
			}

			$currentcategoryid = -1;

			foreach ( $linkitems as $linkitem ) {

				if ($currentcategoryid != $linkitem['term_id'])
				{
					if ($currentcategoryid != -1 && $showonecatonly && $_GET['searchll'] == "")
					{
						break;
					}
					if ($currentcategoryid != -1)
					{
						// Close the last category
						if ($displayastable)
							$output .= "\t</table>\n";
						else
							$output .= "\t</ul>\n";
							
						if ($catlistwrappers != '')
							$output .= "</div>";
							
						$output .= "</div>";

						$currentcategory = $currentcategory + 1;
					}

					$currentcategoryid = $linkitem['term_id'];
					$output .= "<div class='LinkLibraryCat" . $currentcategoryid . "'>";
					$linkcount = 0;

					if ($catlistwrappers == 1)
						$output .= "<div class=\"" . $beforecatlist1 . "\">";
					else if ($catlistwrappers == 2)
					{
						$remainder = $currentcategory % $catlistwrappers;
						switch ($remainder) {

							case 0:
								$output .= "<div class=\"" . $beforecatlist2 . "\">";
								break;
								
							case 1:
								$output .= "<div class=\"" . $beforecatlist1 . "\">";
								break;
						}
					}
					else if ($catlistwrappers == 3)
					{
						$remainder = $currentcategory % $catlistwrappers;
						switch ($remainder) {

							case 0:
								$output .= "<div class=\"" . $beforecatlist3 . "\">";
								break;

							case 2:
								$output .= "<div class=\"" . $beforecatlist2 . "\">";
								break;

							case 1:
								$output .= "<div class=\"" . $beforecatlist1 . "\">";
								break;
						}
					}

					// Display the category name
					if ($hidecategorynames == false || $hidecategorynames == "")
					{
						if ($catanchor)
							$cattext = '<div id="' . $linkitem['slug'] . '">';
						else
							$cattext = '';

						if ($divorheader == false)
						{
							if ($mode == "search")
								foreach ($searchterms as $searchterm)
								{
									$linkitem['name'] = $this->ll_highlight_phrase($linkitem['name'], $searchterm, '<span class="highlight_word">', '</span>'); 
								}

							$catlink = '<div class="' . $catnameoutput . '">';

							if ($catdescpos == "right" || $catdescpos == '')
								$catlink .= $linkitem['name'];

							if ($showcategorydesclinks)
							{
								$catlink .= "<span class='linklistcatnamedesc'>";
								$linkitem['description'] = str_replace("[", "<", $linkitem['description']);
								$linkitem['description'] = str_replace("]", ">", $linkitem['description']);
								$catlink .= $linkitem['description'];
								$catlink .= '</span>';
							}

							if ($catdescpos == "left")
								$catlink .= $linkitem['name'];

							$catlink .= "</div>";
						}
						else if ($divorheader == true)
						{
							if ($mode == "search")
							foreach ($searchterms as $searchterm)
							{
								$linkitem['name'] = $this->ll_highlight_phrase($linkitem['name'], $searchterm, '<span class="highlight_word">', '</span>');
							}

							$catlink = '<'. $catnameoutput . '>';

							if ($catdescpos == "right" || $catdescpos == '')
								$catlink .= $linkitem['name'];

							if ($showcategorydesclinks)
							{
								$catlink .= "<span class='linklistcatnamedesc'>";
								$linkitem['description'] = str_replace("[", "<", $linkitem['description']);
								$linkitem['description'] = str_replace("]", ">", $linkitem['description']);
								$catlink .= $linkitem['description'];
								$catlink .= '</span>';
							}

							if ($catdescpos == "left")
								$catlink .= $linkitem['name'];

							$catlink .= '</' . $catnameoutput . '>';
						}

						if ($catanchor)
							$catenddiv = '</div>';
						else
							$catenddiv = '';
					}

					if ($displayastable == true)
					{
						$catstartlist = "\n\t<table class='linklisttable'>\n";
						if ($showcolumnheaders == true)
						{
							$catstartlist .= "<div class='linklisttableheaders'><tr>";

							if ($linkheader != "")
								$catstartlist .= "<th><div class='linklistcolumnheader'>".$linkheader."</div></th>";

							if ($descheader != "")
								$catstartlist .= "<th><div class='linklistcolumnheader'>".$descheader."</div></th>";

							if ($notesheader != "")
								$catstartlist .= "<th><div class='linklistcolumnheader'>".$notesheader."</div></th>";

							$catstartlist .= "</tr></div>\n";
						}
						else
							$catstartlist .= '';
					}
					else
						$catstartlist = "\n\t<ul>\n";

					$output .= $cattext . $catlink . $catenddiv . $catstartlist; 
				}

				$between = "\n";

				$feed = NULL;

				if ($rssfeedinline)
				{
					ini_set('display_errors', '0');

					if( !class_exists('SimplePie'))
					{
						require_once( 'simplepie.inc' );
					}

					$feed = new SimplePie();

					// We'll enable the discovering and caching of favicons.
					$feed->set_favicon_handler('./handler_image.php');

					$feed->set_item_limit($rssfeedinlinecount);

					$feed->enable_cache(true);
					if ($rsscachedir == '')
						$rsscachedir = ABSPATH . 'wp-content/cache/link-library';
					$feed->set_cache_location($rsscachedir);

					$feed->set_stupidly_fast(true);

					// We'll make sure that the right content type and character encoding gets set automatically.
					// This function will grab the proper character encoding, as well as set the content type to text/html.
					$feed->handle_content_type();
				}

				if ($showuserlinks == true || strpos($linkitem['link_description'], "LinkLibrary:AwaitingModeration:RemoveTextToApprove") == false)
				{
					$linkcount = $linkcount + 1;

					if ($linkaddfrequency > 0)
						if (($linkcount - 1) % $linkaddfrequency == 0)
							$output .= stripslashes($addbeforelink);

					if (!isset($linkitem['recently_updated'])) $linkitem['recently_updated'] = false; 
					$output .= stripslashes($beforeitem);
					if ($showupdated && $linkitem['recently_updated'])
						$output .= get_option('links_recently_updated_prepend'); 

					$the_link = '#';
					if (!empty($linkitem['link_url']) )
						$the_link = wp_specialchars($linkitem['link_url']);

					$the_second_link = '#';
					if (!empty($linkitem['link_second_url']) )
						$the_second_link = wp_specialchars($linkitem['link_second_url']);

					$rel = $linkitem['link_rel'];
					if ('' != $rel and !$nofollow and !$linkitem['link_no_follow'])
						$rel = ' rel="' . $rel . '"';
					else if ('' != $rel and ($nofollow or $linkitem['link_no_follow']))
						$rel = ' rel="' . $rel . ' nofollow"';
					else if ('' == $rel and ($nofollow or $linkitem['link_no_follow']))
						$rel = ' rel="nofollow"';

					if ($use_html_tags) {
						$descnotes = $linkitem['link_notes'];
						$descnotes = str_replace("[", "<", $descnotes);
						$descnotes = str_replace("]", ">", $descnotes);
					}
					else
						$descnotes = wp_specialchars($linkitem['link_notes'], ENT_QUOTES);

					if ($use_html_tags) {
						$desc = $linkitem['link_description'];
						$desc = str_replace("[", "<", $desc);
						$desc = str_replace("]", ">", $desc);
					}
					else {
						$desc = wp_specialchars($linkitem['link_description'], ENT_QUOTES);
					}

					$cleanname = wp_specialchars($linkitem['link_name'], ENT_QUOTES);
					$textfield = stripslashes($linkitem['link_textfield']);

					if ($mode == "search")
					{
						foreach ($searchterms as $searchterm)
						{
							$descnotes = $this->ll_highlight_phrase($descnotes, $searchterm, '<span class="highlight_word">', '</span>');
							$desc = $this->ll_highlight_phrase($desc, $searchterm, '<span class="highlight_word">', '</span>');
							$name = $this->ll_highlight_phrase($linkitem['link_name'], $searchterm, '<span class="highlight_word">', '</span>');
							$textfield = $this->ll_highlight_phrase($textfield, $searchterm, '<span class="highlight_word">', '</span>');
						}
				}
					else
						$name = $cleanname;

					$title = wp_specialchars($linkitem['link_description'], ENT_QUOTES);

					if ($showupdated) {
					   if (substr($linkitem['link_updated'],0,2) != '00') {
							$title .= ' ('.__('Last updated', 'link-library') . '  ' . date(get_option('links_updated_date_format'), $linkitem['link_updated'] + (get_option('gmt_offset') * 3600)) .')';
						}
					}

					if ('' != $title)
						$title = ' title="' . $title . '"';

					$alt = ' alt="' . $cleanname . '"';
						
					$target = $linkitem['link_target'];
					if ('' != $target)
						$target = ' target="' . $target . '"';
					else 
					{
						$target = $linktarget;
						if ('' != $target)
							$target = ' target="' . $target . '"';
					}
					
					global $llpluginpath;

					if ($dragndroporder == '') $dragndroporder = '1,2,3,4,5,6,7,8,9,10';
						$dragndroparray = explode(',', $dragndroporder);
						if ($dragndroparray)
						{
							foreach ($dragndroparray as $arrayelements) {
								switch ($arrayelements) {
									case 1: 	//------------------ Image Output --------------------

										if ( ($linkitem['link_image'] != null || $usethumbshotsforimages) && ($show_images)) {
											$imageoutput = stripslashes($beforeimage) . '<a href="';

											if ($sourceimage == 'primary' || $sourceimage == '')
												$imageoutput .= $the_link;
											elseif ($sourceimage == 'secondary')
												$imageoutput .= $the_second_link;

											$imageoutput .= '" id="' . $linkitem['proper_link_id'] . '" class="track_this_link ' . ( $linkitem['link_featured'] ? 'featured' : '' ). '" ' . $rel . $title . $target. '>';

											if ($usethumbshotsforimages)
											{
												if ($thumbshotscid == '')
													$imageoutput .= '<img src="http://open.thumbshots.org/image.aspx?url=' . $the_link . '"';
												elseif ($thumbshotscid != '')
													$imageoutput .= '<img src="http://images.thumbshots.com/image.aspx?cid=' . $thumbshotscid . 
														'&v=1&w=120&h=90&url=' . $the_link . '"';											
											}
											elseif ( strpos($linkitem['link_image'], 'http') !== false )
												$imageoutput .= '<img src="' . $linkitem['link_image'] . '"';
											else // If it's a relative path
												$imageoutput .= '<img src="' . get_option('siteurl') . $linkitem['link_image'] . '"';

											$imageoutput .= $alt . $title;

											if ($imageclass != '')
												$imageoutput .= ' class="' . $imageclass . '" ';

											$imageoutput .= "/>";

											$imageoutput .= '</a>' . stripslashes($afterimage);
										}

										if ( ($linkitem['link_image'] != null || $usethumbshotsforimages) && ($show_images) ) {
											$output .= $imageoutput;
										}
										break;

									case 2: 	//------------------ Name Output --------------------   

										if ($showname == true)
										{
											$output .= stripslashes($beforelink);
											
											if (($sourcename == 'primary' && $the_link != '#') || ($sourcename == 'secondary' && $the_second_link != '#'))
											{
												$output .= '<a href="';

												if ($sourcename == 'primary' || $sourcename == '')
													$output .= $the_link;
												elseif ($sourcename == 'secondary')
													$output .= $the_second_link;

												$output .= '" id="' . $linkitem['proper_link_id'] . '" class="track_this_link ' . ( $linkitem['link_featured'] ? 'featured' : '' ). '" ' . $rel . $title . $target. '>';
											}
											
											$output .= $name;
											
											if (($sourcename == 'primary' && $the_link != '#') || ($sourcename == 'secondary' && $the_second_link != '#'))
												$output .= '</a>';

											if (($showadmineditlinks) && current_user_can("manage_links")) {
												$output .= $between . '<a href="' . WP_ADMIN_URL . '/link.php?action=edit&link_id=' . $linkitem['proper_link_id'] .'">(' . __('Edit', 'link-library') . ')</a>';
											}

											if ($showupdated && $linkitem['recently_updated']) {
												$output .= get_option('links_recently_updated_append');
											}

											$output .= stripslashes($afterlink);
										}

										break;

									case 3: 	//------------------ Date Output --------------------   

										$formatteddate = date("F d Y", $linkitem['link_date']);

										if ($showdate)
											$output .= $between . stripslashes($beforedate) . $formatteddate . stripslashes($afterdate);

										break;

									case 4: 	//------------------ Description Output --------------------   

										if ($showdescription)
											$output .= $between . stripslashes($beforedesc) . $desc . stripslashes($afterdesc);

										break;

									case 5: 	//------------------ Notes Output --------------------   

										if ($shownotes) {
											$output .= $between . stripslashes($beforenote) . $descnotes . stripslashes($afternote);
										}

										break;

									case 6: 	//------------------ RSS Icons Output --------------------   

										if ($show_rss || $show_rss_icon || $rsspreview)
											$output .= stripslashes($beforerss) . '<div class="rsselements">';

										if ($show_rss && ($linkitem['link_rss'] != '')) {
											$output .= $between . '<a class="rss" href="' . $linkitem['link_rss'] . '">RSS</a>';
										}
										if ($show_rss_icon && ($linkitem['link_rss'] != '')) {
											$output .= $between . '<a class="rssicon" href="' . $linkitem['link_rss'] . '"><img src="' . $llpluginpath . '/icons/feed-icon-14x14.png" /></a>';
										}
										if ($rsspreview && $linkitem['link_rss'] != '')
										{
											$output .= $between . '<a href="' . WP_PLUGIN_URL . '/link-library/rsspreview.php?keepThis=true&linkid=' . $linkitem['proper_link_id'] . '&previewcount=' . $rsspreviewcount . 'height=' . (($rsspreviewwidth == "") ?  900 : $rsspreviewwidth) . '&width=' . (($rsspreviewheight == "") ? 700 : $rsspreviewheight) . '" title="' . __('Preview of RSS feed for', 'link-library') . ' ' . $cleanname . '" class="thickbox"><img src="' . $llpluginpath . '/icons/preview-16x16.png" /></a>';
										}
										
										if ($show_rss || $show_rss_icon || $rsspreview)
											$output .= '</div>' . stripslashes($afterrss);

										if ($rssfeedinline && $linkitem['link_rss'])
										{
											$feed->set_feed_url($linkitem['link_rss']);

											$feed->init();

												if ($feed->data && $feed->get_item_quantity() > 0)
												{
													$output .= '<div id="ll_rss_results">';

													$items = $feed->get_items(0, $rssfeedinlinecount);
													foreach($items as $item)
													{
														$output .= '<div class="chunk" style="padding:0 5px 5px;">';
														$output .= '<div class="rsstitle"><a target="feedwindow" href="' . $item->get_permalink() . '">' . $item->get_title() . '</a> - ' . $item->get_date("j M Y") . '</div>';
														if ($rssfeedinlinecontent) $output .= '<div class="rsscontent">' . $item->get_content() . '</div>';
														$output .= '</div>';
														$output .= '<br />';
													}

													$output .= '</div>';
												}
												
											ini_set('display_errors', '1');
										}
										break;
									case 7: 	//------------------ Web Link Output --------------------   

										if ($displayweblink != 'false') {
											$output .= $between . stripslashes($beforeweblink) . "<a href='";

											if ($sourceweblink == "primary" || $sourceweblink == "")
												$output .= $the_link;
											elseif ($sourceweblink == "secondary")
												$output .= $the_second_link;

											$output .= "' id='" . $linkitem['proper_link_id'] . "' class='track_this_link' " . $target . ">";

											if ($displayweblink == 'address')
											{
												if (($sourceweblink == "primary" || $sourceweblink == '') && $the_link != '')
													$output .= $the_link;
												elseif ($sourceweblink == "secondary" && $the_second_link != '')
													$output .= $the_second_link;
											}
											elseif ($displayweblink == 'label' && $weblinklabel != '')
												$output .= $weblinklabel;

											$output .= "</a>" . stripslashes($afterweblink);
										}

										break;
									case 8: 	//------------------ Telephone Output --------------------   

										if ($showtelephone != 'false')
										{
											$output .= $between . stripslashes($beforetelephone);

											if ($showtelephone != 'plain')
											{
												$output .= "<a href='";

												if (($sourcetelephone == "primary" || $sourcetelephone == '') && $the_link != '')
													$output .= $the_link;
												elseif ($sourcetelephone == "secondary" && $the_second_link != '')
													$output .= $the_second_link;

												$output .= "' id='" . $linkitem['proper_link_id'] . "' class='track_this_link' >";
											}

											if ($showtelephone == 'link' || $showtelephone == "plain")
												$output .= $linkitem['link_telephone'];
											elseif ($showtelephone == 'label')
												$output .= $telephonelabel;

											if ($showtelephone != 'plain')
												$output .= "</a>";

											$output .= stripslashes($aftertelephone);
										}
										break;
									case 9: 	//------------------ E-mail Output --------------------   

										if ($showemail != 'false')
										{
											$output .= $between . stripslashes($beforeemail);

											if ($showemail != 'plain')
											{
												$output .= "<a href='";

												if ($showemail == 'mailto' || $showemail == 'mailtolabel')
													$output .= "mailto:" . $linkitem['link_email'];
												elseif ($showemail == 'command' || $showemail == 'commandlabel')
												{
													$newcommand = str_replace("#email", $linkitem['link_email'], $emailcommand);
													$cleanlinkname = str_replace(" ", "%20", $linkitem['link_name']);
													$newcommand = str_replace("#company", $cleanlinkname, $newcommand);
													$output .= $newcommand;												
												}

												$output .= "'>";
											}

											if ($showemail == 'plain' || $showemail == 'mailto' || $showemail == 'command')
												$output .= $linkitem['link_email'];
											elseif ($showemail == 'mailtolabel' || $showemail == 'commandlabel')
												$output .= $emaillabel;
												
											if ($showemail != 'plain')
												$output .= "</a>";

											$output .= stripslashes($afteremail);
										}

										break;
									case 10: 	//------------------ Link Hits Output --------------------   
									
										if ($showlinkhits)
										{
											$output .= $between . stripslashes($beforelinkhits);
											
											$output .= $linkitem['link_visits'];
											
											$output .= stripslashes($afterlinkhits);
										}

										break;

									case 11: 	//------------------ Link Rating Output --------------------   

										if ($showrating)
										{
											$output .= $between . stripslashes($beforelinkrating);

											$output .= $linkitem['link_rating'];

											$output .= stripslashes($afterlinkrating);
										}

										break;
										
									case 12: 	//------------------ Link Large Description Output --------------------   

										if ($showlargedescription)
										{
											$output .= $between . stripslashes($beforelargedescription);

											$output .= $textfield;

											$output .= stripslashes($afterlargedescription);
										}

										break;
									}
								}
							}

					$output .= stripslashes($afteritem) . "\n";

					if ($linkaddfrequency > 0)
						if ($linkcount % $linkaddfrequency == 0)
							$output .= stripslashes($addafterlink);

				}

			} // end while

			// Close the last category
			if ($displayastable)
				$output .= "\t</table>\n";
			else
				$output .= "\t</ul>\n";

			if ($catlistwrappers != '')
				$output .= "</div>";
				
			$output .= "</div>";

			if ($pagination && $mode != "search")
			{
				$previouspagenumber = $pagenumber - 1;
				$nextpagenumber = $pagenumber + 1;
				$dotbelow = false;
				$dotabove = false;

				if ($numberofpages > 1)
				{
					$output .= "<div class='pageselector'>";	

					if ($pagenumber != 1)
					{
						$output .= "<span class='previousnextactive'>";

						if (!$showonecatonly)
							$output .= "<a href='?page_id=" . get_the_ID() . "&linkresultpage=" . $previouspagenumber . "'>" . __('Previous', 'link-library') . "</a>";
						elseif ($showonecatonly)
						{
							if ($showonecatmode == 'AJAX' || $showonecatmode == '')
								$output .= "<a href='#' onClick=\"showLinkCat('" . $AJAXcatid . "', '" . $settings . "', " . $previouspagenumber . ");return false;\" >" . __('Previous', 'link-library') . "</a>";
							elseif ($showonecatmode == 'HTMLGET')
								$output .= "<a href='?page_id=" . get_the_ID() . "&linkresultpage=" . $previouspagenumber . "&cat_id=" . $AJAXcatid . "' >" . __('Previous', 'link-library') . "</a>";
						}

						$output .= "</span>";
					}
					else
						$output .= "<span class='previousnextinactive'>" . __('Previous', 'link-library') . "</span>";

					for ($counter = 1; $counter <= $numberofpages; $counter++)
					{
						if ($counter <= 2 || $counter >= $numberofpages - 1 || ($counter <= $pagenumber + 2 && $counter >= $pagenumber - 2))
						{
							if ($counter != $pagenumber)
								$output .= "<span class='unselectedpage'>";
							else
								$output .= "<span class='selectedpage'>";

							if (!$showonecatonly)
								$output .= "<a href='?page_id=" . get_the_ID() . "&linkresultpage=" . $counter . "'>" . $counter . "</a>";
							elseif ($showonecatonly)
							{
								if ($showonecatmode == 'AJAX' || $showonecatmode == '')
									$output .= "<a href='#' onClick=\"showLinkCat('" . $AJAXcatid . "', '" . $settings . "', " . $counter . ");return false;\" >" . $counter . "</a>";
								elseif ($showonecatmode == 'HTMLGET')
									$output .= "<a href='?page_id=" . get_the_ID() . "&linkresultpage=" . $counter . "&cat_id=" . $AJAXcatid . "' >" . $counter . "</a>";
							}

							$output .= "</a></span>";
						}

						if ($counter >= 2 && $counter < $pagenumber - 2 && $dotbelow == false)
						{
							$output .= "...";
							$dotbelow = true;
						}

						if ($counter > $pagenumber + 2 && $counter < $numberofpages - 1 && $dotabove == false)
						{
							$output .= "...";
							$dotabove = true;
						}
					}

					if ($pagenumber != $numberofpages)
					{
						$output .= "<span class='previousnextactive'>";

						if (!$showonecatonly)
							$output .= "<a href='?page_id=" . get_the_ID() . "&linkresultpage=" . $nextpagenumber . "'>" . __('Next', 'link-library') . "</a>";
						elseif ($showonecatonly)
						{
							if ($showonecatmode == 'AJAX' || $showonecatmode == '')
								$output .= "<a href='#' onClick=\"showLinkCat('" . $AJAXcatid . "', '" . $settings . "', " . $nextpagenumber . ");return false;\" >" . __('Next', 'link-library') . "</a>";
							elseif ($showonecatmode == 'HTMLGET')
								$output .= "<a href='?page_id=" . get_the_ID() . "&linkresultpage=" . $nextpagenumber . "&cat_id=" . $AJAXcatid . "' >" . __('Next', 'link-library') . "</a>";
						}

						$output .= "</span>";
					}
					else
						$output .= "<span class='previousnextinactive'>" . __('Next', 'link-library') . "</span>";

					$output .= "</div>";
				}
			}

			$output .= "<script type='text/javascript'>\n";
			$output .= "jQuery(document).ready(function()\n";
			$output .= "{\n";
			$output .= "jQuery('a.track_this_link').click(function() {\n";
			$output .= "jQuery.post('" . WP_PLUGIN_URL . "/link-library/tracker.php', {id:this.id});\n";
			$output .= "return true;\n";
			$output .= "});\n";
			$output .= "});\n";
			$output .= "</script>";

			$currentcategory = $currentcategory + 1;

			$output .= "</div>\n";

		}
		else
		{
			$output .= "<div id='linklist" . $settings . "' class='linklist'>\n";
			$output .= __('No links found', 'link-library') . ".\n";
			$output .= "</div>";
		}
		
		$output .= "\n<!-- End of Link Library Output -->\n\n";

		return $output;
	}

	function PrivateLinkLibrarySearchForm($searchlabel = 'Search') {

		if ($searchlabel == "") $searchlabel = __('Search', 'link-library');
		$output = "<form method='get' id='llsearch'>\n";
		$output .= "<div>\n";
		$output .= "<input type='text' onfocus=\"this.value=''\" value='" . $searchlabel . "...' name='searchll' id='searchll' />\n";
		$output .= "<input type='hidden' value='" .  get_the_ID() . "' name='page_id' id='page_id' />\n";
		$output .= "<input type='submit' value='" . $searchlabel . "' />\n";
		$output .= "</div>\n";
		$output .= "</form>\n\n";
		
		return $output;
	}

	function PrivateLinkLibraryAddLinkForm($selectedcategorylist = '', $excludedcategorylist = '', $addnewlinkmsg = '', $linknamelabel = '', $linkaddrlabel = '',
											$linkrsslabel = '', $linkcatlabel = '', $linkdesclabel = '', $linknoteslabel = '', $addlinkbtnlabel = '', $hide_if_empty = true,
											$showaddlinkrss = false, $showaddlinkdesc = false, $showaddlinkcat = false, $showaddlinknotes = false,
											$addlinkreqlogin = false, $debugmode = false, $addlinkcustomcat = false, $linkcustomcatlabel = '',
											$linkcustomcatlistentry = 'User-submitted category (define below)', $showaddlinkreciprocal = false,
											$linkreciprocallabel = '', $showaddlinksecondurl = false, $linksecondurllabel = '',
											$showaddlinktelephone = false, $linktelephonelabel = '', $showaddlinkemail = false, $linkemaillabel = '',
											$showcaptcha = false, $captureddata = '', $linksubmitternamelabel = '', $showlinksubmittername = false,
											$linksubmitteremaillabel = '', $showaddlinksubmitteremail = false, $linksubmittercommentlabel = '',
											$showlinksubmittercomment = false, $linksubmissionthankyouurl = '', $addlinkcatlistoverride = '',
											$showcustomcaptcha = false, $customcaptchaquestion = '', $linklargedesclabel = 'Large Description', $showuserlargedescription = false) {
											
		global $wpdb, $llpluginpath;
		
		if (($addlinkreqlogin && current_user_can("read")) || !$addlinkreqlogin)
		{
			$output = "<form method='post' id='lladdlink' ";
			
			if ($linksubmissionthankyouurl)
				$output .= "action='" . $linksubmissionthankyouurl. "'";
			
			$output .= ">\n";
			$output .= "<div class='lladdlink'>\n";
			
			if ($addnewlinkmsg == "") $addnewlinkmsg = __('Add new link', 'link-library');
			$output .= "<div id='lladdlinktitle'>" . $addnewlinkmsg . "</div>\n";

			$output .= "<table>\n";

			if ($linknamelabel == "") $linknamelabel = __('Link name', 'link-library');
			$output .= "<tr><th>" . $linknamelabel . "</th><td><input type='text' name='link_name' id='link_name' value='" . wp_specialchars(stripslashes($captureddata['link_name']), '1') . "' /></td></tr>\n";

			if ($linkaddrlabel == "") $linkaddrlabel = __('Link address', 'link-library');
			$output .= "<tr><th>" . $linkaddrlabel . "</th><td><input type='text' name='link_url' id='link_url' value='" . wp_specialchars(stripslashes($captureddata['link_url']), '1') . "' /></td></tr>\n";

			if ($showaddlinkrss)
			{
				if ($linkrsslabel == "") $linkrsslabel = __('Link RSS', 'link-library');
				$output .= "<tr><th>" . $linkrsslabel . "</th><td><input type='text' name='link_rss' id='link_rss' value='" . wp_specialchars(stripslashes($captureddata['link_rss']), '1') . "' /></td></tr>\n";
			}

			$linkcatquery = "SELECT distinct t.name, t.term_id, t.slug as category_nicename, tt.description as category_description ";
			$linkcatquery .= "FROM " . $this->db_prefix() . "terms t ";
			$linkcatquery .= "LEFT JOIN " . $this->db_prefix() . "term_taxonomy tt ON (t.term_id = tt.term_id) ";
			$linkcatquery .= "LEFT JOIN " . $this->db_prefix() . "term_relationships tr ON (tt.term_taxonomy_id = tr.term_taxonomy_id) ";

			$linkcatquery .= "WHERE tt.taxonomy = 'link_category' ";

			if ($selectedcategorylist != "")
			{
				if ($hide_if_empty) $linkcatquery .= " AND ";
				$linkcatquery .= " t.term_id in (" . $selectedcategorylist. ")";
			}

			if ($excludedcategorylist != "")
			{
				if ($hide_if_empty || $selectedcategorylist != "") $linkcatquery .= " AND ";
				$linkcatquery .= " t.term_id not in (" . $excludedcategorylist . ")";
			}

			$linkcatquery .= " ORDER by t.name " . $direction;

			$linkcats = $wpdb->get_results($linkcatquery);

			if ($debugmode)
			{
				$output .= "\n<!-- Category query for add link form:" . print_r($linkcatquery, TRUE) . "-->\n\n";
				$output .= "\n<!-- Results of Category query for add link form:" . print_r($linkcats, TRUE) . "-->\n";
			}

			if ($linkcats)
			{
				if ($showaddlinkcat)
				{
					if ($linkcatlabel == "") $linkcatlabel = __('Link category', 'link-library');

					$output .= "<tr><th>" . $linkcatlabel . "</th><td><SELECT name='link_category' id='link_category'>";

					if ($linkcustomcatlistentry == "") $linkcustomcatlistentry = __('User-submitted category (define below)', 'link-library');

					foreach ($linkcats as $linkcat)
					{
						$output .= "<OPTION VALUE='" . $linkcat->term_id . "' ";
						if ($captureddata['link_category'] == $linkcat->term_id)
							$output .= "selected";
						$output .= ">" . $linkcat->name;
					}
					
					if ($addlinkcustomcat)
						$output .= "<OPTION VALUE='new'>" . stripslashes($linkcustomcatlistentry) . "\n";
					
					$output .= "</SELECT></td></tr>\n";
				}
				else
				{
					$output .= "<input type='hidden' name='link_category' id='link_category' value='" . $linkcats[0]->term_id . "'>";
				}
				
				if ($addlinkcustomcat)
					$output .= "<tr><th>" .  $linkcustomcatlabel . "</th><td><input type='text' name='link_user_category' id='link_user_category' value='" . wp_specialchars(stripslashes($captureddata['link_user_category']), '1') . "' /></td></tr>\n";			
			}		
			
			if ($showaddlinkdesc)
			{
				if ($linkdesclabel == "") $linkdesclabel = __('Link description', 'link-library');
				$output .= "<tr><th>" . $linkdesclabel . "</th><td><input type='text' name='link_description' id='link_description' value='" . wp_specialchars(stripslashes($captureddata['link_description']), '1') . "' /></td></tr>\n";
			}
			
			if ($showuserlargedescription)
			{
				if ($linklargedesclabel == "") $linklargedesclabel = __('Large description', 'link-library');
				$output .= "<tr><th style='vertical-align: top'>" . $linklargedesclabel . "</th><td><textarea name='link_textfield' id='link_textfield' cols='66'>" . wp_specialchars(stripslashes($captureddata['link_textfield']), '1') . "</textarea></td></tr>\n";
			}
			
			if ($showaddlinknotes)
			{
				if ($linknoteslabel == "") $linknoteslabel = __('Link notes', 'link-library');
				$output .= "<tr><th>" . $linknoteslabel . "</th><td><input type='text' name='link_notes' id='link_notes' value='" . wp_specialchars(stripslashes($captureddata['link_notes']), '1') . "' /></td></tr>\n";
			}
			
			if ($showaddlinkreciprocal)
			{
				if ($linkreciprocallabel == "") $linkreciprocallabel = __('Reciprocal Link', 'link-library');
				$output .= "<tr><th>" . $linkreciprocallabel . "</th><td><input type='text' name='ll_reciprocal' id='ll_reciprocal' value='" . wp_specialchars(stripslashes($captureddata['link_reciprocal']), '1') . "' /></td></tr>\n";
			}
			
			if ($showaddlinksecondurl)
			{
				if ($linksecondurllabel == "") $linksecondurllabel = __('Secondary Address', 'link-library');
				$output .= "<tr><th>" . $linksecondurllabel . "</th><td><input type='text' name='ll_secondwebaddr' id='ll_secondwebaddr' value='" . wp_specialchars(stripslashes($captureddata['ll_secondwebaddr']), '1') . "' /></td></tr>\n";
			}
			
			if ($showaddlinktelephone)
			{
				if ($linktelephonelabel == "") $linktelephonelabel = __('Telephone', 'link-library');
				$output .= "<tr><th>" . $linktelephonelabel . "</th><td><input type='text' name='ll_telephone' id='ll_telephone' value='" . wp_specialchars(stripslashes($captureddata['ll_telephone']), '1') . "' /></td></tr>\n";
			}
			
			if ($showaddlinkemail)
			{
				if ($linkemaillabel == "") $linkemaillabel = __('E-mail', 'link-library');
				$output .= "<tr><th>" . $linkemaillabel . "</th><td><input type='text' name='ll_email' id='ll_email' value='" . wp_specialchars(stripslashes($captureddata['ll_email']), '1') . "' /></td></tr>\n";
			}
			
			if ($showlinksubmittername)
			{
				if ($linksubmitternamelabel == "") $linksubmitternamelabel = __('Submitter Name', 'link-library');
				$output .= "<tr><th>" . $linksubmitternamelabel . "</th><td><input type='text' name='ll_submittername' id='ll_submittername' value='" . wp_specialchars(stripslashes($captureddata['ll_submittername']), '1') . "' /></td></tr>\n";
			}
			
			if ($showaddlinksubmitteremail)
			{
				if ($linksubmitteremaillabel == "") $linksubmitteremaillabel = __('Submitter E-mail', 'link-library');
				$output .= "<tr><th>" . $linksubmitteremaillabel . "</th><td><input type='text' name='ll_submitteremail' id='ll_submitteremail' value='" . wp_specialchars(stripslashes($captureddata['ll_submitteremail']), '1') . "' /></td></tr>\n";
			}
			
			if ($showlinksubmittercomment)
			{
				if ($linksubmittercommentlabel == "") $linksubmittercommentlabel = __('Submitter Comment', 'link-library');
				$output .= "<tr><th style='vertical-align: top;'>" . $linksubmittercommentlabel . "</th><td><textarea name='ll_submittercomment' id='ll_submittercomment' cols='38''>" . wp_specialchars(stripslashes($captureddata['ll_submittercomment']), '1') . "</textarea></td></tr>\n";
			}
			
			if ($showcaptcha)
			{
				$output .= "<tr><td></td><td><span id='captchaimage'><img src='" . $llpluginpath . "captcha/easycaptcha.php' /></span></td></tr>\n";
				$output .= "<tr><th>" . __('Enter code from above image', 'link-library') . "</th><td><input type='text' name='confirm_code' /></td></tr>\n";
			}
			
			if ($showcustomcaptcha)
			{
				if ($customcaptchaquestion == "") $customcaptchaquestion = __('Is boiling water hot or cold?', 'link-library');
				$output .= "<tr><th style='vertical-align: top;'>" . $customcaptchaquestion . "</th><td><input type='text' name='ll_customcaptchaanswer' id='ll_customcaptchaanswer' value='" . $captureddata['ll_customcaptchaanswer'] . "' /></td></tr>\n";			
			}
						
			$output .= "</table>\n";
			
			if ($addlinkbtnlabel == "") $addlinkbtnlabel = __('Add link', 'link-library');
			$output .= '<span style="border:0;" class="LLUserLinkSubmit"><input type="submit" name="submit" value="' . $addlinkbtnlabel . '" /></span>';
			
			$output .= "</div>\n";
			$output .= "</form>\n\n";		
		}

		return $output;
	}
	
	/*
	 * function LinkLibraryCategories()
	 *
	 * added by Yannick Lefebvre
	 *
	 * Output a list of all links categories, listed by category, using the
	 * settings in $wpdb->linkcategories and output it as table
	 *
	 * Parameters:
	 *   order (default 'name')  - Sort link categories by 'name' or 'id' or 'category-list'. When set to 'AdminSettings', will use parameters set in Admin Settings Panel.
	 *   hide_if_empty (default true)  - Supress listing empty link categories
	 *   table_witdh (default 100) - Width of table, percentage
	 *   num_columns (default 1) - Number of columns in table
	 *   catanchor (default true) - Determines if links to generated anchors should be created
	 *   flatlist (default false) - When set to true, displays an unordered list instead of a table
	 *   categorylist (default null) - Specifies a comma-separate list of the only categories that should be displayed
	 *   excludecategorylist (default null) - Specifies a comma-separate list of the categories that should not be displayed
	 *   showcategorydescheaders (default null) - Show category descriptions in category list
	 *   showonecatonly (default false) - Enable AJAX mode showing only one category at a time
	 *   settings (default NULL) - Settings Set ID, only used when showonecatonly is true
	 *   loadingicon (default NULL) - Path to icon to display when only show one category at a time
	 *   catlistdescpos (default 'right') - Position of category description relative to name
	 *   debugmode (default false)
	 *   pagination (default false)
	 *   linksperpage (default 5)
	 *   showcatlinkcount (default false)
	 *   showonecatmode (default 'AJAX')
	 *   cattargetaddress
	 *   rewritepage
	 *   showinvisible
	 */

	function LinkLibraryCategories($order = 'name', $hide_if_empty = true, $table_width = 100, $num_columns = 1, $catanchor = true, 
								   $flatlist = false, $categorylist = '', $excludecategorylist = '', $showcategorydescheaders = false,
								   $showonecatonly = false, $settings = '', $loadingicon = '/icons/Ajax-loader.gif', $catlistdescpos = 'right', $debugmode = false,
								   $pagination = false, $linksperpage = 5, $showcatlinkcount = false, $showonecatmode = 'AJAX', $cattargetaddress = '',
								   $rewritepage = '', $showinvisible = false) {
		
		if (strpos($order, 'AdminSettings') != false)
		{
			$settingsetid = substr($order, 13);
			$settingsetname = "LinkLibraryPP" . $settingsetid;
			$options = get_option($settingsetname);
			
			$genoptions = get_option('LinkLibraryGeneral');

			return $this->PrivateLinkLibraryCategories($options['order'], $options['hide_if_empty'], $options['table_width'], $options['num_columns'], $options['catanchor'], $options['flatlist'],
									 $options['categorylist'], $options['excludecategorylist'], $options['showcategorydescheaders'], $options['showonecatonly'], '',
									 $options['loadingicon'], $options['catlistdescpos'], $genoptions['debugmode'], $options['pagination'], $options['linksperpage'],
									 $options['showcatlinkcount'], $options['showonecatmode'], $options['cattargetaddress'], $options['rewritepage'], $options['showinvisible']);   
		}
		else
			return $this->PrivateLinkLibraryCategories($order, $hide_if_empty, $table_width, $num_columns, $catanchor, $flatlist, $categorylist, $excludecategorylist, $showcategorydescheaders,
			$showonecatonly, $settings, $loadingicon, $catlistdescpos, $debugmode, $pagination, $linksperpage, $showcatlinkcount, $showonecatmode, $cattargetaddress,
			$rewritepage, $showinvisible);   
		
	}

	/*
	 * function LinkLibrary()
	 *
	 * added by Yannick Lefebvre
	 *
	 * Output a list of all links, listed by category, using the
	 * settings in $wpdb->linkcategories and output it as a nested
	 * HTML unordered list. Can also insert anchors for categories
	 *
	 * Parameters:
	 *   order (default 'name')  - Sort link categories by 'name' or 'id'. When set to 'AdminSettings', will use parameters set in Admin Settings Panel.
	 *   hide_if_empty (default true)  - Supress listing empty link categories
	 *   catanchor (default true) - Adds name anchors to categorie links to be able to link directly to categories\
	 *   showdescription (default false) - Displays link descriptions. Added for 2.1 since link categories no longer have this setting
	 *   shownotes (default false) - Shows notes in addition to description for links (useful since notes field is larger than description)
	 *   showrating (default false) - Displays link ratings. Added for 2.1 since link categories no longer have this setting
	 *   showupdated (default false) - Displays link updated date. Added for 2.1 since link categories no longer have this setting
	 *   categorylist (default null) - Only show links inside of selected categories. Enter category numbers in a string separated by commas
	 *   showimages (default false) - Displays link images. Added for 2.1 since link categories no longer have this setting
	 *   show_image_and_name (default false) - Show both image and name instead of only one or the other
	 *   use_html_tags (default false) - Use HTML tags for formatting instead of just displaying them
	 *   show_rss (default false) - Display RSS URI if available in link description
	 *   beforenote (default <br />) - Code to print out between the description and notes
	 *   nofollow (default false) - Adds nofollow tag to outgoing links
	 *   excludecategorylist (default null) - Specifies a comma-separate list of the categories that should not be displayed
	 *   afternote (default null) - Code / Text to be displayed after note
	 *   beforeitem (default null) - Code / Text to be displayed before item
	 *   afteritem (default null) - Code / Text to be displayed after item
	 *   beforedesc (default null) - Code / Text to be displayed before description
	 *   afterdesc (default null) - Code / Text to be displayed after description
	 *   displayastable (default false) - Display lists of links as a table (when true) or as an unordered list (when false)
	 *   beforelink (default null) - Code / Text to be displayed before link
	 *   afterlink (default null) - Code / Text to be displayed after link
	 *   showcolumnheaders (default false) - Show column headers if rendering in table mode
	 *   linkheader (default null) - Text to be shown in link column when displaying as table
	 *   descheader (default null) - Text to be shown in desc column when displaying as table
	 *   notesheader (default null) - Text to be shown in notes column when displaying as table
	 *   catlistwrappers (default 1) - Number of different sets of alternating elements to be placed before and after each link category section
	 *   beforecatlist1 (default null) - First element to be placed before a link category section
	 *   beforecatlist2 (default null) - Second element to be placed before a link category section
	 *   beforecatlist3 (default null) - Third element to be placed before a link category section
	 *   divorheader (default false) - Output div before and after cat name if false, output heading tag if true
	 *   catnameoutput (default linklistcatname) - Name of div class or heading to output
	 *   showrssicon (default false) - Output RSS URI if available and assign to standard RSS icon
	 *   linkaddfrequency (default 0) - Frequency at which extra before and after output should be placed around links
	 *   addbeforelink (default null) - Addition output to be placed before link
	 *   addafterlink (default null) - Addition output to be placed after link
	 *   linktarget (default null) - Specifies the link target window
	 *   showcategorydescheaders (default false) - Display link category description when printing category list
	 *   showcategorydesclinks (default false) - Display link category description when printing links
	 *   showadmineditlinks (default false) - Display edit links in output if logged in as administrator
	 *   showonecatonly (default false) - Only show one category at a time
	 *   AJAXcatid (default null) - Category ID for AJAX sub-queries
	 *   defaultsinglecat (default null) - ID of first category to be shown in single category mode
	 *   rsspreview (default false) - Add preview links after RSS feed addresses
	 *   rssfeedpreviewcount(default 3) - Number of RSS feed items to show in preview
	 *   rssfeedinline (default false) - Shows latest feed items inline with link list
	 *   rssfeedinlinecontent (default false) - Shows latest feed items contents inline with link list
	 *   rssfeedinlinecount (default 1) - Number of RSS feed items to show inline
	 *   beforerss (default null) - String to output before RSS block
	 *   afterrss (default null) - String to output after RSS block
	 *   rsscachedir (default null) - Path for SimplePie library to store RSS cache information
	 *   direction (default ASC) - Sort direction for Link Categories
	 *   linkdirection (default ASC) - Sort direction for Links within each category
	 *   linkorder (default 'name') - Sort order for Links within each category
	 *   pagination (default false) - Limit number of links displayed per page
	 *   linksperpage (default 5) - Number of links to be shown per page in Pagination Mode
	 *   hidecategorynames (default false) - Show category names in Link Library list
	 *   settings (default NULL) - Setting Set ID
	 *   showinvisible (default false) - Shows links that are set to be invisible
	 *   showdate (default false) - Determines is link update date should be displayed
	 *   beforedate (default null) - Code/Text to be displayed before link date
	 *   afterdate (default null) - Code/Text to be displated after link date
	 *   catdescpos (default 'right') - Position of link category description output
	 *   showuserlinks (default false) - Specifies if user submitted links should be shown immediately after submission
	 *   rsspreviewwidth (default 900) - Specifies the width of the box in which RSS previews are displayed
	 *   rsspreviewheight (default 700) - Specifies the height of the box in which RSS previews are displayed
	 *   beforeimage (default null) - Code/Text to be displayed before link image
	 *   afterimage (default null) - Code/Text to be displayed after link image
	 *   imagepos (default beforename) - Position of image relative to link name
	 *   imageclass (default null) - Class that will be assigned to link images
	 *   debugmode (default false) - Adds debug information as comments in the Wordpress output to facilitate remote debugging
	 *   usethumbshotsforimages (default false) - Uses thumbshots.org to generate images for links
	 *   showonecatmode (default AJAX) - Method used to load different categories when only showing one at a time
	 *   dragndroporder (default 1,2,3,4,5,6,7,8,9,10) - Order to display link sub-sections
	 *   displayweblink (default 'false')
	 *   sourceweblink (default 'primary')
	 *   showtelephone (default 'false')
	 *   sourcetelephone (default 'primary')
	 *   showemail (default 'false')
	 *   showlinkhits (default false)
	 *   beforeweblink (default null)
	 *   afterweblink (default null)
	 *   weblinklabel (default null)
	 *   beforetelephone (default null)
	 *   aftertelephone (default null)
	 *   telephonelabel (default null)
	 *   beforeemail (default null)
	 *   afteremail (default null)
	 *   emaillabel (default null)
	 *   beforelinkhits (default null)
	 *   afterlinkhits (default null)
	 *   emailcommand (default null)
	 */

	function LinkLibrary($order = 'name', $hide_if_empty = true, $catanchor = true,
									$showdescription = false, $shownotes = false, $showrating = false,
									$showupdated = false, $categorylist = '', $show_images = false, 
									$show_image_and_name = false, $use_html_tags = false, 
									$show_rss = false, $beforenote = '<br />', $nofollow = false, $excludecategorylist = '',
									$afternote = '', $beforeitem = '<li>', $afteritem = '</li>', $beforedesc = '', $afterdesc = '',
									$displayastable = false, $beforelink = '', $afterlink = '', $showcolumnheaders = false, 
									$linkheader = '', $descheader = '', $notesheader = '', $catlistwrappers = 1, $beforecatlist1 = '', 
									$beforecatlist2 = '', $beforecatlist3 = '', $divorheader = false, $catnameoutput = 'linklistcatname',
									$show_rss_icon = false, $linkaddfrequency = 0, $addbeforelink = '', $addafterlink = '', $linktarget = '',
									$showcategorydesclinks = false, $showadmineditlinks = true, $showonecatonly = false, $AJAXcatid = '',
									$defaultsinglecat = '', $rsspreview = false, $rsspreviewcount = 3, $rssfeedinline = false, $rssfeedinlinecontent = false,
									$rssfeedinlinecount = 1, $beforerss = '', $afterrss = '', $rsscachedir = '', $direction = 'ASC', 
									$linkdirection = 'ASC', $linkorder = 'name', $pagination = false, $linksperpage = 5, $hidecategorynames = false,
									$settings = '', $showinvisible = false, $showdate = false, $beforedate = '', $afterdate = '', $catdescpos = 'right',
									$showuserlinks = false, $rsspreviewwidth = 900, $rsspreviewheight = 700, $beforeimage = '', $afterimage = '', $imagepos = 'beforename',
									$imageclass = '', $AJAXpageid = 1, $debugmode = false, $usethumbshotsforimages = false, $showonecatmode = 'AJAX',
									$dragndroporder = '1,2,3,4,5,6,7,8,9,10', $showname = true, $displayweblink = 'false', $sourceweblink = 'primary', $showtelephone = 'false',
									$sourcetelephone = 'primary', $showemail = 'false', $showlinkhits = false, $beforeweblink = '', $afterweblink = '', $weblinklabel = '',
									$beforetelephone = '', $aftertelephone = '', $telephonelabel = '', $beforeemail = '', $afteremail = '', $emaillabel = '', $beforelinkhits = '',
									$afterlinkhits = '', $emailcommand = '', $sourceimage = 'primary', $sourcename = 'primary', $thumbshotscid = '',
									$maxlinks = '', $beforelinkrating = '', $afterlinkrating = '', $showlargedescription = false, $beforelargedescription = '',
									$afterlargedescription = '', $featuredfirst = false) {

		if (strpos($order, 'AdminSettings') !== false)
		{
			$settingsetid = substr($order, 13);
			$settingsetname = "LinkLibraryPP" . $settingsetid;
			$options = get_option($settingsetname);
			
			$genoptions = get_option('LinkLibraryGeneral');		

			return $this->PrivateLinkLibrary($options['order'], $options['hide_if_empty'], $options['catanchor'], $options['showdescription'], $options['shownotes'],
									  $options['showrating'], $options['showupdated'], $options['categorylist'], $options['show_images'],
									  $options['show_image_and_name'], $options['use_html_tags'], $options['show_rss'], $options['beforenote'],
									  $options['nofollow'], $options['excludecategorylist'], $options['afternote'], $options['beforeitem'],
									  $options['afteritem'], $options['beforedesc'], $options['afterdesc'], $options['displayastable'],
									  $options['beforelink'], $options['afterlink'], $options['showcolumnheaders'], $options['linkheader'],
									  $options['descheader'], $options['notesheader'], $options['catlistwrappers'], $options['beforecatlist1'], 
									  $options['beforecatlist2'], $options['beforecatlist3'], $options['divorheader'], $options['catnameoutput'],
									  $options['show_rss_icon'], $options['linkaddfrequency'], $options['addbeforelink'], $options['addafterlink'],
									  $options['linktarget'], $options['showcategorydesclinks'], $options['showadmineditlinks'], $options['showonecatonly'],
									  $AJAXcatid, $options['defaultsinglecat'], $options['rsspreview'], $options['rsspreviewcount'], $options['rssfeedinline'],
									  $options['rssfeedinlinecontent'], $options['rssfeedinlinecount'], $options['beforerss'], $options['afterrss'],
									  $options['rsscachedir'], $options['direction'], $options['linkdirection'], $options['linkorder'],
									  $options['pagination'], $options['linksperpage'], $options['hidecategorynames'], $settingsetid, $options['showinvisible'],
									  $options['showdate'], $options['beforedate'], $options['afterdate'], $options['catdescpos'], $options['showuserlinks'],
									  $options['rsspreviewwidth'], $options['rsspreviewheight'], $options['beforeimage'], $options['afterimage'], $options['imagepos'],
									  $options['imageclass'], $AJAXpageid, $genoptions['debugmode'], $options['usethumbshotsforimages'], 'AJAX', $options['dragndroporder'],
									  $options['showname'], $options['displayweblink'], $options['sourceweblink'], $options['showtelephone'], $options['sourcetelephone'], 
									  $options['showemail'], $options['showlinkhits'], $options['beforeweblink'], $options['afterweblink'], $options['weblinklabel'],
									  $options['beforetelephone'], $options['aftertelephone'], $options['telephonelabel'], $options['beforeemail'], $options['afteremail'],
									  $options['emaillabel'], $options['beforelinkhits'], $options['afterlinkhits'], $options['emailcommand'], $options['sourceimage'],
									  $options['sourcename'], $genoptions['thumbshotscid'], $options['maxlinks'], $options['beforelinkrating'],
									  $options['afterlinkrating'], $options['showlargedescription'], $options['beforelargedescription'],
									  $options['afterlargedescription'], $options['featuredfirst']);	
		}
		else
			return $this->PrivateLinkLibrary($order, $hide_if_empty, $catanchor, $showdescription, $shownotes, $showrating,
									$showupdated, $categorylist, $show_images, $show_image_and_name, $use_html_tags, 
									$show_rss, $beforenote, $nofollow, $excludecategorylist, $afternote, $beforeitem, $afteritem,
									$beforedesc, $afterdesc, $displayastable, $beforelink, $afterlink, $showcolumnheaders, 
									$linkheader, $descheader, $notesheader, $catlistwrappers, $beforecatlist1, 
									$beforecatlist2, $beforecatlist3, $divorheader, $catnameoutput, $show_rss_icon,
									$linkaddfrequency, $addbeforelink, $addafterlink, $linktarget, $showcategorydesclinks, $showadmineditlinks,
									$showonecatonly, '', $defaultsinglecat, $rsspreview, $rsspreviewcount, $rssfeedinline, $rssfeedinlinecontent, $rssfeedinlinecount,
									$beforerss, $afterrss, $rsscachedir, $direction, $linkdirection, $linkorder,
									$pagination, $linksperpage, $hidecategorynames, $settings, $showinvisible, $showdate, $beforedate, $afterdate, $catdescpos,
									$showuserlinks, $rsspreviewwidth, $rsspreviewheight, $beforeimage, $afterimage, $imagepos, $imageclass, '', $debugmode,
									$usethumbshotsforimages, $showonecatmode, $dragndroporder, $showname, $displayweblink, $sourceweblink, $showtelephone,
									$sourcetelephone, $showemail, $showlinkhits, $beforeweblink, $afterweblink, $weblinklabel, $beforetelephone, $aftertelephone,
									$telephonelabel, $beforeemail, $afteremail, $emaillabel, $beforelinkhits, $afterlinkhits, $emailcommand, $sourceimage, $sourcename,
									$thumbshotscid, $maxlinks, $beforelinkrating, $afterlinkrating, $showlargedescription, $beforelargedescription,
									$afterlargedescription, $featuredfirst);
	}
	
	/********************************************** Function to Process [link-library-cats] shortcode *********************************************/
	
	function link_library_cats_func($atts) {
		extract(shortcode_atts(array(
			'categorylistoverride' => '',
			'excludecategoryoverride' => '',
			'settings' => ''
		), $atts));
		
		if ($settings == '')
		{
			$settings = 1;
			$options = get_option('LinkLibraryPP1');
		}
		else
		{
			$settingsname = 'LinkLibraryPP' . $settings;
			$options = get_option($settingsname);
		}
		
		if ($categorylistoverride != '')
			$selectedcategorylist = $categorylistoverride;
		else
			$selectedcategorylist = $options['categorylist'];
			
		if ($excludecategoryoverride != '')
			$excludedcategorylist = $excludecategoryoverride;
		else
			$excludedcategorylist = $options['excludecategorylist'];
			
		$genoptions = get_option('LinkLibraryGeneral');

		return $this->PrivateLinkLibraryCategories($options['order'], $options['hide_if_empty'], $options['table_width'], $options['num_columns'], $options['catanchor'], $options['flatlist'],
									 $selectedcategorylist, $excludedcategorylist, $options['showcategorydescheaders'], $options['showonecatonly'], $settings,
									 $options['loadingicon'], $options['catlistdescpos'], $genoptions['debugmode'], $options['pagination'], $options['linksperpage'],
									 $options['showcatlinkcount'], $options['showonecatmode'], $options['cattargetaddress'], $options['rewritepage'],
									 $options['showinvisible']);
	}
	
	/********************************************** Function to Process [link-library-search] shortcode *********************************************/

	function link_library_search_func($atts) {
		extract(shortcode_atts(array(
			'settings' => ''
		), $atts));
		
		if ($settings == '')
			$options = get_option('LinkLibraryPP1');
		else
		{
			$settingsname = 'LinkLibraryPP' . $settings;
			$options = get_option($settingsname);
		}
		
		return $this->PrivateLinkLibrarySearchForm($options['searchlabel']);
	}
	
	/********************************************** Function to Process [link-library-add-link] shortcode *********************************************/

	function link_library_insert_link( $linkdata, $wp_error = false, $addlinknoaddress = false) {
		global $wpdb;

		$defaults = array( 'link_id' => 0, 'link_name' => '', 'link_url' => '', 'link_rating' => 0 );

		$linkdata = wp_parse_args( $linkdata, $defaults );
		$linkdata = sanitize_bookmark( $linkdata, 'db' );

		extract( stripslashes_deep( $linkdata ), EXTR_SKIP );

		$update = false;

		if ( !empty( $link_id ) )
			$update = true;

		if ( trim( $link_name ) == '' ) {
			if ( trim( $link_url ) != '' ) {
				$link_name = $link_url;
			} else {
				return 0;
			}
		}

		if ($addlinknoaddress == false)
		{			
			if ( trim( $link_url ) == '' )
			return 0;
		}		
		
		if ( empty( $link_rating ) )
			$link_rating = 0;

		if ( empty( $link_image ) )
			$link_image = '';

		if ( empty( $link_target ) )
			$link_target = '';

		if ( empty( $link_visible ) )
			$link_visible = 'Y';

		if ( empty( $link_owner ) )
			$link_owner = get_current_user_id();

		if ( empty( $link_notes ) )
			$link_notes = '';

		if ( empty( $link_description ) )
			$link_description = '';

		if ( empty( $link_rss ) )
			$link_rss = '';

		if ( empty( $link_rel ) )
			$link_rel = '';

		// Make sure we set a valid category
		if ( ! isset( $link_category ) || 0 == count( $link_category ) || !is_array( $link_category ) ) {
			$link_category = array( get_option( 'default_link_category' ) );
		}

		if ( $update ) {
			if ( false === $wpdb->update( $wpdb->links, compact('link_url', 'link_name', 'link_image', 'link_target', 'link_description', 'link_visible', 'link_rating', 'link_rel', 'link_notes', 'link_rss'), compact('link_id') ) ) {
				if ( $wp_error )
					return new WP_Error( 'db_update_error', __( 'Could not update link in the database' ), $wpdb->last_error );
				else
					return 0;
			}
		} else {
			if ( false === $wpdb->insert( $wpdb->links, compact('link_url', 'link_name', 'link_image', 'link_target', 'link_description', 'link_visible', 'link_owner', 'link_rating', 'link_rel', 'link_notes', 'link_rss') ) ) {
				if ( $wp_error )
					return new WP_Error( 'db_insert_error', __( 'Could not insert link into the database' ), $wpdb->last_error );
				else
					return 0;
			}
			$link_id = (int) $wpdb->insert_id;
		}

		wp_set_link_cats( $link_id, $link_category );

		if ( $update )
			do_action( 'edit_link', $link_id );
		else
			do_action( 'add_link', $link_id );

		clean_bookmark_cache( $link_id );

		return $link_id;
	}

	function link_library_addlink_func($atts, $content, $code) {
		extract(shortcode_atts(array(
			'settings' => '',
			'categorylistoverride' => '',
			'excludecategoryoverride' => ''
		), $atts));
		
		global $wpdb;
		
		if ($settings == '')
			$options = get_option('LinkLibraryPP1');
		else
		{
			$settingsname = 'LinkLibraryPP' . $settings;
			$options = get_option($settingsname);
		}
		
		$genoptions = get_option('LinkLibraryGeneral');
		
		$valid = false;
		$validmessage = "";
		
		if ($_POST['link_name'])
		{		
			if ($options['showcaptcha'])
			{
				if (empty($_REQUEST['confirm_code']))
				{
					$valid = false;
					$validmessage = __('Confirm code not given', 'link-library') . ".";
				}
				else
				{
					if ( isset($_COOKIE['Captcha']) )
					{
						list($Hash, $Time) = explode('.', $_COOKIE['Captcha']);
						if ( md5("ORHFUKELFPTUEODKFJ".$_REQUEST['confirm_code'].$_SERVER['REMOTE_ADDR'].$Time) != $Hash )
						{
							$valid = false;
							$validmessage = __('Captcha code is wrong', 'link-library') . ".";
						}
						elseif( (time() - 5*60) > $Time)
						{
							$valid = false;
							$validmessage = __('Captcha code is only valid for 5 minutes', 'link-library') . ".";
						}
						else
						{
							$valid = true;					
						}
					}
					else
					{
						$valid = false;
						$validmessage = __('No captcha cookie given. Make sure cookies are enabled', 'link-library') . ".";
					}
				}
			}
			
			if ($options['showcustomcaptcha'])
			{
				if ($_POST['ll_customcaptchaanswer'] == "")
				{
					$valid = false;
					$validmessage = __("Captcha answer was not provided.", 'link-library');
				}
				else
				{
					if (strtolower($_POST['ll_customcaptchaanswer']) == strtolower($options['customcaptchaanswer']))
						$valid = true;
					else
					{
						$valid = false;
						$validmessage = __("Captcha answer is incorrect", 'link-library');
					}
				}
			}
			
			$captureddata = array();
			
			if ($valid == false && ($options['showcaptcha'] == true || $options['showcustomcaptcha'] == true))
			{
				$message = "<div class='llmessage'>" . $validmessage . "</div>";
				echo $message;

				$captureddata['link_category'] = $_POST['link_category'];
				$captureddata['link_user_category'] = $_POST['link_user_category'];
				$captureddata['link_description'] = $_POST['link_description'];
				$captureddata['link_textfield'] = $_POST['link_textfield'];
				$captureddata['link_name'] = $_POST['link_name'];
				$captureddata['link_url'] = $_POST['link_url'];
				$captureddata['link_rss'] = $_POST['link_rss'];
				$captureddata['link_notes'] = $_POST['link_notes'];
				$captureddata['ll_secondwebaddr'] = $_POST['ll_secondwebaddr'];
				$captureddata['ll_telephone'] = $_POST['ll_telephone'];
				$captureddata['ll_email'] = $_POST['ll_email'];
				$captureddata['ll_reciprocal'] = $_POST['ll_reciprocal'];
				$captureddata['ll_submittername'] = $_POST['ll_submittername'];
				$captureddata['ll_submitteremail'] = $_POST['ll_submitteremail'];
				$captureddata['ll_submittercomment'] = $_POST['ll_submittercomment'];
				$captureddata['ll_customcaptchaanswer'] = $_POST['ll_customcaptchaanswer'];
			}
			elseif ($valid || ($options['showcaptcha'] == false && $options['showcustomcaptcha'] == false))
			{			
				$existinglinkquery = "SELECT * from " . $this->db_prefix() . "links l where l.link_name = '" . $_POST['link_name'] . "' ";
				
				if ( ( $options['addlinknoaddress'] == false ) || ( $options['addlinknoaddress'] == true && $_POST['link_url'] != "" ) )
					$existinglinkquery = "l.link_url = '" . $_POST['link_url'] . "'";
				
				$existinglink = $wpdb->get_var($existinglinkquery);
				
				if ($existinglink == null)
				{
					if ($_POST['link_category'] == 'new' && $_POST['link_user_category'] != '')
					{
						$existingcatquery = "SELECT t.term_id FROM " . $this->db_prefix() . "terms t, " . $this->db_prefix() . "term_taxonomy tt ";
						$existingcatquery .= "WHERE t.name = '" . $_POST['link_user_category'] . "' AND t.term_id = tt.term_id AND tt.taxonomy = 'link_category'";
						$existingcat = $wpdb->get_var($existingcatquery);
						
						if (!$existingcat)
						{
							$newlinkcatdata = array("cat_name" => $_POST['link_user_category'], "category_description" => "", "category_nicename" => $wpdb->escape($_POST['link_user_category']));
							$newlinkcat = wp_insert_category($newlinkcatdata);
							
							$newcatarray = array("term_id" => $newlinkcat);

							$newcattype = array("taxonomy" => 'link_category');
							
							$wpdb->update( $this->db_prefix().'term_taxonomy', $newcattype, $newcatarray);
							
							$newlinkcat = array($newlinkcat);
						}
						else
						{
							$newlinkcat = array($existingcat);
						}
						
						$message = "<div class='llmessage'>" . $options['newlinkmsg'];
						if ($options['showuserlinks'] == false)
							$message .= ", " . $options['moderatemsg'];
						else
							$message .= ".";
							
						$message .= "</div>";	
						
						if ($code == 'link-library-addlink')
							echo $message;

						$validcat = true;
					}
					elseif ($_POST['link_category'] == 'new' && $_POST['link_user_category'] == '')
					{
						$message = "<div class='llmessage'>" . __('User Category was not provided correctly. Link insertion failed.', 'link-library') . "</div>";	
						echo $message;		
						
						$validcat = false;
					}
					else
					{
						$newlinkcat = array($_POST['link_category']);
						
						$message = "<div class='llmessage'>" . $options['newlinkmsg'];
						if ($options['showuserlinks'] == false)
							$message .= ", " . $options['moderatemsg'];
						else
							$message .= ".";
							
						$message .= "</div>";
						
						if ($code == 'link-library-addlink')
							echo $message;
						
						$validcat = true;
					}
					
					if ($validcat == true)
					{
						if ($options['showuserlinks'] == false)
						{
							$newlinkdesc = "(LinkLibrary:AwaitingModeration:RemoveTextToApprove)" . $_POST['link_description'];
							$newlinkvisibility = 'N';
						}
						else
						{
							$newlinkdesc = $_POST['link_description'];
							$newlinkvisibility = 'Y';
						}
						
						if ($options['storelinksubmitter'] == true)
						{
							global $current_user;
							
							get_currentuserinfo();
							
							if ($current_user)
								$username = $current_user->user_login;
						}
							
						$newlink = array("link_name" => wp_specialchars(stripslashes($_POST['link_name'])), "link_url" => wp_specialchars(stripslashes($_POST['link_url'])), "link_rss" => wp_specialchars(stripslashes($_POST['link_rss'])),
							"link_description" => wp_specialchars(stripslashes($newlinkdesc)), "link_notes" => wp_specialchars(stripslashes($_POST['link_notes'])), "link_category" => $newlinkcat, "link_visible" => $newlinkvisibility);
						$newlinkid = $this->link_library_insert_link($newlink, false, $options['addlinknoaddress']);
						
						$extradatatable = $this->db_prefix() . "links_extrainfo";
						$wpdb->update( $extradatatable, array( 'link_second_url' => $_POST['ll_secondwebaddr'], 'link_telephone' => $_POST['ll_telephone'], 'link_email' => $_POST['ll_email'], 'link_reciprocal' => $_POST['ll_reciprocal'], 'link_submitter' => $username, 'link_submitter_name' => $_POST['ll_submittername'], 'link_submitter_email' => $_POST['ll_submitteremail'], 'link_textfield' => $_POST['link_textfield']), array( 'link_id' => $newlinkid ));		
						
						if ($options['emailnewlink'])
						{
							$adminmail = get_option('admin_email');
							$headers = "MIME-Version: 1.0\r\n";
							$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
							
							$message = __('A user submitted a new link to your Wordpress Link database.', 'link-library') . "<br /><br />";
							$message .= __('Link Name', 'link-library') . ": " . wp_specialchars(stripslashes($_POST['link_name'])) . "<br />";
							$message .= __('Link Address', 'link-library') . ": " . wp_specialchars(stripslashes($_POST['link_url'])) . "<br />";
							$message .= __('Link RSS', 'link-library') . ": " . wp_specialchars(stripslashes($_POST['link_rss'])) . "<br />";
							$message .= __('Link Description', 'link-library') . ": " . wp_specialchars(stripslashes($_POST['link_description'])) . "<br />";
							$message .= __('Link Large Description', 'link-library') . ": " . wp_specialchars(stripslashes($_POST['link_textfield'])) . "<br />";
							$message .= __('Link Notes', 'link-library') . ": " . wp_specialchars(stripslashes($_POST['link_notes'])) . "<br />";
							$message .= __('Link Category', 'link-library') . ": " . $_POST['link_category'] . "<br /><br />";
							$message .= __('Reciprocal Link', 'link-library') . ": " . $_POST['ll_reciprocal'] . "<br /><br />";
							$message .= __('Link Secondary Address', 'link-library') . ": " . $_POST['ll_secondwebaddr'] . "<br /><br />";
							$message .= __('Link Telephone', 'link-library') . ": " . $_POST['ll_telephone'] . "<br /><br />";
							$message .= __('Link E-mail', 'link-library') . ": " . $_POST['ll_email'] . "<br /><br />";
							$message .= __('Link Submitter', 'link-library') . ": " . $username . "<br /><br />";
							$message .= __('Link Submitter Name', 'link-library') . ": " . $_POST['ll_submittername'] . "<br /><br />";
							$message .= __('Link Submitter E-mail', 'link-library') . ": " . $_POST['ll_submitteremail'] . "<br /><br />";
							$message .= __('Link Comment', 'link-library') . ": " . $_POST['ll_submittercomment'] . "<br /><br />";

							if ($options['showuserlinks'] == false)
								$message .= "<a href='" . WP_ADMIN_URL . "/link-manager.php?s=LinkLibrary%3AAwaitingModeration%3ARemoveTextToApprove'>Moderate new links</a>";
							elseif ($options['showuserlinks'] == true)
								$message .= "<a href='" . WP_ADMIN_URL . "/link-manager.php'>View links</a>";
								
							$message .= "<br /><br />" . __('Message generated by', 'link-library') . " <a href='http://yannickcorner.nayanna.biz/wordpress-plugins/link-library/'>Link Library</a> for Wordpress";
							
							if ($emailtitle == '')
							{
								$emailtitle = stripslashes($genoptions['moderationnotificationtitle']);
								$emailtitle = str_replace('%linkname%', wp_specialchars(stripslashes($_POST['link_name'])), $emailtitle);
							}
							else
							{
								$emailtitle = htmlspecialchars_decode(get_option('blogname'), ENT_QUOTES) . " - " . __('New link added', 'link-library') . ": " . htmlspecialchars($_POST['link_name']);
							}
							
							wp_mail($adminmail, $emailtitle, $message, $headers);
						}
					}	
				}
				else
				{
					$message = "<div class='llmessage'>";
					$message = __('Error: Link already exists.', 'link-library');
					$message .= "</div>";
					
					echo $message;
				}
			}
		}
		
		if ($categorylistoverride != '')
			$selectedcategorylist = $categorylistoverride;
		elseif ($options['addlinkcatlistoverride'] != '')
			$selectedcategorylist = $options['addlinkcatlistoverride'];
		else	
			$selectedcategorylist = $options['categorylist'];
			
		if ($excludecategoryoverride != '')
			$excludedcategorylist = $excludecategoryoverride;
		else
			$excludedcategorylist = $options['excludecategorylist'];
			
		if ($code == 'link-library-addlink')
			return $this->PrivateLinkLibraryAddLinkForm($selectedcategorylist, $excludedcategorylist, $options['addnewlinkmsg'], $options['linknamelabel'], $options['linkaddrlabel'],
											 $options['linkrsslabel'], $options['linkcatlabel'], $options['linkdesclabel'], $options['linknoteslabel'],
											 $options['addlinkbtnlabel'], $options['hide_if_empty'], $options['showaddlinkrss'], $options['showaddlinkdesc'],
											 $options['showaddlinkcat'], $options['showaddlinknotes'], $options['addlinkreqlogin'], $genoptions['debugmode'],
											 $options['addlinkcustomcat'], $options['linkcustomcatlabel'], $options['linkcustomcatlistentry'], 
											 $options['showaddlinkreciprocal'], $options['linkreciprocallabel'], $options['showaddlinksecondurl'], $options['linksecondurllabel'],
											 $options['showaddlinktelephone'], $options['linktelephonelabel'], $options['showaddlinkemail'], $options['linkemaillabel'],
											 $options['showcaptcha'], $captureddata, $options['linksubmitternamelabel'], $options['showlinksubmittername'],
											 $options['linksubmitteremaillabel'], $options['showaddlinksubmitteremail'], $options['linksubmittercommentlabel'],
											 $options['showlinksubmittercomment'], $genoptions['linksubmissionthankyouurl'], $options['addlinkcatlistoverride'],
											 $options['showcustomcaptcha'], $options['customcaptchaquestion'], $options['linklargedesclabel'], $options['showuserlargedescription']);	
		
		
	}
	
	/********************************************** Function to Process [link-library] shortcode *********************************************/

	function link_library_func($atts) {
		extract(shortcode_atts(array(
			'categorylistoverride' => '',
			'excludecategoryoverride' => '',
			'notesoverride' => '',
			'descoverride' => '',
			'rssoverride' => '',
			'tableoverride' => '',
			'settings' => ''
		), $atts));
		
		if ($settings == '')
		{
			$settings = 1;
			$options = get_option('LinkLibraryPP1');		
		}
		else
		{
			$settingsname = 'LinkLibraryPP' . $settings;
			$options = get_option($settingsname);
		}
		
		if ($notesoverride != '')
			$selectedshownotes = $notesoverride;
		else
			$selectedshownotes = $options['shownotes'];
		
		if ($descoverride != '')
			$selectedshowdescription = $descoverride;
		else
			$selectedshowdescription = $options['showdescription'];

		if ($rssoverride != '')
			$selectedshowrss = $rssoverride;
		else
			$selectedshowrss = $options['show_rss'];					
			
		if ($categorylistoverride != '')
			$selectedcategorylist = $categorylistoverride;
		else
			$selectedcategorylist = $options['categorylist'];
			
		if ($excludecategoryoverride != '')
			$excludedcategorylist = $excludecategoryoverride;
		else
			$excludedcategorylist = $options['excludecategorylist'];	
			
		if ($tableoverride != '')
			$overridedisplayastable = $tableoverride;
		else
			$overridedisplayastable = $options['displayastable'];
			
		$genoptions = get_option('LinkLibraryGeneral');
		
		$linklibraryoutput = "";
				
		if (floatval($genoptions['schemaversion']) < "4.6")
		{
			$this->ll_install();
			$genoptions = get_option('LinkLibraryGeneral');
			
			if ($settings == '')
				$options = get_option('LinkLibraryPP1');		
			else
			{
				$settingsname = 'LinkLibraryPP' . $settings;
				$options = get_option($settingsname);
			}
		}
		
		if ($genoptions['debugmode'] == true)
			$linklibraryoutput .= "\n<!-- Library Settings Info:" . print_r($options, TRUE) . "-->\n";
			
		$linklibraryoutput .= $this->PrivateLinkLibrary($options['order'], $options['hide_if_empty'], $options['catanchor'], $selectedshowdescription, $selectedshownotes,
									  $options['showrating'], $options['showupdated'], $selectedcategorylist, $options['show_images'],
									  $options['show_image_and_name'], $options['use_html_tags'], $options['show_rss'], $options['beforenote'],
									  $options['nofollow'], $excludedcategorylist, $options['afternote'], $options['beforeitem'],
									  $options['afteritem'], $options['beforedesc'], $options['afterdesc'], $overridedisplayastable,
									  $options['beforelink'], $options['afterlink'], $options['showcolumnheaders'], $options['linkheader'],
									  $options['descheader'], $options['notesheader'], 	$options['catlistwrappers'], $options['beforecatlist1'], 
									  $options['beforecatlist2'], $options['beforecatlist3'], $options['divorheader'], $options['catnameoutput'],
									  $options['show_rss_icon'], $options['linkaddfrequency'], $options['addbeforelink'], $options['addafterlink'],
									  $options['linktarget'], $options['showcategorydesclinks'], $options['showadmineditlinks'],
									  $options['showonecatonly'], '', $options['defaultsinglecat'], $options['rsspreview'], $options['rsspreviewcount'], 
									  $options['rssfeedinline'], $options['rssfeedinlinecontent'], $options['rssfeedinlinecount'],
									  $options['beforerss'], $options['afterrss'], $options['rsscachedir'], $options['direction'],
									  $options['linkdirection'], $options['linkorder'], $options['pagination'], $options['linksperpage'],
									  $options['hidecategorynames'], $settings, $options['showinvisible'], $options['showdate'], $options['beforedate'],
									  $options['afterdate'], $options['catdescpos'], $options['showuserlinks'], $options['rsspreviewwidth'], $options['rsspreviewheight'],
									  $options['beforeimage'], $options['afterimage'], $options['imagepos'], $options['imageclass'], '', $genoptions['debugmode'],
									  $options['usethumbshotsforimages'], $options['showonecatmode'], $options['dragndroporder'], $options['showname'], $options['displayweblink'],
									  $options['sourceweblink'], $options['showtelephone'], $options['sourcetelephone'], $options['showemail'], $options['showlinkhits'],
									  $options['beforeweblink'], $options['afterweblink'], $options['weblinklabel'], $options['beforetelephone'], $options['aftertelephone'],
									  $options['telephonelabel'], $options['beforeemail'], $options['afteremail'], $options['emaillabel'], $options['beforelinkhits'],
									  $options['afterlinkhits'], $options['emailcommand'], $options['sourceimage'], $options['sourcename'], $genoptions['thumbshotscid'],
									  $options['maxlinks'], $options['beforelinkrating'], $options['afterlinkrating'], $options['showlargedescription'],
									  $options['beforelargedescription'], $options['afterlargedescription'], $options['featuredfirst']); 
			
		return $linklibraryoutput;
	}
	
	
	function conditionally_add_scripts_and_styles($posts){
		if (empty($posts)) return $posts;
		
		$load_jquery = false;
		$load_thickbox = false;
		$load_style = false;
		global $testvar;
		
		$genoptions = get_option('LinkLibraryGeneral');

		if (is_admin()) 
		{
			$load_jquery = false;
			$load_thickbox = false;
			$load_style = false;
		}
		else
		{
			foreach ($posts as $post) {		
				$continuesearch = true;
				$searchpos = 0;
				$settingsetids = array();
				
				while ($continuesearch) 
				{
					$linklibrarypos = stripos($post->post_content, 'link-library ', $searchpos);
					if ($linklibrarypos == false)
					{
						$linklibrarypos = stripos($post->post_content, 'link-library]', $searchpos);
						if ($linklibrarypos == false)
							if (stripos($post->post_content, 'link-library-cats') || stripos($post->post_content, 'link-library-addlink'))
								$load_style = true;
					}
					$continuesearch = $linklibrarypos;
					if ($continuesearch)
					{
						$load_style = true;
						$shortcodeend = stripos($post->post_content, ']', $linklibrarypos);
						if ($shortcodeend)
							$searchpos = $shortcodeend;
						else
							$searchpos = $linklibrarypos + 1;
							
						if ($shortcodeend)
						{
							$settingconfigpos = stripos($post->post_content, 'settings=', $linklibrarypos);
							if ($settingconfigpos && $settingconfigpos < $shortcodeend)
							{
								$settingset = substr($post->post_content, $settingconfigpos + 9, $shortcodeend - $settingconfigpos - 9);
									
								$settingsetids[] = $settingset;
							}
							else if (count($settingsetids) == 0)
							{
								$settingsetids[] = 1;
							}
						}
					}	
				}
			}
			
			if ($settingsetids)
			{
				foreach ($settingsetids as $settingsetid)
				{
					$settingsname = 'LinkLibraryPP' . $settingsetid;
					$options = get_option($settingsname);			
					
					if ($options['showonecatonly'])
					{
						$load_jquery = true;
					}
			
					if ($options['rsspreview'])
					{
						$load_thickbox = true;
					}

					if ($options['publishrssfeed'] == true)			
					{
						global $rss_settings;
						$rss_settings = $settingsetid;
					}	
				}
			}
				
			if ($genoptions['includescriptcss'] != '')
			{
				$pagelist = explode (',', $genoptions['includescriptcss']);
				foreach($pagelist as $pageid) {
					if (is_page($pageid))
					{
						$load_jquery = true;
						$load_thickbox = true;
						$load_style = true;
					}
				}
			}
		}
		
		if ($load_style)
		{		
			global $llstylesheet;
			$llstylesheet = true;
		}
		else
		{
			global $llstylesheet;
			$llstylesheet = false;
		}
	 
		if ($load_jquery)
		{
			wp_enqueue_script('jquery');
		}
			
		if ($load_thickbox)
		{
			wp_enqueue_script('thickbox');
			wp_enqueue_style ('thickbox');
		}
	 
		return $posts;
	}
	


}

$my_link_library_plugin = new link_library_plugin();

?>