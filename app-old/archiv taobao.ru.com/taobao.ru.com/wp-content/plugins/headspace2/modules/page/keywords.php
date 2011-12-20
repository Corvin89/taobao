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

class HSM_Keywords extends HSM_Module
{
	var $metakey    = null;
	var $use_tags   = true;
	var $max_length = 0;
	
	function HSM_Keywords ($options = array ()) {
		if ( isset( $options['use_tags'] ) )
			$this->use_tags = $options['use_tags'] ? true : false;
			
		if ( isset( $options['max_length'] ) )
			$this->max_length = $options['max_length'];
	}
	
	function load ($data) {
		if ((!$this->use_tags || !class_exists ('HSM_Tags')) && isset( $data['metakey']) )
			$this->metakey = $data['metakey'];
	}
	
	function head () {
		if ($this->use_tags && class_exists ('HSM_Tags') && $this->metakey == '') {
			$hs = HeadSpace2::get ();
			$tags = $hs->modules->get ('hsm_tags');
			
			$this->metakey = $tags->normalize_tags ($tags->get_the_tags ());
		}

		if ($this->metakey)
		  echo '<meta name="keywords" content="'.$this->metakey.'" />'."\r\n";
	}
	
	function can_quick_edit () { return true; }
	
	function quick_view () {
		echo $this->metakey;
	}
	
	function name () {
		return __ ('Keywords', 'headspace');
	}
	
	function description () {
		return __ ('Allows meta keywords to be defined, seperate from tags (if necessary, disable keyword display in the Tags module)', 'headspace');
	}
	
	function has_config () { return true; }

	function edit_options () {
		?>
		<tr>
			<th width="80"><?php _e ('Use tags', 'headspace'); ?>:</th>
			<td>
				<input type="checkbox" name="use_tags"<?php if ($this->use_tags) echo ' checked="checked"' ?>/>
				<span class="sub"><?php _e ('Checking this will mean that your tags are also used as keywords and you will not be able to modify keywords independently', 'headspace'); ?></span>
			</td>
		</tr>
		<tr>
			<th><?php _e ('Max length', 'headspace'); ?>:</th>
			<td>
				<input type="text" name="max_length" size="5" value="<?php echo $this->max_length ?>"/>
				<span class="sub"><?php _e ('Keywords will be trimmed to this length', 'headspace'); ?></span>
			</td>
		</tr>
		<?php
	}
	
	function save_options ($data) {
		return array
		(
			'use_tags'   => isset ( $data['use_tags'] ) ? true : false,
			'max_length' => intval( $data['max_length'] )
		);
	}

	function edit ($width, $area) {
		$id = time();
		if ($this->use_tags === false || !class_exists ('HSM_Tags')) {
?>
<tr>
	<th width="<?php echo $width ?>" align="right">
		<?php if ($area == 'page') : ?>
		<a href="#update" onclick="copy_keywords(jQuery('input[name=headspace_metakey]'));return false;">
		<?php endif;?>
		<?php _e ('Keywords', 'headspace') ?>:
		<?php if ($area == 'page') : ?>
		</a>
		<?php endif; ?>
	</th>
	<td>
		<input type="text" name="headspace_metakey" style="width: 95%" value="<?php echo htmlspecialchars ($this->metakey) ?>" id="keywords_<?php echo $id; ?>"/>
		
		<script type="text/javascript" charset="utf-8">
			jQuery('#keywords_<?php echo $id ?>').Counter( { limit: <?php echo $this->max_length; ?>, remaining: '<?php echo esc_js( __( 'remaining', 'headspace' ) )?>' } );
		</script>
	</td>
</tr>
<?php
		}
	}
	
	function save ($data, $area) {
		if (isset($data['headspace_metakey']))
			return array ('metakey' => $data['headspace_metakey']);
		return array();
	}
	
	function file () {
		return basename (__FILE__);
	}
}