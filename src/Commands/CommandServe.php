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
	private string $host;
	private string $port;

	/** @param array<string> $argv */
	public function __construct(array $argv) {
		$this->argv = $argv;
		$this->host = config('core.serve_host', '127.0.0.1');
		$this->port = config('core.serve_port', '82');

		// Quiet mode?
		if (in_array('-q', $this->argv) || in_array('--quiet', $this->argv)) {
			$this->quiet = true;
		}
	}

	/**
	 * Run the "serve" command.
	 */
	public function run(): void {
		validate_in_capro_dir();

		if ($this->is_port_busy()) {
			tell('Error: Port ' .  $this->port . ' is busy.');
			return;
		}

		$this->start_server();

		// Prepare file watcher, to watch for any changes.
		$this->FileWatcher = new FileWatcher([
			// Watch dirs
			CAPRO_VIEWS_DIR,
			CAPRO_STATIC_DIR,
			CAPRO_CONFIG_DIR,
		], [
			// Exclude paths
			CAPRO_VIEWS_CACHE_DIR,
		]);

		while ($this->server->isRunning()) {
			// Keep cli running...

			if ($this->FileWatcher->is_changed()) {
				// Changes detected. Start building.
				usleep(100_000);
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
			->host($this->host)
			->port($this->port)
			->root(CAPRO_PUBLIC_DIR);

		if (file_exists(CAPRO_SITE_ROOT_DIR . '/.env')) {
			$this->server->withEnvFile(CAPRO_SITE_ROOT_DIR . '/.env');
		}

		$this->server->runInBackground();
		if (!$this->quiet) {
			tell('Capro development server started at ' . $this->server->getAddress());
		}
	}

	/**
	 * Check if port is busy (already in use)
	 *
	 * @return bool
	 */
	private function is_port_busy(): bool {
		$connection = @fsockopen($this->host, intval($this->port), $error_code, $error_message, 0.2);

		if (is_resource($connection)) {
			// Port is busy.
			fclose($connection);
			return true;
		}

		return false;
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
			echo $out;
		}
	}
}
