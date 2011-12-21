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

class HS_InlineTags
{
	function replace($value, $post) {
		if ( is_array( $value ) || is_object( $value ) )
			return $value;

		global $wp_query, $wp_locale;
			
		$replace_with = '';
		
		// We can only replace inline post tags when given a post
		if (is_object ($post)) {
			$userData = get_userdata ($post->post_author);

			$tags = '';
			if (function_exists ('the_tags')) {
				if (is_tag ())
					$tags = single_tag_title (false, '');
				else
				{
					$items = get_the_tags ($post->ID);
					if ($items) {
						foreach ($items AS $tag)
							$tags[] = $tag->name;
						
						$tags = implode (', ', $tags);
					}
				}
			}

			if (is_day ())
				$date = get_the_time('F jS, Y');
			else if (is_month ())
				$date = get_the_time('F, Y');
			else if (is_year ())
				$date = get_the_time('Y');
			else
				$date = $post->post_date;
			
			$replace_with = array
			(
				$date,
			  $post->post_title,
			  $post->post_modified,
			  $post->ID,
			  $userData->display_name,
			  $post->post_author,
				$tags,
				$_SERVER['REQUEST_URI']
			);		
		}
		else if (is_author ()) {
			global $posts;
			$userData = get_userdata ($posts[0]->post_author);
			$replace_with = array ('', '', '', '', $userData->display_name, $posts[0]->post_author);
		}
		else if (is_archive ()) {
			$m        = get_query_var ('m');
			$year     = get_query_var ('year');
			$monthnum = get_query_var('monthnum');
			$day      = get_query_var('day');
			$date     = '';

			// If there's a month
			if (!empty ($m)) {
				$my_year  = substr($m, 0, 4);
				$my_month = $wp_locale->get_month(substr($m, 4, 2));
				$my_day   = intval(substr($m, 6, 2));
				$date     = "$my_year" . ($my_month ? "$sep $my_month" : "") . ($my_day ? "$sep $my_day" : "");
			}
		
			if (!empty ($year)) {
				if ( !empty($monthnum) )
					$date .= " $sep " . $wp_locale->get_month($monthnum);
				if ( !empty($day) )
					$date .= " $sep " . zeroise($day, 2);
					
				$date .= ' '.$year;
			}
			
			$replace_with = array
			(
				$date,
				'',
				'',
				'',
				'',
				'',
				'',
				$_SERVER['REQUEST_URI']
			);
		}
		else if (function_exists ('is_tag') && is_tag ())
			$replace_with = array ('', '', '', '', '', '', single_tag_title('', false));

		$search_for = array
		(
			"%%date%%",
			"%%title%%",
			"%%modified%%",
			"%%id%%",
			"%%name%%",
			"%%userid%%",
			'%%tag%%',
			'%%url%%'
		);
		
		// Replace post values
		$value = str_replace ($search_for, $replace_with, $value);

		// Replace static values
		$value = str_replace ('%%searchphrase%%', isset ($wp_query->query_vars['s']) ? strip_tags ($wp_query->query_vars['s']) : '', $value);
		$value = str_replace ('%%currentdate%%', date (get_option ('date_format')), $value);
		$value = str_replace ('%%currenttime%%', date (get_option ('time_format')), $value);
		$value = str_replace ('%%currentyear%%', date ('Y'), $value);

		global $headspace2;
		$headspace2->ugly_hack = true;
		$value = str_replace ('%%sitename%%', get_bloginfo ('blogname'), $value);
		$headspace2->ugly_hack = false;
		
		if (is_object ($wp_locale))
			$value = str_replace ('%%currentmonth%%', $wp_locale->get_month(date ('n')), $value);
		else
			$value = str_replace ('%%currentmonth%%', date ('F'), $value);
		
		// These need extra work so we only do it if necessary
		if (strpos ($value, '%%excerpt%%') !== false)
			$value = str_replace ('%%excerpt%%', HS_InlineTags::get_excerpt ($post, true), $value);

		if (strpos ($value, '%%excerpt_only%%') !== false)
			$value = str_replace ('%%excerpt_only%%', HS_InlineTags::get_excerpt ($post, false), $value);

		if (strpos ($value, '%%category%%') !== false)
			$value = str_replace ('%%category%%', HS_InlineTags::get_category ($post), $value);

		if (strpos ($value, '%%category_description%%') !== false)
			$value = str_replace ('%%category_description%%', HS_InlineTags::get_category_description ($post), $value);
			
		if (strpos ($value, '%%tag_description%%') !== false)
			$value = str_replace ('%%tag_description%%', HS_InlineTags::get_tag_description ($post), $value);

                if (strpos ($value, '%%term_description%%') !== false)
                        $value = str_replace ('%%term_description%%', HS_InlineTags::get_term_description (), $value);

                if (strpos ($value, '%%term_title%%') !== false)
                        $value = str_replace ('%%term_title%%', HS_InlineTags::get_term_title (), $value);

		if (strpos ($value, '%%page%%') !== false)
			$value = str_replace ('%%page%%', HS_InlineTags::get_page ($post), $value);

		if (strpos ($value, '%%pagenumber%%') !== false)
			$value = str_replace ('%%pagenumber%%', HS_InlineTags::get_page ($post, 'number'), $value);

		if (strpos ($value, '%%pagetotal%%') !== false)
			$value = str_replace ('%%pagetotal%%', HS_InlineTags::get_page ($post, 'total'), $value);
			
		if (strpos ($value, '%%caption%%') !== false)
			$value = str_replace ('%%caption%%', $post->post_excerpt, $value);

		return $value;
	}
	
	/**
	 * Return the current category description
	 *
	 * @return string
	 **/
	
	function get_category_description($post) {
		$desc = category_description ();
		if (is_object ($desc))
			return '';
		
		return strip_tags ($desc);
	}
	
	/**
	 * Return the current category description
	 *
	 * @return string
	 **/

	function get_tag_description($post) {
		if (function_exists ('the_tags')) {
			if (is_tag ()) {
				$tag = intval( get_query_var('tag_id') );
				return strip_tags( get_term_field('description', $tag, 'post_tag') );
			} else {
				$items = get_the_tags ($post->ID);
				if ($items) {
					$tags = '';
					foreach ($items AS $tag)
						$tags[] = get_term_field('description', $tag, 'post_tag');

					return strip_tags( implode (', ', $tags) );
				}
			}
		}
		return '';
	}

       /**
        * Return the current taxonomy term description
        *
        * @return string
        **/

       function get_term_description() {

               $taxonomy = get_query_var('taxonomy');
               $term = get_query_var('term');

               if (function_exists ('get_term') && $taxonomy != '' && $term != '') {

                       $term = get_term_by('slug', $term, $taxonomy);

                       if ( is_wp_error($term) )
                               return '';

                       return($term->description);
               }
               return '';
       }
       
        /**
        * Return the current taxonomy term title        *
        * @return string
        **/

       function get_term_title() {

               $taxonomy = get_query_var('taxonomy');
               $term = get_query_var('term');

               if (function_exists ('get_term') && $taxonomy != '' && $term != '') {

                       $term = get_term_by('slug', $term, $taxonomy);

                       if ( is_wp_error($term) )
                               return '';

                       return($term->name);
               }
               return '';
       }


	
	/**
	 * Return the current categories
	 *
	 * @return string
	 **/
	
	function get_category($post) {
		// Get data from the post
		if (is_single ()) {
			$cats = get_the_category ($post->ID);
			if (count ($cats) > 0) {
			  foreach ($cats AS $cat)
					$category[] = $cat->cat_name;
			
			  $category = implode (', ', $category);
			}
		
			return $category;
		}
		else if (is_archive ())
			return single_cat_title ('', false);
		return '';
	}
	
	
	/**
	 * Return the current post excerpt
	 *
	 * @return string
	 **/
	
	function get_excerpt($post, $auto = true) {
		$excerpt = '';
		if ( is_object( $post ) ) {
			if ($post->post_excerpt != '')
				$excerpt = trim (str_replace ('[...]', '', $post->post_excerpt));
			else if ($auto) {
				$hs      = HeadSpace2::get ();
				$options = $hs->get_options ();		
				$excerpt = $post->post_content;
			
				if (isset ($options['excerpt']) && $options['excerpt']) {
					$hs->disabled = true;
					$excerpt = apply_filters ('the_content', $post->post_content);
					$hs->disabled = false;
				}
			
				$excerpt_length = apply_filters( 'excerpt_length', 1000 );	
				$excerpt = substr( strip_shortcodes( $excerpt ), 0, $excerpt_length );
			}
		}

		$excerpt = strip_tags ($excerpt);
		return $excerpt;
	}
	
	
	/**
	 * Return the page position
	 *
	 * @return string
	 **/
	
	function get_page($post, $type = 'all') {
		global $wp_query;
		
		if ($wp_query->max_num_pages > 1) {
			$paged = get_query_var ('paged');
			$max   = $wp_query->max_num_pages;
			
			if ($paged == 0)
				$paged = 1;
				
			if ($paged == 1)
				return '';
				
			if ($type == 'all')
				return sprintf ( __( 'page %d of %d', 'headspace' ), $paged, $max );
			else if ($type == 'number')
				return $paged;
			else if ($type == 'total')
				return $max;
		}
	}
}

?>