<?php
/**
 * Admin hooks handler.
 *
 * @package Quick_WP_Lang
 * @since 1.0.0
 */

namespace Quick_WP_Lang;

defined( 'ABSPATH' ) || die();

/**
 * Handles admin-side functionality.
 *
 * Registers and renders the meta box for language selection.
 *
 * @since 1.0.0
 */
class Admin_Hooks {
	/**
	 * Register the language meta box.
	 *
	 * Adds the meta box to all public post types.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_meta_box(): void {
		$post_types = $this->get_supported_post_types();

		add_meta_box(
			'quick_wp_lang_language_meta_box',
			__( 'Content Language', 'quick-wp-lang' ),
			array( $this, 'render_meta_box' ),
			$post_types,
			'side',
			'default'
		);
	}

	/**
	 * Render the language selection meta box.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $post Current post object.
	 *
	 * @return void
	 */
	public function render_meta_box( \WP_Post $post ): void {
		// Verify nonce.
		wp_nonce_field( NONCE_ACTION_META_BOX, NONCE_NAME_META_BOX );

		// Get current value.
		$current_language = get_post_meta( $post->ID, META_KEY_LANGUAGE, true );
		$current_language = is_string( $current_language ) ? $current_language : DEF_LANGUAGE;

		// Get enabled languages.
		$languages = get_enabled_languages_list();

		// Render help text.
		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Select the language for this content:', 'quick-wp-lang' )
		);

		// Render radio buttons.
		printf( '<fieldset>' );

		// Site default option.
		printf(
			'<label><input type="radio" name="quick_wp_lang_language" value="%s"%s /> %s</label><br />',
			esc_attr( DEF_LANGUAGE ),
			checked( $current_language, DEF_LANGUAGE, false ),
			esc_html__( 'Site Default', 'quick-wp-lang' )
		);

		// Enabled language options.
		foreach ( $languages as $locale => $name ) {
			printf(
				'<label><input type="radio" name="quick_wp_lang_language" value="%s"%s /> %s</label><br />',
				esc_attr( $locale ),
				checked( $current_language, $locale, false ),
				esc_html( $name )
			);
		}

		printf( '</fieldset>' );

		// Link to settings page.
		$settings_url = admin_url( 'options-general.php?page=' . SETTINGS_PAGE_SLUG );

		printf(
			'<p class="description" style="margin-top: 12px;"><a href="%s" target="_blank">%s</a></p>',
			esc_url( $settings_url ),
			esc_html__( 'Configure available languages →', 'quick-wp-lang' )
		);

		// Additional help text.
		printf(
			'<p class="description" style="margin-top: 8px;">%s</p>',
			esc_html__( 'If this content is written in a language other than the site default, select it here. This will update the page\'s language attributes and hreflang tags for better SEO.', 'quick-wp-lang' )
		);
	}

	/**
	 * Save the language meta box data.
	 *
	 * @since 1.0.0
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 *
	 * @return void
	 */
	public function save_meta_box( int $post_id, \WP_Post $post ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Required by WordPress save_post hook.
		$should_save = true;

		// Verify nonce.
		$nonce_value = isset( $_POST[ NONCE_NAME_META_BOX ] ) ? sanitize_text_field( wp_unslash( $_POST[ NONCE_NAME_META_BOX ] ) ) : '';

		if ( ! wp_verify_nonce( $nonce_value, NONCE_ACTION_META_BOX ) ) {
			$should_save = false;
		}

		// Check autosave.
		if ( $should_save && defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			$should_save = false;
		}

		// Check permissions.
		if ( $should_save && ! current_user_can( 'edit_post', $post_id ) ) {
			$should_save = false;
		}

		if ( $should_save ) {
			// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified above.
			$language = isset( $_POST['quick_wp_lang_language'] ) ? sanitize_text_field( wp_unslash( $_POST['quick_wp_lang_language'] ) ) : DEF_LANGUAGE;
			// phpcs:enable

			// Validate language is in our list or is the default.
			if ( DEF_LANGUAGE !== $language ) {
				$languages = get_enabled_languages_list();
				if ( ! isset( $languages[ $language ] ) ) {
					$language = DEF_LANGUAGE;
				}
			}

			// Save or delete meta.
			if ( DEF_LANGUAGE === $language ) {
				delete_post_meta( $post_id, META_KEY_LANGUAGE );
			} else {
				update_post_meta( $post_id, META_KEY_LANGUAGE, $language );
			}
		}
	}

	/**
	 * Get list of supported post types for the meta box.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string> Array of post type names.
	 */
	private function get_supported_post_types(): array {
		$post_types = get_post_types( array( 'public' => true ), 'names' );

		/**
		 * Filter the post types that support the language meta box.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string> $post_types Array of post type names.
		 */
		$post_types = apply_filters( 'quick_wp_lang_supported_post_types', $post_types );

		return is_array( $post_types ) ? $post_types : array();
	}

	/**
	 * Display admin notice when no languages are enabled.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function show_no_languages_notice(): void {
		$should_show = false;

		// Check if user can manage options.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if notice has been dismissed.
		$dismissed_notices = get_option( OPT_DISMISSED_NOTICES, array() );
		$dismissed_notices = is_array( $dismissed_notices ) ? $dismissed_notices : array();

		if ( in_array( 'no_languages', $dismissed_notices, true ) ) {
			return;
		}

		// Check if any languages are enabled.
		$enabled_languages = get_option( OPT_ENABLED_LANGUAGES, array() );
		$enabled_languages = is_array( $enabled_languages ) ? $enabled_languages : array();

		if ( empty( $enabled_languages ) ) {
			$should_show = true;
		}

		if ( $should_show ) {
			$settings_url = admin_url( 'options-general.php?page=' . SETTINGS_PAGE_SLUG );

			printf(
				'<div class="notice notice-info is-dismissible" data-notice-id="no_languages"><p><strong>%s</strong> %s <a href="%s">%s</a></p></div>',
				esc_html__( 'Quick WP Lang:', 'quick-wp-lang' ),
				esc_html__( 'No languages are enabled yet.', 'quick-wp-lang' ),
				esc_url( $settings_url ),
				esc_html__( 'Configure your languages to get started →', 'quick-wp-lang' )
			);
		}
	}

	/**
	 * Handle AJAX request to dismiss admin notice.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function ajax_dismiss_notice(): void {
		$should_dismiss = true;

		// Check nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'quick_wp_lang_dismiss_notice' ) ) {
			$should_dismiss = false;
		}

		// Check permissions.
		if ( $should_dismiss && ! current_user_can( 'manage_options' ) ) {
			$should_dismiss = false;
		}

		if ( $should_dismiss ) {
			// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified above.
			$notice_id = isset( $_POST['notice_id'] ) ? sanitize_text_field( wp_unslash( $_POST['notice_id'] ) ) : '';
			// phpcs:enable

			if ( ! empty( $notice_id ) ) {
				$dismissed_notices   = get_option( OPT_DISMISSED_NOTICES, array() );
				$dismissed_notices   = is_array( $dismissed_notices ) ? $dismissed_notices : array();
				$dismissed_notices[] = $notice_id;

				update_option( OPT_DISMISSED_NOTICES, array_unique( $dismissed_notices ) );

				wp_send_json_success();
			}
		}

		wp_send_json_error();
	}

	/**
	 * Add language column to posts list table.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, string> $columns Existing columns.
	 *
	 * @return array<string, string> Modified columns.
	 */
	public function add_language_column( array $columns ): array {
		$result = array();

		// Insert language column before date column.
		foreach ( $columns as $key => $label ) {
			if ( 'date' === $key ) {
				$result['quick_wp_lang_language'] = __( 'Language', 'quick-wp-lang' );
			}
			$result[ $key ] = $label;
		}

		return $result;
	}

	/**
	 * Render language column content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $column_name Column name.
	 * @param int    $post_id     Post ID.
	 *
	 * @return void
	 */
	public function render_language_column( string $column_name, int $post_id ): void {
		if ( 'quick_wp_lang_language' !== $column_name ) {
			return;
		}

		$language = get_post_meta( $post_id, META_KEY_LANGUAGE, true );
		$language = is_string( $language ) ? $language : '';

		if ( empty( $language ) ) {
			printf(
				'<span style="color: #999;">%s</span>',
				esc_html__( '—', 'quick-wp-lang' )
			);
		} else {
			printf(
				'<code>%s</code>',
				esc_html( $language )
			);
		}
	}

	/**
	 * Make language column sortable.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, string> $columns Sortable columns.
	 *
	 * @return array<string, string> Modified sortable columns.
	 */
	public function make_language_column_sortable( array $columns ): array {
		$columns['quick_wp_lang_language'] = 'quick_wp_lang_language';
		return $columns;
	}

	/**
	 * Modify query to sort by language column.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Query $query The WP_Query instance.
	 *
	 * @return void
	 */
	public function sort_by_language_column( \WP_Query $query ): void {
		$should_modify = false;

		if ( ! is_admin() ) {
			return;
		}

		if ( ! $query->is_main_query() ) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		if ( 'quick_wp_lang_language' === $orderby ) {
			$should_modify = true;
		}

		if ( $should_modify ) {
			$query->set( 'meta_key', META_KEY_LANGUAGE );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	/**
	 * Enqueue admin assets for notice dismissal.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_admin_assets(): void {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		// Only enqueue on relevant admin pages.
		$allowed_screens = array( 'dashboard', 'edit-post', 'edit-page', 'post', 'page' );

		if ( in_array( $screen->id, $allowed_screens, true ) || strpos( $screen->id, 'edit-' ) === 0 ) {
			wp_add_inline_script(
				'jquery',
				"
				jQuery(function($) {
					$(document).on('click', '.notice[data-notice-id] .notice-dismiss', function() {
						var noticeId = $(this).closest('.notice').data('notice-id');
						$.post(ajaxurl, {
							action: 'quick_wp_lang_dismiss_notice',
							notice_id: noticeId,
							nonce: '" . wp_create_nonce( 'quick_wp_lang_dismiss_notice' ) . "'
						});
					});
				});
				"
			);
		}
	}
}
