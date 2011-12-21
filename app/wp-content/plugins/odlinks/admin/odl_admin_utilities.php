<?php

/*
 * odl_admin_utilities.php
 *
 * This file handles the Administration of the Categories
 *
 * @author Mohammad Forgani
 * @copyright Copyright 2008, Oh Jung-Su
 * @version 1.0
 * @link http://www.forgani.com
*/


function process_odlinksutilities(){
	global $_GET, $_POST, $wpdb;
	$exit = FALSE;
	$msg = '<div class="wrap"><h2>ODLinks Utilities</h2><p>';	
	switch ($_GET["odlinks_admin_action"]){
		default:
		case "list":
			?>
			<?php
		break;
		case "uninstall":
			odlinksuninstall_db();
			$deactivate_url = 'plugins.php?action=deactivate&plugin=odlinks/odl_control.php';
			if(function_exists('wp_nonce_url')) {
				$deactivate_url = wp_nonce_url($deactivate_url, 'deactivate-plugin_odlinks/odl_control.php');
			}
         update_option('odlinksdata', array());
			$msg .= '<h3><strong><a href='.$deactivate_url.'>Click Here</a> to finish the uninstallation and "ODLinks" will be deactivated automatically.</strong></h3></div>';
			$exit = TRUE;
		break;
	}
	if ($msg!=''){
		?>
		<p>
		<b><?php echo $msg; ?></b>
		</p>
		<?php
	}
?>

<script language=javascript>
<!--
function uninstallodlinks(y){
	if (confirm("Are you sure you want to Uninstall the ODLinks?\n")){
		document.location.href = y;
	}
}
//-->
</script>
	<?php
	if (!$exit) {
	?>
		<p>
			<h3>Uninstall</h3>
			Just make sure you create backups before you drops the Open Directory Links Database tables.
			<br />
			<br />
			PLEASE NOTE: To remove ODLinks you must deactivate it via the Plugins page BEFORE removing the odlinks folder.
			<br />
			<br />
			<a href="javascript:uninstallodlinks('<?php echo $PHP_SELF;?>?page=odlinksutilities&odlinks_admin_action=uninstall')">Uninstall ODLinks from the Database?</a>
		</p></div>
	<?php
	}
}

function odlinksuninstall_db(){
	global $wpdb, $table_prefix;
	$wpdb->query("DROP TABLE {$table_prefix}odcategories, {$table_prefix}odlinks, {$table_prefix}odnew_links, {$table_prefix}odpages, {$table_prefix}odbanned");
	$wpdb->query("DELETE FROM {$table_prefix}posts WHERE post_title = '[[ODLINKS]]'");
	$wpdb->query("DELETE FROM {$table_prefix}options WHERE option_name = 'odlinksdata'");
	echo '<div id="message" class="updated fade">Uninstall Successful!</div>';
}


?>
