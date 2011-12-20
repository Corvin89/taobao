<?php

/*
 * odl_main.php
 * @author Mohammad Forgani
 * wordpress plugin website directory project
 * @copyright Copyright 2008, Oh Jung-Su
 * @version 1.0
 * @link http://www.forgani.com
*/



function odlinksadmin_page(){
	global $_GET, $_POST, $PHP_SELF, $user_level, $wpdb, $pagelabel,
		$odlinksuser_level, $odlinksversion;

	get_currentuserinfo();

	$odlinkssettings = get_option('odlinksdata');

	?>
	<div class="wrap">
		<h2>
			<?php echo $pagelabel;?>
		</h2>

		<?php
      if (isset($_REQUEST['odlinks_admin_page_arg'])) 
        switch ($_REQUEST['odlinks_admin_page_arg']){
          case "odlinkssettings":
              process_odlinkssettings();
          break;
          case "odlinksstructure":
              process_odlinksstructure();
          break;
          case "odlinksposts":
              process_odlinksposts();
          break;
          case "odlinksutilities":
              process_odlinksutilities();
          break;
        }
      process_odlinkssettings();
		?>
	</div>
	<?php

}

?>
