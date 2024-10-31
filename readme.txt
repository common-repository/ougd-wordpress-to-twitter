=== ou.gd: WordPress to Twitter ===
Contributors: craighton
Donate link: http://logiclounge.com/donate
Tags: twitter, tweet, ougd, ou.gd, short url, url shortener, oauth, wordpress to twitter
Requires at least: 3.0
Tested up to: 3.0.3
Stable tag: trunk

Use ou.gd (a free GPL URL shortener service) to create short URL of your posts and tweet them

== Description ==

ou.gd is a free URL shortener service you can use to shorten links and gain stats.

This plugin is a bridge between ou.gd, Twitter and your blog: when you submit a new post or page, your blog will tap into the ou.gd API to generate a short URL for it, and will then tweet it.

Requires PHP 5.

== Changelog ==

= 1.0 =
* Initial release

= 1.1 =
* Fixed: template tag makes post previews die (more generally, plugin wasn't properly initiated when triggered from the public part of the blog). Thanks moggy!

= 1.2 =
* Added: uninstall procedure
* Added: "get url" button as on wp.com
* Improved: using internal WP_Http class instead of cURL for posting to Twitter
* Fixed: short URLs generated on pages or posts even if option unchecked in settings
* Fixed: PEAR class was included without checking existence first, conflicting with Twitter Tools for instance 

= 1.2.1 =
* Fixed: oops, forgot to remove a test hook
* Fixed: Don't generate short URLs on preview pages
* Fixed: Tweet when posting scheduled post or using the XMLRPC API

= 1.3.1 =
* Added: option to add <link> in <real>

= 1.3.2 =
* Fixed: Compatibility with ou.gd 1.4

= 1.3.3 =     
* Fixed: Compatibility with WP 2.9 & wp.me integration

= 1.3.4 =
* Fixed: Compatibility with WP 3.0, ou.gd 1.4.2

= 1.4 =
* Fixed: Compatibility with WP 3.0, ou.gd 1.4.3 & ou.gd 1.5
* Removed: support with ou.gd 1.3. Upgrade.
* Added: Ajax checks for ou.gd config, super cool.
* Added: OAuth support. Curse you, Twitter.
* Added: Support for custom post type
* Added: filters everywhere so you can hack without hacking
* Added: lots of tweet template tokens
* Fixed: notices when no or just one tag/category
* Fixed: don't load twitter oauth classes if already there
* Added: filter for admin notice
* Fixed: Application name on Twitter was not unique
* Added: Built-in support for custom keyword with post custom field 'ougd-keyword'
* Added: Both 'ougd-keyword' and 'ougd_keyword'
* Fixed: Possible wrong shorturl when not on singular pages. Thanks Otto for the fix!
* Changed: Logic to connect to Twitter. No one pass, should be simpler.
* Fixed (hopefully): Creating duplicates URL with ou.gd
* Fixed: the "Show letters" toggable password fields on Chrome
* Fixed: ressource now loaded in compliance with SSL pref
* Added: More actions and filters

= 1.4.1 =
* Fixed: Loop error with in the options page

= 1.4.2 =
* Fixed: Critical link generation error. 

== Upgrade Notice ==

= 1.4 =
This version fixes critical Twitter OAuth issue!

== Installation ==

Installation is, as usual :

1. Upload files to your `/wp-content/plugins/` directory (preserve sub-directory structure if applicable)
2. Activate the plugin through the 'Plugins' menu in WordPress