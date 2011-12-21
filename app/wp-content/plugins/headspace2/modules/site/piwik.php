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

class HSS_Piwik extends HS_SiteModule
{
	var $piwik_id    = '1';
	var $piwik_js    = '/piwik/piwik.js';
	var $piwik_php   = '/piwik/piwik.php';
	var $downloads   = '7z|aac|avi|csv|doc|exe|flv|gif|gz|jpe?g|js|mp(3|4|e?g)|mov|pdf|phps|png|ppt|rar|sit|tar|torrent|txt|wma|wmv|xls|xml|zip';
	var $default     = '7z|aac|avi|csv|doc|exe|flv|gif|gz|jpe?g|js|mp(3|4|e?g)|mov|pdf|phps|png|ppt|rar|sit|tar|torrent|txt|wma|wmv|xls|xml|zip';
	var $aliases     = '';
	var $pause       = 250;
	var $role        = 'everyone';
	var $is_trackable = null;
	
	function name ()
	{
		return __ ('Piwik', 'headspace');
	}
	
	function description ()
	{
		return __ ('Adds Piwik tracking code to all pages', 'headspace');
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
		if ($this->piwik_id && $this->is_trackable ())
		{
			$aliases = explode ("\r\n", $this->aliases);
			if (count ($aliases) > 0)
			{
				$full = array ();
				foreach ($aliases AS $alias)
					$full[] = '"'.$alias.'"';
					
				$aliases = implode (', ', $full);
			}
			else
				$aliases = '';
			
			?>
<script type="text/javascript" src="<?php echo $this->piwik_js ?>"></script>
<script type="text/javascript">
//<![CDATA[
	piwik_action_name = document.title;
	piwik_idsite = <?php echo $this->piwik_id ?>;
	piwik_url = '<?php echo $this->piwik_php ?>';
	<?php if ($this->downloads == '') : ?>
	piwik_install_tracker = 0;
	<?php elseif ($this->downloads != $this->default) : ?>
	piwik_download_extensions = "<?php echo htmlspecialchars ($this->downloads) ?>";
	<?php endif; ?>
	<?php if ($this->pause != 250) : ?>
	piwik_tracker_pause = <?php echo $this->pause; ?>;
	<?php endif; ?>
	<?php if ($aliases != '') : ?>
	piwik_hosts_alias = [<?php echo $aliases; ?>];
	<?php endif; ?>
	piwik_log (piwik_action_name, piwik_idsite, piwik_url);
//]]>
</script>
			<?php
		}
	}
	
	function load ($data)
	{
		if (isset ($data['piwik_js']))
			$this->piwik_js = $data['piwik_js'];
			
		if (isset ($data['piwik_id']))
			$this->piwik_id = $data['piwik_id'];
			
		if (isset ($data['piwik_php']))
			$this->piwik_php = $data['piwik_php'];

		if (isset ($data['downloads']))
			$this->downloads = $data['downloads'];
			
		if (isset ($data['aliases']))
			$this->aliases = $data['aliases'];
			
		if (isset ($data['pause']))
			$this->pause = $data['pause'];
			
		if (isset ($data['role']))
			$this->role = $data['role'];
	}
	
	function has_config () { return true; }
	
	function save_options ($data)
	{
		return array
		(
			'piwik_id'  => intval ($data['piwik_id']),
			'piwik_js'  => $data['piwik_js'],
			'piwik_php' => $data['piwik_php'],
			'downloads' => $data['downloads'],
			'aliases'   => trim ($data['aliases']),
			'role'      => $data['role'],
			'pause'     => intval ($data['pause'])
		);
	}
	
	function edit ()
	{
	?>
	<tr>
		<th width="150"><?php _e ('Piwik Site ID', 'headspace'); ?>:</th>
		<td>
			<input type="text" name="piwik_id" value="<?php echo $this->piwik_id; ?>"/>
			<span class="sub"><?php _e ('If you are monitoring multiple sites this allows you to identify each', 'headspace'); ?></span>
		</td>
	</tr>
	<tr>
		<th width="150"><?php _e ('Piwik PHP', 'headspace'); ?>:</th>
		<td>
			<input type="text" name="piwik_php" value="<?php echo htmlspecialchars ($this->piwik_php); ?>"/>
			<span class="sub"><?php _e ('The location of the Piwik PHP file (i.e. <code>/piwik/piwik.php</code>)', 'headspace'); ?></span>
		</td>
	</tr>
	<tr>
		<th width="150"><?php _e ('Piwik JavaScript', 'headspace'); ?>:</th>
		<td>
			<input type="text" name="piwik_js" value="<?php echo htmlspecialchars ($this->piwik_js); ?>"/>
			<span class="sub"><?php _e ('The location of the Piwik JavaScript file (i.e. <code>/piwik/piwik.js</code>)', 'headspace') ?></span>
		</td>
	</tr>
	<tr>
		<th width="150"><?php _e ('Track downloads', 'headspace'); ?>:</th>
		<td>
			<input id="piwik_downloads" type="text" name="downloads" style="width: 95%" value="<?php echo htmlspecialchars ($this->downloads); ?>"/><br/>
			<span class="sub"><?php _e ('Clear to disable download tracking', 'headspace') ?> - <a href="#" onclick="jQuery('#piwik_downloads').val ('<?php echo $this->default ?>'); return false"><?php _e ('reset to default', 'headspace') ?></a></span>
		</td>
	</tr>
	<tr>
		<th width="150"><?php _e ('Tracker pause', 'headspace'); ?>:</th>
		<td>
			<input type="text" name="pause" style="width: 95%" value="<?php echo $this->pause; ?>"/><br/>
			<span class="sub"><?php _e ('A pause (milliseconds) added when a file is downloaded.  Small values may not be tracked', 'headspace') ?></span>
		</td>
	</tr>
	<tr>
		<th width="150"><?php _e ('Host aliases', 'headspace'); ?>:</th>
		<td>
			<textarea name="aliases" style="width: 95%" rows="4"><?php echo htmlspecialchars ($this->aliases); ?></textarea><br/>
			<span class="sub"><?php _e ('Enter each host on a separate line', 'headspace') ?></span>
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