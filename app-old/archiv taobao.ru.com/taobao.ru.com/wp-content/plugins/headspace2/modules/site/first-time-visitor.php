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

class HSS_FirstTimeVisitor extends HS_SiteModule
{
	var $message  = '';
	var $repeat   = 4;
	var $bots     = true;
	var $position = 'content_before';
	var $visited  = false;
	
	function name ()
	{
		return __ ('First Time Visitor', 'headspace');
	}
	
	function description ()
	{
		return __ ('Display a message for first time visitors (based upon idea from Seth Godin)', 'headspace');
	}
	
	function run ()
	{
		if (!is_admin () && !is_404 () && !is_search () && $this->is_robot () == false)
		{
			$visits = 0;
			if (isset ($_COOKIE['hs_first_time']))
				$visits = $_COOKIE['hs_first_time'];

			if ($visits < $this->repeat || $this->repeat == 0)
			{
				$url = parse_url (get_option ('home'));

				setcookie ('hs_first_time', $visits  + 1, time () + 60 * 60 * 24 * 3, $url['path'].'/');
				add_filter ('the_content', array (&$this, 'content'));
			}
		}
	}
	
	function is_robot ()
	{
		if ($this->bots)
		{
			$agent = $_SERVER['HTTP_USER_AGENT'];

			// This should capture FireFox, IE, Safari, and Netscape
			if (stripos ($agent, 'mozilla') === false && stripos ($agent, 'opera') !== false)
				return true;
		}

		return false;
	}
	
	function content ($text)
	{
		$hs = HeadSpace2::get ();

		if ($hs->disabled == false && !is_feed () && !is_search () && $this->visited == false)
		{
			$this->visited = true;

			if ($this->position == 'content_before')
				return $this->message.$text;
			else if ($this->position == 'content_after')
				return $text.$this->message;
		}

		return $text;
	}
	
	function load ($data)
	{
		$this->message = sprintf (__ ('<p>As a new visitor you may want to subscribe to my <a href="%s/feed/">RSS</a> feed.</p>', 'headspace'), get_bloginfo ('home'));
		
		if (isset ($data['message']))
			$this->message = $data['message'];
			
		if (isset ($data['repeat']))
			$this->repeat = $data['repeat'];
			
		if (isset ($data['position']))
			$this->position = $data['position'];
			
		if (isset ($data['bots']))
			$this->bots = $data['bots'];
	}
	
	function has_config () { return true; }
	
	function save_options ($data)
	{
		return array(
			'message' => stripslashes($data['message']),
			'repeat' => intval ($data['repeat']),
			'position' => $data['position'],
			'bots' => isset ($data['bots']) ? true : false
		);
	}
	
	function edit ()
	{
	?>
	<tr>
		<th width="150"><?php _e ('Message to display', 'headspace'); ?>:</th>
		<td>
			<textarea rows="3" cols="40" name="message"><?php echo htmlspecialchars ($this->message); ?></textarea><br/>
		</td>
	</tr>
	<tr>
		<th><?php _e ('Repeat', 'headspace'); ?>:</th>
		<td><input type="text" name="repeat" value="<?php echo $this->repeat ?>"/> <span class="sub"><?php _e ('Enter 0 for always', 'headspace'); ?></span></td>
	</tr>
	<tr>
		<th><?php _e ('Display position', 'headspace'); ?>:</th>
		<td>
			<select name="position">
				<option <?php if ($this->position == 'content_before') echo 'selected="selected "' ?>value="content_before"><?php _e ('Before content', 'headspace'); ?></option>
				<option <?php if ($this->position == 'content_after') echo 'selected="selected "' ?>value="content_after"><?php _e ('After content', 'headspace'); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<th><label for="bots"><?php _e ('Ignore Bots', 'headspace'); ?>:</label></th>
		<td><input type="checkbox" name="bots" id="bots"<?php if ($this->bots) echo ' checked="checked"' ?>/></td>
	</tr>
	<?php
	}
	
	function file ()
	{
		return basename (__FILE__);
	}
}

?>