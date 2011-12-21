<?php
function luc_spybot()
	  {   global $wpdb;

	        $action="spybot";
            $table_name = $wpdb->prefix . "statpress";
		
			$LIMIT = get_option('statpressV_bots_per_page_spybot');
		    $LIMIT_PROOF = get_option('statpressV_visits_per_bot_spybot');
			if ($LIMIT ==0) $LIMIT = 10;
            if ($LIMIT_PROOF == 0) $LIMIT_PROOF = 30;
			$pa = luc_page_posts();
			$LimitValue = ($pa * $LIMIT) - $LIMIT;
			
			// limit the search 7 days ago
			$day_ago = gmdate('Ymd', current_time('timestamp') - 7*86400);	   
            $MinId = $wpdb->get_var("SELECT min(id) as MinId FROM $table_name WHERE date > $day_ago");	
			
			// Number of distinct spiders after $day_ago
			$Num = $wpdb->get_var("SELECT count(distinct spider) FROM $table_name WHERE spider<>'' AND id >$MinId");
			$NA = ceil($Num/$LIMIT);
			
			echo "<div class='wrap'><h2>" . __('Bot Spy', 'statpress') . "</h2>";
			
            // selection of spider, group by spider, order by most recently visit (last id in the table)
			$sql = "SELECT *
			        FROM $table_name as T1
					JOIN
			       (SELECT spider,max(id) as MaxId FROM $table_name WHERE spider<>'' GROUP BY spider ORDER BY MaxId DESC LIMIT $LimitValue, $LIMIT ) as T2
				    ON T1.spider = T2.spider
                    WHERE T1.id > $MinId				
			        ORDER BY MaxId DESC, id DESC";
			
			$qry = $wpdb->get_results($sql);
            echo '<div align="center">';
			luc_print_pp_pa_link (0,0,$action,$NA,$pa);
			echo '</div><div align="left">';
		?>
<script>
function ttogle(thediv){
if (document.getElementById(thediv).style.display=="inline") {
document.getElementById(thediv).style.display="none"
} else {document.getElementById(thediv).style.display="inline"}
}
</script>
<table id="mainspytab" name="mainspytab" width="99%" border="0" cellspacing="0" cellpadding="4"><div align='left'>
   <?php        $spider="robot";
		  $num_row=0;
          foreach ($qry as $rk)
          {  // Bot Spy
			if ($robot <> $rk->spider)  
			    {echo "<div align='left'>
				<tr>
				<td colspan='2' bgcolor='#dedede'>";
			     $img=str_replace(" ","_",strtolower($rk->spider));
			     $img=str_replace('.','',$img).".png";
				 $lines = file(ABSPATH.'wp-content/plugins/'.dirname(dirname(dirname(plugin_basename(__FILE__)))) .'/def/spider.dat');
				 foreach($lines as $line_num => $spider) //seeks the tooltip corresponding to the photo
			                  { list($title,$id)=explode("|",$spider);
							    if($title==$rk->spider) break; // break, the tooltip ($title) is found
					            }
				 echo "<IMG style='border:0px;height:16px;align:left;' alt='".$title."' title='".$title."' SRC='" .plugins_url('statpress-visitors/images/spider/'.$img, dirname(dirname(dirname(__FILE__)))). "'>    		
				 <span style='color:#006dca;cursor:pointer;border-bottom:1px dotted #AFD5F9;font-size:8pt;' onClick=ttogle('" . $img . "');>http more info</span>
                 <div id='" . $img . "' name='" . $img . "'><br /><small>" . $rk->ip . "</small><br><small>" . $rk->agent . "<br /></small></div>
                 <script>document.getElementById('" . $img . "').style.display='none';</script>
			     </tr>
				 <tr><td valign='top' width='170'><div><font size='1' color='#3B3B3B'><strong>" . luc_hdate($rk->date) . " " . $rk->time . "</strong></font></div></td>
                 <td><div>" . luc_StatPressV_Decode($rk->urlrequested) . "</div></td></tr>";
			     $robot=$rk->spider;
			     $num_row=1;
			    }
				
			 elseif ($num_row < $LIMIT_PROOF)
			    {echo "<tr>
			     <td valign='top' width='170'><div><font size='1' color='#3B3B3B'><strong>" . luc_hdate($rk->date) . " " . $rk->time . "</strong></font></div></td>
                 <td><div>" . luc_StatPressV_Decode($rk->urlrequested) . "</div></td></tr>";
			     $num_row+=1;
			    }
		echo "</div></td></tr>\n";
         }
		echo "</table>"; 
		luc_print_pp_pa_link (0,0,$action,$NA,$pa);
        echo "</div>";
 }
?>