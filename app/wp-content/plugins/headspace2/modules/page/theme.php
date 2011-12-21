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

class HSM_Theme extends HSM_Module
{
	var $theme = null;
	
	function run () {
		$pages   = get_option ('headspace_page_themes');
		$options = get_option ('headspace_options');

		if (isset ($options['debug']) && $options['debug'] == true)
			HeadSpace2::debug (print_r ($pages, true));
		
		if (isset ($_GET['page_id']))
			$url = '/?page_id='.intval ($_GET['page_id']);
		else
			$url = '/?p='.intval ($_GET['p']);
			
		if (isset ($_GET['page_id']))
			$url = '/?page_id='.intval ($_GET['page_id']);
		elseif (isset ($_GET['p']))
			$url = '/?p='.intval ($_GET['p']);
		else
			$url = str_replace( get_bloginfo( 'home' ), '', 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
			
		if (isset ($pages[$_SERVER['REQUEST_URI']]) || isset ($pages[$url])) {
			if (isset ($pages[$_SERVER['REQUEST_URI']]))
				$this->theme = $pages[$_SERVER['REQUEST_URI']];
			else
				$this->theme = $pages[$url];
			
			add_filter ('template', array (&$this, 'template'));
			add_filter ('stylesheet', array (&$this, 'template'));
		}
	}
	
	function load ($meta = '') {
		// Extract settings from $meta and $options
		if (isset ($meta['theme']))
			$this->theme = $meta['theme'];
	}
	
	function template ($current) {
		if ($this->theme)
			return $this->theme;
		return $current;
	}
	
	function name () {
		return __ ('Page-specific Theme', 'headspace');
	}
	
	function is_restricted ($area) {
		if (current_user_can ('administrator') && $area == 'page')
			return false;
		return true;
	}
	
	function description () {
		return __ ('Allows a custom page-specific theme to over-ride the default theme', 'headspace');
	}
	
	function edit ($width, $area) {
		$themes = get_themes ();
		?>
	<tr>
		<th width="<?php echo $width ?>" align="right"><?php _e ('Theme', 'headspace') ?>:</th>
		<td>
			<select name="headspace_theme">
				<option value="0"><?php _e ('Current theme', 'headspace'); ?></option>
				
				<?php foreach ($themes AS $name => $values) : ?>
				<option value="<?php echo $values['Template'] ?>"<?php if ($values['Template'] == $this->theme) echo ' selected="selected"' ?>><?php echo $name ?></option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<?php
	}
	
	function save ($data, $area) {
		global $wpdb;
		
		// Go through and re-create all page URLs
		$url = $this->link (intval ($_POST['post_ID']));

		$pages = get_option ('headspace_page_themes');
		if ($data['headspace_theme'] != '0') {		
			$posts = $wpdb->get_results ("SELECT post_id,meta_value FROM {$wpdb->postmeta} WHERE meta_key='_headspace_theme'");
			if ($posts) {
				$pages = array ();
				
				foreach ($posts AS $post)
					$pages[$this->link ($post->post_id)] = $post->meta_value;
					
				$pages[$url] = $data['headspace_theme'];
				update_option ('headspace_page_themes', $pages);
			}
			
			return array ('theme' => $data['headspace_theme']);
		}
		else if (isset ($pages[$url])) {
			unset ($pages[$url]);
			update_option ('headspace_page_themes', $pages);
		}
		
		return array ('theme' => '');
	}
	
	function link ($id) {
		$url = get_permalink ($id);
		$url = str_replace (get_bloginfo ('home'), '', $url);
		return $url;
	}
	
	function can_quick_edit () { return true; }
	function quick_view () {
		echo $this->theme;
	}
	
	function file () {
		return basename (__FILE__);
	}
}
