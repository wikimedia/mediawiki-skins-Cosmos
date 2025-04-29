<?php

namespace MediaWiki\Skins\Cosmos;

use Config;
use ConfigFactory;
use CookieWarning\Decisions as CookieWarningDecisions;
use ExtensionRegistry;
use Language;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\SpecialPage\SpecialPageFactory;
use MediaWiki\User\Options\UserOptionsManager;
use SkinTemplate;
use TitleFactory;
use UserProfilePage;

class SkinCosmos extends SkinTemplate {

	/** @var Config */
	public $config;

	/** @var CookieWarningDecisions|null */
	public $cookieWarningDecisions;

	/** @var Language */
	public $contentLanguage;

	/** @var CosmosRailBuilder */
	public $cosmosRailBuilder;

	/** @var LanguageNameUtils */
	public $languageNameUtils;

	/** @var PermissionManager */
	public $permissionManager;

	/** @var SpecialPageFactory */
	public $specialPageFactory;

	/** @var TitleFactory */
	public $titleFactory;

	/** @var UserOptionsManager */
	public $userOptionsManager;

	/** @var CosmosWordmarkLookup */
	public $wordmarkLookup;

	/**
	 * @param ConfigFactory $configFactory
	 * @param Language $contentLanguage
	 * @param CosmosRailBuilder $cosmosRailBuilder
	 * @param CosmosWordmarkLookup $cosmosWordmarkLookup
	 * @param LanguageNameUtils $languageNameUtils
	 * @param PermissionManager $permissionManager
	 * @param SpecialPageFactory $specialPageFactory
	 * @param TitleFactory $titleFactory
	 * @param UserOptionsManager $userOptionsManager
	 * @param ?CookieWarningDecisions $cookieWarningDecisions
	 * @param array $options
	 */
	public function __construct(
		ConfigFactory $configFactory,
		Language $contentLanguage,
		CosmosRailBuilder $cosmosRailBuilder,
		CosmosWordmarkLookup $cosmosWordmarkLookup,
		LanguageNameUtils $languageNameUtils,
		PermissionManager $permissionManager,
		SpecialPageFactory $specialPageFactory,
		TitleFactory $titleFactory,
		UserOptionsManager $userOptionsManager,
		?CookieWarningDecisions $cookieWarningDecisions,
		array $options
	) {
		parent::__construct( $options );

		$this->config = $configFactory->makeConfig( 'Cosmos' );
		$this->contentLanguage = $contentLanguage;
		$this->cosmosRailBuilder = $cosmosRailBuilder;
		$this->languageNameUtils = $languageNameUtils;
		$this->permissionManager = $permissionManager;
		$this->specialPageFactory = $specialPageFactory;
		$this->titleFactory = $titleFactory;
		$this->userOptionsManager = $userOptionsManager;
		$this->wordmarkLookup = $cosmosWordmarkLookup;

		$this->cookieWarningDecisions = $cookieWarningDecisions;
	}

	/**
	 * @return array
	 */
	public function getDefaultModules() {
		$modules = parent::getDefaultModules();

		// CosmosRail styles
		if (
			!$this->cosmosRailBuilder->isHidden() &&
			$this->cosmosRailBuilder->hasModules()
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

		if ( ExtensionRegistry::getInstance()->isLoaded( 'CodeEditor' ) ) {
			$modules['styles']['skin'][] = 'skins.cosmos.codeeditor';
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
