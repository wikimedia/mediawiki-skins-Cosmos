<?php
namespace Cosmos;

use ALItem;
use ALRow;
use ALSection;
use ALTree;
use OutputPage;
use Skin;
use Title;

class CosmosHooks {
	public static function onGetPreferences( $user, &$preferences ) {
		$preferences['cosmos-mobile-responsiveness'] = [
			'type' => 'check',
			'label-message' => 'cosmos-mobile-preference',
			'section' => 'cosmos/cosmos-responsiveness'
		];
	}

	public static function onOutputPageBodyAttributes( OutputPage $out, Skin $skin, &$bodyAttrs ) {
		if ( $skin->getUser()->isLoggedIn() ) {
			$bodyAttrs['class'] .= ' user-logged';
		} else {
			$bodyAttrs['class'] .= ' user-anon';
		}
		if ( $out->getTitle()->equals( Title::newMainPage() ) ) {
			$bodyAttrs['class'] .= ' mainpage';
		}
		return true;
	}

	/**
	 * Implements AdminLinks hook from Extension:Admin_Links.
	 *
	 * @param ALTree &$adminLinksTree
	 * @return bool
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

		return true;
	}
}
