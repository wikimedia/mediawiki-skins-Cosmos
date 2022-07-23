<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Skins\Cosmos\CosmosBackgroundLookup;
use MediaWiki\Skins\Cosmos\CosmosConfig;
use MediaWiki\Skins\Cosmos\CosmosWordmarkLookup;

return [
	'CosmosConfig' => static function ( MediaWikiServices $services ): CosmosConfig {
		return new CosmosConfig(
			$services->getConfigFactory()->makeConfig( 'Cosmos' )
		);
	},

	'CosmosWordmarkLookup' => static function ( MediaWikiServices $services ): CosmosWordmarkLookup {
		return new CosmosWordmarkLookup(
			$services->getTitleFactory(),
			$services->getRepoGroup(),
			$services->getService( 'CosmosConfig' )->getWordmark()
		);
	},

	'CosmosBackgroundLookup' => static function ( MediaWikiServices $services ): CosmosBackgroundLookup {
		return new CosmosBackgroundLookup(
			$services->getTitleFactory(),
			$services->getRepoGroup(),
			$services->getService( 'CosmosConfig' )->getBackgroundImage(),
			$services->getService( 'CosmosConfig' )->getWikiHeaderBackgroundImage()
		);
	}
];
