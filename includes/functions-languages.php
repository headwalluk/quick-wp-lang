<?php
/**
 * Language helper functions.
 *
 * @package Quick_WP_Lang
 * @since 1.0.0
 */

namespace Quick_WP_Lang;

defined( 'ABSPATH' ) || die();

/**
 * Get the list of available languages.
 *
 * Returns a curated list of ~50 common languages with their WordPress locale codes.
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
	 * Allows developers to add, remove, or modify the language options.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, string> $languages Array of locale => language name pairs.
	 */
	$languages = apply_filters( 'quick_wp_lang_languages', $languages );

	return is_array( $languages ) ? $languages : array();
}

/**
 * Get the list of languages enabled by the user.
 *
 * Returns only the languages the user has selected on the settings page.
 *
 * @since 1.0.0
 *
 * @return array<string, string> Array of locale => language name pairs.
 */
function get_enabled_languages_list(): array {
	$result = array();

	$enabled_locales = get_option( OPT_ENABLED_LANGUAGES, array() );
	$enabled_locales = is_array( $enabled_locales ) ? $enabled_locales : array();

	if ( empty( $enabled_locales ) ) {
		return $result;
	}

	$all_languages = get_available_languages_list();

	foreach ( $enabled_locales as $locale ) {
		if ( isset( $all_languages[ $locale ] ) ) {
			$result[ $locale ] = $all_languages[ $locale ];
		}
	}

	return $result;
}
