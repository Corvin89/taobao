<?php
/*
Plugin Name: Shutter Reloaded
Plugin URI: http://www.laptoptips.ca/projects/wp-shutter-reloaded/
Description: Darkens the current page and displays an image on top like Lightbox, Thickbox, etc. However this script is a lot smaller and faster.
Version: 2.4.1
Author: Andrew Ozz
Author URI: http://www.laptoptips.ca/

Acknowledgement: some ideas from: Shutter by Andrew Sutherland - http://code.jalenack.com, WordPress - http://wordpress.org, Lightbox by Lokesh Dhakar - http://www.huddletogether.com, IE6 css position:fixed ideas from gunlaug.no and quirksmode.org, the icons are from Crystal Project Icons, Everaldo Coelho, http://www.everaldo.com

Released under the GPL version 2 or newer, http://www.gnu.org/copyleft/gpl.html

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
*/

$srel_autoset = false;
$srel_load_txtdomain = true;

function srel_txtdomain() {
	global $srel_load_txtdomain;

	if ( $srel_load_txtdomain ) {
		if ( defined('WP_PLUGIN_DIR') )
			load_plugin_textdomain('srel-l10n', '', 'shutter-reloaded/languages');
		else
			load_plugin_textdomain('srel-l10n', ABSPATH . '/' . PLUGINDIR . '/shutter-reloaded/languages');

		$srel_load_txtdomain = false;
	}
}

function srel_makeshutter() {
	global $post, $srel_autoset, $addshutter;

	$url = defined('WP_PLUGIN_URL') ? WP_PLUGIN_URL . '/shutter-reloaded' : get_bloginfo('wpurl') . '/wp-content/plugins/shutter-reloaded';

	$options = get_option( 'srel_options', array() );
	$srel_main = get_option( 'srel_main', '' );
	$srel_included = get_option( 'srel_included', array() );
	$srel_excluded = get_option( 'srel_excluded', array() );

	srel_txtdomain();

	$addshutter = false;
	switch( $srel_main ) {
		case 'srel_pages' :
			if ( in_array($post->ID, $srel_included) )
				$addshutter = 'shutterReloaded.init();';
			break;

		case 'auto_set' :
			if ( ! in_array($post->ID, $srel_excluded) ) {
				$addshutter = "shutterReloaded.init('sh');";
				$srel_autoset = true;
			}
			break;

		case 'srel_class' :
			$addshutter = "shutterReloaded.init('sh');";
			break;

		case 'srel_lb' :
			$addshutter = "shutterReloaded.init('lb');";
			break;

		default :
			if ( ! in_array($post->ID, $srel_excluded) )
				$addshutter = 'shutterReloaded.init();';
	}

	if ( $addshutter ) {
?>
<link rel="stylesheet" href="<?php echo $url; ?>/shutter-reloaded.css?ver=2.4" type="text/css" media="screen" />
<?php
		if ( !empty($options['custom']) ) {
			$css = '';
			if ( $options['btncolor'] != 'cccccc' )
				$css .= "div#shNavBar a {color: #" . $options['btncolor'] . ";}\n";
			if ( $options['menucolor'] != '3e3e3e' )
				$css .= "div#shNavBar {background-color:#" . $options['menucolor'] . ";}\n";
			if ( $options['countcolor'] != '999999' )
				$css .= "div#shNavBar {color:#" . $options['countcolor'] . ";}\n";
			if ( $options['shcolor'] != '000000' || $options['opacity'] != '80' )
				$css .= "div#shShutter{background-color:#" . $options['shcolor'] . ";opacity:" . ($options['opacity']/100) . ";filter:alpha(opacity=" . $options['opacity'] . ");}\n";
			if ( $options['capcolor'] != 'ffffff' )
				$css .= "div#shDisplay div#shTitle {color:#" . $options['capcolor'] . ";}\n";

			echo "<style type='text/css'>\n$css</style>\n";
		}

		if ( !empty($options['headload']) )
			srel_addjs(true);
		else
			add_action('get_footer', 'srel_addjs', 99);
	}
}
add_action('wp_head', 'srel_makeshutter');

function srel_addjs($head = false) {
	global $addshutter;

	$options = get_option( 'srel_options', array() );
	$url = defined('WP_PLUGIN_URL') ? WP_PLUGIN_URL . '/shutter-reloaded' : get_bloginfo('wpurl') . '/wp-content/plugins/shutter-reloaded';
?>
<script type="text/javascript">
//<![CDATA[
shutterSettings = {
	imgDir : '<?php echo $url; ?>/menu/',
<?php if ( !empty($options['imageCount']) ) { ?>
	imageCount : 1,
<?php }
	if ( !empty($options['startFull']) ) { ?>
	FS : 1,
<?php }
	if ( !empty($options['textBtns']) ) { ?>
	textBtns : 1,
<?php }
	if ( !empty($options['oneset']) ) { ?>
	oneSet : 1,
<?php }
	echo "\t" . 'L10n : ["'.js_escape(__("Previous", "srel-l10n")).'","'. js_escape(__("Next", "srel-l10n")).'","'. js_escape(__("Close", "srel-l10n")).'","'. js_escape(__("Full Size", "srel-l10n")).'","'. js_escape(__("Fit to Screen", "srel-l10n")).'","'. js_escape(__("Image", "srel-l10n")).'","'. js_escape(__("of", "srel-l10n")).'","'. js_escape(__("Loading...", "srel-l10n")).'"]'."\n}\n"; ?>
//]]>
</script>
<script src="<?php echo $url; ?>/shutter-reloaded.js?ver=2.4" type="text/javascript"></script>
<script type="text/javascript">
<?php echo $head ? 'try{shutterAddLoad( function(){' . $addshutter . '} );}catch(e){}' : 'try{' . $addshutter . '}catch(e){}'; ?>
</script>
<?php
}

function srel_auto_set($content) {
	global $srel_autoset;

	if ( $srel_autoset )
		return preg_replace_callback('/<a ([^>]+)>/i', 'srel_callback', $content);

	return $content;
}
add_filter('the_content', 'srel_auto_set', 65 );

function srel_callback($a) {
	global $post;
	$str = $a[1];

	if ( preg_match('/href=[\'"][^"\']+\.(?:gif|jpeg|jpg|png)/i', $str) ) {
		if ( false !== strpos(strtolower($str), 'class=') )
			return '<a ' . preg_replace('/(class=[\'"])/i', '$1shutterset_' . $post->ID . ' ', $str) . '>';
		else
			return '<a class="shutterset_' . $post->ID . '" ' . $str . '>';
	}
	return $a[0];
}

function srel_activate() {
	if ( false === get_option('srel_options') )
		update_option('srel_options', array());

	if ( false === get_option('srel_main') )
		update_option('srel_main', '');

	if ( false === get_option('srel_included') )
		update_option('srel_included', array());

	if ( false === get_option('srel_excluded') )
		update_option('srel_excluded', array());
}
add_action('activate_shutter-reloaded/shutter-reloaded.php', 'srel_activate');

function srel_optpage() {
	define('SREL_SETTINGS', true);
	include_once('admin-page.php');
}

function srel_addmenu() {
	if ( function_exists('add_theme_page') ) {
		srel_txtdomain();
		add_theme_page(__('Shutter Reloaded', 'srel-l10n'), __('Shutter Reloaded', 'srel-l10n'), 9,  'shutter-reloaded', 'srel_optpage');
	}
}
add_action('admin_menu', 'srel_addmenu');
