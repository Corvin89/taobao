<div class="headspace column-headspace quick-edit-div" title="<?php _e('HeadSpace', 'headspace'); ?>" style="width: 300px; height: auto ">
	<div class="title"><?php _e('HeadSpace', 'headspace'); ?></div>
	<div class="in">
		<table width="100%" style="margin-top: 5px">
			<?php foreach ($simple AS $module) : ?>
				<?php $module->edit ($width, $area); ?>
			<?php endforeach; ?>
		</table>
	</div>
</div>