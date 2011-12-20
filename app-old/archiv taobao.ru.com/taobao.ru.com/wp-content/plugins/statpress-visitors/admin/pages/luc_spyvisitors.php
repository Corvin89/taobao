<?php
 function luc_spyvisitors()
	    {  global $wpdb;
	        $action="spyvisitors";
            $table_name = $wpdb->prefix . "statpress";
		  // number of IP or bot by page
		    $LIMIT = get_option('statpressV_ip_per_page_spyvisitor');
		    $LIMIT_PROOF = get_option('statpressV_visits_per_ip_spyvisitor');
            if ($LIMIT == 0)
              $LIMIT = 20;
			if ($LIMIT_PROOF == 0)
              $LIMIT_PROOF = 20;
			$pp = luc_page_periode();	
	
          	// Number of distinct ip (unique visitors)
			$NumIP = $wpdb->get_var("SELECT count(distinct ip) FROM $table_name WHERE spider=''");
			$NP = ceil($NumIP/$LIMIT);
            $LimitValue = ($pp * $LIMIT) - $LIMIT;
        
			$sql = "SELECT *
			FROM $table_name as T1
			JOIN
			(SELECT max(id) as MaxId,min(id) as MinId,ip, nation FROM $table_name WHERE spider='' GROUP BY ip ORDER BY MaxId DESC LIMIT $LimitValue, $LIMIT ) as T2
			ON T1.ip = T2.ip 
			WHERE id BETWEEN MinId AND MaxId
			ORDER BY MaxId DESC, id DESC
			";
			$qry = $wpdb->get_results($sql);

			echo "<div class='wrap'><h2>" . __('Visitor Spy', 'statpress') . "</h2>";
?>
<script>
function ttogle(thediv){
if (document.getElementById(thediv).style.display=="inline") {
document.getElementById(thediv).style.display="none"
} else {document.getElementById(thediv).style.display="inline"}
}
</script>
 <?php    $ip = 0;
		  $num_row=0;
		  echo'<div id="paginating" align="center">';
		  luc_print_pp_link($NP,$pp,$action);
		  echo'</div><table id="mainspytab" name="mainspytab" width="99%" border="0" cellspacing="0" cellpadding="4">';    
          foreach ($qry as $rk)
          { // Visitor Spy
   		   if ($ip <> $rk->ip) //this is the first time these ip appear, print informations
             {echo "<tr>
			       <td colspan='2' bgcolor='#dedede'><div align='left'>";  
              $title='';
			  $id ='';		
			  if ($rk->country <> '')
			         { $img=strtolower($rk->country).".png"; 
					   $lines = file(ABSPATH.'wp-content/plugins/'.dirname(dirname(dirname(plugin_basename(__FILE__)))) .'/def/domain.dat');		
			           foreach($lines as $line_num => $country) 
			                  { list($id,$title)=explode("|",$country);
							    if($id===strtolower($rk->country)) break;
								}  
					   echo "http country <IMG style='border:0px;height:16px;' alt='".$title."' title='".$title."' SRC='" .plugins_url('statpress-visitors/images/domain/'.$img, dirname(dirname(dirname(__FILE__)))). "'>  ";		
					 }
			  elseif($rk->nation <> '') // the nation exist
			         { $img=strtolower($rk->nation).".png"; 
					   $lines = file(ABSPATH.'wp-content/plugins/'.dirname(dirname(dirname(plugin_basename(__FILE__)))) .'/def/domain.dat');		
			           foreach($lines as $line_num => $nation) 
			                  { list($id,$title)=explode("|",$nation);
							    if($id===$rk->nation) break;
								}  
					   echo "http domain <IMG style='border:0px;height:16px;' alt='".$title."' title='".$title."' SRC='" .plugins_url('statpress-visitors/images/domain/'.$img, dirname(dirname(dirname(__FILE__)))). "'>  ";		
					  }		
              else	echo "Hostip country <IMG SRC='http://api.hostip.info/flag.php?ip=".$rk->ip."' border=0 width=18 height=12>  ";						         
              echo  "<strong><span><font size='2' color='#7b7b7b'>" . $rk->ip . "</font></span></strong>
              <span style='color:#006dca;cursor:pointer;border-bottom:1px dotted #AFD5F9;font-size:8pt;' onClick=ttogle('" . $rk->ip . "');>Hostip (subject) more info</span></div>
              <div id='" . $rk->ip . "' name='" . $rk->ip . "'>";
              echo "<iframe style='overflow:hide;border:0px;width:100%;height:35px;font-family:helvetica;paddng:0;' scrolling='no' marginwidth=0 marginheight=0 src=http://api.hostip.info/get_html.php?ip=" . $rk->ip . "></iframe>
			  <br /><small>" . $rk->os . ", " . $rk->browser."<br />" . gethostbyaddr($rk->ip). "</small><br><small>" . $rk->agent . "</small></div>
              <script>document.getElementById('" . $rk->ip . "').style.display='none';</script></td></tr><tr>
			  <td valign='top' width='151'><div><font size='1' color='#3B3B3B'><strong>" . luc_hdate($rk->date) . " " . $rk->time . "</strong></font></div></td>
              <td>" . luc_StatPressV_Decode($rk->urlrequested) ."";
                  if ($rk->searchengine != '')
                      echo "<br><small>arrived from <b>" . $rk->searchengine . "</b> searching <a href='" . $rk->referrer . "' target=_blank>" . urldecode($rk->search) . "</a></small>";
                  elseif ($rk->referrer != '' && strpos($rk->referrer, get_option('home')) === false)
                      echo "<br><small>arrived from <a href='" . $rk->referrer . "' target=_blank>" . $rk->referrer . "</a></small>";
                  echo "</div></td></tr>\n";
			  $ip=$rk->ip;
			  $num_row = 1;
			   }
		   elseif ($num_row < $LIMIT_PROOF)
			   {  echo "<tr><td valign='top' width='151'><div><font size='1' color='#3B3B3B'><strong>" . luc_hdate($rk->date) . " " . $rk->time . "</strong></font></div></td>
                  <td><div>" . luc_StatPressV_Decode($rk->urlrequested) . "";
                  if ($rk->searchengine != '')
                      echo "<br><small>arrived from <b>" . $rk->searchengine . "</b> searching <a href='" . $rk->referrer . "' target=_blank>" . urldecode($rk->search) . "</a></small>";
                  elseif ($rk->referrer != '' && strpos($rk->referrer, get_option('home')) === false)
                      echo "<br><small>arrived from <a href='" . $rk->referrer . "' target=_blank>" . $rk->referrer . "</a></small>";
				  $num_row += 1;
                  echo "</div></td></tr>\n";
				}
		   }
		echo "</div></td></tr>\n</table>";   
        luc_print_pp_link($NP,$pp,$action);
        echo "</div>";
	  } 
?>