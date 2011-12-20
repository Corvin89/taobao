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

class ImportSimpleTags extends HS_Importer
{
	function name ()
	{
		return __ ('Simple Tags (old version)', 'headspace');
	}
	
	function import ()
	{
		$count = 0;
		
		global $wpdb;
		$values = $wpdb->get_results ("SELECT ID,post_content FROM {$wpdb->posts} WHERE post_content LIKE '%[tag]%' OR post_content LIKE '%[tags]%'");
		if ($values)
		{
			foreach ($values AS $post)
			{
				$tags = '';
				
				if (preg_match_all ('/(\[tag\](.*?)\[\/tag\])/i', $post->post_content, $matches) > 0)
				{
					$tags .= implode (',', $matches[2]);
					$count += count ($matches[2]);
				}

				if (preg_match_all ('/(\[tags\](.*?)\[\/tags\])/i', $post->post_content, $matches) > 0)
				{
					$tags .= ','.implode (',', $matches[2]);
					$count += count ($matches[2]);
				}
	
				$tags = $this->normalize_tags ($tags);
				MetaData::add_tags ($post->ID, $tags);
			}
			
			$count += count ($keywords);
		}
		
		return $count;
	}
	
	function cleanup ()
	{
		// Best not to mess with posts
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
			if ($order)
				sort ($list);
				
			return implode (',', $list);
		}
		
		return $words;
	}
}

?>