<?php	
/**	
 * ResourceLoader module for LESS configs	
 * @author Universal Omega
 */	

namespace Cosmos;

use Cosmos\Config;
use Cosmos\LessUtil;
use MediaWiki\MediaWikiServices;	
use ResourceLoaderContext;	
use ResourceLoaderFileModule;	
use ExtensionRegistry;


class ResourceLoaderLessModule extends ResourceLoaderFileModule {	
	/**	
	 * Get language-specific LESS variables for this module.	
	 *	
	 * @param ResourceLoaderContext $context	
	 * @return array
	 */
	protected function getLessVars( ResourceLoaderContext $context ) {
		$lessVars = parent::getLessVars( $context );
		$config = new Config();
		$content__background_color = $config->getString( 'content-background-color' );
		if(strpos($content__background_color, 'rgb') !== false){
            $rgbarr = explode(",",$content__background_color,3);
            $colorname = sprintf("#%02x%02x%02x", $rgbarr[0], $rgbarr[1], $rgbarr[2]);
		} else {
		    $colorname = LessUtil::colorNameToHex($content__background_color);
		}
		$lessVars[ 'banner-background-color' ] = $config->getString( 'banner-background-color' );
		
		if($config->getString( 'main-background-image' )){
			$lessVars[ 'main-background-image-isset' ] = 1;
			$lessVars[ 'main-background-image' ] = 'url(' . $config->getString( 'main-background-image' ) . ')';
		} else {
			$lessVars[ 'main-background-image-isset' ] = 0;
		}
		$lessVars[ 'main-background-color' ] = $config->getString( 'main-background-color' );
		$lessVars[ 'content-background-color' ] = $config->getString( 'content-background-color' );
		$lessVars[ 'main-background-image-size' ] = $config->getString( 'main-background-image-size' );
		$lessVars[ 'link-color' ] = $config->getString( 'link-color' );
		$lessVars[ 'button-color' ] = $config->getString( 'button-color' );
		$lessVars[ 'font-family' ] = $config->getString( 'font-family' );
		$lessVars[ 'font-style' ] = $config->getString( 'font-style' );
		if($config->isEnabled( 'main-background-image-norepeat' )){
		    $lessVars[ 'main-background-image-repeat' ] = 'no-repeat';
		} else {
		    $lessVars[ 'main-background-image-repeat' ] = 'repeat';
		}
		if($config->isEnabled( 'main-background-image-fixed' )){
		    $lessVars[ 'main-background-image-position' ] = 'fixed';
		} else {
		    $lessVars[ 'main-background-image-position' ] = 'absolute';
		}
		// convert @content-background-color to rgba for background-color opacity
		list($r, $g, $b) = array_map(
    		function($c) {
        		return hexdec(str_pad($c, 2, $c));
    		},
    		str_split(ltrim($colorname, '#'), strlen($colorname) > 4 ? 2 : 1)
		);
		if (ExtensionRegistry::getInstance()->isLoaded( 'ManageWiki' )){
		    global $wgCosmosContentOpacityLevel;
		    $content_opacity_level_config = $wgCosmosContentOpacityLevel;
		}else{
	        $content_opacity_level_config = $config->getInteger('content-opacity-level');
		}
		$lessVars[ 'content-opacity-level' ] = "rgba($r, $g, $b, " . $content_opacity_level_config / 100.00 . ')';
		$footer_background_color = $config->getString( 'footer-color' );
		if(strpos($footer_background_color, 'rgb') !== false){
            $rgbarr = explode(",",$footer_background_color,3);
            $colorname = sprintf("#%02x%02x%02x", $rgbarr[0], $rgbarr[1], $rgbarr[2]);
		} else {
		    $colorname = LessUtil::colorNameToHex($footer_background_color);
		}
		list($r, $g, $b) = array_map(
    		function($c) {
        		return hexdec(str_pad($c, 2, $c));
    		},
    		str_split(ltrim($colorname, '#'), strlen($colorname) > 4 ? 2 : 1)
		);
		$lessVars[ 'footer-background-color' ] = "rgba($r, $g, $b,0.9)";
		$lessVars[ 'footer-font-color1' ] = LessUtil::isFooterThemeDark() ? '#999' : '#666';
		$lessVars[ 'footer-font-color2' ] = LessUtil::isFooterThemeDark() ? '#fff' : '#000';
		$header_background_color = $config->getString( 'header-background-color' );
		if(strpos($header_background_color, 'rgb') !== false){
            $rgbarr = explode(",",$header_background_color,3);
            $colorname = sprintf("#%02x%02x%02x", $rgbarr[0], $rgbarr[1], $rgbarr[2]);
		} else {
		    $colorname = LessUtil::colorNameToHex($header_background_color);
		}
		list($r, $g, $b) = array_map(
    		function($c) {
        		return hexdec(str_pad($c, 2, $c));
    		},
    		str_split(ltrim($colorname, '#'), strlen($colorname) > 4 ? 2 : 1)
		);
		$lessVars[ 'header-background-color' ] = "linear-gradient(to right,rgba($r, $g, $b,0.5),rgba($r, $g, $b,0.5)),linear-gradient(to left,rgba($r, $g, $b,0) 200px,$colorname 430px)";
		$lessVars[ 'header-background-color2' ] = "linear-gradient(to right,rgba($r, $g, $b,0.5),rgba($r, $g, $b,0.5)),linear-gradient(to left,rgba($r, $g, $b,0) 200px,$colorname 471px)";
		$lessVars[ 'header-font-color' ] = LessUtil::isHeaderThemeDark() ? '#fff' : '#000';
		$lessVars[ 'toolbar-color2' ] = $config->getString( 'toolbar-color' );
		$lessVars[ 'toolbar-color-mix' ] =  $config->getString( 'toolbar-color' ) == '#000' || $config->getString( 'toolbar-color' ) == '#000000'  || $config->getString( 'toolbar-color' ) == 'black' ? '#404040' : '#000';
		$lessVars[ 'toolbar-font-color' ] = LessUtil::isToolbarThemeDark() ? '#fff' : '#000';
		$lessVars[ 'font-color' ] = LessUtil::isThemeDark() ? '#fff' : '#000';
		$lessVars[ 'banner-font-color' ] = LessUtil::isBannerThemeDark() ? '#fff' : '#000';
		$lessVars[ 'banner-echo-font-color' ] = LessUtil::isBannerThemeDark() ? 'fff' : '111';
		$lessVars[ 'banner-input-bottom-border' ] = LessUtil::isBannerThemeDark() ? 'rgba(255,255,255,0.5)' : 'rgba(0,0,0,0.5)';
		$lessVars[ 'button-font-color' ] = LessUtil::isButtonThemeDark() ? '#fff' : '#000';
		$lessVars[ 'infobox-background-mix' ] = LessUtil::isThemeDark() ? '85%' : '90%';
		    return $lessVars;	
	}	
}
