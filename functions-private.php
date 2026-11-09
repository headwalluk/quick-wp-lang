<?php
/**
 * Plugin functions.
 *
 * Internal namespaced helpers. Everything here is private to the plugin; sites should use
 * the hooks documented in docs/developers/hooks-and-filters.md instead.
 *
 * @package Quick_WP_Lang
 * @since 1.0.0
 */

namespace Quick_WP_Lang;

// Exit if accessed directly.
defined( 'ABSPATH' ) || die();

/**
 * Get the curated list of available languages, as locale => name.
 *
 * @since 1.0.0
 *
 * @return array<string, string> Array of locale => language name pairs.
 */
function get_available_languages_list(): array {
	$languages = array(
		'af'    => __( 'Afrikaans', 'quick-wp-lang' ),
		'ar'    => __( 'Arabic', 'quick-wp-lang' ),
		'bg_BG' => __( 'Bulgarian', 'quick-wp-lang' ),
		'ca'    => __( 'Catalan', 'quick-wp-lang' ),
		'cs_CZ' => __( 'Czech', 'quick-wp-lang' ),
		'cy'    => __( 'Welsh', 'quick-wp-lang' ),
		'da_DK' => __( 'Danish', 'quick-wp-lang' ),
		'de_DE' => __( 'German', 'quick-wp-lang' ),
		'de_CH' => __( 'German (Switzerland)', 'quick-wp-lang' ),
		'de_AT' => __( 'German (Austria)', 'quick-wp-lang' ),
		'el'    => __( 'Greek', 'quick-wp-lang' ),
		'en_GB' => __( 'English (UK)', 'quick-wp-lang' ),
		'en_US' => __( 'English (US)', 'quick-wp-lang' ),
		'en_AU' => __( 'English (Australia)', 'quick-wp-lang' ),
		'en_CA' => __( 'English (Canada)', 'quick-wp-lang' ),
		'eo'    => __( 'Esperanto', 'quick-wp-lang' ),
		'es_ES' => __( 'Spanish (Spain)', 'quick-wp-lang' ),
		'es_MX' => __( 'Spanish (Mexico)', 'quick-wp-lang' ),
		'es_AR' => __( 'Spanish (Argentina)', 'quick-wp-lang' ),
		'et'    => __( 'Estonian', 'quick-wp-lang' ),
		'eu'    => __( 'Basque', 'quick-wp-lang' ),
		'fa_IR' => __( 'Persian', 'quick-wp-lang' ),
		'fi'    => __( 'Finnish', 'quick-wp-lang' ),
		'fr_FR' => __( 'French (France)', 'quick-wp-lang' ),
		'fr_CA' => __( 'French (Canada)', 'quick-wp-lang' ),
		'he_IL' => __( 'Hebrew', 'quick-wp-lang' ),
		'hi_IN' => __( 'Hindi', 'quick-wp-lang' ),
		'hr'    => __( 'Croatian', 'quick-wp-lang' ),
		'hu_HU' => __( 'Hungarian', 'quick-wp-lang' ),
		'id_ID' => __( 'Indonesian', 'quick-wp-lang' ),
		'is_IS' => __( 'Icelandic', 'quick-wp-lang' ),
		'it_IT' => __( 'Italian', 'quick-wp-lang' ),
		'ja'    => __( 'Japanese', 'quick-wp-lang' ),
		'ko_KR' => __( 'Korean', 'quick-wp-lang' ),
		'lt_LT' => __( 'Lithuanian', 'quick-wp-lang' ),
		'lv'    => __( 'Latvian', 'quick-wp-lang' ),
		'nl_NL' => __( 'Dutch', 'quick-wp-lang' ),
		'nl_BE' => __( 'Dutch (Belgium)', 'quick-wp-lang' ),
		'no_NO' => __( 'Norwegian', 'quick-wp-lang' ),
		'pl_PL' => __( 'Polish', 'quick-wp-lang' ),
		'pt_BR' => __( 'Portuguese (Brazil)', 'quick-wp-lang' ),
		'pt_PT' => __( 'Portuguese (Portugal)', 'quick-wp-lang' ),
		'ro_RO' => __( 'Romanian', 'quick-wp-lang' ),
		'ru_RU' => __( 'Russian', 'quick-wp-lang' ),
		'sk_SK' => __( 'Slovak', 'quick-wp-lang' ),
		'sl_SI' => __( 'Slovenian', 'quick-wp-lang' ),
		'sq'    => __( 'Albanian', 'quick-wp-lang' ),
		'sr_RS' => __( 'Serbian', 'quick-wp-lang' ),
		'sv_SE' => __( 'Swedish', 'quick-wp-lang' ),
		'th'    => __( 'Thai', 'quick-wp-lang' ),
		'tr_TR' => __( 'Turkish', 'quick-wp-lang' ),
		'uk'    => __( 'Ukrainian', 'quick-wp-lang' ),
		'vi'    => __( 'Vietnamese', 'quick-wp-lang' ),
		'zh_CN' => __( 'Chinese (Simplified)', 'quick-wp-lang' ),
		'zh_TW' => __( 'Chinese (Traditional)', 'quick-wp-lang' ),
	);

	/**
	 * Filter the available languages list.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, string> $languages Array of locale => language name pairs.
	 */
	$languages = apply_filters( 'quick_wp_lang_languages', $languages );

	return is_array( $languages ) ? $languages : array();
}

/**
 * Get the languages enabled on the settings page, as locale => name.
 *
 * @since 1.0.0
 *
 * @return array<string, string> Array of locale => language name pairs.
 */
function get_enabled_languages_list(): array {
	$result          = array();
	$enabled_locales = get_option( OPTION_ENABLED_LANGUAGES, array() );

	if ( is_array( $enabled_locales ) && ! empty( $enabled_locales ) ) {
		$all_languages = get_available_languages_list();

		foreach ( $enabled_locales as $locale ) {
			if ( is_string( $locale ) && isset( $all_languages[ $locale ] ) ) {
				$result[ $locale ] = $all_languages[ $locale ];
			}
		}
	}

	return $result;
}

/**
 * Get the post types ticked on the settings page, before the supported-post-types filter.
 *
 * @since 1.3.0
 *
 * @return array<string> Post type names.
 */
function get_enabled_post_types(): array {
	$enabled_post_types = get_option( OPTION_ENABLED_POST_TYPES, array() );
	$enabled_post_types = is_array( $enabled_post_types ) ? $enabled_post_types : array();

	if ( empty( $enabled_post_types ) ) {
		$enabled_post_types = DEFAULT_POST_TYPES;
	}

	return $enabled_post_types;
}

/**
 * Get the post types that show the language meta box and list-table column.
 *
 * @since 1.3.0
 *
 * @return array<string> Post type names.
 */
function get_supported_post_types(): array {
	/**
	 * Filter the post types that support the language meta box and column.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string> $post_types Array of post type names.
	 */
	$post_types = apply_filters( 'quick_wp_lang_supported_post_types', get_enabled_post_types() );

	return is_array( $post_types ) ? array_values( array_filter( $post_types, 'is_string' ) ) : array();
}

/**
 * Get the language override stored against a post, or '' for the site default.
 *
 * @since 1.3.0
 *
 * @param int $post_id Post ID.
 *
 * @return string WordPress locale, e.g. 'fr_FR', or ''.
 */
function get_post_language( int $post_id ): string {
	$language = get_post_meta( $post_id, META_LANGUAGE, true );

	return is_string( $language ) ? $language : DEFAULT_LANGUAGE;
}

/**
 * Convert a WordPress locale to a BCP 47 language tag, e.g. 'en_GB' to 'en-GB'.
 *
 * @since 1.0.0
 *
 * @param string $locale WordPress locale.
 *
 * @return string BCP 47 language tag.
 */
function locale_to_bcp47( string $locale ): string {
	return str_replace( '_', '-', $locale );
}

/**
 * Get the URL of the plugin's settings page.
 *
 * @since 1.3.0
 *
 * @return string Settings page URL.
 */
function get_settings_page_url(): string {
	return admin_url( 'options-general.php?page=' . SETTINGS_PAGE_SLUG );
}

/**
 * Whether the Content-Language header setting is on.
 *
 * @since 1.3.0
 *
 * @return bool
 */
function is_content_language_header_enabled(): bool {
	return (bool) filter_var(
		get_option( OPTION_ENABLE_CONTENT_LANGUAGE_HEADER, DEFAULT_ENABLE_CONTENT_LANGUAGE_HEADER ),
		FILTER_VALIDATE_BOOLEAN
	);
}
