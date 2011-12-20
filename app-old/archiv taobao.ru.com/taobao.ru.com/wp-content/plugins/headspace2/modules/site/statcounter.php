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

class HSS_StatCounter extends HS_SiteModule
{
	var $project    = 0;
	var $partition  = 0;
	var $security   = '';
	var $role       = 'everyone';
	
	var $trackable  = null;
	
	function name ()
	{
		return __ ('StatCounter', 'headspace');
	}
	
	function description ()
	{
		return __ ('Adds StatCounter tracking code to all pages', 'headspace');
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
		if ($this->project > 0 && $this->partition > 0 && $this->security && $this->is_trackable ())
		{
			?>
			<script type="text/javascript">
				var sc_project     = <?php echo $this->project ?>; 
				var sc_invisible   = 1; 
				var sc_partition   = <?php echo $this->partition ?>; 
				var sc_security    = "<?php echo $this->security; ?>"; 
				var sc_remove_link = 1; 
			</script>
			<script type="text/javascript" src="http://www.statcounter.com/counter/counter_xhtml.js"></script>
			<?php
		}
	}
	
	function load ($data)
	{
		if (isset ($data['project']))
			$this->project = $data['project'];

		if (isset ($data['partition']))
			$this->partition = $data['partition'];
			
		if (isset ($data['security']))
			$this->security = $data['security'];
			
		if (isset ($data['role']))
			$this->role = $data['role'];
	}
	
	function has_config () { return true; }
	
	function save_options ($data)
	{
		return array ('project' => intval ($data['project']), 'partition' => intval ($data['partition']), 'security' => $data['security'], 'role' => $data['role']);
	}
	
	function edit ()
	{
	?>
	<tr>
		<th width="150"><?php _e ('Project ID', 'headspace'); ?>:</th>
		<td>
			<input type="text" name="project" value="<?php echo $this->project; ?>"/>
		</td>
	</tr>
	<tr>
		<th width="150"><?php _e ('Partition ID', 'headspace'); ?>:</th>
		<td>
			<input type="text" name="partition" value="<?php echo $this->partition; ?>"/>
		</td>
	</tr>
	<tr>
		<th width="150"><?php _e ('Security ID', 'headspace'); ?>:</th>
		<td>
			<input type="text" name="security" value="<?php echo htmlspecialchars ($this->security); ?>"/>
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