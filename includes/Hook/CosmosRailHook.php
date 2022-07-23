<?php

namespace MediaWiki\Skins\Cosmos\Hook;

use MediaWiki\Skins\Cosmos\CosmosRail;
use Skin;

interface CosmosRailHook {
	/**
	 * @param CosmosRail $cosmosRail
	 * @param Skin $skin
	 */
	public function onCosmosRail( CosmosRail $cosmosRail, Skin $skin );
}
