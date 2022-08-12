<?php

namespace MediaWiki\Skins\Cosmos;

use Html;
use IContextSource;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Skins\Cosmos\Hooks\CosmosHookRunner;
use MediaWiki\SpecialPage\SpecialPageFactory;
use MediaWiki\User\UserFactory;
use MWTimestamp;
use RecentChange;
use TitleValue;
use WANObjectCache;
use Wikimedia\Rdbms\ILoadBalancer;
use Wikimedia\Rdbms\SelectQueryBuilder;

class CosmosRailBuilder {

	public const CONSTRUCTOR_OPTIONS = [
		'ContentNamespaces',
		'CosmosEnabledRailModules',
		'CosmosRailDisabledNamespaces',
		'CosmosRailDisabledPages',
	];

	/** @var CosmosHookRunner */
	private $hookRunner;

	/** @var IContextSource */
	private $context;

	/** @var ILoadBalancer */
	private $dbLoadBalancer;

	/** @var LinkRenderer */
	private $linkRenderer;

	/** @var ServiceOptions */
	private $options;

	/** @var SpecialPageFactory */
	private $specialPageFactory;

	/** @var UserFactory */
	private $userFactory;

	/** @var WANObjectCache */
	private $WANObjectCache;

	/** @var array */
	protected $disabledModules = [];

	/**
	 * @param CosmosHookRunner $hookRunner
	 * @param ILoadBalancer $dbLoadBalancer
	 * @param LinkRenderer $linkRenderer
	 * @param IContextSource $context
	 * @param ServiceOptions $options
	 * @param SpecialPageFactory $specialPageFactory
	 * @param UserFactory $userFactory
	 * @param WANObjectCache $WANObjectCache
	 */
	public function __construct(
		CosmosHookRunner $hookRunner,
		ILoadBalancer $dbLoadBalancer,
		LinkRenderer $linkRenderer,
		IContextSource $context,
		ServiceOptions $options,
		SpecialPageFactory $specialPageFactory,
		UserFactory $userFactory,
		WANObjectCache $WANObjectCache
	) {
		$options->assertRequiredOptions( self::CONSTRUCTOR_OPTIONS );

		$this->context = $context;
		$this->dbLoadBalancer = $dbLoadBalancer;
		$this->hookRunner = $hookRunner;
		$this->linkRenderer = $linkRenderer;
		$this->options = $options;
		$this->specialPageFactory = $specialPageFactory;
		$this->userFactory = $userFactory;
		$this->WANObjectCache = $WANObjectCache;
	}

	/**
	 * @return string
	 */
	public function buildRail(): string {
		$modules = '';
		foreach ( $this->getModules() as $module => $data ) {
			$header = '';
			if ( $data['header'] ?? false ) {
				$header = $this->buildModuleHeader( $data['header'] );
			}

			$isSticky = ( $data['type'] ?? 'normal' ) === 'sticky';
			$modules .= Html::rawElement( 'section', [
				'class' => [
					'railModule' => true,
					'module' => true,
					'rail-sticky-module' => $isSticky,
				] + (array)( $data['class'] ?? 'custom-module' )
			], $header . $data['body'] );
		}

		$rail = '';
		if ( $modules ) {
			$rail .= Html::openElement( 'div', [
				'class' => 'CosmosRail',
				'id' => 'CosmosRailWrapper',
			] );

			$rail .= Html::rawElement( 'div', [
				'class' => 'cosmos-rail-inner',
				'id' => 'CosmosRail',
			], $modules );

			$rail .= Html::closeElement( 'div' );
		}

		return $rail;
	}

	/**
	 * @return bool
	 */
	public function hasModules(): bool {
		// Disable the recentchanges module to manually check what is needed.
		// Checking everything here is a performance degradation.
		$this->disableModule( 'recentchanges' );

		$hasRecentChangesModule = $this->getEnabledModules()['recentchanges'] &&
			!empty( $this->getRecentChanges() );

		$hasModules = $hasRecentChangesModule || $this->getModules();

		$this->resetDisabledModules();
		return $hasModules;
	}

	/**
	 * @param string $module
	 * @return self
	 */
	public function disableModule( string $module ): self {
		$this->disabledModules[] = $module;

		return $this;
	}

	/**
	 * @return self
	 */
	public function resetDisabledModules(): self {
		$this->disabledModules = [];

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isHidden(): bool {
		$disabledNamespaces = $this->options->get( 'CosmosRailDisabledNamespaces' );
		$disabledPages = $this->options->get( 'CosmosRailDisabledPages' );

		$title = $this->context->getTitle();
		$out = $this->context->getOutput();

		if (
			$title->inNamespaces( $disabledNamespaces ) ||
			(
				$title->isMainPage() &&
				in_array( 'mainpage', $disabledPages )
			) ||
			in_array( $title->getFullText(), $disabledPages ) ||
			(bool)$out->getProperty( 'norail' )
		) {
			return true;
		}

		return false;
	}

	/**
	 * @param string $label
	 * @return string
	 */
	protected function buildModuleHeader( string $label ): string {
		if ( !$this->context->msg( $label )->isDisabled() ) {
			$label = $this->context->msg( $label )->text();
		}

		$header = Html::element( 'h3', [], $label );

		return $header;
	}

	/**
	 * @return array
	 */
	protected function getModules(): array {
		$modules = [];

		if ( $this->isHidden() ) {
			return $modules;
		}

		if ( !in_array( 'recentchanges', $this->disabledModules ) ) {
			$this->buildRecentChangesModule( $modules );
		}

		if ( !in_array( 'interface', $this->disabledModules ) ) {
			$this->buildInterfaceModules( $modules );
		}

		$this->hookRunner->onCosmosRailBuilder( $modules, $this->context->getSkin() );

		return $modules;
	}

	/**
	 * @return array
	 */
	protected function getEnabledModules(): array {
		return $this->options->get( 'CosmosEnabledRailModules' );
	}

	/**
	 * @param array &$modules
	 */
	protected function buildInterfaceModules( array &$modules ) {
		$interfaceRailModules = $this->getEnabledModules()['interface'];

		$interfaceModules = $interfaceRailModules[0] ?? $interfaceRailModules;

		foreach ( (array)$interfaceModules as $message => $type ) {
			if ( $type && !$this->context->msg( $message )->isDisabled() ) {
				$modules['interface-' . $message] = [
					'body' => $this->context->msg( $message )->parse(),
					'class' => 'interface-module',
					'type' => $type,
				];
			}
		}
	}

	/**
	 * @param array &$modules
	 */
	protected function buildRecentChangesModule( array &$modules ) {
		$moduleType = $this->getEnabledModules()['recentchanges'];

		if ( !$moduleType ) {
			return;
		}

		$modules['recentchanges'] = [
			'class' => 'recentchanges-module',
			'header' => 'recentchanges',
			'type' => $moduleType,
		];

		$body = '';
		foreach ( $this->getRecentChanges() as $recentChange ) {
			// Open list item for recent change
			$body .= Html::openElement( 'li' );

			$body .= Html::openElement( 'div', [ 'class' => 'cosmos-recentChanges-page' ] );

			// Create a link to the edited page
			$body .= $this->linkRenderer->makeKnownLink(
				new TitleValue( (int)$recentChange['namespace'], $recentChange['title'] )
			);

			$body .= Html::closeElement( 'div' );

			$body .= Html::openElement( 'div', [ 'class' => 'cosmos-recentChanges-info' ] );

			// Create a link to the user who edited it
			$performer = $recentChange['performer'];
			if ( !$performer->isRegistered() ) {
				$linkTarget = new TitleValue(
					NS_SPECIAL,
					$this->specialPageFactory->getLocalNameFor( 'Contributions', $performer->getName() )
				);
			} else {
				$linkTarget = new TitleValue( NS_USER, $performer->getTitleKey() );
			}

			$body .= $this->linkRenderer->makeLink( $linkTarget, $performer->getName() );

			// Display how long ago it was edited
			$body .= ' • ';
			$language = $this->context->getSkin()->getLanguage();

			$body .= $language->getHumanTimestamp(
				MWTimestamp::getInstance( $recentChange['timestamp'] )
			);

			$body .= Html::closeElement( 'div' );

			// Close the list item
			$body .= Html::closeElement( 'li' );
		}

		$modules['recentchanges']['body'] = $body;
	}

	/**
	 * @return array
	 */
	protected function getRecentChanges(): array {
		$cacheKey = $this->WANObjectCache->makeKey( 'cosmos_recentChanges', 4 );
		$recentChanges = $this->WANObjectCache->get( $cacheKey );

		if ( empty( $recentChanges ) ) {
			$dbr = $this->dbLoadBalancer->getConnectionRef( DB_REPLICA );

			$res = $dbr->newSelectQueryBuilder()
				->table( 'recentchanges' )
				->fields( [
					'rc_actor',
					'rc_namespace',
					'rc_title',
					'rc_timestamp',
				] )
				->where( [
					'rc_namespace' => $this->options->get( 'ContentNamespaces' ),
					'rc_type' => RecentChange::parseToRCType( [ 'new', 'edit' ] ),
					'rc_bot' => 0,
					'rc_deleted' => 0,
				] )
				->orderBy( 'rc_timestamp', SelectQueryBuilder::SORT_DESC )
				->limit( 4 )
				->offset( 0 )
				->caller( __METHOD__ )
				->fetchResultSet();

			$recentChanges = [];
			foreach ( $res as $row ) {
				$recentChanges[] = [
					'performer' => $this->userFactory->newFromActorId( $row->rc_actor ),
					'timestamp' => $row->rc_timestamp,
					'namespace' => $row->rc_namespace,
					'title' => $row->rc_title,
				];
			}

			$this->WANObjectCache->set( $cacheKey, $recentChanges, 30 );
		}

		return $recentChanges;
	}
}
