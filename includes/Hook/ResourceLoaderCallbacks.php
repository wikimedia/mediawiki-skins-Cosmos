<?php

namespace MediaWiki\Skins\Cosmos\Hook;

use Config;
use ResourceLoaderContext;

class ResourceLoaderCallbacks {

	/**
	 * @param ResourceLoaderContext $context
	 * @param Config $config
	 * @return array
	 */
	public static function getCosmosResourceLoaderConfig(
		ResourceLoaderContext $context,
		Config $config
	): array {
		return [
			'wgCosmosSearchHost' => $config->get( 'CosmosSearchHost' ),
			'wgCosmosSearchUseActionAPI' => (bool)$config->get( 'CosmosSearchUseActionAPI' ),
		];
	}

	/**
	 * @param ResourceLoaderContext $context
	 * @param Config $config
	 * @return array
	 */
	public static function getCosmosSearchResourceLoaderConfig(
		ResourceLoaderContext $context,
		Config $config
	): array {
		return array_merge( [
			'wgCosmosSearchDescriptionSource' => $config->get( 'CosmosSearchDescriptionSource' ),
			'wgCosmosMaxSearchResults' => $config->get( 'CosmosMaxSearchResults' ),
			'wgSearchSuggestCacheExpiry' => $config->get( 'SearchSuggestCacheExpiry' ),
		], $config->get( 'CosmosSearchOptions' ) );
	}
}
