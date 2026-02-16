/**
 * Settings page language search/filter.
 *
 * @package Quick_WP_Lang
 * @since 1.1.0
 */

( function() {
	'use strict';

	/**
	 * Initialize search functionality when DOM is ready.
	 */
	document.addEventListener( 'DOMContentLoaded', function() {
		const searchInput = document.getElementById( 'qwl-language-search' );
		const clearButton = document.getElementById( 'qwl-language-search-clear' );
		const languageItems = document.querySelectorAll( '.qwl-language-item' );

		if ( ! searchInput || ! clearButton || languageItems.length === 0 ) {
			return;
		}

		/**
		 * Filter languages based on search query.
		 *
		 * @param {string} query Search query string.
		 */
		function filterLanguages( query ) {
			const normalizedQuery = query.toLowerCase().trim();

			languageItems.forEach( function( item ) {
				const name = item.getAttribute( 'data-name' ) || '';
				const locale = item.getAttribute( 'data-locale' ) || '';

				const nameMatch = name.toLowerCase().includes( normalizedQuery );
				const localeMatch = locale.toLowerCase().includes( normalizedQuery );

				if ( normalizedQuery === '' || nameMatch || localeMatch ) {
					item.style.display = '';
				} else {
					item.style.display = 'none';
				}
			} );
		}

		/**
		 * Clear search input and reset filter.
		 */
		function clearSearch() {
			searchInput.value = '';
			filterLanguages( '' );
			searchInput.focus();
		}

		// Attach event listeners.
		searchInput.addEventListener( 'keyup', function() {
			filterLanguages( this.value );
		} );

		clearButton.addEventListener( 'click', clearSearch );
	} );
}() );
