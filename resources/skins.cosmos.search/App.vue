<template>
	<cdx-typeahead-search
		:id="id"
		ref="searchForm"
		class="cosmos-typeahead-search"
		:class="rootClasses"
		:search-results-label="$i18n( 'searchresults' ).text()"
		:accesskey="searchAccessKey"
		:autocapitalize="autocapitalizeValue"
		:title="searchTitle"
		:placeholder="searchPlaceholder"
		:aria-label="searchPlaceholder"
		:initial-input-value="searchQuery"
		:button-label="$i18n( 'searchbutton' ).text()"
		:form-action="action"
		:show-thumbnail="showThumbnail"
		:highlight-query="highlightQuery"
		:auto-expand-width="autoExpandWidth"
		:search-results="suggestions"
		:search-footer-url="searchFooterUrl"
		@input="onInput"
		@focus="onFocus"
		@blur="onBlur"
	>
		<template #default>
			<input
				type="hidden"
				name="title"
				:value="searchPageTitle"
			>
		</template>
		<template #search-results-pending>
			{{ $i18n( 'cosmos-search-loader' ).text() }}
		</template>
		<!-- eslint-disable-next-line vue/no-template-shadow -->
		<template #search-footer-text="{ searchQuery }">
			<span v-i18n-html:cosmos-searchsuggest-containing="[ searchQuery ]"></span>
		</template>
	</cdx-typeahead-search>
</template>

<script>
const { CdxTypeaheadSearch } = mw.loader.require( 'skins.cosmos.search.codex.scripts' ),
	{ defineComponent, nextTick } = require( 'vue' ),
	restClient = require( './restSearchClient.js' )( mw.config ),
	actionClient = require( './actionSearchClient.js' )( mw.config ),
	urlGenerator = require( './urlGenerator.js' )( mw.config );

// @vue/component
module.exports = exports = defineComponent( {
	name: 'App',
	compilerOptions: {
		whitespace: 'condense'
	},
	components: { CdxTypeaheadSearch },
	props: {
		id: {
			type: String,
			required: true
		},
		autocapitalizeValue: {
			type: String,
			default: undefined
		},
		searchPageTitle: {
			type: String,
			default: 'Special:Search'
		},
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
			type: String,
			default: undefined
		},
		/** The access key informational tip for search. */
		searchTitle: {
			type: String,
			default: undefined
		},
		/** The ghost text shown when no search query is entered. */
		searchPlaceholder: {
			type: String,
			default: undefined
		},
		/**
		 * The search query string taken from the server-side rendered input immediately before
		 * client render.
		 */
		searchQuery: {
			type: String,
			default: undefined
		},
		showThumbnail: {
			type: Boolean,
			required: true,
			default: false
		},
		showDescription: {
			type: Boolean,
			default: false
		},
		highlightQuery: {
			type: Boolean,
			default: false
		},
		autoExpandWidth: {
			type: Boolean,
			default: false
		}
	},
	data() {
		return {
			// Suggestions to be shown in the TypeaheadSearch menu.
			suggestions: [],

			// Link to the search page for the current search query.
			searchFooterUrl: '',

			// The current search query. Used to detect whether a fetch response is stale.
			currentSearchQuery: '',

			// Whether to apply a CSS class that disables the CSS transitions on the text input
			disableTransitions: this.autofocusInput,

			isFocused: false
		};
	},
	computed: {
		rootClasses() {
			return {
				'cosmos-search-box-disable-transitions': this.disableTransitions,
				'cosmos-typeahead-search--active': this.isFocused
			};
		}
	},
	methods: {
		/**
		 * Fetch suggestions when new input is received.
		 *
		 * @param {string} value
		 */
		onInput: function ( value ) {
			const domain = mw.config.get( 'wgCosmosSearchHost', location.host ),
				query = value.trim();

			this.currentSearchQuery = query;

			if ( query === '' ) {
				this.suggestions = [];
				this.searchFooterUrl = '';
				return;
			}

			if ( mw.config.get( 'wgCosmosSearchUseActionAPI', false ) ) {
				actionClient.fetchByTitle( query, domain, undefined, this.showDescription ).fetch
					.then( ( data ) => {
						this.suggestions = data.results;
						this.searchFooterUrl = urlGenerator.generateUrl( query );
					} )
					.catch( () => {
						// TODO: error handling
					} );
				return;
			}

			restClient.fetchByTitle( query, domain, undefined, this.showDescription ).fetch
				.then( ( data ) => {
					this.suggestions = data.results;
					this.searchFooterUrl = urlGenerator.generateUrl( query );
				} )
				.catch( () => {
					// TODO: error handling
				} );
		},

		onFocus() {
			this.isFocused = true;
		},

		onBlur() {
			this.isFocused = false;
		}
	},
	mounted() {
		if ( this.autofocusInput ) {
			this.$refs.searchForm.focus();
			nextTick( () => {
				this.disableTransitions = false;
			} );
		}
	}
} );
</script>
