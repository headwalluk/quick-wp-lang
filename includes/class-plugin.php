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
		}

		// Register public hooks.
		if ( ! is_admin() ) {
			add_filter( 'language_attributes', array( $this->get_public_hooks(), 'filter_language_attributes' ), PRIORITY_LANGUAGE_ATTRIBUTES );
			add_action( 'wp_head', array( $this->get_public_hooks(), 'output_hreflang_tags' ), PRIORITY_WP_HEAD );
			add_action( 'wp_head', array( $this->get_public_hooks(), 'output_og_locale' ), PRIORITY_WP_HEAD );
			add_action( 'send_headers', array( $this->get_public_hooks(), 'send_content_language_header' ) );
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
