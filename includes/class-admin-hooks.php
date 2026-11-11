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

			$this->render_translation_of_field( $post, $current_language );
			$this->render_translations_list( $post, $current_language );

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
	 * Render the "Translation of" select, hidden by meta-box.js while the site default is chosen.
	 *
	 * @since 1.3.0
	 *
	 * @param \WP_Post $post             Post being edited.
	 * @param string   $current_language Post's current language override.
	 */
	private function render_translation_of_field( \WP_Post $post, string $current_language ): void {
		$current_original_id = get_translation_of( $post->ID );
		$candidate_ids       = $this->get_translation_candidate_ids( $post );

		if ( $current_original_id > 0 && ! in_array( $current_original_id, $candidate_ids, true ) && get_post( $current_original_id ) instanceof \WP_Post ) {
			array_unshift( $candidate_ids, $current_original_id );
		}

		printf(
			'<div class="qwl-translation-of"%s><p><label for="%s"><strong>%s</strong></label></p>',
			DEFAULT_LANGUAGE === $current_language ? ' hidden' : '',
			esc_attr( FIELD_TRANSLATION_OF ),
			esc_html_x( 'Translation of', 'meta box field label; the original this content translates', 'quick-wp-lang' )
		);

		printf(
			'<select name="%1$s" id="%1$s" class="widefat"><option value="0">%2$s</option>',
			esc_attr( FIELD_TRANSLATION_OF ),
			esc_html_x( '— Not linked —', 'no original selected', 'quick-wp-lang' )
		);

		foreach ( $candidate_ids as $candidate_id ) {
			printf(
				'<option value="%d"%s>%s</option>',
				absint( $candidate_id ),
				selected( $current_original_id, $candidate_id, false ),
				esc_html( $this->get_post_label( $candidate_id ) )
			);
		}

		echo '</select>';

		printf(
			'<p class="description">%s</p></div>',
			esc_html(
				sprintf(
					/* translators: %s: Name of the site's language, e.g. "English (UK)". */
					__( 'The %s version of this content. Both pages then list each other in their hreflang tags, so search engines show visitors the right one.', 'quick-wp-lang' ),
					get_language_name( get_locale() )
				)
			)
		);
	}

	/**
	 * List the translations linked to this post, when it is an original in the site language.
	 *
	 * @since 1.3.0
	 *
	 * @param \WP_Post $post             Post being edited.
	 * @param string   $current_language Post's current language override.
	 */
	private function render_translations_list( \WP_Post $post, string $current_language ): void {
		$translation_ids = DEFAULT_LANGUAGE === $current_language
			? get_translation_ids( $post->ID, array( 'publish', 'future', 'draft', 'pending', 'private' ) )
			: array();

		if ( ! empty( $translation_ids ) ) {
			printf(
				'<div class="qwl-translations"><p><strong>%s</strong></p><ul>',
				esc_html_x( 'Translations', 'meta box heading; list of linked translations', 'quick-wp-lang' )
			);

			$published_languages = array();

			foreach ( $translation_ids as $translation_id ) {
				$edit_link    = get_edit_post_link( $translation_id );
				$language     = get_post_language( $translation_id );
				$is_published = 'publish' === get_post_status( $translation_id );

				// Mirrors Public_Hooks::get_hreflang_alternates(): the oldest published one wins.
				$is_repeated = $is_published && in_array( $language, $published_languages, true );

				if ( $is_published ) {
					$published_languages[] = $language;
				}

				printf(
					'<li>%s: <a href="%s">%s</a>%s</li>',
					esc_html( get_language_name( $language ) ),
					esc_url( is_string( $edit_link ) ? $edit_link : '' ),
					esc_html( $this->get_post_label( $translation_id ) ),
					$is_repeated ? ' <em>' . esc_html__( '(not used: an older translation already covers this language)', 'quick-wp-lang' ) . '</em>' : ''
				);
			}

			echo '</ul></div>';
		}
	}

	/**
	 * Get the posts that can be chosen as this post's original: same type, no language override.
	 *
	 * @since 1.3.0
	 *
	 * @param \WP_Post $post Post being edited.
	 *
	 * @return array<int> Post IDs, ordered by title.
	 */
	private function get_translation_candidate_ids( \WP_Post $post ): array {
		$post_ids = get_posts(
			array(
				'post_type'        => $post->post_type,
				'post_status'      => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				'posts_per_page'   => TRANSLATION_CANDIDATES_LIMIT,
				'post__not_in'     => array( $post->ID ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Excludes one post from an admin-only list.
				'orderby'          => 'title',
				'order'            => 'ASC',
				'fields'           => 'ids',
				'no_found_rows'    => true,
				'suppress_filters' => true,
				'meta_query'       => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Admin-only list, built once per edit screen.
					array(
						'key'     => META_LANGUAGE,
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);

		return array_map( 'intval', $post_ids );
	}

	/**
	 * Get a post's title and ID for a list, e.g. "About us (#42)".
	 *
	 * @since 1.3.0
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return string Label.
	 */
	private function get_post_label( int $post_id ): string {
		$title = get_the_title( $post_id );

		return sprintf(
			/* translators: 1: Post title, 2: Post ID. */
			__( '%1$s (#%2$d)', 'quick-wp-lang' ),
			'' === $title ? __( '(no title)', 'quick-wp-lang' ) : $title,
			$post_id
		);
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
			&& false === wp_is_post_autosave( $post_id )
			&& false === wp_is_post_revision( $post_id )
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

			// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified above.
			$original_id = isset( $_POST[ FIELD_TRANSLATION_OF ] ) ? absint( wp_unslash( $_POST[ FIELD_TRANSLATION_OF ] ) ) : 0;
			// phpcs:enable

			if ( DEFAULT_LANGUAGE !== $language && $this->is_valid_original( $original_id, $post_id ) ) {
				update_post_meta( $post_id, META_TRANSLATION_OF, $original_id );
			} else {
				delete_post_meta( $post_id, META_TRANSLATION_OF );
			}
		}
	}

	/**
	 * Whether a post can be the original of another: a different post, same type, in the site language.
	 *
	 * @since 1.3.0
	 *
	 * @param int $original_id    Proposed original's post ID.
	 * @param int $translation_id Translation's post ID.
	 *
	 * @return bool
	 */
	private function is_valid_original( int $original_id, int $translation_id ): bool {
		$original    = $original_id > 0 && $original_id !== $translation_id ? get_post( $original_id ) : null;
		$translation = get_post( $translation_id );

		return $original instanceof \WP_Post
			&& $translation instanceof \WP_Post
			&& $original->post_type === $translation->post_type
			&& 'trash' !== $original->post_status
			&& DEFAULT_LANGUAGE === get_post_language( $original_id );
	}

	/**
	 * Show a dismissible notice to administrators while no languages are enabled.
	 *
	 * @since 1.0.0
	 */
	public function show_no_languages_notice(): void {
		if ( $this->should_show_no_languages_notice() ) {
			printf(
				'<div class="notice notice-info is-dismissible" data-notice-id="%s"><p><strong>Quick WP Lang:</strong> %s <a href="%s">%s</a></p></div>',
				esc_attr( NOTICE_NO_LANGUAGES ),
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

		if ( $screen instanceof \WP_Screen && 'post' === $screen->base && in_array( $screen->post_type, get_supported_post_types(), true ) ) {
			wp_enqueue_script( HANDLE_META_BOX_SCRIPT, QUICK_WP_LANG_URL . 'assets/admin/meta-box.js', array(), QUICK_WP_LANG_VERSION, true );
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
