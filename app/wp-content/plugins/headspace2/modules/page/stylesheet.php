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

class HSM_Stylesheet extends HSM_Module
{
	var $stylesheets = array ();
	var $disable     = false;
	
	function HSM_Stylesheet ($options = array ()) {
		if (isset ($options['disable']))
			$this->disable = $options['disable'];
	}
	
	function load ($meta) {
		// Extract settings from $meta and $options
		if (isset ($meta['stylesheets'])) {
			$this->stylesheets = $meta['stylesheets'];
			if (!is_array ($this->stylesheets))
				$this->stylesheets = array ($this->stylesheets);
		}
	}
	
	function init () {
	}
	
	function head () {
		if (!empty ($this->stylesheets) && $this->disable == false) {
			foreach ($this->stylesheets AS $style)
				echo '<link rel="stylesheet" href="'.$style.'" type="text/css" />'."\r\n";
		}
	}
	
	function name () {
		return __ ('Stylesheets', 'headspace');
	}
	
	function description () {
		return __ ('Allows CSS stylesheets to be added to a page', 'headspace');
	}
	
	function edit ($width, $area) {
		global $headspace2;
		$id = time ();
		
		if (count ($this->stylesheets) == 0)
			$this->stylesheets = array ('');
?>
<tr>
	<th width="<?php echo $width ?>" align="right" valign="top"><?php _e ('Stylesheets', 'headspace') ?>:</th>
	<td id="headspace_styles_<?php echo $id ?>">
		<?php if (count ($this->stylesheets) > 0) : ?>
			<?php foreach ($this->stylesheets AS $pos => $sheet) : ?>
			<input type="text" name="headspace_style[]" value="<?php echo htmlspecialchars ($sheet) ?>" style="width: 90%"/>
			<?php if ($pos == 0) : ?>
			<a href="#" onclick="jQuery('#headspace_styles_<?php echo $id ?>').append ('<input type=&quot;text&quot; name=&quot;headspace_style[]&quot; style=&quot;width: 90%&quot;/>'); return false">
				<img src="<?php echo $headspace2->url (); ?>/images/add.png" alt="add"/>
			</a>
			<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</td>
</tr>
<?php
	}
	
	function save ($data, $area) {
		if (!is_array ($data['headspace_style']))
			$data['headspace_style'] = array ();
		return array ('stylesheets' => array_filter ($data['headspace_style']));
	}
	
	function file () {
		return basename (__FILE__);
	}
	
	function has_config () { return true; }

	function edit_options () {
		?>
			<tr>
				<th><label for="order"><?php _e ('Do not output CSS', 'headspace'); ?>:</label></th>
				<td>
					<input type="checkbox" name="disable" <?php if ($this->disable) echo ' checked="checked"' ?> id="order"/>
				</td>
			</tr>
		<?php
	}
	
	function save_options ($data) {
		return array ('disable' => isset ($data['disable']) ? true : false);
	}
}
?>
