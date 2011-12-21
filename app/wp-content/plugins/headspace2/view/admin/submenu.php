<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<ul class="subsubsub">
  <li><a <?php if ($sub == '') echo 'class="current"'; ?>href="<?php echo $url ?>"><?php _e ('Page Settings', 'headspace') ?></a> |</li>
  <li><a <?php if ($sub == 'modules') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=modules"><?php _e ('Page Modules', 'headspace') ?></a> |</li>
  <li><a <?php if ($sub == 'site') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=site"><?php _e ('Site Modules', 'headspace') ?></a> |</li>
  <li><a <?php if ($sub == 'options') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=options"><?php _e ('Options', 'headspace') ?></a> |</li>
  <li><a <?php if ($sub == 'import') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=import"><?php _e ('Import', 'headspace') ?></a></li>
</ul>
