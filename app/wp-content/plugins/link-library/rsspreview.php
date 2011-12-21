<?php

	ini_set('display_errors', '0');

	require_once('simplepie.inc');
	require_once('../../../wp-load.php');
	
	$linkid = $_GET['linkid'];
	$itemcount = $_GET['previewcount'];
	
	$link = get_bookmark( $linkid );
	
	$feed = new SimplePie();
	
	$feed->set_item_limit($itemcount);
	
	// Use the URL that was passed to the page in SimplePie
	$feed->set_feed_url($link->link_rss);
	
	// XML dump
	$feed->enable_xml_dump(isset($_GET['xmldump']) ? true : false);
	
	// We'll enable the discovering and caching of favicons.
	$feed->set_favicon_handler('./handler_image.php');
	
	$feed->enable_cache(false);
	
	// Initialize the whole SimplePie object.  Read the feed, process it, parse it, cache it, and 
	// all that other good stuff.  The feed's information will not be available to SimplePie before 
	// this is called.
	$success = $feed->init();

	// We'll make sure that the right content type and character encoding gets set automatically.
	// This function will grab the proper character encoding, as well as set the content type to text/html.
	$feed->handle_content_type();
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo (empty($_GET['feed'])) ? 'SimplePie' : 'SimplePie: ' . $feed->get_title(); ?></title>

<!-- META HTTP-EQUIV -->
<meta http-equiv="content-type" content="text/html; charset=<?php echo ($feed->get_encoding()) ? $feed->get_encoding() : 'UTF-8'; ?>" />
<meta http-equiv="imagetoolbar" content="false" />

<style type="text/css">
html, body {
	height:100%;
	margin:0;
	padding:0;
}

div.content{color: #333333; font-family: "Helvetica Neue",Arial,Helvetica,sans-serif;font-size: 12.8px;line-height: 1.25em (16px);
vertical-align: baseline;letter-spacing: normal;word-spacing: normal;font-weight: normal;font-style: normal;font-variant: normal;text-transform: none;
text-decoration: none;text-align: left;text-indent: 0px;}

h1{font-weight:400;background:#F4F5F3 url(icons/content-sep.jpg) repeat-x 0 0;border-bottom:#E2E4E0 1px solid;clear:both;font-size:1.2em;margin:10px -10px;padding:5px 20px;color:#33352C;font-family:Georgia,"Times New Roman",Times,serif;}
h1 a{color:#33352C !important}

a{color:#3D7283;text-decoration:none}
a:hover{color:#000;text-decoration:underline}

div.date{font-size:0.8em;}

pre {
	background-color:#f3f3ff;
	color:#000080;
	border:1px dotted #000080;
	padding:3px 5px;
}

form {
	margin:0;
	padding:0;
}

div.chunk {
	border-bottom:1px solid #ccc;
}

.clear { /* generic container (i.e. div) for floating buttons */
    overflow: hidden;
    width: 100%;
}

a.button {
    background: transparent url('icons/bg_button_a.gif') no-repeat scroll top right;
    color: #444;
    display: block;
    float: left;
    font: normal 12px arial, sans-serif;
    height: 24px;
    margin-right: 6px;
    padding-right: 18px; /* sliding doors padding */
    text-decoration: none;
}

a.button span {
    background: transparent url('icons/bg_button_span.gif') no-repeat;
    display: block;
    line-height: 14px;
    padding: 5px 0 5px 18px;
} 

a.button:active {
    background-position: bottom right;
    color: #000;
    outline: none; /* hide dotted outline in Firefox */
}

a.button:active span {
    background-position: bottom left;
    padding: 6px 0 4px 18px; /* push text down 1px */
} 

</style>

</head>

<body>
	<div id="sp_results">
		<?php if ($feed->data): ?>
			<?php $items = $feed->get_items(0,$itemcount); ?>
			<?php foreach($items as $item): ?>
				<div class="chunk" style="padding:0 5px 5px;">
					<h1><a target="feedwindow" href="<?php echo $item->get_permalink(); ?>"><?php echo $item->get_title(); ?></a><div class='date'><?php echo $item->get_date('j M Y'); ?></div></h1>
					<div class='content'><?php echo $item->get_content(); ?></div>
				</div>
				<br />
			<?php endforeach; ?>
			<br />
			<div>
				<a class="button" target="feedwindow" href="<?php echo $link->link_rss; ?>"><span>More News from this Feed</span></a> <a class="button" target="sitewindow" href="<?php echo $link->link_url; ?>"><span>See Full Web Site</span></a>
			</div>
			<br />
			<br />
		<?php endif; ?>
	</div>
</body>
</html>

<?php ini_set('display_errors', '1'); ?>