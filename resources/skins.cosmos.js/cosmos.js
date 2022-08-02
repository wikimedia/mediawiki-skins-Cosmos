/* global jQuery, mediaWiki */

( function ( $, mw ) {
	var modal = document.getElementById( 'createPageModal' ),
		btn = document.getElementById( 'createpage' ),
		span = document.getElementsByClassName( 'close' )[ 0 ],
		$top = 0;

	btn.onclick = function () {
		modal.style.display = 'flex';
	};

	span.onclick = function () {
		modal.style.display = 'none';
	};

	window.onclick = function ( event ) {
		if ( event.target === modal ) {
			modal.style.display = 'none';
		}
	};

	$( '.create-page-dialog__wrapper #create-page-dialog__title' ).on( 'keyup', function () {
		var empty = false;

		$( '.create-page-dialog__wrapper #create-page-dialog__title' ).each( function () {
			if ( $( this ).val() === '' ) {
				empty = true;
			}
		} );

		if ( empty ) {
			$( '.create-page-dialog__button' ).prop( 'disabled', true );
		} else {
			$( '.create-page-dialog__button' ).prop( 'disabled', false );
		}
	} );

	mw.hook( 've.activationComplete' ).add( function () {
		$( '.ve-activated .firstHeading' ).html( $( 'title' ).html().replace( ' - ' + mw.config.get( 'wgSiteName' ), '' ) );
	} );

	$( '.CosmosRail .cosmos-rail-inner' ).addClass( 'loaded' );
	$( '.rail-sticky-module' ).each( function () {
		var $module = $( this ).nextAll( '.rail-sticky-module' );

		$top += $( this ).outerHeight() + 20;

		$module.attr( 'style', 'top: ' + ( $top + 60 ) + 'px;' );
	} );

	/**
	 * Updates the height of the footer, in order to make sure it always fills
	 * the space between the bottom of the page, and the bottom of the viewport,
	 * regardless of how small the page is
	 */
	function updateFooterHeight() {
		var $footer = $( '#cosmos-footer' );
		// Reset the footer height to its default value
		$footer.height( 'auto' );
		if ( $( window ).height() > $footer.offset().top + $footer.outerHeight( false ) ) {
			// If the footer is not large enough to fill the bottom of the page,
			// resize its outer height accordingly
			$footer.outerHeight( $( window ).height() - $footer.offset().top, false );
		}
	}

	/**
	 * Closes the site notice
	 */
	function closeSiteNotice() {
		var $siteNotice = $( '#cosmos-content-siteNotice' );
		$siteNotice.remove();
		mw.cookie.set( 'CosmosSiteNoticeState', 'closed', { expires: 604800 } );
	}

	$( function () {
		$( '#cosmos-siteNotice-closeButton' ).on( 'click', closeSiteNotice );
		updateFooterHeight();
	} );

	// On window resize, update the footer height if necessary
	$( window ).on( 'resize', updateFooterHeight );

	$( function () {
		if (
			mw.config.get( 'wgVisualEditorConfig' ) &&
			mw.config.get( 'wgVisualEditorConfig' ).enableWikitext &&
			mw.config.get( 'wgPageName' ) === 'MediaWiki:Cosmos-navigation'
		) {
			var visualEditorConfig = mw.config.get( 'wgVisualEditorConfig' );
			visualEditorConfig.enableWikitext = false;

			mw.config.set( 'wgVisualEditorConfig', visualEditorConfig );
		}
	} );
}( jQuery, mediaWiki ) );
