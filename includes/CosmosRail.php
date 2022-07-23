<?php

namespace MediaWiki\Skin\Cosmos;

use Html;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\SpecialPageFactory;
use MediaWiki\User\UserFactory;
use MWTimestamp;
use RecentChange;
use TitleValue;
use WANObjectCache;
use Wikimedia\Rdbms\ILoadBalancer;
use Wikimedia\Rdbms\SelectQueryBuilder;

class CosmosRail {
	/** @var CosmosConfig */
	private $config;

	/** @var IContextSource */
	private $context;

	/** @var ILoadBalancer */
	private $dbLoadBalancer;

	/** @var LinkRenderer */
	private $linkRenderer;

	/** @var WANObjectCache */
	private $objectCache;

	/** @var SpecialPageFactory */
	private $specialPageFactory;

	/** @var UserFactory */
	private $userFactory;

	/** @var string */
	private static $railHookContents = '';

	/**
	 * @param CosmosConfig $config
	 * @param IContextSource $context
	 */
	public function __construct(
		CosmosConfig $config,
		IContextSource $context
	) {
		$this->config = $config;
		$this->context = $context;

		/** @var SkinCosmos */
		$skin = $context->getSkin();
		'@phan-var SkinCosmos $skin';

		$this->dbLoadBalancer = $skin->dbLoadBalancer;
		$this->linkRenderer = $skin->linkRenderer;
		$this->objectCache = $skin->objectCache;
		$this->specialPageFactory = $skin->specialPageFactory;
		$this->userFactory = $skin->userFactory;

		if ( !(bool)static::$railHookContents ) {
			$hookContainer = MediaWikiServices::getInstance()->getHookContainer();
			$hookContainer->run( 'CosmosRail', [ $this, $skin ] );
		}
	}

	/**
	 * @param CosmosConfig $config
	 * @param IContextSource $context
	 * @return bool
	 */
	public static function railsHidden(
		CosmosConfig $config,
		IContextSource $context
	) {
		$disabledNamespaces = $config->getRailDisabledNamespaces();
		$disabledPages = $config->getRailDisabledPages();

		$title = $context->getTitle();
		$out = $context->getOutput();

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
	 * @param CosmosConfig $config
	 * @param IContextSource $context
	 * @return bool
	 */
	public static function railsExist(
		CosmosConfig $config,
		IContextSource $context
	) {
		$validModules = [ 'interface', 'recentchanges' ];
		$enabledModules = [];

		foreach ( $config->getEnabledRailModules() as $module => $value ) {
			if ( $value ) {
				$enabledModules[] = $module;
			}
		}

		$moduleCount = 0;

		$interfaceRailModules = $config->getEnabledRailModules()['interface'];

		$interfaceModules = $interfaceRailModules[0] ?? $interfaceRailModules;

		foreach ( (array)$interfaceModules as $message => $type ) {
			if ( $type && !$context->msg( $message )->isDisabled() ) {
				$moduleCount++;
			}
		}

		if ( !array_intersect( $validModules, $enabledModules ) ||
			( $enabledModules === [ 'interface' ] &&
				$moduleCount === 0 ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @param CosmosConfig $config
	 * @param IContextSource $context
	 * @return bool
	 */
	public static function hookRailsExist(
		CosmosConfig $config,
		IContextSource $context
	) {
		if ( !(bool)static::$railHookContents ) {
			$self = new self( $config, $context );

			$hookContainer = MediaWikiServices::getInstance()->getHookContainer();
			$hookContainer->run( 'CosmosRail', [ $self, $context->getSkin() ] );
		}

		if ( !(bool)static::$railHookContents ) {
			return false;
		}

		return true;
	}

	/**
	 * @return string
	 */
	public function buildRail() {
		if ( ( !self::railsExist( $this->config, $this->context ) &&
			!self::hookRailsExist( $this->config, $this->context ) ) ||
				self::railsHidden( $this->config, $this->context )
		) {
			return '';
		}

		$html = Html::openElement( 'div', [
				'class' => 'CosmosRail',
				'id' => 'CosmosRailWrapper'
			]
		);

		$html .= Html::openElement( 'div', [
				'class' => 'cosmos-rail-inner',
				'id' => 'CosmosRail'
			]
		);

		$enableRecentChangesModule = $this->config->getEnabledRailModules()['recentchanges'];
		if ( !empty( $this->getRecentChanges() ) && $enableRecentChangesModule ) {
			$html .= $this->getRecentChangesModule();
		}

		if ( (bool)static::$railHookContents ) {
			$html .= static::$railHookContents;
		}

		foreach ( (array)$this->getInterfaceModules() as $message => $type ) {
			if ( $type === 'sticky' ) {
				$html .= Html::rawElement( 'section', [
						'class' => 'railModule module rail-sticky-module interface-module'
					], $this->context->msg( $message )->parse()
				);
			} else {
				$html .= Html::rawElement( 'section', [
						'class' => 'railModule module interface-module'
					], $this->context->msg( $message )->parse()
				);
			}
		}

		$html .= Html::closeElement( 'div' );
		$html .= Html::closeElement( 'div' );

		return $html;
	}

	/**
	 * @param string $body
	 * @param string $header
	 * @param string $type
	 * @param string $class
	 */
	public function buildModule(
		string $body,
		string $header = '',
		string $type = 'normal',
		string $class = 'custom-module'
	) {
		if ( $type === 'sticky' ) {
			static::$railHookContents .= Html::openElement( 'section', [
					'class' => "railModule module rail-sticky-module {$class}"
				]
			);
		} else {
			static::$railHookContents .= Html::openElement( 'section', [
					'class' => "railModule module {$class}"
				]
			);
		}

		if ( $header ) {
			static::$railHookContents .= $this->buildModuleHeader( $header );
		}

		static::$railHookContents .= $body;

		static::$railHookContents .= Html::closeElement( 'section' );
	}

	/**
	 * @param string $label
	 * @return string
	 */
	protected function buildModuleHeader( string $label ) {
		if ( !$this->context->msg( $label )->isDisabled() ) {
			$label = $this->context->msg( $label )->text();
		}

		$html = Html::element( 'h3', [], $label );

		return $html;
	}

	/**
	 * @return array
	 */
	protected function getInterfaceModules() {
		$modules = [];

		$interfaceRailModules = $this->config->getEnabledRailModules()['interface'];

		$interfaceModules = $interfaceRailModules[0] ?? $interfaceRailModules;

		foreach ( (array)$interfaceModules as $message => $type ) {
			if ( $type && !$this->context->msg( $message )->isDisabled() ) {
				$modules += [ $message => $type ];
			}
		}

		return $modules;
	}

	/**
	 * @return string
	 */
	protected function getRecentChangesModule() {
		$type = $this->config->getEnabledRailModules()['recentchanges'];

		if ( $type === 'sticky' ) {
			$html = Html::openElement( 'section', [
					'class' => 'railModule module rail-sticky-module recentchanges-module'
				]
			);
		} else {
			$html = Html::openElement( 'section', [
					'class' => 'railModule module recentchanges-module'
				]
			);
		}

		$html .= $this->buildModuleHeader( 'recentchanges' );

		foreach ( $this->getRecentChanges() as $recentChange ) {
				// Open list item for recent change
				$html .= Html::openElement( 'li' );

				$html .= Html::openElement( 'div', [ 'class' => 'cosmos-recentChanges-page' ] );

				// Create a link to the edited page
				$html .= $this->linkRenderer->makeKnownLink(
					new TitleValue( (int)$recentChange['namespace'], $recentChange['title'] )
				);

				$html .= Html::closeElement( 'div' );

				$html .= Html::openElement( 'div', [ 'class' => 'cosmos-recentChanges-info' ] );

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

				$html .= $this->linkRenderer->makeLink( $linkTarget, $performer->getName() );

				// Display how long ago it was edited
				$html .= ' • ';
				$language = $this->context->getSkin()->getLanguage();
				$html .= $language->getHumanTimestamp(
					MWTimestamp::getInstance( $recentChange['timestamp'] )
				);

				$html .= Html::closeElement( 'div' );

				// Close the list item
				$html .= Html::closeElement( 'li' );
		}

		$html .= Html::closeElement( 'section' );

		return $html;
	}

	/**
	 * @return array
	 */
	protected function getRecentChanges() {
		$cacheKey = $this->objectCache->makeKey( 'cosmos_recentChanges', 4 );
		$recentChanges = $this->objectCache->get( $cacheKey );

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

			$this->objectCache->set( $cacheKey, $recentChanges, 30 );
		}

		return $recentChanges;
	}
}
