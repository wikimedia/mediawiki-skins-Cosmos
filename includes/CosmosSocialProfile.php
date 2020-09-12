<?php
use Cosmos\Config;
use ExtensionRegistry;
class CosmosSocialProfile {

	static function getUser( $parser, $user ) {
		$title = Title::newFromText( $user );
		if ( is_object( $title ) && ($title->getNamespace() == NS_USER || $title->getNamespace() == NS_USER_PROFILE) && !$title->isSubpage()) $user = $title->getText();
		$user = User::newFromName($user);
		return $user;
	}

	static function userregistration( $parser, $user ) {
		$user = self::getUser( $parser, $user );
		if($user){
		    return date('F j, Y', strtotime($user->getRegistration()));
		}
	}

	static function usergroups( $parser, $user ) {
        	$config = new Config();
		$user = self::getUser( $parser, $user );
		if($user && $user->isBlocked()){
		    $usertags = Html::rawElement( 'span', ['class' => 'tag tag-blocked' ], wfMessage('cosmos-user-blocked'));
		} elseif($user){
                    $number_of_tags = 0;
                    $usertags = '';
		    foreach ( $config->getArray('group-tags') as $value ) {
		       if(in_array($value, $user->getGroups())){
		        $number_of_tags++;
		        if (ExtensionRegistry::getInstance()->isLoaded( 'ManageWiki' )){
		            global $wgCosmosNumberofGroupTags;
		            $number_of_tags_config = $wgCosmosNumberofGroupTags;
		        }else{
		            $number_of_tags_config = $config->getInteger('number-of-tags');
		        }
		            if($number_of_tags <= $number_of_tags_config){
		                $usertags .= Html::rawElement( 'span', ['class' => 'tag tag-' . Sanitizer::escapeClass($value) ], ucfirst(wfMessage('group-' . $value . '-member')));
		            }
	    	    }
		    }
		}
		return $usertags;
	}

	static function useredits( $parser, $user ) {
		$user = self::getUser( $parser, $user );
		if($user){
		    return $user->getEditCount();
		}
	}
	static function userbio( $parser, $user ) {
	    if($user){
	        // return '<p class="bio">' . $parser->recursiveTagParse( '{{:User:' . $user . '/bio}}') . '</p>';
	    }
	}
}
