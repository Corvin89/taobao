=== Overwrite Uploads ===
Contributors: iandunn
Donate link: http://kiva.org
Tags: overwrite, uploads, files, media library
Requires at least: 3.1
Tested up to: 3.1
Stable tag: 1.0

Lets you choose whether or not Wordpress should overwrite files uploaded to the Media Library


== Description ==
By default Wordpress doesn't overwrite existing files. Instead, it appends a number to the end of the filename in order to make it unique, *e.g., filename1.jpg*. That isn't always the desired behavior, so this plugin adds an option to the Media Settings page. If that option is checked then any files uploaded will automatically overwrite existing files that have the same name, rather than creating a 2nd file with a unique name. 

**NOTE**: You have to make a small change to one of Wordpress' core files for this to work. See the FAQ for details.

**NOTE**: This plugin requires PHP5 and Wordpress 3.1.


== Installation ==
1. Upload the *overwrite-uploads* directory to your *wp-content/plugins/* directory.
2. Backup a copy of *wp-admin/includes/file.php* in case you make a mistake installing the new hook.
3. Edit *wp-admin/includes/file.php* and scroll down to the line that says, `function wp_handle_upload( &$file, $overrides = false, $time = null ) {` *(line 268 in Wordpress 3.1)*
4. Right above it, add this new line: `define('OVUP_FILTER_ADDED', true); // custom modification for Overwrite Uploads plugin`
5. Scroll down a few more lines until you see, `$file = apply_filters( 'wp_handle_upload_prefilter', $file );`
6. Right above it, add this new line: `$overrides = apply_filters( 'wp_handle_upload_overrides', $overrides ); // custom modification for Overwrite Uploads plugin`
7. Activate the plugin through the 'Plugins' menu in WordPress
8. Go to the 'Media' page under the 'Settings' menu, and check the new option to overwrite uploads.


== Frequently Asked Questions ==
= Why do I have to modify a core file? =
Plugins use [hooks](http://codex.wordpress.org/Writing_a_Plugin#WordPress_Plugin_Hooks) to modify the default behavior of Wordpress, but the hook that this plugin needs doesn't currently exist, so we create it ourselves. I've submitted [a request for Wordpress to add the hook](http://core.trac.wordpress.org/ticket/16849) and when they do I'll release an updated version of the plugin that doesn't require modifying the core.

= What does the new code do? = 
The first line creates a flag to let the plugin know that the new hook has been installed. This lets us warn users if they haven't created the new hook, or if they've upgraded Wordpress haven't re-installed the hook.

The second line adds the new hook itself.

= How do I add the new hook? =
See the installation instructions for full details. It's a fairly easy change to make, but you need to be careful that you follow the directions closely. If you put things in the wrong place then you might cause an error which would make Wordpress stop working. If you're not comfortable with making basic PHP tweaks then you might want to ask someone for help.


= I tried to and the new hook, but something went wrong and now Wordpress is broken = 
If you're seeing any errors after modifying *wp-admin/includes/file.php*, just re-upload your backup copy of it and that will fix the errors. If you didn't make a backup copy before making the changes, you can re-download Wordpress and upload the files.


== Screenshots ==
1. The new setting on the Media Settings page.


== Changelog ==
= 1.0 =
* Initial release


== Upgrade Notice ==
= 1.0 =
Initial release