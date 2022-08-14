<?php

namespace MediaWiki\Skins\Cosmos\Hooks\Handlers;

use Config;
use ConfigFactory;
use Html;
use IContextSource;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\User\UserGroupManager;
use Sanitizer;
use SpecialPage;
use TextContent;
use TitleFactory;
use User;
use UserProfilePage;

class SocialProfileHookHandler {

	/** @var Config */
	private $config;

	/** @var IContextSource */
	private $context;

	/** @var User */
	private $profileOwner;

	/** @var TitleFactory */
	private $titleFactory;

	/** @var UserGroupManager */
	private $userGroupManager;

	/** @var WikiPageFactory */
	private $wikiPageFactory;

	/**
	 * @param ConfigFactory $configFactory
	 * @param TitleFactory $titleFactory
	 * @param UserGroupManager $userGroupManager
	 * @param WikiPageFactory $wikiPageFactory
	 */
	public function __construct(
		ConfigFactory $configFactory,
		TitleFactory $titleFactory,
		UserGroupManager $userGroupManager,
		WikiPageFactory $wikiPageFactory
	) {
		$this->config = $configFactory->makeConfig( 'Cosmos' );

		$this->titleFactory = $titleFactory;
		$this->userGroupManager = $userGroupManager;
		$this->wikiPageFactory = $wikiPageFactory;
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
			$this->context = $userProfilePage->getContext();
			$this->profileOwner = $userProfilePage->profileOwner;

			$groupTags = $this->config->get( 'CosmosSocialProfileShowGroupTags' )
				? $this->getUserGroupTags()
				: null;

			$editCount = null;
			if ( $this->config->get( 'CosmosSocialProfileShowEditCount' ) ) {
				$contribsUrl = SpecialPage::getTitleFor(
					'Contributions', $this->profileOwner->getName()
				)->getFullURL();

				$editCount = Html::rawElement( 'div', [
					'class' => [ 'contributions-details', 'tally' ]
				], Html::rawElement( 'a', [
					'href' => $contribsUrl
				], Html::rawElement( 'em', [],
					$this->getUserEdits()
				) .
				Html::rawElement( 'span', [],
					$this->context->msg( 'cosmos-editcount-label' )->escaped() .
					Html::closeElement( 'br' ) .
					$this->getUserRegistration()
				) ) );
			}

			// experimental
			$followBioRedirects = $this->config->get( 'CosmosSocialProfileFollowBioRedirects' );

			$bio = $this->config->get( 'CosmosSocialProfileAllowBio' )
				? $this->getUserBio()
				: null;

			$profileTitle = Html::rawElement( 'div', [ 'class' => 'hgroup' ],
				Html::element( 'h1', [ 'itemprop' => 'name' ], $this->profileOwner->getName() ) .
				$groupTags
			) . $editCount . $bio;
		}
	}

	/**
	 * @return string
	 */
	private function getUserRegistration(): string {
		return date( 'F j, Y', strtotime( $this->profileOwner->getRegistration() ) );
	}

	/**
	 * @return string
	 */
	private function getUserGroupTags(): string {
		if ( $this->profileOwner->getBlock() ) {
			$userTags = Html::element(
				'span',
				[ 'class' => 'tag tag-blocked' ],
				$this->context->msg( 'cosmos-user-blocked' )->text()
			);
		} else {
			$numberOfTags = 0;
			$userTags = '';

			foreach ( $this->config->get( 'CosmosSocialProfileTagGroups' ) as $value ) {
				if ( in_array( $value, $this->userGroupManager->getUserGroups( $this->profileOwner ) ) ) {
					$numberOfTags++;
					$numberOfTagsConfig = $this->config->get( 'CosmosSocialProfileNumberofGroupTags' );
					$userGroupMessage = $this->context->msg( "group-{$value}-member" );

					if ( $numberOfTags <= $numberOfTagsConfig ) {
						$userTags .= Html::element(
							'span',
							[ 'class' => 'tag tag-' . Sanitizer::escapeClass( $value ) ],
							ucfirst( ( !$userGroupMessage->isDisabled() ? $userGroupMessage->text() : $value ) )
						);
					}
				}
			}
		}

		return $userTags;
	}

	/**
	 * @return string
	 */
	private function getUserEdits(): string {
		return (string)$this->profileOwner->getEditCount();
	}

	/**
	 * @return ?string
	 */
	private function getUserBio(): ?string {
		$userBioPage = $this->titleFactory->newFromText( $this->profileOwner->getName(), NS_USER )
			->getSubpage( 'bio' );

		if ( $userBioPage && $userBioPage->isKnown() ) {
			$followRedirects = $this->config->get( 'CosmosSocialProfileFollowBioRedirects' );
			$wikiPage = $this->wikiPageFactory->newFromTitle( $userBioPage );
			$content = $wikiPage->getContent();

			// experimental
			if (
				$followRedirects &&
				$userBioPage->isRedirect() &&
				$content->getRedirectTarget()->isKnown() &&
				$content->getRedirectTarget()->inNamespace( NS_USER )
			) {
				$userBioPage = $content->getRedirectTarget();

				$wikiPage = $this->wikiPageFactory->newFromTitle( $userBioPage );

				$content = $wikiPage->getContent();
			}

			return $content instanceof TextContent
				? Html::element( 'p', [ 'class' => 'bio' ], $content->getText() )
				: null;
		}

		return null;
	}
}
