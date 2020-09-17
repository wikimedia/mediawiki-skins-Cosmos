<?php
class CosmosHooks extends Hooks {
	public static function onGetPreferences($user, &$preferences) {
		$preferences['cosmos-mobile-responsiveness'] = [
			'type' => 'check',
			'label-message' => 'cosmos-mobile-preference',
			'section' => 'cosmos/cosmos-responsiveness'
		];
	}
}

