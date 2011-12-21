<?php
// This file is part of Shutter Reloaded WordPress plugin

if ( !defined('SREL_SETTINGS') )
	exit;

if ( ! current_user_can('manage_options') )
		die( __('Permission denied', 'srel_l10n') );

	$opt = get_option('srel_main');
	if ( !$opt )
		$opt = 'srel_all';

	srel_txtdomain();

	$srel_options = get_option('srel_options', array());
	$def = array( 'shcolor' => '000000', 'opacity' => '80', 'capcolor' => 'ffffff', 'menucolor' => '3e3e3e', 'btncolor' => 'cccccc', 'countcolor' => '999999', 'headload' => 0, 'oneset' => 1, 'imageCount' => 1, 'textBtns' => 0, 'custom' => 0 );

	if ( empty($srel_options) ) {
		$srel_options = $def;
		update_option('srel_options', $srel_options);
	}

	if ( isset($_POST['srel_main']) ) {
		check_admin_referer('srel-save-options');
		$newopt = $_POST['srel_all'] ? 'srel_all' : '';
		$newopt = $_POST['srel_class'] ? 'srel_class' : $newopt;
		$newopt = $_POST['auto_set'] ? 'auto_set' : $newopt;
		$newopt = $_POST['srel_pages'] ? 'srel_pages' : $newopt;
		$newopt = $_POST['srel_lb'] ? 'srel_lb' : $newopt;

		if ( $newopt != $opt ) {
			$opt = $newopt;
			update_option('srel_main', $newopt);
		}
	}

	if ( isset($_POST['srel_saveopt']) ) {
		check_admin_referer('srel-save-options');
		$new_opt['shcolor'] = preg_match("/[0-9A-Fa-f]{6}/", $_POST['shcolor']) ? strtolower($_POST['shcolor']) : '000000';
		$new_opt['capcolor'] = preg_match("/[0-9A-Fa-f]{6}/", $_POST['capcolor']) ? strtolower($_POST['capcolor']) : 'ffffff';
		$new_opt['menucolor'] = preg_match("/[0-9A-Fa-f]{6}/", $_POST['menucolor']) ? strtolower($_POST['menucolor']) : '3e3e3e';
		$new_opt['btncolor'] = preg_match("/[0-9A-Fa-f]{6}/", $_POST['btncolor']) ? strtolower($_POST['btncolor']) : 'cccccc';
		$new_opt['countcolor'] = preg_match("/[0-9A-Fa-f]{6}/", $_POST['countcolor']) ? strtolower($_POST['countcolor']) : '999999';
		$new_opt['imageCount'] = isset($_POST['imageCount']) ? 1 : 0;
		$new_opt['textBtns'] = isset($_POST['textBtns']) ? 1 : 0;
		$new_opt['opacity'] = -1 < (int) $_POST['opacity'] && (int) $_POST['opacity'] < 101 ? (int) $_POST['opacity'] : '80';
		$new_opt['headload'] = isset($_POST['headload']) ? 1 : 0;
		$new_opt['startFull'] = isset($_POST['startFull']) ? 1 : 0;
		$new_opt['oneset'] = isset($_POST['oneset']) ? 1 : 0;

		$new_opt['custom'] = ( $new_opt['shcolor'] != '000000' ||
			$new_opt['capcolor'] != 'ffffff' ||
			$new_opt['menucolor'] != '3e3e3e' ||
			$new_opt['btncolor'] != 'cccccc' ||
			$new_opt['countcolor'] != '999999' ||
			$new_opt['opacity'] != '80' ) ? 1 : 0;

		if ( $new_opt != $srel_options ) {
			$srel_options = $new_opt;
			update_option('srel_options', $new_opt);
		}
	}

	$excluded = get_option('srel_excluded', array());
	$included = get_option('srel_included', array());

	if ( isset($_POST['srel_add_excluded']) ) {
		check_admin_referer('srel-save-options');
		$exclude = (int) $_POST['srel_exclude'];
		if ( $exclude < 1 )
			wp_die(__('Please enter valid post ID.', 'srel-l10n'));

		$excluded[] = $exclude;
		$excluded = array_values(array_unique($excluded));
		sort($excluded);

		update_option('srel_excluded', $excluded);
	}

	if ( isset($_POST['srel_rem_excluded']) ) {
		check_admin_referer('srel-save-options');
		$rem_exclude = (int) $_POST['srel_exclude'];
		if ( ! in_array($rem_exclude, $excluded) ) { ?>
			<div class="error"><p><?php _e('This post ID is not currently excluded.', 'srel-l10n'); ?></p></div>
<?php	} else {
			$excluded = array_diff($excluded, (array) $rem_exclude );
			if ( is_array($excluded) ) sort($excluded);
			else $excluded = array();
			update_option('srel_excluded', $excluded);
		}
	}

	if ( isset($_POST['srel_add_included']) ) {
		check_admin_referer('srel-save-options');
		$include = (int) $_POST['srel_include'];
		if ( $include < 1 )
			wp_die(__('Please enter valid post ID.', 'srel-l10n'));

		$included[] = $include;
		$included = array_values(array_unique($included));
		sort($included);

		update_option('srel_included', $included);
	}

	if ( isset($_POST['srel_rem_included']) ) {
		check_admin_referer('srel-save-options');
		$rem_include = (int) $_POST['srel_include'];
		if ( ! in_array($rem_include, $included) ) { ?>
			<div class="error"><p><?php _e('This post ID is not currently included.', 'srel-l10n'); ?></p></div>
<?php   } else {
			$included = array_diff($included, (array) $rem_include);
			if ( is_array($included) ) sort($included);
			else $included = array();
			update_option('srel_included', $included);
		}
	}

	if ( isset($_POST['srel_delete_options']) ) {
		check_admin_referer('srel-save-options');
		delete_option('srel_options');
		delete_option('srel_main');
		delete_option('srel_included');
		delete_option('srel_excluded'); ?>
		<div id="message" class="updated fade"><p><?php _e('All options reset! You can either ', 'srel-l10n'); ?><a href="<?php echo admin_url('plugins.php'); ?>"><?php _e( 'deactivate Shutter Reloaded,', 'srel-l10n' ); ?></a> <a href=""><?php _e( 'or return to the settings page.', 'srel-l10n' ); ?></a></p></div><?php 
		return;
	} elseif ( isset($_POST['srel_saveopt']) ) { ?>
	<div id="message" class="updated fade"><p><?php _e('Options saved!', 'srel-l10n'); ?></p></div>
<?php } ?>

	<div class="wrap">
	<h2><?php _e('Shutter Reloaded Options', 'srel-l10n'); ?></h2>
	<form method="post" name="srel_mainform" id="srel_mainform" action="">

	<p class="tablenav" style="text-align:right;"><input type="button" id="srelhide" class="button" onclick="if (document.getElementById('srelhelp').style.display == 'none'){document.getElementById('srelhelp').style.display = 'block';document.getElementById('srelhide').value = '<?php echo js_escape(__("Hide Help", "srel-l10n")); ?>';
} else {document.getElementById('srelhelp').style.display = 'none';document.getElementById('srelhide').value = '<?php echo js_escape(__("Show Help", "srel-l10n")); ?>';}"
value="<?php echo js_escape(__("Show Help", "srel-l10n")); ?>" /></p>

	<div id="srelhelp" style="display:none;border: 1px solid #dfdfdf;padding:0 12px;margin:10px 0 0">
	<h4><?php _e('Setup and Usage', 'srel-l10n'); ?></h4>
	<p><?php _e('Shutter is activated by <strong>the link</strong> pointing to the image you want to display, with or without a thumbnail (text links work too). The activation class and the title have to be set on that link.', 'srel-l10n'); ?></p>
	<p><?php _e('To take full control of Shutter\'s activation and to make multiple image sets on the same page, you will need to add the <strong>class=&quot;shutter&quot;</strong> or <strong>&quot;shutterset&quot;</strong> or <strong>&quot;shutterset_setname&quot;</strong> to your links in &quot;Code&quot; view on the Write/Edit Post page.', 'srel-l10n'); ?></p>
	<p><?php _e('To add caption to the images, set the <strong>title=&quot;...&quot;</strong> attribute of the <strong>links</strong> pointing to them.', 'srel-l10n'); ?></p>
	<p><?php _e('If you want to use image sets, you will need to add <strong>class=&quot;shutterset&quot;</strong> to all <strong>links</strong> that point to the images for that set. If you want to apply css style to the links, you can add second class, like this: class=&quot;shutterset myClass&quot;, but &quot;shutterset&quot; should be first.', 'srel-l10n'); ?></p>
	<p><?php _e('Adding class=&quot;shutterset&quot; will also trigger activation (for the first activation option). There is no need to add both &quot;shutter&quot; and &quot;shutterset&quot;.', 'srel-l10n'); ?></p>
	<p><?php _e('To make more than one set per page, use <strong>class=&quot;shutterset_setname&quot;.</strong> The underscore is required and setname can be any short ASCII word and/or number (different for each set).', 'srel-l10n'); ?></p>
	<p><?php _e('You can use the &quot;Activate shutter on all image links&quot; and also make sets by adding class=&quot;shutterset&quot; or class=&quot;shutterset_setname&quot; or rel=&quot;lightbox[...]&quot; to some of the image links.', 'srel-l10n'); ?></p>
	</div>

	<div style="padding:0 12px 12px;">
	<h4><?php _e('You can add Shutter Reloaded to your site in five different ways:', 'srel-l10n'); ?> </h4>
	<?php wp_nonce_field( 'srel-save-options' ); ?>
	<input type="hidden" name="srel_main" value="srel_main" />

<?php
	if ( $opt == 'srel_class' )
		echo '<div style="background-color:#ffffe0;border:1px solid #e6db55;" class="fade"><p style="margin:8px;"><strong>'.__('Active: ', 'srel-l10n').'</strong>';
	else
		echo '<div><p><input class="button" type="submit" name="srel_class" value="'. __('Activate', 'srel-l10n').'" /> ';
	echo __('Shutter on all image links with class=&quot;shutter&quot; or &quot;shutterset&quot; or &quot;shutterset_setname&quot;.', 'srel-l10n')."</p></div>\n";

	if ( $opt == 'srel_all' )
		echo '<div style="background-color:#ffffe0;border:1px solid #e6db55;" class="fade"><p style="margin:8px;"><strong>'.__('Active: ', 'srel-l10n').'</strong>';
	else
		echo '<div><p><input class="button" type="submit" name="srel_all" value="'.__('Activate', 'srel-l10n').'" /> ';
	echo __('Shutter on all image links. Sets created with class=&quot;shutterset&quot;, &quot;shutterset_setname&quot; or rel=&quot;lightbox[...]&quot; will still work.', 'srel-l10n')."</p></div>\n";

	if ( $opt == 'auto_set' )
		echo '<div style="background-color:#ffffe0;border:1px solid #e6db55;" class="fade"><p style="margin:8px;"><strong>'.__('Active: ', 'srel-l10n').'</strong>';
	else
		echo '<div><p><input class="button" type="submit" name="auto_set" value="'.__('Activate', 'srel-l10n').'" /> ';
	echo __('Shutter on all image links and automatically make image sets for each Post/Page.', 'srel-l10n')."</p></div>\n";

	if ( $opt == 'srel_pages' )
		echo '<div style="background-color:#ffffe0;border:1px solid #e6db55;" class="fade"><p style="margin:8px;"><strong>'.__('Active: ', 'srel-l10n').'</strong>';
	else
		echo '<div><p><input class="button" type="submit" name="srel_pages" value="'.__('Activate', 'srel-l10n').'" /> ';
	echo __('Shutter on all image links on specific page(s).', 'srel-l10n')."</p></div>\n";

	if ( $opt == 'srel_lb' )
		echo '<div style="background-color:#ffffe0;border:1px solid #e6db55;" class="fade"><p style="margin:8px;"><strong>'.__('Active: ', 'srel-l10n').'</strong>';
	else
		echo '<div><p><input class="button" type="submit" name="srel_lb" value="'.__('Activate', 'srel-l10n').'" /> ';
	echo __('Shutter on all image links and use LightBox style (rel=&quot;lightbox[...]&quot;) activation and sets.', 'srel-l10n')."</p></div>\n"; ?>

	</div>
	</form>

	<div style="padding:0 12px 4px;border: 1px solid #dfdfdf;">
<?php
if ( $opt == 'srel_all' || $opt == 'auto_set' ) { ?>

	<form method="post" name="srel_excluded" id="srel_excluded" action="">
	<div>
<?php
	if ( $opt == 'srel_all' ) { ?>
	<p><strong><?php _e('Shutter is activated for all links pointing to an image.', 'srel-l10n'); ?></strong></p>
	<?php }
	else { ?><p><strong><?php _e('Shutter is activated for all links pointing to an image and will create different image set for each Post/Page.', 'srel-l10n'); ?></strong><br /><?php _e('This option is most suitable if you display several Posts on your home page and want to have different image set for each Post. It adds shutter\'s activation class at runtime and doesn\'t modify the html.', 'srel-l10n'); ?></p><?php }
?>
	<p><?php _e('Excluded Posts or Pages (by ID):', 'srel-l10n'); ?> <?php
	if ( is_array($excluded) && !empty($excluded) ) {
		foreach( $excluded as $excl ) { ?>
		  <span style="border: 1px solid #dfdfdf;padding:2px 4px;cursor:pointer;" onclick="document.forms.srel_excluded.srel_exclude.value = '<?php echo $excl; ?>'"><?php echo $excl; ?></span>
<?php   }
	} else { ?>
		<?php _e('[none]', 'srel-l10n'); ?>
<?php
	} ?>
	</p>

	<input type="text" name="srel_exclude" size="4" maxlength="4" tabindex="4" value="" />
	<input class="button" type="submit" name="srel_add_excluded" value="<?php _e('Add Excluded ID', 'srel-l10n'); ?>"
		onclick="if (form.srel_exclude.value == ''){alert('<?php echo js_escape(__("Please enter the Page/Post ID that you want to add to this list.", "srel-l10n")); ?>');return false;}" />

	<input class="button" type="submit" name="srel_rem_excluded" value="<?php _e('Remove Excluded ID', 'srel-l10n'); ?>"
	onclick="if (form.srel_exclude.value == ''){alert('<?php echo js_escape(__("Please enter the Page/Post ID that you want to remove from this list.", "srel-l10n")); ?>');return false;}" />

	<p style="color:#888;"><?php _e('Please enter the ID for the post/page you want to exclude. You can see it in your browser\'s status bar(at the bottom of the window) when hovering over the name at the <a href="edit-pages.php">Edit Pages</a> or the <a href="edit.php">Edit Posts</a> page.', 'srel-l10n'); ?></p>
	<?php wp_nonce_field( 'srel-save-options' ); ?>
	</div>
	</form>
<?php
	}

if ( $opt == 'srel_class' ) { ?>

	<div>
	<p><strong><?php _e('Shutter is activated for all links pointing to an image that have class = &quot;shutter&quot;, &quot;shutterset&quot; or &quot;shutterset_setname&quot;', 'srel-l10n'); ?></strong></p>

	<p><?php _e('Class = &quot;shutter&quot; will display a single image, class = &quot;shutterset&quot; will create a single set for all images and class=&quot;shutterset_setname&quot;, where setname is a short ASCII word and/or number, will create multiple sets on the same page.', 'srel-l10n'); ?></p>
	</div>
<?php
	}

if ( $opt == 'srel_pages' ) { ?>

	<form method="post" name="srel_included" id="srel_included" action="">
	<div>
	<p><strong><?php _e('Shutter is activated for the following Posts and Pages (by ID):', 'srel-l10n'); ?>
<?php
	if ( is_array($included) && !empty($included) ) {
		foreach( $included as $incl ) { ?>
			<span style="border: 1px solid #dfdfdf;padding:2px 4px;cursor:pointer;" onclick="document.forms.srel_included.srel_include.value = '<?php echo $incl; ?>'"><?php echo $incl; ?></span>
<?php   }
	} else { ?>
		<?php _e('[none]', 'srel-l10n'); ?>
<?php
	} ?>
	</strong></p>

	<input type="text" name="srel_include" size="4" maxlength="4" tabindex="4" value="" />
	<input class="button" type="submit" name="srel_add_included" value="<?php _e('Add ID', 'srel-l10n'); ?>"
		onclick="if (form.srel_include.value == ''){alert('<?php echo js_escape(__("Please enter the Page/Post ID that you want to add to this list.", "srel-l10n")); ?>');return false;}" />
	<input class="button" type="submit" name="srel_rem_included" value="<?php _e('Remove ID', 'srel-l10n'); ?>"
	onclick="if (form.srel_include.value == ''){alert('<?php echo js_escape(__("Please enter the Page/Post ID that you want to remove from this list.", "srel-l10n")); ?>');return false;}" />

	<p style="color:#888;"><?php _e('Please enter the ID for the post/page you want to exclude. You can see it in your browser\'s status bar(at the bottom of the window) when hovering over the name at the <a href="edit-pages.php">Manage Pages</a> or the <a href="edit.php">Manage Posts</a> page.', 'srel-l10n'); ?></p>
	<?php wp_nonce_field( 'srel-save-options' ); ?>
	</div>
	</form>
<?php
	}

	if ( $opt == 'srel_lb' ) { ?>
	<div>
	<p><strong><?php _e('Shutter uses Lightbox style activation.', 'srel-l10n'); ?></strong></p>

	<p><?php _e('Shutter is activated for all links pointing to an image, that have rel=&quot;lightbox&quot; or rel=&quot;lightbox[...]&quot;. To make sets of images, you will have to add rel=&quot;lightbox[abc]&quot;, where &quot;abc&quot; can be any short ASCII word and/or number.', 'srel-l10n'); ?></p>
	</div>
<?php
	}
?>
	</div>

	<form method="post" name="srel_saveoptform" id="srel_saveoptform" action="">
	<table class="widefat" style="padding:5px 12px;border:0;margin-top:20px;">
	<thead>
	<tr><th colspan="2" style="text-align:center;">
	<?php _e('Customization', 'srel-l10n'); ?>
	</th></tr>
	</thead>

	<tbody>
	<tr><td style="text-align:right;vertical-align:middle;">
	<label for="oneset"><?php _e('Make one set for all image links on the page that are not part of another set.', 'srel-l10n'); ?></label>
	</td><td style="vertical-align:middle;">
	<input type="checkbox" class="checkbox"  name="oneset" id="oneset" <?php if ($srel_options['oneset'] == 1) { echo ' checked="checked"'; } ?> />
	</td></tr>
	
	<tr><td style="width:50%;text-align:right;vertical-align:middle;">
	<label for="shcolor"><?php _e('Shutter color (default 000000):', 'srel-l10n'); ?> <br /><?php _e('Please enter valid HTML color codes, from 000000 to FFFFFF.', 'srel-l10n'); ?></label>
	</td><td style="vertical-align:middle;">
	<input type="text" name="shcolor" id="shcolor" size="6" maxlength="6" tabindex="" value="<?php echo $srel_options['shcolor']; ?>" />
	<input type="text" name="shcolor2" size="6" disabled style="padding:4px;border:1px solid #888;background-color:#<?php echo $srel_options['shcolor']; ?>;" />
	</td></tr>

	<tr><td style="text-align:right;vertical-align:middle;">
	<label for="opacity"><?php _e('Shutter opacity (default 80):', 'srel-l10n'); ?> <br /><?php _e('Enter a number between 1 (see-through) and 99 (solid color).', 'srel-l10n'); ?></label>
	</td><td style="vertical-align:middle;">
	<input type="text" name="opacity" id="opacity" size="6" maxlength="3" tabindex="" value="<?php echo $srel_options['opacity']; ?>" />
	<input type="text" name="opacity2" size="6" disabled style="padding:4px;border:1px solid #888;background-color:#<?php echo $srel_options['shcolor']; ?>;opacity:<?php echo ($srel_options['opacity']/100); ?>;filter:alpha(opacity=<?php echo $srel_options['opacity']; ?>);" />
	</td></tr>

	<tr><td style="text-align:right;vertical-align:middle;">
	<label for="capcolor"><?php _e('Caption text color (default FFFFFF):', 'srel-l10n'); ?></label>
	</td><td style="vertical-align:middle;">
	<input type="text" name="capcolor" id="capcolor" size="6" maxlength="6" tabindex="" value="<?php echo $srel_options['capcolor']; ?>" />
	<input type="text" name="capcolor2" size="6"  disabled style="padding:4px;border:1px solid #888;background-color:#<?php echo $srel_options['capcolor']; ?>;" />
	</td></tr>

	<tr><td style="text-align:right;vertical-align:middle;">
	<label for="menucolor"><?php _e('Menubar color (default 3E3E3E):', 'srel-l10n'); ?></label>
	</td><td style="vertical-align:middle;">
	<input type="text" name="menucolor" id="menucolor" size="6" maxlength="6" tabindex="" value="<?php echo $srel_options['menucolor']; ?>" />
	<input type="text" name="menucolor2" size="6" disabled style="padding:4px;border:1px solid #888;background-color:#<?php echo $srel_options['menucolor']; ?>;" />
	</td></tr>

	<tr><td style="text-align:right;vertical-align:middle;">
	<label for="imageCount"><?php _e('Show images count for sets (Image 1 of ...):', 'srel-l10n'); ?></label>
	</td><td style="vertical-align:middle;">
	<input type="checkbox" class="checkbox" name="imageCount" id="imageCount" <?php if ($srel_options['imageCount'] == 1) { echo ' checked="checked"'; } ?> />
	</td></tr>

	<tr><td style="text-align:right;vertical-align:middle;">
	<label for="countcolor"><?php _e('Images count color (default 999999):', 'srel-l10n'); ?></label>
	</td><td style="vertical-align:middle;">
	<input type="text" name="countcolor" id="countcolor" size="6" maxlength="6" tabindex="" value="<?php echo $srel_options['countcolor']; ?>" />
	<input type="text" name="countcolor2" size="6" disabled style="padding:4px;border:1px solid #888;background-color:#<?php echo $srel_options['countcolor']; ?>;" />
	</td></tr>

	<tr><td style="text-align:right;vertical-align:middle;">
	<label for="textBtns"><?php _e('Text buttons (instead of images):', 'srel-l10n'); ?></label>
	</td><td style="vertical-align:middle;">
	<input type="checkbox" class="checkbox"  name="textBtns" id="textBtns" <?php if ($srel_options['textBtns'] == 1) { echo ' checked="checked"'; } ?> />
	</td></tr>

	<tr><td style="text-align:right;vertical-align:middle;">
	<label for="btncolor"><?php _e('Text buttons color (default CCCCCC):', 'srel-l10n'); ?></label>
	</td><td style="vertical-align:middle;">
	<input type="text" name="btncolor" id="btncolor" size="6" maxlength="6" value="<?php echo $srel_options['btncolor']; ?>" />
	<input type="text" name="btncolor2" size="6" disabled style="padding:4px;border:1px solid #888;background-color:#<?php echo $srel_options['btncolor']; ?>;" />
	</td></tr>

	<tr><td style="text-align:right;vertical-align:middle;">
	<label for="startFull"><?php _e('Open the images in full size:', 'srel-l10n'); ?></label>
	</td><td style="vertical-align:middle;">
	<input type="checkbox" class="checkbox"  name="startFull" id="startFull" <?php if ($srel_options['startFull'] == 1) { echo ' checked="checked"'; } ?> />
	</td></tr>

	<tr><td style="text-align:right;vertical-align:middle;">
	<label for="headload"><?php _e('Alternate loading (select if Shutter does not start):', 'srel-l10n'); ?></label>
	</td><td style="vertical-align:middle;">
	<input type="checkbox" class="checkbox"  name="headload" id="headload" <?php if ($srel_options['headload'] == 1) { echo ' checked="checked"'; } ?> />
	</td></tr>
	</tbody>
	</table>

	<p><?php _e('To restore the defaults, delete the current value(s) and submit the form.', 'srel-l10n'); ?></p>
	<p class="submit">
	<input type="submit" name="srel_delete_options" value="<?php _e('Reset all options', 'srel-l10n'); ?>" />
	<input class="button-primary" type="submit" name="srel_saveopt" value="<?php _e('Save Options', 'srel-l10n'); ?>" />
	</p>
	<?php wp_nonce_field( 'srel-save-options' ); ?>
	</form>
	</div>

