<?php
/*
 * Get User Custom Field Values plugin shortcode code
 *
 * Copyright (c) 2004-2011 by Scott Reilly (aka coffee2code)
 *
 */

if ( ! class_exists( 'c2c_GetUserCustomFieldValuesShortcode' ) && class_exists( 'c2c_GetUserCustomWidget' ) ) :
class c2c_GetUserCustomFieldValuesShortcode {
	var $name = 'shortcode_get_user_custom_field_values';
	var $shortcode = 'user_custom_field';
	var $title = '';
	var $widget_handler = '';
	var $widget_base = '';

	function c2c_GetUserCustomFieldValuesShortcode( $widget_handler ) {
		$this->title = __( 'Get User Custom Field Values - Shortcode' );
		$this->widget_handler = $widget_handler;
		$this->widget_base = 'widget-' . $this->widget_handler->id_base;
		$this->shortcode = apply_filters( 'c2c_get_user_custom_field_values_shortcode', $this->shortcode );

		add_shortcode( $this->shortcode, array( &$this, 'shortcode' ) );
		add_action( 'admin_menu',        array( &$this, 'admin_menu' ) );
		add_action( 'admin_footer',      array( &$this, 'admin_js' ) );
	}

	function shortcode( $atts, $content = null ) {
		$defaults = array();
		foreach ( $this->widget_handler->config as $opt => $values )
			$defaults[$opt] = $values['default'];
		$atts2 = shortcode_atts( $defaults, $atts );
		foreach ( array_keys( $this->widget_handler->config ) as $key )
			$$key = $atts2[$key];
		$ret = '';

		if ( '0' === $user_id || $this_post )
			$user_id = 'current';

		if ( $user_id ) {
			if ( 'current' == $user_id )
				$ret = c2c_get_author_custom( $field, $before, $after, $none, $between, $before_last );
			else
				$ret = c2c_get_user_custom( $user_id, $field, $before, $after, $none, $between, $before_last );
		} else {
			$ret = c2c_get_current_user_custom( $field, $before, $after, $none, $between, $before_last );
		}
		return $ret;
	}

	function admin_menu() {
		add_meta_box( $this->name, $this->title, array( &$this, 'form' ), 'post', 'side' );
		add_meta_box( $this->name, $this->title, array( &$this, 'form' ), 'page', 'side' );
	}

	function admin_js() {
		global $pagenow;
		if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) )
			return;

		echo <<<JS
		<script type="text/javascript">
			var {$this->name} = function () {}

			{$this->name}.prototype = {
				options           : {},
				generateShortCode : function() {
					var content = this['options']['content'];
					delete this['options']['content'];

					var attrs = '';
					jQuery.each(this['options'], function(name, value){
						if (value != '') {
							attrs += ' ' + name + '="' + value + '"';
						}
					});
					return '[{$this->shortcode}' + attrs + ' /]';
				},
				sendToEditor      : function(f) {
					var collection = jQuery(f).find("input[id^={$this->widget_base}]:not(input:checkbox), \
										input[id^={$this->widget_base}]:checkbox:checked, \
										textarea[id^={$this->widget_base}]");
					var \$this = this;
					collection.each(function () {
						var name = this.name.substring(this.name.lastIndexOf('[')+1, this.name.length-1);
						if (\$this['options'][name] == undefined)
							\$this['options'][name] = this.value;
						else
							\$this['options'][name] += ', '+this.value;
					});
					send_to_editor(this.generateShortCode());
					/* Delete data after generating shortcode so that the form can be used to generate another shortcode */
					collection.each(function () {
						var name = this.name.substring(this.name.lastIndexOf('[')+1, this.name.length-1);
						delete \$this['options'][name];
					});
					return false;
				}
			}

			var admin_{$this->name} = new {$this->name}();
		</script>

JS;
	}

	function form() {
		$this->widget_handler->form( array(), array( 'title' ) );
		echo '<p class="submit">';
		echo '<input type="button" class="button-primary" onclick="return admin_' . $this->name . '.sendToEditor(this.form);" value="' . __( 'Send shortcode to editor' ) . '" />';
		echo '</p>';
	}

} // end class c2c_GetUserCustomFieldValuesShortcode

function register_c2c_GetUserCustomFieldValuesShortcode() {
	new c2c_GetUserCustomFieldValuesShortcode( $GLOBALS['wp_widget_factory']->widgets['c2c_GetUserCustomWidget'] );
}
add_action( 'init', 'register_c2c_GetUserCustomFieldValuesShortcode', 11 );

endif; // end if !class_exists()
?>