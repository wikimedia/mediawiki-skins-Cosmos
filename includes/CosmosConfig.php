<?php

namespace MediaWiki\Skins\Cosmos;

use MediaWiki\Config\ServiceOptions;

class CosmosConfig {

	public const CONSTRUCTOR_OPTIONS = [
		'CacheDirectory',
		'CosmosBackgroundImage',
		'CosmosBackgroundImageFixed',
		'CosmosBackgroundImageRepeat',
		'CosmosBackgroundImageSize',
		'CosmosBannerBackgroundColor',
		'CosmosButtonBackgroundColor',
		'CosmosContentBackgroundColor',
		'CosmosContentOpacityLevel',
		'CosmosContentWidth',
		'CosmosFooterBackgroundColor',
		'CosmosLinkColor',
		'CosmosMainBackgroundColor',
		'CosmosToolbarBackgroundColor',
		'CosmosWikiHeaderBackgroundColor',
		'CosmosWikiHeaderBackgroundImage',
		'CosmosWordmark',
		'DBname',
		'Logos',
	];

	/** @var ServiceOptions */
	private $options;

	/** @var array */
	private $themeDesignerConfig;

	/**
	 * @param ServiceOptions $options
	 */
	public function __construct( ServiceOptions $options ) {
		$options->assertRequiredOptions( self::CONSTRUCTOR_OPTIONS );

		$this->options = $options;

		$cacheDir = $this->options->get( 'CacheDirectory' ) ?: __DIR__ . '/../../../cache';
		$dbName = $this->options->get( 'DBname' );

		if ( file_exists( "{$cacheDir}/cosmos-themedesigner/{$dbName}.json" ) ) {
			$this->themeDesignerConfig = json_decode(
				file_get_contents(
					"{$cacheDir}/cosmos-themedesigner/{$dbName}.json"
				), true
			)['values'] ?? false;
		}
	}

	/**
	 * @return string
	 */
	public function getBannerBackgroundColor(): string {
		$config = $this->options->get( 'CosmosBannerBackgroundColor' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosBannerBackgroundColor'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getWikiHeaderBackgroundColor(): string {
		$config = $this->options->get( 'CosmosWikiHeaderBackgroundColor' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosWikiHeaderBackgroundColor'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getMainBackgroundColor(): string {
		$config = $this->options->get( 'CosmosMainBackgroundColor' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosMainBackgroundColor'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getContentBackgroundColor(): string {
		$config = $this->options->get( 'CosmosContentBackgroundColor' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosContentBackgroundColor'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getButtonBackgroundColor(): string {
		$config = $this->options->get( 'CosmosButtonBackgroundColor' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosButtonBackgroundColor'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getLinkColor(): string {
		$config = $this->options->get( 'CosmosLinkColor' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosLinkColor'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getFooterBackgroundColor(): string {
		$config = $this->options->get( 'CosmosFooterBackgroundColor' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosFooterBackgroundColor'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getToolbarBackgroundColor(): string {
		$config = $this->options->get( 'CosmosToolbarBackgroundColor' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosToolbarBackgroundColor'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getWordmark(): string {
		$config = $this->options->get( 'CosmosWordmark' ) ?:
				$this->options->get( 'Logos' )['wordmark']['src'] ??
				$this->options->get( 'Logos' )['1x'] ?? '';
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosWordmark'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getWikiHeaderBackgroundImage(): string {
		$config = $this->options->get( 'CosmosWikiHeaderBackgroundImage' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosWikiHeaderBackgroundImage'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getBackgroundImage(): string {
		$config = $this->options->get( 'CosmosBackgroundImage' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosBackgroundImage'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getBackgroundImageSize(): string {
		$config = $this->options->get( 'CosmosBackgroundImageSize' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosBackgroundImageSize'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getContentWidth(): string {
		$config = $this->options->get( 'CosmosContentWidth' );

		switch ( $config ) {
			case 'full':
				$width = 'auto';
				break;
			case 'large':
				$width = '176';
				break;
			default:
				$width = '0';
		}

		return $width;
	}

	/**
	 * @return int
	 */
	public function getContentOpacityLevel(): int {
		$config = $this->options->get( 'CosmosContentOpacityLevel' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			(int)$this->themeDesignerConfig['CosmosContentOpacityLevel'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return bool
	 */
	public function getBackgroundImageRepeat(): bool {
		$config = $this->options->get( 'CosmosBackgroundImageRepeat' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosBackgroundImageRepeat'] : null;

		return ( $themeDesignerConfig ?? $config );
	}

	/**
	 * @return bool
	 */
	public function getBackgroundImageFixed(): bool {
		$config = $this->options->get( 'CosmosBackgroundImageFixed' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosBackgroundImageFixed'] : null;

		return ( $themeDesignerConfig ?? $config );
	}
}
