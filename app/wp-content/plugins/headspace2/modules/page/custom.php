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

class HSM_Custom extends HSM_Module
{
	var $fields = array ();
	
	function HSM_Custom ($options = array ()) {
		if (isset ($options['fields']))
			$this->fields = $options['fields'];
	}
	
	function load ($meta) {
		if (isset ($meta['custom_fields'])) {
			$data = unserialize ($meta['custom_fields']);
			if (!is_array ($data))
				$data = unserialize ($data);

			$this->values = $data;
		}
	}
	
	function name () {
		return __ ('Custom data', 'headspace');
	}
	
	function description () {
		return __ ('Allows custom data to be inserted anywhere in your theme using MetaData::custom ()', 'headspace');
	}
	
	function edit ($width, $area) {
		foreach ($this->fields AS $field) {
?>
<tr>
	<th width="<?php echo $width ?>" align="right"><?php echo $field ?>:</th>
	<td>
		<input type="text" name="headspace_custom_field[<?php echo $field ?>]" value="<?php if (isset ($this->values[$field])) echo htmlspecialchars ($this->values[$field])?>" style="width: 95%"/>
	</td>
</tr>
<?php
		}
	}
	
	function save ($data, $area) {
		$meta = '';
		if ( isset( $data['headspace_custom_field'] )  && is_array( $data['headspace_custom_field'] ) )
			$meta = array_filter( $data['headspace_custom_field'] );

		if ( !empty( $meta ) )
			return array( 'custom_fields' => serialize( $meta ) );
		return array( 'custom_fields' => '' );
	}

	
	function has_config () { return true; }
	
	function edit_options () {
		global $headspace2;
		if (count ($this->fields) == 0)
			$this->fields = array ('');

		$id = time ();
		?>
		<tr>
			<th width="50"><?php _e ('Fields', 'headspace'); ?>:</th>
			<td id="headspace_custom_fields<?php echo $id ?>">
				<?php if (count ($this->fields) > 0) : ?>
					<?php foreach ($this->fields AS $pos => $sheet) : ?>
					<input type="text" name="headspace_custom_field[]" value="<?php echo htmlspecialchars ($sheet) ?>" style="width: 90%"/>
					<?php if ($pos == 0) : ?>
					<a href="#" onclick="jQuery('#headspace_custom_fields<?php echo $id ?>').append ('<input type=&quot;text&quot; name=&quot;headspace_custom_field[]&quot; style=&quot;width: 90%&quot;/>'); return false">
						<img src="<?php echo $headspace2->url (); ?>/images/add.png" alt="add"/>
					</a>
					<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}
	
	function save_options ($data) {
		if (!is_array ($data['headspace_custom_field']))
			$data['headspace_custom_field'] = array ();
		return array ('fields' => array_filter ($data['headspace_custom_field']));
	}

	function file () {
		return basename (__FILE__);
	}
}
?>