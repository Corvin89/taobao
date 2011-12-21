<?php

/*
 * odl_search.php
 * wordpress plugin website directory project
 * @author Mohammad Forgani
 * @copyright Copyright 2008, Oh Jung-Su
 * @version 0.2
 * @link http://www.forgani.com
 */


function odlinksdisplay_search(){
	global $_POST, $table_prefix, $wpdb, $PHP_SELF, $odl_lang;
	
	$odlinkssettings = get_option('odlinksdata');
	
	$tpl = new ODLTemplate();
	$tpl->assign('lang', $odl_lang);
	$tpl->assign('odl_images', get_bloginfo('wpurl') . "/wp-content/plugins/odlinks");
	$odl_main_link = odlinkscreate_link("index", array("name"=>"Main"));
	$results_limit = 10;
	$tpl->assign('odl_lang', $odl_lang);
	$tpl->assign('results_limit', $results_limit);
	$tpl->assign('odl_main_link', $odl_main_link); 
	$tpl->assign('odl_wpurl', get_bloginfo('url'));

	$type = $_POST['type'];
	$search_terms =  stripslashes($_POST['search_terms']);
	$search_terms = addslashes(htmlspecialchars($search_terms));
	$links=array();

	$tpl->assign('search_terms',$search_terms);
	if( empty($search_terms) ){
		$tpl->assign('odl_search_error', $odl_lang['ODL_EMPTYSEARCH']);
	} else {
		if($type == "links"){
			$sql = "SELECT * FROM {$table_prefix}odlinks WHERE l_url like '%".$search_terms."%'"; 
			$results = $wpdb->get_results($sql);
			if(!$results){
				$tpl->assign('odl_search_error', $odl_lang['ODL_NOPOSTSEARCH']);
			} else {
				for ($i=0; $i<count($results); $i++){
					$result=$results[$i];
					$PR=new ODLPagerank();
					$rankImage=false;
					$Url=trim($result->l_url);
					if (($Url!='') AND ($Url!='http://')) {
						if (isUrlValid($Url)) {
							$google_rank=$PR->getRank($Url);
							if ($google_rank == '-1') $google_rank='0';
							$rankImage='pr'.$google_rank.".gif";
							$rankText= $odl_lang['ODL_PAGERANK'] . $google_rank .'/10';
						}
					}
					$sendlinkurl=odlinkscreate_link("sendlink", array("name"=>$odl_lang['ODL_SENDTOF'], "id"=>$result->l_id));
					$links[]=array ('title'=>$result->l_title, 'url'=>$Url, 'date'=>$result->l_date, 'description'=>$result->l_description, 'sendlink'=>$sendlinkurl, 'rank_img'=>$rankImage, 'rank_txt'=>$rankText); 
				}
			}
		} elseif($type == "desc"){
			$sql = "SELECT * FROM {$table_prefix}odlinks WHERE l_title like '%".$search_terms."%' OR l_description like '%".$search_terms."%'"; 
			$results = $wpdb->get_results($sql);
			if(!$results){
				$tpl->assign('odl_search_error', $odl_lang['ODL_NOPOSTSEARCH']);
			} else {
				for ($i=0; $i<count($results); $i++){
					$result=$results[$i];
					$PR=new ODLPagerank();
					$rankImage=false;
					$Url=trim($result->l_url);
					if (($Url!='') AND ($Url!='http://')) {
						if (isUrlValid($Url)) {
							$google_rank=$PR->getRank($Url);
							
							if ($google_rank == '-1') $google_rank='0';
							$rankImage='pr'.$google_rank.".gif";
							$rankText= $odl_lang['ODL_PAGERANK'] . $google_rank .'/10';
						}
					}
					$sendlinkurl=odlinkscreate_link("sendlink", array("name"=>$odl_lang['ODL_SENDTOF'], "id"=>$result->l_id));
					$links[]=array ('title'=>$result->l_title, 'url'=>$Url, 'date'=>$result->l_date, 'description'=>$result->l_description, 'sendlink'=>$sendlinkurl, 'rank_img'=>$rankImage, 'rank_txt'=>$rankText); 
				}
			}
		} else {
			$sql = "SELECT * FROM {$table_prefix}odlinks WHERE l_description like '%". $search_terms."%' OR l_url like '%" . $search_terms. "%' OR l_title like '%" . $search_terms . "%'";
			$results = $wpdb->get_results($sql);
			$results = $wpdb->get_results($sql);
			if(!$results){
				$tpl->assign('odl_search_error', $odl_lang['ODL_NOPOSTSEARCH']);
			} else {
				for ($i=0; $i<count($results); $i++){
					$result=$results[$i];
					$PR=new ODLPagerank();
					$rankImage=false;
					$Url=trim($result->l_url);
										
					if (($Url!='') AND ($Url!='http://')) {
						if (isUrlValid($Url)) {
							$google_rank=$PR->getRank($Url);
							if ($google_rank == '-1') $google_rank='0';
							$rankImage='pr'.$google_rank.".gif";
							$rankText= $odl_lang['ODL_PAGERANK'] . $google_rank .'/10';
						}
					}
					$sendlinkurl=odlinkscreate_link("sendlink", array("name"=>$odl_lang['ODL_SENDTOF'], "id"=>$result->l_id));
					$links[]=array ('title'=>$result->l_title, 'url'=>$Url, 'date'=>$result->l_date, 'description'=>$result->l_description, 'sendlink'=>$sendlinkurl, 'rank_img'=>$rankImage, 'rank_txt'=>$rankText); 
				}
			}
		}
	}

	$tpl->assign('links', $links);
	$tpl->assign('results_num', count($results));

	$result=$wpdb->get_results("SELECT * FROM {$table_prefix}odcategories"); 
	$tpl->assign('categories_total', count($result)); 
	$results=$wpdb->get_results("SELECT * FROM {$table_prefix}odlinks WHERE l_hide ='visible'");
	$tpl->assign("links_total", count($results)); 

	odlinks_get_footer($tpl);
	$tpl->display('search.tpl');

}
?>
