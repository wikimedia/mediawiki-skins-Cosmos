<?php
/**
 * SkinTemplate class for the Cosmos skin
 *
 * @ingroup Skins
 */
namespace MediaWiki\Skin\Cosmos;

use ExtensionRegistry;
use MediaWiki\MediaWikiServices;
use OutputPage;
use SkinTemplate;

class SkinCosmos extends SkinTemplate {
	/** @var string */
	public $template = CosmosTemplate::class;

	/**
	 * @param OutputPage $out
	 */
	public function initPage( OutputPage $out ) {
		$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();

		if ( $userOptionsLookup->getOption( $this->getSkin()->getUser(), 'cosmos-mobile-responsiveness' ) == 1 ) {
			$out->addMeta(
				'viewport',
				'width=device-width, initial-scale=1.0, ' .
				'user-scalable=yes, minimum-scale=0.25, maximum-scale=5.0'
			);
		}
	}

	/**
	 * @return $modules
	 */
	public function getDefaultModules() {
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'cosmos' );

		$skin = $this->getSkin();

		$modules = parent::getDefaultModules();

		// CosmosRail styles
		if ( !$skin->msg( 'cosmos-customsidebar' )->isDisabled() || !$skin->msg( 'cosmos-stickysidebar' )->isDisabled() ) {
			$modules['styles']['skin'][] = 'skins.cosmos.rail';
		}

		// Load PortableInfobox styles
		if ( ExtensionRegistry::getInstance()
			->isLoaded( 'Portable Infobox' ) ) {
			$modules['styles']['skin'][] = 'skins.cosmos.portableinfobox';

			// Load PortableInfobox EuropaTheme style if the configuration is enabled
			if ( $config->get( 'CosmosEnablePortableInfoboxEuropaTheme' ) ) {
				$modules['styles']['skin'][] = 'skins.cosmos.portableinfobox.europa';
			} else {
				$modules['styles']['skin'][] = 'skins.cosmos.portableinfobox.default';
			}
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

			if ( $config->get( 'CosmosSocialProfileModernTabs' ) ||
				$config->get( 'CosmosSocialProfileRoundAvatar' ) ||
				$config->get( 'CosmosSocialProfileShowEditCount' ) ||
				$config->get( 'CosmosSocialProfileAllowBio' ) ||
				$config->get( 'CosmosSocialProfileShowGroupTags' ) ) {
				$modules['styles']['skin'][] = 'skins.cosmos.socialprofile';
			}
		}

		return $modules;
	}
}
