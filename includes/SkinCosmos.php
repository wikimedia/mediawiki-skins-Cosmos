<?php
/**
 * SkinTemplate class for the Cosmos skin
 *
 * @ingroup Skins
 */
use Cosmos\Config;
class SkinCosmos extends SkinTemplate {
	/** @var string */
	public $skinname = 'cosmos';

	/** @var string */
	public $stylename = 'Cosmos';

	/** @var string */
	public $template = 'CosmosTemplate';

	 // for some unknown reason we can not access wfReportTime at the moment
	 // this is being investigated. For the time-being, redefine it here
	 function wfReportTime( $nonce = null ) {
    		global $wgShowHostnames;
  
     		$elapsed = ( microtime( true ) - $_SERVER['REQUEST_TIME_FLOAT'] );
     		// seconds to milliseconds
     		$responseTime = round( $elapsed * 1000 );
     		$reportVars = [ 'wgBackendResponseTime' => $responseTime ];
     		if ( $wgShowHostnames ) {
         		$reportVars['wgHostname'] = wfHostname();
    		}
     		return Skin::makeVariablesScript( $reportVars, $nonce );
 	}
	
	/**
	 * @param OutputPage $out
	 */
	public function initPage(OutputPage $out) {
		parent::initPage($out);
		$config = new Config();
		if ($this->getSkin()
			->getUser()
			->getOption('cosmos-mobile-responsiveness') == 1) {
			$out->addMeta(
				'viewport', 
				'width=device-width, initial-scale=1.0, ' . 
				'user-scalable=yes, minimum-scale=0.25, maximum-scale=5.0'
			);
		}
		$out->addModuleStyles([
			'mediawiki.skinning.content.externallinks', 
			'skins.cosmos', 
			'skins.cosmos.legacy'
		]);

		$out->addModules([
			'skins.cosmos.js', 
			'skins.cosmos.mobile'
		]);
		if (ExtensionRegistry::getInstance()
			->isLoaded('Portable Infobox')) {
			$out->addModuleStyles([
				'skins.cosmos.portableinfobox', 
			]);
			if ($config->isEnabled('europa-theme')) {
				$out->addModuleStyles([
					'skins.cosmos.portableinfobox.europa', 
				]);
			}
			else {
				$out->addModuleStyles([
					'skins.cosmos.portableinfobox.default', 
				]);
			}
		}
		//Load SocialProfile styles if the respective configuration variables are enabled
		if (class_exists('UserProfilePage')) {
			if ($config->isEnabled('modern-tabs')) {
				$out->addModuleStyles([
					'skins.cosmos.profiletabs', 
				]);
			}
			if ($config->isEnabled('round-avatar')) {
				$out->addModuleStyles([
					'skins.cosmos.profileavatar', 
				]);
			}
			if ($config->isEnabled('show-editcount')) {
				$out->addModuleStyles([
					'skins.cosmos.profileeditcount', 
				]);
			}
			if ($config->isEnabled('allow-bio')) {
				$out->addModuleStyles([
					'skins.cosmos.profilebio', 
				]);
			}
			if ($config->isEnabled('profile-tags')) {
				$out->addModuleStyles([
					'skins.cosmos.profiletags', 
				]);
			}
			if ($config->isEnabled('modern-tabs') || 
			    $config->isEnabled('round-avatar') || 
			    $config->isEnabled('show-editcount') || 
			    $config->isEnabled('allow-bio') || 
			    $config->isEnabled('profile-tags')) {
				$out->addModuleStyles([
					'skins.cosmos.socialprofile', 
				]);
			}
		}
		if ($out->getTitle()
			->equals(Title::newMainPage())) {
			$out->addBodyClasses('mainpage');
		}
		// to-do convert to $out->getCSP()->getNonce()/remove
		echo wfReportTime( $out->getCSPNonce() );
	}
}
