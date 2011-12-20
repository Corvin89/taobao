<?php
	require_once('../../../wp-load.php');
	require_once('link-library.php');

	$name = $_GET['name'];
	$url = $_GET['url'];
	$mode = $_GET['mode'];
	$cid = $_GET['cid'];
	$filepath = $_GET['filepath'];
	$linkid = $_GET['linkid'];
	
	echo $my_link_library_plugin->ll_get_link_image($url, $name, $mode, $linkid, $cid, $filepath);
?>