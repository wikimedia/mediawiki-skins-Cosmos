if ( $( window ).width() < 851 ) {
	$( '.cosmos-search-input' ).on( 'focus', function () {
		$( '#cosmos-banner-userOptions' ).hide();
		$( '#cosmos-search-buttonContainer' ).css( 'display', 'flex' );
	} );

	$( '.cosmos-search-input' ).on( 'focusout', function () {
		$( '#cosmos-banner-userOptions' ).css( 'display', 'flex' );
		$( '#cosmos-search-buttonContainer' ).hide();
	} );
}

if ( $( window ).width() > 850 ) {
	$( '.cosmos-mobile-navigation' ).remove();
}
