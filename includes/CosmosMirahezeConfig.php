<?php

class Config {
	private const DEFAULT_CONFIG = [
		'banner-logo' => null,
		'header-wordmark' => null,
		'header-background' => null,
		'header-background-color' => null,
		'modern-tabs' => false,
		'round-avatar' => false,
		'show-editcount' => false,
		'allow-bio' => false,
		'profile-tags' => false,
		'social-avatar' => false,
		'group-tags' => array('bureaucrat', 'bot', 'sysop', 'interface-admin', 'suppressor'),
		'number-of-tags' => 2,
	];

	private const CONFIG_TYPES = [
		'banner-logo' => 'string',
		'header-wordmark' => 'string',
		'header-background' => 'string',
		'header-background-color' => 'string',
		'modern-tabs' => 'boolean',
		'round-avatar' => 'boolean',
		'show-editcount' => 'boolean',
		'allow-bio' => 'boolean',
		'profile-tags' => 'boolean',
		'social-avatar' => 'boolean',
		'group-tags' => 'array',
		'number-of-tags' => 'integer',
	];

	private const CONFIG_NAMES = [
		'banner-logo' => 'wgCosmosBannerLogo',
		'header-wordmark' => 'wgCosmosWikiHeaderWordmark',
		'header-background' => 'wgCosmosWikiHeaderBackgroundImage',
		'header-background-color' => 'wgCosmosWikiHeaderBackgroundColor',
		'modern-tabs' => 'wgCosmosSocialProfileModernTabs',
		'round-avatar' => 'wgCosmosSocialProfileRoundAvatar',
		'show-editcount' => 'wgCosmosSocialProfileShowEditCount',
		'allow-bio' => 'wgCosmosSocialProfileAllowBio',
		'profile-tags' => 'wgCosmosSocialProfileShowGroupTags',
		'social-avatar' => 'wgCosmosUseSocialProfileAvatar',
		'group-tags' => 'wgCosmosProfileTagGroups',
		'number-of-tags' => 'wgCosmosNumberofGroupTags',
	];

	private $options;

	public function __construct() {
		global $wgCosmosConfig, $wgLogos, $wgLogo;

		// Set the options array to the default options upon construction
		$this->options = self::DEFAULT_CONFIG;
		
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
