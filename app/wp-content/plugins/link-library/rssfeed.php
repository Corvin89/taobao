<?php 
	require_once('../../../wp-load.php');
	require_once("rss.genesis.php");
	
	global $wpdb;
	
	$settingsetid = $_GET['settingset'];
	
	$settingsname = 'LinkLibraryPP' . $settingsetid;
	$options = get_option($settingsname);
	
	$rss = new rssGenesis();
	
	if ( !defined('WP_CONTENT_DIR') )
		define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
		
	// Guess the location
	$llpluginpath = WP_CONTENT_URL.'/plugins/link-library/';
	
	$feedtitle = ($options['rssfeedtitle'] == "" ? "Link Library Generated Feed" : $options['rssfeedtitle']);
	$feeddescription = ($options['rssfeeddescription'] == "" ? "Link Library Generated Feed Description" : $options['rssfeeddescription']);
	
	// CHANNEL
	$rss->setChannel (
                                  $feedtitle, // Title
                                  $llpluginpath . 'rssfeed.php?settingset=' . $settingsetid, // Link
                                  $feeddescription, // Description
                                  null, // Language
                                  null, // Copyright
                                  null, // Managing Editor
                                  null, // WebMaster
                                  null, // Rating
                                  "auto", // PubDate
                                  "auto", // Last Build Date
								  "Link Library Links", // Category
                                  null, // Docs
								  null, // Time to Live
                                  null, // Skip Days
                                  null // Skip Hours
                                );
								
	$linkquery = "SELECT distinct *, UNIX_TIMESTAMP(l.link_updated) as link_date, ";
	$linkquery .= "IF (DATE_ADD(l.link_updated, INTERVAL " . get_option('links_recently_updated_time') . " MINUTE) >= NOW(), 1,0) as recently_updated ";
	$linkquery .= "FROM " . $wpdb->prefix . "terms t ";
	$linkquery .= "LEFT JOIN " . $wpdb->prefix . "term_taxonomy tt ON (t.term_id = tt.term_id) ";
	$linkquery .= "LEFT JOIN " . $wpdb->prefix . "term_relationships tr ON (tt.term_taxonomy_id = tr.term_taxonomy_id) ";
	$linkquery .= "LEFT JOIN " . $wpdb->prefix . "links l ON (tr.object_id = l.link_id) ";
	$linkquery .= "WHERE tt.taxonomy = 'link_category' ";
	
	if ($options['hide_if_empty'])
		$linkquery .= "AND l.link_id is not NULL AND l.link_description not like '%LinkLibrary:AwaitingModeration:RemoveTextToApprove%' ";
			
	if ($options['categorylist'] != "")
		$linkquery .= " AND t.term_id in (" . $options['categorylist']. ")";
		
	if ($options['excludecategorylist'] != "")
		$linkquery .= " AND t.term_id not in (" . $options['excludecategorylist'] . ")";

	if ($options['showinvisible'] == false)
		$linkquery .= " AND l.link_visible != 'N'";	
	
	$linkquery .= " ORDER by link_date DESC";
		
	$linkquery .= " LIMIT 0, " . $options['numberofrssitems'];
	
	$linkitems = $wpdb->get_results($linkquery);
	
	if ($linkitems)
	{
		foreach ($linkitems as $linkitem)
		{
			// ITEM
			$rss->addItem (
                             $linkitem->link_name, // Title
                             $linkitem->link_url, // Link
                             $linkitem->link_description, // Description
							 $linkitem->link_updated, //Publication Date
							 $linkitem->name // Category							 
                           );
		
		}
	}
	
	echo $rss->getFeed();	
	
?>
