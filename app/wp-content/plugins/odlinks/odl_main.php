<?php

/*
 * odl_main.php
 * @author Mohammad Forgani
 * wordpress plugin website directory project
 * @copyright Copyright 2008, Oh Jung-Su
 * @version 1.1.2-d
 * 2009-10-23 fixed for wp 2.8.5
 * @link http://www.forgani.com
*/

include_once(ABSPATH . 'wp-content/plugins/odlinks/includes/pagerank.php');
//

function odlinksdisplay_index($msg){
	global $_GET, $_POST, $table_prefix, $wpdb, $PHP_SELF, $odl_lang, $odlinksversion;
	$odlinkssettings=get_option('odlinksdata');

	if(!isset($page)) $page=1;
	$id = 0;
	if($_GET['id']*1 > 0) $id=$_GET['id'];
	/*
	$limit=$odlinkssettings['odlinks_num_links'];
	if (!isset($limit) or $limit ==0) $limit = 10;
	*/
	odl_cleanUp();

	$tpl=new ODLTemplate();
	if ( strlen($odlinkssettings['odlinks_top_image']) > 3 ) {
		$img = plugins_url('odlinks') . '/images/' . $odlinkssettings['odlinks_top_image'];
		$tpl->assign('top_image', '<img src="' . $img . '">' );
	}
	$tpl->assign('odl_images', plugins_url('odlinks'));
	$tpl->assign('odl_lang', $odl_lang);
	$odl_search_link=odlinkscreate_link("searchform", array());
	$tpl->assign('odl_search_link', $odl_search_link);
	$odl_main_link=odlinkscreate_link("index", array("name"=>"TOP"));
	$tpl->assign('odl_main_link', $odl_main_link); 
	$tpl->assign('odl_wpurl', plugins_url('odlinks'));
	if ($msg) $tpl->assign('error',$msg);

	$tpl->assign('cat_id',$id); 
	list($navigationLinks, $addurl, $desc)=odl_create_navigation($id,"","","");
	$tpl->assign('addurl_link', $addurl);
	$navigationLinks=trim($navigationLinks);
	$last=$navigationLinks{strlen($navigationLinks)-1};
	if (!strcmp($last,":")){
		$navigationLinks=rtrim($navigationLinks, ':');
	}
	$tpl->assign('navigation_link', $navigationLinks); 
	$tpl->assign('odl_navigation_description', $desc); 
	$sql="SELECT COUNT(l_id) as count FROM {$table_prefix}odlinks WHERE l_c_id='".$id."' AND l_hide ='visible'";
	$result=$wpdb->get_results($sql);
	$sql="SELECT * FROM {$table_prefix}odlinks WHERE l_c_id='".$id."' AND l_hide ='visible'";
	$results=$wpdb->get_results($sql); 
	$links=array();
	for ($i=0; $i<count($results); $i++){
		$result=$results[$i];

		$PR=new ODLPagerank();
		$rankImage=false;
		$Url=trim($result->l_url);
		if (($Url!='') AND ($Url!='http://')) {
			if (isUrlValid($Url)) {
				$PageRank=$PR->getRank($Url);
				if ($PageRank == '-1') $PageRank='0';
				$rankImage='pr'.$PageRank.".gif";
				$rankText= $odl_lang['ODL_PAGERANK'] . $PageRank .'/10';
			}
		}
		$sendlinkurl=odlinkscreate_link("sendlink", array("name"=>$odl_lang['ODL_SENDTOF'], "id"=>$result->l_id));
		$links[]=array('title'=>$result->l_title, 'url'=>$Url, 'date'=>$result->l_date, 'description'=>$result->l_description, 'sendlink'=>$sendlinkurl, 'rank_img'=>$rankImage, 'rank_txt'=>$rankText); 
	}
	
 	if ($id > 0) $tpl->assign('links', $links); 
	$tpl->assign("links_total", number_format(odl_total_links(0))); 

	$result_cats=$wpdb->get_results("SELECT * FROM {$table_prefix}odcategories ORDER BY c_title ASC"); 
	$sub_cats=array();
	$cats=array();
	$c_total=count($result_cats);
	if ($c_total*1 > 0) {
	  foreach ($result_cats as $result_cat) { 
		if($result_cat->c_parent == $id){
			$result_cat->c_links=odl_total_links($result_cat->c_id);
			$title=trim($result_cat->c_title);
			$odl_category_link=odlinkscreate_link("category", array("name"=>$title, "id"=>$result_cat->c_id, "parent"=>$result_cat->c_parent));
			$cats[]=array(
				'c_id'=>$result_cat->c_id,
				'c_title'=>$title,
				'c_links'=>$result_cat->c_links ,
				'cat_link'=>$odl_category_link);
			$sql="SELECT * FROM {$table_prefix}odcategories WHERE c_parent=".$result_cat->c_id." ORDER BY c_title ASC";
			$result_subs=$wpdb->get_results($sql);
			if (!empty($result_subs)){
				$no=0;
				foreach ($result_subs as $result_sub) {	
					$no++;
					$title=trim($result_sub->c_title);
					$subcategory_link=odlinkscreate_link("category", array("name"=>$title, "id"=>$result_sub->c_id,"parent"=>$result_sub->c_parent));
					$lnk=odl_total_links($result_sub->c_id);
					if ( !($result_sub == end($result_subs)) ) $subcategory_link=$subcategory_link;
					$sub_cats[]=array (
						'c_parent'=>$result_sub->c_parent,
						'c_title'=>$title,
						'c_links'=>$lnk,
						'c_path'=>$subcategory_link,
						'c_no'=>$no); 
					};
			}
		}
	  }
	}
	$tpl->assign('categories_total', number_format($c_total)); 
	$tpl->assign('categories',$cats); 
	$tpl->assign('subcategories',$sub_cats); 

	odlinks_get_footer($tpl);

	include_once ( ODL_PLUGIN_DIR . '/includes/odl_rss.php');

	return $tpl->display('body.tpl'); 
}


function odl_create_link_by_title($title){
	global $table_prefix, $wpdb, $odl_lang;
	$sql="SELECT * FROM {$table_prefix}odcategories WHERE c_title='$title' ";
	$result=$wpdb->get_row($sql, ARRAY_A); 
	$name=trim($result['c_title']);
	$odl_link=odlinkscreate_link("category", array('name'=>$name, 'id'=>$result['c_id'], 'parent'=>$result['c_parent']));
	return $odl_link;
}



function odlinks_get_footer($tpl) {
	global $wpdb, $table_prefix, $odl_lang, $odlinksversion;
	$odlinkssettings=get_option('odlinksdata');

	// footer
	if (!$odlinkssettings['odlinks_last_links_num']) $odlinkssettings['odlinks_last_links_num'] = 8;
	$start=0;
	$tpl->assign("linksNum", $odlinkssettings['odlinks_last_links_num']); 
	$sql="SELECT * FROM {$table_prefix}odlinks l, {$table_prefix}odcategories c WHERE l.l_c_id = c.c_id AND l.l_hide='visible' ORDER BY l.l_date DESC, l.l_title DESC LIMIT ".($start).", ".($odlinkssettings['odlinks_last_links_num']);
	$lastAds=$wpdb->get_results($sql);
	$new_links=array();
	for ($l=0; $l<count($lastAds); $l++){
		$result=$lastAds[$l];
		$titleLink = odl_create_link_by_title($result->c_title);
		$new_links[]=array ('date'=>$result->l_date, 'title'=>$result->l_title, 'url'=>$result->l_url, 'description'=>$result->l_description, 'category'=>$titleLink); 
	}
 	$tpl->assign('new_links', $new_links); 

	// end of footer

	list($gAd, $gtop, $gbtn)=get_odl_GADlink();
	if ($gAd) {
		$code='<div class="odl_googleAd">' . $gAd . '</div>';
		$tpl->assign('googletop',$gtop); 
		$tpl->assign('googlebtn',$gbtn); 
		$tpl->assign('googleAd',$code); 
	}

	$filename = ODL_PLUGIN_URL . '/includes/Smarty/cache/odlinks.xml';
	?>
	<script type="text/javascript">
    <!--
		function pop (file,name){
		rsswindow = window.open (file,name,"location=1,status=1,scrollbars=1,width=680,height=800");
		rsswindow.moveTo(0,0);
		rsswindow.focus();
		return false;
		}
    //  end script hiding -->
	</script> 
	<?php

	$rssLink = '<div class="odl_footer"><img src="' . ODL_PLUGIN_URL . '/images/rss.png" />';
	$rssLink .= '<b><a href="'. $filename . '" target="_blank" onclick="return pop('.$filename.',' .  $odlinkssettings['odlinksslug'] . ');">' . $odlinkssettings['page_link_title'] . ' RSS </a></b><br />';
	$odl_top_link=odlinkscreate_link("index", array("name"=>$odlinkssettings['page_link_title']));

  if ($odlinkssettings['odlinksshow_credits'] == 'y') {
      $credit='Open Directory Links Powered By <a href="http://www.forgani.com/" target="_blank">4gani</a>';
      $rssLink .= '<span class="smallTxt">&nbsp;' . $credit . ' (v. '.$odlinksversion.', 2009. All rights reserved.)</span>';
   } 
	 
	$rssLink .= '</div>';
	$odlFbLike = odlFbLike();
	$tpl->assign('odlFbLike', $odlFbLike);
	$tpl->assign('rssLink', $rssLink);

}


function get_odl_GADlink() {
	global $_GET, $_POST, $table_prefix, $wpdb, $odl_lang, $PHP_SELF, $odlinksversion;
	$odlinkssettings=get_option('odlinksdata');

	$gtop=false;
	$gbtn=false;
	if ($odlinkssettings['GADposition'] == 'bth') {
		$gtop=true;
		$gbtn=true;
	} else {
		if ($odlinkssettings['GADposition'] == 'top') {
			$gtop=true;
		} elseif ($odlinkssettings['GADposition'] == 'btn') {
			$gbtn=true;
		}
	}

	if ($gtop || $gbtn){
		$rand=rand(0,100);
		$key_code=($rand <= $odlinkssettings['share']) ? 'pub-xxxxxx' : $odlinkssettings['googleID'];
		$format=$odlinkssettings['GADLformat'] . '_0ads_al'; // _0ads_al_s  5 Ads Per Unit
		$vales= preg_split('/x/',$odlinkssettings['GADLformat']);
		$code="\n" . '<script type="text/javascript"><!--' . "\n";
		$code.= 'google_ad_client="' . $key_code . '"; ' . "\n";
		$code.= 'google_ad_width="' . $vales[0] . '"; ' . "\n";
		$code.= 'google_ad_height="' . $vales[1] . '"; ' . "\n";
		$code.= 'google_ad_format="' . $format . '"; ' . "\n";
		if(isset($settings['alternate_url']) && $settings['alternate_url'] !=''){ 
			$code.= 'google_alternate_ad_url="' . $settings['alternate_url'] . '"; ' . "\n";
		} else {
			if(isset($settings['alternate_url']) && $settings['alternate_color'] !='') { 
				$code.= 'google_alternate_color="' . $settings['alternate_color'] . '"; ' . "\n";
			}
		}				
		//Default to Ads
		$code.= 'google_color_border="' . $odlinkssettings['GADcolor_border'] . '"' . ";\n";
		$code.= 'google_color_bg="' . $odlinkssettings['GADcolor_bg'] . '"' . ";\n";
		$code.= 'google_color_link="' . $odlinkssettings['GADcolor_link'] . '"' . ";\n";
		$code.= 'google_color_text="' . $odlinkssettings['GADcolor_text'] . '"' . ";\n";
		$code.= 'google_color_url="' . $odlinkssettings['GADcolor_url'] . '"' . ";\n";
		$code.= '//--></script>' . "\n";
		$code.= '<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>' . "\n";
		
		return array($code, $gtop, $gbtn);
	}
	return false;
}


function odl_create_navigation($id, $links, $addurl, $desc){
	global $table_prefix, $wpdb, $odl_lang, $tpl;
	$again="";
	$sql="SELECT * FROM {$table_prefix}odcategories WHERE c_id=".$id;
	$result=$wpdb->get_results($sql); 
	for ($i=0; $i<count($result); $i++){
		$row=$result[$i];
		$name=trim($row->c_title);
		$odl_link=odlinkscreate_link("category", array('name'=>$name, 'id'=>$row->c_id, 'parent'=>$row->c_parent));
		$links=$odl_link . ":" . $links;
		//if ($row->c_parent>0)
		$addurl=odlinkscreate_link("postlink", array('name'=>$odl_lang['ODL_SUBMITSITE'], 'id'=>$row->c_id, "parent"=>$row->c_parent));
		$again=odl_create_navigation($row->c_parent, $links, $addurl, $row->c_description);
	}
 	if ($id <> "0") {
		return $again;
	} else {
		$out=array($links, $addurl, $desc);
		return $out;
	}
}

function odl_total_links($cat_id){
	global $table_prefix, $odl_lang, $wpdb;
	$out=""; 
	$all_cats=odl_get_categories($cat_id); 
	for($a=0;$a<=count($all_cats)-1;$a++){
		$out .= $all_cats[$a].","; 
	}
	$sql="SELECT COUNT(l_id) as count FROM {$table_prefix}odlinks WHERE l_hide='visible' AND l_c_id IN (".trim($out,',').")";
	$result=$wpdb->get_row($sql, ARRAY_A); 
	return $result['count']; 
}

function odl_get_categories($parent) {
	global $table_prefix, $wpdb;
	$sql="SELECT c_id FROM {$table_prefix}odcategories WHERE c_parent='$parent'";
	$result=$wpdb->get_results($sql); 
	$arr[]=$parent; 
	for ($x=0; $x<count($result); $x++){
		$row=$result[$x];
		$arr=odl_combine_arrays($arr, odl_get_categories($row->c_id)); 
	}
 	return $arr; 
}

function odl_combine_arrays($arr1, $arr2) {
	foreach ($arr2 as $elem) {
		$arr1[]=$elem; 
	}
	return $arr1; 
}


function odl_html2text( $badStr ) {
    //remove PHP if it exists
    while( substr_count( $badStr, '<'.'?' ) && substr_count( $badStr, '?'.'>' ) && strpos( $badStr, '?'.'>', strpos( $badStr, '<'.'?' ) ) > strpos( $badStr, '<'.'?' ) ) {
        $badStr=substr( $badStr, 0, strpos( $badStr, '<'.'?' ) ) . substr( $badStr, strpos( $badStr, '?'.'>', strpos( $badStr, '<'.'?' ) ) + 2 ); }
    //remove comments
    while( substr_count( $badStr, '<!--' ) && substr_count( $badStr, '-->' ) && strpos( $badStr, '-->', strpos( $badStr, '<!--' ) ) > strpos( $badStr, '<!--' ) ) {
        $badStr=substr( $badStr, 0, strpos( $badStr, '<!--' ) ) . substr( $badStr, strpos( $badStr, '-->', strpos( $badStr, '<!--' ) ) + 3 ); }
    //now make sure all HTML tags are correctly written (> not in between quotes)
    for( $x=0, $goodStr='', $is_open_tb=false, $is_open_sq=false, $is_open_dq=false; strlen( $chr=$badStr{$x} ); $x++ ) {
        //take each letter in turn and check if that character is permitted there
        switch( $chr ) {
            case '<':
                if( !$is_open_tb && strtolower( substr( $badStr, $x + 1, 5 ) ) == 'style' ) {
                    $badStr=substr( $badStr, 0, $x ) . substr( $badStr, strpos( strtolower( $badStr ), '</style>', $x ) + 7 ); $chr='';
                } elseif( !$is_open_tb && strtolower( substr( $badStr, $x + 1, 6 ) ) == 'script' ) {
                    $badStr=substr( $badStr, 0, $x ) . substr( $badStr, strpos( strtolower( $badStr ), '</script>', $x ) + 8 ); $chr='';
                } elseif( !$is_open_tb ) { $is_open_tb=true; } else { $chr='&lt;'; }
                break;
            case '>':
                if( !$is_open_tb || $is_open_dq || $is_open_sq ) { $chr='&gt;'; } else { $is_open_tb=false; }
                break;
            case '"':
                if( $is_open_tb && !$is_open_dq && !$is_open_sq ) { $is_open_dq=true; }
                elseif( $is_open_tb && $is_open_dq && !$is_open_sq ) { $is_open_dq=false; }
                else { $chr='&quot;'; }
                break;
            case "'":
                if( $is_open_tb && !$is_open_dq && !$is_open_sq ) { $is_open_sq=true; }
                elseif( $is_open_tb && !$is_open_dq && $is_open_sq ) { $is_open_sq=false; }
        } $goodStr .= $chr;
    }
    //now that the page is valid (I hope) for strip_tags, strip all unwanted tags
    $goodStr=strip_tags( $goodStr, '<title><hr><h1><h2><h3><h4><h5><h6><div><p><pre><sup><ul><ol><br><dl><dt><table><caption><tr><li><dd><th><td><a><area><img><form><input><textarea><button><select><option>' );
    //strip extra whitespace except between <pre> and <textarea> tags
    $badStr=preg_split( "/<\/?pre[^>]*>/i", $goodStr );
    for( $x=0; is_string( $badStr[$x] ); $x++ ) {
        if( $x % 2 ) { $badStr[$x]='<pre>'.$badStr[$x].'</pre>'; } else {
            $goodStr=preg_split( "/<\/?textarea[^>]*>/i", $badStr[$x] );
            for( $z=0; is_string( $goodStr[$z] ); $z++ ) {
                if( $z % 2 ) { $goodStr[$z]='<textarea>'.$goodStr[$z].'</textarea>'; } else {
                    $goodStr[$z]=preg_replace( "/\s+/", ' ', $goodStr[$z] );
            } }
            $badStr[$x]=implode('',$goodStr);
    } }
    $goodStr=implode('',$badStr);
    //remove all options from select inputs
    $goodStr=preg_replace( "/<option[^>]*>[^<]*/i", '', $goodStr );
    //replace all tags with their text equivalents
    $goodStr=preg_replace( "/<(\/title|hr)[^>]*>/i", "\n-----------------------------------------------\n", $goodStr );
    $goodStr=preg_replace( "/<(h|div|p)[^>]*>/i", "\n\n", $goodStr );
    $goodStr=preg_replace( "/<sup[^>]*>/i", '^', $goodStr );
    $goodStr=preg_replace( "/<(ul|ol|br|dl|dt|table|caption|\/textarea|tr[^>]*>\s*<(td|th))[^>]*>/i", "\n", $goodStr );
    $goodStr=preg_replace( "/<li[^>]*>/i", "\n· ", $goodStr );
    $goodStr=preg_replace( "/<dd[^>]*>/i", "\n\t", $goodStr );
    $goodStr=preg_replace( "/<(th|td)[^>]*>/i", "\t", $goodStr );
    $goodStr=preg_replace( "/<a[^>]* href=(\"((?!\"|#|javascript:)[^\"#]*)(\"|#)|'((?!'|#|javascript:)[^'#]*)('|#)|((?!'|\"|>|#|javascript:)[^#\"'> ]*))[^>]*>/i", "[LINK: $2$4$6] ", $goodStr );
    $goodStr=preg_replace( "/<img[^>]* alt=(\"([^\"]+)\"|'([^']+)'|([^\"'> ]+))[^>]*>/i", "[IMAGE: $2$3$4] ", $goodStr );
    $goodStr=preg_replace( "/<form[^>]* action=(\"([^\"]+)\"|'([^']+)'|([^\"'> ]+))[^>]*>/i", "\n[FORM: $2$3$4] ", $goodStr );
    $goodStr=preg_replace( "/<(input|textarea|button|select)[^>]*>/i", "[INPUT] ", $goodStr );
    //strip all remaining tags (mostly closing tags)
    $goodStr=strip_tags( $goodStr );
    //convert HTML entities
    $goodStr=strtr( $goodStr, array_flip( get_html_translation_table( HTML_ENTITIES ) ) );
    preg_replace( "/&#(\d+);/me", "chr('$1')", $goodStr );
    //wordwrap
    $goodStr=wordwrap( $goodStr );
    //make sure there are no more than 3 linebreaks in a row and trim whitespace
    return preg_replace( "/^\n*|\n*$/", '', preg_replace( "/[ \t]+(\n|$)/", "$1", preg_replace( "/\n(\s*\n){2}/", "\n\n\n", preg_replace( "/\r\n?|\f/", "\n", str_replace( chr(160), ' ', $goodStr ) ) ) ) );
}

function isUrlValid($Url) {
	return (strpos(strtolower($Url),'http://')===0);
}

function odl_cleanUp() {
	$deleteTimeDiff= 5 * 60; // second

	$cache = ODL_PLUGIN_DIR . '/includes/Smarty/cache/';
	if ( !($dh = opendir( $cache )) )
		echo 'Unable to open cache directory "' . $cache . '"';

	while ( $file = readdir($dh) ) {
		if ( ($file != '.') && ($file != '..') ) {
			$file2 = $cache . $file;
			if (isset($file2) && is_file($file2)) {
				$diff = mktime() - @filemtime($file2);
				if ($diff > $deleteTimeDiff) @unlink( $file2 );
			}
		}
	}
}

function odlRssFilter($text){echo convert_chars(ent2ncr($text));} 

function odlRssLink($vars) {
	global $wpdb, $table_prefix, $wp_rewrite;
	$odlinkssettings=get_option('odlinksdata');
	$pageinfo = odlinksget_pageinfo();
	if($wp_rewrite->using_permalinks()) $delim = "?";
	else $delim = "&amp;";
	$perm = get_permalink($pageinfo['ID']);
	$main_link = $perm.$delim;

	return $main_link."_action=main&amp;id=".$vars["id"]."&amp;parent=".$vars['parent'];
}


function odlFbLike() {
  $odlinkssettings=get_option('odlinksdata');
  $layout = 'standard'; // button_count standard
  $show_faces = 'false'; // TODO
  $font = 'arial';
  $colorscheme = 'light'; // dark
  $action = 'like'; //  recommend
  $width = '450';
  $height = '25';
  $perm = get_permalink($pageinfo['ID']);
  $url = get_bloginfo('wpurl').'/?page_id=' . $pageinfo["ID"];
  $permalink = urlencode($url);
  $output = '<div style="margin:5px 0">';
  $output .= '<iframe src="http://www.facebook.com/plugins/like.php?href='.str_replace('&', '&amp;', $url).'&amp;layout='.$layout.'&amp;show_faces='.$show_faces.'&amp;width='.$width.'&amp;action='.$action.'&amp;font='.$font.'&amp;colorscheme='.$colorscheme.'" scrolling="no" frameborder="0" allowTransparency="true" style="border:none; overflow:hidden; width:'.$width.'px; height:'.$height.'px"></iframe>';
  return $output . '</div>';
}

?>