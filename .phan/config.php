<?php
$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

$cfg['suppress_issue_types'] = array_merge( $cfg['suppress_issue_types'], [
	// Ignore temporarily so these can be fixed, not all at once
	'PhanUndeclaredInterface',
	'PhanUndeclaredTypeParameter',
	'PhanUndeclaredClassMethod',
	'PhanUndeclaredFunction',
	'PhanUndeclaredConstant',
	'PhanUndeclaredTypeReturnType',
	'PhanPossiblyUndeclaredVariable',
	'PhanTypePossiblyInvalidDimOffset',
	'PhanTypeInvalidDimOffset',
	'PhanUndeclaredConstantOfClass',
	'PhanUndeclaredExtendedClass',
	'PhanTypeMismatchReturnNullable',
	'PhanUndeclaredExtendedClass',
	'PhanUndeclaredMethod',
	'PhanUndeclaredProperty',
	'PhanPluginDuplicateAdjacentStatement',
	'PhanImpossibleCondition',
	'PhanUndeclaredClassInstanceof',
	'PhanTypeArraySuspiciousNullable',
	'PhanParamTooMany',
	'PhanPluginDuplicateConditionalNullCoalescing',
	'PhanRedundantCondition',
	'PhanPluginDuplicateConditionalTernaryDuplication',
	'PhanTypeMismatchReturnProbablyReal',
	'PhanUndeclaredVariableDim',
	'PhanUndeclaredClass',
	'PhanUnextractableAnnotation',
	'PhanTypeSuspiciousStringExpression',
	'PhanImpossibleTypeComparison',
	'PhanTypeMismatchArgument'
] );

return $cfg;
