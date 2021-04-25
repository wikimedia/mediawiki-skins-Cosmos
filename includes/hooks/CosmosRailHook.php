<?php

namespace MediaWiki\Skin\Cosmos\Hook;

use MediaWiki\Skin\Cosmos\CosmosRail;
use Skin;

interface CosmosRailHook {
	/**
	 * @param CosmosRail $cosmosRail
	 * @param Skin $skin
	 */
	public function onCosmosRail( CosmosRail $cosmosRail, Skin $skin );
}
