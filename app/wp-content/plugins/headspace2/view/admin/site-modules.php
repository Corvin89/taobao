<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<?php $this->render_admin ('annoy'); ?>
	<?php screen_icon(); ?>

	<h2><?php printf (__ ('%s | Site Modules', 'headspace'), HEADSPACE_MENU); ?></h2>
	
	<?php $this->submenu (true); ?>
	
	<p><?php _e ('Site modules apply to your site as a whole.  Only checked modules will run (when properly configured).', 'headspace'); ?></p>
	
	<div class="settings">
		<ul>
			<?php foreach ($site->modules AS $module) : ?>
				<li id="site_<?php echo $module->id () ?>" class="module <?php if (!$module->is_active ()) echo 'disabled'?>">
					<?php $this->render_admin ('site-module-item', array ('module' => $module)); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>

<script type="text/javascript">
jQuery(document).ready( function() {
	var hs = new HeadSpace( { ajaxurl: '<?php echo admin_url( 'admin-ajax.php' ); ?>', nonce: '<?php echo wp_create_nonce ('headspace-site_module')?>' });
	hs.site_modules();
});
</script>