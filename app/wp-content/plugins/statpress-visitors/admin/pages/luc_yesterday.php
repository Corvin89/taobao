<?php
function luc_yesterday()
	  { global $wpdb;
        $table_name = $wpdb->prefix . "statpress";
		$action = "yesterday";
	    $visitors_color = "#114477";
		$rss_visitors_color = "#FFF168";
        $pageviews_color = "#3377B6";
        $rss_pageviews_color = "#f38f36";
        $spider_color = "#83b4d8";
		
        $yesterday = gmdate('Ymd', current_time('timestamp') - 86400);
		$pa = luc_page_posts();
		$permalink = luc_permalink();
 	
		$strqry = "SELECT  count(DISTINCT post_name) as number
		                     FROM $wpdb->posts as p
		                     JOIN $table_name as t
							 ON t.urlrequested LIKE CONCAT('%', p.post_name, '%' )
		                     WHERE p.post_status = 'publish' AND (post_type = 'page' OR post_type = 'post') AND date = $yesterday
							 ";
		$NumberPosts = $wpdb->get_var($strqry);
		$NumberDisplayPost = 100;
		$NA = ceil($NumberPosts / $NumberDisplayPost);
		$LimitValueArticles = ($pa * $NumberDisplayPost) - $NumberDisplayPost;
			  			  
		$qry_visitors =requete_yesterday ("DISTINCT ip","urlrequested = ''","spider = '' AND feed = ''",$yesterday);			
	    foreach ($qry_visitors as $url)
				   {$visitors[$url->post_name]=$url->total;
				    $total_visitors += $url->total;
				   }
				   
		$qry_visitors_feeds =requete_yesterday ("DISTINCT ip","(urlrequested LIKE '%".$permalink[0]."feed%' OR urlrequested LIKE '%".$permalink[0]."comment%') ","spider='' AND feed<>''",$yesterday);	
		foreach ($qry_visitors_feeds as $url)
				   {$visitors_feeds[$url->post_name]=$url->total;
				    $total_visitors_feeds += $url->total;
				   }	
				   
		$qry_pageviews=$wpdb->get_results("
			                SELECT post_name, total, urlrequested
					       FROM
						   ((SELECT 'page_accueil' as post_name, count(ip) as total, urlrequested
							         FROM $wpdb->posts as p
									 JOIN $table_name as t ON t.urlrequested ='' 
									 WHERE  p.post_status = 'publish' AND (p.post_type = 'page' OR p.post_type = 'post') AND t.date = $yesterday
									 AND t.spider='' AND t.feed='' 
									 GROUP BY post_name)
                        UNION ALL
						    (SELECT post_name, count(ip) as total, urlrequested
		                     FROM $wpdb->posts as p
		                     JOIN $table_name as t
                             ON t.urlrequested LIKE CONCAT('%', p.post_name, '%' ) 
		                     WHERE p.post_status = 'publish' AND (p.post_type = 'page' OR p.post_type = 'post') AND t.date = $yesterday
							 AND t.spider='' AND t.feed='' 
							 GROUP BY post_name) 
						UNION 
						    (SELECT post_name, NULL as total, urlrequested
							   FROM	$wpdb->posts as p
                               JOIN $table_name as t
							   ON t.urlrequested LIKE CONCAT('%', p.post_name, '%' )
							   WHERE  p.post_status = 'publish' AND (p.post_type = 'page' OR p.post_type = 'post') 
							   GROUP BY post_name)
							) views		 
						GROUP BY post_name 
		                ORDER BY total DESC LIMIT $LimitValueArticles, $NumberDisplayPost");
		foreach ($qry_pageviews as $url)
				   {$pageviews[$url->post_name]=$url->total;
				   $total_pageviews += $url->total;
				   }
				   
		$qry_pageviews_feeds =requete_yesterday ("ip","(urlrequested LIKE '%".$permalink[0]."feed%' OR urlrequested LIKE '%".$permalink[0]."comment%')"," spider='' AND feed<>''",$yesterday);	
		foreach ($qry_pageviews_feeds as $url)
				   {$pageviews_feeds[$url->post_name]=$url->total;
				    $total_pageviews_feeds += $url->total;
				   }
				   
		$spider = get_option('statpressV_not_collect_spider');
        if ($spider =='')			
			   {$qry_spiders =requete_yesterday ("ip","urlrequested=''","spider<>'' AND feed=''",$yesterday);	
				foreach ($qry_spiders as $url)
				   {$spiders[$url->post_name]=$url->total;
				    $total_spiders += $url->total;
			       }
			   }  
		  	  
	    $total_visitors = $wpdb->get_var("SELECT count(DISTINCT ip) AS total
                                   FROM $table_name
                                   WHERE feed='' AND spider='' AND date = $yesterday");
	    $total_visitors_feeds = $wpdb->get_var("SELECT count(DISTINCT ip) as total
                                   FROM $table_name
                                   WHERE feed<>'' AND spider=''   AND date = $yesterday");			
		echo "<div class='wrap'><h2>" . __('Yesterday ', 'statpressV'). gmdate('d M, Y', current_time('timestamp')-86400) ."</div></br>";

		luc_print_pp_pa_link(0,0,$action,$NA,$pa);
			   
		echo "<table class='widefat'>
	<thead><tr>
	<th scope='col'>". __('URL','statpressV'). "</th>
	<th scope='col'><div style='background:$visitors_color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>". __('Visitors','statpressV'). "<br /><font size=1></font></th>
	<th scope='col'><div style='background:$rss_visitors_color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>". __('Visitors Feeds','statpressV'). "<br /><font size=1></font></th>
	<th scope='col'><div style='background:$pageviews_color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>". __('Views','statpressV'). "<br /><font size=1></font></th>
	<th scope='col'><div style='background:$rss_pageviews_color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>". __('Views Feeds','statpressV'). "<br /><font size=1></font></th>";
	if ($spider =='')
	echo "<th scope='col'><div style='background:$spider_color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>". __('Spider','statpressV'). "<br /><font size=1></font></th>";
	echo "</tr></thead>";		   
				   
		echo "<tr>		   
		<th scope='col'>All URL</th>
		<th scope='col'>". __($total_visitors,'statpressV'). "</th>
		<th scope='col'>". __($total_visitors_feeds,'statpressV'). "</th>
		<th scope='col'>". __($total_pageviews,'statpressV'). "</th>
		<th scope='col'>". __($total_pageviews_feeds,'statpressV'). "</th>";
		if ($spider=='')
		echo "<th scope='col'>". __($total_spiders,'statpressV'). "</th>
		      </tr>";
			  
		foreach ($qry_pageviews as $url)
			  {if ($url->urlrequested == '')
				    $out_url = "Page : Home";
			   else  
			        $out_url=$permalink[0].$url->post_name;	   
               echo "<tr>
			         <td>".$out_url  ."</td>"; 
			   echo "<td>" . $visitors[$url->post_name] . "</td>
			   <td>" . $visitors_feeds[$url->post_name] . "</td>
			   <td>" . $pageviews[$url->post_name] . "</td>
			   <td>" . $pageviews_feeds[$url->post_name] . "</td>";
			   if ($spider =='')
			   echo "<td>" . $spiders[$url->post_name]. "</td>";
			   echo "</tr>";
		       };	   
        echo '</table>';
		luc_print_pp_pa_link(0,0,$action,$NA,$pa);
}

 function requete_yesterday ($count,$where_one,$where_two,$yesterday)
	    {   global $wpdb;
            $table_name = $wpdb->prefix . "statpress";
			$qry =$wpdb->get_results("SELECT post_name, total
					       FROM
						   ((SELECT 'page_accueil' AS post_name, count($count) AS total
							         FROM $table_name 
									 WHERE date = $yesterday  
									 AND $where_one 
									 AND $where_two
									 GROUP BY post_name)
                        UNION ALL
						(SELECT post_name, count($count) AS total
		                     FROM $wpdb->posts AS p
		                     JOIN $table_name AS t
                             ON t.urlrequested LIKE CONCAT('%',p.post_name,'%') 
		                     WHERE t.date = $yesterday 
							 AND p.post_status = 'publish' AND (p.post_type = 'page' OR p.post_type = 'post')
							 AND $where_two 
							 GROUP BY p.post_name) 
							) req	 
					    GROUP BY post_name");
		    return $qry;								  
	    }
?>