<?php

namespace MediaWiki\Skin\Cosmos\Specials;

use BagOStuff;
use Config;
use ConfigFactory;
use FormSpecialPage;
use MediaWiki\MediaWikiServices;
use ObjectCache;

class SpecialThemeDesigner extends FormSpecialPage {
	/** @var BagOStuff */
	private $cache;

	/** @var string */
	private $cacheDir;

	/** @var Config */
	private $config;

	/** @var ConfigFactory */
	private $configFactory;

	/** @var int */
	private $timestamp;

	public function __construct() {
		parent::__construct( 'ThemeDesigner', 'themedesigner' );

		$this->configFactory = MediaWikiServices::getInstance()->getConfigFactory();
		$this->config = $this->configFactory->makeConfig( 'cosmos' );

		$this->cache = ObjectCache::getLocalClusterInstance();
		$this->cacheDir = __DIR__ . "/../../cache";

		$this->timestamp = $this->cache->get(
			$this->cache->makeGlobalKey(
				'CosmosThemeDesigner',
				'cosmos_themedesigner'
			)
		);
	}

	/**
	 * @return array
	 */
	protected function getFormFields() {
		$formDescriptor = [];

		$configs = json_decode(
			file_get_contents(
				__DIR__ . "/../../skin.json"
			), true
		)['config'];

		foreach ( array_keys( $configs ) as $config ) {
			$value = $configs[$config]['value'];
			$description = $configs[$config]['description'];

			switch ( gettype( $value ) ) {
				case 'array':
					$type = 'multiselect';
					$options = $value;
					break;
				case 'boolean':
					$type = 'check';
					break;
				case 'integer':
					$type = 'int';
					break;
				default:
					$type = 'text';
					break;
			}

			$formDescriptor[$config] = [
				'type' => $type,
				'label' => "\$wg{$config}",
				'help' => $description,
				'default' => $this->config->get( $config ) ?? false,
				'options' => $options ?? false
			];
		}

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

		if ( !is_dir( $this->cacheDir ) ) {
			mkdir( $this->cacheDir );
		}

		file_put_contents(
			"{$this->cacheDir}/cosmos_themedesigner.json.tmp",
			json_encode( [
				'timestamp' => $this->timestamp,
				'values' => $formData
			] ), LOCK_EX );

		if ( file_exists( "{$this->cacheDir}/cosmos_themedesigner.json.tmp" ) ) {
			rename( "{$this->cacheDir}/cosmos_themedesigner.json.tmp", "{$this->cacheDir}/cosmos_themedesigner.json" );
		}

		$this->getOutput()->addHTML(
			'<div class="successbox">' .
				$this->msg( 'cosmos-themedesigner-success' )->escaped() .
			'</div>'
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
