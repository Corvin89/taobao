<?php
 function luc_add_pages()
  {
      // Create table if it doesn't exist
      global $wpdb;
      $table_name = $wpdb->prefix . 'statpress';
      if (($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) or (get_option('statpressV_dbversion') <> '1.4'))
                  {$wpdb->query("ALTER TABLE $table_name DROP COLUMN threat_score");
				   $wpdb->query("ALTER TABLE $table_name DROP COLUMN threat_type");
				   update_option('statpressV_dbversion','1.4');
				   luc_statpressV_CreateTable();
				  };

      // add submenu
      $mincap = get_option('statpressV_mincap');
      if ($mincap == '')
          $mincap = 'switch_themes';

	   if ($_GET['statpress_action'] == 'exportnow')
                luc_ExportNow();
       if ($_POST['saveit'] == 'yes')
        { update_option('statpressV_no_page_yesterday', $_POST['statpressV_no_page_yesterday']);
          update_option('statpressV_no_page_spyVisitors', $_POST['statpressV_no_page_spyVisitors']);
          update_option('statpressV_no_page_spybot', $_POST['statpressV_no_page_spybot']);
          update_option('statpressV_no_page_visitors', $_POST['statpressV_no_page_visitors']);
          update_option('statpressV_no_page_view', $_POST['statpressV_no_page_view']);
          update_option('statpressV_no_page_feeds', $_POST['statpressV_no_page_feeds']);
          update_option('statpressV_no_page_referrer', $_POST['statpressV_no_page_referrer']);
		  update_option('statpressV_no_page_stats', $_POST['statpressV_no_page_stats']);
          update_option('statpressV_no_page_update', $_POST['statpressV_no_page_update']);
		  }
      add_menu_page('StatPress V', 'StatPressV', $mincap, __FILE__, 'luc_main',plugins_url('statpress-visitors/images/stat.png',dirname(dirname( __FILE__))));
	  // add optionals submenus
	  if ( (get_option('statpressV_no_page_yesterday') <>'checked') AND (file_exists (ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_yesterday.php')))
		       {include ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_yesterday.php';
			   add_submenu_page(__FILE__,'Yesterday ','Yesterday ', $mincap,'statpress-visitors/action=yesterday', 'luc_yesterday');
			   } 
	  if ( (get_option('statpressV_no_page_spyVisitors') <>'checked') AND (file_exists (ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_spyvisitors.php')))
		       {include ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_spyvisitors.php';
			   add_submenu_page(__FILE__,'Visitor Spy','Visitor Spy', $mincap,'statpress-visitors/action=spyvisitors', 'luc_spyvisitors');
			   }
      if ((get_option('statpressV_no_page_spybot') <>'checked') AND (file_exists (ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_spybot.php')))
		       {include ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_spybot.php';
			   add_submenu_page(__FILE__,'Bot Spy','Bot Spy', $mincap,'statpress-visitors/action=spybot', 'luc_spybot');
			   }   
	  if ((get_option('statpressV_no_page_visitors') <>'checked') AND (file_exists (ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_visitors.php')))
		       {include ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_visitors.php';
			   add_submenu_page(__FILE__,'Visitors ','Visitors ', $mincap,'statpress-visitors/action=visitors', 'luc_visitors');
			   } 
	  if ((get_option('statpressV_no_page_view') <>'checked') AND (file_exists (ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_view.php')))
		       {include ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_view.php';
			    add_submenu_page(__FILE__,'Views','Views ', $mincap,'statpress-visitors/action=views', 'luc_view');
			   } 
	 if ((get_option('statpressV_no_page_feeds') <>'checked') AND (file_exists (ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_feeds.php')))
		       {include ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_feeds.php';
			   add_submenu_page(__FILE__,'Feeds','Feeds ', $mincap,'statpress-visitors/action=feeds', 'luc_feeds');
			   } 
	  if ((get_option('statpressV_no_page_referrer') <>'checked') AND (file_exists (ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_referrer.php')))
		       {include ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_referrer.php';
			   add_submenu_page(__FILE__,'Referrer','Referrer', $mincap, 'statpress-visitors/action=referrer', 'luc_referrer');
			   } 
	 if ((get_option('statpressV_no_page_stats') <>'checked') AND (file_exists (ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_statistics.php')))
		       {include ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_statistics.php';
			   add_submenu_page(__FILE__,'Statistics','Statistics', $mincap,'statpress-visitors/action=details','luc_statistics');
			   }
	  if ((get_option('statpressV_no_page_update') <>'checked') AND (file_exists (ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_update.php')))
		       {include ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_update.php';
			   add_submenu_page(__FILE__,'Update database','Update database', $mincap,'statpress-visitors/action=update', 'luc_update');
			   }
	 add_submenu_page(__FILE__, 'Export', 'Export', $mincap,'statpress-visitors/action=export', 'luc_export');
	 add_submenu_page(__FILE__,'Options','Options', $mincap,'statpress-visitors/action=options', 'luc_options');

  }
  
    function luc_options()
     { global $wpdb;
      $table_name = $wpdb->prefix . 'statpress';
      
?>
  <div class='wrap'><h2>Options</h2>
  <form method=post><table width=100%>
<?php if ($_POST['saveit'] == 'yes')
         {update_option('statpressV_collect_logged_user', $_POST['statpressV_collect_logged_user']);
          update_option('statpressV_autodelete', $_POST['statpressV_autodelete']);
          update_option('statpressV_days_graph', $_POST['statpressV_days_graph']);
          update_option('statpressV_mincap', $_POST['statpressV_mincap']);
          update_option('statpressV_not_collect_spider', $_POST['statpressV_not_collect_spider']);
          update_option('statpressV_autodelete_spider', $_POST['statpressV_autodelete_spider']);
          update_option('statpressV_graph_per_page', $_POST['statpressV_graph_per_page']);
		  update_option('statpressV_ip_per_page_spyvisitor', $_POST['statpressV_ip_per_page_spyvisitor']);
          update_option('statpressV_visits_per_ip_spyvisitor', $_POST['statpressV_visits_per_ip_spyvisitor']);
		  update_option('statpressV_bots_per_page_spybot', $_POST['statpressV_bots_per_page_spybot']);
          update_option('statpressV_visits_per_bot_spybot', $_POST['statpressV_visits_per_bot_spybot']);
		  update_option('statpressV_ip_per_page_logvisitor', $_POST['statpressV_ip_per_page_logvisitor']);
          update_option('statpressV_proof_log_visitor', $_POST['statpressV_proof_log_visitor']);
		  update_option('statpressV_bots_per_page_logbot', $_POST['statpressV_bots_per_page_logbot']);
          update_option('statpressV_proof_logbot', $_POST['statpressV_proof_logbot']);
		  // update database too
           if (($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) or (get_option('statpressV_dbversion') <> '1.4'))
                  {$wpdb->query("ALTER TABLE $table_name DROP COLUMN threat_score");
				   $wpdb->query("ALTER TABLE $table_name DROP COLUMN threat_type");
				   update_option('statpressV_dbversion','1.4');
				   luc_statpressV_CreateTable();
				  };
          echo "<br /><div class='wrap' align ='center'><h2>Recorded !<h2>";
		   }
      echo "<tr><td><input type=checkbox name='statpressV_collect_logged_user' id='statpressV_collect_logged_user' value='checked'" . get_option('statpressV_collect_logged_user') . "> <label for='statpressV_collect_logged_user'>Collect data about logged users</label></td></tr>
            <tr><td><input type=checkbox name='statpressV_not_collect_spider' id='statpressV_not_collect_spider' value='checked'" . get_option('statpressV_not_collect_spider') . "><label for='statpressV_collect_logged_user'>Do not collect spiders visits</label></td></tr>";
      $statpressV_autodelete = get_option('statpressV_autodelete');
      $statpressV_autodelete_spider = get_option('statpressV_autodelete_spider');
      $statpressV_days_graph = get_option('statpressV_days_graph');
	  $statpressV_graph_per_page = get_option('statpressV_graph_per_page');
	  $statpressV_ip_per_page_spyvisitor = get_option('statpressV_ip_per_page_spyvisitor');
	  $statpressV_visits_per_ip_spyvisitor = get_option('statpressV_visits_per_ip_spyvisitor');
	  $statpressV_bots_per_page_spybot = get_option('statpressV_bots_per_page_spybot');
	  $statpressV_visits_per_bot_spybot = get_option('statpressV_visits_per_bot_spybot');
       ?>
   </p>
  <tr><td>Automatically delete visits older than
  <select name="statpressV_autodelete">
  <option value=""        <?php if ($statpressV_autodelete == '')
                                    print "selected"; ?>>Never delete !</option>
  <option value="1 month" <?php if ($statpressV_autodelete == "1 month")
                                    print "selected"; ?>>1 month</option>
  <option value="3 months" <?php if ($statpressV_autodelete == "3 months")
                                    print "selected"; ?>>3 months</option>
  <option value="6 months" <?php if ($statpressV_autodelete == "6 months")
                                    print "selected"; ?>>6 months</option>
  <option value="1 year" <?php if ($statpressV_autodelete == "1 year")
                                    print "selected"; ?>>1 year</option>
  </select></td></tr>
  
  <tr><td>Automatically delete spider visits older than
  <select name="statpressV_autodelete_spider">
  <option value="" <?php if($statpressV_autodelete_spider =='' ) print "selected"; ?>>Never delete !</option>
  <option value="1 day" <?php if($statpressV_autodelete_spider == "1 day") print "selected"; ?>>1 day</option>
  <option value="1 week" <?php if($statpressV_autodelete_spider == "1 week") print "selected"; ?>>1 week</option>
  <option value="1 month" <?php if($statpressV_autodelete_spider == "1 month") print "selected"; ?>>1 month</option>
  <option value="1 year" <?php if($statpressV_autodelete_spider == "1 year") print "selected"; ?>>1 year</option>
  </select></td></tr>
  <tr><td>Minimum capability to view stats
  <select name="statpressV_mincap">
<?php
          luc_dropdown_caps(get_option('statpressV_mincap'));
?>
  </select> 
  <a href="http://codex.wordpress.org/Roles_and_Capabilities" target="_blank">more info</a>
  </td></tr>
  <?php 
  echo "<table width=100%> <h3>Select the pages you do not want to appear, they free up some RAM :</h3><p>
       <input type=checkbox name='statpressV_no_page_yesterday' id='statpressV_no_page_yesterday' value='checked'" . get_option('statpressV_no_page_yesterday') . "  /> <label for='statpressV_no_page_yesterday'>Yesterday </label><br />
       <input type=checkbox name='statpressV_no_page_spyVisitors' id='statpressV_no_page_spyVisitors' value='checked'" . get_option('statpressV_no_page_spyVisitors') . " /> <label for='statpressV_no_page_spyVisitors'>Visitor Spy </label><br />
       <input type=checkbox name='statpressV_no_page_spybot' id='statpressV_no_page_spybot' value='checked'" . get_option('statpressV_no_page_spybot') . " /> <label for='statpressV_no_page_spybot'>Bot Spy  </label><br />
	   <input type=checkbox name='statpressV_no_page_visitors' id='statpressV_no_page_visitors' value='checked'" . get_option('statpressV_no_page_visitors') . " /> <label for='statpressV_no_page_visitors'>Visitors </label><br />
       <input type=checkbox name='statpressV_no_page_view' id='statpressV_no_page_view' value='checked'" . get_option('statpressV_no_page_view') . " /> <label for='statpressV_no_page_view'>View  </label><br />
       <input type=checkbox name='statpressV_no_page_feeds' id='statpressV_no_page_feeds' value='checked'" . get_option('statpressV_no_page_feeds') . " /> <label for='statpressV_no_page_feeds'>Feeds  </label><br />
       <input type=checkbox name='statpressV_no_page_referrer' id='statpressV_no_page_referrer' value='checked'" . get_option('statpressV_no_page_referrer') . " /> <label for='statpressV_no_page_referrer'>Referrer </label><br />
       <input type=checkbox name='statpressV_no_page_stats' id='statpressV_no_page_stats' value='checked'" . get_option('statpressV_no_page_stats') . " /> <label for='statpressV_no_page_stats'>Statistics </label><br />
       <input type=checkbox name='statpressV_no_page_update' id='statpressV_no_page_update' value='checked'" . get_option('statpressV_no_page_update') . " /> <label for='statpressV_no_page_update'>Update database </label><br />
	   <br><br><h4>";
?>
Overview, Visitors, Views, Feeds, Referrer graphs
  <tr><td></h4>Days in graphs 
  <select name="statpressV_days_graph">
  <option value="7" <?php if ($statpressV_days_graph == 7)
                             echo "selected"; ?>>7</option>
  <option value="15" <?php if ($statpressV_days_graph == 15)
                             echo "selected"; ?>>15</option>
  <option value="21" <?php if ($statpressV_days_graph == 21)
                             echo "selected"; ?>>21</option>
  <option value="31" <?php if ($statpressV_days_graph == 31)
                             echo "selected"; ?>>31</option>
  <option value="62" <?php if ($statpressV_days_graph == 62)
                             echo "selected"; ?>>62</option>
  </select></td></tr>

 <tr><td>
  <h4>Visitors, views, feeds, referrer graphs :</h4> Graphs per page
  <select name="statpressV_graph_per_page">
  <option value="20" <?php if ($statpressV_graph_per_page == 20)
                              echo "selected"; ?>>20</option>
  <option value="50" <?php if ($statpressV_graph_per_page == 50)
                              echo "selected"; ?>>50</option>
  <option value="100" <?php if ($statpressV_graph_per_page == 100)
                              echo "selected"; ?>>100</option>
  </select></td></tr>
<tr><td><h4><?php 
  if ( (get_option('statpressV_no_page_spyVisitors') <>'checked') AND (file_exists (ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_spyvisitors.php')))
 { ?>Visitors spy : </h4>
Visitors per page
  <select name="statpressV_ip_per_page_spyvisitor">
  <option value="20" <?php if ($statpressV_ip_per_page_spyvisitor == 20)
                             print "selected"; ?>>20</option>
  <option value="50" <?php if ($statpressV_ip_per_page_spyvisitor == 50)
                             print "selected"; ?>>50</option>
  <option value="100" <?php if ($statpressV_ip_per_page_spyvisitor == 100)
                             print "selected"; ?>>100</option>
   </select></td></tr>
   <tr><td>Visits per visitor
  <select name="statpressV_visits_per_ip_spyvisitor">
  <option value="20" <?php if ($statpressV_visits_per_ip_spyvisitor == 20)
                             print "selected"; ?>>20</option>
  <option value="50" <?php if ($statpressV_visits_per_ip_spyvisitor == 50)
                             print "selected"; ?>>50</option>
  <option value="100" <?php if ($statpressV_visits_per_ip_spyvisitor == 100)
                             print "selected"; ?>>100</option>
  </select></td></tr>
  <tr><td><h4><?php } if ((get_option('statpressV_no_page_spybot') <>'checked') AND (file_exists (ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/pages/luc_spybot.php')))

 { ?>Bots spy : </h4>
 Bots per page
  <select name="statpressV_bots_per_page_spybot">
  <option value="5" <?php if ($statpressV_bots_per_page_spybot == 5)
                             print "selected"; ?>>5</option>
  <option value="10" <?php if ($statpressV_bots_per_page_spybot == 10)
                             print "selected"; ?>>10</option>
  <option value="20" <?php if ($statpressV_bots_per_page_spybot == 20)
                             print "selected"; ?>>20</option>
   </select></td></tr>
   <tr><td>Visits per bot
  <select name="statpressV_visits_per_bot_spybot">
  <option value="20" <?php if ($statpressV_visits_per_bot_spybot == 20)
                             print "selected"; ?>>20</option>
  <option value="50" <?php if ($statpressV_visits_per_bot_spybot == 50)
                             print "selected"; ?>>50</option>
  <option value="100" <?php if ($statpressV_visits_per_bot_spybot == 100)
                             print "selected"; ?>>100</option>
  </select></td></tr>
<?php }
?>
  <tr>
  <td><br> <input type=submit value='Save options' class="button-primary" ></td></tr>
  </table>
  <input type=hidden name=saveit value=yes>
  <input type=hidden name=page value=statpressV><input type=hidden name=statpressV_action value=options>
  </form> </div>
 
<?php
      }  

   function luc_main()
        { global $wpdb;  
          $table_name = $wpdb->prefix . "statpress";
          $action = 'overview';
          // OVERVIEW table
		  $visitors_color = "#114477";
		  $rss_visitors_color = "#FFF168";
          $pageviews_color = "#3377B6";
          $rss_pageviews_color = "#f38f36";
          $spider_color = "#83b4d8";

          $lastmonth = luc_StatPress_lastmonth();
          $thismonth = gmdate('Ym', current_time('timestamp'));
          $yesterday = gmdate('Ymd', current_time('timestamp') - 86400);
          $today = gmdate('Ymd', current_time('timestamp'));

          $tlm[0] = my_substr($lastmonth, 0, 4);
          $tlm[1] = my_substr($lastmonth, 4, 2);

          echo "<div class='wrap' ><h2>Overview</h2>
          <table class='widefat' >
		  <thead><tr >
	<th scope='col'></th>
	<th scope='col'>Total</th>
	<th scope='col'>Last month<br /><font size=1>" . gmdate('M, Y',gmmktime(0,0,0,$tlm[1],1,$tlm[0])) ."</font></th>
	<th scope='col'>This month<br /><font size=1>" . gmdate('M, Y', current_time('timestamp')) ."</font></th>
	<th scope='col'>Target This month<br /><font size=1>" . gmdate('M, Y', current_time('timestamp')) ."</font></th>
	<th scope='col'>Yesterday<br /><font size=1>" . gmdate('d M, Y', current_time('timestamp')-86400) ."</font></th>
	<th scope='col'>Today<br /><font size=1>" . gmdate('d M, Y', current_time('timestamp')) ."</font></th>
	</tr></thead>
	<tbody>";

          //###############################################################################################
          // VISITORS ROW
          luc_ROW ("DISTINCT ip","feed=''","spider=''",$visitors_color,"Visitors");
        
		  //###############################################################################################
          // VISITORS FEEDS ROW
          luc_ROW ("DISTINCT ip","feed<>''","spider=''",$rss_visitors_color,"Visitors RSS Feeds");
     
          //###############################################################################################
          // PAGEVIEWS ROW
		  luc_ROW ("*","feed=''","spider=''",$pageviews_color,"Pageviews");
		
		  //###############################################################################################
          // PAGEVIEWS FEEDS ROW
		  luc_ROW ("*","feed<>''","spider=''",$rss_pageviews_color,"Pageviews RSS Feeds");
		
          //###############################################################################################
		  // SPIDERS ROW
          $not_collect_spider=get_option('statpressV_not_collect_spider'); // chek if collect or not spider
		  if ($not_collect_spider=='') luc_ROW ("*","feed=''","spider<>''",$spider_color,"Spiders");
		   
		   echo "</table>";
          //###############################################################################################
          // THE GRAPHS
         
            $graphdays = get_option('statpressV_days_graph');
            if ($graphdays == 0)
                   $graphdays = 7;
            $pp = luc_page_periode();	// $pp is the slug of the current page
		    $limitdate = gmdate('Ymd', current_time('timestamp')-86400 * $graphdays * $pp + 86400); // first date to display on the graphs
		    $currentdate = gmdate('Ymd', current_time('timestamp')-86400 * $graphdays *($pp -1)); // last date to display on the graphs
			$NP = luc_count_periode("date","","FROM $table_name","ip IS NOT NULL","date",$graphdays); // total of all display pages link
		    $start_of_week = get_option('start_of_week');
            
			$total=luc_init_count_graph($graphdays,$pp);
            $total=luc_count_graph ($graphdays,$pp," 1=1 "," 1=1 ","feed=''","feed<>''",$limitdate,$currentdate);	
			$maxxday = luc_maxxday2($total,$graphdays,$pp); //calculation sum of the maximumn of visitors, pageviews, feeds, and spider for all days of the graph ($graphdays)
		    $px=luc_pixel2($total,$graphdays,$maxxday,$pp,$action);
			
			// Overhead of the graph, display the average by day of the visitors, visitors feeds, pageviews, pageviews feeds and spiders
		    echo "<h2>Graph</h2>	
			<table class='widefat' ><thead>
			<tr>
			<th scope='col'>Average by day : </th>
			<th scope='col'><div style='background:$visitors_color;width:10px;height:10px;float:left;margin-top:2px;margin-right:5px;'></div>".(round($total->totalvisitors/$graphdays,1))." Visitors</th>
			<th scope='col'><div style='background:$rss_visitors_color;width:10px;height:10px;float:left;margin-top:2px;margin-right:5px;'></div>".(round($total->totalvisitors_feeds/$graphdays,1))." Visitors Feeds</th>
			<th scope='col'><div style='background:$pageviews_color;width:10px;height:10px;float:left;margin-top:2px;margin-right:5px;'></div>".(round($total->totalpageviews/$graphdays,1))." Pageviews
			<th scope='col'><div style='background:$rss_pageviews_color;width:10px;height:10px;float:left;margin-top:2px;margin-right:5px;'></div>".(round($total->totalpageviews_feeds/$graphdays,1))." Pageviews Feeds</th>";
			if ($not_collect_spider =='')
			echo "<th scope='col'><div style='background:$spider_color;width:10px;height:10px;float:left;margin-top:2px;margin-right:5px;'></div>".(round($total->totalspiders/$graphdays,1))." Spiders</th>";
			echo "</tr></thead></table>
			      <table width='100%' border='0'><tr>";
            luc_graph2($px,$total,$graphdays,$pp,$action);
			echo "</tr></table>";
            luc_print_pp_link($NP,$pp,"overview");

             // END OF OVERVIEW
             //###################################################################################################
             
             $querylimit = "LIMIT 50";
             // Tabella Last Hits
             echo "<h2>Last Hits</h2>
			 <table class='widefat' >
			 <thead><tr>
			 <th scope='col'>Date</th>
			 <th scope='col'>Time</th>
			 <th scope='col'>IP</th>
			 <th scope='col'>Domain</th>
			 <th scope='col'>language</th>
			 <th scope='col'>Country</th>
			 <th scope='col'>Page</th>
			 <th scope='col'>OS</th>
			 <th scope='col'>Browser</th>";
			 if (get_option('statpressV_collect_logged_user') == 'checked')
			            echo "<th scope='col'>User</th>";
			 echo "<th scope='col'>Feed</th>
			 </tr></thead>
			 <tbody >";
          
             $rks = $wpdb->get_results("SELECT date,time,ip,urlrequested,nation,os,browser,feed,user,language,country FROM $table_name WHERE (os<>'' OR browser <>'') AND ip IS NOT NULL order by id DESC $querylimit");
			 foreach ($rks as $rk)
             {echo "<tr>
              <td>" . luc_hdate($rk->date) . "</td>
              <td>" . $rk->time . "</td>
              <td>" . $rk->ip . "</td>";
			  luc_image_NC($rk,'nation','domain.dat','domain',1==1);
			  luc_language($rk);
			  luc_image_NC($rk,'country','domain.dat','domain',strtolower($rk->country)<>$rk->nation);
			  echo "<td>" . luc_StatPressV_Decode($rk->urlrequested) . "</td>";
			  luc_image_OBS($rk,'os','os.dat','os');
			  luc_image_OBS($rk,'browser','browser.dat','browsers');
			  if (get_option('statpressV_collect_logged_user') == 'checked')
			    {if ($rk->user != '')  echo "<td>".$rk->user."</td>";
			     else echo "<td>&nbsp;</td>"; 
                };
			  luc_image_OBS($rk,'feed','feeds.dat','feeds');
			  echo "</tr>";
            }
             echo "</tbody></table>";

            // Last Search terms
            echo "<h2>Last search terms</h2>
			<table class='widefat' >
			<thead><tr>
			<th scope='col'>Date</th>
			<th scope='col'>Time</th>
			<th scope='col'>IP</th>
			<th scope='col'>Domain</th>
			<th scope='col'>language</th>
		    <th scope='col'>Country</th>
			<th scope='col'>Terms</th>
			<th scope='col'>Page</th>
			<th scope='col'>Engine</th>
			</tr></thead>";
            echo "<tbody >";
            $qry = $wpdb->get_results("SELECT date,time,ip,urlrequested,search,nation,searchengine,language,country FROM $table_name WHERE search<>''  AND ip IS NOT NULL ORDER BY id DESC $querylimit");
            foreach ($qry as $rk)
             {
              echo "<tr>
			  <td>" . luc_hdate($rk->date) . "</td>
			  <td>" . $rk->time . "</td>
			  <td>" . $rk->ip . "</td>";
			  luc_image_NC($rk,'nation','domain.dat','domain',1==1);
			  luc_language($rk);
			  luc_image_NC($rk,'country','domain.dat','domain',strtolower($rk->country)<>$rk->nation);
              echo "<td><a href='" . $rk->referrer . "'>" . urldecode($rk->search) . "</a></td>";
			  echo "<td>" . luc_StatPressV_Decode($rk->urlrequested) . "</td>";
			  luc_image_OBS($rk,'searchengine','searchengines.dat','searchengines');
			  echo "</tr>\n";
             }
            echo "</tbody></table>";

            // Referrer
            echo "<h2>Last referrers</h2>
			<table class='widefat' >
			<thead>
			<tr>
			<th scope='col'>Date</th>
			<th scope='col'>Time</th>
			<th scope='col'>IP</th>
			<th scope='col'>Domain</th>
			 <th scope='col'>language</th>
			 <th scope='col'>Country</th>
			<th scope='col'>URL</th>
			<th scope='col'>Page</th>
			</tr></thead>";
            echo "<tbody >";
            $qry = $wpdb->get_results("SELECT * FROM $table_name WHERE ((referrer NOT LIKE '" . get_option('home') . "%') AND (referrer <>'') AND (searchengine='') AND ip IS NOT NULL ) ORDER BY id DESC $querylimit");
            foreach ($qry as $rk)
             {
              echo "<tr>
			  <td>" . luc_hdate($rk->date) . "</td>
			  <td>" . $rk->time . "</td>
			  <td>" . $rk->ip . "</td>";
			  luc_image_NC($rk,'nation','domain.dat','domain',1==1);
			  luc_language($rk);
			  luc_image_NC($rk,'country','domain.dat','domain',strtolower($rk->country)<>$rk->nation);
			  echo "<td><a href='" . $rk->referrer . "'>" . luc_StatPress_Abbrevia(luc_StatPressV_Decode($rk->referrer), 100) . "</a></td>";
			  echo "<td>" . luc_StatPressV_Decode($rk->urlrequested) . "</td>
			  </tr>\n";
             }
            echo "</tbody></table>";

			// Feeds 
            echo "<h2>Last Feeds</h2>
			<table class='widefat' >
			<thead><tr>
			<th scope='col'>Date</th>
			<th scope='col'>Time</th>
			<th scope='col'>IP</th>
			<th scope='col'>Domain</th>
			<th scope='col'>language</th>
			<th scope='col'>Country</th>
			<th scope='col'>Page</th>
			<th></th>
			<th scope='col'>Feed</th>";
			if (get_option('statpressV_collect_logged_user') == 'checked')
			echo "<th scope='col'>User</th>";
			echo "</tr></thead>";
            echo "<tbody >";
            $qry = $wpdb->get_results("SELECT * FROM $table_name WHERE feed<>''  AND ip IS NOT NULL ORDER BY id DESC $querylimit");
            foreach ($qry as $rk)
             {
              echo "<tr>
			  <td>".luc_hdate($rk->date)."</td>
			  <td>".$rk->time."</td>
			  <td>".$rk->ip."</td>";
			  luc_image_NC($rk,'nation','domain.dat','domain',1==1);
			  luc_language($rk);
			  luc_image_NC($rk,'country','domain.dat','domain',strtolower($rk->country)<>$rk->nation);
			  echo "<td>".$rk->urlrequested."</td>";
			  luc_image_OBS($rk,'feed','feeds.dat','feeds');
			  echo "<td>".$rk->feed."</a></td>";
			  if (get_option('statpressV_collect_logged_user') == 'checked')
			    {if ($rk->user != '')  echo "<td>".$rk->user."</td>";
			     else echo "<td>&nbsp;</td>"; 
                }				 
			  echo "</tr>\n";
             }
            echo "</tbody></table>";
			
			// Last Spiders
            if ($not_collect_spider =='')
            {
            echo "<h2>Last spiders</h2>
            <table class='widefat' ><thead><tr>
            <th scope='col'>Date</th>
			<th scope='col'>Time</th>
			<th scope='col'>IP</th>
			<th></th>
            <th scope='col'>Page</th>
            <th scope='col'>Agent</th>
            </tr>
			</thead>
			<tbody >";
            $qry = $wpdb->get_results("SELECT * FROM $table_name WHERE spider<>'' AND ip IS NOT NULL  ORDER BY id DESC $querylimit");
            foreach ($qry as $rk)
             {
              echo "<tr>
			  <td>" . luc_hdate($rk->date) . "</td>
              <td>" . $rk->time . "</td>
			  <td>".$rk->ip."</td>";
			  luc_image_OBS($rk,'spider','spider.dat','spider');
              echo "<td>" . luc_StatPress_Abbrevia(luc_StatPressV_Decode($rk->urlrequested), 80) . "</td>
              <td> " . $rk->agent . "</td></tr>\n";
             }
            echo "</tbody></table>";
			//Undefined agent
			echo "<h2>Undefined agent</h2>
            <table class='widefat' ><thead><tr>
            <th scope='col'>Date</th>
			<th scope='col'>Time</th>
			<th scope='col'>IP</th>
            <th scope='col'>Agent</th>
            </tr>
			</thead>
			<tbody >";
			$qry = $wpdb->get_results("SELECT * FROM $table_name WHERE os='' AND browser='' AND searchengine='' AND spider='' AND ip IS NOT NULL  ORDER BY id DESC $querylimit");
            foreach ($qry as $rk)
             {
              echo "<tr>
			  <td>".luc_hdate($rk->date)."</td>
              <td>".$rk->time."</td>
			  <td>".$rk->ip."</td>
              <td>". $rk->agent."</td></tr>\n";
             }
			 echo "</tbody></table></div><br />";
			}
            echo "&nbsp;<i>statpress V table size : <b>" . luc_tablesize($wpdb->prefix . "statpress") . "</b></i><br />
            &nbsp;<i>statpress V current time : <b>" . current_time('mysql') . "</b></i><br />
            &nbsp;<i>RSS2 url : <b>" . get_bloginfo('rss2_url') . ' (' . luc_StatPress_extractfeedreq(get_bloginfo('rss2_url')) . ")</b></i><br />
            &nbsp;<i>ATOM url : <b>" . get_bloginfo('atom_url') . ' (' . luc_StatPress_extractfeedreq(get_bloginfo('atom_url')) . ")</b></i><br />
            &nbsp;<i>RSS url : <b>" . get_bloginfo('rss_url') . ' (' . luc_StatPress_extractfeedreq(get_bloginfo('rss_url')) . ")</b></i><br />
            &nbsp;<i>COMMENT RSS2 url : <b>" . get_bloginfo('comments_rss2_url') . ' (' . luc_StatPress_extractfeedreq(get_bloginfo('comments_rss2_url')) . ")</b></i><br />
            &nbsp;<i>COMMENT ATOM url : <b>" . get_bloginfo('comments_atom_url') . ' (' . luc_StatPress_extractfeedreq(get_bloginfo('comments_atom_url')) . ")</b></i><br />";
      
	  }
	  
	function luc_init_count_graph($graphdays,$pp)
	    { for ($i=0; $i <$graphdays; $i++) 
                  { $Date = gmdate('Ymd', current_time('timestamp') - 86400 * ($graphdays * $pp-$i-1 ));
                    $total->visitors[$Date] = 0;
					$total->visitors_feeds[$Date] = 0;
					$total->pageviews[$Date] = 0;
					$total->pageviews_feeds[$Date] = 0;
					$total->spiders[$Date] = 0;
                  } 
		return $total;
        }
		
	function luc_count_graph ($graphdays,$pp,$where1,$where2,$feed1,$feed2,$limitdate,$currentdate)
	    {   //TOTAL VISITORS
			$qry_visitors=luc_query_graph2("DISTINCT ip",$where1." AND ".$feed1." AND spider='' ",$limitdate,$currentdate); // SQL query count of the uniques visitors for all days of the graph ($graphdays)
	        foreach ($qry_visitors as $row )
                       {$total->visitors[$row->date] = $row->total;
					    $total->totalvisitors+=$row->total;
						}
	   
			//TOTAL VISITORS FEEDS
            $qry_visitors_feeds = luc_query_graph2("DISTINCT ip",$where2." AND ".$feed2." AND spider='' ",$limitdate,$currentdate); // SQL query count of the visitors feeds for all days of the graph ($graphdays)
			foreach ($qry_visitors_feeds as $row )
                       {$total->visitors_feeds[$row->date] = $row->total;
					    $total->totalvisitors_feeds+=$row->total;
                       }
			//TOTAL PAGEVIEWS (we do not delete the uniques, this is falsing the info.. uniques are not different visitors!)
	        $qry_pageviews = luc_query_graph2("*",$where1." AND ".$feed1." AND spider='' ",$limitdate,$currentdate);// SQL count of the pageviews for all days of the graph ($graphdays)
			foreach ($qry_pageviews as $row )
                       {$total->pageviews[$row->date] = $row->total;
					    $total->totalpageviews+=$row->total;
						}

			//TOTAL PAGEVIEWS FEEDS
            $qry_pageviews_feeds = luc_query_graph2("*",$where2." AND ".$feed2." AND spider='' ",$limitdate,$currentdate);// SQL query count of the pageviews feeds for all days of the graph ($graphdays)
			foreach ($qry_pageviews_feeds as $row )
					    {$total->pageviews_feeds[$row->date] = $row->total;
						 $total->totalpageviews_feeds+=$row->total;
						 }
                    
            //TOTAL SPIDERS
		    if (get_option('statpressV_not_collect_spider') =='')
               {$qry_spiders = luc_query_graph2("*",$where2." AND ".$feed1." AND spider<>'' ",$limitdate,$currentdate);// SQL query count of the spiders for all days of the graph ($graphdays)
			    foreach ($qry_spiders as $row )
                       {$total->spiders[$row->date] = $row->total;
					    $total->totalspiders+=$row->total;
						}

			    }
			return $total;	
		}
	  
    function luc_ROW ($count,$feed,$spider,$color,$text)
	    { $visitors_color = "#114477";
		  $rss_visitors_color = "#FFF168";
          $pageviews_color = "#3377B6";
          $rss_pageviews_color = "#f38f36";
          $spider_color = "#83b4d8";

          $lastmonth = luc_StatPress_lastmonth();
          $thismonth = gmdate('Ym', current_time('timestamp'));
          $yesterday = gmdate('Ymd', current_time('timestamp') - 86400);
          $today = gmdate('Ymd', current_time('timestamp'));

		//TOTAL
		  $qry_total=requete_main ($count,$feed,$spider,"1 = 1");
		  //LAST MONTH
		  $qry_lmonth=requete_main ($count,$feed,$spider,"date LIKE '".$lastmonth."%'");
		   //THIS MONTH
		  $qry_tmonth=requete_main($count,$feed,$spider,"date LIKE '".$thismonth."%'");   	     
		  $qry_tmonth_change=pourcent_change($qry_tmonth,$qry_lmonth);
		  //TARGET
          $tmonthtarget=round($qry_tmonth/(time()-mktime(0,0,0,date('m'),date('1'),date('Y')))*(86400*date('t')));
		  $tmonthadded=pourcent_change($tmonthtarget,$qry_lmonth);
		  //YESTERDAY
		  $qry_y=requete_main($count,$feed,$spider,"date LIKE '".$yesterday."%'"); 
          //TODAY
		  $qry_t=requete_main($count,$feed,$spider,"date LIKE '".$today."%'");  
          echo "<tr><td><div style='background:$color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>$text</td>
          <td>".$qry_total."</td>\n
          <td>".$qry_lmonth."</td>\n
          <td>".$qry_tmonth.$qry_tmonth_change."</td>\n
          <td>".$tmonthtarget.$tmonthadded."</td>\n
          <td>".$qry_y."</td>\n
          <td>".$qry_t."</td>\n</tr>";
		  }
		
	function luc_image_NC($rk,$champ,$fich_def,$fich_img,$cond)
       {$title='';
		$img='';
		if (($rk->$champ != '') AND $cond)
			         { $img=strtolower($rk->$champ).".png"; //the photo exist, give it a name
					   $lines = file(ABSPATH.'wp-content/plugins/'.dirname(dirname(plugin_basename(__FILE__))) .'/def/'.$fich_def);
			           foreach($lines as $line_num => $ligne) //seeks the tooltip corresponding to the photo
			                  { list($title,$id)=explode("|",$ligne);
							    if($id==strtolower($rk->$champ)) break; // break, the tooltip ($title) is found
							   }
			           echo "<td><IMG style='border:0px;height:16px;' alt='".$title."' title='".$title."' SRC='" .plugins_url('statpress-visitors/images/'.$fich_img.'/'.$img, dirname(dirname(__FILE__))). "'></td>";
					  } 
			  else echo "<td>&nbsp;</td>"; 
		}	  
        
	function luc_language($rk)
	   {$title=''; 
		$img =''; 
		if($rk->language != '') 
			         { $lines = file(ABSPATH.'wp-content/plugins/'.dirname(dirname(plugin_basename(__FILE__))) .'/def/languages.dat');
			           foreach($lines as $line_num => $ligne) //seeks the tooltip corresponding to the photo
			                  { list($langue,$id)=explode("|",$ligne);
							    if($id==$rk->language) break; // break, the tooltip ($title) is found
							   }
			           echo "<td>".$langue."</td>";
					  } 
			  else echo "<td >&nbsp;</td>";
	    }
		
	function luc_image_OBS($rk,$champ,$fich_def,$fich_img)
	    {$title='';
		 $img='';
		 if($rk->$champ != '') 
			        { $img=str_replace(" ","_",strtolower($rk->$champ));
			          $img=str_replace('.','',$img).".png";//the photo exist, give it a name
					  $lines = file(ABSPATH.'wp-content/plugins/'.dirname(dirname(plugin_basename(__FILE__))) .'/def/'.$fich_def);
					  foreach($lines as $line_num => $ligne) //seeks the tooltip corresponding to the photo
			                  { list($title,$id)=explode("|",$ligne);
							    if(strtolower($title)==strtolower($rk->$champ)) break; // break, the tooltip ($title) is found
					            }
			          echo "<td><IMG style='border:0px;height:16px;' alt='".$title."' title='".$title."' SRC='" .plugins_url('statpress-visitors/images/'.$fich_img.'/'.$img, dirname(dirname(__FILE__))). "'></td>";
					} 
			  else echo "<td>&nbsp;</td>";
		}
		
	function luc_dropdown_caps($default = false)
       {  global $wp_roles;
          $role = get_role('administrator');
          foreach ($role->capabilities as $cap => $grant)
             { echo "<option ";
               if ($default == $cap)
                  echo "selected ";
               echo ">$cap</option>";
             }
        }

	function pourcent_change($current,$last)
	 {if ($last<>0)
          {  $pt=round(100*($current/$last)-100,1);
              if ($pt>=0)
                  $pt="+".$pt;
              $change="<code> (".$pt."%)</code>";
			  return $change;
          }
	  return '';
	 }

	function requete_main($count,$feed,$spider,$date)
	 {   global $wpdb;
	     $table_name = $wpdb->prefix . "statpress";
	     $qry = $wpdb->get_var("SELECT count($count) 
                                        FROM $table_name
                                        WHERE ip IS NOT NULL AND $feed AND $spider AND $date");
		 return $qry;
	}

	function luc_export()
        {
?>
  <div class='wrap'><h2><?php _e('Export stats to text file', 'statpressV'); ?> (csv)</h2>
  <form method=get><table>
  <tr><td><?php _e('From', 'statpressV'); ?></td><td><input type=text name=from> (YYYYMMDD)</td></tr>
  <tr><td><?php _e('To', 'statpressV');?></td><td><input type=text name=to> (YYYYMMDD)</td></tr>
  <tr><td><?php _e('Fields delimiter', 'statpressV');?></td><td>
  <select name=del><option>,</option><option>;</option><option>|</option></select></tr>
  <tr><td></td><td><input type=submit value=<?php _e('Export', 'statpressV');?>></td></tr>
  <input type=hidden name=page value=statpress><input type=hidden name=statpress_action value=exportnow>
  </table></form>
  </div>
<?php
      }

    function luc_exportNow()
        { global $wpdb;
          $table_name = $wpdb->prefix . "statpress";
          $filename = get_bloginfo('title') . "-statpress_" . $_GET['from'] . "-" . $_GET['to'] . ".csv";
          header('Content-Description: File Transfer');
          header("Content-Disposition: attachment; filename=$filename");
          header('Content-Type: text/plain charset=' . get_option('blog_charset'), true);
          $qry = $wpdb->get_results("SELECT * FROM $table_name WHERE date>='" . (date("Ymd", strtotime(my_substr($_GET['from'], 0, 8)))) . "' AND date<='" . (date("Ymd", strtotime(my_substr($_GET['to'], 0, 8)))) . "';");
          $del = my_substr($_GET['del'], 0, 1);
          echo "date" . $del . "time" . $del . "ip" . $del . "urlrequested" . $del . "agent" . $del . "referrer" . $del . "search" . $del . "nation" . $del . "os" . $del . "browser" . $del . "searchengine" . $del . "spider" . $del . "feed\n";
          foreach ($qry as $rk)
             {
              echo '"' . $rk->date . '"' . $del . '"' . $rk->time . '"' . $del . '"' . $rk->ip . '"' . $del . '"' . $rk->urlrequested . '"' . $del . '"' . $rk->agent . '"' . $del . '"' . $rk->referrer . '"' . $del . '"' . urldecode($rk->search) . '"' . $del . '"' . $rk->nation . '"' . $del . '"' . $rk->os . '"' . $del . '"' . $rk->browser . '"' . $del . '"' . $rk->searchengine . '"' . $del . '"' . $rk->spider . '"' . $del . '"' . $rk->feed . '"' . "\n";
             }
          die();
        }

	function luc_permalink()
	      { global $wpdb;
            $table_name = $wpdb->prefix . "statpress";
	        $permalink = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'permalink_structure'");
	        $permalink = explode("%", $permalink);
	        return $permalink;
	       }

	function luc_graph2($px,$total,$graphdays,$pp,$action)
	      { $visitors_color = "#114477";
		    $rss_visitors_color = "#FFF168";
            $pageviews_color = "#3377B6";
            $rss_pageviews_color = "#f38f36";
            $spider_color = "#83b4d8";
			$referrer_color = "#419E0C";
            $gd = (90 / $graphdays) . '%';
			if ($action == 'referrer')
			    $color = $referrer_color;
			else
			    $color = $visitors_color;
			    
			for ( $i = 0; $i < $graphdays ; $i++ )
                { echo '<td width="' . $gd . '" valign="bottom"';
                  if ($start_of_week == gmdate('w', current_time('timestamp') - 86400 * ($graphdays * $pp-$i-1)))  // week-cut
                      echo ' style="border-left:2px dotted gray;"';
                  $Date = gmdate('Ymd', current_time('timestamp') - 86400 * ($graphdays * $pp-$i-1 ));
                  echo "><div style='float:left;height: 100%;width:100%;font-family:Helvetica;font-size:7pt;text-align:center;border-right:1px solid white;color:black;'>
                  <div style='background:$color;width:100%;height:" . $px->visitors[$Date] . "px;' title='" . $total->visitors[$Date] . " " . __('visitors', 'statpressV')."'></div>
				  <div style='background:$rss_visitors_color;width:100%;height:" . $px->visitors_feeds[$Date]. "px;' title='" . $total->visitors_feeds[$Date] . " " . __('visitors feeds', 'statpressV')."'></div>
                  <div style='background:$pageviews_color;width:100%;height:" . $px->pageviews[$Date] . "px;' title='" . $total->pageviews[$Date] . " " . __('pageviews', 'statpressV')."'></div>
				  <div style='background:$rss_pageviews_color;width:100%;height:" . $px->pageviews_feeds[$Date]. "px;' title='" . $total->pageviews_feeds[$Date] . " " . __('pageviews feeds', 'statpressV')."'></div>
				  <div style='background:$spider_color;width:100%;height:" . $px->spiders[$Date]. "px;' title='" . $total->spiders[$Date] . " " . __('spiders', 'statpressV')."'></div>
                  <div style='background:gray;width:100%;height:1px;'></div>
                  <br />" . gmdate('d', current_time('timestamp') - 86400 * ($graphdays*$pp-$i-1)) . ' ' . gmdate('M', current_time('timestamp') - 86400 * ($graphdays*$pp-$i-1)) . "</div></td>\n";
                 };	
	      }

	function luc_pixel2($total,$graphdays,$maxxday,$pp,$action)
        {  if ($action <> 'overview')
                $heigth = 100; // heigth of the graph
           else $heigth = 200;		   
		   for ($i =0; $i<$graphdays; $i++)
		      { $Date = gmdate('Ymd', current_time('timestamp') - 86400 * ($graphdays * $pp-$i-1 ));
			    $px->visitors[$Date] = round($total->visitors[$Date] * $heigth / $maxxday);
			    $px->visitors_feeds[$Date] = round($total->visitors_feeds[$Date] * $heigth / $maxxday);
			    $px->pageviews[$Date] = round($total->pageviews[$Date] * $heigth / $maxxday);
			    $px->pageviews_feeds[$Date] = round($total->pageviews_feeds[$Date] * $heigth / $maxxday);
			    $px->spiders[$Date] = round($total->spiders[$Date] * $heigth / $maxxday);
			    $px->white[$Date]= $heigth - $px->visitors[$Date]-$px->visitors_feeds[$Date]-$px->pageviews[$Date]-$px->pageviews_feeds[$Date]-$px->spiders[$Date];
				}
			return $px;  
         } 

		 
	function luc_query_graph2($select,$where,$limitdate,$currentdate)
       { global $wpdb;
          $table_name = $wpdb->prefix . "statpress";
		  $qry = $wpdb->get_results("SELECT count($select) AS total, date 
	               FROM $table_name 
	               WHERE  $where AND date BETWEEN $limitdate AND $currentdate
                   GROUP BY date");
		  return $qry;
	    }

		function luc_maxxday2($total,$graphdays,$pp)
       { $maxxd =0;
	     for ( $i =0; $i<$graphdays; $i++)
		 { $Date = gmdate('Ymd', current_time('timestamp') - 86400 * ($graphdays * $pp-$i-1 ));
		   $maxd[$Date]=$total->visitors[$Date]+$total->visitors_feeds[$Date]+$total->pageviews[$Date]+$total->pageviews_feeds[$Date]+$total->spiders[$Date];
		   if ($maxd[$Date] >$maxxd)
		       $maxxd = $maxd[$Date];
		   }
		
        if ($maxxd == 0)
              $maxxd = 1;
		return $maxxd;
        }

	function luc_page_periode()
	   { global $wpdb;
	     // pp is the display page periode 
	     if(isset($_GET['pp']))
          { // Get Current page periode from URL
          	$periode = $_GET['pp'];
          	if($periode <= 0)
          	// Periode is less than 0 then set it to 1
          	 $periode = 1;
          }
          else
           // URL does not show the page set it to 1
			$periode = 1;
		 return $periode;	
	   }

	function luc_page_posts()
			{global $wpdb;
			// pa is the display pages Articles
			if(isset($_GET['pa']))
               { $pageA = $_GET['pa'];// Get Current page Articles from URL
          	     if($pageA <= 0) // Article is less than 0 then set it to 1
          	          $pageA = 1;
               }
            else  // URL does not show the Article set it to 1
          	   $pageA = 1;
			return $pageA;
			}

	function luc_print_pp_link($NP,$pp,$action)
       {  // For all pages ($NP) Display first 3 pages, 3 pages before current page($pp), 3 pages after current page , each 25 pages and the 3 last pages for($action)
           $GUIL1 = FALSE;
           $GUIL2 = FALSE;// suspension points  not writed  style='border:0px;width:16px;height:16px;   style="border:0px;width:16px;height:16px;"
		   if ($NP >1)
		   {if (($action <>"update") OR ($action <>"overview"))
					 echo "<font size='1'>period of days : </font>";
            for ($i = 1; $i <= $NP; $i++) 
             {if ($i <= $NP)
               { // $page is not the last page
			   if($i == $pp)  
	               echo " [{$i}] "; // $page is current page
	           else 
	              { // Not the current page Hyperlink them
	                if (($i <= 3) or (($i >= $pp-3) and ($i <= $pp+3)) or ($i >= $NP-3) or is_int($i/100))
				       { if ($action == "overview")
				           echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '?page=statpress-visitors/admin/luc_admin.php&pp=' . $i .'">' . $i . '</a> ';
					     else  
	                        echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '?page=statpress-visitors/action='.$action.'/&pp=' . $i . '&pa=1'.'">' . $i . '</a> ';
					    }
	                else 
	                   { if (($GUIL1 == FALSE) OR ($i==$pp+4))
	                       {echo "..."; 
						   $GUIL1 = TRUE;
						   }
						 if ($i == $pp-4) 
                             echo "..";						 
						 if (is_int(($i-1)/100)) 
						     echo "."; 
	                     if ($i == $NP-4) 
	                         { echo ".."; 
							 }  
	                  // suspension points writed
	                    }
	             }
               }
			   }
			}
        }

	function luc_print_pp_pa_link($NP,$pp,$action,$NA,$pa)		
        {   if ($NP<>0)
		        luc_print_pp_link($NP,$pp,$action);

			// For all pages ($NP) display first 5 pages, 3 pages before current page($pa), 3 pages after current page , 3 last pages 
            $GUIL1 = FALSE;// suspension points not writed
            $GUIL2 = FALSE;
		    echo '<table width="100%" border="0"><tr></tr></table>';
			if ($NA >1 )
			{echo "<font size='1'>Pages : </font>";
			 for ($j = 1; $j <= $NA; $j++) 
               {if ($j <= $NA)  // $i is not the last Articles page
                 { if($j == $pa)  // $i is current page
	                  echo " [{$j}] ";
	               else { // Not the current page Hyperlink them
	                     if (($j <= 5) or (( $j>=$pa-2) AND ($j <= $pa+2)) or ($j >= $NA-2)) 
					         echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '?page=statpress-visitors/action='.$action.'&pp=' . $pp . '&pa='. $j . '">' . $j . '</a> ';
	                     else 
	                      { if ($GUIL1 == FALSE) 
					            echo "... "; $GUIL1 = TRUE;
	                        if (($j == $pa+4) and ($GUIL2 == FALSE)) 
					            {echo " ... "; 
								$GUIL2 = TRUE;
								}
	                    // suspension points writed
	                      }
	                    }
                  }
				  }
			}
		}

	function luc_count_periode($select,$from,$join="",$where,$group,$graphdays) // count the number total of day, necessary to count the number of page periode link displayed
	   { global $wpdb;
	     $table_name = $wpdb->prefix . "statpress";
		 // selection of the older day 
	     $old_date = $wpdb->get_var("SELECT $select $from $join WHERE $where GROUP BY $group ORDER BY $group ASC LIMIT 1");
		 if(isset($old_date))
		     $nbjours = ceil((current_time('timestamp') - strtotime($old_date))/86400);
		 else 
			 $nbjours =0;
		 $Number = ceil($nbjours / $graphdays);
		 return $Number;
		}

	 function luc_StatPress_lastmonth()
      {
          $ta = getdate(current_time('timestamp'));
          
          $year = $ta['year'];
          $month = $ta['mon'];
          
          // go back 1 month;
          $month = $month - 1;
          
          if ($month === 0)
          {
          	// if this month is Jan
            // go back a year
            $year  = $year - 1;
          	$month = 12;
          }

          // return in format 'YYYYMM'
          return sprintf($year . '%02d', $month);
      }	

      function luc_StatPress_Abbrevia($s, $c)
      {
          $res = "";
          if (strlen($s) > $c)
              $res = "...";
          return my_substr($s, 0, $c) . $res;
      }

	function luc_StatPress_extractfeedreq($url)
{
		if(!strpos($url, '?') === FALSE)
		{
        list($null, $q) = explode("?", $url);
    	list($res, $null) = explode("&", $q);
        }
    else
    {
    	$prsurl = parse_url($url);
    	$res = $prsurl['path'] . $$prsurl['query'];
    }

    return $res;
}


	 function luc_tablesize($table)
      {
          global $wpdb;
          $res = $wpdb->get_results("SHOW TABLE STATUS LIKE '$table'");
          foreach ($res as $fstatus)
          {
              $data_lenght = $fstatus->Data_length;
              $data_rows = $fstatus->Rows;
          }
          return number_format(($data_lenght / 1024 / 1024), 2, ",", " ") . " MB ($data_rows records)";
      }


?>