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

class HSM_FollowLinks extends HSM_Module
{
	var $rss_feed = 'default';
	var $links = array ();
	
	function names () {
		return array
		(
			'get_archives_link'          => __('Archive Links', 'headspace'),
			'wp_list_categories'         => __('Category Links', 'headspace'),
			'wp_list_pages'              => __('Page Links', 'headspace'),
			'term_links-post_tag'        => __('Tag Links', 'headspace'),
			'get_comment_author_link'    => __('Comm. Author Links', 'headspace'),
			'comment_text'               => __('Comm. Text Links', 'headspace'),
			'next_posts_link_attributes' => __('Next/Prev Posts Link', 'headspace')
		);
	}

	function load ($meta) {
		// Extract settings from $meta and $options
		if (isset ($meta['follow_link'])) {
			$this->links = unserialize ($meta['follow_link']);
			if (!is_array ($this->links))
				$this->links = unserialize ($this->links);
		}
	}
	
	function head () {
		if (count ($this->links) > 0) {
			foreach ($this->links AS $filter => $follow) {
				if ($filter != 'next_posts_link_attributes' )
					add_filter ($filter, array (&$this, 'filter_'.$follow));
			}
				
			// Catch synonymns
			if (isset ($this->links['term_links-post_tag']))
				add_filter ('wp_tag_cloud', array (&$this, 'filter_'.$this->links['term_links-post_tag']));
				
			// Catch previous posts links
			if (isset ($this->links['next_posts_link_attributes'])) {
				add_filter( 'prev_posts_link_attributes', array(&$this, $this->links['next_posts_link_attributes'].'_next_posts_link_attributes'));
				add_filter( 'next_posts_link_attributes', array(&$this, $this->links['next_posts_link_attributes'].'_next_posts_link_attributes'));
			}
		}
	}
	
	function insert_rel ($matches, $follow) {
		$rel = array ();
		
		if (preg_match ('/rel=["\'](.*?)["\']/', $matches[1], $existing) > 0) {
			$rel = array_unique (array_merge ($rel, explode (' ', $existing[1])));
			$rel = array_diff ($rel, array ('follow', 'nofollow'));
			$matches[1] = preg_replace ('/rel=["\'](.*?)["\']/', '', $matches[1]);
		}

		$rel[] = $follow;
		
		return '<a rel="'.implode (' ', $rel).'"'.$matches[1].'>';
	}
	
	function insert_nofollow ($matches) {
		return $this->insert_rel ($matches, 'nofollow');
	}
	
	function insert_follow ($matches) {
		return $this->insert_rel ($matches, '');
	}
	
	function follow_next_posts_link_attributes($attr) {
		return 'rel="follow"';
	}

	function nofollow_next_posts_link_attributes($attr) {
		return 'rel="nofollow"';
	}
	
	function filter_follow ($text) {
		return preg_replace_callback ('@<a(.*?)>@', array (&$this, 'insert_follow'), $text);
	}
	
	function filter_nofollow ($text) {
		return preg_replace_callback ('@<a(.*?)>@', array (&$this, 'insert_nofollow'), $text);
	}
	
	function name () {
		return __ ('Follow Links', 'headspace');
	}
	
	function description () {
		return __ ('Allows follow/no-follow to be set for various links', 'headspace');
	}
	
	function edit ($width, $area) {
		foreach ($this->names () AS $key => $value) : ?>
	<tr>
		<th width="<?php echo $width ?>" align="right"><?php echo $value; ?>:</th>
		<td>
			<label><input type="radio" name="headspace_follow_link[<?php echo $key; ?>]" value="follow"<?php echo $this->checked ($key, 'follow'); ?>/> <?php _e ('Follow', 'headspace'); ?></label>
			<label><input type="radio" name="headspace_follow_link[<?php echo $key; ?>]" value="nofollow"<?php echo $this->checked ($key, 'nofollow'); ?>/> <?php _e ('No-follow', 'headspace'); ?></label>
			<label><input type="radio" name="headspace_follow_link[<?php echo $key; ?>]" value="default"<?php echo $this->checked ($key, 'default'); ?>/> <?php _e ('Default', 'headspace'); ?></label>
		</td>
	</tr>
<?php endforeach;
	}
	
	function checked ($key, $setting) {
		if ((isset ($this->links[$key]) && $this->links[$key] == $setting) || (!isset ($this->links[$key]) && $setting == 'default'))
			echo ' checked="checked"';
	}
	
	function remove_default_filter ($item) {
		if ($item == 'default')
			return false;
		return true;
	}
	
	function save ($data, $area) {
		// Remove default
		$links = array();
		if ( isset($data['headspace_follow_link']))
			$links = array_filter ((array)$data['headspace_follow_link'], array (&$this, 'remove_default_filter'));
		
		if (count ($links) > 0)
			return array ('follow_link' => serialize ($links));
		return array ('follows_links' => array ());
	}
	
	function file () {
		return basename (__FILE__);
	}
}
