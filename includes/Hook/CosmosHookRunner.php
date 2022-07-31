<?php

namespace MediaWiki\Skins\Cosmos\Hook;

use MediaWiki\HookContainer\HookContainer;
use Skin;

class CosmosHookRunner implements CosmosRailBuilderHook {

	/**
	 * @var HookContainer
	 */
	private $container;

	/**
	 * @param HookContainer $container
	 */
	public function __construct( HookContainer $container ) {
		$this->container = $container;
	}

	/** @inheritDoc */
	public function onCosmosRailBuilder( array &$modules, Skin $skin ): void {
		$this->container->run( 'CosmosRailBuilder', [ &$modules, $skin ] );
	}
}
