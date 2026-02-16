<?php
/**
 * Private helper functions.
 *
 * @package Quick_WP_Lang
 * @since 1.0.0
 */

namespace Quick_WP_Lang;

defined( 'ABSPATH' ) || die();

/**
 * Get the plugin instance.
 *
 * @since 1.0.0
 *
 * @return Plugin The plugin instance.
 */
function get_plugin_instance(): Plugin {
	global $quick_wp_lang_plugin_instance;
	return $quick_wp_lang_plugin_instance;
}
