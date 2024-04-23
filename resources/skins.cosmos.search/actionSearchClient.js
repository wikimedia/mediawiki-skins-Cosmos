/** @module actionSearchClient */

const fetchJson = require( './fetch.js' ),
	searchConfig = require( './config.json' ),
	urlGenerator = require( './urlGenerator.js' );

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
 * Build API URL used for fetch request
 *
 * @param {string} query The search term.
 * @param {string} domain The base URL for the wiki without protocol.
 * @param {number} [limit] Maximum number of results
 * @param {MwMap} config
 * @return {string} url
 */
function getApiUrl( query, domain, limit, config ) {
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
 * @param {MwMap} config
 * @param {string} query
 * @param {ActionResponse} actionResponse
 * @param {boolean} showDescription
 * @return {SearchResponse}
 */
function adaptApiResponse( config, query, actionResponse, showDescription ) {
	const urlGeneratorInstance = urlGenerator( config );
	const descriptionSource = searchConfig.wgCosmosSearchDescriptionSource;
	const pages = actionResponse.query ? actionResponse.query.pages : {};

	return {
		query,
		results:
			convertObjectToArray( pages )
				.map( ( { pageid, title, pageprops, description, extract, thumbnail } ) => ( {
					id: pageid,
					value: pageid,
					label: title,
					key: title,
					title: title,
					url: urlGeneratorInstance.generateUrl( { title: title } ),
					description: showDescription ? ( descriptionSource === 'pagedescription' &&
						pageprops &&
						pageprops.description ?
						( pageprops.description.length > 60 ?
							pageprops.description.slice( 0, 60 ) + '...' :
							pageprops.description === '.' ? '' :
								pageprops.description || ''
						) :
						descriptionSource === 'textextracts' ? extract || '' :
							description || '' ) : undefined,
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
	return config.get( 'wgCosmosSearchClient', {
		/**
		 * @type {fetchByTitle}
		 */
		fetchByTitle: ( query, domain,
			limit = searchConfig.wgCosmosMaxSearchResults, showDescription = true ) => {
			query = query.trim();

			if ( !query ) {
				return {
					abort: () => {
						// Do nothing (no-op)
					},
					fetch: Promise.resolve( { query, results: [] } )
				};
			}

			const url = getApiUrl( query, domain, limit, config );
			const result = fetchJson( url, {
				headers: {
					accept: 'application/json'
				}
			} );
			const searchResponsePromise = result.fetch
				.then( ( /** @type {ActionResponse} */ res ) => {
					return adaptApiResponse( config, query, res, showDescription );
				} );
			return {
				abort: result.abort,
				fetch: searchResponsePromise
			};
		}
	} );
}

module.exports = actionSearchClient;
