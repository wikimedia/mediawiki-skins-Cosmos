<template>
	<wvui-typeahead-search
		id="searchform"
		ref="searchForm"
		:client="getClient"
		:domain="domain"
		:footer-search-text="$i18n( 'searchsuggest-containing' ).text()"
		:suggestions-label="$i18n( 'searchresults' ).text()"
		:accesskey="searchAccessKey"
		:title="searchTitle"
		:placeholder="searchPlaceholder"
		:aria-label="searchPlaceholder"
		:initial-input-value="searchQuery"
		:button-label="$i18n( 'searchbutton' ).text()"
		:form-action="action"
		:search-language="language"
		:show-thumbnail="showThumbnail"
		:show-description="showDescription"
	>
		<input type="hidden"
			name="title"
			value="Special:Search"
		>
	</wvui-typeahead-search>
</template>

<script>
var wvui = require( 'wvui' );

module.exports = {
	name: 'App',
	components: wvui,
	mounted: function () {
		// access the element associated with the wvui-typeahead-search component
		// eslint-disable-next-line no-jquery/variable-pattern
		var wvuiSearchForm = this.$refs.searchForm.$el;
		if ( this.autofocusInput ) {
			// TODO: The wvui-typeahead-search component accepts an id prop but does not
			// display that value as an HTML attribute on the form element.
			wvuiSearchForm.querySelector( 'form' ).setAttribute( 'id', 'searchform' );
			// TODO: The wvui-typeahead-search component does not accept an autofocus parameter
			// or directive. This can be removed when its does.
			wvuiSearchForm.querySelector( 'input' ).focus();
		}
	},
	computed: {
		/**
		 * Allow wikis to replace the default search API client
		 *
		 * @return {void|Object}
		 */
		getClient: function () {
			if ( mw.config.get( 'wgCosmosSearchClient', undefined ) ) {
				return mw.config.get( 'wgCosmosSearchClient', undefined );
			}

			if ( mw.config.get( 'wgCosmosSearchUseActionAPI', false ) ) {
				var actionAPI = require( './actionAPIGateway.js' );
				return {
					fetchByTitle: function ( query, domain, limit ) {
						var xhr = fetch( actionAPI.getUrl( query, domain ) )
						.then( function ( resp ) {
							return resp.json();
						} ).then( function ( json ) {
							return {
								results: actionAPI.convertDataToResults( json )
							}
						} );

						return {
							fetch: xhr,
							abort: function () {}
						}
					}
				};
			}

			return mw.config.get( 'wgCosmosSearchClient', undefined );
		},
		language: function () {
			return mw.config.get( 'wgUserLanguage' );
		},
		domain: function () {
			return mw.config.get( 'wgCosmosSearchHost', location.host );
		}
	},
	props: {
		autofocusInput: {
			type: Boolean,
			default: false
		},
		action: {
			type: String,
			default: ''
		},
		/** The keyboard shortcut to focus search. */
		searchAccessKey: {
			type: String
		},
		/** The access key informational tip for search. */
		searchTitle: {
			type: String
		},
		/** The ghost text shown when no search query is entered. */
		searchPlaceholder: {
			type: String
		},
		/**
		 * The search query string taken from the server-side rendered input immediately before
		 * client render.
		 */
		searchQuery: {
			type: String
		},
		showThumbnail: {
			type: Boolean,
			default: true
		},
		showDescription: {
			type: Boolean,
			default: true
		}
	}
};
</script>
