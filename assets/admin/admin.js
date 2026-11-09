/**
 * Persist dismissal of Quick WP Lang admin notices.
 *
 * @package Quick_WP_Lang
 * @since 1.3.0
 */

( function () {
	'use strict';

	const settings = window.quickWpLangAdmin;

	if ( ! settings ) {
		return;
	}

	document.addEventListener( 'click', function ( event ) {
		const notice = event.target.classList.contains( 'notice-dismiss' )
			? event.target.closest( '.notice[data-notice-id]' )
			: null;

		if ( notice ) {
			const formData = new FormData();
			formData.append( 'action', settings.dismissAction );
			formData.append( 'notice_id', notice.getAttribute( 'data-notice-id' ) );
			formData.append( 'nonce', settings.dismissNonce );

			window.fetch( settings.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData,
			} );
		}
	} );
}() );
