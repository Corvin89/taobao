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

class HSM_RelativeLinks extends HSM_Module {
	var $rellink_next   = '';
	var $rellink_prev   = '';
	var $rellink_parent = '';
	var $rellink_start  = '';
	var $rellink_end    = '';
	
	var $reloaded = false;
	
	function load ($data) {
		if ( isset( $data['rellink_next'] ) )
			$this->rellink_next = $data['rellink_next'];

		if ( isset( $data['rellink_prev'] ) )
			$this->rellink_prev = $data['rellink_prev'];

		if ( isset( $data['rellink_parent'] ) )
			$this->rellink_parent = $data['rellink_parent'];

		if ( isset( $data['rellink_start'] ) )
			$this->rellink_start = $data['rellink_start'];

		if ( isset( $data['rellink_end'] ) )
			$this->rellink_end = $data['rellink_end'];
	}
	
	function run() {
		add_filter( 'parent_post_rel_link', array( &$this, 'parent_post_rel_link' ) );
		add_filter( 'start_post_rel_link', array( &$this, 'start_post_rel_link' ) );
		add_filter( 'end_post_rel_link', array( &$this, 'end_post_rel_link' ) );
		add_filter( 'next_post_rel_link', array( &$this, 'next_post_rel_link' ) );
		add_filter( 'previous_post_rel_link', array( &$this, 'prev_post_rel_link' ) );
	}
	
	function do_link( $link, $var ) {
		if ( $this->reloaded == false )
			HeadSpace2::reload ($this);

		$post_id = $this->$var;
		if ( $post_id > 0 ) {

			$post = get_post( $post_id );
			if ( !empty( $post ) ) {
				$link = preg_replace( "/href='(.*?)'/", 'href="'.get_permalink( $post_id ).'"', $link );
				$link = preg_replace( "/title='(.*?)'/", 'title="'.$post->post_title.'"', $link );
			}
		}
		
		return $link;
	}
	
	function parent_post_rel_link( $link ) {
		return $this->do_link( $link, 'rellink_parent' );
	}

	function start_post_rel_link( $link ) {
		return $this->do_link( $link, 'rellink_start' );
	}

	function end_post_rel_link( $link ) {
		return $this->do_link( $link, 'rellink_end' );
	}

	function next_post_rel_link( $link ) {
		return $this->do_link( $link, 'rellink_next' );
	}

	function prev_post_rel_link( $link ) {
		return $this->do_link( $link, 'rellink_prev' );
	}
	
	function name () {
		return __ ('Relative Links', 'headspace');
	}
	
	function description () {
		return __ ('Allows options to be set for relative meta links (WP 2.8+)', 'headspace');
	}
	
	function edit ($width, $area) {
?>
	<tr>
		<th width="<?php echo $width ?>" align="right"><?php _e( 'Relative Links', 'headspace' )?>:</th>
		<td>
			<label>
				<small><?php _e( 'Previous', 'headspace' )?>:</small>
				<input size="5" type="text" name="headspace_rellink_prev" value="<?php echo htmlspecialchars( $this->rellink_prev ); ?>"/>
			</label>
			
			<label>
				<small><?php _e( 'Next', 'headspace' )?>:</small>
				<input size="5" type="text" name="headspace_rellink_next" value="<?php echo htmlspecialchars( $this->rellink_next ); ?>"/>
			</label>
			
			<label>
				<small><?php _e( 'Start', 'headspace' )?>:</small>
				<input size="5" type="text" name="headspace_rellink_start" value="<?php echo htmlspecialchars( $this->rellink_start ); ?>"/>
			</label>

			<label>
				<small><?php _e( 'End', 'headspace' )?>:</small>
				<input size="5" type="text" name="headspace_rellink_end" value="<?php echo htmlspecialchars( $this->rellink_end ); ?>"/>
			</label>

			<label>
				<small><?php _e( 'Parent', 'headspace' )?>:</small>
				<input size="5" type="text" name="headspace_rellink_parent" value="<?php echo htmlspecialchars( $this->rellink_parent ); ?>"/>
			</label>
		</td>
	</tr>
<?php
	}
	
	function save ($data, $area) {
		return array(
			'rellink_prev'   => intval( $data['headspace_rellink_prev'] ),
			'rellink_next'   => intval( $data['headspace_rellink_next'] ),
			'rellink_start'  => intval( $data['headspace_rellink_start'] ),
			'rellink_end'    => intval( $data['headspace_rellink_end'] ),
			'rellink_parent' => intval( $data['headspace_rellink_parent'] ),
		);
	}
	
	function file () {
		return basename (__FILE__);
	}
}
