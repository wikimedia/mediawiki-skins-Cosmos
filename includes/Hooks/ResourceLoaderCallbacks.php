<?php

namespace MediaWiki\Skins\Cosmos\Hooks;

use Config;
use MediaWiki\ResourceLoader\Context;

class ResourceLoaderCallbacks {

	/**
	 * @param Context $context
	 * @param Config $config
	 * @return array
	 */
	public static function getCosmosResourceLoaderConfig(
		Context $context,
		Config $config
	): array {
		return [
			'wgCosmosSearchHost' => $config->get( 'CosmosSearchHost' ),
			'wgCosmosSearchUseActionAPI' => (bool)$config->get( 'CosmosSearchUseActionAPI' ),
		];
	}

	/**
	 * @param Context $context
	 * @param Config $config
	 * @return array
	 */
	public static function getCosmosSearchResourceLoaderConfig(
		Context $context,
		Config $config
	): array {
		return array_merge( [
			'wgCosmosSearchDescriptionSource' => $config->get( 'CosmosSearchDescriptionSource' ),
			'wgCosmosMaxSearchResults' => $config->get( 'CosmosMaxSearchResults' ),
			'wgSearchSuggestCacheExpiry' => $config->get( 'SearchSuggestCacheExpiry' ),
		], $config->get( 'CosmosSearchOptions' ) );
	}
}
