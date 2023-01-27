<?php

$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

$cfg['directory_list'] = array_merge(
	$cfg['directory_list'], [
		'../../extensions/AdminLinks',
		'../../extensions/CookieWarning',
		'../../extensions/SocialProfile',
	]
);

$cfg['exclude_analysis_directory_list'] = array_merge(
	$cfg['exclude_analysis_directory_list'], [
		'../../extensions/AdminLinks',
		'../../extensions/CookieWarning',
		'../../extensions/SocialProfile',
	]
);

$cfg['suppress_issue_types'] = [
	'PhanAccessClassInternal',
	'PhanAccessMethodInternal',
	'PhanParamNameIndicatingUnused',
	'PhanPluginDuplicateAdjacentStatement',
	'PhanPluginMixedKeyNoKey',
	'SecurityCheck-LikelyFalsePositive',
];

return $cfg;
