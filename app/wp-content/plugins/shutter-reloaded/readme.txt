=== Shutter Reloaded ===
Contributors: Andrew Ozz
Tags: images, javascript, viewer, lightbox
Requires at least: 2.6
Tested up to: 2.9
Stable tag: 2.4.1

Darkens the current page and displays an image (like Lightbox, Thickbox, etc.), but is a lot smaller (10KB) and faster.


== Description ==

Shutter Reloaded is an image viewer for your website that works similarly to Lightbox, Thickbox, etc. but is under 10KB in size and does not require any external libraries. It has many features: resizing large images if the window is too small to display them with option to show the full size image, combining images in sets, redrawing the window after resizing, pre-loading of neighbour images for faster display and very good browser compatibility.

This plugin offers customization of the colour and opacity settings for the background and colour for the caption text, buttons text and the menu background.

There are options to enable it for all links pointing to an image on your site (with option to exclude some pages), or just on selected pages. It can be enabled only for image links with CSS class="shutter" with option to create a single set or multiple sets for each page.

The plugin can also "auto-make" image sets for each Post, so when several posts are displayed on the "Home" page, links to images on each post will be in a separate set. See the built-in help for more information.

== Changelog ==

= 2.4.1 =
Update to the French translation courtesy of Lise, http://liseweb.fr

= 2.4 =
 * Fixed problems when saving custom color settings.
 * Reverted the method of making page elements invisible while Shutter os open.
 * Added option to make one big set of all image links on the page when they are not already part of a set.

= 2.3 =
 * The caption is loaded from the title attribute of the link to the displayed image or from the thumbnail (if it exists).
 * Movies, select elements and iframes are not forced visible when Shutter exits if they were invisible before.
 * Improvements to the script loading and auto-sets creation.
 * All JavaScript is loaded in the footer by default.
 * The settings page is under the Appearance menu.

= 2.2 =
 * New loading method (the old method can still be used by selecting it at the bottom of the options page).
 * Update to the options page to fit WordPress 2.7.

= 2.1 =
 * Upgrades for compatibility with WordPress 2.5.

= 2.0 =
 * Option to display full size image if it was resized to fit the browser window.
 * Display of image count (for sets).
 * Option for graphic or text buttons.
 * Support for localization (.pot file included).

= 1.2 =
 * Compatibility with WordPress version 2.0 (2.0.11) and 2.3.
 * Several improvements and small bugfixes. 

= 1.1 =
 * Support for Lightbox style activation (rel = lightbox[...]).
 * Better build-in help and several small bugfixes.

== Installation ==

Standard WordPress quick and easy installation:
 
1. Download.
2. Unzip. 
3. Upload the shutter-reloaded folder to the plugins directory.
4. Activate the plugin.
5. Go to "Appearance - Shutter Reloaded" and set your preferences.

= Upgrade =

1. Deactivate and delete the old version.
2. Upload and activate the new one.


== Frequently Asked Questions == 

= I have ... plugin installed that uses javascript, will there be any conflicts/incompatibilities? =

Since Shutter Reloaded does not use any js libraries, it does not interfere with them.

= What will happen if my site visitors have JavaScript disabled? =

Then none of your links will be changed and will work as usual.

= I have a thumbnail link but it points to a webpage, not to image. Will that affect Shutter Reloaded? =

No, Shutter Reloaded looks only for links pointing to an image (with thumbnails or not), and will not change any other link, even if the link has the same CSS class used for activation.


== Screenshots ==

For demo and screenshots visit the home page for [Shutter Reloaded](http://www.laptoptips.ca/projects/wp-shutter-reloaded/). 
