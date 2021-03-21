<?php

namespace MediaWiki\Skin\Cosmos;

use ALItem;
use ALRow;
use ALSection;
use ALTree;
use MediaWiki\Hook\OutputPageBodyAttributesHook;
use MediaWiki\Preferences\Hook\GetPreferencesHook;
use OutputPage;
use Skin;
use User;

class CosmosHooks implements
	GetPreferencesHook,
	OutputPageBodyAttributesHook
{
	/**
	 * @see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Hooks/GetPreferences
	 * @param User $user
	 * @param array &$preferences
	 */
	public function onGetPreferences( $user, &$preferences ) {
		$preferences['cosmos-mobile-responsiveness'] = [
			'type' => 'check',
			'help-message' => 'cosmos-mobile-preference',
			'label-message' => 'prefs-cosmos-responsiveness',
			'section' => 'rendering/skin/skin-prefs',
			'hide-if' => [ '!==', 'wpskin', 'cosmos' ],
		];
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Hooks/OutputPageBodyAttributes
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @param array &$bodyAttrs
	 */
	public function onOutputPageBodyAttributes( $out, $skin, &$bodyAttrs ) : void {
		if ( $skin->getUser()->isRegistered() ) {
			$bodyAttrs['class'] .= ' user-logged';
		} else {
			$bodyAttrs['class'] .= ' user-anon';
		}

		if ( $out->getTitle()->isMainPage() ) {
			$bodyAttrs['class'] .= ' mainpage';
		}
	}

	/**
	 * Implements AdminLinks hook from Extension:Admin_Links.
	 *
	 * @see https://www.mediawiki.org/wiki/Special:MyLanguage/Extension:Admin_Links/Hooks/AdminLinks
	 * @param ALTree &$adminLinksTree
	 */
	public static function addToAdminLinks( ALTree &$adminLinksTree ) {
		$cosmos_section = new ALSection( wfMessage( 'skinname-cosmos' )->text() );
		$cosmos_row = new ALRow( 'cosmos' );
		$cosmos_row->addItem( ALItem::newFromEditLink( 'Cosmos-navigation', 'Edit navigation' ) );
		$cosmos_row->addItem( ALItem::newFromEditLink( 'Cosmos-tagline', 'Edit tagline' ) );
		$cosmos_section->addRow( $cosmos_row );
		$adminLinksTree->addSection( $cosmos_section, wfMessage( 'adminlinks_users' )->text() );
	}
}
