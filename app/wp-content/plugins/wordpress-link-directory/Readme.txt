=== WordPress Link Directory ===
Contributors: Seans0n
Donate link: http://www.seanbluestone.com/buy-sean-a-coffee
Tags: links, link directory, reciprocal, linking, directory
Requires at least: 2.3
Tested up to: 2.6.3
Stable tag: "trunk,"

WordPress Link Directory is a simple, compact and quick-setup link directory plugin for WordPress which allows other webmasters to add their site to your directory based on reciprocal links, PageRank or other criteria.

Related Links:

* <a href="http://www.seanbluestone.com/wp-link-directory">Plugin Homepage</a>
* <a href="http://www.seanbluestone.com/links">WordPress Link Directory Demo</a>

== Description ==

WordPress Link Directory is, simple, compact and quick-setup link directory plugin for WordPress. The main features are:

* Automatically checks for reciprocal links on pages specified by PR.
* Displays the PR of all sites in the directory.
* Option to display more detailed information on each link.
* Option to notify the admin when a new link is submitted.
* Categories and Sub-Categories with parenting.
* Integrates seemlessly with WordPress and adopts whichever theme you're using.

WordPress Link Directory is now easy to translate into any language and has a language folder with more info.

Related Links:

* <a href="http://www.seanbluestone.com/wp-link-directory">Plugin Homepage</a>
* <a href="http://www.seanbluestone.com/links">WordPress Link Directory Demo</a>

== Installation ==

1. Upload the wplinkdir folder to your '/wp-content/plugins/' directory.
2. Activate the plugin via the Plugins menu in WordPress.
3. Create and publish a new page with this tag in the body: [wplinkdir]

A new WP Link Directory menu will appear in WordPress with access to the settings and options. To begin with you should create a few categories and adjust the 'HTML for reciprocal link' option. You will probably want to delete our demo link too.

You can post the shortcode tag [wplinkdir] in any post or page where you want to display your link directory. You can use the page and category tags to display individual pages or categories of links, for example [wplinkdir page="Add URL"] will display only the Add URL page while [wplinkdir category="Games"] will only display links from the Games category.

== FAQ ==

**Permalinks are not working for me / When I go to my new WordPress Link Directory post/page it shows my index page?**
WordPress Link Directory tries to set up its own permalink structure using the default value "links". So categories would be available at "www.yoursite.com/wordpress/links/category-name". The main reason permalinks will not work for you is if you are using only "%postname&" as your permalink structure. This forces WordPress to interpret the link directory as a post or page and doesn't give it its own structure. If this is the case you can use the default navigation structure by leaving the Permalinks option (under Other Options) blank. This doesn't look as pretty but does the same job.

Another possible solution that works for many people is to create your Link Directory as a *post* rather than a *page*. The disadvantage to this is that your directory may show up on your index page, but it may solve the permalinks problem.

**After I install WordPress Link directory the [shortcodes] for my other plugins don't work / When I install plugin X the [shortcodes] for WordPress Link Directory don't work.**

WordPress Link Directory uses 2 shortcodes: [wplinkdir] for categories, pages and links, and [link] for tagged links. Since other plugins sometimes use [link] you may need to leave this disabled (or edit the code to change the name). If you are still having issues with tagged links disabled then the most likely cause is bad programming in your other plugins. The best solution is to deactivate each plugin and try your WordPress Link Directory page again until it works. You can then identify the plugin causing the trouble and email the creator or try to find another solution.

**I have developed a theme or language pack for WordPress Link Directory, what should I do with it?**

The best method is to upload your theme or language pack using a free service like http://www.megaupload.com then dropping a link and description in the <a href="http://www.seanbluestone.com/forums/topic/wordpress-link-directory-pro">WordPress Link Directory Discussion Forum</a> or get in touch with me using the contact form on my site. I'll then add your file to future updates of the plugin.

**I'm receiving MySQL error reports**

If you are receiving any MySQL errors like:

'Warning: mysql_result(): supplied argument is not a valid MySQL result resource in /wp-content/plugins/wordpress-link-directory/link-directory.php on line xxx'

Then the most likely cause is that your links table doesn't match the one created in the installation function wplinkdir_init() in link-directory.php. A solution to this is to find this function and look at the line:

'$wpdb->query("CREATE TABLE ".WPLD_LINKS_TABLE." ('

Next, log into phpMyAdmin or whatever you use to manage your database and ensure that the table wp_wplinkdir_links (where wp_ is your WordPress prefix) matches the table created at the line above. If not, manually adjust it. This should only be attempted if you know what you're doing or as a last resort as it may cause other issues if not done correctly.

An alternative and potentially safer option is to uncomment line 69 of link-directory.php:

'register_deactivation_hook(__FILE__, 'wplinkdir_uninstall');'

And then deactivate then reactivate the plugin. Please note that *THIS WILL DELETE ALL YOUR LINKS*. If this is not a problem then give this a try. Be sure to re-comment this line after you get things working (or your links will be deleted in future updates).

**I'm still receiving problems / My problem was not addressed**

You can search for related issues in the <a href="http://www.seanbluestone.com/forums/forum/wordpress">WordPress Link Directory Discussion Forum</a> or create your own post in the bugs topic. Be aware, however, that a reply may take several days or longer.

== Screenshots ==

1. WordPress Link Directory Front End

== Developer Information ==

If you want to edit, update or change the content of WP Link Directory, feel free, but please contain, at a minimum, my name and URL. You may find the commenting in the php files gives more information than you find here.

A readme file is included in the /lang/ directory for more information on creating/adopting your own language files and any language files or themes you'd like to share with the community can be posted in the <a href="http://www.seanbluestone.com/forums/forum/wordpress">WordPress Link Directory Discussion Forum</a>.