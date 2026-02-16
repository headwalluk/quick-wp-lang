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
			QUICK_WP_LANG_VERSION,
			true
		);

		wp_add_inline_style(
			'wp-admin',
			'.qwl-search-wrap { margin-bottom: 12px; }' .
			'.qwl-search-wrap input[type="text"] { width: 100%; max-width: 400px; }' .
			'.qwl-search-wrap .button { vertical-align: top; }' .
			'.qwl-language-count { margin: 0 0 12px 0; font-weight: 600; }' .
			'.qwl-language-item code { color: #666; font-size: 0.9em; }' .
			'.qwl-field-help { margin-top: 12px; }'
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

		register_setting(
			SETTINGS_OPTION_GROUP,
			OPT_ENABLED_POST_TYPES,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_enabled_post_types' ),
				'default'           => array(),
			)
		);

		add_settings_section(
			SETTINGS_SECTION_ID,
			__( 'Available Languages', 'quick-wp-lang' ),
			array( $this, 'render_section_description' ),
			SETTINGS_PAGE_SLUG
		);

		add_settings_field(
			'qwl_enable_content_language_header_field',
			__( 'Content-Language Header', 'quick-wp-lang' ),
			array( $this, 'render_header_toggle_field' ),
			SETTINGS_PAGE_SLUG,
			SETTINGS_SECTION_ID
		);

		add_settings_field(
			'qwl_enabled_post_types_field',
			__( 'Enable for Post Types', 'quick-wp-lang' ),
			array( $this, 'render_post_types_field' ),
			SETTINGS_PAGE_SLUG,
			SETTINGS_SECTION_ID
		);

		add_settings_field(
			'qwl_enabled_languages_field',
			__( 'Enable Languages', 'quick-wp-lang' ),
			array( $this, 'render_languages_field' ),
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
		$total_count       = count( $all_languages );
		$enabled_count     = count( $enabled_languages );

		// Sort languages: enabled first, then disabled (alphabetically within each group).
		$sorted_languages = $this->sort_languages_by_enabled_status( $all_languages, $enabled_languages );

		// Search box.
		printf(
			'<div class="qwl-search-wrap"><input type="text" id="qwl-language-search" placeholder="%s" /> <button type="button" id="qwl-language-search-clear" class="button">%s</button></div>',
			esc_attr__( 'Search languages by name or code...', 'quick-wp-lang' ),
			esc_html__( 'Clear', 'quick-wp-lang' )
		);

		// Enabled count indicator.
		printf(
			'<p class="qwl-language-count">%s</p>',
			sprintf(
				/* translators: 1: Number of enabled languages, 2: Total number of available languages. */
				esc_html__( '%1$d of %2$d languages enabled', 'quick-wp-lang' ),
				absint( $enabled_count ),
				absint( $total_count )
			)
		);

		printf( '<fieldset>' );
		printf( '<legend class="screen-reader-text"><span>%s</span></legend>', esc_html__( 'Enable Languages', 'quick-wp-lang' ) );

		foreach ( $sorted_languages as $locale => $name ) {
			$field_id       = 'qwl_lang_' . esc_attr( $locale );
			$checked        = in_array( $locale, $enabled_languages, true );
			$is_site_locale = ( $locale === $site_locale );
			$disabled       = $is_site_locale ? ' disabled' : '';
			$suffix         = $is_site_locale ? ' <em>' . esc_html__( '(site default language)', 'quick-wp-lang' ) . '</em>' : '';

			printf(
				'<div class="qwl-language-item" data-name="%s" data-locale="%s"><label for="%s"><input type="checkbox" name="%s[]" id="%s" value="%s"%s%s /> %s <code>%s</code>%s</label></div>',
				esc_attr( $name ),
				esc_attr( $locale ),
				esc_attr( $field_id ),
				esc_attr( OPT_ENABLED_LANGUAGES ),
				esc_attr( $field_id ),
				esc_attr( $locale ),
				checked( $checked, true, false ),
				$disabled, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Boolean disabled attribute.
				esc_html( $name ),
				esc_html( $locale ),
				$suffix // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped above.
			);
		}

		printf( '</fieldset>' );

		// Empty state help text.
		printf(
			'<p class="description qwl-field-help">%s</p>',
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
	 * Render post types checkboxes field.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function render_post_types_field(): void {
		$all_post_types     = get_post_types( array( 'public' => true ), 'objects' );
		$enabled_post_types = get_option( OPT_ENABLED_POST_TYPES, array() );
		$enabled_post_types = is_array( $enabled_post_types ) ? $enabled_post_types : array();

		// If option is empty, default to post and page.
		if ( empty( $enabled_post_types ) ) {
			$enabled_post_types = array( 'post', 'page' );
		}

		printf( '<fieldset>' );
		printf( '<legend class="screen-reader-text"><span>%s</span></legend>', esc_html__( 'Enable for Post Types', 'quick-wp-lang' ) );

		foreach ( $all_post_types as $post_type_name => $post_type_obj ) {
			$field_id = 'qwl_post_type_' . esc_attr( $post_type_name );
			$checked  = in_array( $post_type_name, $enabled_post_types, true );

			printf(
				'<div><label for="%s"><input type="checkbox" name="%s[]" id="%s" value="%s"%s /> %s <code>%s</code></label></div>',
				esc_attr( $field_id ),
				esc_attr( OPT_ENABLED_POST_TYPES ),
				esc_attr( $field_id ),
				esc_attr( $post_type_name ),
				checked( $checked, true, false ),
				esc_html( $post_type_obj->labels->name ),
				esc_html( $post_type_name )
			);
		}

		printf( '</fieldset>' );

		printf(
			'<p class="description qwl-field-help">%s</p>',
			esc_html__( 'Select which post types should display the language meta box. If no post types are selected, all public post types will be enabled by default.', 'quick-wp-lang' )
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

	/**
	 * Sanitize enabled post types array.
	 *
	 * @since 1.1.0
	 *
	 * @param mixed $value Submitted value.
	 *
	 * @return array<string> Sanitized array of post type names.
	 */
	public function sanitize_enabled_post_types( $value ): array {
		$result = array();

		if ( ! is_array( $value ) ) {
			return $result;
		}

		$all_post_types = get_post_types( array( 'public' => true ), 'names' );

		foreach ( $value as $post_type ) {
			if ( is_string( $post_type ) && in_array( $post_type, $all_post_types, true ) ) {
				$result[] = $post_type;
			}
		}

		return $result;
	}

	/**
	 * Sort languages array with enabled languages first.
	 *
	 * @since 1.1.0
	 *
	 * @param array<string, string> $all_languages    All available languages (locale => name).
	 * @param array<string>         $enabled_languages Array of enabled locale codes.
	 *
	 * @return array<string, string> Sorted languages array.
	 */
	private function sort_languages_by_enabled_status( array $all_languages, array $enabled_languages ): array {
		$enabled  = array();
		$disabled = array();

		foreach ( $all_languages as $locale => $name ) {
			if ( in_array( $locale, $enabled_languages, true ) ) {
				$enabled[ $locale ] = $name;
			} else {
				$disabled[ $locale ] = $name;
			}
		}

		// Both groups are already sorted alphabetically by name (from get_available_languages_list).
		return array_merge( $enabled, $disabled );
	}
}
