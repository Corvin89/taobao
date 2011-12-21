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

class HSM_Plugin extends HSM_Module
{
	var $plugins = null;
	var $admin   = true;
	
	function HSM_Plugin ($options = array ()) {
		if (isset ($options['admin']))
			$this->admin = $options['admin'];
	}
	
	function run () {
		$pages = get_option ('headspace_page_plugins');

		$url = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
		if (isset ($_GET['page_id']))
			$url = '/?page_id='.intval ($_GET['page_id']);
		elseif ( isset( $_GET['p'] ) )
			$url = '/?p='.intval ($_GET['p']);

		if ( isset( $pages[$url] ) ) {
			$this->plugins = $pages[$url];

			add_filter ('init', array (&$this, 'init'));
		}
		else if ($this->admin && is_admin () && !empty ($pages))
			add_filter ('init', array (&$this, 'init'));
	}
	
	function load ($meta) {
		if (isset ($meta['plugins'])) {
			$this->plugins = $meta['plugins'];
			if (!is_array ($this->plugins))
				$this->plugins = array ($this->plugins);
		}
	}
	
	function init ($current) {
		$plugindir = ABSPATH.PLUGINDIR;
		if (defined ('WP_PLUGIN_DIR'))
			$plugindir = WP_PLUGIN_DIR;

		$plugindir = rtrim ($plugindir, '/');
		
		if (is_admin ()) {
			$pages = get_option ('headspace_page_plugins');
 			foreach ($pages AS $page => $plugins) {
				if (!empty ($plugins)) {
					foreach ($plugins AS $plugin) {
						if ( file_exists( $plugindir.'/'.$plugin ) && is_file( $plugindir.'/'.$plugin ) )
							include_once ($plugindir.'/'.$plugin);
					}
				}
			}
		}
		else if (!empty ($this->plugins)) {
			foreach ($this->plugins AS $plugin) {
				if (file_exists ($plugindir.'/'.$plugin) && is_file( $plugindir.'/'.$plugin )) {
					include_once ($plugindir.'/' . $plugin);
				}
			}
		}
	}
	
	function name () {
		return __ ('Page-specific Plugins', 'headspace');
	}
	
	function description () {
		return __ ('Allows disabled plugins to be enabled on specific pages', 'headspace');
	}
	
	function is_restricted ($area) {
		if (current_user_can ('administrator') && function_exists ('get_plugins') && $area == 'page')
			return false;
		return true;
	}
	
	function has_config () { return true; }
	
	function edit_options () {
		?>
		<tr>
			<th><?php _e ('Show in admin', 'headspace'); ?>:</th>
			<td>
				<input type="checkbox" name="admin"<?php if ($this->admin) echo ' checked="checked"' ?>/>
				<span class="sub"><?php _e ('Shows page-specific plugins in the administration menus', 'headspace'); ?></span>
			</td>
		</tr>
		<?php
	}
	
	function save_options ($data) {
		return array ('admin' => isset ($data['admin']) ? true : false);
	}
	
	function edit ($width, $area) {
		global $headspace2;
		$headspace = HeadSpace2::get ();
		
		$plugins = get_plugins();
		$current = array_filter (get_option ('active_plugins'));

		foreach ($current AS $active) {
			if (isset ($plugins[$active]))
				unset ($plugins[$active]);
		}

		?>
	<tr>
		<th width="<?php echo $width ?>" align="right" valign="top"><?php _e ('Plugins', 'headspace'); ?></th>
		<td>
			<select name="headspace_plugin" id="headspace_plugin">
			<?php foreach ($plugins AS $name => $details) : ?>
				<option value="<?php echo $name ?>"><?php echo $details['Name']; ?></option>
			<?php endforeach; ?>
			</select>
			<a href="#" onclick="return add_plugin ()"><img valign="bottom" src="<?php echo $headspace2->url (); ?>/images/add.png" alt="add"/></a>

			<ul id="headspace_plugins">
				<?php if (!empty ($this->plugins)) : ?>
					<?php foreach ($this->plugins AS $name) : ?>
						<li>
							<div class="delete"><a href="#" onclick="return delete_plugin(this);"><img src="<?php echo $headspace->url () ?>/images/delete.png" alt="delete" width="16" height="16"/></a></div>
							<?php echo $plugins[$name]['Name'] ?>
			  			<input type='hidden' name='headspace_plugins[]' value='<?php echo $name ?>'/>
						</li>
					<?php endforeach?>
				<?php endif; ?>
			</ul>
		</td>
	</tr>
<?php
	}
	
	function save ($data) {
		// Go through and re-create all page URLs
		$url = $this->link ($_POST['post_ID']);
		
		$pages = get_option ('headspace_page_plugins');
		if ($data['headspace_plugins'] != '0') {
			global $wpdb;
			
			$posts = $wpdb->get_results ("SELECT post_id,meta_value FROM {$wpdb->postmeta} WHERE meta_key='_headspace_plugins'");
			if ($posts) {
				$pages = array ();
				
				foreach ($posts AS $post)
					$pages[$this->link ($post->post_id)][] = $post->meta_value;
					
				$pages[$url] = $data['headspace_plugins'];
				update_option ('headspace_page_plugins', $pages);
			}
			
			return array ('plugins' => $data['headspace_plugins']);
		}
		else if (isset ($pages[$url])) {
			unset ($url);
			update_option ('headspace_page_plugins', $pages);
		}
		
		return array ();
	}
	
	function link ($id) {
		$url = get_permalink ($id);
		$url = str_replace (get_bloginfo ('home'), '', $url);
		return $url;
	}

	function file () {
		return basename (__FILE__);
	}
}

?>
