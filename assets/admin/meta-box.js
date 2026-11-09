/**
 * Language meta box: show the "Translation of" field only while a language other than the
 * site default is chosen.
 *
 * @package Quick_WP_Lang
 * @since 1.3.0
 */

( function () {
	'use strict';

	document.addEventListener( 'change', function ( event ) {
		const radio = event.target;
		const isLanguageRadio = radio instanceof HTMLInputElement
			&& 'radio' === radio.type
			&& 'quick_wp_lang_language' === radio.name;
		const metaBox = isLanguageRadio ? radio.closest( '#quick_wp_lang_language_meta_box' ) : null;
		const translationOf = metaBox ? metaBox.querySelector( '.qwl-translation-of' ) : null;

		if ( translationOf ) {
			translationOf.hidden = '' === radio.value;
		}
	} );
}() );
