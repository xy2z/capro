<?php

namespace xy2z\Capro;

define('CAPRO_VERSION', '1.0.0-alpha.33');

// Get root dir of the phar file.
// Can't use __DIR__ because that could return the .phar file.
// If in phar, get the DIR that the phar is in. If not, get the root dir of the project.
if (substr(CAPRO_DIR, 0, 7) === 'phar://') {
	$root_dir = dirname(substr(CAPRO_DIR, 7), 1); // in phar.
} else {
	$root_dir = CAPRO_DIR; // not in phar.
}

define('CAPRO_ROOT_DIR', $root_dir);

function capro_path_format(string $path): string {
	// Replace all slashes with "/" so it's easier to compare.
	return trim(str_replace('\\', '/', $path));
}

function capro_path_match_exact(string $path1, string $path2): bool {
	return (capro_path_format(strtolower($path1)) === capro_path_format(strtolower($path2)));
}

function capro_path_match_partial(string $haystack, string $needle): bool {
	return strpos(capro_path_format(strtolower($haystack)), capro_path_format(strtolower($needle))) !== false;
}

/**
 * capro_is_global_bin()
 *
 * Used for checking if user ran "capro" or "vendor/bin/capro" in a project, to determine
 * if this is the global composer package (capro) or is a project package (vendor/bin/capro)
 *
 * A solution to this is to get the COMPOSER_HOME dir by running `composer config --global home`
 * If we are in that directory, then it is the global binary.
 * However, this wouldn't work if COMPOSER is not installed in PATH or set as alias.
 */
function capro_is_global_bin_v2(): bool {
	// ONLY cache for global_bin, as the other is not really used, and can be done later if needed.
	// Must start with ".capro-cache" as it is in the .gitignore.
	$global_cache_path = CAPRO_ROOT_DIR . '/.capro-cache-global';

	// First, check if we have cache so we don't have to run the composer command.
	if (file_exists($global_cache_path)) {
		return true;
	}

	// Cache not found.
	// Check for common composer directories matches.
	if (
		capro_path_match_partial(CAPRO_DIR, '/composer/vendor/xy2z/capro-build-test')
		|| capro_path_match_partial(CAPRO_DIR, '/.composer/vendor/xy2z/capro-build-test')
	) {
		// We are in global path.
		touch($global_cache_path); // cache it for next time.
		return true;
	}

	// Next, we can try to get the global path from Composer.
	// This requires "composer" to be installed in PATH, so it might not work.
	if (PHP_OS_FAMILY === 'Windows') {
		// Windows.
		$composer_home = shell_exec('composer config --global home 2> nul');
	} elseif (PHP_OS_FAMILY === 'Linux') {
		// Linux
		$composer_home = shell_exec('composer config --global home 2> /dev/null');
	} else {
		// Other...?
		$composer_home = shell_exec('composer config --global home');
	}

	// Remove linebreaks etc. Notice: turns possible NULL value into empty string.
	$composer_home = trim($composer_home);

	if (!empty($composer_home)) {
		// The command did not fail.
		if (capro_path_match_exact(CAPRO_DIR, $composer_home . '/vendor/xy2z/capro-build-test')) {
			touch($global_cache_path); // cache it for next time.
			return true;
		}

		// If it's not global then it must be a project directory.
		return false;
	}

	// If we made it here it's most likely because "composer" isn't installed in PATH,
	// which probably means composer's global bin directory isn't installed in PATH either,
	// so we assume the user is in a project directory. But we won't cache it for now.
	return false;
}

// Check if we should passthru to another capro binary.
// This should do the following:
// If running just "capro" (the global composer package) it should call the local project "vendor/bin/capro" if it
// exists, as a shortcut.

// But - if you are in a project-A and run `path/to/project-B/vendor/bin/capro` it should NOT passthru to the
// project-A capro file.

// And if you already are in a project with vendor/bin/capro it should not go in endless loop and call itself.
if (file_exists(getcwd() . '/vendor/bin/capro')) {
	// If these 2 are equal, we should not call anymore, or else it will be an endless loop.
	// Todo: Fix this $project_bin_path as it's actually empty and it should not be?!
	$project_bin_path = realpath(CAPRO_ROOT_DIR . '/../../bin/capro');
	$dir_bin_path = getcwd() . '/vendor/bin/capro';

	if (capro_path_match_exact($project_bin_path, $dir_bin_path)) {
		// The vendor dir is the same as the one we are in, so continue to not get in a infinite loop.
	} elseif (capro_is_global_bin_v2()) {
		// Only pass if this is the global binary, or else it would not be possible to call other projects binary files
		// if you are in Project-A and call `path/to/project-B/vendor/bin/capro` it would still pass it back to the
		// Project-A - and we do not want that.
		passthru(getcwd() . '/vendor/bin/capro ' . implode(' ', array_slice($argv, 1)));
		exit;
	}
}
