<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Skin\Cosmos\CosmosConfig;

return [
	'CosmosConfig' => static function ( MediaWikiServices $services ) : CosmosConfig {
		return new CosmosConfig(
			$services->getConfigFactory()->makeConfig( 'cosmos' )
		);
	}
];
