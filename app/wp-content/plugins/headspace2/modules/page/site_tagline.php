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

class HSM_SiteTagline extends HSM_Module
{
	var $blog_tagline;
	
	function run () {
		add_filter ('option_blogdescription', array (&$this, 'option_blogdescription'));
	}
	
	function load ($meta) {
		if (isset ($meta['blog_tagline']))
			$this->blog_tagline = $meta['blog_tagline'];
	}
	
	
	/**
	 * Get new blog name
	 *
	 * @return string
	 **/
	function option_blogdescription ($tagline) {
		HeadSpace2::reload ($this);
				
		if (strlen ($this->blog_tagline) > 0)
			return $this->blog_tagline;
		return $tagline;
	}

	function name () {
		return __ ('Site Tagline', 'headspace');
	}
	
	function description () {
		return __ ('Allows site description to be changed (i.e. the tagline)', 'headspace');
	}
	
	function is_restricted ($area) {
		if (current_user_can ('administrator') && !in_array ($area, array ('global', 'home')))
			return false;
		return true;
	}
	
	function edit ($width, $area) {
	?>
	<tr>
		<th width="<?php echo $width ?>" align="right"><?php _e ('Site Tagline', 'headspace') ?>:</th>
		<td>
			<input type="text" name="headspace_blog_tagline" value="<?php echo htmlspecialchars ($this->blog_tagline) ?>" style="width: 95%"/>
		</td>
	</tr>
	<?php
	}
	
	function save ($data, $area) {
		return array ('blog_tagline' => $data['headspace_blog_tagline']);
	}
	
	function can_quick_edit () { return true; }
	function quick_view () {
		echo $this->blog_tagline;
	}
	
	function file () {
		return basename (__FILE__);
	}
}
?>