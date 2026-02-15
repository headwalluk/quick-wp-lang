/**
 * Settings page language search/filter.
 *
 * @package Quick_WP_Lang
 * @since 1.1.0
 */

(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {
		const searchBox = document.getElementById('qwl-language-search');
		if (!searchBox) {
			return;
		}

		const languageCheckboxes = document.querySelectorAll('.qwl-language-item');

		searchBox.addEventListener('keyup', function() {
			const searchTerm = this.value.toLowerCase().trim();

			languageCheckboxes.forEach(function(item) {
				const languageName = item.getAttribute('data-name').toLowerCase();
				const localeCode = item.getAttribute('data-locale').toLowerCase();
				const matches = languageName.includes(searchTerm) || localeCode.includes(searchTerm);

				if (matches || searchTerm === '') {
					item.style.display = '';
				} else {
					item.style.display = 'none';
				}
			});
		});

		// Clear button functionality
		const clearButton = document.getElementById('qwl-language-search-clear');
		if (clearButton) {
			clearButton.addEventListener('click', function() {
				searchBox.value = '';
				searchBox.dispatchEvent(new Event('keyup'));
				searchBox.focus();
			});
		}
	});
})();
