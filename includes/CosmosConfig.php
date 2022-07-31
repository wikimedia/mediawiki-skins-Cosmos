<?php

namespace MediaWiki\Skins\Cosmos;

use Config;

class CosmosConfig {

	/** @var string */
	private $cacheDir;

	/** @var Config */
	private $config;

	/** @var array */
	private $themeDesignerConfig;

	/**
	 * @param Config $config
	 */
	public function __construct( Config $config ) {
		$this->config = $config;

		$this->cacheDir = $this->config->get( 'CacheDirectory' ) ?: __DIR__ . '/../../../cache';
		$dbName = $this->config->get( 'DBname' );

		if ( file_exists( "{$this->cacheDir}/cosmos-themedesigner/{$dbName}.json" ) ) {
			$this->themeDesignerConfig = json_decode(
				file_get_contents(
					"{$this->cacheDir}/cosmos-themedesigner/{$dbName}.json"
				), true
			)['values'] ?? false;
		}
	}

	/**
	 * @return string
	 */
	public function getBannerBackgroundColor(): string {
		$config = $this->config->get( 'CosmosBannerBackgroundColor' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosBannerBackgroundColor'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getWikiHeaderBackgroundColor(): string {
		$config = $this->config->get( 'CosmosWikiHeaderBackgroundColor' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosWikiHeaderBackgroundColor'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getMainBackgroundColor(): string {
		$config = $this->config->get( 'CosmosMainBackgroundColor' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosMainBackgroundColor'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getContentBackgroundColor(): string {
		$config = $this->config->get( 'CosmosContentBackgroundColor' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosContentBackgroundColor'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getButtonBackgroundColor(): string {
		$config = $this->config->get( 'CosmosButtonBackgroundColor' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosButtonBackgroundColor'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getLinkColor(): string {
		$config = $this->config->get( 'CosmosLinkColor' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosLinkColor'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getFooterBackgroundColor(): string {
		$config = $this->config->get( 'CosmosFooterBackgroundColor' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosFooterBackgroundColor'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getToolbarBackgroundColor(): string {
		$config = $this->config->get( 'CosmosToolbarBackgroundColor' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosToolbarBackgroundColor'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getWordmark(): string {
		$config = $this->config->get( 'CosmosWordmark' ) ?:
				$this->config->get( 'Logos' )['wordmark']['src'] ??
				$this->config->get( 'Logos' )['1x'] ??
				$this->config->get( 'Logo' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosWordmark'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getWikiHeaderBackgroundImage(): string {
		$config = $this->config->get( 'CosmosWikiHeaderBackgroundImage' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosWikiHeaderBackgroundImage'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getBackgroundImage(): string {
		$config = $this->config->get( 'CosmosBackgroundImage' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosBackgroundImage'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getBackgroundImageSize(): string {
		$config = $this->config->get( 'CosmosBackgroundImageSize' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosBackgroundImageSize'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return string
	 */
	public function getContentWidth(): string {
		$config = $this->config->get( 'CosmosContentWidth' );

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
		$config = $this->config->get( 'CosmosContentOpacityLevel' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			(int)$this->themeDesignerConfig['CosmosContentOpacityLevel'] : false;

		return ( $themeDesignerConfig ?: $config );
	}

	/**
	 * @return bool
	 */
	public function getBackgroundImageRepeat(): bool {
		$config = $this->config->get( 'CosmosBackgroundImageRepeat' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosBackgroundImageRepeat'] : null;

		return ( $themeDesignerConfig ?? $config );
	}

	/**
	 * @return bool
	 */
	public function getBackgroundImageFixed(): bool {
		$config = $this->config->get( 'CosmosBackgroundImageFixed' );
		$themeDesignerConfig = $this->themeDesignerConfig ?
			$this->themeDesignerConfig['CosmosBackgroundImageFixed'] : null;

		return ( $themeDesignerConfig ?? $config );
	}
}
