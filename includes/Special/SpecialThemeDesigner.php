<?php

namespace MediaWiki\Skins\Cosmos\Special;

use BagOStuff;
use FormSpecialPage;
use Html;
use MediaWiki\Skins\Cosmos\CosmosConfig;
use ObjectCache;

class SpecialThemeDesigner extends FormSpecialPage {
	/** @var BagOStuff */
	private $cache;

	/** @var string */
	private $cacheDir;

	/** @var CosmosConfig */
	private $config;

	/** @var int */
	private $timestamp;

	/**
	 * @param CosmosConfig $config
	 */
	public function __construct( CosmosConfig $config ) {
		parent::__construct( 'ThemeDesigner', 'themedesigner' );

		$this->cache = ObjectCache::getLocalClusterInstance();
		$this->cacheDir = $this->getConfig()->get( 'CacheDirectory' ) ?: __DIR__ . '/../../../../cache';

		$this->config = $config;

		$this->timestamp = $this->cache->get(
			$this->cache->makeGlobalKey(
				'CosmosThemeDesigner',
				'cosmos_themedesigner'
			)
		);
	}

	/**
	 * @return string
	 */
	protected function getMessagePrefix() {
		return 'cosmos-' . strtolower( $this->getName() );
	}

	/**
	 * @return array
	 */
	protected function getFormFields() {
		$formDescriptor = [];

		$formDescriptor['CosmosBannerBackgroundColor'] = [
			'type' => 'text',
			'default' => $this->config->getBannerBackgroundColor(),
			'help-message' => 'cosmos-themedesigner-help-1',
		];

		$formDescriptor['CosmosWikiHeaderBackgroundColor'] = [
			'type' => 'text',
			'default' => $this->config->getWikiHeaderBackgroundColor(),
			'help-message' => 'cosmos-themedesigner-help-2',
		];

		$formDescriptor['CosmosMainBackgroundColor'] = [
			'type' => 'text',
			'default' => $this->config->getMainBackgroundColor(),
			'help-message' => 'cosmos-themedesigner-help-3',
		];

		$formDescriptor['CosmosContentBackgroundColor'] = [
			'type' => 'text',
			'default' => $this->config->getContentBackgroundColor(),
			'help-message' => 'cosmos-themedesigner-help-4',
		];

		$formDescriptor['CosmosButtonBackgroundColor'] = [
			'type' => 'text',
			'default' => $this->config->getButtonBackgroundColor(),
			'help-message' => 'cosmos-themedesigner-help-5',
		];

		$formDescriptor['CosmosFooterBackgroundColor'] = [
			'type' => 'text',
			'default' => $this->config->getFooterBackgroundColor(),
			'help-message' => 'cosmos-themedesigner-help-7',
		];

		$formDescriptor['CosmosToolbarBackgroundColor'] = [
			'type' => 'text',
			'default' => $this->config->getToolbarBackgroundColor(),
			'help-message' => 'cosmos-themedesigner-help-8',
		];

		$formDescriptor['CosmosLinkColor'] = [
			'type' => 'text',
			'default' => $this->config->getLinkColor(),
			'help-message' => 'cosmos-themedesigner-help-6',
		];

		$formDescriptor['CosmosContentOpacityLevel'] = [
			'type' => 'int',
			'min' => 0,
			'max' => 100,
			'default' => $this->config->getContentOpacityLevel(),
			'help-message' => 'cosmos-themedesigner-help-12',
		];

		$formDescriptor['CosmosWordmark'] = [
			'type' => 'text',
			'default' => $this->config->getWordmark(),
			'help-message' => 'cosmos-themedesigner-help-9',
		];

		$formDescriptor['CosmosWikiHeaderBackgroundImage'] = [
			'type' => 'text',
			'default' => $this->config->getWikiHeaderBackgroundImage(),
			'help-message' => 'cosmos-themedesigner-help-10',
		];

		$formDescriptor['CosmosBackgroundImage'] = [
			'type' => 'text',
			'default' => $this->config->getBackgroundImage(),
			'help-message' => 'cosmos-themedesigner-help-11',
		];

		$formDescriptor['CosmosBackgroundImageSize'] = [
			'type' => 'select',
			'default' => $this->config->getBackgroundImageSize(),
			'help-message' => 'cosmos-themedesigner-help-13',
			'options' => [
				'auto' => 'auto',
				'contain' => 'contain',
				'cover' => 'cover',
			],
		];

		$formDescriptor['CosmosBackgroundImageRepeat'] = [
			'type' => 'check',
			'default' => $this->config->getBackgroundImageRepeat(),
			'help-message' => 'cosmos-themedesigner-help-14',
		];

		$formDescriptor['CosmosBackgroundImageFixed'] = [
			'type' => 'check',
			'default' => $this->config->getBackgroundImageFixed(),
			'help-message' => 'cosmos-themedesigner-help-15',
		];

		return $formDescriptor;
	}

	/**
	 * @param array $formData
	 * @return bool
	 */
	public function onSubmit( array $formData ) {
		$this->cache->set(
			$this->cache->makeGlobalKey(
				'CosmosThemeDesigner', 'cosmos_themedesigner'
			),
			wfTimestampNow()
		);

		if ( !is_dir( "{$this->cacheDir}/cosmos-themedesigner" ) ) {
			mkdir( "{$this->cacheDir}/cosmos-themedesigner", 0777, true );
		}

		$dbName = $this->getConfig()->get( 'DBname' );

		file_put_contents(
			"{$this->cacheDir}/cosmos-themedesigner/{$dbName}.json.tmp",
			json_encode( [
				'timestamp' => $this->timestamp,
				'values' => $formData
			] ), LOCK_EX );

		if ( file_exists( "{$this->cacheDir}/cosmos-themedesigner/{$dbName}.json.tmp" ) ) {
			rename(
				"{$this->cacheDir}/cosmos-themedesigner/{$dbName}.json.tmp",
				"{$this->cacheDir}/cosmos-themedesigner/{$dbName}.json"
			);
		}

		$this->getOutput()->addHTML(
			Html::successBox( $this->msg( 'cosmos-themedesigner-success' )->escaped() )
		);

		return true;
	}

	/**
	 * @return string
	 */
	protected function getDisplayFormat() {
		return 'ooui';
	}
}
