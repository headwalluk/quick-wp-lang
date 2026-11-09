<?php
/**
 * Admin hooks handler.
 *
 * @package Quick_WP_Lang
 * @since 1.0.0
 */

namespace Quick_WP_Lang;

// Exit if accessed directly.
defined( 'ABSPATH' ) || die();

/**
 * Meta box, list-table column and admin notice for the per-post language.
 *
 * @since 1.0.0
 */
class Admin_Hooks {

	/**
	 * Register the language meta box on every supported post type.
	 *
	 * @since 1.0.0
	 */
	public function register_meta_box(): void {
		add_meta_box(
			META_BOX_ID,
			_x( 'Content Language', 'meta box title', 'quick-wp-lang' ),
			array( $this, 'render_meta_box' ),
			get_supported_post_types(),
			'side',
			'default'
		);
	}

	/**
	 * Render the language selection meta box.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $post Current post object.
	 */
	public function render_meta_box( mixed $post ): void {
		if ( $post instanceof \WP_Post ) {
			wp_nonce_field( NONCE_ACTION_META_BOX, NONCE_NAME_META_BOX );

			$current_language = get_post_language( $post->ID );

			printf(
				'<p class="description">%s</p>',
				esc_html__( 'Select the language for this content:', 'quick-wp-lang' )
			);

			echo '<fieldset>';

			printf(
				'<label><input type="radio" name="%s" value="%s"%s /> %s</label><br />',
				esc_attr( FIELD_LANGUAGE ),
				esc_attr( DEFAULT_LANGUAGE ),
				checked( $current_language, DEFAULT_LANGUAGE, false ),
				esc_html_x( 'Site default', 'language choice', 'quick-wp-lang' )
			);

			foreach ( get_enabled_languages_list() as $locale => $name ) {
				printf(
					'<label><input type="radio" name="%s" value="%s"%s /> %s</label><br />',
					esc_attr( FIELD_LANGUAGE ),
					esc_attr( $locale ),
					checked( $current_language, $locale, false ),
					esc_html( $name )
				);
			}

			echo '</fieldset>';

			printf(
				'<p class="description qwl-meta-box-settings-link"><a href="%s">%s</a></p>',
				esc_url( get_settings_page_url() ),
				esc_html__( 'Configure available languages', 'quick-wp-lang' )
			);

			printf(
				'<p class="description qwl-meta-box-help">%s</p>',
				esc_html__( 'If this content is written in a language other than the site default, select it here. This will update the page\'s language attributes and hreflang tags for better SEO.', 'quick-wp-lang' )
			);
		}
	}

	/**
	 * Save the language chosen in the meta box.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $post_id Post ID.
	 * @param mixed $post    Post object.
	 */
	public function save_meta_box( mixed $post_id, mixed $post ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Required by the save_post hook signature.
		$post_id     = is_numeric( $post_id ) ? (int) $post_id : 0;
		$nonce_value = isset( $_POST[ NONCE_NAME_META_BOX ] ) ? sanitize_text_field( wp_unslash( $_POST[ NONCE_NAME_META_BOX ] ) ) : '';

		$should_save = $post_id > 0
			&& wp_verify_nonce( $nonce_value, NONCE_ACTION_META_BOX )
			&& ! ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			&& current_user_can( 'edit_post', $post_id );

		if ( $should_save ) {
			// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified above.
			$language = isset( $_POST[ FIELD_LANGUAGE ] ) ? sanitize_text_field( wp_unslash( $_POST[ FIELD_LANGUAGE ] ) ) : DEFAULT_LANGUAGE;
			// phpcs:enable

			if ( ! isset( get_enabled_languages_list()[ $language ] ) ) {
				$language = DEFAULT_LANGUAGE;
			}

			if ( DEFAULT_LANGUAGE === $language ) {
				delete_post_meta( $post_id, META_LANGUAGE );
			} else {
				update_post_meta( $post_id, META_LANGUAGE, $language );
			}
		}
	}

	/**
	 * Show a dismissible notice to administrators while no languages are enabled.
	 *
	 * @since 1.0.0
	 */
	public function show_no_languages_notice(): void {
		if ( $this->should_show_no_languages_notice() ) {
			printf(
				'<div class="notice notice-info is-dismissible" data-notice-id="%s"><p><strong>%s</strong> %s <a href="%s">%s</a></p></div>',
				esc_attr( NOTICE_NO_LANGUAGES ),
				esc_html__( 'Quick WP Lang:', 'quick-wp-lang' ),
				esc_html__( 'No languages are enabled yet.', 'quick-wp-lang' ),
				esc_url( get_settings_page_url() ),
				esc_html__( 'Configure your languages to get started', 'quick-wp-lang' )
			);
		}
	}

	/**
	 * Whether the current user should see the no-languages notice.
	 *
	 * @since 1.3.0
	 *
	 * @return bool True for an administrator who hasn't dismissed it, while no languages are enabled.
	 */
	private function should_show_no_languages_notice(): bool {
		$dismissed_notices = get_option( OPTION_DISMISSED_NOTICES, array() );
		$dismissed_notices = is_array( $dismissed_notices ) ? $dismissed_notices : array();

		return current_user_can( 'manage_options' )
			&& ! in_array( NOTICE_NO_LANGUAGES, $dismissed_notices, true )
			&& empty( get_enabled_languages_list() );
	}

	/**
	 * Record a dismissed admin notice, over AJAX.
	 *
	 * @since 1.0.0
	 */
	public function ajax_dismiss_notice(): void {
		$nonce     = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		$notice_id = '';

		if ( wp_verify_nonce( $nonce, NONCE_ACTION_DISMISS_NOTICE ) && current_user_can( 'manage_options' ) ) {
			// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified above.
			$notice_id = isset( $_POST['notice_id'] ) ? sanitize_key( wp_unslash( $_POST['notice_id'] ) ) : '';
			// phpcs:enable
		}

		if ( NOTICE_NO_LANGUAGES === $notice_id ) {
			$dismissed_notices   = get_option( OPTION_DISMISSED_NOTICES, array() );
			$dismissed_notices   = is_array( $dismissed_notices ) ? $dismissed_notices : array();
			$dismissed_notices[] = $notice_id;

			update_option( OPTION_DISMISSED_NOTICES, array_values( array_unique( $dismissed_notices ) ) );

			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * Insert the language column before the date column.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $columns Existing columns.
	 *
	 * @return mixed Columns with the language column added, or the input unchanged if it isn't an array.
	 */
	public function add_language_column( mixed $columns ): mixed {
		$result = $columns;

		if ( is_array( $columns ) ) {
			$result = array();

			foreach ( $columns as $key => $label ) {
				if ( 'date' === $key ) {
					$result[ COLUMN_LANGUAGE ] = _x( 'Language', 'list table column heading', 'quick-wp-lang' );
				}

				$result[ $key ] = $label;
			}

			// No date column to insert before.
			if ( ! isset( $result[ COLUMN_LANGUAGE ] ) ) {
				$result[ COLUMN_LANGUAGE ] = _x( 'Language', 'list table column heading', 'quick-wp-lang' );
			}
		}

		return $result;
	}

	/**
	 * Render a post's language code in the language column.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $column_name Column name.
	 * @param mixed $post_id     Post ID.
	 */
	public function render_language_column( mixed $column_name, mixed $post_id ): void {
		if ( COLUMN_LANGUAGE === $column_name && is_numeric( $post_id ) ) {
			$language = get_post_language( (int) $post_id );

			if ( DEFAULT_LANGUAGE === $language ) {
				echo '<span class="qwl-lang-default" aria-hidden="true">&mdash;</span>';
			} else {
				printf( '<code>%s</code>', esc_html( $language ) );
			}
		}
	}

	/**
	 * Make the language column sortable.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $columns Sortable columns.
	 *
	 * @return mixed Sortable columns with the language column added, or the input unchanged.
	 */
	public function make_language_column_sortable( mixed $columns ): mixed {
		if ( is_array( $columns ) ) {
			$columns[ COLUMN_LANGUAGE ] = COLUMN_LANGUAGE;
		}

		return $columns;
	}

	/**
	 * Sort the admin list table by language, including posts with no language set.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $query The WP_Query instance.
	 */
	public function sort_by_language_column( mixed $query ): void {
		$should_modify = $query instanceof \WP_Query
			&& $query->is_main_query()
			&& COLUMN_LANGUAGE === $query->get( 'orderby' );

		if ( $should_modify ) {
			$query->set(
				'meta_query',
				array(
					'relation'                => 'OR',
					'qwl_language_exists'     => array(
						'key'     => META_LANGUAGE,
						'compare' => 'EXISTS',
					),
					'qwl_language_not_exists' => array(
						'key'     => META_LANGUAGE,
						'compare' => 'NOT EXISTS',
					),
				)
			);
			$query->set( 'orderby', 'qwl_language_exists' );
		}
	}

	/**
	 * Enqueue the list-table stylesheet, and the notice-dismiss script wherever the notice shows.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_assets(): void {
		$screen = get_current_screen();

		if ( $screen instanceof \WP_Screen && ( 'post' === $screen->base || 'edit' === $screen->base ) ) {
			wp_enqueue_style( HANDLE_ADMIN_STYLE, QUICK_WP_LANG_URL . 'assets/admin/admin.css', array(), QUICK_WP_LANG_VERSION );
		}

		if ( $this->should_show_no_languages_notice() ) {
			wp_enqueue_script( HANDLE_ADMIN_SCRIPT, QUICK_WP_LANG_URL . 'assets/admin/admin.js', array(), QUICK_WP_LANG_VERSION, true );
			wp_localize_script(
				HANDLE_ADMIN_SCRIPT,
				'quickWpLangAdmin',
				array(
					'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
					'dismissAction' => AJAX_ACTION_DISMISS_NOTICE,
					'dismissNonce'  => wp_create_nonce( NONCE_ACTION_DISMISS_NOTICE ),
				)
			);
		}
	}
}
