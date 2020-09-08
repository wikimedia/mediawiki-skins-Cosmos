<?php
namespace Cosmos;
use ExtensionRegistry;
class Config {
        
	private const DEFAULT_CONFIG = [
		'banner-background-color' => '#001e3b',
		'banner-logo' => null,
		'header-wordmark' => null,
		'header-background' => null,
		'header-background-color' => '#001e3b',
		'main-background-image' => null,
		'main-background-color' => '#1A1A1A',
		'content-background-color' => '#000',
		'main-background-image-size' => 'cover',
		'link-color' => '#006cb0',
		'button-color' => null,
		'toolbar-color' => null,
		'font-color' => '#fff',
		'font-family' => null,
		'font-style' => null,
		'modern-tabs' => true,
		'round-avatar' => true,
		'show-editcount' => true,
		'allow-bio' => true,
		'profile-tags' => true,
		'social-avatar' => true,
		'toolbar-message' => false,
		'main-background-image-norepeat' => true,
		'main-background-image-fixed' => true,
		'group-tags' => array('bureaucrat', 'bot', 'sysop', 'interface-admin', 'suppressor'),
		'number-of-tags' => 2,
		'content-opacity-level' => 100,
	];
	private const DEFAULT_MIRAHEZE_CONFIG = [
		'banner-background-color' => null,
		'banner-logo' => null,
		'header-wordmark' => null,
		'header-background' => null,
		'header-background-color' => null,
		'main-background-image' => null,
		'main-background-color' => null,
		'content-background-color' => null,
		'main-background-image-size' => null,
		'link-color' => null,
		'button-color' => null,
		'toolbar-color' => null,
		'font-color' => null,
		'font-family' => null,
		'font-style' => null,
		'modern-tabs' => false,
		'round-avatar' => false,
		'show-editcount' => false,
		'allow-bio' => false,
		'profile-tags' => false,
		'social-avatar' => false,
		'toolbar-message' => false,
		'main-background-image-norepeat' => false,
		'main-background-image-fixed' => false,
		'group-tags' => array('bureaucrat', 'bot', 'sysop', 'interface-admin'),
		'number-of-tags' => 2,
		'content-opacity-level' => 100,
	];

	private const CONFIG_TYPES = [
		'banner-background-color' => 'string',
		'banner-logo' => 'string',
		'header-wordmark' => 'string',
		'header-background' => 'string',
		'header-background-color' => 'string',
		'main-background-image' => 'string',
		'main-background-color' => 'string',
		'content-background-color' => 'string',
		'main-background-image-size' => 'string',
		'link-color' => 'string',
		'button-color' => 'string',
		'toolbar-color' => 'string',
		'font-color' => 'string',
		'font-family' => 'string',
		'font-style' => 'string',
		'modern-tabs' => 'boolean',
		'round-avatar' => 'boolean',
		'show-editcount' => 'boolean',
		'allow-bio' => 'boolean',
		'profile-tags' => 'boolean',
		'social-avatar' => 'boolean',
		'social-avatar' => 'boolean',
		'toolbar-message' => 'boolean',
		'main-background-image-norepeat' => 'boolean',
		'main-background-image-fixed' => 'boolean',
		'group-tags' => 'array',
		'number-of-tags' => 'integer',
		'content-opacity-level' => 'integer',
	];

	private const CONFIG_NAMES = [
		'banner-background-color' => 'wgCosmosBannerBackgroundColor',
		'banner-logo' => 'wgCosmosBannerLogo',
		'header-wordmark' => 'wgCosmosWikiHeaderWordmark',
		'header-background' => 'wgCosmosWikiHeaderBackgroundImage',
		'header-background-color' => 'wgCosmosWikiHeaderBackgroundColor',
		'main-background-image' => 'wgCosmosBackgroundImage',
		'main-background-color' => 'wgCosmosMainBackgroundColor',
		'content-background-color' => 'wgCosmosContentBackgroundColor',
		'main-background-image-size' => 'wgCosmosBackgroundImageSize',
		'link-color' => 'wgCosmosLinkColor',
		'button-color' => 'wgCosmosButtonColor',
		'toolbar-color' => 'wgCosmosToolbarColor',
		'font-color' => 'wgCosmosFontColor',
		'font-family' => 'wgCosmosFontFamily',
		'font-style' => 'wgCosmosFontStyle',
		'modern-tabs' => 'wgCosmosSocialProfileModernTabs',
		'round-avatar' => 'wgCosmosSocialProfileRoundAvatar',
		'show-editcount' => 'wgCosmosSocialProfileShowEditCount',
		'allow-bio' => 'wgCosmosSocialProfileAllowBio',
		'profile-tags' => 'wgCosmosSocialProfileShowGroupTags',
		'social-avatar' => 'wgCosmosUseSocialProfileAvatar',
		'toolbar-message' => 'wgCosmosUseMessageforToolbar',
		'main-background-image-norepeat' => 'wgCosmosBackgroundImageNorepeat',
		'main-background-image-fixed' => 'wgCosmosBackgroundImageFixed',
		'group-tags' => 'wgCosmosProfileTagGroups',
		'number-of-tags' => 'wgCosmosNumberofGroupTags',
		'content-opacity-level' => 'wgCosmosContentOpacityLevel',
	];

	private $options;

	public function __construct() {
		global $wgCosmosConfig, $wgLogos, $wgLogo;

		// Set the options array to the default options upon construction
		if (ExtensionRegistry::getInstance()->isLoaded( 'ManageWiki' )){
		    $this->options = self::DEFAULT_MIRAHEZE_CONFIG;
	    } else {
	        $this->options = self::DEFAULT_CONFIG;
	    }
		$this->options['header-wordmark'] = $wgLogos['wordmark']['src'] ? $wgLogos['wordmark']['src'] : $wgLogos['1x'] ? $wgLogos['1x'] : $wgLogo;

		// Loop through the options array and update each entry as necessary
		foreach ( $this->options as $name => &$value ) {
			// Check $wgCosmosConfig first, since it takes priority over individually
			// assigned global variables. If a valid setting is found, assign it and
			// skip to the next option

			if ( isset( $wgCosmosConfig ) && is_array( $wgCosmosConfig ) ) {
				$setting = $wgCosmosConfig[$name];
				if (
					isset( $setting ) &&
					gettype( $setting ) === self::CONFIG_TYPES[$name]
				) {
					$value = $setting;
					continue;
				}
			}

			// Otherwise, check the global variable name associated with the option,
			// and assign the setting to the option if it is a valid setting
			$setting = isset( $GLOBALS[self::CONFIG_NAMES[$name]] ) && $GLOBALS[self::CONFIG_NAMES[$name]] ? $GLOBALS[self::CONFIG_NAMES[$name]] : null;

			if (
				isset( $setting ) &&
				gettype( $setting ) === self::CONFIG_TYPES[$name]
			) {
				$value = $setting;
			}
		}
	}

	public function isEnabled( string $option ) : ?bool {
		if ( isset( $this->options[$option] )
			&& is_bool( $this->options[$option] ) ) {
			return $this->options[$option];
		} else {
			return null;
		}
	}

	public function getInteger( string $option ) : ?int {
		if ( isset( $this->options[$option] )
			&& is_int( $this->options[$option] ) ) {
			return $this->options[$option];
		} else {
			return null;
		}
	}

	public function getFloat( string $option ) : ?float {
		if ( isset( $this->options[$option] )
			&& is_float( $this->options[$option] ) ) {
			return $this->options[$option];
		} else {
			return null;
		}
	}

	public function getString( string $option ) : ?string {
		if ( isset( $this->options[$option] )
			&& is_string( $this->options[$option] ) ) {
			return $this->options[$option];
		} else {
			return null;
		}
	}

	public function getArray( string $option ) : ?array {
		if ( isset( $this->options[$option] )
			&& is_array( $this->options[$option] ) ) {
			return $this->options[$option];
		} else {
			return null;
		}
	}
}
