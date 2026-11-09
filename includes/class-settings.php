<?php
/**
 * Settings page handler.
 *
 * @package Quick_WP_Lang
 * @since 1.0.0
 */

namespace Quick_WP_Lang;

// Exit if accessed directly.
defined( 'ABSPATH' ) || die();

/**
 * Settings page: enabled languages, post types and the Content-Language header.
 *
 * @since 1.0.0
 */
class Settings {
	/**
	 * Register the settings page hooks.
	 *
	 * @since 1.0.0
	 */
	public function run(): void {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Add the settings page under Settings.
	 *
	 * @since 1.0.0
	 */
	public function add_settings_page(): void {
		add_options_page(
			__( 'Content Language Settings', 'quick-wp-lang' ),
			_x( 'Content Language', 'admin menu label', 'quick-wp-lang' ),
			'manage_options',
			SETTINGS_PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Enqueue the settings page script and stylesheet.
	 *
	 * @since 1.1.0
	 *
	 * @param mixed $hook Current admin page hook suffix.
	 */
	public function enqueue_admin_scripts( mixed $hook ): void {
		if ( 'settings_page_' . SETTINGS_PAGE_SLUG === $hook ) {
			wp_enqueue_style( HANDLE_ADMIN_STYLE, QUICK_WP_LANG_URL . 'assets/admin/admin.css', array(), QUICK_WP_LANG_VERSION );
			wp_enqueue_script( HANDLE_SETTINGS_SCRIPT, QUICK_WP_LANG_URL . 'assets/admin/settings.js', array(), QUICK_WP_LANG_VERSION, true );
		}
	}

	/**
	 * Register the plugin's settings, section and fields.
	 *
	 * @since 1.0.0
	 */
	public function register_settings(): void {
		register_setting(
			SETTINGS_OPTION_GROUP,
			OPTION_ENABLED_LANGUAGES,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_enabled_languages' ),
				'default'           => array(),
			)
		);

		register_setting(
			SETTINGS_OPTION_GROUP,
			OPTION_ENABLE_CONTENT_LANGUAGE_HEADER,
			array(
				'type'              => 'boolean',
				'sanitize_callback' => array( $this, 'sanitize_boolean' ),
				'default'           => DEFAULT_ENABLE_CONTENT_LANGUAGE_HEADER,
			)
		);

		register_setting(
			SETTINGS_OPTION_GROUP,
			OPTION_ENABLED_POST_TYPES,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_enabled_post_types' ),
				'default'           => DEFAULT_POST_TYPES,
			)
		);

		register_setting(
			SETTINGS_OPTION_GROUP,
			OPTION_DELETE_DATA_ON_UNINSTALL,
			array(
				'type'              => 'boolean',
				'sanitize_callback' => array( $this, 'sanitize_boolean' ),
				'default'           => DEFAULT_DELETE_DATA_ON_UNINSTALL,
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

		add_settings_section(
			SETTINGS_UNINSTALL_ID,
			_x( 'Uninstall', 'settings section heading', 'quick-wp-lang' ),
			'__return_false',
			SETTINGS_PAGE_SLUG
		);

		add_settings_field(
			'qwl_delete_data_on_uninstall_field',
			__( 'Plugin data', 'quick-wp-lang' ),
			array( $this, 'render_delete_data_field' ),
			SETTINGS_PAGE_SLUG,
			SETTINGS_UNINSTALL_ID
		);
	}

	/**
	 * Render the delete-data-on-uninstall checkbox.
	 *
	 * @since 1.3.0
	 */
	public function render_delete_data_field(): void {
		$enabled = (bool) filter_var(
			get_option( OPTION_DELETE_DATA_ON_UNINSTALL, DEFAULT_DELETE_DATA_ON_UNINSTALL ),
			FILTER_VALIDATE_BOOLEAN
		);

		printf(
			'<label for="qwl_delete_data_on_uninstall"><input type="checkbox" name="%s" id="qwl_delete_data_on_uninstall" value="1"%s /> %s</label>',
			esc_attr( OPTION_DELETE_DATA_ON_UNINSTALL ),
			checked( $enabled, true, false ),
			esc_html__( 'Delete all plugin data when uninstalled', 'quick-wp-lang' )
		);

		printf(
			'<p class="description">%s</p>',
			esc_html__( 'When ticked, deleting the plugin from the Plugins screen removes its settings and every language and translation link set on your content. Deactivating never deletes anything.', 'quick-wp-lang' )
		);
	}

	/**
	 * Render the settings page.
	 *
	 * @since 1.0.0
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'quick-wp-lang' ) );
		}

		echo '<div class="wrap">';
		printf( '<h1>%s</h1>', esc_html( get_admin_page_title() ) );
		echo '<form method="post" action="options.php">';

		settings_fields( SETTINGS_OPTION_GROUP );
		do_settings_sections( SETTINGS_PAGE_SLUG );
		submit_button();

		echo '</form>';
		echo '</div>';
	}

	/**
	 * Render the section description.
	 *
	 * @since 1.0.0
	 */
	public function render_section_description(): void {
		printf(
			'<p>%s</p>',
			esc_html__( 'Select the languages you want to make available for content assignment. These will appear as options in post/page editor sidebars.', 'quick-wp-lang' )
		);
	}

	/**
	 * Render the language checkboxes, enabled languages first.
	 *
	 * @since 1.0.0
	 */
	public function render_languages_field(): void {
		$all_languages     = get_available_languages_list();
		$enabled_languages = get_option( OPTION_ENABLED_LANGUAGES, array() );
		$enabled_languages = is_array( $enabled_languages ) ? $enabled_languages : array();
		$site_locale       = get_locale();
		$total_count       = count( $all_languages );
		$enabled_count     = count( array_intersect( array_keys( $all_languages ), $enabled_languages ) );
		$sorted_languages  = $this->sort_languages_by_enabled_status( $all_languages, $enabled_languages );

		printf(
			'<div class="qwl-search-wrap"><input type="text" id="qwl-language-search" placeholder="%s" /> <button type="button" id="qwl-language-search-clear" class="button">%s</button></div>',
			esc_attr__( 'Search languages by name or code...', 'quick-wp-lang' ),
			esc_html_x( 'Clear', 'button label; empties the search box', 'quick-wp-lang' )
		);

		printf(
			'<p class="qwl-language-count">%s</p>',
			esc_html(
				sprintf(
					/* translators: 1: Number of enabled languages, 2: Total number of available languages. */
					_n( '%1$d of %2$d language enabled', '%1$d of %2$d languages enabled', $total_count, 'quick-wp-lang' ),
					$enabled_count,
					$total_count
				)
			)
		);

		echo '<fieldset>';
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
				esc_attr( OPTION_ENABLED_LANGUAGES ),
				esc_attr( $field_id ),
				esc_attr( $locale ),
				checked( $checked, true, false ),
				$disabled, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Boolean disabled attribute.
				esc_html( $name ),
				esc_html( $locale ),
				$suffix // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped above.
			);
		}

		echo '</fieldset>';

		printf(
			'<p class="description qwl-field-help">%s</p>',
			esc_html__( 'Select at least one language to enable language selection in your posts and pages.', 'quick-wp-lang' )
		);
	}

	/**
	 * Render the Content-Language header checkbox.
	 *
	 * @since 1.0.0
	 */
	public function render_header_toggle_field(): void {
		$enabled = is_content_language_header_enabled();

		printf(
			'<label for="qwl_enable_content_language_header"><input type="checkbox" name="%s" id="qwl_enable_content_language_header" value="1"%s /> %s</label>',
			esc_attr( OPTION_ENABLE_CONTENT_LANGUAGE_HEADER ),
			checked( $enabled, true, false ),
			esc_html__( 'Send Content-Language HTTP header for posts with alternative languages', 'quick-wp-lang' )
		);

		printf(
			'<p class="description">%s</p>',
			esc_html__( 'When enabled, the plugin will send a Content-Language HTTP header for posts/pages with an assigned language. This helps search engines and browsers identify the content language.', 'quick-wp-lang' )
		);
	}

	/**
	 * Render the post type checkboxes.
	 *
	 * @since 1.1.0
	 */
	public function render_post_types_field(): void {
		$all_post_types     = get_post_types( array( 'public' => true ), 'objects' );
		$enabled_post_types = get_enabled_post_types();

		echo '<fieldset>';
		printf( '<legend class="screen-reader-text"><span>%s</span></legend>', esc_html__( 'Enable for Post Types', 'quick-wp-lang' ) );

		foreach ( $all_post_types as $post_type_name => $post_type_obj ) {
			$field_id = 'qwl_post_type_' . esc_attr( $post_type_name );
			$checked  = in_array( $post_type_name, $enabled_post_types, true );

			printf(
				'<div><label for="%s"><input type="checkbox" name="%s[]" id="%s" value="%s"%s /> %s <code>%s</code></label></div>',
				esc_attr( $field_id ),
				esc_attr( OPTION_ENABLED_POST_TYPES ),
				esc_attr( $field_id ),
				esc_attr( $post_type_name ),
				checked( $checked, true, false ),
				esc_html( $post_type_obj->labels->name ),
				esc_html( $post_type_name )
			);
		}

		echo '</fieldset>';

		printf(
			'<p class="description qwl-field-help">%s</p>',
			esc_html__( 'Select which post types show the language box in the editor and the Language column in the list of posts. Untick them all to hide both everywhere.', 'quick-wp-lang' )
		);
	}

	/**
	 * Keep only submitted locales that are in the available languages list.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Submitted value.
	 *
	 * @return array<string> Locale codes.
	 */
	public function sanitize_enabled_languages( mixed $value ): array {
		$result = array();

		if ( is_array( $value ) ) {
			$all_languages = get_available_languages_list();

			foreach ( $value as $locale ) {
				if ( is_string( $locale ) && isset( $all_languages[ $locale ] ) ) {
					$result[] = $locale;
				}
			}
		}

		return $result;
	}

	/**
	 * Keep only submitted post types that are registered and public.
	 *
	 * @since 1.1.0
	 *
	 * @param mixed $value Submitted value.
	 *
	 * @return array<string> Post type names.
	 */
	public function sanitize_enabled_post_types( mixed $value ): array {
		$result = array();

		if ( is_array( $value ) ) {
			$all_post_types = get_post_types( array( 'public' => true ), 'names' );

			foreach ( $value as $post_type ) {
				if ( is_string( $post_type ) && in_array( $post_type, $all_post_types, true ) ) {
					$result[] = $post_type;
				}
			}
		}

		return $result;
	}

	/**
	 * Read a submitted checkbox as a boolean.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed $value Submitted value.
	 *
	 * @return bool
	 */
	public function sanitize_boolean( mixed $value ): bool {
		return (bool) filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Sort the languages so enabled ones come first, keeping list order within each group.
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

		return array_merge( $enabled, $disabled );
	}
}
