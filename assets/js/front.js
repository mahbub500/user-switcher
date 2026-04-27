/* global USER_SWITCHER, jQuery */
jQuery( function ( $ ) {
	'use strict';

	$( document ).on( 'click', '#us_floatingBtn', function ( e ) {
		e.preventDefault();
		var url = $( this ).attr( 'href' );

		$.post( USER_SWITCHER.ajaxurl, {
			action: 'remove_cookie',
			_wpnonce: USER_SWITCHER._wpnonce
		}, function ( res ) {
			if ( res.success ) {
				window.location.href = url;
			}
		} );
	} );
} );
