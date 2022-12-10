<?php

namespace xy2z\Capro\Commands;

/**
 * CommandNewSite
 *
 * Manages everything related to making a new capro project (directory).
 */
class CommandNewSite implements CommandInterface {
	protected string $site_name;

	/** @param array<string> $argv */
	public function __construct(array $argv) {
		if (!isset($argv[2])) {
			tell('Error: Need a sitename. "capro new [sitename]');
			exit;
		}

		$this->site_name = $argv[2];
	}

	public function run(): void {
		// Create new capro site using stubs, setup git and composer, etc.
		// Make sure to have as much as possible in the "stubs" dir - and only run commands here.

		tell('Creating new site: ' . $this->site_name);

		if (is_dir($this->site_name)) {
			// Directory exists.
			tell('Error: The directory "' . $this->site_name . '" already exists.');
			if (!confirm('The directory already exists, are you sure you want to possibly overwrite files? [Y to continue]')) {
				exit;
			}
		} else {
			mkdir($this->site_name);
		}

		rcopy(__DIR__ . '/../../stubs/new-site/', $this->site_name);
		chdir($this->site_name);
		passthru('composer install');
		echo PHP_EOL;

		// Bulild the new site.
		passthru('php vendor/bin/capro build');
		echo PHP_EOL;
		tell('✔ Done. Your new site has been setup and build. Run `php -S localhost:8000` in the ' . $this->site_name . '/public directory to see your site.');
	}
}
