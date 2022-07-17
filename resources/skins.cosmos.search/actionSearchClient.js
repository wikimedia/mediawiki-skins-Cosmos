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
 * @param {string} query The search term.
 * @param {string} domain The base URL for the wiki without protocol.
 * @param {number} [limit] Maximum number of results
 * @param {MwMap} config
 * @return {string} url
 */
function getUrl( query, domain, limit, config ) {
	const endpoint = '//' + domain + config.get( 'wgScriptPath' ) + '/api.php?format=json',
		cacheExpiry = searchConfig.wgSearchSuggestCacheExpiry,
		params = {
			formatversion: '2',
			uselang: 'content',
			smaxage: cacheExpiry,
			maxage: cacheExpiry,
			origin: '*',
			redirects: '',
			action: 'query',
			prop: 'pageprops|pageimages',
			ppprop: 'displaytitle',
			pilicense: 'any',
			piprop: 'thumbnail',
			pithumbsize: '80',
			generator: 'prefixsearch',
			gpslimit: limit.toString(),
			gpssearch: query
		};

	switch ( searchConfig.wgCosmosSearchDescriptionSource ) {
		case 'wikidata':
			params.prop += '|description';
			params.descprefersource = 'local';
			break;
		case 'textextracts':
			params.prop += '|extracts';
			params.exchars = '60';
			params.exintro = '1';
			params.explaintext = '1';
			break;
		case 'pagedescription':
			params.prop += '|pageprops';
			params.ppprop += '|description';
			break;
	}

	let queryString = '';
	for ( const property in params ) {
		queryString += '&' + property + '=' + params[ property ];
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
	const pages = actionResponse.query ? actionResponse.query.pages : {};

	return {
		query,
		results:
			convertObjectToArray( pages )
				.map( ( { pageid, title, pageprops, description, extract, thumbnail } ) => ( {
					id: pageid,
					key: title,
					title: title,
					description: descriptionSource === 'pagedescription' &&
						pageprops &&
						pageprops.description ?
						( pageprops.description.length > 60 ?
							pageprops.description.slice( 0, 60 ) + '...' :
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
		fetchByTitle: ( query, domain, limit = searchConfig.wgCosmosMaxSearchResults ) => {
			query = query.trim();

			if ( !query ) {
				return {
					abort: () => {
						// Do nothing (no-op)
					},
					fetch: Promise.resolve( { query, results: [] } )
				};
			}

			const headers = {
				accept: 'application/json'
			};

			const url = getUrl( query, domain, limit, config );
			const { fetch, abort } = fetchJson( url, { headers } );

			const searchResponsePromise = fetch.then(
				( /** @type {ActionResponse} */ res ) => {
					return adaptApiResponse( query, res );
				}
			);

			return {
				abort,
				fetch: searchResponsePromise
			};
		}
	};
}

module.exports = actionSearchClient;
