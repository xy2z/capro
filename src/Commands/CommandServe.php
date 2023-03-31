<?php

namespace xy2z\Capro\Commands;

use xy2z\Capro\Config;
use Statix\Server\Server;
use xy2z\Capro\Commands\CommandBuild;
use xy2z\Capro\FileWatcher;

/**
 * CommandServe
 * Serve the public directory for development.
 */
class CommandServe implements CommandInterface {
	/** @var array<mixed> */
	protected array $argv;
	protected Server $server;
	protected bool $quiet = false;
	public static float $time_start_build = 0;
	private FileWatcher $FileWatcher;

	/** @param array<string> $argv */
	public function __construct(array $argv) {
		$this->argv = $argv;

		// Quiet mode?
		if (in_array('-q', $this->argv) || in_array('--quiet', $this->argv)) {
			$this->quiet = true;
		}
	}

	/**
	 * Run the "serve" command.
	 */
	public function run(): void {
		$this->start_server();

		// Prepare file watcher, to watch for any changes.
		$this->FileWatcher = new FileWatcher([
			VIEWS_DIR,
			STATIC_DIR,
			CONFIG_DIR,
		]);

		while ($this->server->isRunning()) {
			// Keep cli running...

			if ($this->FileWatcher->is_changed()) {
				// Changes detected. Start building.
				$this->rebuild();
			}

			usleep(500_000);
		}
	}

	/**
	 * Start the development server
	 */
	private function start_server(): void {
		$this->server = Server::new()
			->host(config('core.serve_host', '127.0.0.1'))
			->port(config('core.serve_port', '82'))
			->root(PUBLIC_DIR);

		if (file_exists(SITE_ROOT_DIR . '/.env')) {
			$this->server->withEnvFile(SITE_ROOT_DIR . '/.env');
		}

		$this->server->runInBackground();
		if (!$this->quiet) {
			tell('Capro development server started at ' . $this->server->getAddress());
		}
	}

	/**
	 * Rebuild the site (triggered on file event)
	 */
	private function rebuild(): void {
		// Restart build timer.
		static::$time_start_build = microtime(true);

		// Reload config variables.
		Config::reload();

		// Build (quietly)
		ob_start();
		(new CommandBuild([]))->run();
		$out = ob_get_clean();

		if (!$this->quiet && $out) {
			// Print only last line ("Done ...")
			$lines = explode(PHP_EOL, $out);
			$lastline = $lines[count($lines) - 2];
			tell('[' . date('H:i:s') . ']' . substr($lastline, 2));
		}
	}
}
