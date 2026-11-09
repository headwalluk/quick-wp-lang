<?php
/**
 * Main Plugin class.
 *
 * @package Quick_WP_Lang
 * @since 1.0.0
 */

namespace Quick_WP_Lang;

// Exit if accessed directly.
defined( 'ABSPATH' ) || die();

/**
 * Main plugin bootstrap: registers hooks and lazy-loads the admin and public handlers.
 *
 * @since 1.0.0
 */
class Plugin {

	/**
	 * Single instance of the class.
	 *
	 * @since 1.3.0
	 * @var ?Plugin
	 */
	private static ?Plugin $instance = null;

	/**
	 * Settings handler.
	 *
	 * @since 1.0.0
	 * @var ?Settings
	 */
	private ?Settings $settings = null;

	/**
	 * Admin hooks handler.
	 *
	 * @since 1.0.0
	 * @var ?Admin_Hooks
	 */
	private ?Admin_Hooks $admin_hooks = null;

	/**
	 * Public hooks handler.
	 *
	 * @since 1.0.0
	 * @var ?Public_Hooks
	 */
	private ?Public_Hooks $public_hooks = null;

	/**
	 * Get the single instance of the class.
	 *
	 * @since 1.3.0
	 * @return Plugin
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register all hooks.
	 *
	 * @since 1.0.0
	 */
	public function run(): void {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		$this->settings = new Settings();
		$this->settings->run();

		if ( is_admin() ) {
			add_action( 'add_meta_boxes', array( $this->get_admin_hooks(), 'register_meta_box' ) );
			add_action( 'save_post', array( $this->get_admin_hooks(), 'save_meta_box' ), 10, 2 );
			add_action( 'admin_notices', array( $this->get_admin_hooks(), 'show_no_languages_notice' ) );
			add_action( 'admin_enqueue_scripts', array( $this->get_admin_hooks(), 'enqueue_admin_assets' ) );
			add_action( 'wp_ajax_' . AJAX_ACTION_DISMISS_NOTICE, array( $this->get_admin_hooks(), 'ajax_dismiss_notice' ) );
			add_action( 'pre_get_posts', array( $this->get_admin_hooks(), 'sort_by_language_column' ) );
			add_filter( 'plugin_action_links_' . QUICK_WP_LANG_BASENAME, array( $this, 'add_plugin_action_links' ) );

			// On admin_init so a theme's quick_wp_lang_supported_post_types filter is already added.
			add_action( 'admin_init', array( $this, 'register_language_columns' ) );
		} else {
			add_filter( 'language_attributes', array( $this->get_public_hooks(), 'filter_language_attributes' ), PRIORITY_LANGUAGE_ATTRIBUTES );
			add_action( 'wp_head', array( $this->get_public_hooks(), 'output_hreflang_tags' ), PRIORITY_WP_HEAD );
			add_action( 'wp_head', array( $this->get_public_hooks(), 'output_og_locale' ), PRIORITY_WP_HEAD );
			add_action( 'send_headers', array( $this->get_public_hooks(), 'send_content_language_header' ) );
			add_filter( 'wpseo_locale', array( $this->get_public_hooks(), 'filter_yoast_locale' ) );
			add_filter( 'wpseo_schema_piece_language', array( $this->get_public_hooks(), 'filter_yoast_schema_language' ), 10, 2 );
		}
	}

	/**
	 * Load the plugin text domain from the bundled languages/ directory.
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain( 'quick-wp-lang', false, dirname( QUICK_WP_LANG_BASENAME ) . '/languages' );
	}

	/**
	 * Add a Settings link to the plugin's row on the Plugins screen.
	 *
	 * @since 1.1.1
	 *
	 * @param mixed $links Existing action links.
	 *
	 * @return mixed Action links with Settings first, or the input unchanged if it isn't an array.
	 */
	public function add_plugin_action_links( mixed $links ): mixed {
		if ( is_array( $links ) ) {
			array_unshift(
				$links,
				sprintf(
					'<a href="%s">%s</a>',
					esc_url( get_settings_page_url() ),
					esc_html_x( 'Settings', 'plugin action link', 'quick-wp-lang' )
				)
			);
		}

		return $links;
	}

	/**
	 * Add the language column to the list table of every supported post type.
	 *
	 * @since 1.0.0
	 */
	public function register_language_columns(): void {
		$admin_hooks = $this->get_admin_hooks();

		foreach ( get_supported_post_types() as $post_type ) {
			add_filter( "manage_{$post_type}_posts_columns", array( $admin_hooks, 'add_language_column' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( $admin_hooks, 'render_language_column' ), 10, 2 );
			add_filter( "manage_edit-{$post_type}_sortable_columns", array( $admin_hooks, 'make_language_column_sortable' ) );
		}
	}

	/**
	 * Get the admin hooks handler, creating it on first use.
	 *
	 * @since 1.0.0
	 * @return Admin_Hooks
	 */
	public function get_admin_hooks(): Admin_Hooks {
		if ( null === $this->admin_hooks ) {
			$this->admin_hooks = new Admin_Hooks();
		}

		return $this->admin_hooks;
	}

	/**
	 * Get the public hooks handler, creating it on first use.
	 *
	 * @since 1.0.0
	 * @return Public_Hooks
	 */
	public function get_public_hooks(): Public_Hooks {
		if ( null === $this->public_hooks ) {
			$this->public_hooks = new Public_Hooks();
		}

		return $this->public_hooks;
	}
}
