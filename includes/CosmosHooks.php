<?php
class CosmosHooks extends Hooks {
	public static function onGetPreferences( $user, &$preferences ) {
		$preferences['cosmos-colorscheme'] = [
			'type' => 'radio',
			'label-message' => 'cosmos-colorscheme-preference',
			'section' => 'cosmos/cosmos-colorscheme',
			'options' => [
				wfMessage( 'cosmos-darkmode-preference' )->text() => 'cosmos-darkmode',
				wfMessage( 'cosmos-lightmode-preference' )->text() => 'cosmos-lightmode',
			],
		];
		$preferences['cosmos-mobile-responsiveness'] = [
			'type' => 'check',
			'label-message' => 'cosmos-mobile-preference',
			'section' => 'cosmos/cosmos-responsiveness'
		];
    }
}
