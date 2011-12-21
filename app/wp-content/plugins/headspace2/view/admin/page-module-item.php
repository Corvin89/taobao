<div class="option">
	<?php if ($module->has_config ()) : ?>
	<a class="edit" href="admin-ajax.php?action=hs_module_edit&amp;_ajax_nonce=<?php echo wp_create_nonce ('headspace-module_'.$module->id() )?>&amp;module=<?php echo $module->id(); ?>">
		<img src="<?php echo $this->url () ?>/images/edit.png" width="16" height="16" alt="Edit"/>
	</a>
	<?php endif; ?>
	
	<a class="help" href="#help"><img src="<?php echo $this->url () ?>/images/help.png" width="16" height="16" alt="Help"/></a>
</div>

<?php echo $module->name (); ?>

<div class="help" style="display: none">
	<?php echo htmlspecialchars ($module->description ()); ?>
</div>