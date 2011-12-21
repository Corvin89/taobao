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

class HSM_RssTagline extends HSM_Module
{
	var $rss_desc;
	
	function run () {
		add_filter ('get_bloginfo_rss', array (&$this, 'bloginfo_rss'), 10, 2);
	}
	
	function load ($meta = '') {
		if (isset ($meta['rss_description']))
			$this->rss_desc = $meta['rss_description'];
	}
	
	
	/**
	 * Insert re-configured blog name and description into the RSS feed
	 *
	 * @return void
	 **/
	
	function bloginfo_rss ($info, $show) {
		HeadSpace2::reload ($this);
		
		if ($show == 'description' && $this->rss_desc)
			return $this->rss_desc;
		return $info;
	}
	
	function name () {
		return __ ('RSS Description', 'headspace');
	}
	
	function description () {
		return __ ('Allows site RSS description to be changed', 'headspace');
	}
	
	function is_restricted ($area) {
		if (current_user_can ('administrator') && in_array ($area, array ('category', 'global', 'author', 'home', 'archive')))
			return false;
		return true;
	}
	
	function edit ($width, $area) {
?>
<tr>
	<th width="<?php echo $width ?>" align="right"><?php _e ('RSS Desc.', 'headspace') ?>:</th>
	<td>
		<input type="text" name="headspace_rss_description" value="<?php echo htmlspecialchars ($this->rss_desc); ?>" style="width: 95%"/>
	</td>
</tr>
<?php
	}
	
	function save ($data, $area) {
		return array ('rss_description' => $data['headspace_rss_description']);
	}
	
	function file () {
		return basename (__FILE__);
	}
}
?>