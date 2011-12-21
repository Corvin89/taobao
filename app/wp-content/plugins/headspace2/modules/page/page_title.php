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

class HSM_PageTitle extends HSM_Module
{
	var $page_title  = null;
	var $separator   = '';
	var $force       = false;
	var $position    = 'before';
	var $max_length  = 0;
	
	function HSM_PageTitle ($options = array ()) {
		if (isset ($options['separator']))
			$this->separator = $options['separator'];
		
		if (isset ($options['position']))
			$this->position = $options['position'];

		if (isset ($options['force']))
			$this->force = $options['force'];
			
		if ( isset( $options['max_length'] ) )
			$this->max_length = $options['max_length'];
	}
	
	function run () {
		add_filter ('wp_title', array (&$this, 'wp_title'), 1, 3);
		
		if ($this->force)
			ob_start (array (&$this, 'brute_force_title'));
	}
	
	function brute_force_title ($page) {
		return preg_replace_callback ('@<title>(.*?)</title>@s', array (&$this, 'replace_title'), $page);
	}
	
	function replace_title ($matches) {
		return '<title>'.trim ($this->wp_title ($matches[1])).'</title>';
	}
	
	
	function load ($meta) {
		if (isset ($meta['page_title']))
			$this->page_title = $meta['page_title'];
	}
	
	
	/**
	 * Insert re-configured site title
	 *
	 * @return void
	 **/
	
	function wp_title ($title, $separator = '', $location = '') {
		HeadSpace2::reload ($this);

		$sep = $separator;
		if ($this->separator != '')
			$sep = $this->separator;

		$replace = $separator;
		if ($replace == ' ')
			$replace = '';

		if (strlen ($this->page_title) == 0 && strlen ($title) == 0)
			$title = '';
		else if ($this->position == 'after' || $location == 'right') {
			if (strlen ($this->page_title) > 0)
				$title = $this->page_title." ".$sep." ";
			else
				$title = trim (str_replace ($replace, '', $title))." ".$sep." ";
		}
		else
		{
			// Before
			if (strlen ($this->page_title) > 0)
				$title = " ".$sep." ".$this->page_title;
			else
				$title = $sep." ".trim (str_replace ($replace, '', $title)).'';
		}

		return $title;
	}
	
	function name () {
		return __ ('Page title', 'headspace');
	}
	
	function can_quick_edit () { return true; }
	function quick_view () {
		echo $this->page_title;
	}
	
	function description () {
		return __ ('Allow page title to be changed (i.e. the title in the browser window title)', 'headspace');
	}
	
	function has_config () { return true; }
	
	function edit_options () {
		?>
		<tr>
			<th width="120"><?php _e ('Title separator', 'headspace'); ?>:</th>
			<td>
				<input class="text" type="text" name="separator" size="5" value="<?php esc_attr_e( $this->separator ) ?>"/>
				<span class="sub"><?php _e ('Leave blank to use theme default', 'headspace'); ?></span>
			</td>
		</tr>
		<tr>
			<th width="120"><?php _e ('Separator position', 'headspace'); ?>:</th>
			<td>
				<select name="position">
					<option value="before"<?php if ($this->position == 'after') echo ' selected="selected"' ?>><?php _e ('Before', 'headspace'); ?></option>
					<option value="after"<?php if ($this->position == 'after') echo ' selected="selected"' ?>><?php _e ('After', 'headspace'); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<th width="120"><?php _e ('Force title rewrite', 'headspace'); ?></th>
			<td>
				<label>
					<input type="checkbox" name="force"<?php if ($this->force) echo ' checked="checked"' ?>/>
				</label>

				<span class="sub">
					<?php _e ('This will cache your page and brute-force change the title.  While this is convienent because you don\'t need to change your theme it does lead to increased memory usage and a reduction in performance.', 'headspace'); ?>
				</span>
			</td>
		</tr>
		<tr>
			<th><?php _e ('Max length', 'headspace'); ?>:</th>
			<td>
				<input type="text" name="max_length" size="5" value="<?php echo $this->max_length ?>"/>
			</td>
		</tr>
		<?php
	}
	
	function save_options ($data) {
		return array(
			'separator' => $data['separator'],
			'position'  => $data['position'],
			'force'     => isset( $data['force'] ) ? true : false,
			'max_length' => intval( $data['max_length'] )
		);
	}
	
	function edit ($width, $area) {
		$id = time();
	?>
	<tr>
		<th width="<?php echo $width ?>" align="right" valign="top">
			<?php if ($area == 'page') : ?>
			<a href="#update" onclick="jQuery('input[name=headspace_page_title]').val(jQuery('#title').val ());return false;">
			<?php endif; ?>
			<?php _e ('Page Title', 'headspace') ?>:
			<?php if ($area == 'page') : ?>
			</a>
			<?php endif; ?>
		</th>
		<td>
			<input class="text" type="text" name="headspace_page_title" value="<?php echo esc_attr( $this->page_title ) ?>" style="width: 95%" id="title_<?php echo $id; ?>"/>
			
			<?php if ( $this->max_length > 0 ) : ?>
				<script type="text/javascript" charset="utf-8">
					jQuery('#title_<?php echo $id ?>').Counter( { limit: <?php echo $this->max_length; ?>, remaining: '<?php echo esc_js( __( 'remaining', 'headspace' ) )?>' } );
				</script>
			<?php endif; ?>
		</td>
	</tr>
	<?php
	}
	
	function save ($data, $area) {
		return array ('page_title' => trim ($data['headspace_page_title']));
	}
	
	function file () {
		return basename (__FILE__);
	}
}
?>