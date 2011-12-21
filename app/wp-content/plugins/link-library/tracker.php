<?php
	require_once('../../../wp-load.php');
	require_once('link-library.php');
	
	$link_id = $_POST['id'];
	echo "Received ID is: " . $link_id;
	
	global $wpdb;
	
	$extradatatable = $wpdb->prefix . "links_extrainfo";	
	$linkextradataquery = "select * from " . $wpdb->prefix . "links_extrainfo where link_id = " . $link_id;
	$extradata = $wpdb->get_row($linkextradataquery, ARRAY_A);
	
	if ($extradata)
	{
		$newcount = $extradata['link_visits'] + 1;
		$wpdb->update( $extradatatable, array( 'link_visits' => $newcount ), array( 'link_id' => $link_id ));
		echo "Updated row";
	}
	else
	{
		$wpdb->insert( $extradatatable, array( 'link_id' => $link_id, 'link_visits' => 1 ));
		echo "Inserted new row";
	}
	
?>