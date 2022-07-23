<?php

namespace MediaWiki\Skins\Cosmos;

use ALItem;
use ALRow;
use ALSection;
use ALTree;
use Content;
use EditPage;
use Html;
use MediaWiki;
use MediaWiki\Hook\AlternateEditPreviewHook;
use MediaWiki\Hook\BeforeInitializeHook;
use MediaWiki\Hook\GetDoubleUnderscoreIDsHook;
use MediaWiki\Hook\OutputPageBodyAttributesHook;
use MediaWiki\Hook\OutputPageParserOutputHook;
use ObjectCache;
use OutputPage;
use Parser;
use ParserOutput;
use Sanitizer;
use Skin;
use Title;
use User;
use WebRequest;
use WikitextContent;

class Hooks implements
	AlternateEditPreviewHook,
	BeforeInitializeHook,
	GetDoubleUnderscoreIDsHook,
	OutputPageBodyAttributesHook,
	OutputPageParserOutputHook
{

	/**
	 * @param EditPage $editPage
	 * @param Content &$content
	 * @param string &$previewHTML
	 * @param ParserOutput &$parserOutput
	 * @return bool
	 */
	public function onAlternateEditPreview(
		$editPage,
		&$content,
		&$previewHTML,
		&$parserOutput
	): bool {
		$context = $editPage->getContext();
		$skin = $context->getSkin();
		$out = $context->getOutput();

		if (
			!( $skin instanceof SkinCosmos ) ||
			!( $content instanceof WikitextContent ) ||
			!$editPage->getTitle()->equals( Title::newFromText( 'Cosmos-navigation', NS_MEDIAWIKI ) )
		) {
			return true;
		}

		$pageText = trim( $content->getText() );

		if ( $pageText === '' || $pageText === '-' ) {
			return true;
		}

		$out->enableOOUI();

		$conflict = '';
		if ( $editPage->isConflict ) {
			$conflict = Html::rawElement(
				'div',
				[
					'id' => 'mw-previewconflict',
					'class' => 'warningbox'
				],
				$context->msg( 'previewconflict' )->escaped()
			);
		}

		$previewnote = $context->msg( 'previewnote' )->plain() .
			' <span class="mw-continue-editing">' .
			'[[#' . EditPage::EDITFORM_ID . '|' .
			$context->getLanguage()->getArrow() . ' ' .
			$context->msg( 'continue-editing' )->text() . ']]</span>';

		$previewHTML = Html::rawElement(
			'div',
			[ 'class' => 'previewnote' ],
			Html::rawElement(
				'h2',
				[ 'id' => 'mw-previewheader' ],
				$context->msg( 'preview' )->escaped()
			) .
			Html::rawElement(
				'div',
				[ 'class' => 'warningbox' ],
				$out->parseAsInterface( $previewnote )
			) . $conflict
		);

		$cosmosNavigation = new CosmosNavigation( $context );

		$previewHTML .= Html::openElement(
			'header',
			[ 'class' => 'cosmos-header' ]
		);

		$previewHTML .= Html::openElement(
			'nav',
			[ 'class' => 'cosmos-header__local-navigation navigation-preview' ]
		);

		$previewHTML .= Html::openElement( 'ul', [ 'class' => 'wds-tabs' ] );
		$previewHTML .= $cosmosNavigation->getMenu( CosmosNavigation::extract( $pageText ) );
		$previewHTML .= Html::closeElement( 'ul' );

		$previewHTML .= Html::closeElement( 'nav' );
		$previewHTML .= Html::closeElement( 'header' );

		return false;
	}

	/**
	 * @param Title $title
	 * @param null $unused
	 * @param OutputPage $output
	 * @param User $user
	 * @param WebRequest $request
	 * @param MediaWiki $mediaWiki
	 */
	public function onBeforeInitialize( $title, $unused, $output, $user, $request, $mediaWiki ) {
		if (
			( $output->getSkin() instanceof SkinCosmos ) &&
			$title->equals( Title::newFromText( 'Cosmos-navigation', NS_MEDIAWIKI ) )
		) {
			$request->setVal( 'wteswitched', '1' );
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Hooks/GetDoubleUnderscoreIDs
	 * @param array &$doubleUnderscoreIDs
	 */
	public function onGetDoubleUnderscoreIDs( &$doubleUnderscoreIDs ) {
		$doubleUnderscoreIDs[] = 'norail';
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Hooks/OutputPageBodyAttributes
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @param array &$bodyAttrs
	 */
	public function onOutputPageBodyAttributes( $out, $skin, &$bodyAttrs ): void {
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
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Hooks/OutputPageParserOutput
	 * @param OutputPage $out
	 * @param ParserOutput $parserOutput
	 */
	public function onOutputPageParserOutput( $out, $parserOutput ): void {
		if ( $parserOutput->getPageProperty( 'norail' ) !== null ) {
			$out->setProperty( 'norail', true );
		}

		if ( $parserOutput->getPageProperty( 'additionalBodyClass' ) ) {
			$parserProperty = $parserOutput->getPageProperty( 'additionalBodyClass' );
			$out->setProperty( 'additionalBodyClass', $parserProperty );
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
		$parser->getOutput()->setPageProperty( 'additionalBodyClass', $newClass );
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
}
