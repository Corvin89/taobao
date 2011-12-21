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

class HSS_AwStats extends HS_SiteModule
{
	var $role         = 'everyone';	
	var $is_trackable = null;
	
	function name ()
	{
		return __ ('AWStats JavaScript Tracking', 'headspace');
	}
	
	function description ()
	{
		return __ ('Adds AWStats JavaScript tracking to all pages (through <code>wp_footer</code>)', 'headspace');
	}
	
	function run ()
	{
		add_action ('wp_footer', array (&$this, 'wp_footer'));
	}
	
	function is_trackable ()
	{
		if ($this->is_trackable !== null)
			return $this->is_trackable;
			
		if (is_user_logged_in () && $this->role != 'everyone')
		{
			$user = wp_get_current_user ();
			
			global $wp_roles;
			$caps = $wp_roles->get_role ($this->role);
			
			if ($caps)
			{
				// Calculate the highest level of the user and the role
				$role_level = $user_level = 0;
				for ($x = 10; $x >= 0; $x--)
				{
					if (isset ($caps->capabilities['level_'.$x]))
						break;
				}
			
				$role_level = $x;

				for ($x = 10; $x >= 0; $x--)
				{
					if (isset ($user->allcaps['level_'.$x]))
						break;
				}
			
				$user_level = $x;
			
				// Quit if the user is greater level than the role
				if ($user_level > $role_level)
				{
					$this->is_trackable = false;
					return false;
				}
			}
		}
		
		$this->is_trackable = true;
		return $this->is_trackable;
	}
	
	function wp_footer ()
	{
		global $headspace2;
		if ($this->is_trackable ())
			echo '<script src="'.$headspace2->url ().'/js/awstats.js" type="text/javascript" charset="utf-8"></script>';
	}

	function load ($data)
	{
		$load = array ('role');
		
		foreach ($load AS $key)
		{
			if (isset ($data[$key]))
				$this->$key = $data[$key];
		}
	}
	
	function has_config () { return true; }
	
	function save_options ($data)
	{
		return array
		(
			'role'      => $data['role'],
		);
	}
	
	function edit ()
	{
	?>
	<tr>
		<th><?php _e ('Who to track', 'headspace'); ?>:</th>
		<td>
			<select name="role">
				<option value="everyone"><?php _e ('Everyone', 'headspace'); ?></option>
					<?php global $wp_roles; foreach ($wp_roles->role_names as $key => $rolename) : ?>
						<option value="<?php echo $key ?>"<?php if ($this->role == $key) echo ' selected="selected"'; ?>><?php echo $rolename ?></option>
					<?php endforeach; ?>
				</select>
			</select>
			
			<span class="sub"><?php _e ('Users of the specified role or less will be tracked', 'headspace'); ?></span>
		</td>
	</tr>
	<?php
	}
	
	function file ()
	{
		return basename (__FILE__);
	}
}

?>