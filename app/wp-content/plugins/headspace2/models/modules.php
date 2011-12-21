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

class HSM_Module
{
	var $headspace = null;

	function HSM_Module($options = array ()) {}
	function id() { return strtolower (get_class ($this)); }
	function run() { }
	function config($options) { }
	function load($meta = '') { }
	function head() { }
	function plugins_loaded() {}
	function has_config() { return false; }
	
	function can_quick_edit() { return false; }
	function quick_view() { }
	
	function edit($width, $area) {}
	function save($data, $area) {}
	function is_restricted($area) { return !current_user_can ('edit_posts'); }
	
	function save_options($data) { return array (); }
	function edit_options() {}
	function init($args) {}
	
	function update($data) {
		$data = $this->save_options ($data);
		
		if (!empty ($data)) {
			$options = get_option ('headspace_options');
			
			$options['modules'][$this->id ()] = $data;
			update_option ('headspace_options', $options);
			return true;
		}
		
		return false;
	}
}

class HSM_ModuleManager
{
	var $modules = array ();
	var $active  = array ();
	
	function HSM_ModuleManager($active) {
		$available = get_declared_classes ();

		// Load all available module files if on the headspace modules pages
		if ((is_admin() && isset($_GET['page']) && isset($_GET['sub']) && $_GET['page'] == 'headspace.php' && $_GET['sub'] == 'modules') || (defined ('DOING_AJAX') && isset($_POST['action']) && in_array ($_POST['action'], array ('hs_module_order')))) {
			$files = glob (dirname (__FILE__).'/../modules/page/*.php');
			if (!empty ($files)) {
				foreach ($files AS $file)
					include_once ($file);
			}
		}
		else if (!empty ($active)) {
			// Only load active modules
			foreach ($active AS $file => $name) {
				if (file_exists (dirname (__FILE__)."/../modules/page/$file"))
					include_once (dirname (__FILE__)."/../modules/page/$file");
			}
		}

		$available = array_diff (get_declared_classes (), $available);
		
		if (count ($available) > 0) {
			$options = get_option ('headspace_options');

			foreach ($available AS $name) {
				$name = strtolower ($name);
				if (isset ($options['modules'][$name]))
					$module = new $name ($options['modules'][$name]);
				else
					$module = new $name ();
					
				$this->modules[$name] = $module;
			}
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
	
	function get_disabled($simple, $advanced) {
		// Disabled modules are everything that isnt in simple or advanced
		$disabled = $this->modules;
		
		foreach ($simple AS $module)
			unset ($disabled[$module->id ()]);

		foreach ($advanced AS $module)
			unset ($disabled[$module->id ()]);
			
		return $disabled;
	}

	function get_restricted($name, $settings, $area) {
		$modules = $this->get ($name, $settings);
		if (count ($modules) > 0) {
			foreach ($modules AS $pos => $module) {
				if ($module->is_restricted ($area))
					unset ($modules[$pos]);
			}
		}
		
		return $modules;
	}
	
	function get($name, $settings = '') {
		if (is_array ($name)) {
			$options = get_option ('headspace_options');

			$modules = array ();
			if (count ($name) > 0) {
				foreach ($name AS $modulename) {
					if (isset ($this->modules[$modulename])) {
						$module = $this->modules[$modulename];

						if ($settings != '') {
							$option = array();
							if (isset ($options['modules'][$modulename]))
								$option = $options['modules'][$modulename];

							$module = new $modulename ($option);
							$module->load ($settings);
						}
						
						$modules[] = $module;
					}
				}		
			}

			return $modules;
		}
		else if (isset ($this->modules[$name]))
			return $this->modules[$name];
		return false;
	}
	
	function get_active($settings) {
		if (count ($this->active) > 0) {
			foreach ($this->active AS $pos => $module)
				$this->active[$pos]->load ($settings);
		}
		return $this->active;
	}
}
