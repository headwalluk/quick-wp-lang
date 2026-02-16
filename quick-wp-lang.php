<?php
/**
 * Plugin Name: Quick WP Lang
 * Plugin URI: https://headwall-hosting.com/plugins/alternative-language-for-wordpress-posts/
 * Description: Assign alternative languages to individual posts, pages, and custom post types without requiring a full multilingual setup. Automatically adjusts hreflang tags and language attributes for better SEO.
 * Version: 1.0.1
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: Paul Faulkner
 * Author URI: https://headwall-hosting.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: quick-wp-lang
 * Domain Path: /languages
 *
 * @package Quick_WP_Lang
 */

defined( 'ABSPATH' ) || die();

// Define plugin constants.
define( 'QUICK_WP_LANG_VERSION', '1.0.1' );
define( 'QUICK_WP_LANG_PLUGIN_FILE', __FILE__ );
define( 'QUICK_WP_LANG_PLUGIN_DIR', __DIR__ );
define( 'QUICK_WP_LANG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load constants.
require_once QUICK_WP_LANG_PLUGIN_DIR . '/constants.php';

// Load helper functions.
require_once QUICK_WP_LANG_PLUGIN_DIR . '/includes/functions-languages.php';

// Load core classes.
require_once QUICK_WP_LANG_PLUGIN_DIR . '/includes/class-plugin.php';
require_once QUICK_WP_LANG_PLUGIN_DIR . '/includes/class-settings.php';
require_once QUICK_WP_LANG_PLUGIN_DIR . '/includes/class-admin-hooks.php';
require_once QUICK_WP_LANG_PLUGIN_DIR . '/includes/class-public-hooks.php';

/**
 * Initialize and run the plugin.
 *
 * @since 1.0.0
 *
 * @return void
 */
function quick_wp_lang_plugin_run() {
	global $quick_wp_lang_plugin_instance;
	$quick_wp_lang_plugin_instance = new \Quick_WP_Lang\Plugin();
	$quick_wp_lang_plugin_instance->run();
}
quick_wp_lang_plugin_run();
