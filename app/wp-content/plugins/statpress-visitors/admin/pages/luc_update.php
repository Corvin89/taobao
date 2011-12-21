<?php
function  luc_update()
	      { global $wpdb;
            $table_name = $wpdb->prefix . "statpress";
		    $not_collect_spider = get_option('statpressV_not_collect_spider'); // chek if collect or not spider
		    $action="update";
            // last "N" days graph  NEW
			if (($_POST['period'] == 'Apply the new period of days') or ($_POST['update'] == 'Update Now'))
		          update_option('Update_days', $_POST['days']);	
            $Update_days = get_option('Update_days');
            if ($Update_days == '')
                     $Update_days = 7;
            $pp = luc_page_periode();	
		    $NP = luc_count_periode("date","","FROM $table_name","ip IS NOT NULL","date",$Update_days);
		  
		    $limitdate = gmdate('Ymd', current_time('timestamp')-86400 * $Update_days * $pp + 86400);
		    $currentdate = gmdate('Ymd', current_time('timestamp')-86400 * $Update_days *($pp -1));
		    $start_of_week = get_option('start_of_week');
 
			if ($_POST['update'] == 'Update Now')
                 { $wpdb->flush();
				 // update table
				   $temps_deb = microtime(true);
                   luc_StatPressV_CreateTable();
                   // Update Browser
		           if ($_POST['Browsers'] == 'on')
                      { echo "<br><h4>Updating Browsers... ";
					    $wpdb->query("UPDATE $table_name SET browser = '' WHERE date BETWEEN $limitdate AND $currentdate");
	                    $lines = file(ABSPATH.'wp-content/plugins/'.dirname(dirname(dirname(plugin_basename(__FILE__)))) .'/def/browser.dat');		
						$lines = (array) $lines;
						$lines = array_reverse ($lines); // each lines of $lines is read, then we must begin the update in reverse of function luc_StatAppend() do. 
	                    foreach($lines as $line_num => $browser) 
			                  { list($nome,$id)=explode("|",$browser);
							    $qry="UPDATE $table_name SET browser = '$nome' WHERE spider ='' AND date BETWEEN $limitdate AND $currentdate AND replace(agent,' ','') LIKE '%".$id."%'";
		                        $wpdb->query($qry);
	                          }
						echo "done </h4>";	
						$text = $text." Browsers";
				      }
			       if ($_POST['OS'] == 'on')
                      { echo "<h4>Updating OS... ";
					    $wpdb->query("UPDATE $table_name SET os = '' WHERE date BETWEEN $limitdate AND $currentdate");
	                    $lines = file(ABSPATH.'wp-content/plugins/'.dirname(dirname(dirname(plugin_basename(__FILE__)))).'/def/os.dat');
						$lines = (array) $lines;
						$lines = array_reverse ($lines);
	                    foreach($lines as $line_num => $os) 
				              { list($nome_os,$id_os)=explode("|",$os);
		                        $qry="UPDATE $table_name SET os = '$nome_os' WHERE spider ='' AND date BETWEEN $limitdate AND $currentdate AND replace(agent,' ','') LIKE '%".$id_os."%'";
		                        $wpdb->query($qry);
	                          }	 
                        echo "done </h4><br>";
						if (isset($text)) $text .= ',';
						$text = $text." OS";
				      }	  
			       if ($_POST['Searchsengines'] == 'on')	  
                      { // Update Search engine
                        echo "<h4>Updating Search engines... ";
						$wpdb->query("UPDATE $table_name SET searchengine = '', search=''; WHERE date BETWEEN $limitdate AND $currentdate");
                        $qry = $wpdb->get_results("SELECT id, referrer FROM $table_name WHERE referrer <> '' AND feed ='' AND date BETWEEN $limitdate AND $currentdate 
		                                AND referrer NOT LIKE '%" . get_bloginfo('url') . "%'");									
                        foreach ($qry as $rk)
                             { list($searchengine, $search_phrase) = explode("|", luc_GetSE($rk->referrer));			 
                               if ($searchengine <> '')
                                   { $qry = "UPDATE $table_name SET searchengine = '$searchengine', search='" . addslashes($search_phrase) . "' WHERE feed ='' AND spider = '' AND date BETWEEN $limitdate AND $currentdate AND id=" . $rk->id;
                                     $wpdb->query($qry);
                                   }
                             }
						echo "done </h4><br>";	 
						if (isset($text)) $text .= ',';
						$text = $text." Searchs engines";
				       }
			       if (($_POST['Spiders'] == 'on') AND ($not_collect_spider ==''))
		             { // Update Spider
                        echo  "<h4>Updating Spiders... ";
						$wpdb->query("UPDATE $table_name SET spider = '' WHERE date BETWEEN $limitdate AND $currentdate");
	                    $lines = file(ABSPATH.'wp-content/plugins/'.dirname(dirname(dirname(plugin_basename(__FILE__)))).'/def/spider.dat');
						$lines = (array) $lines;
						$lines = array_reverse ($lines);
	                    foreach($lines as $line_num => $spider) 
			                 { list($nome,$id)=explode("|",$spider);
		                       $qry="UPDATE $table_name SET spider = '$nome',browser='' WHERE os ='' AND date BETWEEN $limitdate AND $currentdate AND replace(agent,' ','') LIKE '%".$id."%'";
		                       $wpdb->query($qry);
	                         }
                        echo "done </h4><br>";
						if (isset($text)) $text .= ',';
						$text = $text." Spiders";
                     }
					if ($_POST['Domain'] == 'on') 
		             { // Update Domain
                        echo  "<h4>Updating Domain... ";
						$qry=$wpdb->get_results("SELECT ip FROM $table_name WHERE date BETWEEN $limitdate AND $currentdate GROUP BY ip");
	                    foreach($qry as $ip) 
			                 { $ipadress = $ip->ip;
		                       $domain = luc_Domain($ipadress); 
							   $wpdb->query("UPDATE $table_name SET nation = '$domain' WHERE ip ='$ipadress'");
	                         }
                        echo "done </h4><br>";
						if (isset($text)) $text .= ',';
						$text = $text." Domain";
                     } 
			       echo "<h3>Updated ".$text." from ".gmdate('d M, Y', strtotime($limitdate))."  to  ".gmdate('d M, Y', strtotime($currentdate))." !</h3>";
			       $temps_fin = microtime(true);
				   $Nbsql=$wpdb->num_queries;
                   echo '<h4>Information :</h4>
                   Duration of the update : '.round($temps_fin - $temps_deb, 2).' sec<br>
				   This update was done in ',$Nbsql, ' SQL queries.';
				 }
            echo '</tr></table></div>';  
			$text ="Update Browsers OS Searchs Engines ";
			if ($not_collect_spider =='') $text .= "Spiders ";
			$text .= "Domain ";
			echo "<div class='wrap'><h2>".$text."</h2>";
			
		?> 
            <form method=post  ><table width=100%>
	        <br>

Do not wait for an update of StatPress Visitors to reflect the new <strong>browsers</strong>, <strong>OS</strong>, <strong>Search Engine</strong> and <strong>Spiders</strong>. Do the <strong>update</strong> of your database's data by <strong>yourself</strong> ! <br>
For informations on how to update the OS, browsers and their icon with StatPress Visitors, see here : <a href='http://additifstabac.free.fr/index.php/how-to-update-definitions-os-browsers-of-statpress-visitors/' target='_blank'>Learn more</a><br><br>
<?php 
  
            echo "<h4>The period of ".$Update_days." days selected for the update is from ".gmdate('d M, Y', strtotime($limitdate))."  to ".gmdate('d M, Y', strtotime($currentdate))." !</h4><p>To change the period of days selected, simply click on the number below...<br>"; 
            luc_print_pp_link($NP,$pp,$action); 
            echo '<br><br>Select the number of days per period';

   ?>
  <select name=days >
  <option value="1" <?php if ($Update_days == 1)
                             echo "selected"; ?>>1</option>
  <option value="2" <?php if ($Update_days == 2)
                             echo "selected"; ?>>2</option>						 
  <option value="7" <?php if ($Update_days == 7)
                             echo "selected"; ?>>7</option>
  <option value="15" <?php if ($Update_days == 15)
                             echo "selected"; ?>>15</option>
  <option value="21" <?php if ($Update_days == 21)
                             echo "selected"; ?>>21</option>
  <option value="31" <?php if ($Update_days == 31)
                             echo "selected"; ?>>31</option>
  <option value="62" <?php if ($Update_days == 62)
                             echo "selected"; ?>>62</option>
  <option value="92" <?php if ($Update_days == 92)
                             echo "selected"; ?>>92</option>	
  <option value="183" <?php if ($Update_days == 183)
                             echo "selected"; ?>>183</option>
  <option value="366" <?php if ($Update_days == 366)
                             echo "selected"; ?>>366</option>							 
  </select></td></tr><br><br>
  <input type=submit name=period value='Apply the new period of days' class="button-secondary" ></td></tr>
  
	   <table width=100%>
       <h3>Select the datas you want to update :</h3><p>
       <input type="checkbox" name="Browsers" id="Browsers" /> <label for="Browsers">Browsers</label><br />
       <input type="checkbox" name="OS" id="OS" /> <label for="OS">OS</label><br />
       <input type="checkbox" name="Searchsengines" id="Searchsengines" /> <label for="Searchsengines">Searchs engines</label><br />
       <?php if ($not_collect_spider =='')
	              echo '<input type="checkbox" name="Spiders" id="Spiders" /> <label for="Spiders">Spiders</label><br />'; ?>
	   <input type="checkbox" name="Domain" id="Domain" /> <label for="Domain">Domain</label><br />		  
	   <br><br>
	   <input type=submit name=update value='Update Now' class="button-primary" ></td></tr>
   </p></table>
         </form>
        <?php 
		  
      }
?>