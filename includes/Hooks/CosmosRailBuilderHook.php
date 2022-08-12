<?php

namespace MediaWiki\Skins\Cosmos\Hooks;

use Skin;

interface CosmosRailBuilderHook {

	/**
	 * @param array &$modules
	 * @param Skin $skin
	 * @return void
	 */
	public function onCosmosRailBuilder( array &$modules, Skin $skin ): void;
}
