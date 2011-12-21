<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<input type="hidden" name="headspace" value="headspace"/>

<table width="100%" style="margin-top: 5px">
	<?php foreach( $simple AS $module ) : ?>
		<?php $module->edit( $width, $area ); ?>
	<?php endforeach; ?>
</table>	

<?php if ( count( $advanced ) > 0 ) : ?>
	<table width="100%" style="display: none" class="toggle">
		<?php foreach ( $advanced AS $module ) : ?>
			<?php $module->edit ($width, $area); ?>
		<?php endforeach; ?>
	</table>
	
	<div style="text-align: right; margin: 0 20px; padding: 0; font-size: 0.9em">
		<a href="#toggle"><?php _e ('advanced', 'headspace'); ?></a>
	</div>
<?php endif; ?>
