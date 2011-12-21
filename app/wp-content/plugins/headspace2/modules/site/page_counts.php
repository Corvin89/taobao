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

class HSS_PageCounts extends HS_SiteModule
{
	var $archive_count   = 0;
	var $archive_display = 'default';
	var $search_count    = 0;
	var $search_display  = 'default';
	
	function name ()
	{
		return __ ('Page Counts', 'headspace');
	}
	
	function description ()
	{
		return __ ('Customise the number of posts shown on the archive and search pages, and decide whether to show full content or the excerpt', 'headspace');
	}
	
	function run ()
	{
		if ($this->archive_count > 0 || $this->search_count > 0)
			add_filter ('pre_option_posts_per_page', array (&$this, 'posts_per_page'));
			
		if ($this->archive_display != 'default' || $this->search_display != 'default')
			add_action ('the_posts', array (&$this, 'content'));
	}
	
	function content ($posts)
	{
		if (is_search () && $this->search_display != 'default')
			return $this->modify ($posts, $this->search_display);
		
		if (is_archive () && $this->archive_display != 'default')
			return $this->modify ($posts, $this->archive_display);
			
		return $posts;
	}
	
	function modify ($posts, $type)
	{
		if (count ($posts) > 0)
		{
			foreach ($posts AS $pos => $post)
			{
				if ($type == 'excerpt')
					$posts[$pos]->post_content = wp_trim_excerpt ($post->post_content);
				else if ($type == 'content')
					$posts[$pos]->post_excerpt = $post->post_content;
			}
		}
		
		return $posts;
	}
	
	function posts_per_page ($thing)
	{
		if (is_archive ())
			return $this->archive_count;
			
		else if (is_search ())
			return $this->search_count;
			
		return false;
	}
	
	function load ($data)
	{
		if (isset ($data['archive_count']))
			$this->archive_count = $data['archive_count'];
			
		if (isset ($data['search_count']))
			$this->search_count = $data['search_count'];
			
		if (isset ($data['archive_display']))
			$this->archive_display = $data['archive_display'];
			
		if (isset ($data['search_display']))
			$this->search_display = $data['search_display'];
	}
	
	function has_config () { return true; }
	
	function save_options ($data)
	{
		return array ('archive_count' => intval ($data['archive_count']), 'archive_display' => $data['archive_display'], 'search_count' => intval ($data['search_count']), 'search_display' => $data['search_display']);
	}
	
	function edit ()
	{
	?>
	<tr>
		<th width="50"><?php _e ('Archives', 'headspace'); ?>:</th>
		<td>
			<input type="text" size="5" name="archive_count" value="<?php echo $this->archive_count; ?>"/> <?php _e ('posts, showing the', 'headspace'); ?>
			<select name="archive_display">
				<option value="default"<?php if ($this->archive_display == 'default') echo ' selected="selected"' ?>><?php _e ('default', 'headspace'); ?></option>
				<option value="content"<?php if ($this->archive_display == 'content') echo ' selected="selected"' ?>><?php _e ('content', 'headspace'); ?></option>
				<option value="excerpt"<?php if ($this->archive_display == 'excerpt') echo ' selected="selected"' ?>><?php _e ('excerpt', 'headspace'); ?></option>
			</select>
			<span class="sub"><?php _e ('(set number of posts to 0 for theme default)', 'headspace'); ?></span>
		</td>
	</tr>
	<tr>
		<th width="50"><?php _e ('Searches', 'headspace'); ?>:</th>
		<td>
			<input type="text" size="5" name="search_count" value="<?php echo $this->search_count; ?>"/> <?php _e ('posts, showing the', 'headspace'); ?>
			<select name="search_display">
				<option value="default"<?php if ($this->search_display == 'default') echo ' selected="selected"' ?>><?php _e ('default', 'headspace'); ?></option>
				<option value="content"<?php if ($this->search_display == 'content') echo ' selected="selected"' ?>><?php _e ('content', 'headspace'); ?></option>
				<option value="excerpt"<?php if ($this->search_display == 'excerpt') echo ' selected="selected"' ?>><?php _e ('excerpt', 'headspace'); ?></option>
			</select>
			<span class="sub">(<?php _e ('set number of posts to 0 for theme default', 'headspace'); ?>)</span>
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