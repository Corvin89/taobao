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

class HSM_JavaScript extends HSM_Module
{
	var $scripts = null;
	
	function load ($meta) {
		// Extract settings from $meta and $options
		if (isset ($meta['scripts'])) {
			$this->scripts = $meta['scripts'];
			if (!is_array ($this->scripts))
				$this->scripts = array ($this->scripts);
		}
	}
	
	function head () {
		if (!empty ($this->scripts)) {
			foreach ($this->scripts AS $script)
				echo '<script type="text/javascript" src="'.$script.'"></script>'."\r\n";
		}
	}

	function name () {
		return __ ('JavaScript', 'headspace');
	}
	
	function description () {
		return __ ('Allow external JavaScript files to be referenced', 'headspace');
	}
	
	function edit ($width, $area) {
		global $headspace2;
		$id = time ();
		
		if (count ($this->scripts) == 0)
			$this->scripts = array ('');
?>
<tr>
	<th width="<?php echo $width ?>" align="right" valign="top"><?php _e ('JavaScript', 'headspace') ?>:</th>
	<td id="headspace_scripts_<?php echo $id ?>">
		<?php if (count ($this->scripts) > 0) : ?>
			<?php foreach ($this->scripts AS $pos => $sheet) : ?>
			<input type="text" name="headspace_js[]" value="<?php echo htmlspecialchars ($sheet) ?>" style="width: 90%"/>
			<?php if ($pos == 0) : ?>
			<a href="#" onclick="jQuery('#headspace_scripts_<?php echo $id ?>').append ('<input type=&quot;text&quot; name=&quot;headspace_js[]&quot; style=&quot;width: 90%&quot;/>'); return false">
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
		if (!is_array ($data['headspace_js']))
			$data['headspace_js'] = array ();
		return array ('scripts' => array_filter ($data['headspace_js']));
	}
	
	function file () {
		return basename (__FILE__);
	}
}
?>
