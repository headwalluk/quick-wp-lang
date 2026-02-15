<?php
/**
 * Settings page handler.
 *
 * @package Quick_WP_Lang
 * @since 1.0.0
 */

namespace Quick_WP_Lang;

defined( 'ABSPATH' ) || die();

/**
 * Handles plugin settings page.
 *
 * Manages the admin settings page for selecting which languages to enable.
 *
 * @since 1.0.0
 */
class Settings {
	/**
	 * Register settings and add menu items.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Add settings page to admin menu.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_settings_page(): void {
		add_options_page(
			__( 'Content Language Settings', 'quick-wp-lang' ),
			__( 'Content Language', 'quick-wp-lang' ),
			'manage_options',
			SETTINGS_PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @since 1.1.0
	 *
	 * @param string $hook Current admin page hook.
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts( string $hook ): void {
		if ( 'settings_page_' . SETTINGS_PAGE_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'qwl-settings-search',
			plugins_url( 'assets/admin/settings-search.js', __DIR__ ),
			array(),
			QWL_VERSION,
			true
		);
	}

	/**
	 * Register plugin settings.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			SETTINGS_OPTION_GROUP,
			OPT_ENABLED_LANGUAGES,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_enabled_languages' ),
				'default'           => array(),
			)
		);

		register_setting(
			SETTINGS_OPTION_GROUP,
			OPT_ENABLE_CONTENT_LANGUAGE_HEADER,
			array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => true,
			)
		);

		add_settings_section(
			SETTINGS_SECTION_ID,
			__( 'Available Languages', 'quick-wp-lang' ),
			array( $this, 'render_section_description' ),
			SETTINGS_PAGE_SLUG
		);

		add_settings_field(
			'qwl_enabled_languages_field',
			__( 'Enable Languages', 'quick-wp-lang' ),
			array( $this, 'render_languages_field' ),
			SETTINGS_PAGE_SLUG,
			SETTINGS_SECTION_ID
		);

		add_settings_field(
			'qwl_enable_content_language_header_field',
			__( 'Content-Language Header', 'quick-wp-lang' ),
			array( $this, 'render_header_toggle_field' ),
			SETTINGS_PAGE_SLUG,
			SETTINGS_SECTION_ID
		);
	}

	/**
	 * Render settings page.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		$capability_check = current_user_can( 'manage_options' );

		if ( ! $capability_check ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'quick-wp-lang' ) );
		}

		printf( '<div class="wrap">' );
		printf( '<h1>%s</h1>', esc_html( get_admin_page_title() ) );
		printf( '<form method="post" action="options.php">' );

		settings_fields( SETTINGS_OPTION_GROUP );
		do_settings_sections( SETTINGS_PAGE_SLUG );
		submit_button( __( 'Save Languages', 'quick-wp-lang' ) );

		printf( '</form>' );
		printf( '</div>' );
	}

	/**
	 * Render section description.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		printf(
			'<p>%s</p>',
			esc_html__( 'Select the languages you want to make available for content assignment. These will appear as options in post/page editor sidebars.', 'quick-wp-lang' )
		);
	}

	/**
	 * Render languages checkboxes field.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_languages_field(): void {
		$all_languages     = get_available_languages_list();
		$enabled_languages = get_option( OPT_ENABLED_LANGUAGES, array() );
		$enabled_languages = is_array( $enabled_languages ) ? $enabled_languages : array();
		$site_locale       = get_locale();

		// Search box.
		printf(
			'<div style="margin-bottom: 12px;"><input type="text" id="qwl-language-search" placeholder="%s" style="width: 100%%; max-width: 400px;" /> <button type="button" id="qwl-language-search-clear" class="button" style="vertical-align: top;">%s</button></div>',
			esc_attr__( 'Search languages by name or code...', 'quick-wp-lang' ),
			esc_html__( 'Clear', 'quick-wp-lang' )
		);

		printf( '<fieldset>' );
		printf( '<legend class="screen-reader-text"><span>%s</span></legend>', esc_html__( 'Enable Languages', 'quick-wp-lang' ) );

		foreach ( $all_languages as $locale => $name ) {
			$field_id       = 'qwl_lang_' . esc_attr( $locale );
			$checked        = in_array( $locale, $enabled_languages, true );
			$is_site_locale = ( $locale === $site_locale );
			$disabled       = $is_site_locale ? ' disabled' : '';
			$suffix         = $is_site_locale ? ' <em>' . esc_html__( '(site default language)', 'quick-wp-lang' ) . '</em>' : '';

			printf(
				'<div class="qwl-language-item" data-name="%s" data-locale="%s"><label for="%s"><input type="checkbox" name="%s[]" id="%s" value="%s"%s%s /> %s%s</label></div>',
				esc_attr( $name ),
				esc_attr( $locale ),
				esc_attr( $field_id ),
				esc_attr( OPT_ENABLED_LANGUAGES ),
				esc_attr( $field_id ),
				esc_attr( $locale ),
				checked( $checked, true, false ),
				$disabled, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Boolean disabled attribute.
				esc_html( $name ),
				$suffix // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped above.
			);
		}

		printf( '</fieldset>' );

		// Empty state help text.
		printf(
			'<p class="description" style="margin-top: 12px;">%s</p>',
			esc_html__( 'Select at least one language to enable language selection in your posts and pages.', 'quick-wp-lang' )
		);
	}

	/**
	 * Render Content-Language header toggle field.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_header_toggle_field(): void {
		$enabled = get_option( OPT_ENABLE_CONTENT_LANGUAGE_HEADER, true );
		$enabled = rest_sanitize_boolean( $enabled );

		printf(
			'<label for="qwl_enable_content_language_header"><input type="checkbox" name="%s" id="qwl_enable_content_language_header" value="1"%s /> %s</label>',
			esc_attr( OPT_ENABLE_CONTENT_LANGUAGE_HEADER ),
			checked( $enabled, true, false ),
			esc_html__( 'Send Content-Language HTTP header for posts with alternative languages', 'quick-wp-lang' )
		);

		printf(
			'<p class="description">%s</p>',
			esc_html__( 'When enabled, the plugin will send a Content-Language HTTP header for posts/pages with an assigned language. This helps search engines and browsers identify the content language.', 'quick-wp-lang' )
		);
	}

	/**
	 * Sanitize enabled languages array.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Submitted value.
	 *
	 * @return array<string> Sanitized array of language codes.
	 */
	public function sanitize_enabled_languages( $value ): array {
		$result = array();

		if ( ! is_array( $value ) ) {
			return $result;
		}

		$all_languages = get_available_languages_list();

		foreach ( $value as $locale ) {
			if ( is_string( $locale ) && isset( $all_languages[ $locale ] ) ) {
				$result[] = $locale;
			}
		}

		return $result;
	}
}
