<template>
	<wvui-typeahead-search
		:id="id"
		ref="searchForm"
		:client="getClient"
		:domain="domain"
		:suggestions-label="$i18n( 'searchresults' ).text()"
		:accesskey="searchAccessKey"
		:title="searchTitle"
		:article-path="articlePath"
		:placeholder="searchPlaceholder"
		:aria-label="searchPlaceholder"
		:search-page-title="searchPageTitle"
		:initial-input-value="searchQuery"
		:button-label="$i18n( 'searchbutton' ).text()"
		:form-action="action"
		:search-language="language"
		:show-thumbnail="showThumbnail"
		:show-description="showDescription"
		:highlight-query="highlightQuery"
		:auto-expand-width="autoExpandWidth"
	>
		<template #default>
			<input type="hidden"
				name="title"
				:value="searchPageTitle"
			>
			<input type="hidden"
				name="wprov"
				:value="wprov"
			>
		</template>
		<template #search-footer-text="{ searchQuery }">
			<span v-i18n-html:cosmos-searchsuggest-containing="[ searchQuery ]"></span>
		</template>
	</wvui-typeahead-search>
</template>

<script>
const wvui = require( 'wvui-search' ),
	restClient = require( './restSearchClient.js' ),
	actionClient = require( './actionSearchClient.js' );

module.exports = {
	name: 'App',
	components: wvui,
	mounted() {
		// access the element associated with the wvui-typeahead-search component
		// eslint-disable-next-line no-jquery/variable-pattern
		const wvuiSearchForm = this.$refs.searchForm.$el;
		if ( this.autofocusInput ) {
			// TODO: The wvui-typeahead-search component does not accept an autofocus parameter
			// or directive. This can be removed when its does.
			wvuiSearchForm.querySelector( 'input' ).focus();
		}
	},
	computed: {
		/**
		 * @return {string}
		 */
		articlePath: () => mw.config.get( 'wgScript' ),
		/**
		 * Allow wikis to replace the default search API client
		 *
		 * @return {Object}
		 */
		getClient: () => {
			if ( mw.config.get( 'wgCosmosSearchUseActionAPI', false ) ) {
				return actionClient( mw.config );
			}

			return restClient( mw.config );
		},
		language: () => {
			return mw.config.get( 'wgUserLanguage' );
		},
		domain: () => {
			return mw.config.get( 'wgCosmosSearchHost', location.host );
		}
	},
	props: {
		id: {
			type: String,
			required: true
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
		},
		highlightQuery: {
			type: Boolean,
			default: true
		},
		autoExpandWidth: {
			type: Boolean,
			default: false
		}
	}
};
</script>
