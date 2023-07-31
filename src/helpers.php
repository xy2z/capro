<?php

use xy2z\Capro\Config;

// Print messages to CLI.
function tell(string $msg): void {
	echo '» ' . $msg . PHP_EOL;
}

function tell_error(string $msg, bool $exit = true): void {
	echo '❌ ' . $msg . PHP_EOL;
	if ($exit) {
		exit(1);
	}
}

// Recursive copy directory content from $src to $dest
function rcopy(string $src, string $dest): void {
	if (!is_dir($dest)) {
		mkdir($dest);
	}

	$content = glob($src . '/{,.}*', GLOB_BRACE);

	foreach ($content as $path) {
		$basename = basename($path);
		if ($basename == '.' || $basename == '..') {
			continue;
		}

		if (is_dir($path)) {
			rcopy($path, $dest . '/' . $basename);
		} else {
			// Copy single file
			copy($path, $dest . '/' . $basename);
		}
	}
}

/**
 * rm_dir_content()
 * This will delete all contents in the directory (including subdirs and hidden files)
 * But will not actually delete the dir itself (to keep original chmod and chown)
 */
function rm_dir_content(string $dir): bool {
	foreach (glob($dir . '/{,.}*', GLOB_BRACE) as $item) {
		if ((substr($item, -2) === '/.') || (substr($item, -3) === '/..')) {
			continue;
		}

		if (is_dir($item)) {
			rm_dir_content($item);

			$rm_sub_dir = rmdir($item);
			if (!$rm_sub_dir) {
				throw new Exception('Could not delete dir: ' . $item);
			}
		} else {
			$unlink = unlink($item);
			if (!$unlink) {
				throw new Exception('Could not delete file: ' . $item);
			}
		}
	}

	return true;
}

/**
 * confirm()
 * CLI: Ask user a question, and if $yes matches we return true, false otherwise.
 *
 * @param string $question
 * @param array<string> $yes
 *
 * @return bool
 */
function confirm(string $question, array $yes = ['y', 'yes']): bool {
	tell('⚠ ' . $question);
	$stdin = fopen('php://stdin', 'r');
	$line = strtolower(trim(fgets($stdin)));
	fclose($stdin);

	if (in_array($line, $yes)) {
		return true;
	}

	return false;
}

/**
 * config()
 * A shorthand for writing Config::get($key), can be used in view files.
 *
 * @param string $key
 * @param mixed $default
 *
 * @return mixed
 */
function config(string $key, mixed $default = null): mixed {
	return Config::get($key, $default);
}

/**
 * Validate cwd (current working dir) is a capro project directory. Exists if not.
 * @return void
 */
function validate_in_capro_dir(): void {
	// Validate that the current working dir (cwd) is a capro project directory.
	if (!file_exists(getcwd() . '/composer.json') && !file_exists(getcwd() . '/vendor/bin/capro')) {
		tell('Sorry, it doesnt look like you are in a capro directory...');
		exit;
	}
}
