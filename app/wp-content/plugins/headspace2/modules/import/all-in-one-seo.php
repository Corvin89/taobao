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

class ImportAllInOne extends HS_Importer
{
	function name ()
	{
		return __ ('All-in-one SEO', 'headspace');
	}
	
	function import ()
	{
		$count = 0;
		
		global $wpdb;
		$values = $wpdb->get_results ("SELECT * FROM {$wpdb->postmeta} WHERE meta_key='keywords'");
		if ($values)
		{
			foreach ($values AS $meta)
				MetaData::add_tags ($meta->post_id, stripslashes ($meta->meta_value));
			
			$count += count ($values);
		}
		
		$values = $wpdb->get_results ("SELECT * FROM {$wpdb->postmeta} WHERE meta_key='title'");
		if ($values)
		{
			foreach ($values AS $meta)
				MetaData::add_page_title ($meta->post_id, stripslashes ($meta->meta_value));
			
			$count += count ($values);
		}
		
		$values = $wpdb->get_results ("SELECT * FROM {$wpdb->postmeta} WHERE meta_key='description'");
		if ($values)
		{
			foreach ($values AS $meta)
				MetaData::add_description ($meta->post_id, stripslashes ($meta->meta_value));
			
			$count += count ($values);
		}
		
		return $count;
	}
	
	function cleanup ()
	{
		global $wpdb;
		$wpdb->query ("DELETE FROM {$wpdb->postmeta} WHERE meta_key='keywords'");
		$wpdb->query ("DELETE FROM {$wpdb->postmeta} WHERE meta_key='title'");
		$wpdb->query ("DELETE FROM {$wpdb->postmeta} WHERE meta_key='description'");
	}
}

?>