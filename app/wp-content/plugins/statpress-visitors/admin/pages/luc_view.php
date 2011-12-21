<?php
function luc_view()
        {   global $wpdb;
            $table_name = $wpdb->prefix . "statpress";
            $pageviews_color = "#3377B6";
		    $action="views";
			$graphdays = get_option('statpressV_days_graph');
            if ($graphdays == 0)
              $graphdays = 7;
		    
			// $pa = pa and $pp = pp in the slug
			$pa = luc_page_posts();
			$pp = luc_page_periode();
			$today = gmdate('Ymd', current_time('timestamp'));
			$limitdate = gmdate('Ymd', current_time('timestamp')-86400 * $graphdays * $pp + 86400);
		    $currentdate = gmdate('Ymd', current_time('timestamp')-86400 * $graphdays *($pp -1));
			$permalink = luc_permalink();
			$NP = luc_count_periode("date","FROM $table_name as t","JOIN $wpdb->posts as p ON t.urlrequested LIKE CONCAT('%', p.post_name, '_' ) OR t.urlrequested =''",
				   "(ip IS NOT NULL) AND p.post_status = 'publish' AND (post_type = 'page' OR post_type = 'post')","date",$graphdays);
		    
			$start_of_week = get_option('start_of_week');
			
			$strqry="SELECT  count(distinct post_name)
		                     FROM $wpdb->posts as p
		                     JOIN $table_name as t
							 ON t.urlrequested LIKE CONCAT('".$permalink[0]."', p.post_name, '_' ) 
		                     WHERE p.post_status = 'publish' AND feed='' AND (post_type = 'page' OR post_type = 'post') AND t.date BETWEEN $limitdate AND $currentdate 
							 ";				   
		    $NumberPosts = $wpdb->get_var($strqry);
			$NumberDisplayPost = get_option('statpressV_graph_per_page');
            if ($NumberDisplayPost == 0)
              $NumberDisplayPost = 20;
		    $NA = ceil($NumberPosts / $NumberDisplayPost);
		    $LimitValueArticles = ($pa * $NumberDisplayPost) - $NumberDisplayPost;
			
		    luc_print_pp_pa_link ($NP,$pp,$action,$NA,$pa);
		    
			 // sort post or page by most views
			$strqry="SELECT post_name, total, urlrequested
					 FROM
						   ((SELECT 'page_accueil' as post_name, count(*) as total, urlrequested
							         FROM $wpdb->posts as p1
									 JOIN $table_name as t1 ON urlrequested ='' 
									 WHERE  post_status = 'publish' AND (post_type = 'page' OR post_type = 'post') 
									 AND spider='' AND feed='' AND (date BETWEEN $limitdate AND $currentdate)
									 GROUP BY post_name)
                     UNION ALL
						(SELECT post_name, count(*) as total, urlrequested
		                     FROM $wpdb->posts as p
		                     JOIN $table_name as t
                             ON urlrequested LIKE CONCAT('%', p.post_name, '_' )  
		                     WHERE post_status = 'publish' AND (post_type = 'page' OR post_type = 'post') 
							 AND spider='' AND feed='' AND date BETWEEN $limitdate AND $currentdate
							 GROUP BY post_name) 
							) views		 
					 GROUP BY post_name 
		             ORDER BY total DESC LIMIT $LimitValueArticles, $NumberDisplayPost";
				
		    $query = $wpdb->get_results($strqry);
		    $spider = get_option('statpressV_not_collect_spider');
			
			echo "<div class='wrap'><h2>" . __('Most views these ', 'statpressV')."".$graphdays." days </h2>"; 
	        foreach ($query as $url)
			{$total=luc_init_count_graph($graphdays,$pp);
			$total->totalpageviews	= 0; 
			$where1 = " (urlrequested LIKE '".$permalink[0]."feed%' OR urlrequested LIKE '".$permalink[0]."comment%') ";
			$where2 = " urlrequested LIKE '%".$url->post_name."%' AND spider='' ";
			if ($url->post_name =='page_accueil') //url == home
			        $total=luc_count_graph ($graphdays,$pp," urlrequested ='' ",$where1,"feed=''","feed<>''",$limitdate,$currentdate);
			else //url<> home
	                $total=luc_count_graph ($graphdays,$pp,$where2,$where2,"feed=''","feed<>''",$limitdate,$currentdate);
					
	      
			$maxxday = luc_maxxday2($total,$graphdays,$pp);
			$px=luc_pixel2($total,$graphdays,$maxxday,$pp,$action);
			// Overhead of the graph, display the name of the post/page and the average by day of the visitors
			echo "<div class='wrap'>
			      <table class='widefat' width='100%' border='0'>
			      <thead><tr>
				  <th scope='col' width='15%'><div style='background:$pageviews_color;width:10px;height:10px;float:left;margin-top:2px;margin-right:5px;'></div>".$total->totalpageviews." pages views
			      <th scope='col' width='15%'><div style='background:$pageviews_color;width:10px;height:10px;float:left;margin-top:2px;margin-right:5px;'></div>Average ".round($total->totalpageviews/$graphdays,1)." by day";
			if ($url->urlrequested == '') $out_url = "Page : Home";
			else  $out_url=$permalink[0].$url->post_name;
			echo "<th scope='col'><font font-size='1'>".$out_url  ."</font></th>
			      </tr></thead></table><table width='100%' border='0'><tr>";
			
			luc_graph2($px,$total,$graphdays,$pp,$action);
			echo '</tr></table></div>';
		}
	  luc_print_pp_pa_link ($NP,$pp,$action,$NA,$pa);
	  echo '</div>';

	  }
?>