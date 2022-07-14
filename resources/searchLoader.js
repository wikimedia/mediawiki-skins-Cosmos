/**
 * Disabling this rule as it's only necessary for
 * combining multiple class names and documenting the output.
 * That doesn't happen in this file but the linter still throws an error.
 * https://github.com/wikimedia/eslint-plugin-mediawiki/blob/master/docs/rules/class-doc.md
 */
/* eslint-disable mediawiki/class-doc */

/** @interface CosmosResourceLoaderVirtualConfig */
/** @interface MediaWikiPageReadyModule */

var /** @type {CosmosResourceLoaderVirtualConfig} */
	config = require( /** @type {string} */ ( './config.json' ) ),
	SEARCH_LOADING_CLASS = 'search-form__loader';

/**
 * Loads the search module via `mw.loader.using` on the element's
 * focus event. Or, if the element is already focused, loads the
 * search module immediately.
 * After the search module is loaded, executes a function to remove
 * the loading indicator.
 *
 * @param {HTMLElement} element search input.
 * @param {string} moduleName resourceLoader module to load.
 * @param {function(): void} afterLoadFn function to execute after search module loads.
 */
function loadSearchModule( element, moduleName, afterLoadFn ) {
	function requestSearchModule() {
		mw.loader.using( moduleName, afterLoadFn );

		element.removeEventListener( 'focus', requestSearchModule );

		if ( $( window ).width() < 851 ) {
			$( '#cosmos-banner-userOptions' ).hide();
			$( '.cosmos-mobile-menu-button' ).hide();
		}

		function onFocusOut() {
			$( '#cosmos-banner-userOptions' ).show();
			$( '.cosmos-mobile-menu-button' ).show();
		}

		function onFocus() {
			$( '#cosmos-banner-userOptions' ).hide();
			$( '.cosmos-mobile-menu-button' ).hide();
		}

		if ( $( window ).width() < 851 ) {
			var inputCheck = setInterval( function () {
				if ( document.getElementsByClassName( 'wvui-input__input' )[ 0 ] !== undefined ) {
					clearInterval( inputCheck );

					document.getElementsByClassName( 'wvui-input__input' )[ 0 ].addEventListener( 'focus', onFocus );
					document.getElementsByClassName( 'wvui-input__input' )[ 0 ].addEventListener( 'focusout', onFocusOut );
				}
			}, 100 );
		}
	}

	if ( document.activeElement === element ) {
		requestSearchModule();
	} else {
		element.addEventListener( 'focus', requestSearchModule );
	}
}

/**
 * Event callback that shows or hides the loading indicator based on the event type.
 * The loading indicator states are:
 * 1. Show on input event (while user is typing)
 * 2. Hide on focusout event (when user removes focus from the input )
 * 3. Show when input is focused, if it contains a query. (in case user re-focuses on input)
 *
 * @param {Event} event
 */
function renderSearchLoadingIndicator( event ) {

	var form = /** @type {HTMLElement} */ ( event.currentTarget ),
		input = /** @type {HTMLInputElement} */ ( event.target );

	if (
		!( event.currentTarget instanceof HTMLElement ) ||
		!( event.target instanceof HTMLInputElement )
	) {
		return;
	}

	if ( !form.dataset.loadingMsg ) {
		form.dataset.loadingMsg = mw.msg( 'cosmos-search-loader' );
	}

	if ( event.type === 'input' ) {
		form.classList.add( SEARCH_LOADING_CLASS );

	} else if ( event.type === 'focusout' ) {
		form.classList.remove( SEARCH_LOADING_CLASS );

	} else if ( event.type === 'focusin' && input.value.trim() ) {
		form.classList.add( SEARCH_LOADING_CLASS );
	}
}

/**
 * Attaches or detaches the event listeners responsible for activating
 * the loading indicator.
 *
 * @param {Element} element
 * @param {boolean} attach
 * @param {function(Event): void} eventCallback
 */
function setLoadingIndicatorListeners( element, attach, eventCallback ) {

	/** @type { "addEventListener" | "removeEventListener" } */
	var addOrRemoveListener = ( attach ? 'addEventListener' : 'removeEventListener' );

	[ 'input', 'focusin', 'focusout' ].forEach( function ( eventType ) {
		element[ addOrRemoveListener ]( eventType, eventCallback );
	} );

	if ( !attach ) {
		element.classList.remove( SEARCH_LOADING_CLASS );
	}
}

/**
 * Initialize the loading of the search module as well as the loading indicator.
 * Only initialize the loading indicator when not using the core search module.
 *
 * @param {Document} document
 */
function initSearchLoader( document ) {
	var searchBoxes = document.querySelectorAll( '.cosmos-search-box' );

	if ( config.wgCosmosSearchHost ) {
		mw.config.set( 'wgCosmosSearchHost', config.wgCosmosSearchHost );
	}

	if ( config.wgCosmosSearchUseActionAPI ) {
		mw.config.set( 'wgCosmosSearchUseActionAPI', true );
	}

	if ( !searchBoxes.length ) {
		return;
	}

	/**
	 * If we are in a browser that doesn't support ES6 fall back to non-JS version.
	 */
	if ( mw.loader.getState( 'skins.cosmos.search' ) === null ) {
		document.body.classList.remove(
			'skin-cosmos-search-vue'
		);
		return;
	}

	Array.prototype.forEach.call( searchBoxes, function ( searchBox ) {
		var searchInner = searchBox.querySelector( 'form > div' ),
			searchInput = searchBox.querySelector( 'input[name="search"]' ),
			clearLoadingIndicators = function () {
				setLoadingIndicatorListeners(
					searchInner,
					false,
					renderSearchLoadingIndicator
				);
			},
			isPrimarySearch = searchInput && searchInput.getAttribute( 'id' ) === 'searchInput';

		if ( !searchInput || !searchInner ) {
			return;
		}
		// Remove tooltips while Vue search is still loading
		searchInput.setAttribute( 'autocomplete', 'off' );
		searchInput.removeAttribute( 'title' );
		setLoadingIndicatorListeners( searchInner, true, renderSearchLoadingIndicator );
		loadSearchModule(
			searchInput,
			'skins.cosmos.search',
			// Make sure we clearLoadingIndicators so that event listeners are removed.
			// Note, loading Vue.js will remove the element from the DOM.
			isPrimarySearch ? function () {
				clearLoadingIndicators();
			} : clearLoadingIndicators
		);
	} );
}

initSearchLoader( document );
