<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<?php if (count ($simple) > 0 || count ($advanced) > 0) : ?>
	
<div class="meta-box-sortables ui-sortable" style="position: relative;">
<div class="postbox">
	<h3 class="hndle"><?php _e ('HeadSpace Meta data', 'headspace') ?></h3>
	
	<div class="inside" id="headspacestuff">
		<?php $this->render_admin ('page-settings-edit', array ('simple' => $simple, 'advanced' => $advanced, 'width' => 140, 'area' => 'page')); ?>
	</div>
</div>
</div>
<?php endif; ?>

