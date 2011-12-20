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

class HSS_WpWidgets extends HS_SiteModule
{
	var $widgets = array ();
	
	function name ()
	{
		return __ ('Disable WordPress Widgets', 'headspace');
	}
	
	function description ()
	{
		return __ ('Allows you to enable or disable various WordPress Widgets', 'headspace');
	}
	
	function plugins_loaded ()
	{
		remove_filter ('init', 'wp_widgets_init', 1);
		
		if (count ($this->widgets) > 0)
			add_action ('init', array (&$this, 'init'));
	}
	
	function init ()
	{
		$dims90  = array( 'height' => 90, 'width' => 300 );
		$dims100 = array( 'height' => 100, 'width' => 300 );
		$dims150 = array( 'height' => 150, 'width' => 300 );

		if (!function_exists ('wp_widget_text_register'))
			return;
		
		foreach ($this->widgets AS $widget)
		{
			switch ($widget)
			{
				case 'pages' : 
					$class = array('classname' => 'widget_pages');
					wp_register_sidebar_widget('pages', __('Pages'), 'wp_widget_pages', $class);
					wp_register_widget_control('pages', __('Pages'), 'wp_widget_pages_control', $dims150);
					break;

				case 'calendar' : 
					$class['classname'] = 'widget_calendar';
					wp_register_sidebar_widget('calendar', __('Calendar'), 'wp_widget_calendar', $class);
					wp_register_widget_control('calendar', __('Calendar'), 'wp_widget_calendar_control', $dims90);
					break;

				case 'archives' :
					$class['classname'] = 'widget_archives';
					wp_register_sidebar_widget('archives', __('Archives'), 'wp_widget_archives', $class);
					wp_register_widget_control('archives', __('Archives'), 'wp_widget_archives_control', $dims100);
					break;

				case 'links' : 
					$class['classname'] = 'widget_links';
					wp_register_sidebar_widget('links', __('Links'), 'wp_widget_links', $class);
					break;

				case 'meta' : 
					$class['classname'] = 'widget_meta';
					wp_register_sidebar_widget('meta', __('Meta'), 'wp_widget_meta', $class);
					wp_register_widget_control('meta', __('Meta'), 'wp_widget_meta_control', $dims90);
					break;

				case 'search' : 
					$class['classname'] = 'widget_search';
					wp_register_sidebar_widget('search', __('Search'), 'wp_widget_search', $class);
					break;

				case 'recent_entries' :
					$class['classname'] = 'widget_recent_entries';
					wp_register_sidebar_widget('recent-posts', __('Recent Posts'), 'wp_widget_recent_entries', $class);
					wp_register_widget_control('recent-posts', __('Recent Posts'), 'wp_widget_recent_entries_control', $dims90);
					break;

				case 'tag_cloud' :
					$class['classname'] = 'widget_tag_cloud';
					wp_register_sidebar_widget('tag_cloud', __('Tag Cloud'), 'wp_widget_tag_cloud', $class);
					wp_register_widget_control('tag_cloud', __('Tag Cloud'), 'wp_widget_tag_cloud_control', 'width=300&height=160');
					break;

				case 'categories' : wp_widget_categories_register();
					break;
					
				case 'text' : wp_widget_text_register();
					break;
					
				case 'rss' : wp_widget_rss_register();
					break;
					
				case 'recent_comments' :
					wp_widget_recent_comments_register();
					break;
			}
		}
	}
	
	function load ($data)
	{
		if (isset ($data['widgets']))
			$this->widgets = $data['widgets'];
	}
	
	function has_config () { return true; }
	
	function save_options ($data)
	{
		$widgets = array ();
		if (isset ($_POST['widgets']))
			$widgets = $_POST['widgets'];
		return array ('widgets' => $widgets);
	}
	
	function edit ()
	{
		$widgets = array
		(
			'categories'        => __ ('categories', 'headspace'),
			'text'              => __ ('text', 'headspace'),
			'rss'               => __ ('rss', 'headspace'),
			'recent_comments'   => __ ('recent comments', 'headspace'),
			'pages'             => __ ('pages', 'headspace'),
			'search'            => __ ('search', 'headspace'),
			'calendar'          => __ ('calendar', 'headspace'),
			'archives'          => __ ('archives', 'headspace'),
			'links'             => __ ('links', 'headspace'),
			'meta'              => __ ('meta', 'headspace'),
			'recent_entries'    => __ ('recent entries', 'headspace'),
			'tag_cloud'         => __ ('tag cloud', 'headspace')
		);
	?>
	<tr>
		<th width="150"><?php _e ('Enabled Widgets', 'headspace'); ?>:</th>
		<td>
			<?php foreach ($widgets AS $widg => $name) : ?>
			<label><input type="checkbox" name="widgets[]" value="<?php echo $widg; ?>" <?php if (in_array ($widg, $this->widgets)) echo ' checked="checked"' ?>/> <?php echo $name; ?></label><br/>
			<?php endforeach; ?>
		</td>
	</tr>
	<?php
	}
	
	function file ()
	{
		return basename (__FILE__);
	}
}

?>