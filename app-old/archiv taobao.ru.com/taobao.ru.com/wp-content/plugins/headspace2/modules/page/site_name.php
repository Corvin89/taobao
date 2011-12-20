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

class HSM_SiteName extends HSM_Module
{
	var $blog_name;
	
	function run () {
		add_filter ('option_blogname', array (&$this, 'option_blogname'));
	}
	
	function load ($meta) {
		if (isset ($meta['blog_name']))
			$this->blog_name = $meta['blog_name'];
	}
	
	
	/**
	 * Get new blog name
	 *
	 * @return string
	 **/
	function option_blogname ($tagline) {
		global $headspace2;
		if ($headspace2->ugly_hack !== true)
			HeadSpace2::reload ($this);

		if (strlen ($this->blog_name) > 0)
			return trim (HeadSpace_Plugin::specialchars ($this->blog_name));
		return trim ($tagline);
	}
	
	function name () {
		return __ ('Site name', 'headspace');
	}
	
	function description () {
		return __ ('Allows site name to be changed (i.e your blog name)', 'headspace');
	}
	
	function is_restricted ($area) {
		if (current_user_can ('administrator') && !in_array ($area, array ('global', 'home')))
			return false;
		return true;
	}
	
	function edit ($width, $area) {
		?>
		<tr>
			<th width="<?php echo $width ?>" align="right"><?php _e ('Site name', 'headspace') ?>:</th>
			<td>
				<input type="text" name="headspace_blog_name" value="<?php echo HeadSpace_Plugin::specialchars ($this->blog_name) ?>" style="width: 95%"/>
			</td>
		</tr>
		<?php
	}
	
	function can_quick_edit () { return true; }
	function quick_view () {
		echo $this->blog_name;
	}
	
	function save ($data, $area) {
		return array ('blog_name' => $data['headspace_blog_name']);
	}
	
	function file () {
		return basename (__FILE__);
	}
}
?>