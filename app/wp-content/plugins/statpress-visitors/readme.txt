=== Statpress Visitors ===

Contributors: luciole135
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=F9J2LJPDE5UQ8
Tags: stats, statistics, widget, admin, sidebar, visits, visitors, pageview, feed, referrer, spy, log, spider, bot, page, post
Requires at least: 2.6
Tested up to: 3.2.1
Stable Tag: 1.4.3

A fork of Statpress with news "spy bot", "visitor", "referrer", "yesterday" and 9 convenients OPTIONALS pages.


== Description ==

This plugin (a highly improved fork of StatPress Reloaded) shows the real-time statistics on your blog. It **corrects** **many** programming **errors** of Statpress and statPress Reloaded. It collects informations about visitors, spiders, search keywords, feeds, browsers, OS, etc., as Statpress Reloaded and StatPress. It is compatible with StatPress, StatPress Cn, StatPress Reloaded and Statsurfer, but not with NewStatpress who change the datatable (if you try it, you must delete the statpress datatable to work with Visitors) and with kStats Reloaded who have it's own datatable.

* A **new** **counting** **method** significantly reduces the number of SQL queries in the main page by the use of the **Set** **theory**. Now, the graphics are made in only 4 SQL query, then Statpress Visitors is faster than all others fork of statpress : 2 seconds than 10 sec with a database of 45,000 datas. These new method of counting allows the counting of visitors, page views, search engines and subscription to RSS feeds for each page. Then it gives an **accurate** **view** of **traffic** to your website: You can see the number of unique visitors, page views, subscriptions to RSS feeds and search engines for each page and posts of your website for every day saved in the database by graphs of 7, 15, 21, 31 or 62 days depending on the option chosen.
* **Spy** **visitors** (log of visits) page has been *redesigned*. Now it displays the **most** **recent** **visit** to the **oldest**. This corrects an error of Statpress and Statpress reloaded. Indeed, if a visitor came yesterday and today you will see his first visit to yesterday's date and the other after it. So you can not see the loyalty of regular visitors to your site: each visitor is displayed on the date of his first visit. In the options page you can choose the number of IP displayed on each page (20, 50 or 100) and the number of visits for each IP (20, 50 or 100). These pages "Visitors Spy" and "Spy Bot" have now **optimized** **SQL** **queries**  in by the use of the natural index of the datatable. They are made in only **one** **SQL** **query**. All others fork of StatPress need as many SQL queries that there is IP or Bot displayed on the page. The speed is 3 times faster than all other fork of Statpress. 
* The **spy** **robots** page is now available. This lets you know which pages were indexed by search robots.
* the **visitors**, **views**, **feeds** pages shows you the results of site traffic on your site for each page by graphs of 7, 15, 21, 31 or 62 days from the largest to the smallest traffic.
* The **referrer** page is now available. This shows what referrer bring the most visitors to your website.
* The **yesterday** page show you the results of site traffic at the time of yesterday.
* The **Update** **database** page has been redesigned. Now, you can update your database by yourself, learn more :<a href='http://additifstabac.free.fr/index.php/how-to-update-definitions-os-browsers-of-statpress-visitors/' target='_blank'>Learn more</a>
* **9** **pages** are **optionals** : "yesterday", "visitor Spy", "Bot Spy", "Visitors", "Views", "Feeds", "Referrer", "Statistics" and "Update". Simply click in the "options" page on the pages you do not wish to appear. They will be not stored in RAM and freeing up some RAM ! 

The download system of WordPress dont saving the changes made for version 1.4.1, that made me very angry and made some problem for the users. I propose this version taking care to check that now the files are saved by WordPress changed as I did at home.
I take this update to make some minors changes that you can see in the changelog.

**NEW** **in** **1.4.x** :

* Optionals pages more convenients, simply click now in the "Options page" on the pages you do not wish to appear. They will be not stored in RAM and freeing up some RAM !
* ALL **logos** and **icons** with tooltip : "**search** **engines**", "**spider**", "**RSS** **feeds**, **browsers** and **OS** are represented by their **logo**. **Internet** **domains**" and **country**, are represented by a **flag**. All icons, flags and logo display the correct name by a **tooltip** at mouse-over. 
* Two new informations : the **language** and **country** in addition to the internet domain. Indeed, the "original StatPress" stores languages spoken as the country which is not true, Americans speak English and so far are American. To remedy this error, "StatPress Reloaded" stored in the data table (column "nation") the Internet domain. So we added two columns in the table of data: the language and the country given by the visitor's browser. And we add a possible update of the database: the Internet domain for those who have previously installed the original StatPress can migrate to Statpress Visitors easily.
* On the "spy visitors" page, the flag displayed in the first place is the country given by the visitor's browser (preceded by "http country"), if it is not known then, secondly, it's the flag of the internet domain that is displayed (preceded by "http domain"). If neither is given, then querying the free internet service "hostip.info" (preceded by "hostip country").
* In the main page, the country's flag is displayed only if different from the Internet domain. If the same flag is displayed, then the tooltips do not give the same indication. Indeed, some Internet domains correspond to several countries and some countries have regions with theirs own internet domain.
* The functions of the administration part of the plugin are no longer stored in RAM when a visitor visits the site, this frees up RAM unnecessarily consumed otherwise. The functions and administration pages are stored in memory RAM only if the Dashboard is visible. Thanks to xknown.
* FULL PHP 5.3 and higher compatibility
* Replacement of all functions deprecated by the new WordPress functions.

**STILL** **in** **1.4.x** :

* When you have selected "**no** **collect** **spiders**", the spiders datas are not displayed on all pages.
* Added a **new** **way** to count the **RSS** feed by IP. Thus, there are two separate counts of RSS: as far as total subscription on every page (pageviews feeds), as far as visitors subscribers(visitors feeds) : if a unique visitor subscribes to the RSS feeds of 5 pages of your website, "Visitors feeds" is equal to 1 and "Pageviews feeds" is equal to 5.

= DB Table maintenance =
* StatPress can automatically delete older records to allow the insertion of newer records when your space is limited. In these case the datatable is **automaticaly** **Optimized** after the removal of olds datas.
* StatPress could do no collect "logged user" or "bot" if you want.

= StatPress Widget / StatPress_Print function =

The widget is customizable. These are the available variables :

* %today% - day of today
* %since% - day of first statpress
* %totalvisitors% - total visitors, so far
* %totalpageviews% - total pageviews, so far
* %todayvisitors% - total visitors, today
* %todaypageviews% - total pageviews, today
* %thistotalvisitors% - this page, total visitors, so far
* %thistotalpageviews% - this page, total pageviews, so far
* %thistodayvisitors% - this page, total visitors, today
* %thistodaypageviews% - this page, total pageviews, today
* %os% - os of current visitor
* %browser% - browser of current visitor
* %ip% - ip of current visitor
* %visitorsonline% - number of online visitors, now
* %usersonline% - number of logged on visitors, now
* %toppost% - the most readed post
* %topbrowser% - the top browser, so far
* %topos% - the top os, so far
* %latesthits%  - the 10 last page readed


You could add these values everywhere! StatPress offers a new PHP function *StatPress_Print() i.e. StatPress_Print("%totalvisits% total visits."). Put it whereever you want the details to be displayed in your template. Remember, as this is PHP, it needs to be surrounded by PHP-Tags!

= Ban IP =
* You could ban IP list from stats editing def/banips.dat file.

= Update Statpress database =
* You can choose the datas you want to update in your database (Browsers, OS, Searchs engines and spiders)
* To add an **new** **search** **engine** on /def/searchengines.dat make like on these example : If the referrer is : http://www.google.fr/url?sa=t&amp;source=web&amp;cd=22&amp;ved=0CCMQFjABOBQ&amp;url=http%3A%2F%2Fadditifstabac.free.fr%2Findex.php%2Ftabac-rouler-pourcentage-additifs-taux-nicotine-goudrons%2F&amp;rct=j&amp;q=rasta%20chill%20tobacco&amp;ei=F5VeTeOtAo2t8QOTyYBa&amp;usg=AFQjCNEw04UOF9nDHWpgmkNga6l6X6SexA, add these line on statpress-visitors/def/searchengines.dat : **google|www.google.|q** where **google** is the name of the search engine, **www.google.** is the URL of the search engine, **q** is the key of the query search q=rasta%20chill%20tobacco then update the statpress database
* To add an **new** **spider** on /def/spider.dat make like on these example : if the spider is picsearch add these line : **picsearch|www.picsearch.com|** where **picsearch** is the name of the spider displayed in the dashboard page and **www.picsearch.com** the URL. Then update the statpress database

== Installation ==
1. Unzip file and Upload "statpress-visitors" directory in wp-content/plugins/ 
1. Then just activate it on your plugin management page. That's it, you're done! 
1. Note: If you have been using an other compatible StatPress plugin before, **deactivate** **it**. Your data is taken over!


== Frequently Asked Questions ==
= How to update the definitions of OS and browsers of StatPress Visitors? =

see here : http://additifstabac.free.fr/index.php/how-to-update-definitions-os-browsers-of-statpress-visitors/
= What is the difference between "visitors Feeds" and "Pageviews feeds"? =

Quite simply, if a single visitor subscribed to RSS feeds on 5 pages of your website, then "Visitors Feeds" is 1 and "Pageviews Feeds" is 5.
= Why "Visitors Feeds" and "pageviews feeds" are not the same count in the pages "yesterday" and in the "Main Overview"? =

This is because the calculations are not the same!
On the "Main Overview", all pages are counted, even those that are automatically generated by WordPress (category, etc.).
On the "Yesterday", only the pages you have actually written and that are stored in your database are taken into account, those generated by WordPress are not counted.
= An user say : "I’ve use Statpress V on a few sites and noticed that the visitor total is never accurate. I’ve put it on a new site just a couple of days ago and already the vistor total is wrong. It says 211 (3 days) but if I add the individual day unique visits 147 + 91 + 68 i.e 306. So after 3 days the total is already almost 100 visitors inaccurate". =

It’s because the calculation isnt the same. In the main page, the unique visitors of 3 days isnt the total of the unique visitor of each day : the same IP is counted one time in 3 day and it’ is counted 1 time each day in the graph !
This way of counting of the main page is carried from the original statpress, change it made a very long software with more SQL query.
The SQL query count the DISTINCT IP in the 3 day, not the DISTINCT IP each day.
= Isn’t it possible to make it work with network of sites ? =

You can use http://wordpress.org/extend/plugins/proper-network-activation/


= Where can I get help? =

* Please visit the http://additifstabac.free.fr/index.php/statpress-visitors-new-statistics-wordpress-plugin/


== Screenshots ==
http://www.flickr.com/photos/59604063@N05/sets/72157626522412772/

== Changelog ==
= 1.4.3 =
* Replacement of all the WordPress functions deprecated by the new WordPress functions.
* Add a new table in the main page : "Undefined agent", the agent without definition in StatPress Visitors, then you can update it by yourself.

= 1.4.2 =
* Put again the correct file of 1.4.1 in the repository systeme of WordPress who dont work very well.
* Add .arpa domain in the domain and image
* new definition of Opera 11.5
* Dont display the name of browsers and OS, u will see their name with Tooltip
* dont made abrevia on the name of page in the main page

= 1.4.1 =
* The tables "last terms search", "Last referrers", "Last Feeds" and "Last spiders" on the main page are more informatives. 
* New update field **domain**
* PHP optimization : StatPress Visitors 1.4 make more with less memory RAM use than the previous versions.
* PHP and MySql optimization, work between 8% and 15% faster in main page. Work 2 times more faster in "Visitors", "Views", "Feeds" and "referrer" pages. Thanks to Guy.
* FULL PHP 5.3 and higher compatibility
* On "Bot spy", "more info" show the agent and ip of the bot.
* **Spam** **Bots** are detected with new definitions.
* add a version of the database to make a possible upgrade, thanks to kittz.
= 1.4.0.3. =
* Put the wrong file, sorry, it's the rigth one.

= 1.4.0.2 = 
* correct the tooltip who dont appear on Spy visitor page

= 1.4.0.1 =
* correct the variables who use "today"
* Add the IP to the referrer page.
* Do no display the user on the feed table (on main page) if do no collect logged user is checked
* Change the text in initialization of the widget.
* correct the definition of the country GB Great Britain use by some browsers (and not United Kingdom who is uk) 

= 1.4 =
* Optionals pages more convenients, simply click now in the "Options page" on the pages you do not wish to appear. 
* ALL **logos** and **icons** with tooltip.
* Two new informations : the **language** and **country** in addition to the internet domain. 
* On the "spy visitors" page, the flag displayed in the first place is the country given by the visitor's browser (preceded by "http country"), if it is not known then, secondly, it's the flag of the internet domain that is displayed (preceded by "http domain"). If neither is given, then querying the free internet service "hostip.info" (preceded by "hostip country").
* In the main page, the country's flag is displayed only if different from the Internet domain. If the same flag is displayed, then the tooltips do not give the same indication. Indeed, some Internet domains correspond to several countries and some countries have regions with theirs own internet domain.
* The functions of the administration part of the plugin are no longer stored in RAM when a visitor visits the site, this frees up RAM unnecessarily consumed otherwise. The functions and administration pages are stored in memory RAM only if the Dashboard is visible.
* The tables "last terms search", "Last referrers", "Last Feeds" and "Last spiders" on the main page are more informatives. 
* New update field **domain**
* PHP optimization : StatPress Visitors 1.4 make more with less memory RAM use than the previous versions.
* PHP and MySql optimization, work between 8% and 15% faster in main page. Work 2 times more faster in "Visitors", "Views", "Feeds" and "referrer" pages. Thanks Guy.
* FULL PHP 5.3 and higher compatibility
* On "Bot spy", "more info" show the agent and ip of the bot.
* Spam Bots are detected with new definitions.
* correct the variable who use "today", thanks to Markvandark
= 1.3.1 =
* Correct a memory use bug.
* Optimize PHP Overview main page inherited from original Statpress (0,07 MB use less).
= 1.3 =
* New page update replaces the previous two inherited from the original StatPress not working (another mistake). Now it work. You can choose the datas you want to update (Browsers, OS, Searchs engines and spiders)
* Added to the main page, a new table **"Last** **Feeds"** with the columns :  date, time, page, feed, user
* Better design of the page "visitors", "views", "feeds" and "referrer". Now, there is a row above the graphs indicate the page/post/URL, the total number of visits and the average daily visits.
* New design of the general statistics  page inherited from the original StatPress much more enjoyable.
* New color for the "referrer" : green, more readable.
* When you have selected "no collect spiders", the spiders datas are not displayed on all pages
* correct an error, in very few situation, if you change of period day in graph twice in a day, this update is not working
* New définitions of Browsers, Os. To know how to Update these definitions by yourself : <a href='http://additifstabac.free.fr/index.php/how-to-update-definitions-os-browsers-of-statpress-visitors/' target='_blank'>Learn more</a>
* Correction of errors on widgets and variables. Now it works.
= 1.2.0.5.5 =
* correct account of "Visitors feeds" and "Pageviews feeds" in page "yesterday"
= 1.2.0.5.4 = 
* correct options "logged user" and "do no collect bot" who dont work
* add some icons and definitions
= 1.2.0.5.3 =
* correct option in spybot page who dont work
= 1.2.0.5.2 = 
* add a wrong file in 1.0.2.5 and 1.0.2.5.1, sorry, then the Facebook referrer dont recognize the page/post visited. I add the rigth one in it. Sorry.
= 1.2.0.5 =
* reactivate the optionals pages.
= 1.2.0.4 = 
* deactivate the optionals pages.
* correct error on the recognition of Windows XP.
= 1.2.0.3 =
* Now the graphics of the main page "Overview" and those pages "Visitors", "Views," Feeds "and" Referrer " are not in the same height. 200 pixels tall for the main page and 100 pixels for the others.
* Fixed a small error in the graphics of the page "Visitors".
= 1.2.0.2 =
* The graphics were too smalls, I think right now is better.
= 1.2.0.1 =
* Encoding in UTF8 (without BOM) to avoid the error "Cannot modify header...".
= 1.2 = 
* fix bug on 1.1.2
* All the pages are OPTIONALS (except "main" and "options"). How it works: all the pages in the folder wp-content/plugins/statpress-visitors/pages are optionals. If you do not want to use a page, delete the file via FTP (with Filezilla, par ex) in the folder and this will free some RAM. If you want to use it again, add it in this same folder, simply.
* the page "yesterday" show all your pages, with or without visits.
* I corrected an error in the page yesterday on account of Feeds and Visitors Pageviews Feeds. 
* added %since% and %totalvisitors% that I deleted by mistake
* update some OS and browsers definitions
= 1.1.2 =
* sorry, the 1.1 and 1.1.1 file wasnt the finals file, this it the final file.
= 1.1 =
* Added a new page "yesterday" with the results of site traffic at the time of yesterday.
* SQL queries optimization in the pages "Visitors Spy" and "Spy Bot" by the use of Set Theory. Now these pages are made in only one SQL query. The previous versions and all others fork of StatPress need as many SQL queries that there is IP or Bot displayed on the page. The speed is 3 times faster than the previous version and than all other fork of Statpress.
* Detection of the referring page when the referrer is Facebook. In this case, in previous versions, all page views were called "fb_xd_fragment", now, their real name is displayed.
* Added a new way to count the RSS feed by IP. Thus, there are two separate counts of RSS: as far as total subscription on every page (pageviews feeds), as far as visitors subscribers(visitors feeds).
* Every day, automatic optimization of the data table "statpress" when the "autodelete" option is on. The data table is optimize after the removal of olds data. Then, now, the data table 'statpress' is always optimized.
* New count of the RSS Feeds : Count 1 feed by IP (more realistic way of count).
* Correct the count of the variable %toppost%
= 1.0.10 =
* correct "spy visitor" to work like in version 1.0.5 and lower : display "arrived from...searching..."
= 1.0.9 =
* add these variable
  %thistotalpageviews% - this page, total pageviews
= 1.0.8 =
* correct an URL error on 'Spy visitors' and 'Spy bot' page when there are multiple pages.
= 1.0.7 =
* Better URL for Statpress-Visitors pages.
* correct an URL error on 'Overview' page when there are multiple pages.
* New menu icon.
= 1.0.6 =
* Now when selecting one of the Statpress Visitors pages, such as visitor spy, the menu indicates that it is this page who is selected (shaded background & notch on left side).
* The main menu item is now "Statpress V" to keep it on a single line.
= 1.0.5 =
* this version correct some error when dadabase is empty 
= 1.0.4 =
 this version correct minimum capability to view stat
= 1.0.3 =
* This version 1.0.3 optimize some SQL query in "visitor and view" page, then it work a little faster.
= 1.0.2 =
* This version 1.0.2 optimize some SQL query in "feed page".
= 1.0.1 =
* statpress-visitors 1.0.1 correct a SQL query to work faster in "Overview" main page.
* This version 1.0.1 is much faster in displaying the main "Overview" page.
* add Cityreview spider in def/spider.dat

== Upgrade Notice ==



