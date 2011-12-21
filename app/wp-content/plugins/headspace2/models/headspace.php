<?php

/**
 * HeadSpace
 *
 * @package HeadSpace
 * @author John Godley
 * @copyright Copyright (C) John Godley
 **/

/*
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages (including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================ */

include (dirname (__FILE__).'/modules.php');
include (dirname (__FILE__).'/site.php');
include (dirname (__FILE__).'/inline_tags.php');

class HeadSpace2 extends HeadSpace_Plugin
{
	var $modules  = null;
	var $site     = null;
	var $disabled = false;
	
	function HeadSpace2() {
		$this->register_plugin( 'headspace', dirname ( __FILE__ ) );
		
		// Load active modules
		$this->modules = new HSM_ModuleManager ($this->get_active_modules ());
		$this->site    = new HS_SiteManager ($this->get_site_modules ());

		// Add our own filter
		$this->add_action ('wp_head');
		$this->add_action ('headspace_wp_head', 'wp_head');   // For custom themes
		$this->add_action ('login_head', 'wp_head');
		
		// 'plugins_loaded' seems to cause problem on non-english sites for 2.7
		$this->add_action ('init', 'plugins_loaded');
	}

	function get_simple_modules() {
		$options = $this->get_options ();
		return $options['simple_modules'];
	}
	
	function get_advanced_modules() {
		$options = $this->get_options ();
		return $options['advanced_modules'];
	}
	
	function get_site_modules() {
		$options = get_option ('headspace_options');
		if ($options === false)
			$options = array ();

		if (!isset ($options['site_modules']))
			$options['site_modules'] = array ();
		return $options['site_modules'];
	}
	
	function get_active_modules() {
		return array_merge ($this->get_simple_modules (), $this->get_advanced_modules ());
	}
	
	function get_options() {
		$options = get_option ('headspace_options');
		if ($options === false)
			$options = array ();

		if (!isset ($options['simple_modules']))
			$options['simple_modules'] = array ('page_title.php' => 'hsm_pagetitle', 'description.php' => 'hsm_description', 'tags.php' => 'hsm_tags');

		if (!isset ($options['advanced_modules']))
			$options['advanced_modules'] = array ('javascript.php' => 'hsm_javascript', 'stylesheet.php' => 'hsm_stylesheet');
		
		if (!isset ($options['inherit']))
			$options['inherit'] = true;
		return $options;
	}
	
	function get_types() {

		// Standard types
		$types =  array(
			'global'     => array (__ ('Global Settings', 'headspace'), __ ('applied to everything unless otherwise specified', 'headspace')),
			'home'       => array (__ ('Home Page', 'headspace'), __ ('applied to the home page (or blog page)', 'headspace')),
			'front'      => array (__ ('Front Page', 'headspace'), __('applied to front page (if you have set WordPress to use a static page)', 'headspace')),
			'taxonomy'   => array (__ ('Taxonomy Archives', 'headspace'), __ ('applied when viewing a taxonomy archive', 'headspace')));
		
		// get taxonomy types
		$all_taxonomies = get_object_taxonomies('post');

		foreach ($all_taxonomies as $taxonomy) {

			if ($taxonomy == 'post_tag' || $taxonomy == 'category')
				continue;

			$tax_detail = get_taxonomy($taxonomy);
			$types['taxonomy_'.$tax_detail->name] = array (__ ('Taxonomy Archives - '.$tax_detail->label, 'headspace'), __ ('applied when viewing a taxonomy archive for the '.$tax_detail->label.' taxonomy', 'headspace'));

		}

		$types = array_merge($types, array(
			'archive'    => array (__ ('Archives', 'headspace'), __ ('applied when viewing the archives', 'headspace')),
			'category'   => array (__ ('Categories', 'headspace'), __ ('applied to category pages without specific settings', 'headspace')),
			'post'       => array (__ ('Posts', 'headspace'), __ ('applied to posts without specific settings', 'headspace')),
			'page'       => array (__ ('Pages', 'headspace'), __ ('applied to pages without specific settings', 'headspace')),
			'author'     => array (__ ('Author Pages', 'headspace'), __ ('applied to author pages', 'headspace')),
			'search'     => array (__ ('Search Pages', 'headspace'), __ ('applied when viewing search results', 'headspace')),
			'404'        => array (__ ('404 Page', 'headspace'), __ ('applied when viewing a 404 error', 'headspace')),
			'tags'       => array (__ ('Tag Pages', 'headspace'), __ ('applied when viewing tag pages', 'headspace')),
			'attachment' => array (__ ('Attachment Pages'), __ ('applied when viewing an attachment', 'headspace')),
			'login'      => array (__ ('Login Pages', 'headspace'), __ ('applied when viewing login, logout, or registration pages', 'headspace')),
		));

		return $types;

	}
	
	function extract_module_settings($data, $area) {
		$data = stripslashes_deep ($data);
		
		$modules = $this->modules->get_restricted ($this->get_simple_modules (), array (), $area);
		$modules = array_merge ($modules, $this->modules->get_restricted ($this->get_advanced_modules (), array (), $area));

		$save = array ();
		if (count ($modules) > 0) {
			foreach ($modules AS $pos => $module)
				$save = array_merge ($save, $modules[$pos]->save ($data, $area));
		}

		return $save;
	}

	function get_current_settings($override = '') {
		global $post;

		if ($this->disabled == true)
			return array ();				// This is useful for when we call a filter to prevent infinite loops
		
		$meta = array ();	
		if ($override)
			$meta[] = $override;
		else if (is_admin ())
			$meta[] = $this->get_post_settings (isset( $_GET['post']) ? intval ($_GET['post']) : 0);
		else
		{
			if (!is_admin ())
				$meta[] = get_option ('headspace_global');   // We don't get this in admin mode as it will affect our settings

			// Decide what kind of page we're on
			// Note that on the home page we want headspace_home settings when outside the loop, but post settings inside
			if (is_single () || is_page () || ((is_front_page() || is_home() || is_archive() || is_search()) && in_the_loop()) ) {
				if (is_attachment ())
					$meta[] = get_option ('headspace_attachment');
				
				if (is_page ())
					$meta[] = get_option ('headspace_page');
				else
					$meta[] = get_option ('headspace_post');
					
				if (!empty ($post->ID))
					$meta[] = $this->get_post_settings ($post->ID);
			}
			else if (is_404 ())
				$meta[] = get_option ('headspace_404');
			else if (is_category ()) {
				$meta[] = get_option ('headspace_category');
				$meta[] = get_option ('headspace_cat_'.intval (get_query_var ('cat')));
			}
			else if (is_author ())
				$meta[] = get_option ('headspace_author');
			else if (is_home ())
				$meta[] = get_option ('headspace_home');
			else if (is_front_page ())
				$meta[] = get_option ('headspace_front');
			else if (is_search ())
				$meta[] = get_option ('headspace_search');
			else if (is_tag ())
				$meta[] = get_option ('headspace_tags');
			else if (is_archive ()) {
				if (is_tax())  {
					$taxonomy = get_query_var('taxonomy');
					$specific_taxonomy_settings = get_option ('headspace_taxonomy_'.$taxonomy);
					$generic_taxonomy_settings = get_option ('headspace_taxonomy');

					// Settings for a specific taxonomy override general ones
					foreach( (array)$specific_taxonomy_settings as $key => $value) {
						if (!empty($value))
							$generic_taxonomy_settings[$key] = $value;
					}

					$meta[] = $generic_taxonomy_settings;
				} else {
					$meta[] = get_option ('headspace_archive');
				}
			} else if (strpos ($_SERVER['REQUEST_URI'], 'wp-login.php') !== false)
				$meta[] = get_option ('headspace_login');
			else if (is_feed ()) {
				// Remove title from RSS
				if (is_array ($meta) && isset ($meta[0]) && isset ($meta[0]['page_title']))
					unset ($meta[0]['page_title']);
			}
		}

		$meta = array_filter ($meta);

		// Do we merge the settings?
		$options = $this->get_options ();
		if ($options['inherit'] !== true && count ($meta) > 1)
			$meta = array ($meta[count ($meta) - 1]);
		
		// Merge the settings together
		$merged = array ();
		foreach ($meta AS $item) {
			if ( !empty( $item ) && is_array( $item ) ) {
				foreach ($item AS $key => $value) {
					if (!empty ($value))
						$merged[$key] = $value;
				}
			}
		}

		$meta = $merged;
		if (!$override && !is_admin ()) {
			// Replace any inline tags
			if (count ($meta) > 0) {
				foreach ($meta AS $key => $value)
					$meta[$key] = HS_InlineTags::replace ($value, $post);
			}
			
			$meta = array_filter ($meta);
		}

		$this->meta = $meta;
		return $this->meta;
	}
	
	function get_post_settings( $id ) {
		$meta = array();
		
		if ( $id > 0 ) {
			$custom = get_post_custom( $id );

			if ( !empty( $custom ) > 0 && is_array( $custom ) ) {
				foreach ( $custom AS $key => $value ) {
					$var = substr( $key, 0, 10 );
					if ( $var == '_headspace' ) {
						$field = substr( $key, 11 );
						$meta[$field] = $value;
					}
				}
			
				// Flatten any arrays with one element
				foreach ( $meta AS $field => $value ) {
					if ( is_array( $value ) && count( $value ) == 1 )
						$meta[$field] = $value[0];
				}
			}
		}
		
		return $meta;
	}
	
	function save_post_settings( $postid, $settings ) {
		global $wpdb;

		// Try to find existing headspace meta for this post
		$existing = get_metadata( 'post', $postid );

		// Save each variable
		foreach ( $settings AS $var => $values ) {
			$field = '_headspace_'.$var;

			if ( !is_array( $values ) )
				$values = array( $values );

			if ( isset( $existing[$field] ) )
				delete_metadata( 'post', $postid, $field );

			foreach ( $values AS $value ) {
				add_metadata( 'post', $postid, $field, $value );
			}
		}
	}
	
	function reload(&$obj) {
		$headspace = HeadSpace2::get ();
		$obj->load ($headspace->get_current_settings ());
	}

	function wp_head() {
		global $headspace2;

		$modules = array_merge ($this->site->get_active (), $this->modules->get_active ($this->get_current_settings ()));

		echo '<!-- HeadSpace SEO '.$headspace2->version().' by John Godley - urbangiraffe.com -->'."\n";
		if ( count( $modules ) > 0 ) {
			foreach ( $modules AS $module ) {
				$module->head ();
			}
		}
		
		echo "<!-- HeadSpace -->\n";
	}
	
	function plugins_loaded() {
		$modules = array_merge ($this->site->get_active (), $this->modules->get_active ($this->get_current_settings ()));

		if (count ($modules) > 0) {
			foreach ($modules AS $module)
				$module->plugins_loaded ();
		}
	}

	function debug($text) {
		echo '<pre>';
		echo '<h4>HeadSpace Debug</h4>';
		echo $text;
		echo '</pre>';
	}
	
	function &get () {
    static $instance;

    if (!isset ($instance)) {
			$c = __CLASS__;
			$instance = new $c;
    }

    return $instance;
	}
}

// Cause the singleton to fire
HeadSpace2::get ();
