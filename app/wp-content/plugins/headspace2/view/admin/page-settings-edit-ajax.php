<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<form action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post">
	<?php $this->render_admin( 'page-settings-edit', array ('simple' => $simple, 'advanced' => $advanced, 'width' => '140px', 'id' => $type, 'area' => 'ajax' ) );?>

	<input class="button-primary" style="margin-left: 118px" type="submit" name="save" value="<?php _e ('Save', 'headspace'); ?>"/>
	<input class="button-secondary" type="submit" name="cancel" value="<?php _e ('Cancel', 'headspace'); ?>"/>

	<input type="hidden" name="module" value="<?php echo $type ?>"/>
	<input type="hidden" name="action" value="hs_settings_save"/>
	<input type="hidden" name="_ajax_nonce" value="<?php echo wp_create_nonce( 'headspace-page_setting_'.$type ) ?>"/>
</form>
