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
		$lessVars[ 'banner-background-color' ] = $config->getString( 'banner-background-color' );
		$lessVars[ 'header-background-color' ] = $config->getString( 'header-background-color' );
		$lessVars[ 'main-background-image' ] = $config->getString( 'main-background-image' );
		$lessVars[ 'main-background-color' ] = $config->getString( 'main-background-color' );
		$lessVars[ 'content-background-color' ] = $config->getString( 'content-background-color' );
		$lessVars[ 'main-background-image-size' ] = $config->getString( 'main-background-image-size' );
		$lessVars[ 'link-color' ] = $config->getString( 'link-color' );
		$lessVars[ 'button-color' ] = $config->getString( 'button-color' );
		$lessVars[ 'toolbar-color' ] = $config->getString( 'toolbar-color' );
		$lessVars[ 'font-color' ] = $config->getString( 'font-color' );
		$lessVars[ 'font-family' ] = $config->getString( 'font-family' );
		$lessVars[ 'font-style' ] = $config->getString( 'font-style' );
		$lessVars[ 'content-opacity-level' ] = $config->getString( 'content-opacity-level' ) . '%';
		    return $lessVars;	
	}	
}
