<?php
/**
 * @package Get_User_Custom_Field_Values
 * @author Scott Reilly
 * @version 2.5.1
 */
/*
Plugin Name: Get User Custom Field Values
Version: 2.5.1
Plugin URI: http://coffee2code.com/wp-plugins/get-user-custom-field-values/
Author: Scott Reilly
Author URI: http://coffee2code.com
Description: Easily retrieve and control the display of any custom field values/meta data for the currently logged in user or any specified user.

Compatible with WordPress 2.8+, 2.9+, 3.0+, 3.1+, 3.2+.

=>> Read the accompanying readme.txt file for instructions and documentation.
=>> Also, visit the plugin's homepage for additional information and updates.
=>> Or visit: http://wordpress.org/extend/plugins/get-user-custom-field-values/

TODO
	* Create hooks to allow disabling shortcode, shortcode builder, and widget support
	* Create method to handle args passing (or adapt existing functions to prefer it and deprecate older arguments)
	* Support getting random custom field values
	* Support specifying a limit on the number of custom field values returned
	* Facilitate conditional output, maybe via c2c_get_user_custom_if() where text is only output if post
	  has the custom field AND it equals a specified value (or one of an array of possible values)
	  echo c2c_get_user_custom_if( 'size', array( 'XL', 'XXL' ), 'Sorry, this size is out of stock.' );
	* Introduce a 'format' shortcode attribute and template tag argument.  Defines the output format for each
	  matching custom field, i.e. c2c_get_user_custom(..., $format = 'Size %key% has %value%' in stock.')
	* Support specifying $field as array or comma-separated list of custom fields.
	* Create args array alternative template tag: c2c_user_custom_field( $field, $args = array() ) so features
	  can be added and multiple arguments don't have to be explicitly provided.  Perhaps transition c2c_get_user_custom()
	  in plugin v3.0 and detect args.
	  function c2c_get_user_custom( $field, $args = array() ) {
	    if ( ! empty( $args ) && ! is_array( $args ) ) // Old style usage
	      return c2c_old_get_user_custom( $field, ... ); // Or: $args = c2c_get_user_custom_args_into_array( ... );
	    // Do new handling here.
	  }
	* Support name filters to run against found custom fields
	  c2c_get_user_custom( 'favorite_site', array( 'filters' => array( 'strtoupper', 'make_clickable' ) ) )
	* Since it's shifting to args array, might as well support 'echo'

*/

/*
Copyright (c) 2006-2011 by Scott Reilly (aka coffee2code)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy,
modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

include( dirname( __FILE__ ) . '/get-user-custom.widget.php' );
include( dirname( __FILE__ ) . '/get-user-custom.shortcode.php' );

if ( ! function_exists( 'c2c_get_current_user_custom' ) ):
/**
 * Access custom fields for the currently logged in user.
 * If the current visitor is NOT logged in, then the $none value is returned.
 *
 * @param string $field The name/key value of the custom field
 * @param string $before (optional) Text to appear before all the custom field value(s) if a value exists. Default is ''.
 * @param string $after (optional) Text to appear after all the custom field value if a value(s) exists. Default is ''.
 * @param string $none (optional) 	The text to display in place of the field value should no field value exists; if defined
 *               as '' and no field value exists, then nothing (including no $before and $after) gets displayed.  Default is ''.
 * @param string $between (optional) 	The text to display between multiple occurrences of the custom field; if defined as '',
 *               then only the first instance will be used.  Default is ''.
 * @param string $before_last (optional) The text to display between the next-to-last and last items listed when multiple occurrences of
 *               the custom field exist; $between MUST be set to something other than '' for this to take effect.  Default is ''.
 * @return string The value for the specified custom field
 */
function c2c_get_current_user_custom( $field, $before='', $after='', $none='', $between='', $before_last='' ) {
	$user = wp_get_current_user();

	return c2c_get_user_custom( ( isset( $user->ID ) ? (int) $user->ID : 0 ), $field, $before, $after, $none, $between, $before_last );
}
add_filter( 'c2c_get_current_user_custom', 'c2c_get_current_user_custom', 10, 6 );
endif;

if ( ! function_exists( 'c2c_get_author_custom' ) ) :
/**
 * Access custom fields for the current author (when on the permalink page for a post, page, or in a loop),
 *
 * @param string $field The name/key value of the custom field
 * @param string $before (optional) Text to appear before all the custom field value(s) if a value exists. Default is ''.
 * @param string $after (optional) Text to appear after all the custom field value if a value(s) exists. Default is ''.
 * @param string $none (optional) 	The text to display in place of the field value should no field value exists; if defined
 *               as '' and no field value exists, then nothing (including no $before and $after) gets displayed.  Default is ''.
 * @param string $between (optional) 	The text to display between multiple occurrences of the custom field; if defined as '',
 *               then only the first instance will be used.  Default is ''.
 * @param string $before_last (optional) The text to display between the next-to-last and last items listed when multiple occurrences of
 *               the custom field exist; $between MUST be set to something other than '' for this to take effect.  Default is ''.
 * @return string The value for the specified custom field
 */
function c2c_get_author_custom( $field, $before='', $after='', $none='', $between='', $before_last='' ) {
	global $authordata;

	if ( is_single() || is_page() || in_the_loop() )
		return c2c_get_user_custom( ( isset( $authordata->ID ) ? (int) $authordata->ID : 0 ), $field, $before, $after, $none, $between, $before_last );
}
add_filter( 'c2c_get_author_custom', 'c2c_get_author_custom', 10, 6 );
endif;

if ( ! function_exists( 'c2c_get_user_custom' ) ) :
/**
 * Access custom fields for any user specified by the $user_id value.
 *
 * @param int $user_id The user ID
 * @param string $field The name/key value of the custom field
 * @param string $before (optional) Text to appear before all the custom field value(s) if a value exists. Default is ''.
 * @param string $after (optional) Text to appear after all the custom field value if a value(s) exists. Default is ''.
 * @param string $none (optional) 	The text to display in place of the field value should no field value exists; if defined
 *               as '' and no field value exists, then nothing (including no $before and $after) gets displayed.  Default is ''.
 * @param string $between (optional) 	The text to display between multiple occurrences of the custom field; if defined as '',
 *               then only the first instance will be used.  Default is ''.
 * @param string $before_last (optional) The text to display between the next-to-last and last items listed when multiple occurrences of
 *               the custom field exist; $between MUST be set to something other than '' for this to take effect.  Default is ''.
 * @return string The value for the specified custom field
 */
function c2c_get_user_custom( $user_id, $field, $before='', $after='', $none='', $between='', $before_last='' ) {
	global $wpdb;

	if ( empty( $field ) )
		return;

	$values = array();
	$meta_values = function_exists( 'get_user_meta' ) ?
		get_user_meta( $user_id, $field ) :
		$wpdb->get_col( $wpdb->prepare( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = %d AND meta_key = %s", $user_id, $field ) );

	if ( empty( $between ) )
		$meta_values = array_slice( $meta_values, 0, 1 );

	if ( ! empty( $meta_values ) )
		foreach ( $meta_values as $meta ) {
			$meta = apply_filters( "the_user_meta_$field", $meta );
			$values[] = apply_filters( 'the_user_meta', $meta );
		}

	if ( empty( $values ) ) {
		$value = '';
	} else {
		$values = array_map( 'trim', $values );
		if ( empty( $before_last ) ) {
			$value = implode( $values, $between );
		} else {
			switch ( $size = sizeof( $values ) ) {
				case 1:
					$value = $values[0];
					break;
				case 2:
					$value = $values[0] . $before_last . $values[1];
					break;
				default:
					$value = implode( array_slice( $values, 0, $size-1 ), $between ) . $before_last . $values[$size-1];
			}
		}
	}

	if ( empty( $value ) ) {
		if ( empty( $none ) )
			return;
		$value = $none;
	}

	return $before . $value . $after;
}
add_filter( 'c2c_get_user_custom', 'c2c_get_user_custom', 10, 7 );
endif;

// Some filters you may wish to perform: (these are filters typically done to 'the_content' (post content))
//add_filter('the_user_meta', 'convert_chars');
//add_filter('the_user_meta', 'wptexturize');

// Other optional filters (you would need to obtain and activate these plugins before trying to use these)
//add_filter('the_user_meta', 'c2c_hyperlink_urls', 9);
//add_filter('the_user_meta', 'text_replace', 2);
//add_filter('the_user_meta', 'textile', 6);

?>