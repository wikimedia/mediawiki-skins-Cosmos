<?php
/**
 * SkinTemplate class for the Cosmos skin
 *
 * @ingroup Skins
 */

use MediaWiki\Skin\Cosmos\Config;

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
		$config = new Config();
		if ( $this->getSkin()
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
		if ( ( wfMessage( 'cosmos-customsidebar' )
			->text() !== '-' && wfMessage( 'cosmos-customsidebar' )
			->text() !== '' && wfMessage( 'cosmos-customsidebar' )
			->exists() ) || ( wfMessage( 'cosmos-stickysidebar' )
			->text() !== '-' && wfMessage( 'cosmos-stickysidebar' )
			->text() !== '' && wfMessage( 'cosmos-stickysidebar' )
			->exists() ) ) {
				$out->addModuleStyles( [
					'skins.cosmos.rail',
				] );
		}

		if ( ExtensionRegistry::getInstance()
			->isLoaded( 'Portable Infobox' ) ) {
			$out->addModuleStyles( [
				'skins.cosmos.portableinfobox',
			] );
			if ( $config->isEnabled( 'europa-theme' ) ) {
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
			if ( $config->isEnabled( 'modern-tabs' ) ) {
				$out->addModuleStyles( [
					'skins.cosmos.profiletabs',
				] );
			}
			if ( $config->isEnabled( 'round-avatar' ) ) {
				$out->addModuleStyles( [
					'skins.cosmos.profileavatar',
				] );
			}
			if ( $config->isEnabled( 'show-editcount' ) ) {
				$out->addModuleStyles( [
					'skins.cosmos.profileeditcount',
				] );
			}
			if ( $config->isEnabled( 'allow-bio' ) ) {
				$out->addModuleStyles( [
					'skins.cosmos.profilebio',
				] );
			}
			if ( $config->isEnabled( 'profile-tags' ) ) {
				$out->addModuleStyles( [
					'skins.cosmos.profiletags',
				] );
			}
			if ( $config->isEnabled( 'modern-tabs' ) ||
				$config->isEnabled( 'round-avatar' ) ||
				$config->isEnabled( 'show-editcount' ) ||
				$config->isEnabled( 'allow-bio' ) ||
				$config->isEnabled( 'profile-tags' ) ) {
				$out->addModuleStyles( [
					'skins.cosmos.socialprofile',
				] );
			}
		}
	}
}
