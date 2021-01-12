<?php
/**
 * ResourceLoader module for LESS configs
 * @author Universal Omega
 */

namespace MediaWiki\Skin\Cosmos;

use Config;
use MediaWiki\MediaWikiServices;
use ResourceLoaderContext;
use ResourceLoaderFileModule;

class ResourceLoaderLessModule extends ResourceLoaderFileModule {
	/**
	 * Get language-specific LESS variables for this module.
	 *
	 * @param ResourceLoaderContext $context
	 * @return array
	 */
	protected function getLessVars( ResourceLoaderContext $context ) {
		$lessVars = parent::getLessVars( $context );
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'cosmos' );
		$contentBackgroundColor = $config->get( 'CosmosContentBackgroundColor' );
		if ( strpos( $contentBackgroundColor, 'rgb' ) !== false ) {
			$rgbArr = explode( ",", $contentBackgroundColor, 3 );
			$colorName = sprintf( "#%02x%02x%02x", $rgbArr[0], $rgbArr[1], $rgbArr[2] );
		} else {
			$colorName = LessUtil::colorNameToHex( $contentBackgroundColor );
		}
		$lessVars['banner-background-color'] = $config->get( 'CosmosBannerBackgroundColor' );

		if ( $config->get( 'CosmosBackgroundImage' ) ) {
			$lessVars['main-background-image-isset'] = 1;
			$lessVars['main-background-image'] = 'url(' . $config->get( 'CosmosBackgroundImage' ) . ')';
		} else {
			$lessVars['main-background-image-isset'] = 0;
		}
		$lessVars['main-background-color'] = $config->get( 'CosmosMainBackgroundColor' );
		$lessVars['content-background-color'] = $config->get( 'CosmosContentBackgroundColor' );
		$lessVars['main-background-image-size'] = $config->get( 'CosmosBackgroundImageSize' );
		$lessVars['link-color'] = $config->get( 'CosmosLinkColor' );
		$lessVars['button-color'] = $config->get( 'CosmosButtonColor' );
		$lessVars['font-family'] = $config->get( 'CosmosFontFamily' );
		$lessVars['font-style'] = $config->get( 'CosmosFontStyle' );
		if ( $config->get( 'CosmosBackgroundImageNorepeat' ) ) {
			$lessVars['main-background-image-repeat'] = 'no-repeat';
		} else {
			$lessVars['main-background-image-repeat'] = 'repeat';
		}
		if ( $config->get( 'CosmosBackgroundImageFixed' ) ) {
			$lessVars['main-background-image-position'] = 'fixed';
		} else {
			$lessVars['main-background-image-position'] = 'absolute';
		}
		// convert @content-background-color to rgba for background-color opacity
		list( $r, $g, $b ) = array_map( function ( $c ) {
			return hexdec( str_pad( $c, 2, $c ) );
		},
		str_split( ltrim( $colorName, '#' ), strlen( $colorName ) > 4 ? 2 : 1 ) );
		$contentOpacityLevelConfig = $config->get( 'CosmosContentOpacityLevel' );
		$lessVars['content-opacity-level'] = "rgba($r, $g, $b, " . $contentOpacityLevelConfig / 100.00 . ')';
		$footerBackgroundColor = $config->get( 'CosmosFooterColor' );
		if ( strpos( $footerBackgroundColor, 'rgb' ) !== false ) {
			$rgbArr = explode( ",", $footerBackgroundColor, 3 );
			$colorName = sprintf( "#%02x%02x%02x", $rgbArr[0], $rgbArr[1], $rgbArr[2] );
		} else {
			$colorName = LessUtil::colorNameToHex( $footerBackgroundColor );
		}
		list( $r, $g, $b ) = array_map( function ( $c ) {
			return hexdec( str_pad( $c, 2, $c ) );
		},
		str_split( ltrim( $colorName, '#' ), strlen( $colorName ) > 4 ? 2 : 1 ) );
		$lessVars['footer-background-color'] = "rgba($r, $g, $b,0.9)";

		$isFooterColorDark = LessUtil::isThemeDark( 'footer-color' );
		$lessVars['footer-font-color1'] = $isFooterColorDark ? '#999' : '#666';
		$lessVars['footer-font-color2'] = $isFooterColorDark ? '#fff' : '#000';

		$headerBackgroundColor = $config->get( 'CosmosWikiHeaderBackgroundColor' );
		if ( strpos( $headerBackgroundColor, 'rgb' ) !== false ) {
			$rgbArr = explode( ",", $headerBackgroundColor, 3 );
			$colorName = sprintf( "#%02x%02x%02x", $rgbArr[0], $rgbArr[1], $rgbArr[2] );
		} else {
			$colorName = LessUtil::colorNameToHex( $headerBackgroundColor );
		}
		list( $r, $g, $b ) = array_map( function ( $c ) {
			return hexdec( str_pad( $c, 2, $c ) );
		},
		str_split( ltrim( $colorName, '#' ), strlen( $colorName ) > 4 ? 2 : 1 ) );

		$rightGradient = "linear-gradient(to right,rgba($r,$g,$b,0.5),rgba($r,$g,$b,0.5))";
		$leftGradient = "linear-gradient(to left,rgba($r,$g,$b,0) 200px,$colorName 430px)";
		$lessVars['header-background-color'] = "$rightGradient,$leftGradient";

		$rightGradient = "linear-gradient(to right,rgba($r,$g,$b,0.5),rgba($r,$g,$b,0.5))";
		$leftGradient = "linear-gradient(to left,rgba($r,$g,$b,0) 200px,$colorName 471px)";
		$lessVars['header-background-color2'] = "$rightGradient,$leftGradient";

		$lessVars['header-background-solid-color'] = $headerBackgroundColor;
		$lessVars['header-font-color'] = LessUtil::isThemeDark( 'header-background-color' ) ? '#fff' : '#000';

		return array_merge(
			$lessVars,
			$this->getThemedToolbarColorSettings( $config ),
			$this->getThemeContentBackgroundColorSettings(),
			$this->getThemedBannerBackgroundColorSettings(),
			$this->getThemedButtonColorSettings()
		);
	}

	/**
	 * @param Config $config
	 * @return array
	 */
	private function getThemedToolbarColorSettings( Config $config ) : array {
		$toolbarColor = $config->get( 'CosmosToolbarColor' );

		return [
			'toolbar-color2' => $toolbarColor,
			'toolbar-color-mix' =>
				$toolbarColor == '#000' ||
				$toolbarColor == '#000000' ||
				$toolbarColor == 'black' ? '#404040' : '#000',
			'toolbar-font-color' => LessUtil::isThemeDark( 'toolbar-color' ) ? '#fff' : '#000',
		];
	}

	/**
	 * @return array
	 */
	private function getThemeContentBackgroundColorSettings() : array {
		$isContentBackgroundColorDark = LessUtil::isThemeDark( 'content-background-color' );

		return [
			'font-color' => $isContentBackgroundColorDark ? '#D5D4D4' : '#000',
			'border-color' => $isContentBackgroundColorDark ? '#333333' : '#CCCCCC',
			'editsection-color' => $isContentBackgroundColorDark ? '#54595d' : '#aba6a2',
			'alt-font-color' => $isContentBackgroundColorDark ? '#fff' : '#000',
			'code-background-color' => $isContentBackgroundColorDark ? '#c5c6c6' : '#3a3939',
			'tabs-background-color' => $isContentBackgroundColorDark ? 'transparent' : '#eaecf0',
			'infobox-background-mix' => $isContentBackgroundColorDark ? '85%' : '90%',
		];
	}

	/**
	 * @return array
	 */
	private function getThemedBannerBackgroundColorSettings() : array {
		$isBannerBackgroundColorDark = LessUtil::isThemeDark( 'banner-background-color' );

		return [
			'banner-font-color' => $isBannerBackgroundColorDark ? '#fff' : '#000',
			'banner-echo-font-color' => $isBannerBackgroundColorDark ? 'fff' : '111',
			'banner-input-bottom-border' => $isBannerBackgroundColorDark ? 'rgba(255,255,255,0.5)' : 'rgba(0,0,0,0.5)',
		];
	}

	/**
	 * @return array
	 */
	private function getThemedButtonColorSettings() : array {
		$isButtonColorDark = LessUtil::isThemeDark( 'button-color' );

		return [
			'notice-close-button-color' => $isButtonColorDark ? 'fff' : '111',
			'button-font-color' => $isButtonColorDark ? '#fff' : '#000',
		];
	}
}
