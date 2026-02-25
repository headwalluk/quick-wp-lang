<?php
/**
 * Public hooks handler.
 *
 * @package Quick_WP_Lang
 * @since 1.0.0
 */

namespace Quick_WP_Lang;

defined( 'ABSPATH' ) || die();

/**
 * Handles public/front-end functionality.
 *
 * Filters language attributes and outputs hreflang tags for singular content with language overrides.
 *
 * @since 1.0.0
 */
class Public_Hooks {
	/**
	 * Filter the language attributes for the HTML tag.
	 *
	 * @since 1.0.0
	 *
	 * @param string $output Language attributes string.
	 *
	 * @return string Modified language attributes.
	 */
	public function filter_language_attributes( string $output ): string {
		$result   = $output;
		$language = $this->get_current_post_language();

		if ( ! empty( $language ) ) {
			// Convert WordPress locale to BCP 47 format.
			$bcp47_language = $this->locale_to_bcp47( $language );

			// Replace the lang attribute.
			$replaced = preg_replace(
				'/lang="[^"]*"/',
				'lang="' . esc_attr( $bcp47_language ) . '"',
				$output
			);

			if ( is_string( $replaced ) ) {
				$result = $replaced;
			}
		}

		return $result;
	}

	/**
	 * Output hreflang link tag in the head.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function output_hreflang_tags(): void {
		$language = $this->get_current_post_language();

		if ( ! empty( $language ) ) {
			$bcp47_language = $this->locale_to_bcp47( $language );
			$current_url    = $this->get_current_url();

			printf(
				'<link rel="alternate" hreflang="%s" href="%s" />' . "\n",
				esc_attr( $bcp47_language ),
				esc_url( $current_url )
			);
		}
	}

	/**
	 * Output og:locale meta tag.
	 *
	 * Only outputs if no known SEO plugin is active.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function output_og_locale(): void {
		$should_output = false;
		$language      = '';

		// Skip if a known SEO plugin is active.
		if ( ! $this->is_seo_plugin_active() ) {
			$language = $this->get_current_post_language();

			if ( ! empty( $language ) ) {
				$should_output = true;
			}
		}

		if ( $should_output ) {
			printf(
				'<meta property="og:locale" content="%s" />' . "\n",
				esc_attr( $language )
			);
		}
	}

	/**
	 * Send Content-Language HTTP header.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function send_content_language_header(): void {
		$language = $this->get_current_post_language();

		if ( ! empty( $language ) ) {
			$bcp47_language = $this->locale_to_bcp47( $language );

			// Validate BCP 47 format before sending header.
			if ( preg_match( '/^[a-zA-Z]{2,3}(-[a-zA-Z0-9]{2,8})*$/', $bcp47_language ) ) {
				header( 'Content-Language: ' . $bcp47_language );
			}
		}
	}

	/**
	 * Get the language override for the current post.
	 *
	 * @since 1.0.0
	 *
	 * @return string Language code or empty string if not set/not singular.
	 */
	private function get_current_post_language(): string {
		$result = '';

		if ( ! is_singular() ) {
			return $result;
		}

		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return $result;
		}

		$language = get_post_meta( $post_id, META_KEY_LANGUAGE, true );

		if ( is_string( $language ) && ! empty( $language ) ) {
			$result = $language;
		}

		return $result;
	}

	/**
	 * Convert WordPress locale to BCP 47 format.
	 *
	 * @since 1.0.0
	 *
	 * @param string $locale WordPress locale (e.g., 'en_GB', 'de_DE').
	 *
	 * @return string BCP 47 format (e.g., 'en-GB', 'de-DE').
	 */
	private function locale_to_bcp47( string $locale ): string {
		return str_replace( '_', '-', $locale );
	}

	/**
	 * Get the current URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string Current URL.
	 */
	private function get_current_url(): string {
		global $wp;

		return home_url( $wp->request );
	}

	/**
	 * Check if a known SEO plugin is active.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if SEO plugin detected.
	 */
	private function is_seo_plugin_active(): bool {
		$result = false;

		// Check for Yoast SEO.
		if ( defined( 'WPSEO_VERSION' ) ) {
			$result = true;
		}

		// Check for Rank Math.
		if ( ! $result && defined( 'RANK_MATH_VERSION' ) ) {
			$result = true;
		}

		// Check for All in One SEO.
		if ( ! $result && defined( 'AIOSEO_VERSION' ) ) {
			$result = true;
		}

		return $result;
	}
}
