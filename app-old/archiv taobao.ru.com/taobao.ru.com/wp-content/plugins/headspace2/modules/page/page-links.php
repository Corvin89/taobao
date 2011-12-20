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

class HSM_PageLinks extends HSM_Module {
	var $meta  = array();
	
	var $link_display = '%link%';
	var $before_text  = '';
	var $after_text   = '';
	
	var $pagelink_text     = '';
	var $pagelink_title    = '';
	var $pagelink_exclude  = '';
	var $pagelink_nofollow = false;
	var $pagelink_opennew  = false;
	
	function HSM_PageLinks ($options = array ()) {
		if ( isset( $options['before_text'] ) )
			$this->before_text = $options['before_text'];
			
		if ( isset( $options['after_text'] ) )
			$this->after_text = $options['after_text'];

		if ( isset( $options['link_display'] ) )
			$this->link_display = $options['link_display'];
	}
	
	function load ($meta) {
		if ( isset( $meta['pagelink'] ) && is_string( $meta['pagelink'] ) ) {
			$data = $this->unserialize( $meta['pagelink'] );
	
			if (isset ($data['text']))
				$this->pagelink_text = $data['text'];
			
			if (isset ($data['title']))
				$this->pagelink_title = $data['title'];
			
			if (isset ($data['exclude']))
				$this->pagelink_exclude = $data['exclude'];
			
			if (isset ($data['nofollow']))
				$this->pagelink_nofollow = $data['nofollow'];
			
			if (isset ($data['opennew']))
				$this->pagelink_opennew = $data['opennew'];
		}
	}
	
	function unserialize( $data ) {
		$data = unserialize( $data );
		if ( !is_array( $data ) )
			return unserialize( $data );
		return $data;
	}
	
	function head () {
		add_filter( 'wp_list_pages_excludes', array( &$this, 'wp_list_pages_excludes' ) );
		add_filter( 'wp_list_pages', array( &$this, 'wp_list_pages' ) );
	}
	
	function wp_list_pages_excludes( $exclude ) {
		// Get all HeadSpace page meta-data
		$this->meta = $this->get_page_links();
		
		// Add any to the exclude list
		foreach ( (array)$this->meta AS $id => $details ) {
			if ( $details['exclude'] )
				$exclude[] = $id;
		}
		
		return array_unique( $exclude );
	}
	
	function wp_list_pages( $html ) {
		// Apply them to the items
		$html = preg_replace_callback( '@<li class="page_item page-item-(\d*)(.*?)"><a(.*?)>(.*)?</a>@', array( &$this, 'insert_options' ), $html );

		// Add the before and after stuff
		$html = $this->before_text.$html.$this->after_text;
		
		// Link display - do this last
		if ( $this->link_display != '%link%' )
			$html = preg_replace_callback( '@<li(.*?)><a(.*?)</a>@', array( &$this, 'link_display' ), $html );

		return $html;
	}
	
	function insert_options( $matches ) {
		$id   = intval( $matches[1] );
		$attr = $matches[3];
		$text = $matches[4];
		
		if ( isset( $this->meta[$id] ) && !empty( $this->meta[$id]['text'] ) ) {
			$text = $this->meta[$id]['text'];
			
			if ( $this->meta[$id]['title'] != '' )
				$attr = preg_replace( '@title=".*?"@', 'title="'.$this->meta[$id]['title'].'"', $attr );
				
			if ( $this->meta[$id]['nofollow'] )
				$attr .= ' rel="nofollow"';

			if ( $this->meta[$id]['opennew'] )
				$attr .= ' target="_blank"';
		}
		
		return '<li class="page_item page-item-'.$matches[1].$matches[2].'"><a'.$attr.'>'.$text.'</a>';
	}
	
	function get_page_links() {
		global $wpdb;
		
		$items = array();
		$meta  = $wpdb->get_results( "SELECT {$wpdb->postmeta}.post_id,{$wpdb->postmeta}.meta_value FROM {$wpdb->postmeta},{$wpdb->posts} WHERE {$wpdb->postmeta}.meta_key='_headspace_pagelink' AND {$wpdb->posts}.ID={$wpdb->postmeta}.post_id AND {$wpdb->posts}.post_status='publish'" );
		foreach ( $meta AS $item ) {
			$items[$item->post_id] = $this->unserialize( $item->meta_value );
		}
		
		return $items;
	}
	
	function link_display( $matches ) {
		return '<li'.$matches[1].'>'.str_replace( '%link%', '<a'.$matches[2].'</a>', $this->link_display );
	}
	
	function name () {
		return __ ('Page Links', 'headspace');
	}
	
	function description () {
		return __ ('Allows options to be set for wp_list_pages links', 'headspace');
	}
	
	function edit ($width, $area) {
		global $post;
		
		if ( isset( $post ) && $post->post_type == 'page' ) {
?>
	<tr>
		<th width="<?php echo $width ?>" align="right"><?php _e( 'Page Link Text', 'headspace' )?>:</th>
		<td><input style="width: 95%" type="text" name="headspace_pagelink_text" value="<?php echo htmlspecialchars( $this->pagelink_text ); ?>"/>
		</td>
	</tr>
	<tr>
		<th width="<?php echo $width ?>" align="right"><?php _e( 'Page Link Title', 'headspace' )?>:</th>
		<td><input style="width: 95%" type="text" name="headspace_pagelink_title" value="<?php echo htmlspecialchars( $this->pagelink_title ); ?>"/>
		</td>
	</tr>
	<tr>
		<th width="<?php echo $width ?>" align="right"><?php _e( 'Page Link Options', 'headspace' )?>:</th>
		<td>
			<label><input type="checkbox" name="headspace_pagelink_exclude"  <?php if ( $this->pagelink_exclude ) echo ' checked="checked"'; ?>/> <small><?php _e( 'Exclude from list', 'headspace' )?></small></label> &nbsp;
			<label><input type="checkbox" name="headspace_pagelink_nofollow" <?php if ( $this->pagelink_nofollow ) echo ' checked="checked"'; ?>/> <small><?php _e( 'Nofollow', 'headspace' )?></small></label> &nbsp;
			<label><input type="checkbox" name="headspace_pagelink_opennew"  <?php if ( $this->pagelink_opennew ) echo ' checked="checked"'; ?>/> <small><?php _e( 'Open in new window', 'headspace' )?></small></label>
		</td>
	</tr>
<?php }
	}
	
	function save ($data, $area) {
		if ( isset( $data['headspace_pagelink_text'] ) ) {
			$meta = array(
				'text'     => $data['headspace_pagelink_text'],
				'title'    => $data['headspace_pagelink_title'],
				'exclude'  => isset( $data['headspace_pagelink_exclude'] ) ? true : false,
				'nofollow' => isset( $data['headspace_pagelink_nofollow'] ) ? true : false,
				'opennew'  => isset( $data['headspace_pagelink_opennew'] ) ? true : false
			);
		
			return array( 'pagelink' => serialize( $meta ) );
		}

		return array();
	}
	
	function file () {
		return basename (__FILE__);
	}
	
	function has_config () { return true; }
	
	function edit_options () {
		?>
		<tr>
			<th width="120"><?php _e ('Display', 'headspace'); ?>:</th>
			<td>
				<input type="text" name="link_display" value="<?php echo htmlspecialchars ($this->link_display) ?>"/><br/>
				<span class="sub"><?php _e ('How a link is displayed. Use %link% for the link itself', 'headspace'); ?></span>
			</td>
		</tr>
		<tr>
			<th width="120"><?php _e ('Before HTML', 'headspace'); ?>:</th>
			<td>
				<input type="text" name="before_text" value="<?php echo htmlspecialchars ($this->before_text) ?>"/><br/>
				<span class="sub"><?php _e ('HTML added to start of list', 'headspace'); ?></span>
			</td>
		</tr>
		<tr>
			<th width="120"><?php _e ('After HTML', 'headspace'); ?>:</th>
			<td>
				<input type="text" name="after_text" value="<?php echo htmlspecialchars ($this->after_text) ?>"/><br/>
				<span class="sub"><?php _e ('HTML added to end of list', 'headspace'); ?></span>
			</td>
		</tr>
		<?php
	}
	
	function save_options ($data) {
		return array
		(
			'link_display' => $data['link_display'],
			'before_text'  => $data['before_text'],
			'after_text'   => $data['after_text']
		);
	}
}
