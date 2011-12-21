<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<script type="text/javascript" charset="utf-8">
	var wp_hs_base     = '<?php echo $this->url (); ?>/ajax.php';
	var headspace_delete = '<?php echo $this->url () ?>/images/delete.png';
	var wp_hs_mergetag = '<?php _e( 'Are you sure you want to merge that tag?', 'headspace' ); ?>';
	var headspace_error = '<?php _e('A problem occured retrieving data from the server. If this persists please check that you have installed the plugin correctly.'); ?>';
</script>