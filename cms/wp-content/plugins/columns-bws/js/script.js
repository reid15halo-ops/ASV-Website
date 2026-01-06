( function( $ ) {
	$( document ).ready( function() {
		if ( 0 < $( '.clmns-column-bws-wrapper.clmns-background-dark' ).length ) {
			if ( 0 < $( '.clmns-column-bws-wrapper.clmns-background-dark' ).parents( '.widget' ).length ) {
				$( '.clmns-column-bws-wrapper.clmns-background-dark' ).parents( '.widget' ).addClass( 'clmns-background-dark' );
			}
		}
	} );
} )( jQuery );
