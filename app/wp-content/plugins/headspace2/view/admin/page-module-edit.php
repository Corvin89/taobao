<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<?php echo $module->name (); ?>

<form style="font-size: 0.9em" action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post" accept-charset="utf-8">
	<table class="headspace">
		<?php $module->edit_options (); ?>
		
		<tr>
			<th></th>
			<td>
				<input class="button-primary" type="submit" name="save" value="<?php _e ('Save', 'headspace'); ?>"/>
				<input class="button-secondary" type="submit" name="cancel" value="<?php _e ('Cancel', 'headspace'); ?>"/>
				
				<input type="hidden" name="action" value="hs_module_save"/>
				<input type="hidden" name="module" value="<?php echo htmlspecialchars( $id ); ?>"/>
				<input type="hidden" name="_ajax_nonce" value="<?php echo wp_create_nonce ('headspace-module_save_'.$id ) ?>"/>
			</td>
		</tr>
	</table>
</form>
