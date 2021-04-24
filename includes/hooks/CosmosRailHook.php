<?php

namespace MediaWiki\Skin\Cosmos\Hook;

use MediaWiki\Skin\Cosmos\CosmosRail;

interface CosmosRailHook {
	/**
	 * @param CosmosRail $cosmosRail
	 */
	public function onCosmosRail( CosmosRail $cosmosRail );
}
