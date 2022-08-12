<?php

namespace MediaWiki\Skins\Cosmos\Hooks\Handlers;

use Config;
use ConfigFactory;
use Html;
use MediaWiki\Skins\Cosmos\CosmosSocialProfile;
use SpecialPage;
use UserProfilePage;

class SocialProfileHookHandler {

	/** @var Config */
	private $config;

	/**
	 * @param ConfigFactory $configFactory
	 */
	public function __construct( ConfigFactory $configFactory ) {
		$this->config = $configFactory->makeConfig( 'Cosmos' );
	}

	/**
	 * Set up Cosmos-specific SocialProfile elements
	 *
	 * @param UserProfilePage $userProfilePage
	 * @param string &$profileTitle
	 */
	public function onUserProfileGetProfileTitle(
		UserProfilePage $userProfilePage,
		string &$profileTitle
	) {
		if (
			$this->config->get( 'CosmosSocialProfileShowGroupTags' ) ||
			$this->config->get( 'CosmosSocialProfileShowEditCount' ) ||
			$this->config->get( 'CosmosSocialProfileAllowBio' )
		) {
			$profileOwner = $userProfilePage->profileOwner;

			$groupTags = $this->config->get( 'CosmosSocialProfileShowGroupTags' )
				? CosmosSocialProfile::getUserGroups( $profileOwner )
				: null;

			$editCount = null;
			if ( $this->config->get( 'CosmosSocialProfileShowEditCount' ) ) {
				$contribsUrl = SpecialPage::getTitleFor(
					'Contributions', $profileOwner->getName()
				)->getFullURL();

				$editCount = Html::rawElement( 'div', [
					'class' => [ 'contributions-details', 'tally' ]
				], Html::rawElement( 'a', [
					'href' => $contribsUrl
				], Html::rawElement( 'em', [],
					CosmosSocialProfile::getUserEdits( $profileOwner )
				) .
				Html::rawElement( 'span', [],
					$userProfilePage->getContext()->msg( 'cosmos-editcount-label' )->escaped() .
					Html::closeElement( 'br' ) .
					CosmosSocialProfile::getUserRegistration( $profileOwner )
				) ) );
			}

			// experimental
			$followBioRedirects = $this->config->get( 'CosmosSocialProfileFollowBioRedirects' );

			$bio = $this->config->get( 'CosmosSocialProfileAllowBio' )
				? CosmosSocialProfile::getUserBio( $profileOwner->getName(), $followBioRedirects )
				: null;

			$profileTitle = Html::rawElement( 'div', [ 'class' => 'hgroup' ],
				Html::element( 'h1', [ 'itemprop' => 'name' ], $profileOwner->getName() ) .
				$groupTags
			) . $editCount . $bio;
		}
	}
}
