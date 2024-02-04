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

	// Finders is not needed anymore, since Box is adding the files needed in "box.json.dist"
	// php-scoper add-prefix tests src stubs .gitattributes .gitignore capro vendor composer.json --force || exit
	// 'finders' => [
	// 	Finder::create()->files()->in('src'),
	// 	Finder::create()->files()->in('tests'),
	// 	Finder::create()->files()->in('stubs'),
	// 	// Finder::create()->files()->in('vendor'),
	// 	Finder::create()->append([
	// 		'.gitattributes',
	// 		'.gitignore',
	// 		'capro',
	// 		'composer.json',
	// 		// 'composer.lock', // ??
	// 	])
	// ],

	// [ Excludes ]
	'exclude-namespaces' => [
		// 'xy2z', // doesn't work for some reason, for LiteConfig (neither does empty array...)
		'xy2z\Capro',
	],
	'exclude-files' => [
		'src/blade_helpers.php',
	],
	'exclude-classes' => [],
	'exclude-functions' => [],
	'exclude-constants' => ['/^CAPRO_/'],

	// [ Exposes ]
	// 'expose-global-functions' => true, // test for '\Illuminate\View\tap()' - No don't, because this will overwrite the 'config()' function, and probably others. Only expose the functions that are needed (that break the code if not exposed)
	// Laravel Illuminate functions
	'expose-functions' => ['tap', 'collect', 'data_get', 'last', 'e', 'env', 'value'],

	// [ Patchers ]
	'patchers' => [
		static function (string $filePath, string $prefix, string $content): string {

			// Replace "CAPRO_PHAR_SCOPE" for all files in the project.
			if (strpos($filePath, 'vendor') === false) {
				// Remember that scoper/box has added a "\" in front of the constant so it's `\CAPRO_PHAR_SCOPE` that needs to be replaced.
				$content = str_replace('\CAPRO_PHAR_SCOPE', "'\\" . $prefix . "'", $content);
			}

			if (strpos($filePath, 'vendor/illuminate') === 0) {
				$content = str_replace('(\\\\Illuminate\\\\', '(' . $prefix . '\\\\Illuminate\\\\', $content);
				$content = str_replace(' \\\\Illuminate\\\\', ' ' . $prefix . '\\\\Illuminate\\\\', $content);
			}

			return $content;
		},
	],

];
