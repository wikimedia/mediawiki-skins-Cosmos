const config = require( './config.json' ),
	descriptionSource = config.wgCosmosSearchDescriptionSource;

/**
 * Build URL used for fetch request
 *
 * @param {string} input
 * @param {string} domain
 * @return {string} url
 */
function getUrl( input, domain ) {
	const endpoint = '//' + domain + config.wgScriptPath + '/api.php?format=json',
		cacheExpiry = config.wgSearchSuggestCacheExpiry,
		maxResults = config.wgCosmosMaxSearchResults,
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

	switch ( descriptionSource ) {
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

function convertDataToResults( data ) {
	const getTitle = ( item ) => {
		// don't use display title for now, currently if used, it
		// makes the page URL also go to the display title.
		/* if ( item.pageprops && item.pageprops.displaytitle ) {
			return ( item.pageprops.displaytitle ).replace( /<\/?[^>]+(>|$)/g, '' );
		} else {
			return item.title;
		} */

		return item.title;
	};

	const getDescription = ( item ) => {
		switch ( descriptionSource ) {
			case 'wikidata':
				return item.description || '';
			case 'textextracts':
				return item.extract || '';
			case 'pagedescription':
				return item.pageprops.description.substring( 0, 60 ) + '...' || '';
		}
	};

	const results = [];

	if ( typeof data?.query?.pages === 'undefined' ) {
		return [];
	}

	/* eslint-disable-next-line es/no-object-values */
	data = Object.values( data.query.pages );

	// Sort the data with the index property since it is not in order
	data.sort( ( a, b ) => {
		return a.index - b.index;
	} );

	for ( let i = 0; i < data.length; i++ ) {
		results[ i ] = {
			id: data[ i ].pageid,
			title: getTitle( data[ i ] ),
			description: getDescription( data[ i ] )
		};

		if ( data[ i ].thumbnail && data[ i ].thumbnail.source ) {
			results[ i ].thumbnail = {
				url: data[ i ].thumbnail.source,
				height: data[ i ].thumbnail.height,
				width: data[ i ].thumbnail.width
			};
		}
	}

	return results;
}

module.exports = {
	getUrl: getUrl,
	convertDataToResults: convertDataToResults
};
