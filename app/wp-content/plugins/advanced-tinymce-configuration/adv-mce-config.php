<?php
/*
Plugin Name: Advanced TinyMCE Config
Plugin URI: http://www.laptoptips.ca/projects/advanced-tinymce-configuration/
Description: Set advanced options for TinyMCE, the visual editor in WordPress.
Version: 1.0
Author: Andrew Ozz
Author URI: http://www.laptoptips.ca/

Released under the GPL v.2, http://www.gnu.org/copyleft/gpl.html

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/

add_filter('tiny_mce_before_init', 'advmceconf_config_mce', 1111);
function advmceconf_config_mce($a) {
	$options = get_option('advmceconf_options');

	if ( empty($options) || !is_array($options) )
		return $a;

	return array_merge($a, $options);
}

add_action('admin_head-settings_page_advanced-tinymce-configuration/adv-mce-config', 'advmceconf_style');
function advmceconf_style() {
?>
<style type="text/css">
.advmceconf-table {
	table-layout: fixed;
	border-collapse: collapse;
	margin: 8px 0;
	width: 100%;
	clear: both;
}

.advmceconf-defaults {
	white-space: -moz-pre-wrap !important;
	word-wrap: break-word;
	white-space: pre-wrap;
}

.advmceconf-table td {
	padding: 7px 4px;
	line-height: 20px;
	vertical-align: top;
}

.advmceconf-defaults td {
	border-bottom: 1px solid #ddd;
}

.advmceconf-table th {
	font-weight: bold;
	text-shadow: rgba(255,255,255,1) 0 1px 0;
	padding: 10px 4px;
	border-bottom: 1px solid #ddd;
	text-align: left;
}

.advmceconf-table td.names input {
	width: 270px;
}

.advmceconf-table textarea {
	width: 98%;
	min-height: 80px;
}

.advmceconf-table th.names {
	width: 280px;
}

.advmceconf-defaults .names {
	text-align: right;
}

.advmceconf-table .sep {
	width: 10px;
	text-align: center;
}

.advmceconf-table .actions {
	width: 100px;
	text-align: center;
}

.advmceconf-table td.actions {
	vertical-align: middle;
}

.advmceconf-hilite td {
	background: #fee;
}
</style>
<?php
}

$advmceconf_show_defaults = array();
function advmceconf_show_defaults($a) {
	global $advmceconf_show_defaults;
	$advmceconf_show_defaults = $a;
	return array();
}

add_action( 'admin_menu', 'advmceconf_menu' );
function advmceconf_menu() {
    if ( function_exists('add_options_page') )
	   add_options_page( 'TinyMCE Config', 'TinyMCE Config', 'manage_options', __FILE__, 'advmceconf_admin' );
}

function advmceconf_admin() {
	if ( !current_user_can('manage_options') )
		wp_die('Access denied');

	global $advmceconf_show_defaults;
	$message = '';
	$options = get_option('advmceconf_options', array());

	if ( !empty($_POST['advmceconf_save']) ) {
		check_admin_referer('advmceconf-save-options');
		$old_options = $options;

		if ( !is_array($_POST['advmceconf_options']) )
			$_POST['advmceconf_options'] = array();

		if ( !empty($_POST['advmceconf-new']) && isset($_POST['advmceconf-new-val']) )
			$_POST['advmceconf_options'][$_POST['advmceconf-new']] = $_POST['advmceconf-new-val'];

		foreach ( $_POST['advmceconf_options'] as $key => $val ) {
			$key = preg_replace( '/[^a-z0-9_]+/i', '', $key );
			if ( empty($key) )
				continue;

			if ( isset($_POST[$key]) && empty($_POST[$key]) ) {
				unset($options[$key]);
				continue;
			}

			$val = stripslashes($val);
			if ( 'true' == $val )
				$options[$key] = true;
			elseif ( 'false' == $val )
				$options[$key] = false;
			else
				$options[$key] = $val;
		}

		if ( $options != $old_options ) {
			update_option('advmceconf_options', $options);
			$message = '<div class="updated fade"><p>' . __('Options saved.', 'advmceconf') . '</p></div>';
		}

	} ?>

<div class="wrap">
<?php screen_icon(); ?>
	<h2><?php _e('Advanced TinyMCE Settings', 'advmceconf'); ?></h2>
<?php if ( $message ) echo $message; ?>

	<p class="description"><?php _e('To add an option to TinyMCE type the name on the left and the value on the right. Do not type quotes around the option name or value. To remove an option you can delete its name and value. To add boolean values type the word <strong>true</strong> or <strong>false</strong>.', 'advmceconf'); ?></p>

	<p class="description"><?php _e('Several of the more commonly used settings are:', 'advmceconf'); ?></p>
	<ul class="ul-disc">
	<li><a href="http://tinymce.moxiecode.com/wiki.php/Configuration:theme_advanced_blockformats" target="_blank">theme_advanced_blockformats</a></li>
	<li><a href="http://tinymce.moxiecode.com/wiki.php/Configuration:theme_advanced_styles" target="_blank">theme_advanced_styles</a></li>
	<li><a href="http://tinymce.moxiecode.com/wiki.php/Configuration:theme_advanced_text_colors" target="_blank">theme_advanced_text_colors</a></li>
	<li><a href="http://tinymce.moxiecode.com/wiki.php/Configuration:theme_advanced_background_colors" target="_blank">theme_advanced_background_colors</a></li>
	<li><a href="http://tinymce.moxiecode.com/wiki.php/Configuration:invalid_elements" target="_blank">invalid_elements</a></li>
	<li><a href="http://tinymce.moxiecode.com/wiki.php/Configuration:extended_valid_elements" target="_blank">extended_valid_elements</a></li>
	</ul>

	<p class="description"><?php _e('You can also add settings for the default TinyMCE plugins. For example:', 'advmceconf'); ?>
	<a href="http://tinymce.moxiecode.com/wiki.php/Plugin:paste" target="_blank">paste_retain_style_properties</a></p>

	<p class="description"><?php _e('Description of all settings is available in the', 'advmceconf'); ?> <a href="http://tinymce.moxiecode.com/wiki.php/Configuration" target="_blank"><?php _e('TinyMCE documentation.', 'advmceconf'); ?></a></p>

	<table class="advmceconf-defaults advmceconf-table" id="showhide" style="display: none;">
	<thead><tr>
	<th class="names"><?php _e('Name', 'advmceconf'); ?></th>
	<th class="sep">&nbsp;</th>
	<th><?php _e('Value', 'advmceconf'); ?></th>
	<th class="actions">&nbsp;</th>
	</tr></thead>
	<tbody>
<?php

remove_filter('tiny_mce_before_init', 'advmceconf_config_mce');
add_filter('tiny_mce_before_init', 'advmceconf_show_defaults', 1001);
ob_start();
wp_tiny_mce();
ob_end_clean();

unset($GLOBALS['merged_filters']['tiny_mce_before_init']);

$n = 1;
foreach ( $advmceconf_show_defaults as $dfield => $dvalue ) {
	if ( is_bool($dvalue) )
		$dvalue = $dvalue ? 'true' : 'false';
?>

<tr>
<td id="n<?php echo $n; ?>" class="names"><?php echo $dfield; ?></td>
<td class="sep">:</td>
<td id="v<?php echo $n; ?>"><?php echo htmlspecialchars($dvalue, ENT_QUOTES); ?></td>
<td class="actions"><input type="button" class="button-secondary" onclick="advmceconfCopy(<?php echo $n; ?>)" value="<?php _e('Change', 'advmceconf'); ?>" /></td>
</tr>
<?php 
	$n++;
} ?>

</tbody>
</table>

<p>
	<button type="button" class="button-secondary" id="show" onclick="advmceconfShowhide();"><?php _e('Show the default TinyMCE settings', 'advmceconf'); ?></button>
	<button type="button" class="button-secondary" id="hide" onclick="advmceconfShowhide();" style="display: none;"><?php _e('Hide the default TinyMCE settings', 'advmceconf'); ?></button>
	</p>
	
<form method="post" action="" style="padding:10px 0">
	<table class="advmceconf-table" id="advmceconf-set">
	<thead><tr>
	<th class="names"><?php _e('Option name', 'advmceconf'); ?></th>
	<th><?php _e('Value', 'advmceconf'); ?></th>
	<th class="actions">&nbsp;</th>
	</tr></thead>
	<tbody>
	<?php

	foreach ( $options as $field => $value ) {
		$id = "advmceconf_option-{$field}";
		$name = "advmceconf_options[{$field}]";

		if ( is_bool($value) )
			$value = $value ? 'true' : 'false'; ?>

		<tr>
		<td class="names"><input type="text" name="<?php echo $field; ?>" id="<?php echo $field; ?>" value="<?php echo $field; ?>" /></td>
		<td><textarea name="<?php echo $name; ?>" id="<?php echo $id; ?>" spellcheck="false"><?php echo htmlspecialchars($value, ENT_NOQUOTES); ?></textarea></td>
		<td class="actions"><input type="button" class="button-secondary" onclick="advmceconfRemove('<?php echo $field; ?>')" value="<?php _e('Remove', 'advmceconf'); ?>" /></td>
		</tr>
<?php } ?>

		<tr>
		<td class="names"><input type="text" name="advmceconf-new" id="advmceconf-new" value="" /></td>
		<td><textarea name="advmceconf-new-val" id="advmceconf-new-val" spellcheck="false"></textarea></td>
		<td>&nbsp;</td>
		</tr>
	</tbody>
	</table>

	<p class="submit">
		<?php wp_nonce_field('advmceconf-save-options'); ?>
		<input type="submit" value="<?php _e('Save Changes', 'advmceconf'); ?>" class="button-primary" name="advmceconf_save" />
	</p>
</form>
</div>
	
<script type="text/javascript">
	function advmceconfCopy(id) {
		jQuery('#advmceconf-new').val( jQuery('#n'+id).text() );
		jQuery('#advmceconf-new-val').val( jQuery('#v'+id).text() );
		scrollTo(0,50000);
	}
	function advmceconfRemove(id) {
		jQuery('#'+id).val('');
		jQuery('#advmceconf_option-'+id).val('');
	}
	function advmceconfShowhide() {
		jQuery('#showhide, #show, #hide').toggle();
	}
	jQuery(document).ready(function($){
		var defaults = [];

		$('td.names', '#showhide').each(function(n, el){
			defaults.push( $(el).text() );
		});

		$('td.names', '#advmceconf-set').each(function(n, el){
			var text = $('input', el).val();

			if ( text && $.inArray(text, defaults) > -1 ) {
				$(el).parent().addClass('advmceconf-hilite');
				$('td.names:contains("' + text + '")', '#showhide').parent().addClass('advmceconf-hilite');
			}
		});
	});
</script>
<?php
}
