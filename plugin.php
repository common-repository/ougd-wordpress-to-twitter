<?php
/*
Plugin Name: ou.gd: WordPress to Twitter
Plugin URI: http://ou.gd/pages/wordpress/
Description: Create short URLs for posts with <a href="http://ou.gd" title="ou.gd">ou.gd</a> and tweet them.
Author: Craighton Miller
Author URI: http://craighton.me
Version: 1.4.2
*/

/* Release History :
 * 1.0:       Initial release
 * 1.1:       Fixed: template tag makes post previews die (more generally, plugin wasn't properly initiated when triggered from the public part of the blog). Thanks moggy!
 * 1.2:       Added: ping.fm support, unused at the moment because those fucktards from ping.fm just don't approve the api key.
              Added: template tag wp_ozh_yourls_raw_url()
			  Added: uninstall procedure
			  Added: "get url" button as on wp.com
			  Improved: using internal WP_Http class instead of cURL for posting to Twitter
			  Fixed: short URLs generated on pages or posts even if option unchecked in settings (thanks Viper007Bond for noticing)
			  Fixed: PEAR class was included without checking existence first, conflicting with Twitter Tools for instance (thanks Doug Stewart for noticing)
 * 1.2.1:     Fixed: oops, forgot to remove a test hook
 * 1.3:       Fixed: Don't generate short URLs on preview pages
              Fixed: Tweet when posting scheduled post or using the XMLRPC API
 * 1.3.1:     Added: option to add <link> in <real>
 * 1.3.2:     Fixed: compat with ou.gd 1.4
 * 1.3.3:     Fixed: compat with WP 2.9 & wp.me integration
 * 1.3.4:     Fixed: compat with WP 3.0, ou.gd 1.4.2
 * 1.4:       Fixed: compat with WP 3.0, ou.gd 1.4.3 & ou.gd 1.5
              Removed: support with ou.gd 1.3. Upgrade.
              Added: Ajax checks for ou.gd config, super cool.
              Added: OAuth support. Curse you, Twitter.
			  Added: Support for custom post type
			  Added: filters everywhere so you can hack without hacking
			  Added: lots of tweet template tokens
              Fixed: notices when no or just one tag/category
              Fixed: don't load twitter oauth classes if already there
			  Added: filter for admin notice
              Fixed: Application name on Twitter was not unique
              Added: Built-in support for custom keyword with post custom field 'ougd-keyword'
              Added: Both 'ougd-keyword' and 'ougd_keyword'
              Fixed: Possible wrong shorturl when not on singular pages. Thanks Otto for the fix!
              Changed: Logic to connect to Twitter. No one pass, should be simpler.
              Fixed (hopefully): Creating duplicates URL with ou.gd
			  Fixed: the "Show letters" toggable password fields on Chrome
			  Fixed: ressource now loaded in compliance with SSL pref
              Added: More actions and filters
 * 1.4.1:     Fixed: Loop error with in the options page
 * 1.4.2:     Fixed: Critical link generation error.
 */

/********************* DO NOT EDIT *********************/

global $wp_ozh_yourls;
session_start();
require_once( dirname(__FILE__).'/inc/core.php' );


/******************** TEMPLATE TAGS ********************/

// Template tag: echo short URL for current post
function wp_ozh_yourls_url() {
	global $id;
	$short = esc_url( apply_filters( 'ozh_yourls_shorturl', wp_ozh_yourls_geturl( $id ) ) );
	if ($short) {
		$rel    = esc_attr( apply_filters( 'ozh_yourls_shorturl_rel', 'nofollow alternate shorturl shortlink' ) );
		$title  = esc_attr( apply_filters( 'ozh_yourls_shorturl_title', 'Short URL' ) );
		$anchor = esc_html( apply_filters( 'ozh_yourls_shorturl_anchor', $short ) );
		echo "<a href=\"$short\" rel=\"$rel\" title=\"$title\">$anchor</a>";
	}
}

// Template tag: echo short URL alternate link in <head> for current post. See http://revcanonical.appspot.com/ && http://shorturl.appjet.net/
function wp_ozh_yourls_head_linkrel() {
	global $post;
	$id = $post->ID;
	$type = get_post_type( $id );
	if( wp_ozh_yourls_generate_on( $type ) ) {	
		$short = apply_filters( 'ozh_yourls_shorturl', wp_ozh_yourls_geturl( $id ) );
		if ($short) {
			$rel    = apply_filters( 'ozh_yourls_shorturl_linkrel', 'alternate shorturl shortlink' );
			echo "<link rel=\"$rel\" href=\"$short\" />\n";
		}
	}
}

// Template tag: return/echo short URL with no formatting
function wp_ozh_yourls_raw_url( $echo = false ) {
	global $id;
	$short = apply_filters( 'ozh_yourls_shorturl', wp_ozh_yourls_geturl( $id ) );
	if ($short) {
		if ($echo)
			echo $short;
		return $short;
	}
}

// Get or create the short URL for a post. Input integer (post id), output string(url)
function wp_ozh_yourls_geturl( $id ) {
	do_action( 'yourls_geturl' );
	// Hardcode this const to always poll the shortening service. Debug tests only, obviously.
	if( defined('YOURLS_ALWAYS_FRESH') && YOURLS_ALWAYS_FRESH ) {
		$short = null;
	} else {
		$short = get_post_meta( $id, 'ougd_shorturl', true );
	}
	
	// short URL never was not created before? let's get it now!
	if ( !$short && !is_preview() && !get_post_custom_values( 'yourls_fetching', $id) ) {
		// Allow plugin to define custom keyword
		$keyword = apply_filters( 'ozh_yourls_custom_keyword', '', $id );
		$short = wp_ozh_yourls_get_new_short_url( get_permalink( $id ), $id, $keyword );
	}
	
	return $short;
}

/************************ HOOKS ************************/

// Check PHP 5 on activation and upgrade settings
register_activation_hook( __FILE__, 'wp_ozh_yourls_activate_plugin' );
function wp_ozh_yourls_activate_plugin() {
	if ( version_compare(PHP_VERSION, '5.0.0', '<') ) {
		deactivate_plugins( basename( __FILE__ ) );
		wp_die( 'This plugin requires PHP5. Sorry!' );
	}
}

// Conditional actions
if (is_admin()) {
	require_once( dirname(__FILE__).'/inc/options.php' );
	require_once( dirname(__FILE__).'/inc/oauth.php' );
	// Add menu page, init options, add box on the Post/Edit interface
	add_action('admin_menu', 'wp_ozh_yourls_add_page');
	add_action('admin_init', 'wp_ozh_yourls_admin_init');
	add_action('admin_init', 'wp_ozh_yourls_addbox', 10);
	// Handle AJAX requests
	add_action('wp_ajax_yourls-promote', 'wp_ozh_yourls_promote' );
	add_action('wp_ajax_yourls-reset', 'wp_ozh_yourls_reset_url' );
	add_action('wp_ajax_yourls-check', 'wp_ozh_yourls_check_yourls' );
	// Custom icon & plugin action link
	add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'wp_ozh_yourls_plugin_actions', -10);
	add_filter( 'ozh_adminmenu_icon_ozh_yourls', 'wp_ozh_yourls_customicon' );
} else {
	add_action('init', 'wp_ozh_yourls_init', 1 );
}

// Handle new stuff published
add_action('new_to_publish', 'wp_ozh_yourls_newpost', 10, 1);
add_action('draft_to_publish', 'wp_ozh_yourls_newpost', 10, 1);
add_action('pending_to_publish', 'wp_ozh_yourls_newpost', 10, 1);
add_action('future_to_publish', 'wp_ozh_yourls_newpost', 10, 1);

// Shortcut internal shortlink functions
add_filter( 'pre_get_shortlink', 'wp_ozh_yourls_wp_get_shortlink', 10, 3 );

