<?php

namespace MediaWiki\Skin\Cosmos;

use Config;

class CosmosConfig {
	/** @var Config */
	private $config;

	/**
	 * @param Config $config
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * @return string
	 */
	public function getBannerBackgroundColor() : string {
		return $this->config->get( 'CosmosBannerBackgroundColor' );
	}

	/**
	 * @return string
	 */
	public function getWikiHeaderBackgroundColor() : string {
		return $this->config->get( 'CosmosWikiHeaderBackgroundColor' );
	}

	/**
	 * @return string
	 */
	public function getMainBackgroundColor() : string {
		return $this->config->get( 'CosmosMainBackgroundColor' );
	}

	/**
	 * @return string
	 */
	public function getContentBackgroundColor() : string {
		return $this->config->get( 'CosmosContentBackgroundColor' );
	}

	/**
	 * @return string
	 */
	public function getButtonColor() : string {
		return $this->config->get( 'CosmosButtonColor' );
	}

	/**
	 * @return string
	 */
	public function getLinkColor() : string {
		return $this->config->get( 'CosmosLinkColor' );
	}

	/**
	 * @return string
	 */
	public function getFooterColor() : string {
		return $this->config->get( 'CosmosFooterColor' );
	}

	/**
	 * @return string
	 */
	public function getToolbarColor() : string {
		return $this->config->get( 'CosmosToolbarColor' );
	}

	/**
	 * @return string
	 */
	public function getWikiHeaderWordmark() : string {
		return $this->config->get( 'CosmosWikiHeaderWordmark' );
	}

	/**
	 * @return string
	 */
	public function getWikiHeaderBackgroundImage() : string {
		return $this->config->get( 'CosmosWikiHeaderBackgroundImage' );
	}

	/**
	 * @return string
	 */
	public function getBackgroundImage() : string {
		return $this->config->get( 'CosmosBackgroundImage' );
	}

	/**
	 * @return string
	 */
	public function getBackgroundImageSize() : string {
		return $this->config->get( 'CosmosBackgroundImageSize' );
	}

	/**
	 * @return bool
	 */
	public function getBackgroundImageRepeat() : bool {
		return $this->config->get( 'CosmosBackgroundImageRepeat' );
	}

	/**
	 * @return bool
	 */
	public function getBackgroundImageFixed() : bool {
		return $this->config->get( 'CosmosBackgroundImageFixed' );
	}

	/**
	 * @return int
	 */
	public function getContentOpacityLevel() : int {
		return $this->config->get( 'CosmosContentOpacityLevel' );
	}

}
