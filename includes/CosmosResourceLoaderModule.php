<?php

namespace MediaWiki\Skins\Cosmos;

use MediaWiki\MediaWikiServices;
use ResourceLoaderContext;
use ResourceLoaderSkinModule;
use Wikimedia\Minify\CSSMin;

class CosmosResourceLoaderModule extends ResourceLoaderSkinModule {

	/** @var CosmosConfig */
	private $cosmosConfig;

	/**
	 * @inheritDoc
	 *
	 * @suppress PhanParamSignatureRealMismatchParamType
	 * @param ResourceLoaderContext $context
	 * @return array
	 */
	public function getPreloadLinks( ResourceLoaderContext $context ): array {
		$preloadLinks = parent::getPreloadLinks( $context );

		$services = MediaWikiServices::getInstance();
		$wordmark = $services->getService( 'CosmosWordmarkLookup' )->getWordmarkUrl();

		$mainBackground = $services->getService( 'CosmosBackgroundLookup' )->getMainBackgroundUrl();
		$wikiHeaderBackground = $services->getService( 'CosmosBackgroundLookup' )->getWikiHeaderBackgroundUrl();

		if ( $wordmark ?? false ) {
			$preloadLinks[$wordmark] = [ 'as' => 'image' ];
		}

		if ( $mainBackground ?? false ) {
			$preloadLinks[$mainBackground] = [ 'as' => 'image' ];
		}

		if ( $wikiHeaderBackground ?? false ) {
			$preloadLinks[$wikiHeaderBackground] = [ 'as' => 'image' ];
		}

		return $preloadLinks;
	}

	/**
	 * @inheritDoc
	 *
	 * @suppress PhanParamSignatureRealMismatchParamType
	 * @param ResourceLoaderContext $context
	 * @return array
	 */
	protected function getLessVars( ResourceLoaderContext $context ) {
		$lessVars = parent::getLessVars( $context );

		$services = MediaWikiServices::getInstance();

		$this->cosmosConfig = $services->getService( 'CosmosConfig' );

		$mainBackground = $services->getService( 'CosmosBackgroundLookup' )->getMainBackgroundUrl();
		$wikiHeaderBackground = $services->getService( 'CosmosBackgroundLookup' )->getWikiHeaderBackgroundUrl();

		$contentBackgroundColor = $this->cosmosConfig->getContentBackgroundColor();

		if ( strpos( $contentBackgroundColor, 'rgb' ) !== false ) {
			$rgbArr = explode( ',', $contentBackgroundColor, 3 );
			$colorName = sprintf( '#%02x%02x%02x', $rgbArr[0], $rgbArr[1], $rgbArr[2] );
		} else {
			$colorName = LessUtil::colorNameToHex( $contentBackgroundColor );
		}

		$lessVars['banner-background-color'] = $this->cosmosConfig->getBannerBackgroundColor();

		if ( $mainBackground ) {
			$lessVars['main-background-image'] = CSSMin::buildUrlValue( $mainBackground );
		} else {
			$lessVars['main-background-image'] = 0;
		}

		if ( $wikiHeaderBackground ) {
			$lessVars['wiki-header-background-image'] = CSSMin::buildUrlValue( $wikiHeaderBackground );
		} else {
			$lessVars['wiki-header-background-image'] = 0;
		}

		$lessVars['main-background-color'] = $this->cosmosConfig->getMainBackgroundColor();
		$lessVars['content-background-color'] = $this->cosmosConfig->getContentBackgroundColor();
		$lessVars['main-background-image-size'] = $this->cosmosConfig->getBackgroundImageSize();

		$contentWidth = $this->cosmosConfig->getContentWidth();
		$lessVars['content-width-1084'] = $contentWidth === 'auto' ? 'auto' : 1024 + $contentWidth . 'px';
		$lessVars['content-width-1596'] = $contentWidth === 'auto' ? 'auto' : 1178 + $contentWidth . 'px';

		$lessVars['link-color'] = $this->cosmosConfig->getLinkColor();
		$lessVars['button-background-color'] = $this->cosmosConfig->getButtonBackgroundColor();

		if ( $this->cosmosConfig->getBackgroundImageRepeat() ) {
			$lessVars['main-background-image-repeat'] = 'repeat';
		} else {
			$lessVars['main-background-image-repeat'] = 'no-repeat';
		}

		if ( $this->cosmosConfig->getBackgroundImageFixed() ) {
			$lessVars['main-background-image-position'] = 'fixed';
		} else {
			$lessVars['main-background-image-position'] = 'absolute';
		}

		// convert @content-background-color to rgba for background-color opacity
		list( $r, $g, $b ) = array_map( static function ( $c ) {
			return hexdec( str_pad( $c, 2, $c ) );
		},

		str_split( ltrim( $colorName, '#' ), strlen( $colorName ) > 4 ? 2 : 1 ) );

		$contentOpacityLevelConfig = $this->cosmosConfig->getContentOpacityLevel();
		$lessVars['content-opacity-level'] = "rgba($r, $g, $b, " . $contentOpacityLevelConfig / 100.00 . ')';

		$footerBackgroundColor = $this->cosmosConfig->getFooterBackgroundColor();
		if ( strpos( $footerBackgroundColor, 'rgb' ) !== false ) {
			$rgbArr = explode( ',', $footerBackgroundColor, 3 );
			$colorName = sprintf( '#%02x%02x%02x', $rgbArr[0], $rgbArr[1], $rgbArr[2] );
		} else {
			$colorName = LessUtil::colorNameToHex( $footerBackgroundColor );
		}

		list( $r, $g, $b ) = array_map( static function ( $c ) {
			return hexdec( str_pad( $c, 2, $c ) );
		},

		str_split( ltrim( $colorName, '#' ), strlen( $colorName ) > 4 ? 2 : 1 ) );
		$lessVars['footer-background-color'] = "rgba($r, $g, $b, 0.9)";

		$isFooterBackgroundColorDark = LessUtil::isThemeDark( 'footer-background-color' );
		$lessVars['footer-font-color1'] = $isFooterBackgroundColorDark ? '#999' : '#666';
		$lessVars['footer-font-color2'] = $isFooterBackgroundColorDark ? '#fff' : '#000';

		$headerBackgroundColor = $this->cosmosConfig->getWikiHeaderBackgroundColor();
		if ( strpos( $headerBackgroundColor, 'rgb' ) !== false ) {
			$rgbArr = explode( ',', $headerBackgroundColor, 3 );
			$colorName = sprintf( '#%02x%02x%02x', $rgbArr[0], $rgbArr[1], $rgbArr[2] );
		} else {
			$colorName = LessUtil::colorNameToHex( $headerBackgroundColor );
		}

		list( $r, $g, $b ) = array_map( static function ( $c ) {
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
			$this->getThemedToolbarBackgroundColorSettings(),
			$this->getThemeContentBackgroundColorSettings(),
			$this->getThemedBannerBackgroundColorSettings(),
			$this->getThemedButtonBackgroundColorSettings()
		);
	}

	/**
	 * @return array
	 */
	private function getThemedToolbarBackgroundColorSettings(): array {
		$toolbarBackgroundColor = $this->cosmosConfig->getToolbarBackgroundColor();

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
	private function getThemeContentBackgroundColorSettings(): array {
		$isContentBackgroundColorDark = LessUtil::isThemeDark( 'content-background-color' );

		return [
			'font-color' => $isContentBackgroundColorDark ? '#D5D4D4' : '#000',
			'border-color' => $isContentBackgroundColorDark ? '#333333' : '#CCCCCC',
			'alt-font-color' => $isContentBackgroundColorDark ? '#fff' : '#000',
			'code-background-color' => $isContentBackgroundColorDark ? '#c5c6c6' : '#3a3939',
			'rail-header-bottom-border' => $isContentBackgroundColorDark ? '#0a0a0a' : '#eaecf0',
			'tabs-background-color' => $isContentBackgroundColorDark ? 'transparent' : '#eaecf0',
			'infobox-background-mix' => $isContentBackgroundColorDark ? '85%' : '90%',
			'toc-background-color' => $isContentBackgroundColorDark ? 'transparent' : '#f8f9fa',
		];
	}

	/**
	 * @return array
	 */
	private function getThemedBannerBackgroundColorSettings(): array {
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
	private function getThemedButtonBackgroundColorSettings(): array {
		$isButtonBackgroundColorDark = LessUtil::isThemeDark( 'button-background-color' );

		return [
			'notice-close-button-color' => $isButtonBackgroundColorDark ? 'fff' : '111',
			'button-font-color' => $isButtonBackgroundColorDark ? '#fff' : '#000',
		];
	}
}
