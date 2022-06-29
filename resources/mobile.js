if ( $( window ).width() < 851 ) {
	$( 'body:not(.skin-cosmos-search-vue) .cosmos-search-box-input' ).on( 'focus', function () {
		$( '#cosmos-banner-userOptions' ).hide();
		$( '.cosmos-mobile-menu-button' ).hide();
		$( '#cosmos-search-buttonContainer' ).show();
	} );

	$( 'body:not(.skin-cosmos-search-vue) .cosmos-search-box-input' ).on( 'focusout', function () {
		$( '#cosmos-banner-userOptions' ).show();
		$( '.cosmos-mobile-menu-button' ).show();
		$( '#cosmos-search-buttonContainer' ).hide();
	} );
}

if ( $( window ).width() > 850 ) {
	$( '.cosmos-mobile-navigation' ).remove();
}
