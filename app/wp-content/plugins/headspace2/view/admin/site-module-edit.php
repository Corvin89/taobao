<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="option">
	<img style="display: none" src="<?php echo $this->url () ?>/images/progress.gif" width="50" height="16" alt="Progress" id="load_<?php echo $module->id () ?>"/>
</div>

<input type="checkbox" <?php if ($module->is_active ()) echo ' checked="checked"' ?> name="site_modules[<?php echo $module->id() ?>]" value="<?php echo $module->file(); ?>" />

<?php echo $module->name (); ?>

<form action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post">
	<table class="headspace">
		<?php $module->edit (); ?>
		
		<tr>
			<th></th>
			<td>
				<input class="button-primary" type="submit" name="save" value="<?php _e ('Save', 'headspace'); ?>"/>
				<input class="button-secondary" type="submit" name="cancel" value="<?php _e ('Cancel', 'headspace'); ?>"/>
				
				<input type="hidden" name="action" value="hs_site_save"/>
				<input type="hidden" name="module" value="<?php echo htmlspecialchars( $module->id() ); ?>"/>
				<input type="hidden" name="_ajax_nonce" value="<?php echo wp_create_nonce ('headspace-site_save_'.$module->id() ) ?>"/>
			</td>
		</tr>
	</table>
</form>
