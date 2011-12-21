<?php

/*
 * odl_functions.php
 * wordpress plugin website directory project
 * This file contains the ODLinks functions
 * @author website: http://www.forgani.com
 * @copyright Copyright 2008, Mohammad Forgani
 * @version 1.0
 * @link 
*/

function process_odlinkssettings(){
	global $_GET, $_POST, $wp_rewrite, $PHP_SELF, $wpdb, $table_prefix, $odlinksversion, $wp_version;
	$msg = '';

  // dir setting checker
  $arr = array('templates_c', 'cache');
  foreach ($arr as $value) {
     $dir = ODL_PLUGIN_DIR . '/includes/Smarty/' . $value. '/';
     if( ! is_writable( $dir ) || ! is_readable( $dir ) ) {
        echo "<BR /><BR /><fieldset><legend style='font-weight: bold; color: #900;'>Directory Checker</legend>";
        echo "<font color='#FF0000'>Check directory permission:".$dir."</font>" ;
        echo "</fieldset>";
     }
  }

   if (isset($_GET['odlinks_admin_action']))
    switch ($_GET['odlinks_admin_action']){
      case "savesettings":
        odlinkscheck_db();
        $odlinkswp_pageinfo = $wpdb->get_row("SELECT * FROM {$table_prefix}posts WHERE post_title = '[[ODLINKS]]'", ARRAY_A);
        if ($odlinkswp_pageinfo["post_title"]!="[[ODLINKS]]"){
          $odlinkswp_pageinfo = odlinkscreate_page();
        }
        $_POST['odlinksdata']['odlinksinstalled'] = 'y';
        $_POST['odlinksdata']['odlinksversion'] = $odlinksversion;
        $odl_new_slug = $_POST['odlinksdata']['odlinksslug'];
        $matching_slug = $wpdb->get_var("SELECT post_name FROM {$table_prefix}posts WHERE post_name = '".$wpdb->escape($odl_new_slug)."'");
        $odl_current_settings = get_option('odlinksdata');
        if($odl_new_slug!=$odl_current_settings['odlinksslug']){
          if($matching_slug!=$odl_new_slug){
              $wpdb->query("UPDATE {$table_prefix}posts SET post_name = '".$odl_new_slug."' WHERE post_title = '[[ODLINKS]]'");
          }else{
              $msg ="A slug exists with the name: ".$odl_new_slug."<br />"."try again.";
              $_POST['odlinksdata']['odlinksslug'] = $odl_current_settings['odlinksslug'];
          }
        }
        $data = array();
        foreach ($_POST['odlinksdata'] as $k=>$v){
          $v = stripslashes($_POST['odlinksdata'][$k]);
          $data[$k]=$v;
        }
        update_option('odlinksdata', $data);
        $wp_rewrite->flush_rules();
        $msg = "Settings Updated.";
        break;
     }
   $odlinkssettings = array();
   $odlinkssettings = get_option('odlinksdata');
	if ($odlinkssettings['odlinksinstalled']!='y') $odlinkssettings = odlinksinstall($odlinkssettings);

	if ($msg!=''){
		?>
		<div id="message" class="updated fade">
			<?php echo $msg;?>
		</div>
		<?php
	}

  $selflink = ($wp_rewrite->get_page_permastruct()=="")?"<a href=\"".get_bloginfo('url')."/index.php?pagename=".$odlinkssettings['odlinksslug']."\">".get_bloginfo('url')."/index.php?pagename=".$odlinkssettings['odlinksslug']."</a>":"<a href=\"".get_bloginfo('url')."/".$odlinkssettings['odlinksslug']."/\">".get_bloginfo('url')."/".$odlinkssettings['odlinksslug']."/</a>";

  $pageinfo = odlinksget_pageinfo();
  odl_showCategoryImg();
  ?>
  <div class="wrap">
  <h2>General Settings</h2>
  <p>
  <form method="POST" id="odlSettings" name="odlSettings" action="<?php echo $PHP_SELF;?>?page=odlinkssettings&odlinks_admin_action=savesettings">
		<input type="hidden" name="odlinksdata[odlinksversion]" value="<?php echo $odlinksversion;?>">
		<table border="0" class="editform">
			<tr><th align="right">odlinks Version:</th><td><?php echo $odlinksversion;?></td></tr>
			<tr><th align="right">WordPress Version:</th><td><?php echo $wp_version;?></td></tr>
			<tr><th align="right">odlinks URL:</th><td><?php echo $selflink;?></td></tr>
			<tr><th align="right">odlinks Slug:</th>
			<td><input type="text" size="30" name="odlinksdata[odlinksslug]" value="<?php echo str_replace('"', "&quot;", stripslashes($odlinkssettings['odlinksslug']));?>">
			</td>
			</tr>
			<tr>
			<th align="right">&nbsp;</th>
			<td><input type="checkbox" name="odlinksdata[odlinksshow_credits]" value="y"<?php echo ($odlinkssettings['odlinksshow_credits']=='y')?" checked":"";?>> Display odlinks credit line at the bottom of ODLinks pages
			</td>
			</tr>
			<tr>
			<th align="right">ODLinks Page Link Name:</th>
			<td><input type="text" size="40" name="odlinksdata[page_link_title]" value="<?php echo $odlinkssettings['page_link_title'];?>">
			</td>
			</tr>	
			<tr><th align="right">Number Of 'Last Links':</th>
			<td>
			<input type="text" size="4" name="odlinksdata[odlinks_last_links_num]" value="<?php echo ($odlinkssettings['odlinks_last_links_num']);?>" onchange="this.value=this.value*1;">
			</td>
			</tr>
			<tr><th align="right">&nbsp;</th>
			<td><input type="checkbox" name="odlinksdata[odlinks_last_links]" value="y"<?php echo ($odlinkssettings['odlinks_last_links']=='y')?" checked":"";?>>	Display 'Last Links' Post.
			</td>
			</tr>
			<tr><th align="right">string exceeded length:</th>
			<td><input type="text" size="4" name="odlinksdata[odlinks_excerpt_length]" value="<?php echo ($odlinkssettings['odlinks_excerpt_length']);?>" onchange="this.value=this.value*1;">
			</td>
			</tr>
			<tr><th align="right">Count of new/updated links show in footer:</th>
			<td><input type="text" size="4" name="odlinksdata[odlinks_new_links]" value="<?php echo ($odlinkssettings['odlinks_new_links']);?>" onchange="this.value=this.value*1;">
			</td>
			</tr>
			<tr><th align="right">Count of subcategories under each category:</th>
			<td><input type="text" size="4" name="odlinksdata[odlinks_sub_cat_num]" value="<?php echo ($odlinkssettings['odlinks_sub_cat_num']);?>" onchange="this.value=this.value*1;">
			</td>
			</tr>
			<tr><th align="right">Count of links under each category:</th>
			<td><input type="text" size="4" name="odlinksdata[odlinks_num_links]" value="<?php echo ($odlinkssettings['odlinks_num_links']);?>" onchange="this.value=this.value*1;">
			</td>
			</tr>
				
			<tr><th align="right">number of links per page to display in categories:</th>
			<td><input type="text" size="4" name="odlinksdata[odlinks_num_pages]" value="<?php echo ($odlinkssettings['odlinks_num_pages']);?>" onchange="this.value=this.value*1;">
			</td>
			</tr>
			<tr>	
				<th align="right" valign="top"><label>Top Image:</label></th>
				<td>
				<input type=hidden name="odlinksdata[odlinks_top_image]" value="<?php echo ($odlinkssettings['odlinks_top_image']);?>">
				<?php
				echo "\n<select name=\"topImage\" onChange=\"showimage()\">";	  
				$rep = ODL_PLUGIN_DIR . "/images/";
				$handle=opendir($rep);
				while ($file = readdir($handle)) {
					$filelist[] = $file;
				}
				asort($filelist);
				while (list ($key, $file) = each ($filelist)) {
					if (!ereg(".gif|.jpg|.png",$file)) {
						if ($file == "." || $file == "..") $a=1;
					} else {
						if ($file == $odlinkssettings['odlinks_top_image']) {
							echo "\n<option value=\"$file\" selected>$file</option>\n";
						} else {
							echo "\n<option value=\"$file\">$file</option>\n";
						}
					}
				}
				echo "\n</select>&nbsp;&nbsp;<img name=\"avatar\" src=\"". ODL_PLUGIN_URL . "/images/" . $odlinkssettings['odlinks_top_image'] ."\" class=\"imgMiddle\"><br />";
				?>
				<span class="smallTxt">images from plugins/odlinks/images directory</span></td>
			</tr>
				
			<tr><th align="right">Show the confirmation code: </th>	
				<td><input type=checkbox name="odlinksdata[confirmation_code]" value="y"<?php echo ($odlinkssettings['confirmation_code']=='y')?" checked":"";?>></td>
			</tr>
			<tr><td colspan=2><hr /><strong>Google AdSense for Open Directory Links</strong><hr /></td></tr>
			<?php
			//for upgrade versions
			if (!isset($odlinkssettings['GADcolor_border'])) $odlinkssettings['GADcolor_border']= 'FFFFFF';
			if (!isset($odlinkssettings['GADcolor_link'])) $odlinkssettings['GADcolor_link']= '0000FF';
			if (!isset($odlinkssettings['GADcolor_bg'])) $odlinkssettings['GADcolor_bg']= 'FFFFFF';
			if (!isset($odlinkssettings['GADcolor_text'])) $odlinkssettings['GADcolor_text']= '000000';
			if (!isset($odlinkssettings['GADcolor_url'])) $odlinkssettings['GADcolor_url']= 'FF0000';
			if (!isset($odlinkssettings['GADposition'])) $odlinkssettings['GADposition']= 'btn';
			if (!isset($odlinkssettings['GADproduct'])) $odlinkssettings['GADproduct']= 'link';
			if (!isset($odlinkssettings['googleID'])) $odlinkssettings['googleID'] = 'pub-xxxxxxx';
			$GADpos = array ('top' => 'top','btn' => 'bottom', 'bth' => 'both','no' => 'none');
			?>
			<tr>
			<th align="right" valign="top"><a href='https://www.google.com/adsense/' target='google'>Google AdSense Account ID: </a></th>
			<td><input type='text' name='odlinksdata[googleID]' id='odlinksdata[googleID]' value="<?php echo ($odlinkssettings['googleID']);?>" size='30' /><br><span class="smallTxt"> example: no, pub-xxxxxxx or ...
			</span></td></tr>
			<tr>
				<th align="right" valign="top">Google Ad Position: </th>
				<td>
					<select name="odlinksdata[GADposition]" tabindex="1">
					<?php
					foreach($GADpos as $key=>$value)	{
						if ($key == $odlinkssettings['GADposition']) {
							echo "\n<option value='$key' selected='selected'>$value</option>\n";
						} else {
							echo "\n<option value='$key'>$value</option>\n";
						}
					}
					?>
					</select>&nbsp;&nbsp;<span class="smallTxt">(If this value is assigned to 'none' then the Google Ads will not show up)</small>
				</td>
			</tr>

			<?php
			$odlinkssettings['GADproduct'] = 'link';
			$lformats=array ('728x15'  => '728 x 15', '468x15' => '468 x 15');
			?>
			
			<tr>
				<th align="right" valign="top"><label>Link Format: </label></th>
				<td><select name="odlinksdata[GADLformat]">
					<?php
					foreach($lformats as $key=>$value)	{
						if ($key == $odlinkssettings[GADLformat]) {
							echo "\n<option value='$key' selected='selected'>$value</option>\n";
						} else {
							echo "\n<option value='$key'>$value</option>\n";
						}
					}
					?>
				</select></td>	
		</tr>
		<tr><th align="right">Ad Colours: </th><td></td></tr>
		<tr>
			<td colspan=2 align="center">
			<table><tr>
			<td>Border: </td>
			<td><input name='odlinksdata[GADcolor_border]' id='odlinksdata[GADcolor_border]' size='6' value='<?php echo $odlinkssettings['GADcolor_border']; ?>'/>
			</td>
			<td>Title/Link: </td>
			<td><input name='odlinksdata[GADcolor_link]' id='odlinksdata[GADcolor_link]' size='6' value='<?php echo $odlinkssettings['GADcolor_link']; ?>'/>
			</td>
			<td>Background: </td>
			<td><input name='odlinksdata[GADcolor_bg]' id='odlinksdata[GADcolor_bg]' size='6' value='<?php echo $odlinkssettings['GADcolor_bg']; ?>'/>
			</td>
			<td>Text: </td>
			<td><input name='odlinksdata[GADcolor_text]' id='odlinksdata[GADcolor_text]' size='6' value='<?php echo $odlinkssettings['GADcolor_text']; ?>'/>
			</td>
			<td>URL: </td>
			<td><input name='odlinksdata[GADcolor_url]' id='odlinksdata[GADcolor_url]' size='6' value='<?php echo $odlinkssettings['GADcolor_url']; ?>'/>
			</td>
			</tr>
			</th></tr></table>
			</td>
		</tr>
		<tr><td colspan=2><HR /></td></tr>
		<tr><th>&nbsp;</th>
			<td><p><input type="submit" value="Update ODLinks Settings"></p></td>
		</tr>
		</table>
		</form>
	</p></div>
	<?php
}

function odlinksinstall($odlinkssettings){
	global $odlinksversion;
	$odlinkssettings['odlinksversion'] = $odlinksversion;
	$odlinkssettings['odlinksinstalled'] = 'y';
	$odlinkssettings['userfield'] = odlinksget_namefield();
	$odlinkssettings['odlinksadd_into_pages'] = 'y';
	$odlinkssettings['odlinksshow_credits'] = 'y';
	$odlinkssettings['odlinksread_blog'] = 'y';
	$odlinkssettings['odlinksslug'] = 'odlinks';
	$odlinkssettings['page_link_title'] = 'Open Directory Links';
	$odlinkssettings['odlinkstheme'] = 'default';
	$odlinkssettings['odlinks_display_titles'] = 'y';
	$odlinkssettings['odlinks_top_image'] = '';
	$odlinkssettings['odlinks_display_last_links'] = 'y';
	$odlinkssettings['odlinks_display_last_post_link'] = 'y';
	$odlinkssettings['odlinks_last_links_num'] = 10;
	$odlinkssettings['odlinks_excerpt_length'] = 500;
	$odlinkssettings['odlinks_last_links'] = "y";
	$odlinkssettings['confirmation_code']="y";
	$odlinkssettings['odlinks_new_links'] = 10;
	$odlinkssettings['odlinks_sub_cat_num'] = 10;
	$odlinkssettings['odlinks_num_links'] = 4;
	$odlinkssettings['odlinks_num_pages'] = 10;
	$odlinkssettings['odlinks_search_log'] = 25;
	$odlinkssettings['odlinks_description'] = '';
	$odlinkssettings['odlinks_keywords'] = 'dummy';
	$odlinkssettings['googleID'] = 'pub-xxxxxxx';
	$odlinkssettings['GADproduct'] = 'link';
	$odlinkssettings['GADLformat'] = '468x15';
	$odlinkssettings['GADtype'] = 'text';
	$odlinkssettings['GADcolor_border']= 'FFFFFF';
	$odlinkssettings['GADcolor_link']= '0000FF';
	$odlinkssettings['GADcolor_bg']= 'E4F2FD';
	$odlinkssettings['GADcolor_text']= '000000';
	$odlinkssettings['GADcolor_url']= 'FF0000';
	$odlinkssettings['GADposition'] = 'btn';
   update_option('odlinksdata', $odlinkssettings);
   return $odlinkssettings;
}

function odlinkscheck_db(){
	odlinksupdate_db();
}

function odlinksupdate_db(){
	global $wpdb, $table_prefix, $odlinksversion;

	$odlinkssql[$table_prefix.'odcategories'] = "CREATE TABLE IF NOT EXISTS {$table_prefix}odcategories (
		c_id int(11) NOT NULL auto_increment,
		c_parent int(11) NOT NULL default '0',
		c_position int(11) NOT NULL default '0',
		c_name varchar(150) NOT NULL,
		c_title varchar(150) NOT NULL,
		c_description text NOT NULL,
		c_text text NOT NULL,
		c_date date,
		c_keywords text,
		c_status enum('active','inactive','readonly') NOT NULL default 'active',
		c_hide enum('hidden','visible') NOT NULL default 'visible',
		c_links int(11) NOT NULL default '0',
		c_posts int(11) NOT NULL default '0',
		c_rss text,
		PRIMARY KEY (c_id),
		KEY c_parent (c_parent)
	);";

	$odlinkssql[$table_prefix.'odbanned'] = "CREATE TABLE IF NOT EXISTS {$table_prefix}odbanned (
		b_id int(11) NOT NULL auto_increment,
		c_domain text NOT NULL,
		PRIMARY KEY (b_id)
	);";

/*
	CREATE TABLE search_log (
  id int(11) NOT NULL auto_increment,
  date date NOT NULL default '0000-00-00',
  matches int(11) NOT NULL default '0',
  text text NOT NULL,
  PRIMARY KEY  (id),
  FULLTEXT KEY text (text)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

*/
	$odlinkssql[$table_prefix.'odlinks'] = "CREATE TABLE IF NOT EXISTS {$table_prefix}odlinks (
		l_id int(11) NOT NULL auto_increment,
		l_c_id int(11) NOT NULL default '0',
		l_date date,
		l_subject varchar(255) NOT NULL default '',
		l_description text NOT NULL default '',
		l_url varchar(255) NOT NULL default '',
		l_posts int(11) NOT NULL default '0',
		l_views int(11) NOT NULL default '0',
		l_sticky enum('y','n') NOT NULL default 'n',
		l_status enum('closed','deleted','open') NOT NULL default 'open',
		l_hide enum('hidden','visible') NOT NULL default 'visible',
		l_author_name varchar(100) NOT NULL default '',
		l_author_ip varchar(15) NOT NULL default '',
		l_author_mail varchar(64) NOT NULL default '',
		l_recip tinyint(4) NOT NULL default '0',
		l_title varchar(255),
		l_google_rank varchar(255),
		PRIMARY KEY (l_id) 
	);";

	$odlinkssql[$table_prefix.'odpages'] = "CREATE TABLE IF NOT EXISTS {$table_prefix}odpages (
		p_id int(11) NOT NULL auto_increment,
		p_l_id int(11) NOT NULL default '0',
		p_url text NOT NULL,
		p_title text NOT NULL,
		p_description text NOT NULL,
		p_google_rank tinyint(4) NOT NULL default '0',
		p_recip tinyint(4) NOT NULL default '0',
		p_date int(20) NOT NULL default '0',
		p_size int(32) NOT NULL default '0',
		PRIMARY KEY (p_id)
	);";

	$odlinkssql[$table_prefix.'odnew_links'] = "CREATE TABLE IF NOT EXISTS {$table_prefix}odnew_links (
		n_id int(11) NOT NULL auto_increment,
		n_url text NOT NULL,
		n_title text NOT NULL,
		n_description text NOT NULL,
		n_email tinytext NOT NULL,
		n_category int(11) NOT NULL default '0',
		PRIMARY KEY  (n_id)
	);";


	$tabs = $wpdb->get_results("SHOW TABLES", ARRAY_N);

	$tables = array();

	for ($i=0; $i<count($tabs); $i++){
		$tables[] = $tabs[$i][0];
	}

	@reset($odlinkssql);

	while (list($k, $v) = @each($odlinkssql)){
		if (!@in_array($k, $tables)){
			echo " - create table: " .  $k . "<br />"; 
			$wpdb->query($v);
		}
	}
}

function odlinksget_namefield(){
	global $wpdb, $table_prefix, $wp_version;
	if ($user_field == false){
		$tcols = $wpdb->get_results("SHOW COLUMNS FROM IF NOT EXISTS {$table_prefix}users", ARRAY_A);
		$cols = array();
		for ($i=0; $i<count($tcols); $i++){
			$cols[] = $tcols[$i]['Field'];
		}
		if (in_array("display_name", $cols)){
			$wpc_user_field = "display_name";
			$wp_version = "2";
		} else {
			$wpc_user_field = "user_nickname";
			$wp_version = "1";
		}
	}
	return $user_field;
}


function odl_showCategoryImg() {
	echo "<script type=\"text/javascript\">\n";
	echo "<!--\n\n";
	echo "function showimage() {\n";
	echo "if (!document.images)\n";
	echo "return\n";
	echo "document.images.avatar.src=\n";
	echo "'". ODL_PLUGIN_URL . "/images/' + document.odlSettings.topImage.options[document.odlSettings.topImage.selectedIndex].value;\n";
	echo 'document.odlSettings.elements["odlinksdata[odlinks_top_image]"].value = document.odlSettings.topImage.options[document.odlSettings.topImage.selectedIndex].value;';
	echo "}\n\n";

	echo "function showCatimage() {\n";
	echo "if (!document.images)\n";
	echo "return\n";
	echo "document.images.avatar.src=\n";
	echo "'".get_bloginfo('wpurl')."/wp-content/plugins/odlinks/' + document.admCatStructure.topImage.options[document.admCatStructure.topImage.selectedIndex].value;\n";
	echo 'document.admCatStructure.elements["odlinksdata[photo]"].value = document.admCatStructure.topImage.options[document.admCatStructure.topImage.selectedIndex].value;';
	echo "}\n\n";
	echo "//-->\n";
	echo "</script>\n"; 
}

?>