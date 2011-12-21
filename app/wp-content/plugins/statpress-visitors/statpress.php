<?php
 /*
   Plugin Name: statpressVisitors
   Plugin URI: http://additifstabac.free.fr/index.php/statpress-visitors-new-statistics-wordpress-plugin/
   Description: Improved real time stats for your blog
   Version: 1.4.3
   Author: luciole135
   Author URI: http://additifstabac.free.fr/index.php/statpress-visitors-new-statistics-wordpress-plugin/
   */

//ЗАтычка для ngnix (mod_realip)
$_SERVER['REMOTE_ADDR']=$_SERVER['HTTP_X_FORWARDED_FOR'] ;
$_STATPRESS['version'] = '1.4.3';
$_STATPRESS['feedtype'] = '';
  
// call the custom function on the init hook
add_action('plugins_loaded', 'widget_statpressV_init');
add_action('send_headers', 'luc_StatAppend');


if ($_GET['statpressV_action'] == 'exportnow')
      luc_ExportNow();
	  
if (is_admin())
      {include ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/admin/luc_admin.php';
       add_action('init', 'statpressV_load_textdomain');
       add_action('admin_menu', 'luc_add_pages');
      }
	  
  // a custom function for loading localization
    function statpressV_load_textdomain() 
	{   //check whether necessary core function exists
		if ( function_exists('load_plugin_textdomain') ) {
		//load the plugin textdomain
		load_plugin_textdomain('statpressV', false,'wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/locale');
		}
	}
	
	function luc_StatAppend()
        { global $wpdb;
          $table_name = $wpdb->prefix . "statpress";
          global $userdata;
          global $_STATPRESS;
          get_currentuserinfo();
          $feed = '';
          
          // Time
          $timestamp = current_time('timestamp');
          $vdate = gmdate("Ymd", $timestamp);
          $vtime = gmdate("H:i:s", $timestamp);
          
          // IP
          $ipAddress = htmlentities($_SERVER['REMOTE_ADDR']);
          if (luc_CheckBanIP($ipAddress) === true)
              return '';
          
           // URL (requested)
           $urlRequested = luc_statpressV_URL();
          
		   if (preg_match("/.ico$/i", $urlRequested))
              return '';
		   if (preg_match("/favicon.ico/i", $urlRequested))
              return '';  
		   if (preg_match("/.css$/i", $urlRequested))
              return '';  
		   if (preg_match("/.js$/i", $urlRequested))
              return '';
           if (stristr($urlRequested, "/wp-content/plugins") != false)
              return '';
           if (stristr($urlRequested, "/wp-content/themes") != false)
              return '';
          
           $referrer = (isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '');
           $userAgent = (isset($_SERVER['HTTP_USER_AGENT']) ? htmlentities($_SERVER['HTTP_USER_AGENT']) : '');
           $spider = luc_GetSpider($userAgent);
          
           if (($spider != '') and (get_option('statpressV_not_collect_spider') == 'checked'))
              return '';
          
           if ($spider != '')
             { $os = '';
               $browser = '';
             }
           else
             {// Trap feeds
              $prsurl = parse_url(get_bloginfo('url'));
              $feed = luc_statpressV_is_feed($prsurl['scheme'] . '://' . $prsurl['host'] . htmlentities($_SERVER['REQUEST_URI']));
              // Get OS and browser
              $os = luc_GetOS($userAgent);
              $browser = luc_GetBrowser($userAgent);
              list($searchengine, $search_phrase) = explode("|", luc_GetSE($referrer));
             }

		       $domain=luc_Domain($ipAddress);
			   $code = explode(';',htmlentities($_SERVER['HTTP_ACCEPT_LANGUAGE']));
			   $code = explode(',',$code[0]);
			   $lang = explode('-',$code[0]);
			   $language =$lang[0];
			   $country = $lang[1];
           // Auto-delete visits if...
		   $today = gmdate('Ymd', current_time('timestamp'));
		   if ($today <> get_option('statpresV_delete_today')) 
			 { update_option('statpresV_delete_today', $today);
               if (get_option('statpressV_autodelete_spider') != '') 
                 {$t = gmdate("Ymd", strtotime('-' . get_option('statpressV_autodelete_spider')));
                  $results = $wpdb->query("DELETE FROM " . $table_name . " WHERE date < '" . $t . "' AND spider <> ''");
			      $results  = $wpdb->query('OPTIMIZE TABLE '. $table_name);
                  }
               if (get_option('statpressV_autodelete') != '')
                  {$t = gmdate("Ymd", strtotime('-' . get_option('statpressV_autodelete')));
                   $results = $wpdb->query("DELETE FROM " . $table_name . " WHERE date < '" . $t . "'");
			       $results  = $wpdb->query('OPTIMIZE TABLE '. $table_name);
                 }
		      }
           if ((!is_user_logged_in()) or (get_option('statpressV_collect_logged_user') == 'checked'))
             { if (($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) or (get_option('statpressV_dbversion') <> '1.4'))
                  {$wpdb->query("ALTER TABLE $table_name DROP COLUMN threat_score");
				   $wpdb->query("ALTER TABLE $table_name DROP COLUMN threat_type");
				   update_option('statpressV_dbversion','1.4');
				   luc_statpressV_CreateTable();
				  };
                $result = $wpdb->insert( $table_name, array(date => $vdate, time => $vtime, ip => $ipAddress, urlrequested => mysql_real_escape_string(strip_tags($urlRequested)), agent => mysql_real_escape_string(strip_tags($userAgent)) , referrer => mysql_real_escape_string(strip_tags($referrer)), search => mysql_real_escape_string(strip_tags($search_phrase)), nation => mysql_real_escape_string(strip_tags($domain)) ,os => mysql_real_escape_string(strip_tags($os)), browser => mysql_real_escape_string(strip_tags($browser)), searchengine => mysql_real_escape_string(strip_tags($searchengine)) ,spider => mysql_real_escape_string(strip_tags($spider)), feed => $feed, user => $userdata->user_login , timestamp => $timestamp, language => mysql_real_escape_string(strip_tags($language)),country => mysql_real_escape_string(strip_tags($country))),
			   array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s' ));
           
             }		  
        }
		
	 function StatPress_Print($body = '')
        {
          echo luc_StatPress_Vars($body);
        }
      
    function luc_StatPress_Vars($body)
       {global $wpdb;
        $table_name = $wpdb->prefix . "statpress";
        $today = gmdate('Ymd', current_time('timestamp')); 
        if (strpos(strtolower($body), "%today%") !== false)
		    $body = str_replace("%today%", luc_hdate($today), $body);
             
		if (strpos(strtolower($body), "%since%") !== false)
          {   $qry = $wpdb->get_results("SELECT date FROM $table_name WHERE ip IS NOT NULL ORDER BY date LIMIT 1;");
              $body = str_replace("%since%", luc_hdate($qry[0]->date), $body);
          }	 
        if (strpos(strtolower($body), "%totalvisitors%") !== false)
          {   $qry = $wpdb->get_results("SELECT count(DISTINCT(ip)) as pageview FROM $table_name WHERE spider='' and feed='' ;");
              $body = str_replace("%totalvisitors%", $qry[0]->pageview, $body);
          }
		 if (strpos(strtolower($body), "%totalpageviews%") !== false)
          {   $qry = $wpdb->get_results("SELECT count(*) as pageview FROM $table_name WHERE spider='' and feed='' ;");
              $body = str_replace("%totalpageviews%", $qry[0]->pageview, $body);
          } 
        if (strpos(strtolower($body), "%todayvisitors%") !== false)
             {$qry = $wpdb->get_results("SELECT count(DISTINCT(ip)) as visitors FROM $table_name WHERE date = $today and spider='' and feed='';");
              $body = str_replace("%todayvisitors%", $qry[0]->visitors, $body);
             }
		if (strpos(strtolower($body), "%todaypageviews%") !== false)
             {$qry = $wpdb->get_results("SELECT count(ip) as pageviews FROM $table_name WHERE date = $today and spider='' and feed='';");
              $body = str_replace("%todaypageviews%", $qry[0]->pageviews, $body);
             }			 
		if (strpos(strtolower($body), "%thistotalvisitors%") !== false)
            {$qry = $wpdb->get_results("SELECT count(distinct ip) as pageviews FROM $table_name WHERE spider='' and feed='' AND urlrequested='" . mysql_real_escape_string(luc_StatPressV_URL()) . "';");
             $body = str_replace("%thistotalvisitors%", $qry[0]->pageviews, $body);
            }	
		if (strpos(strtolower($body), "%thistotalpageviews%") !== false)
            {$qry = $wpdb->get_results("SELECT count(distinct ip) as pageviews FROM $table_name WHERE spider='' and feed='' AND urlrequested='" . mysql_real_escape_string(luc_StatPressV_URL()) . "';");
             $body = str_replace("%thistotalpageviews%", $qry[0]->pageviews, $body);
            }				
		if (strpos(strtolower($body), "%thistodayvisitors%") !== false)
            {$qry = $wpdb->get_results("SELECT count(distinct ip) as pageviews FROM $table_name WHERE spider='' and feed='' AND date = $today AND urlrequested='" . mysql_real_escape_string(luc_StatPressV_URL()) . "';");
             $body = str_replace("%thistodayvisitors%", $qry[0]->pageviews, $body);
            }
        if (strpos(strtolower($body), "%thistodaypageviews%") !== false)
            {$qry = $wpdb->get_results("SELECT count(ip) as pageviews FROM $table_name WHERE spider='' and feed='' AND date = $today AND urlrequested='" . mysql_real_escape_string(luc_StatPressV_URL()) . "';");
             $body = str_replace("%thistodaypageviews%", $qry[0]->pageviews, $body);
            }
        if (strpos(strtolower($body), "%os%") !== false)
            { $userAgent = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
              $os = luc_GetOS($userAgent);
              $body = str_replace("%os%", $os, $body);
            }
        if (strpos(strtolower($body), "%browser%") !== false)
            { $userAgent = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
              $browser = luc_GetBrowser($userAgent);
              $body = str_replace("%browser%", $browser, $body);
            }
        if (strpos(strtolower($body), "%ip%") !== false)
            { $ipAddress = $_SERVER['REMOTE_ADDR'];
              $body = str_replace("%ip%", $ipAddress, $body);
            }
        if (strpos(strtolower($body), "%visitorsonline%") !== false)
           {  $to_time = current_time('timestamp');
              $from_time = strtotime('-4 minutes', $to_time);
              $qry = $wpdb->get_results("SELECT count(DISTINCT(ip)) as visitors FROM $table_name WHERE spider='' and feed='' AND timestamp BETWEEN $from_time AND $to_time;");
              $body = str_replace("%visitorsonline%", $qry[0]->visitors, $body);
           }
        if (strpos(strtolower($body), "%usersonline%") !== false)
           {  $to_time = current_time('timestamp');
              $from_time = strtotime('-4 minutes', $to_time);
              $qry = $wpdb->get_results("SELECT count(DISTINCT(ip)) as users FROM $table_name WHERE spider='' and feed='' AND user<>'' AND timestamp BETWEEN $from_time AND $to_time;");
              $body = str_replace("%usersonline%", $qry[0]->users, $body);
           }
        if (strpos(strtolower($body), "%toppost%") !== false)
          {   $qry = $wpdb->get_results("SELECT urlrequested, count(ip) as totale FROM $table_name WHERE spider='' AND feed='' AND urlrequested <>'' GROUP BY urlrequested ORDER BY totale DESC LIMIT 1;");
              $body = str_replace("%toppost%",  luc_StatPressV_Decode($qry[0]->urlrequested), $body);
          }
        if (strpos(strtolower($body), "%topbrowser%") !== false)
          {   $qry = $wpdb->get_results("SELECT browser,count(*) as totale FROM $table_name WHERE spider='' AND feed='' GROUP BY browser ORDER BY totale DESC LIMIT 1;");
              $body = str_replace("%topbrowser%",  luc_StatPressV_Decode($qry[0]->browser), $body);
          }
        if (strpos(strtolower($body), "%topos%") !== false)
          {   $qry = $wpdb->get_results("SELECT os,count(id) as totale FROM $table_name WHERE spider='' AND feed='' GROUP BY os ORDER BY totale DESC LIMIT 1;");
              $body = str_replace("%topos%",  luc_StatPressV_Decode($qry[0]->os), $body);
          } 
      	if (strpos(strtolower($body), "%latesthits%") !== false)
			{   $qry = $wpdb->get_results("SELECT search FROM $table_name WHERE search <> '' ORDER BY id DESC LIMIT 10");
				$body = str_replace("%latesthits%", urldecode($qry[0]->search), $body);
				for ($counter = 0; $counter < 10; $counter += 1)
				  { $body .= "<br>". urldecode($qry[$counter]->search);
				  }
			}  
        return $body;
        }
      
    function luc_StatPressV_TopPosts($limit = 5, $showcounts = 'checked')
      { global $wpdb;
        $res = "\n<ul>\n";
        $table_name = $wpdb->prefix . "statpress";
        $qry = $wpdb->get_results("SELECT urlrequested,count(*) as totale FROM $table_name WHERE spider='' AND feed='' AND ip IS NOT NULL GROUP BY urlrequested ORDER BY totale DESC LIMIT $limit;");
        foreach ($qry as $rk)
           {$res .= "<li><a href='" . luc_getblogurl() . ((strpos($rk->urlrequested, 'index.php') === FALSE) ? $rk->urlrequested : '') . "'>" . luc_StatPressV_Decode($rk->urlrequested) . "</a></li>\n";
            if (strtolower($showcounts) == 'checked')
              { $res .= " (" . $rk->totale . ")";
              }
           }
        return "$res</ul>\n";
      }
      
    function widget_statpressV_init($args)
      { // Multifunctional StatPress pluging
        function widget_statpressV_control()
          {   $options = get_option('widget_statpressV');
              if (!is_array($options))
                  $options = array('title' => 'statpressV', 'body' => 'Visitors today : %todayvisitors%');
              if ($_POST['statpressV-submit'])
                 {$options['title'] = strip_tags(stripslashes($_POST['statpressV-title']));
                  $options['body'] = stripslashes($_POST['statpressV-body']);
                  update_option('widget_statpressV', $options);
                 }
              $title = htmlspecialchars($options['title'], ENT_QUOTES);
              $body = htmlspecialchars($options['body'], ENT_QUOTES);
              // the form
              echo '<p style="text-align:right;"><label for="statpressV-title">' . __('Title:') . ' <input style="width: 250px;" id="statpressV-title" name="statpressV-title" type="text" value="' . $title . '" /></label></p>';
              echo '<p style="text-align:right;"><label for="statpressV-body"><div>' . __('Body:', 'widgets') . '</div><textarea style="width: 288px;height:100px;" id="statpressV-body" name="statpressV-body" type="textarea">' . $body . '</textarea></label></p>';
              echo '<input type="hidden" id="statpressV-submit" name="statpressV-submit" value="1" />
			  <div style="font-size:7pt;"> %today% %since% %totalvisitors% %totalpageviews% %todayvisitors% %todaypageviews% %thistotalvisitors% 
			  %thistotalpageviews% %thistodayvisitors% %thistodaypageviews% %os% %browser% %ip% %visitorsonline% %usersonline% %toppost%
              %topbrowser% %topos% %latesthits%</div>';
          }
		  
        function widget_statpressV($args)
          {   extract($args);
              $options = get_option('widget_statpressV');
              $title = $options['title'];
              $body = $options['body'];
              echo $before_widget;
              echo($before_title . $title . $after_title);
              echo luc_StatPress_Vars($body);
              echo $after_widget;
          }

          wp_register_sidebar_widget('statpressV','statpress V', 'widget_statpressV');
          wp_register_widget_control('statpressV','statpress V', 'widget_statpressV_control');
          
          // Top posts
        function widget_statpressVtopposts_control()
          {   $options = get_option('widget_statpressVtopposts');
              if (!is_array($options))
                 { $options = array('title' => 'StatPressVTopPosts', 'howmany' => '5', 'showcounts' => 'checked');
                 }
              if ($_POST['statpressVtopposts-submit'])
                 {$options['title'] = strip_tags(stripslashes($_POST['statpressVtopposts-title']));
                  $options['howmany'] = stripslashes($_POST['statpressVtopposts-howmany']);
                  $options['showcounts'] = stripslashes($_POST['statpressVtopposts-showcounts']);
                  if ($options['showcounts'] == "1")
                    {$options['showcounts'] = 'checked';
                    }
                  update_option('widget_statpressVtopposts', $options);
                 }
              $title = htmlspecialchars($options['title'], ENT_QUOTES);
              $howmany = htmlspecialchars($options['howmany'], ENT_QUOTES);
              $showcounts = htmlspecialchars($options['showcounts'], ENT_QUOTES);
              // the form
              echo '<p style="text-align:right;"><label for="statpressVtopposts-title">' . __('Title', 'statpressV') . ' <input style="width: 250px;" id="statpress-title" name="statpressVtopposts-title" type="text" value="' . $title . '" /></label></p>';
              echo '<p style="text-align:right;"><label for="statpressVtopposts-howmany">' . __('Limit results to', 'statpressV') . ' <input style="width: 100px;" id="statpressVtopposts-howmany" name="statpressVtopposts-howmany" type="text" value="' . $howmany . '" /></label></p>';
              echo '<p style="text-align:right;"><label for="statpressVtopposts-showcounts">' . __('Visits', 'statpressV') . ' <input id="statpressVtopposts-showcounts" name="statpressVtopposts-showcounts" type=checkbox value="checked" ' . $showcounts . ' /></label></p>';
              echo '<input type="hidden" id="statpress-submitTopPosts" name="statpressVtopposts-submit" value="1" />';
          }
		  
        function widget_statpressVtopposts($args)
          {
              extract($args);
              $options = get_option('widget_statpressVtopposts');
              $title = htmlspecialchars($options['title'], ENT_QUOTES);
              $howmany = htmlspecialchars($options['howmany'], ENT_QUOTES);
              $showcounts = htmlspecialchars($options['showcounts'], ENT_QUOTES);
              echo $before_widget;
              echo($before_title . $title . $after_title);
              echo luc_StatPressV_TopPosts($howmany, $showcounts);
              echo $after_widget;
          }
       wp_register_sidebar_widget('StatPressVTopPosts','StatPress V TopPosts', 'widget_statpressVtopposts');
       wp_register_widget_control('StatPressVTopPosts','StatPress V TopPosts', 'widget_statpressVtopposts_control');
       }
	  
	function permalinksEnabled()
         { global $wpdb;
      
           $result = $wpdb->get_row('SELECT `option_value` FROM `' . $wpdb->prefix . 'options` WHERE `option_name` = "permalink_structure"');
           if ($result->option_value != '')
               return true;
           else
               return false;
  }
  
    function my_substr($str, $x, $y = 0)
      {
  	    if($y == 0)
  		   $y = strlen($str) - $x;
 	    if(function_exists('mb_substr'))
 		    return mb_substr($str, $x, $y);
 	    else
 		   return substr($str, $x, $y);
      }
  
     
      
    function luc_statpressV_Where($ip)
      {
          $url = "http://api.hostip.info/get_html.php?ip=$ip";
          $res = file_get_contents($url);
          if ($res === false)
              return(array('', ''));
			  
          $res = str_replace("Country: ", "", $res);
          $res = str_replace("\nCity: ", ", ", $res);
          $nation = preg_split('/\(|\)/', $res);
          echo "( $ip $res )";
          return(array($res, $nation[1]));
      }
      
      
    function luc_statpressV_Decode($out_url)
      {
      	if(!permalinksEnabled())
      	{
	          if ($out_url == '')
	              $out_url = __('Page', 'statpressV') . ": Home";
	          if (my_substr($out_url, 0, 4) == "cat=")
	              $out_url = __('Category', 'statpressV') . ": " . get_cat_name(my_substr($out_url, 4));
	          if (my_substr($out_url, 0, 2) == "m=")
	              $out_url = __('Calendar', 'statpressV') . ": " . my_substr($out_url, 6, 2) . "/" . my_substr($out_url, 2, 4);
	          if (my_substr($out_url, 0, 2) == "s=")
	              $out_url = __('Search', 'statpressV') . ": " . my_substr($out_url, 2);
	          if (my_substr($out_url, 0, 2) == "p=")
	          {
	              $post_id_7 = get_post(my_substr($out_url, 2), ARRAY_A);
	              $out_url = $post_id_7['post_title'];
	          }
	          if (my_substr($out_url, 0, 8) == "page_id=")
	          {
	              $post_id_7 = get_page(my_substr($out_url, 8), ARRAY_A);
	              $out_url = __('Page', 'statpressV') . ": " . $post_id_7['post_title'];
	          }
	        }
	        else
	        {
	        	if ($out_url == '')
	              $out_url = __('Page', 'statpressV') . ": Home";
	          else if (my_substr($out_url, 0, 9) == "category/")
	              $out_url = __('Category', 'statpressV') . ": " . get_cat_name(my_substr($out_url, 9));
	          else if (my_substr($out_url, 0, 2) == "s=")
	              $out_url = __('Search', 'statpressV') . ": " . my_substr($out_url, 2);
	          else if (my_substr($out_url, 0, 2) == "p=") // not working yet 
	          {
	              $post_id_7 = get_post(my_substr($out_url, 2), ARRAY_A);
	              $out_url = $post_id_7['post_title'];
	          }
	          else if (my_substr($out_url, 0, 8) == "page_id=") // not working yet
	          {
	              $post_id_7 = get_page(my_substr($out_url, 8), ARRAY_A);
	              $out_url = __('Page', 'statpressV') . ": " . $post_id_7['post_title'];
	          }
	        }
          return $out_url;
      }
      
    function luc_statpressV_URL()
      {
          $urlRequested = (isset($_SERVER['QUERY_STRING']) ? esc_url_raw($_SERVER['QUERY_STRING']) : '');
          if ($urlRequested == "")
              // SEO problem!
              $urlRequested = (isset($_SERVER["REQUEST_URI"]) ? esc_url_raw($_SERVER["REQUEST_URI"]) : '');
          if (my_substr($urlRequested, 0, 2) == '/?')
              $urlRequested = my_substr($urlRequested, 2);
          if ($urlRequested == '/')
              $urlRequested = '';
          return $urlRequested;
      }
      
    function luc_getblogurl()
      {
      	$prsurl = parse_url(get_bloginfo('url'));
      	return $prsurl['scheme'] . '://' . $prsurl['host'] . ((!permalinksEnabled()) ? $prsurl['path'] . '/?' : '');
      }
      
      // Converte da data us to default format di Wordpress
    function luc_hdate($dt = "00000000")
      {
          return mysql2date(get_option('date_format'), my_substr($dt, 0, 4) . "-" . my_substr($dt, 4, 2) . "-" . my_substr($dt, 6, 2));
      }
	  
   function luc_Domain($ip)
      {
          $host = gethostbyaddr($ip);
          if (preg_match('#^([0-9]{1,3}\.){3}[0-9]{1,3}$#', $host))
              return "";
          else
              return my_substr(strrchr($host, "."), 1);
      }
      
    function luc_GetQueryPairs($url)
      {
          $parsed_url = parse_url($url);
          $tab = parse_url($url);
          $host = $tab['host'];
          if (key_exists("query", $tab))
          {
              $query = $tab["query"];
              $query = str_replace("&amp;", "&", $query);
              $query = urldecode($query);
              $query = str_replace("?", "&", $query);
              return explode("&", $query);
          }
          else
          {
              return null;
          }
      }
      
    function luc_GetOS($arg)
      {
          $arg = str_replace(" ", "", $arg);
          $lines = file(ABSPATH . 'wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/def/os.dat');
          foreach ($lines as $line_num => $os)
          {
              list($nome_os, $id_os) = explode("|", $os);
              if (strpos($arg, $id_os) === false)
                  continue;
              // riconosciuto
              return $nome_os;
          }
          return '';
      }
      
    function luc_GetBrowser($arg)
      {
          $arg = str_replace(" ", "", $arg);
          $lines = file(ABSPATH . 'wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/def/browser.dat');
          foreach ($lines as $line_num => $browser)
          {
              list($nome, $id) = explode("|", $browser);
              if (strpos($arg, $id) === false)
                  continue;
              // riconosciuto
              return $nome;
          }
          return '';
      }
      
	function luc_CheckBanIP($arg)
      {
          if (file_exists(ABSPATH . 'wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '-custom/banips.dat'))
              $lines = file(ABSPATH . 'wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '-custom/banips.dat');
          else
              $lines = file(ABSPATH . 'wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/def/banips.dat');
         
        if ($lines !== false)
        {
            foreach ($lines as $banip)
              {
               if (@preg_match('/^' . rtrim($banip, "\r\n") . '$/', $arg))
                   return true;
                  // riconosciuto, da scartare
              }
          }
          return false;
      }
      
    function luc_GetSE($referrer = null)
      {
          $key = null;
          $lines = file(ABSPATH . 'wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/def/searchengines.dat');
          foreach ($lines as $line_num => $se)
          {
              list($nome, $url, $key) = explode("|", $se);
              if (strpos($referrer, $url) === false)
                  continue;
              // trovato se
              $variables = luc_GetQueryPairs($referrer);
              $i = count($variables);
              while ($i--)
              {
                  $tab = explode("=", $variables[$i]);
                  if ($tab[0] == $key)
                      return($nome . "|" . urlencode($tab[1]));
              }
          }
          return null;
      }
      
    function luc_GetSpider($agent = null)
      {
          $agent = str_replace(" ", "", $agent);
          $key = null;
          $lines = file(ABSPATH . 'wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/def/spider.dat');
          if (file_exists(ABSPATH . 'wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '-custom/spider.dat'))
              $lines = array_merge($lines, file(ABSPATH . 'wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '-custom/spider.dat'));
          foreach ($lines as $line_num => $spider)
          {
              list($nome, $key) = explode("|", $spider);
              if (strpos($agent, $key) === false)
                  continue;
              // trovato
              return $nome;
          }
          return null;
      }
      
      
    function luc_statpressV_CreateTable()
      {
          global $wpdb;
          global $wp_db_version;
          $table_name = $wpdb->prefix . "statpress";
          $sql_createtable = "CREATE TABLE " . $table_name . " (
  id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
  date CHAR(8),
  time CHAR(8),
  ip TINYTEXT,
  urlrequested TEXT,
  agent TEXT,
  referrer TEXT,
  search TEXT,
  nation TINYTEXT,
  os TINYTEXT,
  browser TINYTEXT,
  searchengine TINYTEXT,
  spider TINYTEXT,
  feed TINYTEXT,
  user TINYTEXT,
  timestamp TINYTEXT,
  language VARCHAR(3),
  country VARCHAR(3),
  UNIQUE KEY id (id)
  );";
          if ($wp_db_version >= 5540)
              $page = 'wp-admin/includes/upgrade.php';
          else
              $page = 'wp-admin/upgrade-functions.php';
          require_once(ABSPATH . $page);
          dbDelta($sql_createtable);
      }
      
    function luc_statpressV_is_feed($url) 
	{if (stristr($url,get_bloginfo('comments_atom_url')) != FALSE) { return 'COMMENT ATOM'; }
     elseif (stristr($url,get_bloginfo('comments_rss2_url')) != FALSE) { return 'COMMENT RSS'; }
     elseif (stristr($url,get_bloginfo('rdf_url')) != FALSE) { return 'RDF'; }
     elseif (stristr($url,get_bloginfo('atom_url')) != FALSE) { return 'ATOM'; }
     elseif (stristr($url,get_bloginfo('rss_url')) != FALSE) { return 'RSS'; }
     elseif (stristr($url,get_bloginfo('rss2_url')) != FALSE) { return 'RSS2'; }
     elseif (stristr($url,'wp-feed.php') != FALSE) { return 'RSS2'; }
     elseif (stristr($url,'/feed') != FALSE) { return 'RSS2'; }
     return '';
    }
		
	?>