<?php

namespace xy2z\Capro;

use xy2z\Capro\Config;
use Symfony\Component\Yaml\Yaml;

abstract class Helpers {
	// Print messages to CLI.
	public static function tell(string $msg): void {
		echo '» ' . $msg . PHP_EOL;
	}

	public static function tell_error(string $msg, bool $exit = true): void {
		echo '❌ ' . $msg . PHP_EOL;
		if ($exit) {
			exit(1);
		}
	}

	// Recursive copy directory content from $dir to $dest
	public static function rcopy(string $dir, string $dest): void {
		if (!is_dir($dest)) {
			mkdir($dest);
		}

		// Notice: PHAR files does not support glob().
		foreach (scandir($dir) as $basename) {
			// $basename = basename($path);
			$path = $dir . DIRECTORY_SEPARATOR . $basename;

			if ($basename == '.' || $basename == '..') {
				continue;
			}

			if (is_dir($path)) {
				self::rcopy($path, $dest . DIRECTORY_SEPARATOR . $basename);
			} else {
				// Copy single file
				copy($path, $dest . DIRECTORY_SEPARATOR . $basename);
			}
		}
	}

	/**
	 * rm_dir_content()
	 * This will delete all contents in the directory (including subdirs and hidden files)
	 * But will not actually delete the dir itself (to keep original chmod and chown)
	 */
	public static function rm_dir_content(string $dir): bool {
		foreach (glob($dir . '/{,.}*', GLOB_BRACE) as $item) {
			if ((substr($item, -2) === '/.') || (substr($item, -3) === '/..')) {
				continue;
			}

			if (is_dir($item)) {
				self::rm_dir_content($item);

				if (file_exists($item) && !@rmdir($item)) {
					throw new \Exception('Could not delete directory: ' . $item);
				}
			} else {
				// Is file.
				if (file_exists($item) && !@unlink($item)) {
					throw new \Exception('Could not delete file: ' . $item);
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
	public static function confirm(string $question, array $yes = ['y', 'yes']): bool {
		self::tell('⚠ ' . $question);
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
	public static function config(string $key, mixed $default = null): mixed {
		return Config::get($key, $default);
	}

	/**
	 * Validate cwd (current working dir) is a capro project directory. Exists if not.
	 * @return void
	 */
	public static function validate_in_capro_dir(): void {
		// Validate that the current working dir (cwd) is a capro project directory.
		if (!file_exists(getcwd() . '/composer.json') && !file_exists(getcwd() . '/vendor/bin/capro')) {
			self::tell('Sorry, it doesnt look like you are in a capro directory...');
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
	public static function replace_yaml_placeholders(string $str): string {
		$finder_pos = 0;

		while ($start = strpos($str, '${{', $finder_pos)) {
			$end = strpos($str, '}}', $start);
			// $finder_pos = $end; // this won't work because the $end pos will not be correct after str replace!
			$finder_pos = $start + 1;

			$str_to_replace = substr($str, $start, $end - $start + 2);
			$code = substr($str_to_replace, 3, -2);
			// TODO: Try to just require blade_hlelpers.php in the eval, just like for blade files, so it's all using the same classes/functions.
			$eval = eval('
				use xy2z\Capro\Config;
				use xy2z\Capro\Capro;
				if (!function_exists("config")) {
					function config($key, $default = null) {
						return Config::get($key, $default);
					}
				}
				return ' . $code . ';
			');

			// It has to be returned as a yaml string.
			if (is_array($eval)) {
				// TODO: Consider just making this json_encoded as default, instead of sending an error... (and then maybe show it as NOTICE?)
				self::tell_error('Arrays are not supported for YAML placeholders yet. Please use json_encode() if needed.');
				continue;
			}

			if (is_object($eval)) {
				// TODO: Consider just making this json_encoded as default, instead of sending an error... (and then maybe show it as NOTICE?)
				self::tell_error('Objects are not supported for YAML placeholders yet. Please use json_encode() if needed.');
				continue;
			}

			$str = str_replace($str_to_replace, Yaml::dump($eval), $str);
		}

		return $str;
	}

	/**
	 * Fix Linebreaks, so we only use "\n" as linebreaks
	 * So it's easier to do string comparisons.
	 * also see https://www.sttmedia.com/newline
	 *
	 * @param string $str Global, for performance.
	 * @return void
	 */
	public static function fix_linebreaks(string &$str): void {
		$str = str_replace("\r\n", "\n", $str); // windows
		$str = str_replace("\r", "\n", $str); // mac os 9 and older
	}
}
