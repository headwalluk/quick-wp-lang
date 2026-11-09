<?php
/**
 * Quick WP Lang — Uninstaller
 *
 * Runs when the plugin is deleted from the Plugins screen, not on deactivation.
 * Deletes nothing unless the "Delete all plugin data when uninstalled" setting
 * is on; see docs/configuration.md.
 *
 * @package Quick_WP_Lang
 */

namespace Quick_WP_Lang;

// Bail out if not invoked by WordPress's uninstall handler.
defined( 'WP_UNINSTALL_PLUGIN' ) || die();

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/includes/class-github-updater.php';

$quick_wp_lang_delete_data = (bool) filter_var(
	get_option( OPTION_DELETE_DATA_ON_UNINSTALL, DEFAULT_DELETE_DATA_ON_UNINSTALL ),
	FILTER_VALIDATE_BOOLEAN
);

if ( $quick_wp_lang_delete_data ) {
	global $wpdb;

	// Keys are found by prefix so data from older versions goes too; each is then
	// deleted through the WordPress API so object caches are cleared with it.
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-off prefix lookup at uninstall; there is no API for it.
	$quick_wp_lang_meta_keys = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
			$wpdb->esc_like( META_PREFIX ) . '%'
		)
	);

	$quick_wp_lang_option_names = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
			$wpdb->esc_like( OPTION_PREFIX ) . '%'
		)
	);
	// phpcs:enable

	foreach ( $quick_wp_lang_meta_keys as $quick_wp_lang_meta_key ) {
		delete_metadata( 'post', 0, $quick_wp_lang_meta_key, '', true );
	}

	foreach ( $quick_wp_lang_option_names as $quick_wp_lang_option_name ) {
		delete_option( $quick_wp_lang_option_name );
	}

	Github_Updater::delete_cache( basename( __DIR__ ) );
}
