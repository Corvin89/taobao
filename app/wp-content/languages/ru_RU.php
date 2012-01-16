<?php
/**
 * Localized default SECRET_KEY string.
 *
 * @since 2.9.2
 * @link http://core.trac.wordpress.org/ticket/12081
 */
if ( empty( $current_user ) ) // See http://core.trac.wordpress.org/ticket/14024
	$wp_default_secret_key = 'впишите сюда уникальную фразу';
else
	$wp_default_secret_key = 'put your unique phrase here';

/**
 * Correct layout issues with overlapping strings.
 *
 * @since 2.7.0
 */
function ru_accomodate_markup() {
	wp_enqueue_style( 'ru_RU', content_url( 'languages/ru_RU.css' ), array(), '20111213', 'all' );
}
add_action('admin_enqueue_scripts', 'ru_accomodate_markup');

/**
 * Restore $wp_scripts l10n if some plugin called wp_register_script() too early.
 *
 * @since 2.9.0
 * @link http://core.trac.wordpress.org/ticket/11526
 */
function ru_restore_scripts_l10n() {
	global $wp_scripts;

	if ( is_a( $wp_scripts, 'WP_Scripts' ) )
		do_action_ref_array( 'wp_default_scripts', array( &$wp_scripts ) );
}
add_action('init', 'ru_restore_scripts_l10n', 9);

/**
 * Predefine some options specific to ru_RU package.
 *
 * @since 2.9.2
 */
function ru_populate_options() {
	add_option('rss_language', 'ru');
}
add_action('populate_options', 'ru_populate_options');

/**
 * Extend Press This width to make room for translated strings.
 *
 * @since 3.1.0
 */
function ru_extend_press_this() {
	global $hook_suffix;

	if ( 'press-this.php' == $hook_suffix ) : ?>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready( function() {
	window.resizeTo(765, 580);
});
/* ]]> */
</script>
<?php
	endif;
}
add_action('admin_print_footer_scripts', 'ru_extend_press_this');

/**
 * Add Russian language to TinyMCE spellchecker and translate other language names.
 *
 * @since 3.3.0
 */
function ru_add_spellchecker() {
	return '+Русский=ru,Английский=en,Датский=da,Испанский=es,Итальянский=it,Немецкий=de,Нидерландский=nl,Польский=pl,Португальский=pt,Финский=fi,Французский=fr,Шведский=sv';
}
add_filter('mce_spellchecker_languages', 'ru_add_spellchecker');
?>