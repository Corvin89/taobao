<?php
/* ======================================================================================================
	Plugin Name: Web Directory WordPress plugin
	Plugin URI: http://codestuff.com/projects/web-directory-wordpress-plugin
	Description: A web directory for your blog that uses WordPress bookmarks.
	Version: 2.6.5
	Author: Gerry Ilagan
	Author URI: http://www.codestuff.com

=========================================================================================================
2.0.2 - 2009-04-16 - created as a plugin
2.5.0 - 2009-05-17 - finished basic functioning web directory
2.5.5 - 2009-05-29 - fix permalink issues
2.6.0 - 2009-06-22 - automatically use a custom style sheet in the site's default theme dir
2.6.1 - 2009-07-17 - added option in manage directories to enable/disable display of
					"Websites / Blogs under" prefix label
2.6.4 - 2010-02-15 - fixed permalinks to work for blogs under a subdirectory
2.6.5 - 2010-05-26 - fixed linkcat validation issue for submission form

=========================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to,
the implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no
event shall the copyright owner or contributors be liable for any direct, indirect, incidental, special,
exemplary, or consequential damages (including, but not limited to, procurement of substitute goods or
services; loss of use, data, or profits; or business interruption) however caused and on any theory of
liability, whether in contract, strict liability, or tort (including negligence or otherwise) arising
in any way out of the use of this software, even if advised of the possibility of such damage.

For full license details see license.txt
====================================================================================================== */

define( _KIH_WEBDIR_, true );

load_plugin_textdomain('kih-web-directory', 'kih-web-directory');

include_once('kih-wp-links.php');

class _KIH_WEB_DIRECTORY {

	var $optname;
	var $options = array();
	var $plugin = array();

	function _KIH_WEB_DIRECTORY( $optvar='kih_webdir' ) {
		$this->optname = $optvar;
		$this->load_options( $this->optname );
		$this->plugin['version'] = 20602;
		$this->plugin['name']    = 'Web Directory WordPress Plugin';
		$this->plugin['domain']  = 'kih-web-directory';
	}

	/**
	 * Return the version of this plugin
	 *
	 * @return string
	 */
	function version() { return $this->plugin['version']; }

	/**
	 * Return the domain of this plugin
	 *
	 * @return string
	 */
	function domain() { return $this->plugin['domain']; }

	/*
	 * Return the name of this plugin
	 *
	 * @return string
	 */
	function name() { return $this->plugin['name']; }

	/**
	 * Create the admin page of this plugin.
	 */
	function add_admin() {

        // TODO: Add capabilities check
		if ( $this->get_optdb_version() != $this->version() )
			$this->upgrade();

        // Create a submenu under Links:
	    add_links_page( __('Manage Web Directory'), __('Manage Directory'), 8,
	    				'kih-web-directory', array($this, 'admin') );
	}

	function admin() {

        if (isset($_POST['action']) && $_POST['action'] == 'save'
        		// TODO: Add capabilities check
        		) {
    		check_admin_referer('kih-webdir-opts');

    		$this->update_option( 'pageid', strip_tags($_POST['kih_webdir_page']) );
    		$this->update_option( 'metadesc', $_POST['kih_webdir_desc'] );
    		$this->update_option( 'before_cattitle',
    			($_POST['kih_catname_prefix'] ? true : false) );
    		$this->update_option( 'submiturl', strip_tags($_POST['kih_submit_url']) );
    		$this->update_option( 'linksperpage', intval($_POST['kih_links_per_page']) );

			$page = get_page( intval($_POST['kih_webdir_page']) );
    		$this->update_option( 'pagetitle', strip_tags($page->post_title) );
    		$this->update_option( 'pageslug', strip_tags($page->post_name) );

    		$this->save_options( $this->options );

    		// display update message
    		echo "<div class='updated fade'><p>" . __('Options updated.') . "</p></div>";
        }

	?>
	<div class="wrap">
		<h2><?php _e('Manage Web Directory'); ?></h2>

		<form method="post">
		<?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('kih-webdir-opts'); ?>

		<table class="form-table">
		<tr valign="top">
			<th scope="row" style="width:30%;"><label for="kih_webdir_page"
			><?php _e('Displayed in')?></label>
			</th>
			<td>
			<?php printf( __('Page Title: %s'),
						wp_dropdown_pages("name=kih_webdir_page&echo=0&show_option_none=" .
						__('- Select page -') . "&selected=" .
						$this->get_option('pageid'))); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="kih_webdir_desc"
			><?php _e('Description meta tag')?></label></th>
			<td>
			<textarea name="kih_webdir_desc" id="kih_webdir_desc" cols="32" rows="3"
			><?php echo $this->get_option('metadesc'); ?></textarea>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="kih_catname_prefix"
			><?php _e('Enable "Websites/Blogs under" prefix label for categories')?></label></th>
			<td style="padding-top:10px;">
			<input
			type="checkbox" name="kih_catname_prefix" id="kih_catname_prefix"
			<?php if ($this->get_option('before_cattitle')) echo 'checked="checked"'; ?> />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="kih_links_per_page"
			><?php _e('The number of links / bookmarks to display in a category per page')?></label></th>
			<td>
			<input type="text" name="kih_links_per_page" id="kih_links_per_page"
			size="4"  maxlength="3"
			value="<?php echo $this->get_option('linksperpage'); ?>"></input>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="kih_submit_url"
			><?php _e('The URL of your Submission Page')?></label></th>
			<td>
			<input type="text" name="kih_submit_url" id="kih_submit_url" size="24"  maxlength="128"
			value="<?php echo $this->get_option('submiturl'); ?>"></input>
			</td>
		</tr>
		</table>

		<p class="submit">
			<input type="submit" name="Submit" class="button-primary"
				value="<?php _e('Save Changes') ?>" />
         	<input type="hidden" name="action" id="action" value="save" />
		</p>
		</form>

	</div>
	<?php
	}

	function upgrade() {

		$optdb_version = $this->get_optdb_version();
		$optvar = array();

		switch ( $optdb_version ) {
			case 20601 :
	    		$kih_webdir_page = get_option( 'kih_webdir_page' );
				$optvar['pageid'] = $kih_webdir_page;

	    		$kih_webdir_pagetitle = get_option( 'kih_webdir_pagetitle' );
				$optvar['pagetitle'] = $kih_webdir_pagetitle;

	    		$kih_webdir_pageslug = get_option( 'kih_webdir_pageslug' );
				$optvar['pageslug'] = $kih_webdir_pageslug;

	    		$kih_webdir_desc = get_option( 'kih_webdir_desc' );
				$optvar['metadesc'] = $kih_webdir_desc;

				$kih_catname_prefix = get_option( 'kih_catname_prefix' );
				$optvar['before_cattitle'] = ($kih_catname_prefix?true:false);

				$this->save_options( $optvar );
				delete_option('kih_webdir_page');
				delete_option('kih_webdir_pagetitle');
				delete_option('kih_webdir_pageslug');
				delete_option('kih_webdir_desc');
				delete_option('kih_catname_prefix');

    			echo "<div class='updated fade'><p>" .
    					__('Web Directory Plugin options data upgraded to ') .
    					$this->version() . "</p></div>";

    			break;
			default :
				break;
		}
	}

	function get_optdb_version() {
		$ver261 = get_option('kih_webdir_page');
		if ( ! empty( $ver261 ) ) {
			return 20601;
		} else {
			$opts = get_option( $this->optname );
			$ver = $opts['plugin']['version'];
		}
	}

	function load_options( $optvar='kih_webdir' ) {
		$this->options = get_option( $optvar );
	}

	function save_options( &$newvalue, $optvar='kih_webdir' ) {
		$newvalue['plugin']['name'] = $this->name();
		$newvalue['plugin']['version'] = $this->version();
		$newvalue['plugin']['domain'] = $this->domain();
		update_option( $optvar, $newvalue );
		$this->load_options( $optvar );
	}

	function get_option( $opt ) {

		$webdir_opts = $this->options;

		switch ( $opt ) {
			case 'before_cattitle'	:
			case 'pagetitle' 		:
			case 'pageslug'			:
			case 'metadesc'			:
			case 'pageid' 			:
			case 'submiturl'		:
			case 'linksperpage'		:
				return ( empty($webdir_opts[ $opt ])? '' : $webdir_opts[ $opt ] );
			default :
				return null;
		}
	}

	function update_option( $var, $val ) {

		$webdir_opts = $this->options;

		switch ( $var ) {
			case 'before_cattitle'	:
			case 'metadesc' 		:
			case 'pageslug'			:
			case 'pagetitle'		:
			case 'pageid' 			:
			case 'submiturl'		:
			case 'linksperpage'		:
				$webdir_opts[ $var ] = $val;
				break;
			default :
				break;
		}

		return $this->save_options( $webdir_opts );
	}

	function include_css() {
		$css = $this->get_css_url('kih-web-directory.css');

		if (empty($css)) return;

		if ( !is_page( $this->get_option('pageslug')) ) return;

		$styletag = '<link rel="stylesheet" href="%s" type="text/css" media="screen" />' . "\n";

		printf( $styletag, $css );

		return;
	}

	function get_css_url( $basefilename, $subdir='/wp-content/plugins/kih-web-directory/' ) {

		if ( empty($basefilename) ) return '';

		if (is_callable(array('kih_file','url')) ) {
			$css = kih_file::url( __FILE__, $basefilename, 'css/' );
			if ( !empty($css) ) return $css;
		}

		if ( file_exists(get_template_directory() . '/' . $basefilename) )
			return get_bloginfo('template_url') . '/' . $basefilename;
		else
			return get_bloginfo('url') . $subdir . $basefilename;

		return '';
	}

}

$kih_webdir = new _KIH_WEB_DIRECTORY();

$kih_webdir_options = $kih_webdir->options;

if (empty($kih_webdir_options['metadesc']))
	$kih_webdir_options['metadesc'] = 'A web directory of web sites and blogs compiled by ' .
										get_bloginfo('name');

$kih_links = new _KIH_WP_LINKS( $kih_webdir_options );

$wpcf7_linkcat = new _KIH_WPCF7_LINKCAT( $kih_links );

if ( function_exists('yoast_breadcrumb') ) {
	add_filter( 'wp_breadcrumb', array( $kih_links, 'filter_breadcrumb'), 10, 2 );
}

add_action( 'wp_head', array($kih_webdir,'include_css'));

if (is_admin())	add_action('admin_menu', array($kih_webdir, 'add_admin'));
?>