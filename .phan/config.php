<?php

$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

$cfg['directory_list'] = array_merge(
	$cfg['directory_list'],
	[
		'../../extensions/AdminLinks',
		'../../extensions/SocialProfile',
	]
);

$cfg['exclude_analysis_directory_list'] = array_merge(
	$cfg['exclude_analysis_directory_list'],
	[
		'../../extensions/AdminLinks',
		'../../extensions/SocialProfile',
	]
);

$cfg['suppress_issue_types'] = array_merge( $cfg['suppress_issue_types'], [
	// Ignore temporarily so these can be fixed, not all at once
	'PhanUndeclaredTypeParameter',
	'PhanUndeclaredClassMethod',
	'PhanUndeclaredConstant',
	'PhanTypePossiblyInvalidDimOffset',
	'PhanTypeInvalidDimOffset',
	'PhanUndeclaredMethod',
	'PhanPluginDuplicateAdjacentStatement',
	'PhanUndeclaredClassInstanceof',
	'PhanTypeArraySuspiciousNullable',
	'PhanUndeclaredClass',
] );

$cfg['scalar_implicit_cast'] = true;

return $cfg;
