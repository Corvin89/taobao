<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><h2><?php _e ('HeadSpace Settings', 'headspace'); ?></h2>

<?php $this->render_admin ('page-settings-edit', array ('simple' => $simple, 'advanced' => $advanced, 'width' => '33%', 'area' => 'category')); ?>
