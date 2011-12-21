<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<div class="wrap">
	<?php $this->render_admin ('annoy'); ?>
	<?php screen_icon(); ?>
	
	<h2><?php printf (__ ('%s | Page Modules', 'headspace'), HEADSPACE_MENU); ?></h2>
	
	<?php $this->submenu (true); ?>
	
	<p><?php _e ('Page modules apply to individual pages.  Drag-and-drop modules into the appropriate area.  Modules can be re-ordered to change their position on the edit screen.', 'headspace'); ?></p>

	<ul class="modules">
		<li id="simple">
			<h3><?php _e ('Simple', 'headspace'); ?></h3>
			<p><?php _e ('Modules will always appear on edit screens', 'headspace'); ?></p>
			
			<?php $this->render_admin ('page-module-list', array ('modules' => $simple, 'id' => 'simple-modules')); ?>
		</li>
		<li id="advanced">
			<h3><?php _e ('Advanced', 'headspace'); ?></h3>
			<p><?php _e ('Modules will be hidden behind a link', 'headspace'); ?></p>
			
			<?php $this->render_admin ('page-module-list', array ('modules' => $advanced, 'id' => 'advanced-modules')); ?>
		</li>
	</ul>
	<div style="clear: both"></div>
	<br/><br/>
	
	<ul class="modules">
		<li id="disabled">
			<h3><?php _e ('Disabled', 'headspace'); ?></h3>
			<p><?php _e ('Modules are disabled and do not appear', 'headspace'); ?></p>
			
			<?php $this->render_admin ('page-module-list', array ('modules' => $disabled, 'id' => 'disabled-modules')); ?>
		</li>
	</ul>
	
	<script type="text/javascript" charset="utf-8">
		jQuery(document).ready( function() {
			var hs = new HeadSpace( { ajaxurl: '<?php echo admin_url( 'admin-ajax.php' ) ?>', nonce: '<?php echo wp_create_nonce ('headspace-save_order')?>' });
			hs.page_modules();
		});
	</script>
	
	<div style="clear:both"></div>
</div>
