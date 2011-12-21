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

class HSM_Tags extends HSM_Module
{
	var $tags = null;
	
	var $show_post    = 'never';
	var $show_page    = 'never';
	var $append       = true;
	var $zone_tag     = true;
	var $force_tag_search = false;
	var $disable_suggest = false;
	var $zone_tag_key = 'QeXfUYfV34GcpUS9TaSfy8kEtcMb8GVMq7Z0hPi1s4rBUQVTU8NSoHApm_m80DJQkkNj29p2Dfc-';
	
	function HSM_Tags ($options = array ()) {
		// XXXX
		if (isset ($options['show_post']))
			$this->show_post = $options['show_post'];
			
		if (isset ($options['show_page']))
			$this->show_page = $options['show_page'];
			
		if (isset ($options['zone_tag']))
			$this->zone_tag = $options['zone_tag'];
			
		if (isset ($options['zone_tag_key']))
			$this->zone_tag_key = $options['zone_tag_key'];
			
		if (isset ($options['force_tag_search']))
			$this->force_tag_search = $options['force_tag_search'];
			
		if (isset ($options['disable_suggest']))
			$this->disable_suggest = $options['disable_suggest'];
	}
	
	function load ($meta) {
		global $post;

		if (is_single () || is_page () || (is_admin () && !defined ('DOING_AJAX') && strpos ($_SERVER['REQUEST_URI'], 'categories.php') === false))
			$this->tags = $this->get_the_tags ();
			
		if (isset ($meta['keywords']) && !is_array ($meta['keywords'])) {
			$this->tags .= ','.HeadSpace_Plugin::specialchars ($meta['keywords']);
			$this->tags  = $this->normalize_tags ($this->tags);
		}

		if (empty ($this->tags))
			$this->tags = '';
	}
	
	// Special version of get_the_tags that is not dependant on being in the loop
	function get_the_tags( $id = 0 ) {
		$tags = $this->tags;
		
		if (is_single () || is_page ()) {
			global $post;
	
		 	$id = (int) $id;
	
			if ( !$id )
				$id = (int) $post->ID;
	
			$tagsextra = get_object_term_cache($id, 'post_tag');
			if ( false === $tagsextra )
				$tagsextra = wp_get_object_terms($id, 'post_tag');
	
			$tagsextra = apply_filters( 'get_the_tags', $tagsextra );
			if (!empty ($tagsextra)) {
				foreach ($tagsextra AS $tag)
					$newtags[] = $tag->name;
				
				$tags .= ','.implode (',', $newtags);
			}
		}
		
		return trim ($tags, ',');
	}
	
	function run () {
		add_filter ('the_content', array (&$this, 'content'));
		
		if ($this->force_tag_search)
			add_filter ('posts_request', array (&$this, 'posts_request'));
	}
	
	function posts_request ($request) {
		if (is_tag ())
			return str_replace ('wp_posts.post_type = \'post\'', '(post_type = \'post\' OR post_type = \'page\')', $request);
		return $request;
	}
	
	function content ($text) {
		if ((is_single () && $this->show_post == 'always') || (is_page () && $this->show_page == 'always'))
			return $this->add_tags ($text);
		return $text;
	}
	
	function add_tags ($text) {
		$headspace = HeadSpace2::get ();
		$headspace->reload ($this);
		
		return $text.get_the_tag_list (__ ('<p>Tags: ', 'headspace'), ', ', '</p>');
	}
	
	function normalize_tags( $words, $order = true ) {
		if ( is_array( $words ) )
			$words = implode( ',', $words );

		$list = explode( ',', trim( str_replace( ',,', '', $words ), ',' ) );
		if (count ($list) > 0) {
			foreach ($list AS $pos => $item) {
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
	
	function name () {
		return __ ('Tags', 'headspace');
	}
	
	function description () {
		return __ ('Allows tags to be added to pages', 'headspace');
	}
	
	function has_config () { return true; }

	function edit_options () {
		?>
		<tr>
			<th width="130"><?php _e ('Force tags in posts', 'headspace'); ?>:</th>
			<td>
				<select name="show_post">
					<option value="never"<?php if ($this->show_post == 'never') echo 'selected="selected"' ?>><?php _e ('No', 'headspace'); ?></option>
					<option value="always"<?php if ($this->show_post == 'always') echo 'selected="selected"' ?>><?php _e ('Yes', 'headspace'); ?></option>
				</select>
				<span class="sub"><?php _e ('Your theme may overrule this setting', 'headspace')?></span>
			</td>
		</tr>
		<tr>
			<th width="130"><?php _e ('Force tags in pages', 'headspace'); ?>:</th>
			<td>
				<select name="show_page">
					<option value="never"<?php if ($this->show_post == 'never') echo 'selected="selected"' ?>><?php _e ('No', 'headspace'); ?></option>
					<option value="always"<?php if ($this->show_post == 'always') echo 'selected="selected"' ?>><?php _e ('Yes', 'headspace'); ?></option>
				</select>
				<span class="sub"><?php _e ('Your theme may overrule this setting', 'headspace')?></span>
			</td>
		</tr>
		<tr>
			<th width="130"><?php _e ('Show pages', 'headspace'); ?>:</th>
			<td>
				<input type="checkbox" name="force_tag_search"<?php if ($this->force_tag_search) echo ' checked="checked"' ?>/>
				<span class="sub"><?php _e ('Show pages in tag archives', 'headspace'); ?></span>
			</td>
		</tr>
		<tr>
			<th width="130"><?php _e ('Disable Suggestions', 'headspace'); ?>:</th>
			<td>
				<input type="checkbox" name="disable_suggest"<?php if ($this->disable_suggest) echo ' checked="checked"' ?>/>
				<span class="sub"><?php _e ('In case of low memory issues', 'headspace'); ?></span>
			</td>
		</tr>
		<tr>
			<th width="130"><?php _e ('Yahoo ZoneTag', 'headspace'); ?>:</th>
			<td>
				<input class="regular-text" type="text" name="zone_tag_key" value="<?php echo HeadSpace_Plugin::specialchars ($this->zone_tag_key) ?>"/>
				<label><span class="sub"><?php _e ('enable', 'headspace'); ?> <input type="checkbox" name="zone_tag"<?php if ($this->zone_tag) echo ' checked="checked"' ?>/></span></label>
			</td>
		</tr>
		<?php
	}
	
	function save_options ($data) {
		return array
		(
			'show_post'    => $data['show_post'],
			'show_page'    => $data['show_page'],
			'zone_tag'     => isset ($data['zone_tag']) ? true : false,
			'zone_tag_key' => $data['zone_tag_key'],
			'force_tag_search' => isset ($data['force_tag_search']) ? true : false,
			'disable_suggest'  => isset ($data['disable_suggest']) ? true : false
		);
	}
	
	function edit ($width, $area) {
		global $post;

		if ($area == 'page') {
			// Edit post - already have a tag box
			?>			
			<div class="suggested" id="suggestions" style="display: none">
				<?php if ( !empty($post)) $this->suggestions ($post->ID, $post->post_content.' '.$post->post_title); ?>
			</div>
			<?php
		}
		else
		{
			// Page settings - no existing tag box
		?>
		<tr>
			<th width="<?php echo $width ?>" align="right"><?php _e ('Tags', 'headspace') ?>:</th>
			<td>
				<input id="tags-input_<?php echo $area ?>" type="text" name="tags_input" value="<?php echo HeadSpace_Plugin::specialchars ($this->tags) ?>" style="width: 95%"/>
			</td>
		</tr>
		<?php
	}
	}

	function term_to_tag ($term) {
		return $term->name;
	}
	
	function get_dictionary () {
		return implode (',', array_map (array (&$this, 'term_to_tag'), get_terms ('post_tag', array ('hide_empty' => false))));
	}
	
	function get_suggestions ($content, $type = 'hs') {
		$suggest = array ();
		if ($type == 'hs' && $this->disable_suggest === false) {
			include_once (dirname (__FILE__).'/tags/auto_suggest.php');
			include_once (dirname (__FILE__).'/tags/porter_stem.php');

			$suggest = new HS_TagSuggest ($this->get_dictionary ());
		}
		else if ($type == 'yahoo') {
			include_once (dirname (__FILE__).'/tags/yahoo.php');

			$suggest = new HS_TagYahoo ($this->zone_tag);
		}

		if (is_array ($suggest))
			return $suggest;
		return $suggest->matches ($content);
	}
	
	function suggestions ($id = 0, $content = '', $type = 'hs') {
		global $headspace2;

		$suggested = $this->get_suggestions ($content, $type);
		$nonce     = wp_create_nonce ('headspace-tags');

		if (count ($suggested) > 0 && is_array ($suggested)) {
			$all = array ();
			foreach ($suggested AS $word)
				$all[] = "'$word'";
			?>
			<h4>
				<?php _e ('Suggested tags', 'headspace'); ?>
				<small>
					(<a href="#addall" class="headspace-add-all"><?php _e ('add all', 'headspace'); ?></a> |
					
					<?php if ($this->disable_suggest === false) : ?>
					<a href="<?php echo admin_url( 'admin-ajax.php' ) ?>?action=hs_tag_update&amp;type=hs&amp;_ajax_nonce=<?php echo $nonce; ?>&amp;id=<?php echo $id; ?>" class="headspace-suggest"><?php _e ('suggest', 'headspace'); ?></a>
					<?php endif; ?>
					
					<?php if ($this->zone_tag && $this->zone_tag_key && function_exists ('curl_init')) : ?>
						<?php if ($this->disable_suggest === false) echo '|' ?>
						<a href="<?php echo admin_url( 'admin-ajax.php' ) ?>?action=hs_tag_update&amp;type=yahoo&amp;_ajax_nonce=<?php echo $nonce; ?>&amp;id=<?php echo $id; ?>" class="headspace-suggest"><?php _e ('Yahoo', 'headspace'); ?></a>
					<?php endif; ?>)
				</small>
			</h4>

			<div class="tags" id="suggested_tags">
			<?php foreach ($suggested AS $word) : ?>
				<a href="#add_tag" class="disabled button"><?php echo $word; ?></a>
			<?php endforeach; ?>
			</div>

			<img style="display: none" id="tag_loading" align="middle" src="<?php echo $headspace2->url (); ?>/images/small.gif" width="16" height="16" alt="Small"/>
			<?php
		}
		else
		{
			?>
			<h4>
				<?php if ($this->disable_suggest === false) : ?>
				<small><a href="<?php echo admin_url( 'admin-ajax.php' ) ?>?action=hs_tag_update&amp;type=hs&amp;_ajax_nonce=<?php echo $nonce; ?>&amp;id=<?php echo $id; ?>" class="headspace-suggest"><?php _e ('Suggest tags based on content', 'headspace'); ?></a>
				<?php endif; ?>
				
				<?php if ($this->zone_tag && $this->zone_tag_key && function_exists ('curl_init')) : ?>
					<?php if ($this->disable_suggest === false) echo '|' ?>
					<a href="<?php echo admin_url( 'admin-ajax.php' ) ?>?action=hs_tag_update&amp;type=yahoo&amp;_ajax_nonce=<?php echo $nonce; ?>&amp;id=<?php echo $id; ?>" class="headspace-suggest"><?php _e ('Yahoo Suggest', 'headspace'); ?></a>
				<?php endif; ?>
				</small>
			</h4>
			
			<img style="display: none" id="tag_loading" align="middle" src="<?php echo $headspace2->url (); ?>/images/small.gif" width="16" height="16" alt="Small"/>
		<?php
		}
	}
	
	
	function save ($data, $area) {
		// Normalize tags
		$tags = '';
		if (isset($data['tags_input']))
			$tags = $data['tags_input'];
		elseif (isset($data['tax_input']['post_tag']))
			$tags = $data['tax_input']['post_tag'];

		$tags = $this->normalize_tags ($tags);

		// Tags are handled by WP in posts/pages		
		if ($area == 'page')
			return array ();
			
		// Return tags for page settings
		return array ('keywords' => $tags);
	}
	
	function file () {
		return basename (__FILE__);
	}
}

?>