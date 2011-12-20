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

class HS_Mass_Tags extends HSM_Mass_Edit
{
	function name ()
	{
		return __ ('Tags', 'headspace');
	}
	
	function get_pager ()
	{
		global $wpdb;
		
		$orderby = array
		(
			'id'    => "{$wpdb->posts}.ID",
			'title' => "{$wpdb->posts}.post_title",
			'desc'  => "{$wpdb->postmeta}.meta_value"
		);
		
		return new HS_Pager ($_GET, $_SERVER['REQUEST_URI'], "{$wpdb->posts}.ID", 'DESC', 'headspace', $orderby);
	}
	
	function show_header ($pager)
	{
		global $wpdb;
	?>
	<th><?php echo $pager->sortable ('title', __ ('Post title', 'headspace')) ?></th>
	<th width="60%"><?php echo $pager->sortable ('desc', __ ('Tags/keywords', 'headspace')) ?></th>
	<th width="16"></th>
	<?php
	}
	
	function show ($post)
	{
		$tags = str_replace (',', ', ', MetaData::get_tags ($post->ID));
	?>
	<td><?php echo htmlspecialchars ($post->post_title); ?></td>
	<td width="60%" id="tags_<?php echo $post->ID ?>">
		<input style="width: 95%" type="text" name="edit[<?php echo $post->ID ?>]" value="<?php echo htmlspecialchars ($tags); ?>" id="edit_<?php echo $post->ID ?>"/>
	</td>
	<td width="16"><a class="tag" href="<?php echo admin_url( 'admin-ajax.php' ) ?>?id=<?php echo $post->ID ?>&amp;action=hs_auto_tag&amp;_ajax_nonce=<?php echo wp_create_nonce ('headspace-auto_tag_'.$post->ID) ?>"><img src="../wp-content/plugins/headspace2/images/refresh.png" width="16" height="16" alt="View"/></a></td>
	<?php	
	}
	
	function get (&$pager)
	{
		global $wpdb;

		$sql = "SELECT SQL_CALC_FOUND_ROWS {$wpdb->posts}.ID,{$wpdb->posts}.post_title FROM {$wpdb->posts}";

		$limits = $pager->to_limits ("(post_type='page' OR post_type='post')", array ("{$wpdb->postmeta}.meta_value", "{$wpdb->posts}.post_title"));

		$rows = $wpdb->get_results ($sql.$limits);
		$pager->set_total ($wpdb->get_var ("SELECT FOUND_ROWS()"));
		return $rows;
	}
	
	function update ($data)
	{
		if (count ($data['edit']) > 0)
		{
			global $wpdb;
			foreach ($data['edit'] AS $postid => $values)
			{
				$postid = intval ($postid);
				
				// Just check we can edit this
				if (current_user_can ('edit_post', $postid))
					MetaData::add_tags ($postid, $values);
			}
		}
	}
	
	function normalize_tags ($words, $order = true)
	{
		$list = explode (',', trim (str_replace (',,', '', $words), ','));
		if (count ($list) > 0)
		{
			foreach ($list AS $pos => $item)
			{
				$list[$pos] = trim ($item);
				
				if (function_exists ('mb_strtolower'))
					$list[$pos] = mb_strtolower ($list[$pos], get_option ('blog_charset'));
				else
					$list[$pos] = strtolower ($list[$pos]);
			}

			$list = array_unique ($list);
			if ($this->order)
				sort ($list);
				
			return implode (',', $list);
		}
		
		return $words;
	}
}
