<?php
/**
 * HeadSpace AJAX
 *
 * @package HeadSpace
 * @author John Godley
 * @copyright Copyright (C) John Godley
 **/

/*
============================================================================================================
This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.
============================================================================================================ */
if (!class_exists ('MetaData')) {
	class MetaData
	{
		function get_custom($field) {
			$hs2 = HeadSpace2::get ();
			$data = $hs2->get_current_settings ();
			
			if (isset ($data['custom_fields'])) {
				$custom = unserialize ($data['custom_fields']);
				if (!is_array($custom))
					$custom = unserialize($custom);
					
				if (is_array($custom) && isset ($custom[$field]))
					return $custom[$field];
			}
			
			return false;
		}
		
		function add_tags($postid, $tags) {
			$tags = array_filter (explode (',', $tags));
			wp_set_post_tags ($postid, $tags, false);
		}
	
		function add_description($postid, $description) {
			MetaData::add ($postid, 'description', $description);
		}
	
		function add_stylesheet($postid, $stylesheet) {
			MetaData::add ($postid, 'style', $script);
		}

		function add_javascript($postid, $script) {
			MetaData::add ($postid, 'scripts', $script);
		}
	
		function add_more_text($postid, $moretext) {
			MetaData::add ($postid, 'more_text', $moretext);
		}
	
		function add_page_title($postid, $title) {
			MetaData::add ($postid, 'page_title', $title);
		}
	
		function add_raw($postid, $raw) {
			MetaData::add ($postid, 'raw', $raw);
		}
		
		function add_nofollow($postid, $nofollow = true) {
			MetaData::add ($postid, 'nofollow', $nofollow ? true : false);
		}
		
		function add_noindex($postid, $noindex = true) {
			MetaData::add ($postid, 'noindex', $noindex ? true : false);
		}

		function get_page_title($postid) {
			return MetaData::get ($postid, 'page_title');
		}

		function get_description($postid) {
			return MetaData::get ($postid, 'description');
		}
		
		function get_tags($postid) {
			$tags = get_object_term_cache($postid, 'post_tag');
				
			if ( false === $tags)
				$tags = wp_get_object_terms($postid, 'post_tag');

			$tags = apply_filters( 'get_the_tags', $tags );
			if (!empty ($tags)) {
				foreach ($tags AS $tag)
					$newtags[] = $tag->name;

				$tags = implode (',', $newtags);
			}
			else
				$tags = '';
			
			return $tags;
		}
		
		function get($postid, $type) {
			return get_post_meta ($postid, '_headspace_'.$type, true);
		}
		
		function get_noindex($postid) {
			return MetaData::get ($postid, 'noindex');
		}
		
		function get_nofollow($postid) {
			return MetaData::get ($postid, 'nofollow');
		}
		
		function add($postid, $type, $data, $insert = false) {
			global $wpdb;
			
			$field = '_headspace_'.$type;
			
			if (!empty ($data)) {
				// Do we update or insert?
				$meta = get_post_meta ($postid, $field);
			
				if ($insert == true || empty ($meta) || $meta === false)
					$wpdb->query ("INSERT INTO {$wpdb->postmeta} (post_id,meta_key,meta_value) VALUES ('$postid','$field','".$wpdb->escape ($data)."')");
				else
					update_post_meta ($postid, $field, $data);
			}
			else
				delete_post_meta ($postid, $field);
		}
	}
}

?>