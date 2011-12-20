<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><div class="option">
	<img style="display: none" src="<?php echo $this->url () ?>/images/progress.gif" width="50" height="16" alt="Progress" class="load" />
	
	<?php if ($module->has_config ()) : ?>
	<a href="<?php echo admin_url( 'admin-ajax.php' ) ?>?action=hs_site_edit&amp;module=<?php echo $module->id(); ?>" class="edit"><img src="<?php echo $this->url () ?>/images/edit.png" width="16" height="16" alt="Edit"/></a>
	<?php endif; ?>
	
	<a href="#help" class="help">
		<img src="<?php echo $this->url () ?>/images/help.png" width="16" height="16" alt="Help"/>
	</a>
</div>

<input type="checkbox" <?php if ($module->is_active ()) echo ' checked="checked"' ?> name="site_modules[<?php echo $module->id() ?>]" value="<?php echo $module->file(); ?>" />

<?php echo $module->name (); ?>

<div class="help" style="display: none">
	<?php echo $module->description (); ?>
</div>