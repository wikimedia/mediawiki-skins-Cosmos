<?php

namespace MediaWiki\Skin\Cosmos;

use Html;
use MediaWiki\MediaWikiServices;
use Sanitizer;
use TextContent;
use Title;
use User;
use WikiPage;

class CosmosSocialProfile {
	/**
	 * @param string $user
	 * @return User|false
	 */
	private static function getUser( $user ) {
		$title = Title::newFromText( $user );

		if (
			is_object( $title ) &&
			( $title->getNamespace() == NS_USER || $title->getNamespace() == NS_USER_PROFILE ) &&
			!$title->isSubpage()
		) {
			$user = $title->getText();
		}

		$user = User::newFromName( $user );

		return $user;
	}

	/**
	 * @param string $user
	 * @return string|null
	 */
	public static function getUserRegistration( $user ) {
		$user = self::getUser( $user );

		if ( $user ) {
			return date( 'F j, Y', strtotime( $user->getRegistration() ) );
		}
	}

	/**
	 * @param string $user
	 * @return string|null
	 */
	public static function getUserGroups( $user ) {
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'cosmos' );
		$user = self::getUser( $user );

		if ( $user && $user->isBlocked() ) {
			$userTags = Html::element(
				'span',
				[ 'class' => 'tag tag-blocked' ],
				wfMessage( 'cosmos-user-blocked' )->text()
			);
		} elseif ( $user ) {
			$numberOfTags = 0;
			$userTags = '';

			foreach ( $config->get( 'CosmosProfileTagGroups' ) as $value ) {
				if ( in_array( $value, $user->getGroups() ) ) {
					$numberOfTags++;
					$numberOfTagsConfig = $config->get( 'CosmosNumberofGroupTags' );
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
		} else {
			$userTags = null;
		}

		return $userTags;
	}

	/**
	 * @param string $user
	 * @return int|null
	 */
	public static function getUserEdits( $user ) {
		$user = self::getUser( $user );

		if ( $user ) {
			return $user->getEditCount();
		}
	}

	/**
	 * @param string $user
	 * @param bool $followRedirects
	 * @return string|null
	 */
	public static function getUserBio( $user, $followRedirects ) {
		if ( $user && Title::newFromText( "User:{$user}/bio" )->isKnown() ) {
			$userBioPage = Title::newFromText( "User:{$user}/bio" );

			$wikiPage = new WikiPage( $userBioPage );

			$content = $wikiPage->getContent();

			// experimental
			if (
				$followRedirects &&
				$userBioPage->isRedirect() &&
				$content->getRedirectTarget()->isKnown() &&
				$content->getRedirectTarget()->inNamespace( NS_USER )
			) {
				$userBioPage = $content->getRedirectTarget();

				$wikiPage = new WikiPage( $userBioPage );

				$content = $wikiPage->getContent();
			}

			return $content instanceof TextContent
				? Html::element( 'p', [ 'class' => 'bio' ], $content->getText() )
				: null;
		}
	}
}
