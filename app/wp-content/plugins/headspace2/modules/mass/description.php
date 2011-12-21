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

class HS_Mass_Description extends HSM_Mass_Edit
{
	function name ()
	{
		return __ ('Page description', 'headspace');
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
	<th><?php echo $pager->sortable ('desc', __ ('Page description', 'headspace')) ?></th>
	<th width="16"></th>
	<?php
	}
	
	function show ($post)
	{
	?>
	<td><?php echo htmlspecialchars ($post->post_title); ?></td>
	<td width="60%" align="center" ><input style="width: 95%" type="text" name="edit[<?php echo $post->ID ?>][<?php echo $post->meta_id ?>]" value="<?php echo htmlspecialchars ($post->meta_value); ?>" id="edit_<?php echo $post->ID ?>"/></td>
	<td width="16"><a href="<?php echo admin_url( 'admin-ajax.php' ); ?>?action=hs_auto_description&amp;post=<?php echo $post->ID ?>&amp;_ajax_nonce=<?php echo wp_create_nonce( 'headspace-autodescription' ); ?>" class="auto-desc"><img src="../wp-content/plugins/headspace2/images/refresh.png" width="16" height="16" alt="View"/></a></td>
	<?php	
	}
	
	function get (&$pager)
	{
		global $wpdb;

		$sql    = "SELECT SQL_CALC_FOUND_ROWS {$wpdb->posts}.ID,{$wpdb->posts}.post_title,{$wpdb->postmeta}.meta_value,{$wpdb->postmeta}.meta_id FROM {$wpdb->posts} LEFT JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID={$wpdb->postmeta}.post_id AND {$wpdb->postmeta}.meta_key='_headspace_description'";
		$limits = $pager->to_limits ("({$wpdb->posts}.post_type='page' OR {$wpdb->posts}.post_type='post')", array ("{$wpdb->postmeta}.meta_value", "{$wpdb->posts}.post_title"));

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
				{
					foreach ($values AS $metaid => $value)
					{
						$savevalue = $wpdb->escape ($value);
						$metaid    = intval ($metaid);
					
						if ($metaid == 0 && !empty ($value))
						{
							// This is a new value
							$wpdb->query ("INSERT INTO {$wpdb->postmeta} (post_id,meta_key,meta_value) VALUES ($postid,'_headspace_description','$savevalue')");
						}
						else if ($metaid > 0 && empty ($value))
						{
							// Delete the value
							$wpdb->query ("DELETE FROM {$wpdb->postmeta} WHERE meta_id='$metaid'");
						}
						else if ($metaid > 0 && !empty ($value))
						{
							// Update the value
							$wpdb->query ("UPDATE {$wpdb->postmeta} SET meta_value='$savevalue' WHERE meta_id='$metaid'");
						}
					}
				}
			}
		}
	}
}

?>