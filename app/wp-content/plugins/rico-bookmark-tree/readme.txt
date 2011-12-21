=== Rico Bookmark Tree ===
Contributors: obaq
Tags: ajax, rico, bookmark, tree
Requires at least: 2.7
Tested up to: 2.9.2
Stable tag: 1.0

== Description ==
This plugin can create a simple bookmark list with a tree structure on a post using Rico Ajax(http://openrico.org).
Please visit the below site for the details:
http://obaqblog.blogspot.com/2010/06/rico-bookmark-tree-plugin-for-wordpress.html

== Installation ==
*after uploading the plugin folder, please try pointing to http://[your domain]/[your wordpress dir if any]/wp-content/plugins/rico-ajax-menu/accept.png in the browser.
For example, if your domain is 'www.yourblog.com' and your wordpress directory is 'wp', it should be http://www.yourblog.com/wp/wp-content/plugins/rico-bookmark-tree/accept.png.
If you can't see an image, you need to figure out where the plugin folder is located and set up the RBTSITEPATH value in the config.php using your wordpress directory name.

Extract all folder and files.
Modify config.php for the site path(if you installed WordPress under subdirectory, say "/wordpress/",
then the RBTSITEPATH setting will be "/wordpress/". Normally, it is "/").
'RBTGZIPDELIVER' defines if the gzipped javascript and a style sheet will send to users' browsers.
Set 'true' to enable. (It may shorten the time to load the page but may not be necessary if the web server supports gzip deflates.)

Upload the 'rico-bookmark-tree' folder and its contents to '/wp-content/plugins/'.
Go to the 'Plugins' menu of your admin area and activate the plugin.
Please kindly let me know by posting at http://obaq.uuuq.com/?p=240
if you encounter any problem.


== Frequently Asked Questions ==
= How to insert a bookmark tree onto the post? =
You may try inserting the below codes and learn how the things work.
Basically, it is a simple tagging scheme.
[RBT]...[/RBT]: start and end of the insertion code.
[TITLE]...[/TITLE]: Title for the bookmark tree.
[PARENTCAT]..[/PARENTCAT]: make the parent of a tree with [CAT]...[/CAT].
[CAT]...[/CAT]: the categories for the links.


[LINK]...[/LINK]: the settings for a link with below values;
[PARENTCAT]...[/PARENTCAT]: the parent category the link belongs.
[SUBCAT]...[/SUBCAT]: the sub-category which the link belongs under the parent category.
[NAME]...[/NAME]: the name for the link.
[URL]...[/URL]: the URL of the link.
*if you omit [PARENTCAT], [SUBCAT] will be used as the parent category.
*if you omit [SUBCAT], the link will be classified as 'Uncategorised'.


(Code Begin)
[RBT]
[TITLE]Bookmarks[/TITLE]

[PARENTCAT]
[CAT]IT[/CAT]
[CAT]NEWS[/CAT]
[CAT]Movies[/CAT]
[/PARENTCAT]

[LINK]
[PARENTCAT]IT[/PARENTCAT]
[SUBCAT]WordPress[/SUBCAT]
[NAME]WordPress.org[/NAME]
[URL]http://wordpress.org/[/URL]
[/LINK]

[LINK]
[PARENTCAT]WordPress[/PARENTCAT]
[SUBCAT]Themes[/SUBCAT]
[NAME]Themes[/NAME]
[URL]http://wordpress.org/extend/themes/[/URL]
[/LINK]

[LINK]
[PARENTCAT]IT[/PARENTCAT]
[SUBCAT]WordPress[/SUBCAT]
[NAME]Themes Tag Filter[/NAME]
[URL]http://wordpress.org/extend/themes/tag-filter/[/URL]
[/LINK]

[LINK]
[PARENTCAT]IT[/PARENTCAT]
[SUBCAT]WordPress[/SUBCAT]
[NAME]Weblog Tools Collection[/NAME]
[URL]http://weblogtoolscollection.com/[/URL]
[/LINK]

[LINK]
[PARENTCAT]Movies[/PARENTCAT]
[SUBCAT]Fantazy[/SUBCAT]
[NAME]Clash of the Titans[/NAME]
[URL]http://clash-of-the-titans.warnerbros.com/[/URL]
[/LINK]

[LINK]
[PARENTCAT]Movies[/PARENTCAT]
[SUBCAT][/SUBCAT]
[NAME]Disney Movie Trailers[/NAME]
[URL]http://www.youtube.com/user/DisneyMovieTrailers[/URL]
[/LINK]

[LINK]
[PARENTCAT]NEWS[/PARENTCAT]
[SUBCAT][/SUBCAT]
[NAME]Reuters[/NAME]
[URL]http://www.reuters.com/[/URL]
[/LINK]

[LINK]
[PARENTCAT]NEWS[/PARENTCAT]
[SUBCAT]Market[/SUBCAT]
[NAME]Bloombergs: Stocks[/NAME]
[URL]http://www.bloomberg.com/news/markets/stocks.html[/URL]
[/LINK]
http://www.nhk.or.jp/

[/RBT]
(Code End)

= What is Bookmarklet? =
You can drag the Bookmarklet to the Bookmarks Toolbar of your (Firefox) browser and click it on the web page you want to make a code for the link.
A window will open which contains a code similar to the below;
[LINK]
[PARENTCAT][/PARENTCAT]
[SUBCAT][/SUBCAT]
[NAME]Web Site Title[/NAME]
[URL]http://www.website.com/[/URL]
[/LINK]

You can add this code to the existing bookmark tree.

== Screenshots ==
1. screenshot-1.png: a bookmark tree.

== Changelog ==
= 1.0 =
* 1.0 is the beginning of the version.

== Upgrade Notice ==
= 1.0 =
* 1.0 is the beginning of the version.
