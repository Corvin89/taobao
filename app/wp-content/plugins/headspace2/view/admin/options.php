<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<div class="wrap">
	<?php $this->render_admin ('annoy'); ?>
	<?php screen_icon(); ?>
	
  <h2><?php printf (__ ('%s | General Options', 'headspace'), HEADSPACE_MENU); ?></h2>

	<?php $this->submenu (true); ?>
	
	<form method="post" action="">
	<?php wp_nonce_field ('headspace-update_options'); ?>

	<table border="0" cellspacing="5" cellpadding="5" class="form-table">
		<tr>
			<th valign="top" align="right"><label for="inherit"><?php _e ('Inherit settings', 'headspace') ?></label>
			</th>
			<td valign="top" >
				<input type="checkbox" id="inherit" name="inherit"<?php if ($options ['inherit'] == true) echo ' checked="checked"' ?>/>
				<span class="sub"><?php _e ('Inherit from global settings', 'headspace'); ?></span>
			</td>
		</tr>
		<tr>
			<th valign="top" align="right"><label for="debug"><?php _e ('Process excerpt with plugins', 'headspace') ?></label>
			</th>
			<td valign="top" >
				<input type="checkbox" id="excerptx" name="excerpt"<?php if ($options ['excerpt'] == true) echo ' checked="checked"' ?>/>
				<span class="sub"><?php _e ('Will allow plugins to modify generated excerpts', 'headspace'); ?></span>
			</td>
		</tr>
		<tr>
			<th align="right"><?php _e ('Plugin Support', 'headspace'); ?>:</th>
			<td>
				<input type="checkbox" name="support" <?php echo $this->checked ($options['support']) ?> id="support"/> 
				<label for="support"><span class="sub"><?php _e ('I\'m a nice person and I have helped support the author of this plugin', 'headspace'); ?></span></label>
			</td>
		</tr>
		<tr>
			<th valign="top" align="right"><label for="debug"><?php _e ('Debug', 'headspace') ?></label>
			</th>
			<td valign="top" >
				<input type="checkbox" id="debug" name="debug"<?php if ($options ['debug'] == true) echo ' checked="checked"' ?>/>
				<span class="sub"><?php _e ('Enable debug option', 'headspace'); ?></span>
			</td>
		</tr>
		<tr>
			<th/>
			<td>
				<input class="button-primary" type="submit" name="save" value="<?php echo __('Update Options &raquo;', 'headspace')?>" />
			</td>
		</tr>
	</table>
</form>
</div>

<div class="wrap">
	<h2><?php _e ('Remove HeadSpace', 'headspace'); ?></h2>
	
	<p><?php _e ('This option will remove HeadSpace and delete all settings, tags, and meta-data - be sure this is what you want!', 'headspace'); ?></p>
	
	<form action="" method="post" accept-charset="utf-8">
		<?php wp_nonce_field ('headspace-delete_plugin'); ?>
		
		<input class="button-secondary" type="submit" name="delete" value="<?php _e ('Delete HeadSpace', 'headspace'); ?>" onclick="return confirm ('Are you sure you want to remove HeadSpace and all settings?')"/>
	</form>
</div>
