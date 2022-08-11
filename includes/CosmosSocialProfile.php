<?php

namespace MediaWiki\Skins\Cosmos;

use Html;
use MediaWiki\MediaWikiServices;
use Sanitizer;
use TextContent;
use User;

class CosmosSocialProfile {

	/**
	 * @param User $user
	 * @return string
	 */
	public static function getUserRegistration( User $user ): string {
		return date( 'F j, Y', strtotime( $user->getRegistration() ) );
	}

	/**
	 * @param User $user
	 * @return string
	 */
	public static function getUserGroups( User $user ): string {
		$services = MediaWikiServices::getInstance();

		$config = $services->getConfigFactory()->makeConfig( 'Cosmos' );
		$userGroupManager = $services->getUserGroupManager();

		if ( $user->getBlock() ) {
			$userTags = Html::element(
				'span',
				[ 'class' => 'tag tag-blocked' ],
				wfMessage( 'cosmos-user-blocked' )->text()
			);
		} else {
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
	 * @param User $user
	 * @return string
	 */
	public static function getUserEdits( User $user ): string {
		return (string)$user->getEditCount();
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

		if ( $userBioPage && $userBioPage->isKnown() ) {
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
