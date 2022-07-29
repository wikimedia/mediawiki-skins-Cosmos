<?php

namespace MediaWiki\Skins\Cosmos;

use Html;
use MediaWiki\MediaWikiServices;
use Sanitizer;
use TextContent;
use User;

class CosmosSocialProfile {

	/**
	 * @param string $user
	 * @return ?User
	 */
	private static function getUser( string $user ): ?User {
		$services = MediaWikiServices::getInstance();

		$titleFactory = $services->getTitleFactory();
		$title = $titleFactory->newFromText( $user );

		if (
			is_object( $title ) &&
			( $title->getNamespace() == NS_USER || $title->getNamespace() == NS_USER_PROFILE ) &&
			!$title->isSubpage()
		) {
			$user = $title->getText();
		}

		$userFactory = $services->getUserFactory();
		$user = $userFactory->newFromName( $user );

		return $user;
	}

	/**
	 * @param string $user
	 * @return ?string
	 */
	public static function getUserRegistration( string $user ): ?string {
		$user = self::getUser( $user );

		if ( $user ) {
			return date( 'F j, Y', strtotime( $user->getRegistration() ) );
		}

		return null;
	}

	/**
	 * @param string $user
	 * @return ?string
	 */
	public static function getUserGroups( string $user ): ?string {
		$services = MediaWikiServices::getInstance();

		$config = $services->getConfigFactory()->makeConfig( 'Cosmos' );
		$userGroupManager = $services->getUserGroupManager();

		$user = self::getUser( $user );
		$userTags = null;

		if ( $user && $user->getBlock() ) {
			$userTags = Html::element(
				'span',
				[ 'class' => 'tag tag-blocked' ],
				wfMessage( 'cosmos-user-blocked' )->text()
			);
		} elseif ( $user ) {
			$numberOfTags = 0;
			$userTags = '';

			foreach ( $config->get( 'CosmosSocialProfileTagGroups' ) as $value ) {
				if ( in_array( $value, $userGroupManager->getUserGroups( $user ) ) ) {
					$numberOfTags++;
					$numberOfTagsConfig = $config->get( 'CosmosSocialProfileNumberofGroupTags' );
					$userGroupMessage = wfMessage( "group-{$value}-member" );

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
	 * @param string $user
	 * @return ?int
	 */
	public static function getUserEdits( string $user ): ?int {
		$user = self::getUser( $user );

		if ( $user ) {
			return $user->getEditCount();
		}

		return null;
	}

	/**
	 * @param string $user
	 * @param bool $followRedirects
	 * @return ?string
	 */
	public static function getUserBio(
		string $user,
		bool $followRedirects
	): ?string {
		$services = MediaWikiServices::getInstance();
		$titleFactory = $services->getTitleFactory();

		$userBioPage = $titleFactory->newFromText( "User:{$user}" )
			->getSubpage( 'bio' );

		if ( $user && $userBioPage && $userBioPage->isKnown() ) {
			$wikiPageFactory = $services->getWikiPageFactory();
			$wikiPage = $wikiPageFactory->newFromTitle( $userBioPage );

			$content = $wikiPage->getContent();

			// experimental
			if (
				$followRedirects &&
				$userBioPage->isRedirect() &&
				$content->getRedirectTarget()->isKnown() &&
				$content->getRedirectTarget()->inNamespace( NS_USER )
			) {
				$userBioPage = $content->getRedirectTarget();

				$wikiPage = $wikiPageFactory->newFromTitle( $userBioPage );

				$content = $wikiPage->getContent();
			}

			return $content instanceof TextContent
				? Html::element( 'p', [ 'class' => 'bio' ], $content->getText() )
				: null;
		}

		return null;
	}
}
