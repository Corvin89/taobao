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

class HSM_RssName extends HSM_Module
{
	var $rss_title;
	
	function run () {
		add_filter ('get_wp_title_rss', array (&$this, 'get_wp_title_rss'));
	}
	
	function load ($meta = '') {
		if (isset ($meta['rss_title']))
			$this->rss_title = $meta['rss_title'];
	}
	
	
	/**
	 * Insert re-configured blog name and description into the RSS feed
	 *
	 * @return void
	 **/
	
	function get_wp_title_rss ($show) {
		HeadSpace2::reload ($this);

		if ($this->rss_title)
			return $this->rss_title;
		return $show;
	}
	
	function name () {
		return __ ('RSS Name', 'headspace');
	}
	
	function description () {
		return __ ('Allows site RSS name to be changed', 'headspace');
	}
	
	function is_restricted ($area) {
		if (current_user_can ('administrator') && in_array ($area, array ('category', 'global', 'author', 'home', 'archive')))
			return false;
		return true;
	}
	
	function edit ($width, $area) {
?>
<tr>
	<th width="<?php echo $width ?>" align="right"><?php _e ('RSS Name', 'headspace') ?>:</th>
	<td>
		<input type="text" name="headspace_rss_title" value="<?php echo htmlspecialchars ($this->rss_title) ?>" style="width: 95%"/>
	</td>
</tr>
<?php
	}
	
	function save ($data, $area) {
		return array ('rss_title' => $data['headspace_rss_title']);
	}
	
	function file () {
		return basename (__FILE__);
	}
}
?>