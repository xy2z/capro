<?php

use xy2z\Capro\Config;
use Symfony\Component\Yaml\Yaml;

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

			if (file_exists($item) && !@rmdir($item)) {
				throw new Exception('Could not delete directory: ' . $item);
			}
		} else {
			// Is file.
			if (file_exists($item) && !@unlink($item)) {
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

/**
 * Replace YAML placeholders (variables)
 * Will replace all `${{ ... }}` with the evaluated result of the php code inside.
 * Can be used for config(), env(), php functions, etc.
 * Examples:
 * 		core.disable_build: ${{ env('IS_PRODUCTION') }}
 * 		data: ${{ json_decode(config('app.data')) }}
 */
function replace_yaml_placeholders(string $str): string {
	$finder_pos = 0;

	while ($start = strpos($str, '${{', $finder_pos)) {
		$end = strpos($str, '}}', $start);
		// $finder_pos = $end; // this won't work because the $end pos will not be correct after str replace!
		$finder_pos = $start + 1;

		$str_to_replace = substr($str, $start, $end - $start + 2);
		$code = substr($str_to_replace, 3, -2);
		$eval = eval('return ' . $code . ';');

		// It has to be returned as a yaml string.
		if (is_array($eval)) {
			tell_error('Arrays are not supported for YAML placeholders yet. Please use json_encode() if needed.');
			continue;
		}

		if (is_object($eval)) {
			tell_error('Objects are not supported for YAML placeholders yet. Please use json_encode() if needed.');
			continue;
		}

		$str = str_replace($str_to_replace, Yaml::dump($eval), $str);
	}

	return $str;
}
