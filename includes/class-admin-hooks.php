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
			'qwl_language_meta_box',
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
			'<label><input type="radio" name="qwl_language" value="%s"%s /> %s</label><br />',
			esc_attr( DEF_LANGUAGE ),
			checked( $current_language, DEF_LANGUAGE, false ),
			esc_html__( 'Site Default', 'quick-wp-lang' )
		);

		// Enabled language options.
		foreach ( $languages as $locale => $name ) {
			printf(
				'<label><input type="radio" name="qwl_language" value="%s"%s /> %s</label><br />',
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
			$language = isset( $_POST['qwl_language'] ) ? sanitize_text_field( wp_unslash( $_POST['qwl_language'] ) ) : DEF_LANGUAGE;
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
		$post_types = apply_filters( 'qwl_supported_post_types', $post_types );

		return is_array( $post_types ) ? $post_types : array();
	}
}
