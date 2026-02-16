<?php
/**
 * Uninstall script for Quick WP Lang
 *
 * Removes all plugin data when the plugin is deleted via the WordPress admin.
 * This file is only executed when the plugin is uninstalled through the WordPress
 * admin interface (Plugins > Delete). It is not executed on deactivation.
 *
 * @package Quick_WP_Lang
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Exit if not called by WordPress during uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Clean up plugin data
 *
 * Removes all options and post meta created by the plugin.
 * This ensures a clean uninstall with no database residue.
 */
function quick_wp_lang_uninstall_cleanup() {
	global $wpdb;

	// Remove plugin options.
	delete_option( 'qwl_enabled_languages' );
	delete_option( 'qwl_enable_content_language_header' );
	delete_option( 'qwl_dismissed_notices' );

	// Remove all post meta for language assignments.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s",
			'_qwl_language'
		)
	);

	// Clear any cached data.
	wp_cache_flush();
}

// Execute cleanup.
quick_wp_lang_uninstall_cleanup();
