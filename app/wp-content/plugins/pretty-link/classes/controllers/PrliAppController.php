<?php
class PrliAppController
{
  function PrliAppController()
  {
    add_action('init', array(&$this,'parse_standalone_request'));
    add_action('admin_notices', array(&$this, 'upgrade_database_headline'));
  }
  
  public function upgrade_database_headline()
  {
    global $prli_update, $prli_db_version, $prlipro_db_version, $prli_blogurl;

    $old_prli_db_version = get_option('prli_db_version');
    $show_db_upgrade_message = ( !$old_prli_db_version or ( intval($old_prli_db_version) < $prli_db_version ) );

    if( !$show_db_upgrade_message and
        $prli_update->pro_is_installed_and_authorized())
    {
      $old_prlipro_db_version = get_option('prlipro_db_version');
      $show_db_upgrade_message = ( !$old_prlipro_db_version or ( intval($old_prlipro_db_version) < $prlipro_db_version ) );
    }

    if( $show_db_upgrade_message )
    {
       $db_upgrade_url = wp_nonce_url("{$prli_blogurl}/index.php?plugin=pretty-link&controller=admin&action=db_upgrade", "prli-db-upgrade");
       ?>
       <div class="error" style="padding-top: 5px; padding-bottom: 5px;"><?php printf(__('Database Upgrade is required for Pretty Link to work properly<br/>%1$sAutomatically Upgrade your Database%2$s', 'pretty-link'), "<a href=\"{$db_upgrade_url}\">",'</a>'); ?></div>
       <?php
    }
  }
  
  public function parse_standalone_request()
  {
    if( !empty($_REQUEST['plugin']) and $_REQUEST['plugin'] == 'pretty-link' and 
        !empty($_REQUEST['controller']) and !empty($_REQUEST['action']) )
    {
      $this->standalone_route($_REQUEST['controller'], $_REQUEST['action']);
      exit;
    }
  }
  
  public function standalone_route($controller, $action)
  {
    if($controller=='admin')
    {
      if($action=='db_upgrade')
        $this->db_upgrade();
    }
  }
  
  public function db_upgrade()
  {
    global $prli_blogurl, $prli_update, $prli_db_version, $prlipro_db_version;
    
    if(!function_exists('wp_redirect'))
      require_once(ABSPATH . WPINC . '/pluggable.php');

    if( wp_verify_nonce( $_REQUEST['_wpnonce'], "prli-db-upgrade" ) and current_user_can( 'update_core' ) ) {
      $old_prli_db_version = get_option('prli_db_version');
      $upgrade_db = ( !$old_prli_db_version or ( intval($old_prli_db_version) < $prli_db_version ) );

      if( !$upgrade_db and $prli_update->pro_is_installed_and_authorized()) {
        $old_prlipro_db_version = get_option('prlipro_db_version');
        $upgrade_db = ( !$old_prlipro_db_version or ( intval($old_prlipro_db_version) < $prlipro_db_version ) );
      }
      
      if( $upgrade_db ) {
        prli_install();
        wp_redirect("{$prli_blogurl}/wp-admin/admin.php?page=pretty-link/prli-links.php&message=" . urlencode(__('Your Database Has Been Successfully Upgraded.')));
      }
      else
        wp_redirect($prli_blogurl);
    }  
    else
      wp_redirect($prli_blogurl);
  }
}
?>
