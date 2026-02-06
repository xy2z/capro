<?php

namespace xy2z\Capro\Commands;

use xy2z\Capro\Config;

use function dump;

/**
 * CommandConfig
 * Debug config in the command line interface.
 */
class CommandConfig implements CommandInterface {
	/** @var array<mixed> */
	protected array $argv;
	protected bool $var_dump = false;
	protected bool $json = true;
	protected bool $json_min = true;

	/** @param array<string> $argv */
	public function __construct(array $argv) {
		$this->argv = $argv;
		$this->var_dump = (in_array('-d', $argv) || in_array('--dump', $argv));
		$this->json = in_array('--json', $argv);
		$this->json_min = in_array('--json-min', $argv);
	}

	/**
	 * Run the command.
	 */
	public function run(): void {
		if ($this->var_dump) {
			var_dump($this->get_result());
		} elseif ($this->json) {
			echo json_encode($this->get_result(), JSON_PRETTY_PRINT);
		} elseif ($this->json_min) {
			echo json_encode($this->get_result());
		} else {
			dump($this->get_result());
		}
	}

	/**
	 * Return either ALL config values, or the one's specificed in arguments.
	 * Eg `capro config app.title app.email core` should output all 3 variables.
	 */
	private function get_result(): mixed {
		$result = [];
		foreach (array_slice($this->argv, 2) as $arg) {
			// Return the first argument that isn't a flag/option.
			if (isset($arg) && (substr($arg, 0, 1) !== '-')) {
				// if (Config::has($arg)) { // TODO: Awaiting LiteConfig.
				$result[$arg] = Config::get($arg);
				// }
			}
		}
		if ($result) {
			return $result;
		}

		// No arguments passed, so we return all config variables.
		return Config::all();
	}
}
