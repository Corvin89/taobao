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

class HSM_NoIndex extends HSM_Module
{
	var $noindex = false;
	var $nofollow = false;
	var $noarchive = false;
	var $noodp = false;
	var $noydir = false;
	
	function names () {
		return array
		(
			'noindex'   => __ ('No-index', 'headspace'),
			'nofollow'  => __ ('No-follow', 'headspace'),
			'noarchive' => __ ('No-archive', 'headspace'),
			'noodp'     => __ ('No-ODP', 'headspace'),
			'noydir'    => __ ('No-Yahoo Dir', 'headspace')
		);
	}
	
	function load ($meta) {
		// Extract settings from $meta and $options
		foreach ($this->names () AS $name => $title) {
			if (isset ($meta[$name]))
				$this->$name = ($meta[$name] == 'robots') ? true : false;
		}
	}
	
	function can_quick_edit () {
		return true;
	}
	
	function quick_view () {
		$options = array ();
		
		foreach ($this->names () AS $name => $title) {
			if ($this->$name === true)
				$options[] = $name;
		}
		
		if (count ($options) > 0)
			echo implode (', ', $options);
	}
	
	function head () {
		$options = array ();
		
		foreach ($this->names () AS $name => $title) {
			if ($this->$name === true)
				$options[] = $name;
		}
		
		if (count ($options) == 1 && $this->noindex)
			$options[] = 'follow';
		
		if (count ($options) > 0)
			echo '<meta name="robots" content="'.implode (',', $options).'"/>'."\r\n";
	}
	
	function name () {
		return __ ('Meta-Robots', 'headspace');
	}
	
	function description () {
		return __ ('Allows various meta-robot options to be set to prevent search engines and robots from indexing or following pages', 'headspace');
	}
	
	function is_restricted ($area) {
		if (current_user_can ('administrator'))
			return false;
		return true;
	}
	
	function edit ($width, $area) {
?>
<tr>
	<th width="<?php echo $width ?>" align="right"><?php _e ('Meta-Robots', 'headspace') ?>:</th>
	<td>
		<?php foreach ($this->names () AS $name => $title) : ?>
			<label style="line-height: 1.8">
				<input type="checkbox" name="headspace_<?php echo $name; ?>"<?php if ($this->$name) echo ' checked="checked"' ?>/>
				<?php echo $title ?> 
			</label>&nbsp;
		<?php endforeach; ?>
	</td>
</tr>
<?php
	}
	
	function save ($data, $area) {
		$save = array ();
		foreach ($this->names () AS $name => $title)
			$save[$name] = isset ($data['headspace_'.$name]) ? 'robots' : '';
			
		return $save;
	}
	
	function file () {
		return basename (__FILE__);
	}
}

?>