<?php

/**
 * odl_admin_structure.php
 *
 * This file handles the Administration of the Categories
 *
 * @author Mohammad Forgani
 * @copyright Copyright 2008, Oh Jung-Su
 * @version 1.0-a
 * @link http://www.forgani.com
 **/

function process_odlinksstructure(){
	global $_GET, $_POST, $table_prefix, $PHP_SELF, $wpdb;
	$odlinkssettings = get_option('odlinksdata');
   $view = true;
	$id = $_GET['c_id']*1;
	if (!$id) $id=0;
	echo '<div class="wrap"><h2>ODLinks Sturcture - Add/Edit Categories</h2><p>';
   if ($msg) unset($_GET['odlinks_admin_action']);
	switch ($_GET['odlinks_admin_action']){
		case "saveCategory":
			if ($id==0) {
				$today = date("Y-m-d");
				if ($_POST['odlinksdata']['c_hide']=='y'){
					$c_hide = "hidden";
				} else {$c_hide = "visible";}
				$position = $wpdb->get_var("SELECT MAX(c_position) FROM {$table_prefix}odcategories")+1;
				$sql = "INSERT INTO {$table_prefix}odcategories (c_parent, c_name, c_title, c_description, c_keywords, c_position, c_status, c_hide, c_date) values ('".($_POST['odlinksdata']['c_parent']*1)."', '".$wpdb->escape($_POST['odlinksdata']['c_name'])."', '".$wpdb->escape($_POST['odlinksdata']['c_title'])."', '".$wpdb->escape($_POST['odlinksdata']['c_description'])."',  '".$wpdb->escape($_POST['odlinksdata']['c_keywords'])."', '".$position."', '".$_POST['odlinksdata']['c_status']."', '".$c_hide."', '".$today."')";
            if (strlen($_POST['odlinksdata']['c_title']) > 2 ) $wpdb->query($sql);
			} else {
				if ($_POST['odlinksdata']['c_hide']=='y'){
					$c_hide = "hidden";
				} else {
					$c_hide = "visible";
				}
				$sql = "UPDATE {$table_prefix}odcategories SET c_status = '".$_POST['odlinksdata']['c_status']."', c_hide = '".$c_hide."', c_parent = '".($_POST['odlinksdata']['c_parent']*1)."', c_title = '".$wpdb->escape(stripslashes($_POST['odlinksdata']['c_title']))."', c_name = '".$wpdb->escape(stripslashes($_POST['odlinksdata']['c_name']))."', c_status = '".$wpdb->escape(stripslashes($_POST['odlinksdata']['c_status']))."', c_hide = '".$c_hide."', c_description = '".$wpdb->escape(stripslashes($_POST['odlinksdata']['c_description']))."' WHERE c_id = '".($_GET['c_id']*1)."'";
            if (strlen($_POST['odlinksdata']['c_title']) > 2 ) $wpdb->query($sql);
			}
			$msg ="Category Saved.";
         $view=false;
		break;
		case "editCategory":
			odl_edit_category($id);
			$view = false;
		break;
		case "viewLinks":
			odl_view_links($id);
			$view=false;
		break;
		case "deleteCategory":
        if ($id<>0) {
          $wpdb->query("DELETE FROM {$table_prefix}odcategories WHERE c_id = '".($id)."'");
          $wpdb->query("DELETE FROM {$table_prefix}odlinks WHERE l_c_id = '".($id)."'");
		  }
        $msg ="Category Removed.";
        $view=false;
		break;
	}

	if ($msg!=''){
		?>
		<div id="message" class="updated fade"><?php echo $msg;?></div>
		<?php
	}
   
	if ($view) {
    odl_view_category(0);
   } else {
    echo '<p><a href="' . $PHP_SELF . '?page=odlinksstructure">back to main page</a></p>';
    unset($_GET['odlinks_admin_action']);
   }
	echo '</div>';
}



function odl_edit_category($id) {
	global $_GET, $_POST, $table_prefix, $PHP_SELF, $wpdb;
	$odlinkssettings = get_option('odlinksdata');
	$odCategories = $wpdb->get_row("SELECT * FROM {$table_prefix}odcategories WHERE c_id = '".($id)."'", ARRAY_A);
	?>
	<P>
	<style type="text/css">
	fieldset {
	  padding: 1em;
	  font:12px;
	  font-weight:bold;
	  border:1px solid #ddd;
	}
	label {
	  float:left;
	  width:25%;
	  margin-right:0.5em;
	  padding-top:0.2em;
	  text-align:right;
	  font-weight:bold;}
	 </style>
	<form method="post" id="odl_form_post" name="odl_form_post" action="<?php echo $PHP_SELF;?>?page=odlinksstructure&odlinks_admin_action=saveCategory&c_id=<?php echo $id; ?>">
    <fieldset>
    <legend>Edit Category</legend>
    <label>Category title:</label>
    <input type="text" size="50" name="odlinksdata[c_title]" value="<?php echo $odCategories['c_title'];?>">
    <br>
    <label>Category name:</label>
    <input type="text" size="50" name="odlinksdata[c_name]" value="<?php echo $odCategories['c_name'];?>">
    <br>
    <label>Category description:</label>
    <textarea name="odlinksdata[c_description]" rows="3" cols="48"><?php echo $odCategories['c_description'];?></textarea>
    <br>
    <label>Category keywords:</label>
    <input type="text" size="50" name="odlinksdata[c_keywords]" value="<?php echo $odCategories['c_keywords'];?>">
    <br>
    <label>Parent category:</label>
      <select name="odlinksdata[c_parent]">
      <option value="0">root/  </option>
      <?php echo odl_list_cats(0,0,$odCategories['c_id'],$odCategories['c_parent']); ?>
      </select>
    <br>
    <label>Category Status</label>
    <select name="odlinksdata[c_status]"><option value="active">Open</option>
    <option value="inactive"<?php echo ($odCategories['c_status']=='inactive')?" selected":"";?>>Closed</option>
    <option value="readonly"<?php echo ($odCategories['c_status']=='readonly')?" selected":"";?>>Read-Only</option>
    </select>
    <br>
    <label>&nbsp;</label>
    <input type="checkbox" name="odlinksdata[c_hide]" value="y"<?php echo ($odCategories['c_hide']=='hidden')?" checked":"";?>> -Hide Category?
    <P>
    <label>&nbsp;</label>
    <input type="submit" value="Save category">&nbsp;&nbsp;<input type=button value="Cancel" onclick="history.go(-1);"> </fieldset>
	</form>
	<P>
	<?php
} 

function odl_view_category($id) {
	global $_GET, $_POST, $table_prefix, $PHP_SELF, $wpdb;
	$odlinkssettings = get_option('odlinksdata');
	$categoy_status = array(active=>"Open",inactive=>"Closed",readonly=>"Read-Only");
	?>
	<input type="button" value="Add Category" onclick="document.location.href='<?php echo $PHP_SELF;?>?page=odlinksstructure&odlinks_admin_action=editCategory&c_id=0';">
	<hr> 
	<img src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/odlinks/images/delete.png"> - delete category, including subcategories and all links within.<p> 
	
	<table class="struct">
    <tr><td> 
		<table width="100%" style="border:1px #C0C0C0 solid;"> 
        <tr style="background-color:#ccc">
          <td><span style="font-weight:bold">
          <img border=0 src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/odlinks/images/edit.gif"> Title / Name</span></td>
          <td width="150"><span style="font-weight:bold">Number of links</span></td>
          <td nowrap class="struct"><span style="font-weight:bold">Date</span></td>
          <td><span style="font-weight:bold">Delete</td>
        </tr> 
        <?php odl_main_cats($id, 0, $orderby, $how);?> 
		</table></td>
    </tr> 
	</table>
	<?php
} // odl_view_category


function odl_list_cats($parent, $lev, $exclude, $selected) { 
	global $table_prefix, $wpdb;
	$out = ""; 
	if ($lev == 0) {
		$out .= "\n"; 
	}
	$space = ""; 
	for ($x = 0; $x < $lev; $x++) { 
		if ($x == 2) $space = "&#160;&nbsp; &#160;&nbsp; &#160;&#4347;&#160;";
	   if ($x == 1) $space = "&#160;&nbsp; &#160;&#4347;&#160;";
		if ($x == 0) $space = "&#160;&#4347;&#160;";
	} 
	$categories = $wpdb->get_results("SELECT * FROM {$table_prefix}odcategories WHERE c_parent = '$parent' ORDER BY c_title ASC"); 
	for ($i=0; $i<count($categories); $i++){
		$category = $categories[$i];
		$id = $category->c_id;
		$linksNum = $wpdb->get_var("SELECT count(*) FROM {$table_prefix}odlinks WHERE l_c_id = '".$id."'"); 
		$title = $category->c_title;
		$sel = ""; 
		if($id == $selected){$sel = " selected ";}
		$value = $space . $title . " (" . $linksNum. ")";
		if ($parent == 0) $value = "<b>" . $value. "</b>";
		if($id != $exclude){$out .= '<option ' . $sel. ' value="' . $id . '">' . $value . '</option></br>';} 
		$out .= odl_list_cats($id, $lev + 1, $exclude, $selected);  
	}
	return $out; 
} 


function odl_main_cats($parent,$lev,$orderby,$how) { 
	global $table_prefix, $wpdb;
	$out = ""; 
	if($lev == 0){print "\n";} 
	if(!$how){$how = "ASC";} 
	if($orderby <> "c_date"){$orderby = "c_title";} 
	$space = ""; 

	?>
	<script language=javascript>
		<!--
		function deleteCategory(x, y){
			if (confirm("<?php echo ("Are you sure you wish to delete the Category"); ?>\n"+x)){
				document.location.href = y;
			}
		}
		//-->
	</script>
	<?php

	for($x=0;$x<$lev;$x++){ 
		$space .= "&nbsp;&nbsp;&nbsp;&nbsp;"; 
	} 
	$sql = "SELECT * FROM {$table_prefix}odcategories WHERE c_parent = $parent ORDER BY $orderby $how";
	$categories = $wpdb->get_results($sql); 
	for ($i=0; $i<count($categories); $i++){
		$category = $categories[$i];
		$linksCnt = $wpdb->get_row("SELECT count(l_id) as count FROM {$table_prefix}odlinks WHERE l_c_id = '".$category->c_id."'", ARRAY_A); 
		$linksNum = $linksCnt['count'];
		$id = $category->c_id; 
		$title = $category->c_title;
		$name = $category->c_name;
		$categoy_status = array(active=>"Open",inactive=>"Closed",readonly=>"Read-Only");
		
		$status = $categoy_status[$category->c_status];
		echo '<tr bgcolor="#F0F0F0" onMouseOver="this.bgColor="#FFFFFF";" onMouseOut="this.bgColor="#E9E9E9";">'; 
		echo '<td style="background-color:#E9E9E9">&nbsp;' . $space;
		?>	
		<a style="text-decoration: none;" href="<?php echo $PHP_SELF;?>?page=odlinksstructure&odlinks_admin_action=editCategory&c_id=<?php echo $id;?>"><?php echo ($title . " / " . $name); ?></a>
		<?php
		echo "<span class=\"font-size:8px;\">(" . $status;
		echo ($tfs->c_hide=='hidden')?" and Hidden":""; 
		echo ")</span></td>";

		echo '<td style="background-color:#E9E9E9">(<a href="'. $PHP_SELF .'?page=odlinksstructure&odlinks_admin_action=viewLinks&c_id=' .$id. '">'. $linksNum .'</a>)</td>';
		echo '<td>'.$category->c_date.'</td>'; 
		?>
		<td style="background-color:#E9E9E9;"><center>
      <a style="text-decoration: none;" href="javascript:deleteCategory('<?php echo rawurlencode($category->c_title . " " . $category->c_name );?>', '<?php echo $PHP_SELF;?>?page=odlinksstructure&odlinks_admin_action=deleteCategory&c_id=<?php echo $id;?>');">
      <img border=0 src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/odlinks/images/delete.png"></a></center></td></tr>
		<?php
		$print = odl_main_cats($id,$lev + 1,$orderby,$how); 
	} 
	return $out; 
}



function odl_view_links($id){
	global $table_prefix, $wpdb, $_GET, $PHP_SELF;
	$linkb= $PHP_SELF . "?page=odlinksposts&odlinks_admin_page_arg=odlinksposts";
	$sql = "SELECT * FROM {$table_prefix}odlinks l, {$table_prefix}odcategories c WHERE l.l_c_id = c.c_id AND c.c_id=" . $id;
	$result=$wpdb->get_row($sql, ARRAY_A); 
	$NumberOfResults=$result['count']; 
	$results=$wpdb->get_results($sql); 
		if(!empty($results)){
			?> 
			<h2>Category: <?php echo $results[1]->c_title ?> (<?php echo count($results) ?>)</h2>
         <p><?php echo $results[1]->c_description ?> </p>
         <tr><td colspan=7><hr></td></tr>
			<table class="struct">
			<tr>
			<td><span style="font-weight:bold">&nbsp;Edit&nbsp;&nbsp;</span></td> 
			<td><span style="font-weight:bold">&nbsp;WebSite URL</span></td>  
			<td><span style="font-weight:bold">&nbsp;Visible&nbsp;&nbsp;</span></tb> 
			<td><span style="font-weight:bold">&nbsp;Title</span></td> 
			<td><span style="font-weight:bold">&nbsp;Date</span></td> 
			<td>&nbsp;</tb> 
			<td><span style="font-weight:bold">&nbsp;Delete</span></td></tr> 
         <tr><td colspan=7><hr></td></tr>
			<?php
			for($x=0; $x<count($results); $x++){
				$row=$results[$x];
				?> 
				<tr bgcolor="#F4F4F4" onMouseOver="this.bgColor='#FFFFFF';" onMouseOut="this.bgColor='#F4F4F4';"> 
				<td><a href="<?php echo $linkb ?>&odlinks_admin_action=main&id=<?php echo $row->l_id ?>">
				<img border=0 src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/odlinks/images/edit.gif"></a></td>
				<td><?php echo $row->l_url ?></td>
				<?php
				if($row->l_hide == "visible")	$new_link="<font color=blue>Yes</font>";
            else $new_link="<font color=purple>No</font>"; 
				echo "<td>&nbsp;".$new_link."</td>"; 
				echo "<td>&nbsp;<a target=\"_blank\" href=\"".$row->l_url."\">".$row->l_title."</a></td>"; 
            print "<td>&nbsp;".$row->l_date."</td>"; echo "<td>&nbsp;</td>";
				echo "<td><center><a href=\"". $linkb ."&odlinks_admin_action=main&action=delete&id=".$row->l_id ."\">";
            echo "<img border=0 src=\"" .get_bloginfo('wpurl'). "/wp-content/plugins/odlinks/images/delete.png\"</a></center>"; 
				?> 
            </td></tr> 
				<?php
			} //for main links
			?> 
         <tr><td colspan=7><hr></td></tr>
			</table>
			<?php
		} // if
	return $msg;
}

?>
