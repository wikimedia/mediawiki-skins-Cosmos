<?php
class CosmosHooks extends Hooks {
	public static function onGetPreferences($user, &$preferences) {
		$preferences['cosmos-mobile-responsiveness'] = [
			'type' => 'check',
			'label-message' => 'cosmos-mobile-preference',
			'section' => 'cosmos/cosmos-responsiveness'
		];
	}
	public static function onOutputPageBodyAttributes( OutputPage $out, Skin $skin, &$bodyAttrs ) {
		if ( $skin->getUser()->isLoggedIn() ) {
			$bodyAttrs['class'] .= ' user-logged';
		}
		else {
			$bodyAttrs['class'] .= ' user-anon';
		}
		if ($out->getTitle()->equals(Title::newMainPage())) {
			$bodyAttrs['class'] .= ' mainpage';
		}
		return true;
	}
}

