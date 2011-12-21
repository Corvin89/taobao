<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<div style="display: none" class="option">
	<img src="<?php echo $this->url (); ?>/images/progress.gif" alt="progress"/>
</div>

<img src="<?php echo $this->url (); ?>/images/page.png" alt="page" width="16" height="16" />

<?php if ($nolink !== true) : ?>
	<a href="<?php echo admin_url('admin-ajax.php'); ?>?action=hs_settings_edit&amp;page=<?php echo $type ?>&amp;_ajax_nonce=<?php echo wp_create_nonce( 'headspace-edit_setting_'.$type )?>"><strong><?php echo $name ?></strong></a>
<?php else : ?>
	<strong><?php echo $name ?></strong>
<?php endif; ?>

- <?php echo $desc ?>
