<?php
/**
 * Main Plugin class.
 *
 * @package Quick_WP_Lang
 * @since 1.0.0
 */

namespace Quick_WP_Lang;

defined( 'ABSPATH' ) || die();

/**
 * Main plugin bootstrap class.
 *
 * Handles initialization and hook registration.
 *
 * @since 1.0.0
 */
class Plugin {
	/**
	 * Admin hooks handler.
	 *
	 * @since 1.0.0
	 *
	 * @var Admin_Hooks|null
	 */
	private ?Admin_Hooks $admin_hooks = null;

	/**
	 * Public hooks handler.
	 *
	 * @since 1.0.0
	 *
	 * @var Public_Hooks|null
	 */
	private ?Public_Hooks $public_hooks = null;

	/**
	 * Settings handler.
	 *
	 * @since 1.0.0
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Settings must be instantiated early (before admin_init).
		$this->settings = new Settings();
	}

	/**
	 * Run the plugin.
	 *
	 * Registers all hooks and initializes components.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function run(): void {
		// Load plugin text domain.
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Register settings.
		$this->settings->register();

		// Register admin hooks.
		if ( is_admin() ) {
			add_action( 'add_meta_boxes', array( $this->get_admin_hooks(), 'register_meta_box' ) );
			add_action( 'save_post', array( $this->get_admin_hooks(), 'save_meta_box' ), 10, 2 );
			add_action( 'admin_notices', array( $this->get_admin_hooks(), 'show_no_languages_notice' ) );
			add_action( 'admin_enqueue_scripts', array( $this->get_admin_hooks(), 'enqueue_admin_assets' ) );
			add_action( 'wp_ajax_qwl_dismiss_notice', array( $this->get_admin_hooks(), 'ajax_dismiss_notice' ) );

			// Add language column to post types.
			$this->register_language_columns();
		}

		// Register public hooks.
		if ( ! is_admin() ) {
			add_filter( 'language_attributes', array( $this->get_public_hooks(), 'filter_language_attributes' ), PRIORITY_LANGUAGE_ATTRIBUTES );
			add_action( 'wp_head', array( $this->get_public_hooks(), 'output_hreflang_tags' ), PRIORITY_WP_HEAD );
			add_action( 'wp_head', array( $this->get_public_hooks(), 'output_og_locale' ), PRIORITY_WP_HEAD );

			// Only send Content-Language header if enabled in settings.
			$header_enabled = get_option( OPT_ENABLE_CONTENT_LANGUAGE_HEADER, true );
			if ( rest_sanitize_boolean( $header_enabled ) ) {
				add_action( 'send_headers', array( $this->get_public_hooks(), 'send_content_language_header' ) );
			}
		}
	}

	/**
	 * Load plugin text domain for translations.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'quick-wp-lang',
			false,
			dirname( plugin_basename( QWL_PLUGIN_FILE ) ) . '/languages'
		);
	}

	/**
	 * Register language column for all supported post types.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function register_language_columns(): void {
		$post_types = get_post_types( array( 'public' => true ), 'names' );

		/**
		 * Filter the post types that support the language column.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string> $post_types Array of post type names.
		 */
		$post_types = apply_filters( 'qwl_supported_post_types', $post_types );

		if ( ! is_array( $post_types ) ) {
			return;
		}

		foreach ( $post_types as $post_type ) {
			add_filter( "manage_{$post_type}_posts_columns", array( $this->get_admin_hooks(), 'add_language_column' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( $this->get_admin_hooks(), 'render_language_column' ), 10, 2 );
			add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this->get_admin_hooks(), 'make_language_column_sortable' ) );
		}

		// Add query modifier for sorting.
		add_action( 'pre_get_posts', array( $this->get_admin_hooks(), 'sort_by_language_column' ) );
	}

	/**
	 * Get admin hooks instance (lazy loading).
	 *
	 * @since 1.0.0
	 *
	 * @return Admin_Hooks Admin hooks instance.
	 */
	public function get_admin_hooks(): Admin_Hooks {
		if ( is_null( $this->admin_hooks ) ) {
			$this->admin_hooks = new Admin_Hooks();
		}
		return $this->admin_hooks;
	}

	/**
	 * Get public hooks instance (lazy loading).
	 *
	 * @since 1.0.0
	 *
	 * @return Public_Hooks Public hooks instance.
	 */
	public function get_public_hooks(): Public_Hooks {
		if ( is_null( $this->public_hooks ) ) {
			$this->public_hooks = new Public_Hooks();
		}
		return $this->public_hooks;
	}
}
