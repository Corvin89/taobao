<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<ul id="<?php echo $id ?>">
	<?php if (count ($modules) > 0) : ?>
		<?php foreach ($modules AS $module) : ?>
			<li id="id_<?php echo $module->id () ?>">
				<?php $this->render_admin ('page-module-item', array ('module' => $module, 'config' => $id == 'disabled-modules' ? false : true)); ?>
			</li>
		<?php endforeach; ?>
	<?php endif; ?>
</ul>