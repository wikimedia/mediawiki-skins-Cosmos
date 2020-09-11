<?php	
/**	
 * ResourceLoader module for print styles.	
 *	
 * This program is free software; you can redistribute it and/or modify	
 * it under the terms of the GNU General Public License as published by	
 * the Free Software Foundation; either version 2 of the License, or	
 * (at your option) any later version.	
 *	
 * This program is distributed in the hope that it will be useful,	
 * but WITHOUT ANY WARRANTY; without even the implied warranty of	
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the	
 * GNU General Public License for more details.	
 *	
 * You should have received a copy of the GNU General Public License along	
 * with this program; if not, write to the Free Software Foundation, Inc.,	
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.	
 * http://www.gnu.org/copyleft/gpl.html	
 *	
 * @file	
 */	

namespace Cosmos;

use Cosmos\Config;
use Cosmos\LessUtil;
use MediaWiki\MediaWikiServices;	
use ResourceLoaderContext;	
use ResourceLoaderFileModule;	

/**	
 * ResourceLoader module for print styles.	
 *	
 * This class is also used when rendering styles for the MediaWiki installer.	
 * Do not rely on any of the normal global state, services, etc., and make sure	
 * to test the installer after making any changes here.	
 */	
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
		$lessVars[ 'header-background-color' ] = $config->getString( 'header-background-color' );
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
		$lessVars[ 'toolbar-color' ] = $config->getString( 'toolbar-color' );
		$lessVars[ 'font-family' ] = $config->getString( 'font-family' );
		$lessVars[ 'font-style' ] = $config->getString( 'font-style' );
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
		$lessVars[ 'content-opacity-level' ] = "rgba($r, $g, $b, " . $config->getInteger( 'content-opacity-level' ) / 100.00 . ')';
		$lessVars[ 'font-color' ] = LessUtil::isThemeDark() ? '#fff' : '#000';
		$lessVars[ 'banner-font-color' ] = LessUtil::isBannerThemeDark() ? '#fff' : '#000';
		$lessVars[ 'banner-input-bottom-border' ] = LessUtil::isBannerThemeDark() ? 'rgba(255,255,255,0.5)' : 'rgba(0,0,0,0.5)';
		    return $lessVars;	
	}	
}
