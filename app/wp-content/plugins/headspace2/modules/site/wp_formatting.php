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

class HSS_WpFormatting extends HS_SiteModule
{
	var $wpautop     = false;
	var $clickable   = true;
	var $wptexturize = false;
	
	function name ()
	{
		return __ ('WordPress content formatting', 'headspace');
	}
	
	function description ()
	{
		return __ ('Allows you to enable or disable various WordPress auto-formatting (including wpautop)', 'headspace');
	}
	
	function run ()
	{
		if ($this->wpautop === false)
		{
			remove_filter ('the_content',  'wpautop');
			remove_filter ('the_excerpt',  'wpautop');
			remove_filter ('comment_text', 'wpautop');
		}
		
		if ($this->wptexturize === false)
		{
			remove_filter ('the_content',  'wptexturize');
			remove_filter ('category_description', 'wptexturize');
			remove_filter ('list_cats', 'wptexturize');
			remove_filter ('comment_author', 'wptexturize');
			remove_filter ('comment_text', 'wptexturize');
			remove_filter ('single_post_title', 'wptexturize');
			remove_filter ('the_title', 'wptexturize');
			remove_filter ('the_content', 'wptexturize');
			remove_filter ('the_excerpt', 'wptexturize');
			remove_filter ('bloginfo', 'wptexturize');
			remove_filter ('wp_title', 'wptexturize');
		}
			
		if ($this->clickable === false)
			remove_filter ('comment_text', 'make_clickable');
	}
	
	function load ($data)
	{
		if (isset ($data['wpautop']))
			$this->wpautop = $data['wpautop'];
			
		if (isset ($data['wptexturize']))
			$this->wptexturize = $data['wptexturize'];
	
		if (isset ($data['clickable']))
			$this->clickable = $data['clickable'];
	}
	
	function has_config () { return true; }
	
	function save_options ($data)
	{
		return array ('wpautop' => isset ($data['wpautop']) ? true : false, 'wptexturize' => isset ($data['wptexturize']) ? true : false, 'clickable' => isset ($data['clickable']) ? true : false);
	}
	
	function edit ()
	{
	?>
	<tr>
		<th width="150"><?php _e ('Auto-formatting', 'headspace'); ?>:</th>
		<td>
			<input type="checkbox" name="wpautop"<?php if ($this->wpautop) echo ' checked="checked"' ?>/>
			<span class="sub"><?php _e ('Use <code>wpautop</code> to format paragraphs', 'headspace'); ?></span>
		</td>
	</tr>
	<tr>
		<th width="150"><?php _e ('Auto-fancy quotes', 'headspace'); ?>:</th>
		<td>
			<input type="checkbox" name="wptexturize"<?php if ($this->wptexturize) echo ' checked="checked"' ?>/>
			<span class="sub"><?php _e ('Use <code>wptexturize</code> to turn quotes into fancy quotes', 'headspace'); ?></span>
		</td>
	</tr>
	<tr>
		<th width="150"><?php _e ('Auto-link', 'headspace'); ?>:</th>
		<td>
			<input type="checkbox" name="clickable"<?php if ($this->clickable) echo ' checked="checked"' ?>/>
			<span class="sub"><?php _e ('Makes links clickable in comments', 'headspace'); ?></span>
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