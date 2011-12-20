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

class HS_Upgrade
{
	// Upgrade options
	function upgrade($currentversion, $desiredversion) {
		global $wpdb;

		// From HeadSpace 2.X
		if ($currentversion == false) {
			// Convert very old post meta data
			$wpdb->query ("UPDATE {$wpdb->postmeta} SET meta_key='_headspace_stylesheets' WHERE meta_key='head_style'");
			$wpdb->query ("UPDATE {$wpdb->postmeta} SET meta_key='_headspace_scripts' WHERE meta_key='head_script'");
			$wpdb->query ("UPDATE {$wpdb->postmeta} SET meta_key='_headspace_keywords' WHERE meta_key='head_keywords'");
			$wpdb->query ("UPDATE {$wpdb->postmeta} SET meta_key='_headspace_description' WHERE meta_key='head_description'");
			$wpdb->query ("UPDATE {$wpdb->postmeta} SET meta_key='_headspace_raw' WHERE meta_key='head_raw'");

			delete_option ('headspace2');
		}
		else if ($currentversion == 1) {
			// Convert 3.1 to 3.2
			$options = get_option ('headspace_options');
			
			$main = array
			(
				'inherit' => $options['inherit'] == 'true' ? true : false,
				'updates' => $options['updates'] == 'true' ? true : false,
			);
			
			update_option ('headspace_options', $main);
			
			// Copy all keywords into dictionary, along with hotwords
			$hot = get_option ('headspace_keywords');
			
			$rows = $wpdb->get_results ("SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key='_headspace_keywords'");
			if ($rows) {
				foreach ($rows AS $row)
					$hot .= $row->meta_value.',';
			}
			
			update_option ('headspace_dictionary', HS_Upgrade::normalize_tags ($hot));
			delete_option ('headspace_keywords');
		}
		
		if ($currentversion < 8) {
			//
			$available = get_declared_classes ();
			$files = glob (dirname (__FILE__).'/../modules/page/*.php');
			if (!empty ($files)) {
				foreach ($files AS $file)
					include_once ($file);
			}

			$available = array_diff (get_declared_classes (), $available);
			
			$options = get_option ('headspace_options');
			if (count ($options['advanced_modules']) > 0) {
				foreach ($options['advanced_modules'] AS $name) {
					$module = new $name;
					$newadvanced[$module->file ()] = $name;
				}
			
				$options['advanced_modules'] = $newadvanced;
			}
			
			if (count ($options['simple_modules']) > 0) {
				$newsimple = array ();
				foreach ($options['simple_modules'] AS $name) {
					$module = new $name;
					$newsimple[$module->file ()] = $name;
				}
			
				$options['simple_modules']   = $newsimple;
			}
			
			if (count ($options['site_modules']) > 0) {
				$newsimple = array ();
				foreach ($options['site_modules'] AS $name) {
					if (class_exists ($name)) {
						$module = new $name;
						$newsimple[$module->file ()] = $name;
					}
				}
			
				$options['site_modules'] = $newsimple;
			}

			update_option ('headspace_options', $options);
		}
			
		if ($currentversion < 10) {
			// Copy posts details to page details
			update_option ('headspace_page', get_option ('headspace_post'));
		}
		
		update_option ('headspace_version', $desiredversion);
	}
	
	function normalize_tags($words, $order = true) {
		$list = explode (',', trim (str_replace (',,', '', $words), ','));
		if (count ($list) > 0) {
			foreach ($list AS $pos => $item) {
				$list[$pos] = trim ($item);
				
				if (function_exists ('mb_strtolower'))
					$list[$pos] = mb_strtolower ($list[$pos], get_option ('blog_charset'));
				else
					$list[$pos] = strtolower ($list[$pos]);
			}

			$list = array_unique ($list);
			if ($order)
				sort ($list);
				
			return implode (',', $list);
		}
		
		return $words;
	}
	
	function remove($plugin) {
		global $wpdb;
		
		delete_option ('headspace_page_plugins');
		delete_option ('headspace_page_themes');
		
		$wpdb->query ("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '%_headspace_%'");
		$wpdb->query ("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%headspace_%'");
		
		// Deactivate the plugin
		$current = get_option('active_plugins');
		array_splice ($current, array_search (basename (dirname ($plugin)).'/'.basename ($plugin), $current), 1 );
		update_option('active_plugins', $current);
	}
}

?>