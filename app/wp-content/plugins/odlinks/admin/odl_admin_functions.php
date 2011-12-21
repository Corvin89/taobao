<?php

/*
 * odl_admin_structure.php
 * wordpress plugin open directory project
 * @author Mohammad Forgani
 * @copyright Copyright 2008, Oh Jung-Su
 * @version 1.0.0-b
 * @link http://www.forgani.com
 * 2009-10-23 fixed for wp 2.8.5
 */


function process_odlinksposts(){
	global $_GET, $_POST, $table_prefix, $PHP_SELF, $wpdb;
	$odlinkssettings=get_option('odlinksdata');
	$loadpage=true;
	?>
	<div class="wrap"><h2>ODLinks - Edit/Remove links</h2><p>	
	<input type="button" value="Main" onclick="document.location.href='<?php echo $PHP_SELF;?>?page=odlinksposts&odlinks_admin_action=main&id=0';">
	<input type="button" value="List the incoming posts" onclick="document.location.href='<?php echo $PHP_SELF;?>?page=odlinksposts&odlinks_admin_action=approvelinks&id=0';">
	<input type="button" value="List of banned" onclick="document.location.href='<?php echo $PHP_SELF;?>?page=odlinksposts&odlinks_admin_action=bannedlinks&id=0';">
	<?php
	switch($_GET['odlinks_admin_action']){
		case "main":
			odlinksadmin_main_links($_GET['id']*1, $_GET['action']);
			$loadpage=false;
		break;
		case "approvelinks":
			odlinksadmin_approve_links($_GET['id']*1, $_GET['action']);
			$loadpage=false;
		break;
		case "bannedlinks":
			odlinksadmin_banned_links($_GET['id']*1, $_GET['action']);
			$loadpage=false;
		break;
		case "editlinks":
			odlinksadmin_edit_links($_GET['id']*1);
			$loadpage=false;
		break;
		case "editban":
			odlinksadmin_edit_ban($_GET['id']*1);
			$loadpage=false;
		break;
		default:
			odlinksadmin_approve_links($_GET['id']*1, $_GET['action']);
			$loadpage=false;
		break;
	}

	if($msg!='')
		echo '<div id="message" class="updated fade">' . $msg . '</div>';
	if($loadpage==true) odlinksadmin_main_links(0, 0);
   ?>
	</div>
   <?php
}


function odlinksadmin_approve_links($id, $action){
	global $_GET, $_POST, $wpdb, $table_prefix;
	$odlinkssettings=get_option('odlinksdata');
	$linkb=$PHP_SELF."?page=odlinksposts&odlinks_admin_page_arg=odlinksposts";
	if(isset($id) && $action){ 
		switch($action){ 
			case "ok": 
				?><p>Approving new link ....</p><?php
				$sql="SELECT * FROM {$table_prefix}odnew_links WHERE n_id=$id";
				$news=$wpdb->get_results($sql); 
				for($i=0; $i<count($news); $i++){
					$new=$news[$i];
					$warning=""; 
					$sql="SELECT * FROM {$table_prefix}odlinks";
					$result=$wpdb->get_results($sql); 
					for($x=0; $x<count($result); $x++){
						$row=$result[$x];
						if($new->n_url == $row>l_id){ 
							$warning .= '<font color=red>Warning: This URL already exists:';
							$warning .= '<a href="'. $linkb . '&odlinks_admin_action=main&id=' .$row->l_id. '">';
							$warning .= '<img border=0 src="'. get_bloginfo('wpurl') . '"/wp-content/plugins/odlinks/images/edit.gif"></a></font><p>';
						}
					}
					?> 
					<form method="post" id="odl_form_post" name="odl_form_post" action="<?php echo $linkb ?>&odlinks_admin_action=approvelinks&id=<?php echo $new->n_id; ?>&action=insert">
					<input type="hidden" name="odlinksdata[new_link]" value="1"> 
               <?php $warning?> URL:<br><input type="text" name="odlinksdata[url]" value="<?php echo $new->n_url?>" size="80"> 
					&nbsp;Site view:<a target="_blank" href="<?php echo $new->n_url?>"><img src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/odlinks/images/goto_url.gif"></a> 
               <p> Title:<br><input type="text" name="odlinksdata[title]" value="<?php echo $new->n_title; ?>" size="80"> <p> Email:<br>
					<input type="text" name="odlinksdata[email]" value="<?php echo $new->n_email; ?>" size="80"> 
						<p> Description:<br>
						<textarea rows="5" name="odlinksdata[description]" cols="60"><?php echo $new->n_description?></textarea> 
						<p> Visible?<br> 
						<select name="odlinksdata[visible]"> 
						<option value="1">visible</option> 
						<option value="0">hide</option>
						</select><p> Category:<br> 
						<select name="odlinksdata[parent]"> 
							<?php echo odl_list_cats(0,0,0,$new->n_category); ?> </select> <p> 
					<input  type="submit" value="Add Link!">&nbsp;&nbsp;<input type=button value="Cancel" onclick="history.go(-1);"></form> 
					<?php
				}
			break; 
			case "delete": 
				if(isset($_POST['odlinksdata']['delete_link'])){ 
					?><h3>Deleted link...</h3><?php
					$sql="SELECT * FROM {$table_prefix}odnew_links WHERE n_id=$id";
					$results=$wpdb->get_results($sql); 
					for($i=0; $i<count($results); $i++){
						$row=$results[$i];
						echo "URL: ".$row->n_url."<br>"; 
						echo "Title: ".$row->n_title."<br>"; 
						echo "Description: ".$row->n_description."<br>"; 
						echo "Email: ".$row->n_email."<br>"; 
					}

					$url_back = admin_url("admin.php?page=odlinksposts&odlinks_admin_page_arg=odlinksposts&odlinks_admin_action=approvelinks");
					echo '<p><form method="post" action="'.$url_back.'"><input type="submit" value="&#060;&#060; Back"></form></p>';

					$sql="DELETE FROM {$table_prefix}odnew_links WHERE n_id=$id"; 
					$wpdb->query($sql); 
				}else{ 
					?><h3>Removing a new link...</h3><?php
					$sql="SELECT * FROM {$table_prefix}odnew_links WHERE n_id=$id";
					$results=$wpdb->get_results($sql); 
					for($i=0; $i<count($results); $i++){
						$row=$results[$i];
					?>
					<form method="post" id="odl_form_post" name="odl_form_post" action="<?php echo $linkb ?>&odlinks_admin_action=approvelinks&action=delete&id=<?php echo $id; ?>" method="POST">
					<p>Are you sure you want to delete the website <strong><?php echo $row->n_url ?></strong>?</p> 
					<input type="submit" name="odlinksdata[delete_link]" value="Delete Link">&nbsp;&nbsp;<input type=button value="Cancel" onclick="history.go(-1);"></form>
					<?php
					}
				} 
			break; 
			case "insert":
					$url=$_POST['odlinksdata']['url'];
                    $title=$_POST['odlinksdata']['title'];
                    $description=$_POST['odlinksdata']['description'];
                    $parent=$_POST['odlinksdata']['parent'];
                    $email=$_POST['odlinksdata']['email'];
                    $sql="INSERT INTO {$table_prefix}odlinks (l_url, l_title, l_description, l_c_id, l_date, l_hide, l_author_mail) VALUES ('".$url."', '".$title."', '".$description."', '".$parent."', '".date("Y-m-d")."', 'visible', '".$email."')";
                    $wpdb->query($sql);
                    ?>
                    <p>New link added:</p>
                    <br> URL: <?php echo $url?>
                    <br> Title: <?php echo $title?>
                    <br> Email: <?php echo $email?>
                    <br> Description: <?php echo $description?>
                    <br> Category id: <?php echo $parent?>
                    <?php
                    $url_back = admin_url("admin.php?page=odlinksposts&odlinks_admin_page_arg=odlinksposts&odlinks_admin_action=approvelinks");
                    echo '<p><form method="post" action="'.$url_back.'"><input type="submit" value="&#060;&#060; Back"></form></p>';
                    $sql="DELETE FROM {$table_prefix}odnew_links WHERE n_url='$url'";
                    $wpdb->query($sql);
            break;

			case "edit":
                    $id=$_POST['odlinksdata']['id'];
                    $url=$_POST['odlinksdata']['url'];
                    $title=$_POST['odlinksdata']['title'];
                    $description=$_POST['odlinksdata']['description'];
                    $parent=$_POST['odlinksdata']['parent'];
                    $email=$_POST['odlinksdata']['email'];
                    $sql="update {$table_prefix}odlinks (l_url, l_title, l_description, l_c_id, l_hide, l_author_mail) VALUES ('".$url."', '".$title."', '".$description."', '".$parent."', 'visible', '".$email."') WHERE l_id=" .$id;
                    $wpdb->query($sql);
                    ?>
                     <p>Updated:</p>
                     <br> URL: <?php echo $url?>
                     <br> Title: <?php echo $title?>
                     <br> Email: <?php echo $email?>
                     <br> Description: <?php echo $description?>
                     <br> Category id: <?php echo $parent?> <p>
                     <p>
                     <?php
            break;
			case "ban": 
				if(isset($_POST['odlinksdata']['new_ban'])){ 
					$new_ban_url=$_POST['odlinksdata']['new_ban_url']; 
					?>
					<h3><?php echo $new_ban_url; ?>The site removed from the approval list.</h3>
					<?php 
					$sql="DELETE FROM {$table_prefix}odnew_links WHERE n_id=". $id; 
					$wpdb->query($sql);
					$sql="INSERT INTO {$table_prefix}odbanned (c_domain) VALUES('".$new_ban_url."')"; 
					$wpdb->query($sql);
					echo "and added as ban to the banned list!";
				}else{ 
					?><h3>Banning a new link/site ...</h3>
					<?php
					$sql="SELECT * FROM {$table_prefix}odnew_links WHERE n_id=".$id;
					$results=$wpdb->get_results($sql); 
					for($i=0; $i<count($results); $i++){
						$row=$results[$i];
					?>
					<form method="post" id="odl_form_post" name="odl_form_post"  action="<?php echo $linkb ?>&odlinks_admin_action=approvelinks&id=<?php echo $row->n_id; ?>&action=ban" method="POST">
					The URL, or URL parts to be added to the "banned URLs" list:<br /> 
					<input  type="text" name="odlinksdata[new_ban_url]" size="80" value="<?php echo $row->n_url?>"><p> 
					<input  type="submit" name="odlinksdata[new_ban]" value="Submit">&nbsp;&nbsp;<input type=button value="Cancel" onclick="history.go(-1);"></form>
					<?php
					}
				} 
			break;
		}
	}else{ 
		?>
		<p>Newly submitted links ready to be approved:</P>
		<?php
		$sql="SELECT * FROM {$table_prefix}odnew_links LIMIT 0, 10";
		$results=$wpdb->get_results($sql); 
		if(!empty($results)){
			?> 
			<table width="100%" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF"><tr><td>
			<table width="100%" cellspacing="1" cellpadding="3" border="0"><tr bgcolor="#CCCCCC"><td>Ok</td>
			<td>Website URL </td> 
			<td>Title & Description</td> 
			<td width="3%">Category</td> <td nowrap>Email</td>
			<td>Delete</td><td>Ban</td> </tr> 
			<?php
			for($i=0; $i<count($results); $i++){
				$row=$results[$i];
				?>
				<tr bgcolor="#F4F4F4" onMouseOver="this.bgColor='#FFFFFF';" onMouseOut="this.bgColor='#F4F4F4';"><td>
				<a href="<?php echo $linkb; ?>&odlinks_admin_action=approvelinks&id=<?php echo $row->n_id;?>&action=ok">
				<img border="0" src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/odlinks/images/ok.gif"></a></td>
				<?php
				print "<td><font color=green>".$row->n_url."</font></td>";
				print "<td><a target=\"_blank\" href=\"".$row->n_url."\">".$row->n_title."</a>"; 
				print "<br />".$row->n_description."</font></td>";
				print "<form><td><select>"; 
				print odl_list_cats(0,0,0,$row->n_category);
				print "</select></td></form>";
				print "<td>".$row->n_email."</td>"; ?><td>
				<a href="<?php echo $linkb ?>&odlinks_admin_action=approvelinks&id=<?php echo $row->n_id?>&action=delete"><img border="0" src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/odlinks/images/delete.png"></a></td><td>
				<a href="<?php echo $linkb ?>&odlinks_admin_action=approvelinks&id=<?php echo $row->n_id?>&action=ban"><img border="0" src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/odlinks/images/ban.jpg"></a></td></tr><?php
			} ?>
			</table></td></tr></table><?php
		}
	} 
}


function odlinksadmin_banned_links($id, $action){
	global $_GET, $_POST, $wpdb, $table_prefix;
	$odlinkssettings=get_option('odlinksdata');
	$linkb=$PHP_SELF."?page=odlinksposts&odlinks_admin_page_arg=odlinksposts";
	if(isset($id) && $action){ 
		switch($action){ 
			case "insert":
				$url=$_POST['odlinksdata']['url'];
				$sql="INSERT INTO {$table_prefix}odbanned (c_domain) VALUES('".$url."')"; 
				$wpdb->query($sql);
				?> 
				<p>The record added to the list of banned IP addresses.</p>
				<b>Record:</b> <?php echo $url?><br>
				<?php
				$url_back = admin_url("admin.php?page=odlinksposts&odlinks_admin_page_arg=odlinksposts&odlinks_admin_action=bannedlinks");
				echo '<p><form method="post" action="'.$url_back.'"><input type="submit" value="&#060;&#060; Back"></form></p>';
			break; 
			case "edit":
				$sql="SELECT * FROM {$table_prefix}odbanned WHERE b_id=$id";
				$results=$wpdb->get_results($sql);
				for($i=0; $i<count($results); $i++){
					$row=$results[$i];
					?> 
					<P>
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
				
					<form method="post" id="odl_form_post" name="odl_form_post" action="<?php echo $linkb ?>&odlinks_admin_action=editban&id=<?php echo $row->b_id; ?>">
					<fieldset>
					<legend>Edit Banned Strings/Urls</legend>
					<input type="hidden" name="odlinksdata[id]" value="<?php echo $row->b_id; ?>">
					<label>String:</label>
					<input type="text" name="odlinksdata[url]" value="<?php echo $row->c_domain; ?>" size="80"> 
					<P><label>&nbsp;</label>
					<input  type="submit" value="Save">&nbsp;&nbsp;<input type=button value="Cancel" onclick="history.go(-1);">
					</fieldset>	
					</form> 
					<?php
				}
			break; 
			case "delete": 
				if(isset($_POST['odlinksdata']['delete_ban'])){ 
					?><h3>The selected ban record deleted from the list successfully.</h3><?php
					$sql="SELECT * FROM {$table_prefix}odbanned WHERE b_id=$id";
					$results=$wpdb->get_results($sql); 
					for($i=0; $i<count($results); $i++){
						$row=$results[$i];
						echo "<b>Record: </b>".$row->c_domain."<br>"; 
					}
					$url_back = admin_url("admin.php?page=odlinksposts&odlinks_admin_page_arg=odlinksposts&odlinks_admin_action=bannedlinks");
					echo '<p><form method="post" action="'.$url_back.'"><input type="submit" value="&#060;&#060; Back"></form></p>';

					$sql="DELETE FROM {$table_prefix}odbanned WHERE b_id=$id"; 
					$wpdb->query($sql); 
				}else{ 
					?><h3>Removing a Banned record...</h3><?php
					$sql="SELECT * FROM {$table_prefix}odbanned WHERE b_id=$id";
					$results=$wpdb->get_results($sql);
					for($i=0; $i<count($results); $i++){
						$row=$results[$i];
					?>
					<form method="post" id="odl_form_post" name="odl_form_post" action="<?php echo $linkb ?>&odlinks_admin_action=bannedlinks&id=<?php echo $id; ?>&action=delete" method="POST">
					<p>Are you sure you want to delete this entry?<br>
					<strong>String: </strong><?php echo $row->c_domain?></p> 
					<input type="submit" name="odlinksdata[delete_ban]" value="Delete Ban">&nbsp;&nbsp;<input type=button value="Cancel" onclick="history.go(-1);"></form>
					<?php
					}
				} 
			break; 
		}
	}else{ 
		?>
		<p>This page displays the banned IP/URL addresses.<br>
		You can choose to edit or delete a specific ban record.<br>
		The URLs that contains any banned string will be automatically rejected.</p>
		<p><b>Add a banned address to the list</b><br>
		<form method="post" id="odl_form_post" name="odl_form_post" action="<?php echo $linkb ?>&odlinks_admin_action=bannedlinks&id=<?php echo $new->b_id; ?>&action=insert">
        <input type="hidden" name="odlinksdata[ban_link]" value="1"> <?php $warning?> 
        Record:	<input type="text" name="odlinksdata[url]" value="<?php echo $new->b_url?>" size="40"> 
        <input type="submit" value="Add Ban!"></p>
        <hr>
        <?php
        $sql="SELECT * FROM {$table_prefix}odbanned";
        $results=$wpdb->get_results($sql);
        if(!empty($results)){
          ?> 
          <table width="100%" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF"><tr><td>
          <table width="100%" cellspacing="1" cellpadding="3" border="0"><tr bgcolor="#CCCCCC">
          <td>Edit URL/IP/String</td>
          <td>Delete</td></tr> 
          <?php
          for($i=0; $i<count($results); $i++){
              $row=$results[$i];
              ?>
              <tr bgcolor="#F4F4F4" onMouseOver="this.bgColor='#FFFFFF';" onMouseOut="this.bgColor='#F4F4F4';">
              <?php
              echo "<td><a href=\"".$linkb."&odlinks_admin_action=bannedlinks&action=edit&id=".$row->b_id."\">".$row->c_domain."</a>";
              echo "<td><a href=\"". $linkb ."&odlinks_admin_action=bannedlinks&action=delete&id=".$row->b_id ."\"><img border=0 src=\"" .get_bloginfo('wpurl'). "/wp-content/plugins/odlinks/images/delete.png\"</a>"; 
          } 
			?>
			</table></td></tr></table>
        </form>
        <?php
      }
	} 
} // odlinksadmin_banned_links


function odlinksadmin_edit_links($id){
	global $_GET, $_POST, $wpdb, $table_prefix;
	$odlinkssettings=get_option('odlinksdata');
	if(isset($id)){ 
		$id=$_POST['odlinksdata']['id'];
		$title=$_POST['odlinksdata']['title'];
		$url=$_POST['odlinksdata']['url'];
		$description=$_POST['odlinksdata']['description'];
		$parent=$_POST['odlinksdata']['parent'];
		$email=$_POST['odlinksdata']['email']; 
		$sql="update {$table_prefix}odlinks set l_url='".$url."',
		l_title = '".$title."', l_description='".$description."',
		l_c_id='".$parent."', l_hide='visible', l_author_mail='".$email."' WHERE l_id=" .$id; 
		$wpdb->query($sql);
		?> 
		<p>Record updated successfully.</p>
		<br> URL: <?php echo $url?>
		<br> Title: <?php echo $title?>
		<br> Email: <?php echo $email?>
		<br> Description: <?php echo $description?>
		<br> Category id: <?php echo $parent?><br>
		<?php 
		$url_back = admin_url("admin.php?page=odlinksposts&odlinks_admin_page_arg=odlinksposts&odlinks_admin_action=main");
		echo '<p><form method="post" action="'.$url_back.'"><input type="submit" value="&#060;&#060; Back"></form></p>';
	} 
}

function odlinksadmin_edit_ban($id){
	global $_GET, $_POST, $wpdb, $table_prefix;
	$odlinkssettings=get_option('odlinksdata');

	if(isset($id)){ 
		$id=$_POST['odlinksdata']['id'];
		$url=$_POST['odlinksdata']['url'];
		$sql="update {$table_prefix}odbanned set c_domain='".$url."' WHERE b_id=" .$id; 
		$wpdb->query($sql);
		?> 
		<p>Record updated successfully.</p>
		<b>String: </b><?php echo $url?><br> 
		<?php
		$url_back = admin_url("admin.php?page=odlinksposts&odlinks_admin_page_arg=odlinksposts&odlinks_admin_action=bannedlinks");
		echo '<p><form method="post" action="'.$url_back.'"><input type="submit" value="&#060;&#060; Back"></form></p>';
	} 
}

function odlinksadmin_main_links($id, $action){
	global $table_prefix, $wpdb, $_GET;
	$linkb=$PHP_SELF."?page=odlinksposts&odlinks_admin_page_arg=odlinksposts";
	if(isset($id) && $action){ 
		switch($action){ 
			case "delete": 
				if(isset($_POST['odlinksdata']['delete_link'])){ 
					?><h3>Deleted link...</h3><?php
					$sql="SELECT * FROM {$table_prefix}odlinks WHERE l_id=$id";
					$results=$wpdb->get_results($sql); 
					for($i=0; $i<count($results); $i++){
						$row=$results[$i];
						echo "URL: ".$row->l_url."<br>"; 
						echo "Title: ".$row->l_title."<br>"; 
						echo "Description: ".$row->l_description."<br>"; 
						echo "Email: ".$row->l_author_mail."<br>"; 
					}
					$sql="DELETE FROM {$table_prefix}odlinks WHERE l_id=$id"; 
					$wpdb->query($sql); 
				}else{ 
					?><h3>Deleted link ....</h3><?php
					$sql="SELECT * FROM {$table_prefix}odlinks WHERE l_id=$id";
					$results=$wpdb->get_results($sql); 
					for($i=0; $i<count($results); $i++){
						$row=$results[$i];
					?>
					<form method="post" id="odl_form_post" name="odl_form_post"  action="<?php echo $linkb ?>&odlinks_admin_action=main&id=<?php echo $id; ?>&action=delete">
					<p>Are you sure you want to delete the website <strong><?php echo $row->l_url?></strong>?</p> 
					<input type="submit" name="odlinksdata[delete_link]" value="Delete Link">&nbsp;&nbsp;<input type=button value="Cancel" onclick="history.go(-1);"></form>
					<?php
					}
				}
			break; 
		}
	} else{ 
		$results=$wpdb->get_results("SELECT * FROM {$table_prefix}odlinks WHERE l_id='".$id."'");
		if(!empty($results)) {
			for($i=0; $i<count($results); $i++){
				$row=$results[$i];
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
				 
				<form method="post" id="odl_form_post" name="odl_form_post" action="<?php echo $linkb ?>&odlinks_admin_action=editlinks&id=<?php echo $row->l_id; ?>">
				<fieldset>
				<legend>Edit Website</legend>
				<input type="hidden" name="odlinksdata[id]" value="<?php echo $row->l_id; ?>">
				<label>URL:</label>
				<input type="text" name="odlinksdata[url]" value="<?php echo $row->l_url; ?>" size="80">
            &nbsp;Site view:<a target="_blank" href="<?php echo $row->l_url?>"><img src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/odlinks/images/goto_url.gif"></a> 
				<br><label>Title:</label>
				<input type="text" name="odlinksdata[title]" value="<?php echo $row->l_title; ?>" size="80">
				<br><label>Email:</label>
				<input type="text" name="odlinksdata[email]" value="<?php echo $row->l_author_mail; ?>" size="80">
				<br><label>Description:</label>
				<textarea rows="5" name="odlinksdata[description]" cols="60"><?php echo $row->l_description; ?></textarea>
				<br><label>Visible?</label>
				<select name="odlinksdata[visible]"> 
				<?php
				if($row->l_hide == 'visible'){ 
					$yes="SELECTED";
				} else{ 
					$no="SELECTED";
				} 
				?> 
				<option <?php $yes?> value="1">Yes</option> 
				<option <?php $no?> value="0">No</option> 
				</select>
				<br><label>Category:</label><select name="odlinksdata[parent]"> <?php echo odl_list_cats(0,0,0,$row->l_c_id); ?> </select>
				<P><label>&nbsp;</label>
				<input  type="submit" value="Save Link!">&nbsp;&nbsp;<input type=button value="Cancel" onclick="history.go(-1);">
				</fieldset>	
				</form> 
				<?php
			}
		} else {
			$sql="SELECT COUNT(l_id) as count FROM {$table_prefix}odlinks";
			$result=$wpdb->get_row($sql, ARRAY_A); 
			$NumberOfResults=$result['count']; 
			$sql="SELECT * FROM {$table_prefix}odlinks";
			$results=$wpdb->get_results($sql); 
			if(!empty($results)){
				?> 
				<p>&nbsp;</p>
				<table width="100%" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF"><tr>
				<td>
				<table width="100%" cellspacing="1" cellpadding="3" border="0">
				<tr bgcolor="#CCCCCC">
				<td>Edit</td> 
				<td>WebSite URL</td> 
				<td>Visible</td> 
				<td>Title</td> 
				<td nowrap>Date</td> 
				<td> </td> 
				<td>Delete</td> </tr> 
				<?php
				for($x=0; $x<count($results); $x++){
					$row=$results[$x];
					?> 
					<tr bgcolor="#F4F4F4" onMouseOver="this.bgColor='#FFFFFF';" onMouseOut="this.bgColor='#F4F4F4';"> 
					<td><a href="<?php echo $linkb ?>&odlinks_admin_action=main&id=<?php echo $row->l_id ?>">
					<img border=0 src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/odlinks/images/edit.gif"></td>
					<td><?php echo $row->l_url ?></td>
					<?php
					if($row->l_hide == "visible"){ 
						$new_link="<font color=blue>Yes</font>";
					} else{ 
						$new_link="<font color=purple>No</font>";
					} 
					echo "<td>".$new_link."</td>"; 
					echo "<td><a target=\"_blank\" href=\"".$row->l_url."\">".$row->l_title."</a></td>"; print "<td nowrap>".$row->l_date."</td>"; print "<td>";
					echo"</td>"; 
					echo "<td><a href=\"". $linkb ."&odlinks_admin_action=main&action=delete&id=".$row->l_id ."\"><img border=0 src=\"" .get_bloginfo('wpurl'). "/wp-content/plugins/odlinks/images/delete.png\"</a>"; 
					?> </td></tr> 
					<?php
				} //for main links
				?>  
				</table></td></tr>
				</table> 
				<?php
			} // for 
		} // else
	}
	return $msg;
}


?>
