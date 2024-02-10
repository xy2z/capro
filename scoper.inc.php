<?php

// php-scoper config - used by box on build.
// build using: `composer build`
// Finders is not needed, since Box is adding the files needed in "box.json.dist"

return [
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
