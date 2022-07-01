/** @module actionSearchClient */

const fetchJson = require( './fetch.js' ),
	searchConfig = require( './config.json' );

/**
 * @typedef {Object} ActionResponse
 * @property {ActionResult[]} pages
 */

/**
 * @typedef {Object} ActionResult
 * @property {number} id
 * @property {string} key
 * @property {string} title
 * @property {string} [description]
 * @property {ActionThumbnail | null} [thumbnail]
 *
 */

/**
 * @typedef {Object} ActionThumbnail
 * @property {string} url
 * @property {number | null} [width]
 * @property {number | null} [height]
 */

/**
 * @typedef {Object} SearchResponse
 * @property {string} query
 * @property {SearchResult[]} results
 */

/**
 * @typedef {Object} SearchResult
 * @property {number} id
 * @property {string} key
 * @property {string} title
 * @property {string} [description]
 * @property {SearchResultThumbnail} [thumbnail]
 */

/**
 * @typedef {Object} SearchResultThumbnail
 * @property {string} url
 * @property {number} [width]
 * @property {number} [height]
 */

/**
 * Build URL used for fetch request
 *
 * @param {string} input The search input.
 * @param {string} domain The base URL for the wiki without protocol.
 * @param {number} [limit] Maximum number of results
 * @param {MwMap} config
 * @return {string} url
 */
function getUrl( input, domain, limit, config ) {
	const endpoint = '//' + domain + config.get( 'wgScriptPath' ) + '/api.php?format=json',
		cacheExpiry = searchConfig.wgSearchSuggestCacheExpiry,
		maxResults = searchConfig.wgCosmosMaxSearchResults,
		query = {
			action: 'query',
			smaxage: cacheExpiry,
			maxage: cacheExpiry,
			generator: 'prefixsearch',
			prop: 'pageprops|pageimages',
			redirects: '',
			ppprop: 'displaytitle',
			piprop: 'thumbnail',
			pithumbsize: 200,
			pilimit: maxResults,
			gpssearch: input,
			gpsnamespace: 0,
			gpslimit: maxResults
		};

	switch ( searchConfig.wgCosmosSearchDescriptionSource ) {
		case 'wikidata':
			query.prop += '|description';
			break;
		case 'textextracts':
			query.prop += '|extracts';
			query.exchars = '60';
			query.exintro = '1';
			query.exlimit = maxResults;
			query.explaintext = '1';
			break;
		case 'pagedescription':
			query.prop += '|pageprops';
			query.ppprop += '|description';
			break;
	}

	let queryString = '';
	for ( const property in query ) {
		queryString += '&' + property + '=' + query[ property ];
	}

	return endpoint + queryString;
}

/**
 * @param {Object} pages
 * @return {Array}
 */
function convertObjectToArray( pages ) {
	if ( !pages ) {
		return [];
	}

	return Object.keys( pages ).map( ( key ) => pages[ key ] );
}

/**
 * @param {string} query
 * @param {ActionResponse} actionResponse
 * @return {SearchResponse}
 */
function adaptApiResponse( query, actionResponse ) {
	const descriptionSource = searchConfig.wgCosmosSearchDescriptionSource;
	return {
		query,
		results:
			convertObjectToArray( actionResponse.query.pages )
				.map( ( { pageid, title, pageprops, description, extract, thumbnail } ) => ( {
					id: pageid,
					key: title,
					title: title,
					description: descriptionSource === 'pagedescription' &&
						pageprops &&
						pageprops.description ?
						( pageprops.description.length > 60 ?
							pageprops.description.substring( 0, 60 ) + '...' :
							pageprops.description === '.' ? '' :
								pageprops.description || ''
						) :
						descriptionSource === 'textextracts' ? extract || '' :
							description || '',
					thumbnail: thumbnail ? {
						url: thumbnail.source,
						width: thumbnail.width,
						height: thumbnail.height
					} : undefined
				} ) )
	};
}

/**
 * @typedef {Object} AbortableSearchFetch
 * @property {Promise<SearchResponse>} fetch
 * @property {Function} abort
 */

/**
 * @callback fetchByTitle
 * @param {string} query The search term.
 * @param {string} domain The base URL for the wiki without protocol.
 * @param {number} [limit] Maximum number of results.
 * @return {AbortableSearchFetch}
 */

/**
 * @typedef {Object} SearchClient
 * @property {fetchByTitle} fetchByTitle
 */

/**
 * @param {MwMap} config
 * @return {SearchClient}
 */
function actionSearchClient( config ) {
	const customClient = config.get( 'wgCosmosSearchClient' );
	return customClient || {
		/**
		 * @type {fetchByTitle}
		 */
		fetchByTitle: ( q, domain, limit = searchConfig.wgCosmosMaxSearchResults ) => {
			const url = getUrl( q, domain, limit, config );
			const result = fetchJson( url, {
				headers: {
					accept: 'application/json'
				}
			} );
			const searchResponsePromise = result.fetch
				.then( ( /** @type {ActionResponse} */ res ) => {
					return adaptApiResponse( q, res );
				} );
			return {
				abort: result.abort,
				fetch: searchResponsePromise
			};
		}
	};
}

module.exports = actionSearchClient;
