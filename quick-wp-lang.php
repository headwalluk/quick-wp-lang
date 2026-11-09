<?php
/**
 * Plugin Name: Quick WP Lang
 * Plugin URI: https://github.com/headwalluk/quick-wp-lang
 * Description: Set the language of individual posts, pages and custom post types for SEO, and link translations to their original, without a full multilingual setup.
 * Version: 1.3.0
 * Requires at least: 7.1
 * Requires PHP: 8.2
 * Author: Paul Faulkner
 * Author URI: https://headwall-hosting.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: quick-wp-lang
 * Domain Path: /languages
 *
 * @package Quick_WP_Lang
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || die();

define( 'QUICK_WP_LANG_VERSION', '1.3.0' );
define( 'QUICK_WP_LANG_FILE', __FILE__ );
define( 'QUICK_WP_LANG_PATH', plugin_dir_path( __FILE__ ) );
define( 'QUICK_WP_LANG_URL', plugin_dir_url( __FILE__ ) );
define( 'QUICK_WP_LANG_BASENAME', plugin_basename( __FILE__ ) );

require_once QUICK_WP_LANG_PATH . 'constants.php';
require_once QUICK_WP_LANG_PATH . 'functions-private.php';

require_once QUICK_WP_LANG_PATH . 'includes/class-settings.php';
require_once QUICK_WP_LANG_PATH . 'includes/class-admin-hooks.php';
require_once QUICK_WP_LANG_PATH . 'includes/class-public-hooks.php';
require_once QUICK_WP_LANG_PATH . 'includes/class-plugin.php';

// GitHub auto-updates (admin, cron and WP-CLI only — no need to load on front-end requests).
if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	require_once QUICK_WP_LANG_PATH . 'includes/class-github-updater.php';
	new Quick_WP_Lang\Github_Updater( QUICK_WP_LANG_FILE, Quick_WP_Lang\UPDATER_GITHUB_REPO, QUICK_WP_LANG_VERSION, 'quick_wp_lang_updater_enabled' );
}

/**
 * Initialize the plugin.
 *
 * @since 1.0.0
 */
function quick_wp_lang_run(): void {
	$plugin = Quick_WP_Lang\Plugin::instance();
	$plugin->run();
}
quick_wp_lang_run();
