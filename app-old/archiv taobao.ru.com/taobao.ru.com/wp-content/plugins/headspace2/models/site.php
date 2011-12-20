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

class HS_SiteModule
{
	var $active = false;

	function HS_SiteModule() {}
	function id() { return strtolower (get_class ($this)); }
	function run() { }
	
	function config($options) { }
	function load($meta = '') { }
	function has_config() { return false; }
	function plugins_loaded() {}
	function is_active() { return $this->active; }
	
	function save_options($data) { return array (); }
	function edit_options() {}
	
	function head() {}
	
	
	function update($data) {
		$data = $this->save_options ($data);
		
		if (!empty ($data)) {
			$options = get_option ('headspace_options');
			
			$options['site'][$this->id ()] = $data;
			update_option ('headspace_options', $options);
			return true;
		}
		
		return false;
	}
}


class HS_SiteManager
{
	var $modules = array ();
	var $active  = array ();
	
	function HS_SiteManager($active) {
		// Load all available module files
		$available = get_declared_classes ();

		if ((is_admin () && isset($_GET['sub']) && isset($_GET['page']) && $_GET['page'] == 'headspace.php' && $_GET['sub'] == 'site') || (defined ('DOING_AJAX') && isset( $_REQUEST['action'] ) && in_array ($_REQUEST['action'], array ('hs_site_edit', 'hs_site_save', 'hs_site_load')))) {
			$files = glob (dirname (__FILE__).'/../modules/site/*.php');
			if (!empty ($files)) {
				foreach ($files AS $file)
					include_once ($file);
			}
		}
		else if (count ($active) > 0) {
			foreach ($active AS $file => $name) {
				if (file_exists (dirname (__FILE__)."/../modules/site/$file"))
					include_once (dirname (__FILE__)."/../modules/site/$file");
			}
		}
		
		$available = array_diff (get_declared_classes (), $available);
		
		if (count ($available) > 0) {
			$options = get_option ('headspace_options');
			foreach ($available AS $pos => $name) {
				$name = strtolower ($name);
				$module = new $name;
				if (isset ($options['site'][$name]))
					$module->load ($options['site'][$name]);

				if (in_array ($name, $active))
					$module->active = true;
					
				$this->modules[$name] = $module;
			}
			
			ksort ($this->modules);
		}
		
		// Run through active modules and start them
		if (count ($active) > 0) {
			foreach ($active AS $name) {
				if (isset ($this->modules[$name])) {
					$this->active[] = $this->modules[$name];
					$this->active[count ($this->active) - 1]->run ();
				}
			}
		}
	}
	
	function get($id) {
		if (isset ($this->modules[$id]))
			return $this->modules[$id];
		return false;
	}
	
	function get_active() {
		return $this->active;
	}
}