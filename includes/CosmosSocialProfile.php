<?php
namespace MediaWiki\Skin\Cosmos;

use ExtensionRegistry;
use Html;
use Sanitizer;
use Title;
use User;

class CosmosSocialProfile {

	/**
	 * @param string $user
	 * @return User|false
	 */
	private static function getUser( $user ) {
		$title = Title::newFromText( $user );
		if ( is_object( $title ) && ( $title->getNamespace() == NS_USER || $title->getNamespace() == NS_USER_PROFILE ) && !$title->isSubpage() ) {
			$user = $title->getText();
		}
		$user = User::newFromName( $user );
		return $user;
	}

	/**
	 * @param string $user
	 * @return string|false|null
	 */
	public static function getUserRegistration( $user ) {
		$user = self::getUser( $user );
		if ( $user ) {
			return date( 'F j, Y', strtotime( $user->getRegistration() ) );
		}
	}

	/**
	 * @param string $user
	 * @return string
	 */
	public static function getUserGroups( $user ) {
		$config = new Config();
		$user = self::getUser( $user );
		if ( $user && $user->isBlocked() ) {
			$usertags = Html::rawElement( 'span', [ 'class' => 'tag tag-blocked' ], wfMessage( 'cosmos-user-blocked' ) );
		} elseif ( $user ) {
			$number_of_tags = 0;
			$usertags = '';
			foreach ( $config->getArray( 'group-tags' ) as $value ) {
				if ( in_array( $value, $user->getGroups() ) ) {
					$number_of_tags++;
					if ( ExtensionRegistry::getInstance()->isLoaded( 'ManageWiki' ) ) {
						global $wgCosmosNumberofGroupTags;
						$number_of_tags_config = $wgCosmosNumberofGroupTags;
					} else {
						$number_of_tags_config = $config->getInteger( 'number-of-tags' );
					}
					if ( $number_of_tags <= $number_of_tags_config ) {
						$usertags .= Html::rawElement( 'span', [ 'class' => 'tag tag-' . Sanitizer::escapeClass( $value ) ], ucfirst( wfMessage( 'group-' . $value . '-member' ) ) );
					}
				}
			}
		}
		return $usertags;
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
	 * @todo Seems like a method that is a work in progress...
	 * @param string $user
	 * @return void
	 */
	public static function getUserBio( $user ) {
		if ( $user ) {
			// return '<p class="bio">' . $parser->recursiveTagParse( '{{:User:' . $user . '/bio}}') . '</p>';

		}
	}
}
