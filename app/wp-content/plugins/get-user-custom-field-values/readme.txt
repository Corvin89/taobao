=== Get User Custom Field Values ===
Contributors: coffee2code
Donate link: http://coffee2code.com/donate
Tags: user, custom field, user meta, widget, shortcode, coffee2code
Requires at least: 2.6
Tested up to: 3.2
Stable tag: 2.5.1
Version: 2.5.1

Easily retrieve and control the display of any custom field values/meta data for the currently logged in user or any specified user.


== Description ==

Easily retrieve and control the display of any custom field values/meta data for the currently logged in user or any specified user.

This plugin provides functionality similar to the [Get Custom Field Values](http://coffee2code.com/wp-plugins/get-custom-field-values/) plugin, but for user custom fields (which WordPress manages in a separate database table).

This plugin does NOT help you in setting user custom field values, nor does it provide an interface to list or otherwise manage user custom fields.

The list of useful user custom field values that are provided by default in WordPress are:

* first_name
* last_name
* nickname
* description
* aim
* yim
* jabber

It is up to other plugins or custom code to add additional user custom fields that you may then be able to retrieve with this plugin.

Links: [Plugin Homepage](http://coffee2code.com/wp-plugins/get-user-custom-field-values/) | [Author Homepage](http://coffee2code.com)


== Screenshots ==

1. A screenshot of the 'Get User Custom' widget.
2. A screenshot of the 'Get User Custom' shortcode builder.


== Installation ==

1. Unzip `get-user-custom.zip` inside the `/wp-content/plugins/` directory (or install via the built-in WordPress plugin installer)
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. (Optional) Add filters for 'the_user_meta' to filter user custom field data (see the end of the file for commented out samples you may wish to include).  And/or add per-meta filters by hooking 'the_user_meta_$field'
1. Give a user a custom field with a value, or have user custom fields already defined.  (This generally entails use of plugin(s) that utilize the user custom fields feature built into WordPress. By default, in a practical sense WordPress only sets the 'first_name', 'last_name', and 'nickname' user custom fields, so you could try using one of them, even if just for testing even though WordPress provides functions to get those particular fields.)
1. Use the provided 'Get User Custom' widget  -or-
Use the available shortcode in a post or page  -or-
Use the function `c2c_get_current_user_custom()` if you wish to access user custom fields for the currently logged
in user.  Use the function `c2c_get_user_custom()` to access user custom fields for a specified user.  User the function
`c2c_get_author_custom()` to access custom fields for the current author (when on the permalink page for a post, page, or
in a loop).  Prepend either of the three mentioned functions with 'echo' to display the contents of the custom field; or
use the return value as an argument to another function.


== Frequently Asked Questions ==

= How do I assign users custom fields so that I can retrieve them using this plugin? =

The user profile page within WordPress provides inputs for a handful of user custom fields (first_name, last_name, aim, yim, jabber, description, etc).  However, you're probably more interested in creating your own user custom fields.  In that case, you'll have to use another plugin to store custom fields for users, or directly use WordPress functions manually.

= I don't plan on using the shortcode builder when writing or editing a post or page, so how do I get rid of it? =

When on the Write or Edit admin pages for a page or post, find the "Screen Options" link near the upper right-hand corner.  Clicking it slides down a panel of options.  In the "Show on screen" section, uncheck the checkbox labeled "Get User Custom Field Values - Shortcode".  This must be done separately for posts and for pages if you want the shortcode builder disabled for both sections.


== Template Tags ==

The plugin provides three optional template tags for use in your theme templates.

= Functions =

* `<?php function c2c_get_current_user_custom( $field, $before='', $after='', $none='', $between='', $before_last='' ) ?>`
This allows access to custom fields for the currently logged in user.  If the current visitor is NOT logged in, then the `$none` value is returned.

* `<?php function c2c_get_author_custom( $field, $before='', $after='', $none='', $between='', $before_last='' ) ?>`
This allows access to custom fields for the current author (when on the permalink page for a post, page, or in a loop).

* `<?php function c2c_get_user_custom( $user_id, $field, $before='', $after='', $none='', $between='', $before_last='' ) ?>`
This allows access to custom fields for any user specified by the `$user_id` value.

= Arguments =

* `$user_id`
(only for `c2c_get_user_custom()`) The integer value of the user's id.

* `$field`
The name of the user custom field to display.

* `$before`
(optional) The text to display before all field value(s).

* `$after`
(optional) The text to display after all field value(s).

* `$none`
(optional) The text to display in place of the field value should no field value exists; if defined as '' and no field value exists, then nothing (including no `$before` and `$after`) gets displayed.

* `$between`
(optional) The text to display between multiple occurrences of the custom field; if defined as '', then only the first instance will be used.

* `$before_last`
optional) The text to display between the next-to-last and last items listed when multiple occurrences of the custom field exist; `$between` MUST be set to something other than '' for this to take effect.

= Examples =

* `<?php c2c_get_current_user_custom('first_name'); ?>`
"Scott"

* `<?php c2c_get_current_user_custom('favorite_colors', 'Favorite colors: '); /* Where the 'favorite_colors' user custom field has been defined with values ?>`
"Favorite colors: blue, gray, green, black, red"

* `<?php c2c_get_current_user_custom('favorite_colors', 'Favorite colors: <ul><li>', '</li></ul>', '', '</li><li>'); ?>`
"Favorite colors: <ul><li>blue</li><li>gray</li><li>green</li><li>black</li><li>red</li></ul>"

* `<?php echo c2c_get_user_custom(3, 'first_name', 'Hi, ', '.  Welcome back.'); // where 3 is the id of the user we want ?>`
"Hi, Scott.  Welcome back."


== Filters ==

The plugin exposes four filters for hooking.  Typically, customizations utilizing these hooks would be put into your active theme's functions.php file, or used by another plugin.

= c2c_get_current_user_custom (filter) =

The 'c2c_get_current_user_custom' hook allows you to use an alternative approach to safely invoke `c2c_get_current_user_custom()` in such a way that if the plugin were deactivated or deleted, then your calls to the function won't cause errors in your site.

Arguments:

* same as for `c2c_get_current_user_custom()`

Example:

Instead of:

`<?php $twitter = c2c_get_current_user_custom( 'twitter' ); ?>`

Do:

`<?php $twitter = apply_filters( 'c2c_get_current_user_custom', 'twitter' ); ?>`

= c2c_get_author_custom (filter) =

The 'c2c_get_author_custom' hook allows you to use an alternative approach to safely invoke `c2c_get_author_custom()` in such a way that if the plugin were deactivated or deleted, then your calls to the function won't cause errors in your site.

Arguments:

* same as for `c2c_get_author_custom()`

Example:

Instead of:

`<?php $aim = c2c_get_author_custom( 'aim', 'AIM: ' ); ?>`

Do:

`<?php $aim = apply_filters( 'c2c_get_author_custom', 'aim', 'AIM: ' ); ?>`

= c2c_get_user_custom (filter) =

The 'c2c_get_user_custom' hook allows you to use an alternative approach to safely invoke `c2c_get_user_custom()` in such a way that if the plugin were deactivated or deleted, then your calls to the function won't cause errors in your site.

Arguments:

* same as for `c2c_get_user_custom()`

Example:

Instead of:

`<?php $address = c2c_get_user_custom( 5, 'address' ); ?>`

Do:

`<?php $address = apply_filters( 'c2c_get_user_custom', 5, 'address ); ?>`

= c2c_get_user_custom_field_values_shortcode (filter) =

The 'c2c_get_user_custom_field_values_shortcode' hook allows you to define an alternative to the default shortcode tag.  By default the shortcode tag name used is 'user_custom_field'.  It is recommended you only utilize this filter before making use of the plugin's shortcode in posts and pages.  If you change the shortcode tag name, then any existing shortcodes using an older name will no longer work (unless you employ further coding efforts).

Arguments:

* $shortcode (string)

Example:

`
// Use a shorter shortcode: i.e. [ucf field="last_name" /]
add_filter( 'c2c_get_user_custom_field_values_shortcode', 'change_c2c_get_user_custom_field_values_shortcode' );
function change_c2c_get_user_custom_field_values_shortcode( $shortcode ) {
	return 'ucf';
}
`


== Shortcode ==

This plugin provides one shortcode that can be used within the body of a post or page.  The shortcode is accompanied by a shortcode builder (see Screenshots) that presents a form for easily creating a shortcode.  However, here's the documentation for the shortcode and its supported attributes.

= user_custom_field =

The only shortcode provided by this plugin is named `user_custom_field`.  It is a self-closing tag, meaning that it is not meant to encapsulate text.  Except for 'field', all attributes are optional, though you'll likely need to provide a couple to achieve your desired result.

The name of the shortcode can be changed via the filter 'c2c_get_user_custom_field_values_shortcode' (though making this customization is only recommended for before your first use of the shortcode, since changing to a new name will cause the shortcodes previously defined using the older name to no longer work).

Attributes:

* field : (string) The name of the user custom field key whose value you wish to have displayed.
* this_post : (boolean) Get the custom field value for the author of the post containing this shortcode? Takes precedence over user_id attribute. Specify `1` (for true) or `0` for false. Default is `0`.
* user_id : (integer) ID of user whose custom field's value you want to display. Leave blank to search for the custom field for the currently logged in user. Use `0` to indicate it should only work on the permalink page for a page/post.
* before : (string) Text to display before the custom field.
* after  : (string) Text to display after the custom field.
* none : (string) Text to display if no matching custom field is found (or it has no value). Leave this blank if you don't want anything to display when no match is found.
* between : (string) Text to display between custom field items if more than one are being shown. Default is ', '.
* before_last : (string) Text to display between the second to last and last custom field items if more than one are being shown.

Examples:

* Get nickname for current post's author
`[user_custom_field field="nickname" this_post="1" /]`

* Get AIM account name for a specific user
`[user_custom_field field="aim" user_id="2" /]`

* Wrap post author's bio in markup, but only if the author has a bio.
`[user_custom_field field="description" before="My bio:" /]`


== Changelog ==

= 2.5.1 =
* Fix fatal shortcode bug by updating widget framework to v005 to make a protected class variable public
* Return immediately in c2c_get_user_custom() if value of $field is empty string
* Update widget version to 003

= 2.5 =
* Use get_user_meta() if defined (WP3.0+), rather than direct SQL query
* Use real functions rather than create_function() to register widget and shortcode
* Re-implemented widget, basing it on widget framework v004
* Document shortcode
* Rename widget class from 'GetUserCustomWidget' to 'c2c_GetUserCustomWidget'
* Add filter 'c2c_get_user_custom_field_values_shortcode' to allow changing shortcode name
* Rename shortcode class from 'GetUserCustomFieldValuesShortcode' to 'c2c_GetUserCustomFieldValuesShortcode'
* Add screenshots
* Add .pot
* Change extended description
* Minor code formatting changes (spacing)
* Note compatibility through WP 3.2+
* Update copyright date (2011)

= 2.0 (not publicly released) =
* Add hooks 'c2c_get_current_user_custom' (filter), 'c2c_get_author_custom' (filter), and 'c2c_get_user_custom' (filter) to respond to the function of the same name so that users can use the apply_filters() notation for invoking template tags
* Wrap each global function in if(!function_exists()) check
* Remove docs from top of plugin file (all that and more are in readme.txt)
* Note compatibility with WP 2.9+, 3.0+
* Drop compatibility with versions of WP older than 2.8
* Minor tweaks to code formatting (spacing)
* Add Filters, Screenshots, and Upgrade Notice sections to readme.txt
* Add PHPDoc documentation
* Add package info to top of plugin file
* Update copyright date (2010)
* Remove trailing whitespace

= 1.5 (unreleased) =
* Add widget with full support of all capabilities of plugin
* Add shortcode with full support of all capabilities of plugin
* Add c2c_get_author_custom() to access custom fields for the current author (when on the permalink page for a post/page, or in a loop)
* Fix inability to list multiple same-named custom fields that resulted due to changed behavior in WP
* Note compatibility through WP2.8+
* Remove compatibility with versions of WP older than 2.6
* Minor formatting tweaks
* Add Changelog to readme.txt
* Update copyright date

= 1.0.1 =
* Minor bugfix

= 1.0 =
* Initial release


== Upgrade Notice ==

= 2.5.1 =
Critical bugfix release (if using shortcode): fixed fatal shortcode bug; minor change to bail out of processing if an empty string is passed a custom field key name

= 2.5 =
Recommended update. Highlights: re-implemented widget based on custom widget framework; localized text; noted compatibility through WP 3.2+; and more.

= 2.0 =
Recommended significant update. Highlights: added widget; added shortcode + shortcode builder; added c2c_get_author_custom(); added multiple hooks to allow customization; verified WP 3.0 compatibility; dropped support for versions of WP older than 2.8; other miscellaneous tweaks and fixes.