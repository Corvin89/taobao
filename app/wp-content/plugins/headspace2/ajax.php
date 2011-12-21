<?php

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

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

/**
 * HeadSpace AJAX
 *
 * @package HeadSpace
 * @author John Godley
 * @copyright Copyright (C) John Godley
 **/
class HeadspaceAjax extends HeadSpace_Plugin {
	function HeadspaceAjax() {
		$this->register_plugin( 'headspace', __FILE__ );

		add_action( 'init', array( &$this, 'init' ) );
	}
	
	function init() {
		if ( current_user_can( 'manage_options' ) ) {
			$this->register_ajax( 'hs_settings_edit' );
			$this->register_ajax( 'hs_settings_save' );
			$this->register_ajax( 'hs_settings_load' );
			
			$this->register_ajax( 'hs_module_edit' );
			$this->register_ajax( 'hs_module_load' );
			$this->register_ajax( 'hs_module_save' );
			$this->register_ajax( 'hs_module_order' );
			
			$this->register_ajax( 'hs_site_onoff' );
			$this->register_ajax( 'hs_site_edit' );
			$this->register_ajax( 'hs_site_load' );
			$this->register_ajax( 'hs_site_save' );
		}

		$this->register_ajax( 'hs_tag_update' );
		$this->register_ajax( 'hs_auto_tag' );
		$this->register_ajax( 'hs_auto_description' );
	}
	
	function obj_to_array( $items )	{
		$merged = array();
		if ( !empty( $items ) > 0 ) {
			foreach ( $items AS $key => $value ) {
				if ( !empty( $value ) )
					$merged[$key] = $value;
			}
		}
		
		return $merged;
	}
	
	function hs_settings_edit() {
		$id        = $_GET['page'];
		$headspace = HeadSpace2::get ();
		$types     = $headspace->get_types();
		
		if ( in_array( $id, array_keys( $types ) ) && check_ajax_referer( 'headspace-edit_setting_'.$id ) ) {
			$settings  = $this->obj_to_array( get_option( 'headspace_'.$id ) );

			$simple   = $headspace->modules->get_restricted( $headspace->get_simple_modules(), $settings, $id );
			$advanced = $headspace->modules->get_restricted( $headspace->get_advanced_modules(), $settings, $id );

			$this->render_admin( 'page-settings-item', array( 'type' => $id, 'name' => $types[$id][0], 'desc' => $types[$id][1], 'nolink' => true ) );
			$this->render_admin( 'page-settings-edit-ajax', array( 'simple' => $simple, 'advanced' => $advanced, 'type' => $id, 'area' => 'page' ) );
			
			die();
		}
	}
	
	function hs_settings_save() {
		$id        = $_POST['module'];
		$headspace = HeadSpace2::get ();

		if ( in_array( $id, array_keys( $headspace->get_types() ) ) && check_ajax_referer( 'headspace-page_setting_'.$id ) ) {
			$settings  = $headspace->extract_module_settings( $_POST, $id );

			update_option( 'headspace_'.$id, $settings );

			$this->hs_settings_load();
		}
	}
	
	function hs_settings_load() {
		$id        = $_POST['module'];
		$headspace = HeadSpace2::get ();
		$types     = $headspace->get_types();
		
		if ( in_array( $id, array_keys( $types ) ) && check_ajax_referer( 'headspace-page_setting_'.$id ) ) {
			$settings = get_option( 'headspace_'.$id );

			$this->render_admin('page-settings-item', array( 'type' => $id, 'name' => $types[$id][0], 'desc' => $types[$id][1], 'nolink' => false ) );
			die();
		}
	}
	
	function hs_module_edit() {
		if ( check_ajax_referer( 'headspace-module_'.$_GET['module'] ) ) {
			$headspace = HeadSpace2::get ();
			$module    = $headspace->modules->get( $_GET['module'] );
		
			if ( $module )
				$this->render_admin( 'page-module-edit', array( 'module' => $module, 'id' => $_GET['module'] ) );
				
			die();
		}
	}
	
	function hs_module_save() {
		if ( check_ajax_referer( 'headspace-module_save_'.$_POST['module'] ) ) {
			$headspace = HeadSpace2::get ();
			$module    = $headspace->modules->get( $_POST['module'] );

			if ( $module ) {
				$module->update( $_POST );
				$this->render_admin( 'page-module-item', array( 'module' => $module ) );
				die();
			}
		}
	}
	
	function hs_module_load()	{
		if ( check_ajax_referer( 'headspace-module_'.$_GET['module'] ) ) {
			$headspace = HeadSpace2::get ();
			$module    = $headspace->modules->get( $_GET['module'] );

			if ( $module )
				$this->render_admin( 'page-module-item', array( 'module' => $module ) );
				
			die();
		}
	}

	function hs_module_order() {
		if (check_ajax_referer( 'headspace-save_order' ) ) {
			parse_str( $_POST['simple'], $simple );
			parse_str( $_POST['advanced'], $advanced );

			global $headspace2;
			$options = $headspace2->get_options ();

			$options['simple_modules']   = $simple['id_hsm'];
			$options['advanced_modules'] = $advanced['id_hsm'];

			if ( count( $options['simple_modules'] ) > 0 ) {
				foreach ( $options['simple_modules'] AS $name ) {
					$name = 'hsm_'.str_replace( '-', '_', strtolower( $name ) );
					$module = new $name;
					$newmod[$module->file()] = $name;
				}

				$options['simple_modules'] = $newmod;
			}
			else
				$options['simple_modules'] = array ();

			if ( count( $options['advanced_modules'] ) > 0 ) {
				$newmod = array ();
				
				foreach ( $options['advanced_modules'] AS $name ) {
					$name = 'hsm_'.str_replace( '-', '_', strtolower( $name ) );
					$module = new $name;
					$newmod[$module->file()] = $name;
				}

				$options['advanced_modules'] = $newmod;
			}
			else
				$options['advanced_modules'] = array();

			update_option( 'headspace_options', $options );
		}
	}
	
	function hs_site_edit() {
		if ( check_ajax_referer( 'headspace-site_module' ) ) {
			$headspace = HeadSpace2::get ();
			$module    = $headspace->site->get( $_GET['module'] );

			if ( $module )
				$this->render_admin( 'site-module-edit', array( 'module' => $module, 'id' => $_GET['module'] ) );
				
			die();
		}
	}
	
	function hs_site_load() {
		if ( check_ajax_referer( 'headspace-site_module' ) ) {
			$headspace = HeadSpace2::get ();
			$module    = $headspace->site->get( $_GET['module'] );

			if ($module)
				$this->render_admin( 'site-module-item', array( 'module' => $module ) );
				
			die();
		}
	}
	
	function hs_site_save() {
		if ( check_ajax_referer( 'headspace-site_save_'.$_POST['module'] ) ) {
			$headspace = HeadSpace2::get ();
			$module    = $headspace->site->get( $_POST['module'] );

			if ( $module ) {
				$module->update( stripslashes_deep( $_POST ) );
				$this->render_admin( 'site-module-item', array( 'module' => $module ) );
			}

			die();
		}
	}
	
	function hs_site_onoff() {
		if ( check_ajax_referer( 'headspace-site_module' ) ) {
			$options = get_option( 'headspace_options' );
			if ( $options === false )
				$options = array();

			$id = $_POST['module'];
			
			if ( isset( $_POST['onoff'] ) && !in_array( $id, $options['site_modules'] ) && isset( $_POST['file'] ) && $_POST['file'] )
				$options['site_modules'][$_POST['file']] = $id;
			elseif ( !isset( $_POST['onoff'] ) && in_array( $id, $options['site_modules'] ) )
				unset( $options['site_modules'][$_POST['file']] );

			if ( count( $options['site_modules'] ) > 0 ) {
				foreach ( $options['site_modules'] AS $key => $value ) {
					if ( $key == '' )
						unset( $options['site_modules'][$key] );
				}
			}

			update_option( 'headspace_options', array_filter( $options ) );
		}
	}

	function hs_auto_description() {
		if ( check_ajax_referer( 'headspace-autodescription' ) ) {
			$excerpt = '';

			if ( isset( $_GET['post'] ) && current_user_can( 'edit_post', intval( $_GET['post'] ) ) ) {
				$id   = intval( $_GET['post'] );
				$post = get_post( $id );

				$excerpt = $post->post_content;
				if ( $post->post_excerpt )
					$excerpt = $post->post_excerpt;
			}
			else
				$excerpt = $_POST['content'];

			// Remove any [tags]
			$excerpt = preg_replace( '/\[(.*?)\]/', '', $excerpt );
			$excerpt = strip_tags( $excerpt );
			$excerpt = trim( $excerpt );

			// Extract 1st paragraph first blank line
			if ( function_exists( 'mb_strpos' ) ) {
				$pos = mb_strpos( $excerpt, '.' );
				
				if ( $pos !== false )
					$excerpt = mb_substr( $excerpt, 0, $pos + 1 );
			}
			else {
				$pos = strpos( $excerpt, '.' );
				
				if ($pos !== false)
					$excerpt = substr( $excerpt, 0, $pos + 1 );
			}

			// Replace all returns and HTML
			$excerpt = str_replace( "\r", '', $excerpt );
			$excerpt = str_replace( "\n", '', $excerpt );
		
			// Restrict it to HS description length setting
			if ( function_exists( 'mb_substr' ) )
				$excerpt = mb_substr( $excerpt, 0, 500 );
			else
				$excerpt = substr( $excerpt, 0, 500 );
			
			$excerpt = preg_replace( '/\s+/', ' ', $excerpt );
			echo $excerpt;
			die();
		}
	}
	
	function hs_tag_update() {
		$headspace = HeadSpace2::get ();
		$id        = intval( $_GET['id'] );
		
		if ( check_ajax_referer( 'headspace-tags' ) && current_user_can( 'edit_post', $id ) ) {
			$tags = $headspace->modules->get( 'hsm_tags' );
			$tags->load( $headspace->get_post_settings( $id ) );
		
			$tags->suggestions( $id, $_POST['content'], $_GET['type'] );
			die();
		}
	}

	function hs_auto_tag() {
		$id = intval( $_GET['id'] );

		if ( current_user_can( 'edit_post', $id ) && check_ajax_referer( 'headspace-auto_tag_'.$id ) ) {
			$headspace = HeadSpace2::get();
			$settings  = $headspace->get_post_settings( $id );
		
			$tags = $headspace->modules->get( 'hsm_tags' );
			$tags->load( $settings );

			include (ABSPATH.'wp-admin/admin-functions.php');
	
			$post = get_post( $id );
		
			$suggestions = $tags->get_suggestions( $post->post_content.' '.$post->post_title );
	 		echo HeadSpace_Plugin::specialchars( implode( ', ', $suggestions ) );
			die();
		}
	}
}

