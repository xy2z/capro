<?php

define('CAPRO_TIME_START_BUILD', microtime(true));
define('CAPRO_VERSION', '1.0.0-alpha.31');

/**
 * is_global_bin()
 *
 * Used for checking if user ran "capro" or "vendor/bin/capro" in a project, to determine
 * if this is the global composer package (capro) or is a project package (vendor/bin/capro)
 *
 * A solution to this is to get the COMPOSER_HOME dir by running `composer config --global home`
 * If we are in that directory, then it is the global binary.
 * However, this wouldn't work if COMPOSER is not installed in PATH or set as alias.
 */
function is_global_bin(string $dir): bool {
	// Must start with ".capro-cache" as it is in the .gitignore.
	$is_global_filename = '/.capro-cache-is-global-package';
	$not_global_filename = '/.capro-cache-local';

	// First, check if we have cache so we don't have to run the composer command.
	if (file_exists($dir . $is_global_filename)) {
		return true;
	}
	if (file_exists($dir . $not_global_filename)) {
		return false;
	}

	// Cache not found.
	// Check if the directory matches "Composer/vendor/bin/capro" or ".composer/vendor/bin/capro"
	if (
		(strpos(strtolower($dir), str_replace('/', DIRECTORY_SEPARATOR, '/composer/vendor/xy2z/capro')) !== false)
		|| (strpos(strtolower($dir), str_replace('/', DIRECTORY_SEPARATOR, '/.composer/vendor/xy2z/capro')) !== false)
	) {
		// We are in global path.
		touch($dir . $is_global_filename); // cache it for next time.
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

	if (!is_null($composer_home)) {
		// The command did not fail.
		if ($dir === realpath($composer_home . '/vendor/xy2z/capro')) {
			touch($dir . $is_global_filename); // cache it for next time.
			return true;
		}

		// If it's not global then it must be a project directory.
		touch($dir . $not_global_filename); // cache it for next time.
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
	$project_bin_path = realpath(__DIR__ . '/../../bin/capro');
	$dir_bin_path = realpath(getcwd() . '/vendor/bin/capro');
	if ($project_bin_path === $dir_bin_path) {
		// Always continue. NEVER call any other script, or else it will loop endlessly.
	} elseif (is_global_bin(__DIR__)) {
		// Only pass if this is the global binary, or else it would not be possible to call other projects binary files
		// if you are in Project-A and call `path/to/project-B/vendor/bin/capro` it would still pass it back to the
		// Project-A - and we do not want that.
		passthru(getcwd() . '/vendor/bin/capro ' . implode(' ', array_slice($argv, 1)));
		exit;
	}
}
