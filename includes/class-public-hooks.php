<?php
/**
 * Public hooks handler.
 *
 * @package Quick_WP_Lang
 * @since 1.0.0
 */

namespace Quick_WP_Lang;

// Exit if accessed directly.
defined( 'ABSPATH' ) || die();

/**
 * Front-end output for singular content with a language override.
 *
 * @since 1.0.0
 */
class Public_Hooks {

	/**
	 * Hreflang alternates for the queried post, as BCP 47 tag => URL; null until first built.
	 *
	 * @since 1.3.0
	 * @var ?array<string, string>
	 */
	private ?array $hreflang_alternates = null;

	/**
	 * Replace the lang attribute on the html tag.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $output Language attributes string.
	 *
	 * @return mixed Attributes with the post's language, or the input unchanged.
	 */
	public function filter_language_attributes( mixed $output ): mixed {
		$result   = $output;
		$language = $this->get_current_post_language();

		if ( is_string( $output ) && DEFAULT_LANGUAGE !== $language ) {
			$replaced = preg_replace(
				'/lang="[^"]*"/',
				'lang="' . esc_attr( locale_to_bcp47( $language ) ) . '"',
				$output
			);

			if ( is_string( $replaced ) ) {
				$result = $replaced;
			}
		}

		return $result;
	}

	/**
	 * Output a hreflang link tag for each version of the queried post.
	 *
	 * @since 1.0.0
	 */
	public function output_hreflang_tags(): void {
		foreach ( $this->get_hreflang_alternates() as $hreflang => $url ) {
			printf(
				'<link rel="alternate" hreflang="%s" href="%s" />',
				esc_attr( $hreflang ),
				esc_url( $url )
			);
			echo PHP_EOL;
		}
	}

	/**
	 * Get the hreflang alternates for the queried post, as BCP 47 tag (or x-default) => URL.
	 *
	 * A linked group lists its original under the site locale and as x-default, then each
	 * published translation; the oldest translation wins a language. An unlinked post with a
	 * language lists only itself. See docs/how-it-works.md.
	 *
	 * @since 1.3.0
	 *
	 * @return array<string, string>
	 */
	private function get_hreflang_alternates(): array {
		if ( null === $this->hreflang_alternates ) {
			$this->hreflang_alternates = array();

			$post_id     = is_singular() ? get_queried_object_id() : 0;
			$language    = $post_id > 0 ? get_post_language( $post_id ) : DEFAULT_LANGUAGE;
			$original_id = DEFAULT_LANGUAGE === $language ? $post_id : get_translation_of( $post_id );

			$is_valid_original = $original_id > 0
				&& 'publish' === get_post_status( $original_id )
				&& DEFAULT_LANGUAGE === get_post_language( $original_id );

			$translation_ids = $is_valid_original ? get_translation_ids( $original_id, array( 'publish' ) ) : array();
			$site_hreflang   = locale_to_bcp47( get_locale() );
			$translations    = array();

			foreach ( $translation_ids as $translation_id ) {
				$hreflang = locale_to_bcp47( get_post_language( $translation_id ) );

				if ( '' !== $hreflang && $site_hreflang !== $hreflang && ! isset( $translations[ $hreflang ] ) ) {
					$translations[ $hreflang ] = (string) get_permalink( $translation_id );
				}
			}

			if ( ! empty( $translations ) ) {
				$original_url = (string) get_permalink( $original_id );

				$this->hreflang_alternates = array_merge(
					array( $site_hreflang => $original_url ),
					$translations,
					array( 'x-default' => $original_url )
				);
			} elseif ( DEFAULT_LANGUAGE !== $language ) {
				$this->hreflang_alternates = array( locale_to_bcp47( $language ) => (string) get_permalink( $post_id ) );
			} else {
				// A post in the site language with no translations needs no hreflang.
			}
		}

		return $this->hreflang_alternates;
	}

	/**
	 * Output the og:locale meta tag, unless a known SEO plugin outputs its own (Yoast's is filtered instead).
	 *
	 * @since 1.0.0
	 */
	public function output_og_locale(): void {
		$language = $this->is_seo_plugin_active() ? DEFAULT_LANGUAGE : $this->get_current_post_language();

		if ( DEFAULT_LANGUAGE !== $language ) {
			printf(
				'<meta property="og:locale" content="%s" />',
				esc_attr( $language )
			);
			echo PHP_EOL;
		}
	}

	/**
	 * Give Yoast SEO the post's locale, so its og:locale tag matches the content.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed $locale Locale Yoast is about to normalise for og:locale.
	 *
	 * @return mixed The post's locale, or the input unchanged.
	 */
	public function filter_yoast_locale( mixed $locale ): mixed {
		$language = $this->get_current_post_language();

		return DEFAULT_LANGUAGE !== $language ? $language : $locale;
	}

	/**
	 * Give Yoast SEO schema pieces about the page the post's language, leaving WebSite as is.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed $language BCP 47 language Yoast is about to set as inLanguage.
	 * @param mixed $data     The schema piece.
	 *
	 * @return mixed The post's language as BCP 47, or the input unchanged.
	 */
	public function filter_yoast_schema_language( mixed $language, mixed $data = null ): mixed {
		$result    = $language;
		$post_lang = $this->get_current_post_language();
		$types     = is_array( $data ) && isset( $data['@type'] ) ? (array) $data['@type'] : array();

		if ( DEFAULT_LANGUAGE !== $post_lang && ! in_array( 'WebSite', $types, true ) ) {
			$result = locale_to_bcp47( $post_lang );
		}

		return $result;
	}

	/**
	 * Send the Content-Language HTTP header, when enabled in settings.
	 *
	 * @since 1.0.0
	 */
	public function send_content_language_header(): void {
		$language       = is_content_language_header_enabled() ? $this->get_current_post_language() : DEFAULT_LANGUAGE;
		$bcp47_language = locale_to_bcp47( $language );

		if ( DEFAULT_LANGUAGE !== $language && preg_match( '/^[a-zA-Z]{2,3}(-[a-zA-Z0-9]{2,8})*$/', $bcp47_language ) ) {
			header( 'Content-Language: ' . $bcp47_language );
		}
	}

	/**
	 * Get the language override for the queried post, or '' when there is none.
	 *
	 * @since 1.0.0
	 *
	 * @return string WordPress locale, or ''.
	 */
	private function get_current_post_language(): string {
		$language = DEFAULT_LANGUAGE;

		if ( is_singular() ) {
			$post_id = get_queried_object_id();

			if ( $post_id > 0 ) {
				$language = get_post_language( $post_id );
			}
		}

		return $language;
	}

	/**
	 * Whether a known SEO plugin is active.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if Yoast SEO, Rank Math or All in One SEO is loaded.
	 */
	private function is_seo_plugin_active(): bool {
		return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' );
	}
}
