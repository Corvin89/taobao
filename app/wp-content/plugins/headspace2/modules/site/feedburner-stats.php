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

class HSS_FeedburnerStats extends HS_SiteModule
{
	var $account = '';
	
	function name ()
	{
		return __ ('Feedburner Stats Pro', 'headspace');
	}
	
	function description ()
	{
		return __ ('Adds appropriate code to your posts to enable FeedBurner Stats Pro', 'headspace');
	}
	
	function burn ($text)
	{
		return $text.'<script src="http://feeds.feedburner.com/~s/'.$this->account.'?i='.get_permalink ().'" type="text/javascript" charset="utf-8"></script>';
	}

	function run ()
	{
		add_filter ('the_content', array (&$this, 'burn'));
		add_filter ('the_excerpt', array (&$this, 'burn'));
		add_filter ('the_excerpt_reloaded', array (&$this, 'burn'));
	}
	
	function load ($data)
	{
		if (isset ($data['account']))
			$this->account = $data['account'];
	}
	
	function has_config () { return true; }
	
	function save_options ($data)
	{
		return array ('account' => $data['account']);
	}
	
	function edit ()
	{
	?>
	<tr>
		<th width="150"><?php _e ('Account ID', 'headspace'); ?>:</th>
		<td>
			<input type="text" name="account" value="<?php echo htmlspecialchars ($this->account); ?>"/><br/>
			<span class="sub"><?php _e ('This is your FeedBurner username', 'headspace'); ?></span>
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