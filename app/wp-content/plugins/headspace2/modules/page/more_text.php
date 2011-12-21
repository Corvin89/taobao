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

class HSM_MoreText extends HSM_Module
{
	var $more_text = '';
	var $excerpt   = false;
	var $pattern   = 'class="more-link">(.*?)</a>';
	var $replace   = 'class="more-link">$1</a>';
	
	function HSM_MoreText ($options = array ()) {
		if (isset ($options['excerpt']))
			$this->excerpt = $options['excerpt'];

		if (isset ($options['pattern']))
			$this->pattern = stripslashes( $options['pattern'] );

		if (isset ($options['replace']))
			$this->replace = stripslashes( $options['replace'] );
	}
	
	function run () {
		add_filter ('the_content', array (&$this, 'more_link_text'));
		
		if ($this->excerpt)
			add_filter ('the_excerpt', array (&$this, 'excerpt_more_link'));
	}
	
	function excerpt_more_link ($text) {
		global $post;

		HeadSpace2::reload ($this);

		if ($this->more_text)
			return $text.'<a class="more-link" href="'.get_permalink ($post->ID).'">'.$this->more_text.'</a>';
		return $text;
	}
	
	function more_link_text ($text) {
		HeadSpace2::reload ($this);

		if ($this->more_text)
			return preg_replace ('@'.$this->pattern.'@', str_replace ('$1', $this->more_text, $this->replace), $text);
		return $text.$this->more_text;
	}
	
	function load ($meta = '') {
		$this->more_text = '';

		if (isset ($meta['more_text']))
			$this->more_text = $meta['more_text'];
	}
	
	function can_quick_edit () { return true; }
	
	function quick_view () {
		echo $this->more_text;
	}
	
	function name () {
		return __ ('More text', 'headspace');
	}
	
	function description () {
		return __ ('Allows the \'more\' text to be changed', 'headspace');
	}
	
	function is_restricted ($area) {
		if (current_user_can ('edit_posts') && $area == 'page')
			return false;
		return true;
	}
	
	function edit ($width, $area) {
		?>
		<tr>
			<th width="<?php echo $width ?>" align="right"><?php _e ('More text', 'headspace') ?>:</th>
			<td>
				<input type="text" name="headspace_more_text" value="<?php echo htmlspecialchars ($this->more_text) ?>" style="width: 95%"/>
			</td>
		</tr>
		<?php
	}
	
	function save ($data, $area) {
		return array ('more_text' => trim ($data['headspace_more_text']));
	}
	
	function file () {
		return basename (__FILE__);
	}

	function has_config () { return true; }
	
	function edit_options () {
		?>
		<tr>
			<th width="120"><?php _e ('Enable on excerpts', 'headspace'); ?>:</th>
			<td>
				<input type="checkbox" name="excerpt" <?php if ($this->excerpt) echo ' checked="checked"'; ?>/>
			</td>
		</tr>
		<tr>
			<th width="120"><?php _e ('Pattern', 'headspace'); ?>:</th>
			<td>
				<input type="text" name="pattern" value="<?php echo htmlspecialchars ($this->pattern) ?>"/><br/>
				<span class="sub"><?php _e ('Advanced - the regular expression pattern to select the more link from your posts', 'headspace'); ?></span>
			</td>
		</tr>
		<tr>
			<th width="120"><?php _e ('Replace', 'headspace'); ?>:</th>
				<input type="text" name="replace" value="<?php echo htmlspecialchars ($this->replace) ?>"/><br/>
				<span class="sub"><?php _e ('Advanced - the regular expression replacement text', 'headspace'); ?></span>
			</td>
		</tr>
		<?php
	}
	
	function save_options ($data) {
		return array
		(
			'pattern' => $data['pattern'],
			'replace' => $data['replace'],
			'excerpt' => isset ($data['excerpt']) ? true : false
		);
	}	
}
?>