<?php

namespace MediaWiki\Skin\Cosmos;

use ExtensionRegistry;
use MediaWiki\MediaWikiServices;
use OutputPage;
use SkinTemplate;

class SkinCosmos extends SkinTemplate {
	/**
	 * @param array $options
	 */
	public function __construct(
		array $options = []
	) {
		if ( version_compare( MW_VERSION, '1.36', '<' ) ) {
			// Associate template - this is replaced by `template` option in 1.36
			$this->template = CosmosTemplate::class;
		}

		parent::__construct( $options );
	}

	/**
	 * @param OutputPage $out
	 */
	public function initPage( OutputPage $out ) {
		$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();

		if ( $userOptionsLookup->getBoolOption( $this->getUser(), 'cosmos-mobile-responsiveness' ) ) {
			$out->addMeta(
				'viewport',
				'width=device-width, initial-scale=1.0, ' .
				'user-scalable=yes, minimum-scale=0.25, maximum-scale=5.0'
			);
		}
	}

	/**
	 * @return array
	 */
	public function getDefaultModules() {
		$services = MediaWikiServices::getInstance();
		$config = $services->getConfigFactory()->makeConfig( 'cosmos' );

		$cosmosConfig = $services->getService( 'CosmosConfig' );

		$modules = parent::getDefaultModules();

		// CosmosRail styles
		if ( CosmosRail::railsExist( $cosmosConfig, $this->getContext() ) ) {
			$modules['styles']['skin'][] = 'skins.cosmos.rail';
		}

		// Load PortableInfobox styles
		if ( ExtensionRegistry::getInstance()->isLoaded( 'Portable Infobox' ) ) {
			$modules['styles']['skin'][] = 'skins.cosmos.portableinfobox';

			// Load PortableInfobox EuropaTheme style if the configuration is enabled
			if ( $config->get( 'CosmosEnablePortableInfoboxEuropaTheme' ) ) {
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
		if ( class_exists( 'UserProfilePage' ) ) {
			if ( $config->get( 'CosmosSocialProfileModernTabs' ) ) {
				$modules['styles']['skin'][] = 'skins.cosmos.profiletabs';
			}

			if ( $config->get( 'CosmosSocialProfileRoundAvatar' ) ) {
				$modules['styles']['skin'][] = 'skins.cosmos.profileavatar';
			}

			if ( $config->get( 'CosmosSocialProfileShowEditCount' ) ) {
				$modules['styles']['skin'][] = 'skins.cosmos.profileeditcount';
			}

			if ( $config->get( 'CosmosSocialProfileAllowBio' ) ) {
				$modules['styles']['skin'][] = 'skins.cosmos.profilebio';
			}

			if ( $config->get( 'CosmosSocialProfileShowGroupTags' ) ) {
				$modules['styles']['skin'][] = 'skins.cosmos.profiletags';
			}

			if (
				$config->get( 'CosmosSocialProfileModernTabs' ) ||
				$config->get( 'CosmosSocialProfileRoundAvatar' ) ||
				$config->get( 'CosmosSocialProfileShowEditCount' ) ||
				$config->get( 'CosmosSocialProfileAllowBio' ) ||
				$config->get( 'CosmosSocialProfileShowGroupTags' )
			) {
				$modules['styles']['skin'][] = 'skins.cosmos.socialprofile';
			}
		}

		return $modules;
	}
}
