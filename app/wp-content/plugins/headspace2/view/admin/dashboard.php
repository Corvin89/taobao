<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<script type="text/javascript" charset="utf-8">
	var wp_hs_base     = '<?php echo $this->url (); ?>/ajax.php';

	jQuery(document).ready(function()
	{
		var clone = jQuery('#headspace_dash').clone ();
		jQuery(clone).attr ('id', 'dashboard_headspace');
		
		// Copy internals of HeadSpace box into QuickPress
		jQuery('#quick-press .submit').before ('<p class="submit" id="head_break"></p>');
		jQuery('#dashboard_headspace').remove ();
		jQuery('#head_break').after (clone);
	});
</script>

<div id="headspace_dash">
	<?php $this->render_admin ('page-settings-edit', array ('simple' => $simple, 'advanced' => $advanced, 'width' => 140, 'area' => 'page')); ?>
	<br/>
</div>