<?php
/**
 * @package c2c_GetUserCustomWidget
 * @author Scott Reilly
 * @version 003
 */
/*
 * Get User Custom Field Values plugin widget code
 *
 * Copyright (c) 2004-2011 by Scott Reilly (aka coffee2code)
 *
 */

if ( ! class_exists( 'c2c_GetUserCustomWidget' ) ) :

require_once( 'c2c-widget.php' );

class c2c_GetUserCustomWidget extends C2C_Widget_005 {

	/**
	 * Constructor
	 */
	function c2c_GetUserCustomWidget() {
		$this->C2C_Widget_005( 'get-user-custom', __FILE__, array( 'width' => 300 ) );
		add_filter( $this->get_hook( 'excluded_form_options' ), array( &$this, 'excluded_form_options' ) );
	}

	/**
	 * Initializes the plugin's configuration and localizable text variables.
	 *
	 * @return void
	 */
	function load_config() {
		$this->title       = __( 'Get User Custom Field', $this->textdomain );
		$this->description = __( 'A list of custom field value(s) from user(s).', $this->textdomain );

		$this->config = array(
			// input can be 'checkbox', 'multiselect', 'select', 'short_text', 'text', 'textarea', 'hidden', or 'none'
			// datatype can be 'array' or 'hash'
			// can also specify input_attributes
			'title' => array( 'input' => 'text', 'default' => __( 'User Custom Field', $this->textdomain ),
					'label' => __( 'Title', $this->textdomain ) ),
			'field' => array( 'input' => 'text', 'default' => '',
					'label' => __( 'Custom field key', $this->textdomain ),
					'help' => __( '<strong>*Required.</strong>  The name of the custom field key whose value you wish to have displayed.', $this->textdomain ) ),
			'this_post' => array( 'input' => 'checkbox', 'default' => false,
					'label' => __( 'This post\'s author?', $this->textdomain ),
					'help' => __( 'For the author of the post containing this shortcode. Takes precedence over \'User ID\'', $this->textdomain ) ),
			'user_id' => array( 'input' => 'short_text', 'default' => '',
					'label' => __( 'User ID', $this->textdomain ),
					'help' => __( 'ID of user whose custom field\'s value you want to display. Leave blank to search for the custom field for the currently logged in user. Use <code>0</code> to indicate it should only work on the permalink page for a page/post.', $this->textdomain ) ),
			'before' => array( 'input' => 'text', 'default' => '',
					'label' => __( 'Before text', $this->textdomain ),
					'help' => __( 'Text to display before the custom field.', $this->textdomain ) ),
			'after' => array( 'input' => 'text', 'default' => '',
					'label' => __( 'After text', $this->textdomain ),
					'help' => __( 'Text to display after the custom field.', $this->textdomain ) ),
			'none' => array( 'input' => 'text', 'default' => '',
					'label' => __( 'None text', $this->textdomain ),
					'help' => __( 'Text to display if no matching custom field is found (or it has no value). Leave this blank if you don\'t want anything to display when no match is found.', $this->textdomain ) ),
			'between' => array( 'input' => 'text', 'default' => ', ',
					'label' => __( 'Between text', $this->textdomain ),
					'help' => __( 'Text to display between custom field items if more than one are being shown.', $this->textdomain ) ),
			'before_last' => array( 'input' => 'text', 'default' => '',
					'label' => __( 'Before last text', $this->textdomain ),
					'help' => __( 'Text to display between the second to last and last custom field items if more than one are being shown.', $this->textdomain ) )
		);
	}

	/**
	 * Outputs the body of the widget
	 *
	 * @param array $args Widget args
	 * @param array $instance Widget instance
	 * @param array $settings Widget settings
	 * @return void (Text is echoed.)
	 */
	function widget_body( $args, $instance, $settings ) {
		extract( $args );
		extract( $settings );

		// Determine, based on inputs given, which template tag to use.
		if ( '0' === $user_id || $this_post )
			$user_id = 'current';

		if ( $user_id ) {
			if ( 'current' == $user_id )
				echo c2c_get_author_custom( $field, $before_title . $title . $after_title . $before, $after . $after_widget, $none, $between, $before_last );
			else
				echo c2c_get_user_custom( $user_id, $field, $before, $after, $none, $between, $before_last );
		} else {
			echo c2c_get_current_user_custom( $field, $before, $after, $none, $between, $before_last );
		}
	}

	/**
	 * Validates widget instance values
	 *
	 * @param array $instance Array of widget instance values
	 * @return array The filtered array of widget instance values
	 */
	function validate( $instance ) {
		$instance['field'] = trim( $instance['field'] );
		return $instance;
	}

	/**
	 * Defines widget form options that shouldn't be shown by default (since they are used for the shortcode widget)
	 *
	 * @param array $excluded_form_options Array of form options that shouldn't be shown
	 * @return array The array of form options that shouldn't be shown
	 */
	function excluded_form_options( $excluded_form_options ) {
		if ( $excluded_form_options === null )
			$excluded_form_options = array( 'this_post' );
		return $excluded_form_options;
	}

} // end class c2c_GetUserCustomWidget

function register_c2c_GetUserCustomWidget() {
	register_widget( 'c2c_GetUserCustomWidget' );
}
add_action( 'widgets_init', 'register_c2c_GetUserCustomWidget' );

endif; // end if !class_exists()
?>