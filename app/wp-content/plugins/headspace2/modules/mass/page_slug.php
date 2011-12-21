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

class HS_Mass_PageSlug extends HSM_Mass_Edit
{
	function name ()
	{
		return __ ('Page slug', 'headspace');
	}
	
	function get_pager ()
	{
		global $wpdb;
		
		$orderby = array
		(
			'id'    => "{$wpdb->posts}.ID",
			'title' => "{$wpdb->posts}.post_title",
			'slug'  => "{$wpdb->posts}.post_name"
		);
		
		return new HS_Pager ($_GET, $_SERVER['REQUEST_URI'], "{$wpdb->posts}.ID", 'DESC', 'headspace', $orderby);
	}
	
	function show_header ($pager)
	{
		global $wpdb;
	?>
	<th><?php echo $pager->sortable ('title', __ ('Post title', 'headspace')) ?></th>
	<th><?php echo $pager->sortable ('slug', __ ('Post slug', 'headspace')) ?></th>
	<?php
	}
	
	function show ($post)
	{
	?>
	<td><?php echo htmlspecialchars ($post->post_title); ?></td>
	<td width="60%" align="center" ><input style="width: 95%" type="text" name="edit[<?php echo $post->ID ?>]" value="<?php echo htmlspecialchars ($post->post_name); ?>" id="edit_<?php echo $post->ID ?>"/></td>
	<?php	
	}
	
	function get (&$pager)
	{
		global $wpdb;

		$sql    = "SELECT SQL_CALC_FOUND_ROWS {$wpdb->posts}.ID,{$wpdb->posts}.post_title,{$wpdb->posts}.post_name FROM {$wpdb->posts}";
		$limits = $pager->to_limits ("({$wpdb->posts}.post_type='page' OR {$wpdb->posts}.post_type='post')", array ("{$wpdb->posts}.post_name", "{$wpdb->posts}.post_title"));

		$rows = $wpdb->get_results ($sql.$limits);
		$pager->set_total ($wpdb->get_var ("SELECT FOUND_ROWS()"));
		return $rows;
	}
	
	function update ($data)
	{
		if (count ($data['edit']) > 0)
		{
			global $wpdb;
			foreach ($data['edit'] AS $postid => $slug)
			{
				$postid = intval ($postid);
				
				// Just check we can edit this
				if (current_user_can ('edit_post', $postid))
				{
					$_POST['redirection_slug'] = get_permalink ($postid);
					wp_update_post (array ('ID' => $postid, 'post_name' => $slug));
				}
			}
		}
	}
}

?>