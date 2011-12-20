<?php

/*
Plugin Name: Another Wordpress Meta Plugin
Plugin URI: http://wp.uberdose.com/2006/11/04/another-wordpress-meta-plugin/
Description: Plugin for inserting the META tags 'keywords' and 'description' into your posts, pages and index page.
Version: 2.0.3
Author: some guy
Author URI: http://wp.uberdose.com/
*/

/* Copyright (C) 2007 uberdose.com (awtp AT uberdose DOT com)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA */
 
 class Another_WordPress_Meta_Plugin {
 	
 	var $version = "2.0.3";

	/**
	* Outputs meta tags "keywords" and "description" in the header
	* of a page.
	*/
	function meta_head_start() {
		global $post;
		$meta_string = null;
		
		echo "<!-- awmp $this->version -->\n";
		
		// keywords are always generated from the posts' keywords
		$keywords = $this->get_all_keywords();

		if (is_single() || is_page()) {
			// the easiest part: a single post or page
            $description = trim(stripslashes(get_post_meta($post->ID, "description", true)));
		} else if (is_category()) {
			// here we can at least use the category description			
			$category = get_the_category();
			if (isset($category[0]->category_description)) {
				$description = trim(stripslashes($category[0]->category_description));
			}
		} else if (is_home()){
			// index page
			if (get_option('another_wordpress_meta_plugin_home_description')) {
				$description = stripslashes(get_option('another_wordpress_meta_plugin_home_description'));
			} else {
				$description = get_bloginfo('description');
			}
		}

		if (isset ($description) && !empty($description)) {
			$meta_string = sprintf("<meta name=\"description\" content=\"%s\"/>", $description);
		}
		if (isset ($keywords) && !empty($keywords)) {
			if (isset ($meta_string) && !empty($meta_string)) {
				$meta_string .= "\n";
			}
			$meta_string .= sprintf("<meta name=\"keywords\" content=\"%s\"/>\n", $keywords);
		}

		if ($meta_string != null) {
			echo $meta_string;
			echo "<!-- /awmp -->\n";
		} else {
			echo "<!-- /awmp: nothing found -->\n";
		}
	}
	
	function get_all_keywords() {
		global $posts;

	    if (is_array($posts)) {
	        foreach ($posts as $post) {
	            if ($post) {
					if (get_option('another_wordpress_meta_plugin_use_categories')) {
		                $categories = get_the_category($post->ID);
		                foreach ($categories as $category) {
		                    if (isset($keywords) && !empty($keywords)) {
		                        $keywords .= ',';
		                    }
		                	$keywords .= $category->cat_name;
		                }
					}
	                $keywords_a = $keywords_i = null;
	                $description_a = $description_i = null;
	                $id = $post->ID;
		            $keywords_i = stripslashes(get_post_meta($post->ID, "keywords", true));
	                if (isset($keywords_i) && !empty($keywords_i)) {
	                    if (isset($keywords) && !empty($keywords)) {
	                        $keywords .= ',';
	                    }
	                    $keywords .= $keywords_i;
	                }
	            }
	        }
	    }
	    
	    return $this->get_unique_keywords($keywords);
	}

	function get_unique_keywords($keywords) {
		$keywords_ar = array_unique(explode(',', $keywords));
		return implode(',', $keywords_ar);
	}
	
	function admin_menu() {
		add_submenu_page('options-general.php', __('Another Wordpress Meta Plugin'), __('Another Wordpress Meta Plugin'), 5, basename(dirname(__FILE__)), array($this, 'plugin_menu'));
	}
	
	function add_meta_tags_textinput() {
	    global $post;
	    $keywords = stripslashes(get_post_meta($post->ID, 'keywords', true));
	    $description = stripslashes(get_post_meta($post->ID, 'description', true));
		?>
		<input value="awmp_edit" type="hidden" name="awmp_edit" />
		<table style="margin-bottom:40px; margin-top:30px;">
		<tr><th style="text-align:left;" colspan="2">Meta Information (by <a title="Homepage for Another Wordpress Meta Plugin" href="http://wp.uberdose.com/2006/11/04/another-wordpress-meta-plugin/">Another Wordpress Meta Plugin</a>)</th></tr>
		<tr>
		<th scope="row" style="text-align:right;"><?php _e('Description:') ?></th>
		<td><input value="<?php echo $description ?>" type="text" name="awmp_description" size="50"/></td>
		</tr>
		<tr>
		<th scope="row" style="text-align:right;"><?php _e('Keywords (comma separated):') ?></th>
		<td><input value="<?php echo $keywords ?>" type="text" name="awmp_keywords" size="50"/></td>
		</tr>
		</table>
		<?php
	}

	function post_meta_tags($id) {
	    $awmp_edit = $_POST["awmp_edit"];
	    if (isset($awmp_edit) && !empty($awmp_edit)) {
		    $description = $_POST["awmp_description"];
		    $keywords = $_POST["awmp_keywords"];

		    delete_post_meta($id, 'description');
		    delete_post_meta($id, 'keywords');

		    if (isset($description) && !empty($description)) {
		    	add_post_meta($id, 'description', $description);
		    }
		    if (isset($keywords) && !empty($keywords)) {
			    add_post_meta($id, 'keywords', $keywords);
		    }
	    }
	}

	/**
	 * Used for outputting technorati tags from keywords at
	 * the end of posts.
	 */
	function the_content($content = '') {
		global $post;
		if (get_option('another_wordpress_meta_plugin_generate_tags')) {
        	$base_url = get_option("home");
        	if (strrchr($base_url, '/') != strlen($base_url)) {
        		$base_url .= '/';
        	}
        	$awmp_tag_url = get_option('another_wordpress_meta_plugin_tags_base');
        	if (strpos($awmp_tag_url, '/') == 0) {
        		$awmp_tag_url = substr($awmp_tag_url, 1);
        	}

            $keywords .= stripslashes(get_post_meta($post->ID, "keywords", true));

            if ($keywords) {
            	$tags = '';
            	$count = 0;
            	$a_keywords = split(",", $keywords);
            	foreach ($a_keywords as $tag) {
            		if ($count++) {
            			$tags .= " ";
            		}
            		$tag = trim($tag);
            		$url = $base_url . $awmp_tag_url;
            		$url = str_replace('$tag', $tag, $url);
            		$tags .= "<a href=\"$url\" rel=\"tag\">$tag</a>";
	            }
				$content .= "<div class=\"awmp_tags\">$tags</div>";
            }
		}
		return $content;
	}
	
	function plugin_menu() {
		$message = null;
		$message_updated = __("Options for Another Wordpress Meta Tags Plugin Updated.");
		
		// update options
		if ($_POST['action'] && $_POST['action'] == 'update') {
			$message = $message_updated;
			update_option('another_wordpress_meta_plugin_home_description', $_POST['home_description']);
			update_option('another_wordpress_meta_plugin_tags_base', $_POST['tags_base']);
			update_option('another_wordpress_meta_plugin_home_keywords', $_POST['home_keywords']);
			if ($_POST['generate_tags']) {
				update_option('another_wordpress_meta_plugin_generate_tags', true);
			} else {
				update_option('another_wordpress_meta_plugin_generate_tags', false);
			}
			if ($_POST['use_keywords']) {
				update_option('another_wordpress_meta_plugin_use_categories', true);
			} else {
				update_option('another_wordpress_meta_plugin_use_categories', false);
			}
			wp_cache_flush();
		}

?>
<?php if ($message) : ?>
<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
<?php endif; ?>
<div id="dropmessage" class="updated" style="display:none;"></div>
<div class="wrap">
<h2><?php _e('Another Wordpress Meta Options'); ?></h2>
<p><?php _e('For feedback, help etc. please click <a title="Homepage for Another Wordpress Meta Plugin" href="http://wp.uberdose.com/2006/11/04/another-wordpress-meta-plugin/">here.</a> ') ?></p>
<p><?php _e('Consider using <strong><a href="http://wp.uberdose.com/2007/03/24/all-in-one-seo-pack/">All In One SEO pack</a></strong> for even easier Search Engine Optimization.'); ?></p>
<p><?php _e('These values are used for your <b>main page / home page</b>:') ?></p>
<form name="dofollow" action="" method="post">
<table>
<tr>
<th scope="row" style="text-align:right;"><?php _e('Home Description:')?></td>
<td><input type="text" size="60" name="home_description" value="<?php echo get_option('another_wordpress_meta_plugin_home_description'); ?>"/></td>
</tr>
<tr>
<th scope="row" style="text-align:right;"><?php _e('Use Categories as keywords (additional):')?></td>
<td><input type="checkbox" name="use_keywords" <?php if (get_option('another_wordpress_meta_plugin_use_categories')) echo " checked=\"false\" "; ?>/></td>
</tr>
<tr>
<th scope="row" style="text-align:right;"><?php _e('Generate Technorati Style Tags at the End of Posts:')?></td>
<td><input type="checkbox" name="generate_tags" <?php if (get_option('another_wordpress_meta_plugin_generate_tags')) echo " checked=\"true\" "; ?>/></td>
</tr>
<tr>
<th scope="row" style="text-align:right;"><?php _e('Link Base for Generated Tags<br/>($tag will be replaced by the tag)<br/>NOTE: Only change this if you know what you\'re doing!')?></td>
<td><input type="text" size="60" name="tags_base" value="<?php echo get_option('another_wordpress_meta_plugin_tags_base'); ?>"/></td>
</tr>
</table>
<p class="submit">
<input type="hidden" name="action" value="update" /> 
<input type="hidden" name="page_options" value="home_keywords,home_description" /> 
<input type="submit" name="Submit" value="<?php _e('Update Options')?> &raquo;" /> 
</p>
</form>
</div>
<?php
	
	} // _awmp_plugin_menu
	
} // Another_WordPress_Meta_Plugin

$_awmp_plugin = new Another_WordPress_Meta_Plugin();
add_action('wp_head', array($_awmp_plugin, 'meta_head_start'));

add_action ('admin_menu', array($_awmp_plugin, 'admin_menu'));

add_action('simple_edit_form', array($_awmp_plugin, 'add_meta_tags_textinput'));
add_action('edit_form_advanced', array($_awmp_plugin, 'add_meta_tags_textinput'));
add_action('edit_page_form', array($_awmp_plugin, 'add_meta_tags_textinput'));

add_action('edit_post', array($_awmp_plugin, 'post_meta_tags'));
add_action('publish_post', array($_awmp_plugin, 'post_meta_tags'));
add_action('save_post', array($_awmp_plugin, 'post_meta_tags'));
add_action('edit_page_form', array($_awmp_plugin, 'post_meta_tags'));

add_option("another_wordpress_meta_plugin_home_description", null, __('Home Meta Description (used by Another Wordpress Meta Plugin).'), 'yes');
add_option("another_wordpress_meta_plugin_home_keywords", null, __('Home Meta Keywords (used by Another Wordpress Meta Plugin).'), 'yes');
add_option("another_wordpress_meta_plugin_generate_tags", false, __('Should Technorati Tags be generated at the end of posts? (used by Another Wordpress Meta Plugin).'), 'yes');
add_option("another_wordpress_meta_plugin_use_categories", false, __('Should categories be used as keywords? (used by Another Wordpress Meta Plugin).'), 'no');
add_option("another_wordpress_meta_plugin_tags_base", '/search/$tag/', __('Tag Base for Generated Tags (used by Another Wordpress Meta Plugin).'), 'yes');

// Generate Technorati style Tags at the end of posts
add_filter('the_content', array($_awmp_plugin, 'the_content'));
?>
