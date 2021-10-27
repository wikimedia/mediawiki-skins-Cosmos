<?php

namespace MediaWiki\Skin\Cosmos;

use ALItem;
use ALRow;
use ALSection;
use ALTree;
use Config;
use MediaWiki\Hook\GetDoubleUnderscoreIDsHook;
use MediaWiki\Hook\OutputPageBodyAttributesHook;
use MediaWiki\Hook\OutputPageParserOutputHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\Preferences\Hook\GetPreferencesHook;
use ObjectCache;
use OutputPage;
use Parser;
use ParserOutput;
use ResourceLoaderContext;
use Sanitizer;
use Skin;
use Title;
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
	public function onOutputPageBodyAttributes( $out, $skin, &$bodyAttrs ): void {
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'Cosmos' );

		if ( $skin->getUser()->isRegistered() ) {
			$bodyAttrs['class'] .= ' user-logged';
		} else {
			$bodyAttrs['class'] .= ' user-anon';
		}

		if ( LessUtil::isThemeDark( 'content-background-color' ) ) {
			$bodyAttrs['class'] .= ' theme-dark';
		} else {
			$bodyAttrs['class'] .= ' theme-light';
		}

		if ( $out->getTitle()->isMainPage() ) {
			$bodyAttrs['class'] .= ' mainpage';
		}

		if ( $out->getProperty( 'additionalBodyClass' ) ) {
			$property = $out->getProperty( 'additionalBodyClass' );
			$bodyAttrs['class'] .= ' ' . Sanitizer::escapeClass( $property );
		}

		if ( $config->get( 'CosmosUseWVUISearch' ) ) {
			$bodyAttrs['class'] .= ' skin-cosmos-search-vue';
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Hooks/OutputPageParserOutput
	 * @param OutputPage $out
	 * @param ParserOutput $parserOutput
	 */
	public function onOutputPageParserOutput( $out, $parserOutput ): void {
		if ( version_compare( MW_VERSION, '1.38', '<' ) ) {
			if ( $parserOutput->getProperty( 'norail' ) !== false ) {
				$out->setProperty( 'norail', true );
			}

			if ( $parserOutput->getProperty( 'additionalBodyClass' ) ) {
				$parserProperty = $parserOutput->getProperty( 'additionalBodyClass' );
				$out->setProperty( 'additionalBodyClass', $parserProperty );
			}
		} else {
			if ( $parserOutput->getPageProperty( 'norail' ) !== false ) {
				$out->setProperty( 'norail', true );
			}

			if ( $parserOutput->getPageProperty( 'additionalBodyClass' ) ) {
				$parserProperty = $parserOutput->getPageProperty( 'additionalBodyClass' );
				$out->setProperty( 'additionalBodyClass', $parserProperty );
			}
		}
	}

	/**
	 * @param Title $title
	 * @param string $text
	 */
	public function onMessageCacheReplace( $title, $text ) {
		$memc = ObjectCache::getLocalClusterInstance();

		$memc->delete( $memc->makeKey( 'mCosmosNavigation', 'cosmosNavigation' ) );
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
		if ( version_compare( MW_VERSION, '1.38', '<' ) ) {
			$parser->getOutput()->setProperty( 'additionalBodyClass', $newClass );
		} else {
			$parser->getOutput()->setPageProperty( 'additionalBodyClass', $newClass );
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Special:MyLanguage/Extension:Admin_Links/Hooks/AdminLinks
	 * @param ALTree &$adminLinksTree
	 */
	public static function addToAdminLinks( ALTree &$adminLinksTree ) {
		$cosmosSection = new ALSection( wfMessage( 'skinname-cosmos' )->text() );
		$cosmosRow = new ALRow( 'cosmos' );

		$cosmosRow->addItem(
			ALItem::newFromEditLink(
				'Cosmos-navigation',
				wfMessage( 'cosmos-adminlinks-edit-navigation' )->text()
			)
		);

		$cosmosRow->addItem(
			ALItem::newFromEditLink(
				'Cosmos-tagline',
				wfMessage( 'cosmos-adminlinks-edit-tagline' )->text()
			)
		);

		$cosmosSection->addRow( $cosmosRow );
		$adminLinksTree->addSection( $cosmosSection, wfMessage( 'adminlinks_users' )->text() );
	}

	/**
	 * @param ResourceLoaderContext $context
	 * @param Config $config
	 * @return array
	 */
	public static function getCosmosResourceLoaderConfig(
		ResourceLoaderContext $context,
		Config $config
	) {
		return [
			'wgCosmosSearchHost' => $config->get( 'CosmosSearchHost' ),
			'wgCosmosSearchUseActionAPI' => (bool)$config->get( 'CosmosSearchUseActionAPI' ),
		];
	}

	/**
	 * @param ResourceLoaderContext $context
	 * @param Config $config
	 * @return array
	 */
	public static function getCosmosWVUISearchResourceLoaderConfig(
		ResourceLoaderContext $context,
		Config $config
	): array {
		return array_merge( [
			'wgCosmosSearchDescriptionSource' => $config->get( 'CosmosSearchDescriptionSource' ),
			'wgCosmosMaxSearchResults' => $config->get( 'CosmosMaxSearchResults' ),
			'wgScriptPath' => $config->get( 'ScriptPath' ),
			'wgSearchSuggestCacheExpiry' => $config->get( 'SearchSuggestCacheExpiry' ),
		], $config->get( 'CosmosSearchOptions' ) );
	}
}
