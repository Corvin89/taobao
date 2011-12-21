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

class HSM_RawFooter extends HSM_Module
{
	var $raw_footer = null;
	
	function load ($meta) {
		// Extract settings from $meta and $options
		if (isset ($meta['raw_footer']))
			$this->raw_footer = $meta['raw_footer'];
	}
	
	function run () {
		add_action ('wp_footer', array (&$this, 'wp_footer'));
	}
	
	function wp_footer () {
		if ($this->raw_footer)
		  echo $this->raw_footer."\r\n";
	}
	
	function name () {
		return __ ('Raw footer data', 'headspace');
	}
	
	function description () {
		return __ ('Allows raw data to be inserted into the page footer', 'headspace');
	}
	
	function edit ($width, $area) {
?>
<tr>
	<th width="<?php echo $width ?>" align="right" valign="top"><?php _e ('Raw footer data', 'headspace') ?>:</th>
	<td>
		<textarea name="headspace_raw_footer" style="width: 95%" rows="3"><?php echo htmlspecialchars ($this->raw_footer) ?></textarea>
	</td>
</tr>
<?php
	}
	
	function save ($data, $area) {
		return array ('raw_footer' => $data['headspace_raw_footer']);
	}
	
	function file () {
		return basename (__FILE__);
	}
}
?>