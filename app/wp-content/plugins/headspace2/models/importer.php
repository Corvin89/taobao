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

class HS_Importer
{
	function name() { }
	function import() { return 0; }
	function cleanup() { }
}


class HS_ImportManager
{
	var $modules = array ();
	
	function HS_ImportManager() {
		// Load all available module files
		$available = get_declared_classes ();
		$files = glob (dirname (__FILE__).'/../modules/import/*.php');
		if (!empty ($files)) {
			foreach ($files AS $file)
				include_once ($file);
		}

		$available = array_diff (get_declared_classes (), $available);
		
		if (count ($available) > 0) {
			foreach ($available AS $pos => $name) {
				$name = strtolower ($name);
				$this->modules[$name] = new $name;
			}
		}
	}
	
	function available() {
		return $this->modules;
	}
	
	function get($id) {
		if (isset ($this->modules[$id]))
			return $this->modules[$id];
		return false;
	}
}
?>