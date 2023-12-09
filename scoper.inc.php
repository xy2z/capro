<?php

// php-scoper config
// build using: `composer build`
// IMPORTANT: After each build, go in "build" and run "composer dump" to fix the autoloader issue.

/*
[ Current issues ]
[FIXED] "php capro config" fails because of wrong namespace?
- Research if it's possible to keep everything in one repo? So I don't have to split it up in 2 repo's. (then i have to build a phar right?)
	- Actually maybe, if all composer requirements are '--dev', but then install has to have '--no-dev'
- Remove dev dependencies from build, eg. phpstan, phpunit, php-cs-fixer, etc.

Issues for later:
- Use tabs instead of spaces in build (if possible?)
*/

return [

	// [ Excludes ]
	'exclude-namespaces' => [
		// 'xy2z', // doesn't work for some reason, for LiteConfig (neither does empty array...)
		'xy2z\Capro',
	],
	'exclude-files' => [
		// 'capro',
		// 'src/capro_bootstrap.php',
		// 'src/helpers.php'
		'src/blade_helpers.php',
	],
	'exclude-classes' => [],
	'exclude-functions' => [],
	'exclude-constants' => ['/^CAPRO_/'],

	// [ Exposes ]
	// 'expose-global-functions' => true, // test for '\Illuminate\View\tap()' - No don't, because this will overwrite the 'config()' function, and probably others. Only expose the functions that are needed (that break the code if not exposed)
	// 'expose-functions' => ['PHPUnit\execute_tests', '/regex/'],
	// Laravel Illuminate functions
	'expose-functions' => ['tap', 'collect', 'data_get', 'last', 'e'],
];
