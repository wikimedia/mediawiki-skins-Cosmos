<?php
/**
 * SkinTemplate class for the Cosmos skin
 *
 * @ingroup Skins
 */

use MediaWiki\MediaWikiServices;

class SkinCosmos extends SkinTemplate {
	/** @var string */
	public $skinname = 'cosmos';

	/** @var string */
	public $stylename = 'Cosmos';

	/** @var string */
	public $template = 'MediaWiki\\Skin\\Cosmos\\CosmosTemplate';

	/**
	 * @param OutputPage $out
	 */
	public function initPage( OutputPage $out ) {
		parent::initPage( $out );
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'cosmos' );
		$skin = $this->getSkin();
		if ( $skin
			->getUser()
			->getOption( 'cosmos-mobile-responsiveness' ) == 1 ) {
			$out->addMeta(
				'viewport',
				'width=device-width, initial-scale=1.0, ' .
				'user-scalable=yes, minimum-scale=0.25, maximum-scale=5.0'
			);
		}
		parent::setupSkinUserCss( $out );
		$out->addModuleStyles( [
			'mediawiki.skinning.content.externallinks',
			'skins.cosmos',
			'skins.cosmos.legacy'
		] );

		$out->addModules( [
			'skins.cosmos.js',
			'skins.cosmos.mobile'
		] );
		if (
			!$skin->msg( 'cosmos-customsidebar' )->isDisabled() ||
			!$skin->msg( 'cosmos-stickysidebar' )->isDisabled()
		) {
			$out->addModuleStyles( [
				'skins.cosmos.rail',
			] );
		}

		if ( ExtensionRegistry::getInstance()
			->isLoaded( 'Portable Infobox' ) ) {
			$out->addModuleStyles( [
				'skins.cosmos.portableinfobox',
			] );
			if ( $config->get( 'CosmosEnablePortableInfoboxEuropaTheme' ) ) {
				$out->addModuleStyles( [
					'skins.cosmos.portableinfobox.europa',
				] );
			} else {
				$out->addModuleStyles( [
					'skins.cosmos.portableinfobox.default',
				] );
			}
		}
		// Load SocialProfile styles if the respective configuration variables are enabled
		if ( class_exists( 'UserProfilePage' ) ) {
			if ( $config->get( 'CosmosSocialProfileModernTabs' ) ) {
				$out->addModuleStyles( [
					'skins.cosmos.profiletabs',
				] );
			}
			if ( $config->get( 'CosmosSocialProfileRoundAvatar' ) ) {
				$out->addModuleStyles( [
					'skins.cosmos.profileavatar',
				] );
			}
			if ( $config->get( 'CosmosSocialProfileShowEditCount' ) ) {
				$out->addModuleStyles( [
					'skins.cosmos.profileeditcount',
				] );
			}
			if ( $config->get( 'CosmosSocialProfileAllowBio' ) ) {
				$out->addModuleStyles( [
					'skins.cosmos.profilebio',
				] );
			}
			if ( $config->get( 'CosmosSocialProfileShowGroupTags' ) ) {
				$out->addModuleStyles( [
					'skins.cosmos.profiletags',
				] );
			}
			if ( $config->get( 'CosmosSocialProfileModernTabs' ) ||
				$config->get( 'CosmosSocialProfileRoundAvatar' ) ||
				$config->get( 'CosmosSocialProfileShowEditCount' ) ||
				$config->get( 'CosmosSocialProfileAllowBio' ) ||
				$config->get( 'CosmosSocialProfileShowGroupTags' ) ) {
				$out->addModuleStyles( [
					'skins.cosmos.socialprofile',
				] );
			}
		}
	}
}
