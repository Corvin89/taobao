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

class HSM_Mass_Edit
{
	function id() { return strtolower (get_class ($this)); }
	function name() { return ''; }
	function get(&$pager) { return array (); }
	function get_pager() { }
}

class HSM_Mass_Editor
{
	function available() {
		// Load all available module files
		$available = get_declared_classes ();
		$files = glob (dirname (__FILE__).'/../modules/mass/*.php');
		if (!empty ($files)) {
			foreach ($files AS $file)
				include_once ($file);
		}

		$types     = array ();
		$available = array_diff (get_declared_classes (), $available);
		
		if (count ($available) > 0) {
			$options = get_option ('headspace_options');

			foreach ($available AS $name) {
				$name = strtolower ($name);
				$types[$name] = new $name;
			}
		}
		
		return $types;
	}
}
