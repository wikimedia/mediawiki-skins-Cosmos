<?php
class CosmosHooks extends Hooks {
	public static function onGetPreferences( $user, &$preferences ) {
		$preferences['cosmos-mode'] = [
			'type' => 'radio',
			'label-message' => 'cosmos-mode-preference',
			'section' => 'cosmos/cosmos-mode',
			'options' => [
				wfMessage( 'cosmos-darkmode-preference' )->text() => 'cosmos-darkmode',
				wfMessage( 'cosmos-lightmode-preference' )->text() => 'cosmos-lightmode',
			],
		];
    }
}