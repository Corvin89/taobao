<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<?php $this->render_admin ('annoy'); ?>
	<?php screen_icon(); ?>
	
	<h2><?php printf (__ ('%s | Import', 'headspace'), HEADSPACE_MENU); ?></h2>
  
	<?php $this->submenu (true); ?>
	
	<p><?php _e ('This page will allow you to import meta-data from other WordPress plugins.  The other plugins do not need to be active for the import to work.', 'headspace'); ?></p>
	
	<form action="" method="post" accept-charset="utf-8">
		<select name="importer">
			<?php foreach ($modules AS $type => $module) : ?>
				<option value="<?php echo $type ?>"><?php echo $module->name (); ?></option>
			<?php endforeach; ?>
		</select>
	
		<?php wp_nonce_field ('headspace-import'); ?>
	
		<input class="button-primary" type="submit" name="import" value="<?php _e ('Import', 'headspace'); ?>"/>
		<input class="button-secondary" type="submit" name="import_cleanup" value="<?php _e ('Import and remove original data', 'headspace'); ?>"/> <span class="sub"><?php _e ('(not available in UTW and Simple Tagging)', 'headspace'); ?></span>
	</form>
	
	<p><?php _e ('As with anything else that modifies your database you should <strong>backup your data before running an import</strong>.  No responsibility is accepted for any kittens that may be killed in the process.', 'headspace'); ?></p>
</div>