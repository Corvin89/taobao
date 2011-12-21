<?php
/*
Plugin Name: Chunk Urls for WordPress
Plugin URI: http://www.village-idiot.org/archives/2006/06/29/wp-chunk/
Description: This plugin shorten urls in comments so that they won't break your site.
Author: whoo
Version: 2.0
Author URI: http://www.village-idiot.org/

Changes:

08/01/06 Version 2.0

	Major Change-- Updated for usage in WordPress 2.04.
	Minor Change-- Added commenting for folks that want to adjust where a url is truncated.			

07/07/06 Version 1.2
	
	Major Change-- Incorporated my fix for WordPress Bug 2889 into the plugin.

06/29/06: Version 1.0

	Initial release.


*/

function make_chunky($ret)
{
	
	// pad it with a space
	$ret = ' ' . $ret;
	$ret = preg_replace("#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "$1<a href='$2' rel='nofollow'>$2</a>", $ret);
	$ret = preg_replace("#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "$1<a href='http://$2' rel='nofollow'>$2</a>", $ret);
	//chunk those long urls
	chunk_url($ret);
	$ret = preg_replace("#(\s)([a-z0-9\-_.]+)@([^,< \n\r]+)#i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", $ret);	
	// Remove our padding..
	$ret = substr($ret, 1);
	return($ret);
}


function chunk_url(&$ret)
{
   
   $links = explode('<a', $ret);
   $countlinks = count($links);
   for ($i = 0; $i < $countlinks; $i++)
   {
      $link = $links[$i];
      
      
      $link = (preg_match('#(.*)(href=")#is', $link)) ? '<a' . $link : $link;

      $begin = strpos($link, '>') + 1;
      $end = strpos($link, '<', $begin);
      $length = $end - $begin;
      $urlname = substr($link, $begin, $length);
      
      /**
       * We chunk urls that are longer than 50 characters. Just change
       * '50' to a value that suits your taste. We are not chunking the link
       * text unless if begins with 'http://', 'ftp://', or 'www.'
       */
$chunked = (strlen($urlname) > 50 && preg_match('#^(http://|ftp://|www\.)#is', $urlname)) ? substr_replace($urlname, '.....', 30, -10) : $urlname;
$ret = str_replace('>' . $urlname . '<', '>' . $chunked . '<', $ret); 

   }
} 
remove_filter('comment_text', 'make_clickable');
add_filter('comment_text', 'make_chunky');
add_filter('the_excerpt', 'make_chunky');
add_filter('the_content', 'make_chunky');
?>