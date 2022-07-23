<?php

namespace MediaWiki\Skins\Cosmos;

use Config;
use ConfigFactory;
use ExtensionRegistry;
use Language;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\SpecialPage\SpecialPageFactory;
use MediaWiki\User\UserFactory;
use SkinTemplate;
use TitleFactory;
use UserProfilePage;
use WANObjectCache;
use Wikimedia\Rdbms\ILoadBalancer;

class SkinCosmos extends SkinTemplate {

	/** @var Config */
	public $config;

	/** @var Language */
	public $contentLanguage;

	/** @var CosmosConfig */
	public $cosmosConfig;

	/** @var ILoadBalancer */
	public $dbLoadBalancer;

	/** @var LanguageNameUtils */
	public $languageNameUtils;

	/** @var LinkRenderer */
	public $linkRenderer;

	/** @var WANObjectCache */
	public $objectCache;

	/** @var PermissionManager */
	public $permissionManager;

	/** @var SpecialPageFactory */
	public $specialPageFactory;

	/** @var TitleFactory */
	public $titleFactory;

	/** @var UserFactory */
	public $userFactory;

	/** @var CosmosWordmarkLookup */
	public $wordmarkLookup;

	/**
	 * @param ConfigFactory $configFactory
	 * @param Language $contentLanguage
	 * @param CosmosConfig $cosmosConfig
	 * @param CosmosWordmarkLookup $cosmosWordmarkLookup
	 * @param ILoadBalancer $dbLoadBalancer
	 * @param LanguageNameUtils $languageNameUtils
	 * @param LinkRenderer $linkRenderer
	 * @param WANObjectCache $WANObjectCache
	 * @param PermissionManager $permissionManager
	 * @param SpecialPageFactory $specialPageFactory
	 * @param TitleFactory $titleFactory
	 * @param UserFactory $userFactory
	 * @param array $options
	 */
	public function __construct(
		ConfigFactory $configFactory,
		Language $contentLanguage,
		CosmosConfig $cosmosConfig,
		CosmosWordmarkLookup $cosmosWordmarkLookup,
		ILoadBalancer $dbLoadBalancer,
		LanguageNameUtils $languageNameUtils,
		LinkRenderer $linkRenderer,
		WANObjectCache $WANObjectCache,
		PermissionManager $permissionManager,
		SpecialPageFactory $specialPageFactory,
		TitleFactory $titleFactory,
		UserFactory $userFactory,
		array $options
	) {
		parent::__construct( $options );

		$this->config = $configFactory->makeConfig( 'Cosmos' );
		$this->contentLanguage = $contentLanguage;
		$this->cosmosConfig = $cosmosConfig;
		$this->dbLoadBalancer = $dbLoadBalancer;
		$this->languageNameUtils = $languageNameUtils;
		$this->linkRenderer = $linkRenderer;
		$this->objectCache = $WANObjectCache;
		$this->permissionManager = $permissionManager;
		$this->specialPageFactory = $specialPageFactory;
		$this->titleFactory = $titleFactory;
		$this->userFactory = $userFactory;
		$this->wordmarkLookup = $cosmosWordmarkLookup;
	}

	/**
	 * @return array
	 */
	public function getDefaultModules() {
		$modules = parent::getDefaultModules();

		// CosmosRail styles
		if ( ( CosmosRail::railsExist( $this->cosmosConfig, $this->getContext() ) ||
				CosmosRail::hookRailsExist( $this->cosmosConfig, $this->getContext() )
			) &&
			!CosmosRail::railsHidden( $this->cosmosConfig, $this->getContext() )
		) {
			$modules['styles']['skin'][] = 'skins.cosmos.rail';
		}

		// Load PortableInfobox styles
		if ( ExtensionRegistry::getInstance()->isLoaded( 'Portable Infobox' ) ) {
			$modules['styles']['skin'][] = 'skins.cosmos.portableinfobox';

			// Load PortableInfobox EuropaTheme style if the configuration is enabled
			if ( $this->config->get( 'CosmosEnablePortableInfoboxEuropaTheme' ) ) {
				$modules['styles']['skin'][] = 'skins.cosmos.portableinfobox.europa';
			} else {
				$modules['styles']['skin'][] = 'skins.cosmos.portableinfobox.default';
			}
		}

		if (
			LessUtil::isThemeDark( 'content-background-color' ) &&
			ExtensionRegistry::getInstance()->isLoaded( 'CodeMirror' ) &&
			ExtensionRegistry::getInstance()->isLoaded( 'VisualEditor' )
		) {
			$modules['styles']['skin'][] = 'skins.cosmos.codemirror';
		}

		// Load SocialProfile styles if the respective configuration variables are enabled
		if ( class_exists( UserProfilePage::class ) ) {
			if ( $this->config->get( 'CosmosSocialProfileModernTabs' ) ) {
				$modules['styles']['skin'][] = 'skins.cosmos.profiletabs';
			}

			if ( $this->config->get( 'CosmosSocialProfileRoundAvatar' ) ) {
				$modules['styles']['skin'][] = 'skins.cosmos.profileavatar';
			}

			if ( $this->config->get( 'CosmosSocialProfileShowEditCount' ) ) {
				$modules['styles']['skin'][] = 'skins.cosmos.profileeditcount';
			}

			if ( $this->config->get( 'CosmosSocialProfileAllowBio' ) ) {
				$modules['styles']['skin'][] = 'skins.cosmos.profilebio';
			}

			if ( $this->config->get( 'CosmosSocialProfileShowGroupTags' ) ) {
				$modules['styles']['skin'][] = 'skins.cosmos.profiletags';
			}

			if (
				$this->config->get( 'CosmosSocialProfileModernTabs' ) ||
				$this->config->get( 'CosmosSocialProfileRoundAvatar' ) ||
				$this->config->get( 'CosmosSocialProfileShowEditCount' ) ||
				$this->config->get( 'CosmosSocialProfileAllowBio' ) ||
				$this->config->get( 'CosmosSocialProfileShowGroupTags' )
			) {
				$modules['styles']['skin'][] = 'skins.cosmos.socialprofile';
			}
		}

		return $modules;
	}
}
