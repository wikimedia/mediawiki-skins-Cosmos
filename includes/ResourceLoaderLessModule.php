<?php

namespace MediaWiki\Skin\Cosmos;

use MediaWiki\MediaWikiServices;
use ResourceLoaderContext;
use ResourceLoaderSkinModule;

class ResourceLoaderLessModule extends ResourceLoaderSkinModule {
	/**
	 * Get language-specific LESS variables for this module.
	 *
	 * @param ResourceLoaderContext $context
	 * @return array
	 */
	protected function getLessVars( ResourceLoaderContext $context ) {
		$lessVars = parent::getLessVars( $context );
		$config = MediaWikiServices::getInstance()->getService( 'CosmosConfig' );

		$contentBackgroundColor = $config->getContentBackgroundColor();
		if ( strpos( $contentBackgroundColor, 'rgb' ) !== false ) {
			$rgbArr = explode( ",", $contentBackgroundColor, 3 );
			$colorName = sprintf( "#%02x%02x%02x", $rgbArr[0], $rgbArr[1], $rgbArr[2] );
		} else {
			$colorName = LessUtil::colorNameToHex( $contentBackgroundColor );
		}
		$lessVars['banner-background-color'] = $config->getBannerBackgroundColor();

		if ( $config->getBackgroundImage() ) {
			$lessVars['main-background-image-isset'] = 1;
			$lessVars['main-background-image'] = 'url(' . $config->getBackgroundImage() . ')';
		} else {
			$lessVars['main-background-image-isset'] = 0;
		}
		$lessVars['main-background-color'] = $config->getMainBackgroundColor();
		$lessVars['content-background-color'] = $config->getContentBackgroundColor();
		$lessVars['main-background-image-size'] = $config->getBackgroundImageSize();
		$lessVars['link-color'] = $config->getLinkColor();
		$lessVars['button-background-color'] = $config->getButtonBackgroundColor();
		if ( $config->getBackgroundImageRepeat() ) {
			$lessVars['main-background-image-repeat'] = 'repeat';
		} else {
			$lessVars['main-background-image-repeat'] = 'no-repeat';
		}
		if ( $config->getBackgroundImageFixed() ) {
			$lessVars['main-background-image-position'] = 'fixed';
		} else {
			$lessVars['main-background-image-position'] = 'absolute';
		}
		// convert @content-background-color to rgba for background-color opacity
		list( $r, $g, $b ) = array_map( function ( $c ) {
			return hexdec( str_pad( $c, 2, $c ) );
		},
		str_split( ltrim( $colorName, '#' ), strlen( $colorName ) > 4 ? 2 : 1 ) );
		$contentOpacityLevelConfig = $config->getContentOpacityLevel();
		$lessVars['content-opacity-level'] = "rgba($r, $g, $b, " . $contentOpacityLevelConfig / 100.00 . ')';
		$footerBackgroundColor = $config->getFooterBackgroundColor();
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
		$lessVars['footer-background-color'] = "rgba($r, $g, $b, 0.9)";

		$isFooterBackgroundColorDark = LessUtil::isThemeDark( 'footer-background-color' );
		$lessVars['footer-font-color1'] = $isFooterBackgroundColorDark ? '#999' : '#666';
		$lessVars['footer-font-color2'] = $isFooterBackgroundColorDark ? '#fff' : '#000';

		$headerBackgroundColor = $config->getWikiHeaderBackgroundColor();
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
			$this->getThemedToolbarBackgroundColorSettings( $config ),
			$this->getThemeContentBackgroundColorSettings(),
			$this->getThemedBannerBackgroundColorSettings(),
			$this->getThemedButtonBackgroundColorSettings()
		);
	}

	/**
	 * @param CosmosConfig $config
	 * @return array
	 */
	private function getThemedToolbarBackgroundColorSettings( CosmosConfig $config ) : array {
		$toolbarBackgroundColor = $config->getToolbarBackgroundColor();

		return [
			'toolbar-background-color2' => $toolbarBackgroundColor,
			'toolbar-background-color-mix' =>
				$toolbarBackgroundColor == '#000' ||
				$toolbarBackgroundColor == '#000000' ||
				$toolbarBackgroundColor == 'black' ? '#404040' : '#000',
			'toolbar-font-color' => LessUtil::isThemeDark( 'toolbar-background-color' ) ? '#fff' : '#000',
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
			'rail-header-bottom-border' => $isContentBackgroundColorDark ? '#0a0a0a' : '#eaecf0',
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
			'banner-font-color' =>
				$isBannerBackgroundColorDark ? '#fff' : '#000',
			'banner-echo-font-color' =>
				$isBannerBackgroundColorDark ? 'fff' : '111',
			'banner-search-background' =>
				$isBannerBackgroundColorDark ? 'rgba(0,0,0,0.4)' : 'rgba(255,255,255,0.4)',
			'banner-search-focus-background' =>
				$isBannerBackgroundColorDark ? 'rgba(0,0,0,0.6)' : 'rgba(255,255,255,0.6)',
			'banner-search-button-hover-background' =>
				$isBannerBackgroundColorDark ? 'rgba(0,0,0,0.3)' : 'rgba(255,255,255,0.3)',
			'banner-search-button-active-background' =>
				$isBannerBackgroundColorDark ? 'rgba(255,255,255,0.3)' : 'rgba(0,0,0,0.3)',
		];
	}

	/**
	 * @return array
	 */
	private function getThemedButtonBackgroundColorSettings() : array {
		$isButtonBackgroundColorDark = LessUtil::isThemeDark( 'button-background-color' );

		return [
			'notice-close-button-color' => $isButtonBackgroundColorDark ? 'fff' : '111',
			'button-font-color' => $isButtonBackgroundColorDark ? '#fff' : '#000',
		];
	}
}
