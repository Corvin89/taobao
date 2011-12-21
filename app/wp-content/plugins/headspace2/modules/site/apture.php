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

class HSS_Apture extends HS_SiteModule
{
	var $role = 'everyone';
	var $token = '';
	
	function name ()
	{
		return __ ('Apture', 'headspace');
	}
	
	function description ()
	{
		return __ ('Add Apture to all pages', 'headspace');
	}
	
	function run ()
	{
		add_action ('wp_footer', array (&$this, 'wp_footer'));
	}
	
	function is_trackable ()
	{
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
		
		return true;
	}
	
	function wp_footer ()
	{
		if ($this->is_trackable ())
			echo '<script type="text/javascript" id="aptureScript" src="http://www.apture.com/js/apture.js?siteToken='.$this->token.'" charset="utf-8"></script>';
	}
	
	function load ($data)
	{
		if (isset ($data['role']))
			$this->role = $data['role'];
			
		if (isset ($data['token']))
			$this->token = $data['token'];
	}
	
	function has_config () { return true; }
	
	function save_options ($data)
	{
		return array ('token' => $data['token'], 'role' => $data['role']);
	}
	
	function edit ()
	{
	?>
	<tr>
		<th width="150"><?php _e ('Site token', 'headspace'); ?>:</th>
		<td>
			<input type="text" name="token" value="<?php echo htmlspecialchars ($this->token); ?>"/><br/>
			<span class="sub"><?php _e ('Enter your site token.', 'headspace'); ?></span>
		</td>
	</tr>
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