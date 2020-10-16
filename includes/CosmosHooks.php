<?php
namespace MediaWiki\Skin\Cosmos;

use ALItem;
use ALRow;
use ALSection;
use ALTree;
use OutputPage;
use Skin;
use User;

class CosmosHooks {
	/**
	 * @see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Hooks/GetPreferences
	 * @param User $user
	 * @param array &$preferences
	 */
	public static function onGetPreferences( $user, &$preferences ) {
		$preferences['cosmos-mobile-responsiveness'] = [
			'type' => 'check',
			'help-message' => 'cosmos-mobile-preference',
			'label-message' => 'prefs-cosmos-responsiveness',
			'section' => 'rendering/skin/skin-prefs',
		];
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Hooks/OutputPageBodyAttributes
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @param array &$bodyAttrs
	 */
	public static function onOutputPageBodyAttributes( OutputPage $out, Skin $skin, &$bodyAttrs ) {
		if ( $skin->getUser()->isLoggedIn() ) {
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
		$cosmos_section = new ALSection( wfMessage( 'skinname-cosmos' )->text(), wfMessage( 'adminlinks_users' )->text() );
		$cosmos_row = new ALRow( 'cosmos' );
		$cosmos_row->addItem( ALItem::newFromEditLink( 'Cosmos-navigation', 'Edit navigation' ) );
		$cosmos_row->addItem( ALItem::newFromEditLink( 'Cosmos-toolbar', 'Edit toolbar' ) );
		$cosmos_row->addItem( ALItem::newFromEditLink( 'Cosmos-tagline', 'Edit tagline' ) );
		$cosmos_row->addItem( ALItem::newFromEditLink( 'Cosmos-customsidebar', 'Edit custom sidebar' ) );
		$cosmos_row->addItem( ALItem::newFromEditLink( 'Cosmos-stickysidebar', 'Edit custom sticky sidebar' ) );
		$cosmos_section->addRow( $cosmos_row );
		$adminLinksTree->addSection( $cosmos_section );
	}
}
