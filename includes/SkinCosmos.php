<?php
/**
 * SkinTemplate class for the Cosmos skin
 *
 * @ingroup Skins
 */
class SkinCosmos extends SkinTemplate {
	/** @var string */
	public $skinname = 'cosmos';

	/** @var string */
	public $stylename = 'Cosmos';

	/** @var string */
	public $template = 'CosmosTemplate';

	/**
	 * @param OutputPage $out
	 */
	public function initPage( OutputPage $out ) {
	    $skin = $this->getSkin();
		parent::initPage( $out );

		$out->addMeta( 'viewport',
			'width=device-width, initial-scale=1.0, ' .
			'user-scalable=yes, minimum-scale=0.25, maximum-scale=5.0'
		);

		$out->addModuleStyles( [
			'mediawiki.skinning.content.externallinks',
			'skins.cosmos',
		] );
		
		$out->addModules( [
			'skins.cosmos.js',
			'skins.cosmos.mobile'
		] );
		//Load light-mode if user sets that in their preference
        if($skin->getUser()->getOption( 'cosmos-mode') == 'cosmos-lightmode'){
			$out->addStyle( $this->stylename . '/resources/lightmode.css' );
        }
	}
  
	/**
	 * Add CSS via ResourceLoader
	 *
	 * @param OutputPage $out
	 */
	public function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );
	}
}