<?php

namespace MediaWiki\Skin\Cosmos;

use ALItem;
use ALRow;
use ALSection;
use ALTree;
use MediaWiki\Hook\GetDoubleUnderscoreIDsHook;
use MediaWiki\Hook\OutputPageBodyAttributesHook;
use MediaWiki\Hook\OutputPageParserOutputHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\Preferences\Hook\GetPreferencesHook;
use OutputPage;
use Parser;
use ParserOutput;
use Sanitizer;
use Skin;
use User;

class CosmosHooks implements
	GetDoubleUnderscoreIDsHook,
	GetPreferencesHook,
	OutputPageBodyAttributesHook,
	OutputPageParserOutputHook
{
	/**
	 * @see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Hooks/GetDoubleUnderscoreIDs
	 * @param array &$doubleUnderscoreIDs
	 */
	public function onGetDoubleUnderscoreIDs( &$doubleUnderscoreIDs ) {
		$doubleUnderscoreIDs[] = 'norail';
	}

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

		if ( $out->getProperty( 'additionalBodyClass' ) ) {
			$property = $out->getProperty( 'additionalBodyClass' );
			$bodyAttrs['class'] .= ' ' . Sanitizer::escapeClass( $property );
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Hooks/OutputPageParserOutput
	 * @param OutputPage $out
	 * @param ParserOutput $parserOutput
	 */
	public function onOutputPageParserOutput( $out, $parserOutput ) : void {
		if ( $parserOutput->getProperty( 'norail' ) !== false ) {
			$cosmosConfig = MediaWikiServices::getInstance()->getService( 'CosmosConfig' );

			$cosmosConfig->setConfig( 'wgCosmosRailBlacklistedPages', [ '{$CURRENTPAGE}' ] );
		}

		if ( $parserOutput->getProperty( 'additionalBodyClass' ) ) {
			$parserProperty = $parserOutput->getProperty( 'additionalBodyClass' );
			$out->setProperty( 'additionalBodyClass', $parserProperty );
		}
	}

	/**
	 * @param Parser $parser
	 */
	public function onParserFirstCallInit( Parser $parser ) {
		$parser->setFunctionHook(
			'additionalbodyclass', [ __CLASS__, 'setAdditionalBodyClass' ]
		);
	}

	/**
	 * @param Parser $parser
	 * @param string $newClass
	 */
	public static function setAdditionalBodyClass( Parser $parser, string $newClass ) {
		$parser->getOutput()->setProperty( 'additionalBodyClass', $newClass );
	}

	/**
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
