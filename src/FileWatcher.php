<?php

namespace xy2z\Capro;

/**
 * FileWatcher
 *
 * Watches for any new, modified or deleted files in the watched directories.
 *
 */
class FileWatcher {
	private $changes = false;
	private array $directories;
	private array $files = [];
	private int $files_checked = 0;

	/**
	 * Constructor
	 */
	public function __construct(array $directories) {
		$this->directories = $directories;
	}

	/**
	 * Check if any files in the directories are changed since last call.
	 */
	public function is_changed(): bool {
		// Has any file been added, modified or deleted since last check?
		// $this->last_files_count = count($this->files);
		$this->files_checked = 0; // reset.
		$this->changes = false;

		foreach ($this->directories as $directory) {
			if (!is_dir($directory)) {
				// tell('Dir does not exist: ' . $directory);
				continue;
			}
			$this->scandir($directory);
		}

		if ($this->changes) {
			// skip checking for deleted files, for faster response.
			return true;
		}

		// Check if any files from last scan was deleted.
		foreach ($this->files as $file => $details) {
			if (!file_exists($file)) {
				$this->changes = true;
				unset($this->files[$file]);
			}
		}

		return $this->changes;
	}

	/**
	 * Scan directories recursively if any files was added or modified since last check.
	 */
	private function scandir(string $directory): void {
		foreach (scandir($directory) as $item) {
			if ($item === '.' || $item === '..') {
				continue;
			}

			$item_full_path = $directory . DIRECTORY_SEPARATOR . $item;

			if (is_dir($item_full_path)) {
				$this->scandir($item_full_path);
			} else {
				$this->check_file($item_full_path);
			}
		}
	}

	/**
	 * Check if file is new or modified since last check.
	 */
	private function check_file(string $file): void {
		$this->files_checked++;
		if (!isset($this->files[$file])) {
			// New file. Add to array for next check.
			$this->changes = true;
			$this->add_update_file($file);
			return;
		}

		// Was file modified?
		if ($this->files[$file]->modified !== filemtime($file)) {
			// File was modified (or at least the modified timestamp was).
			$this->add_update_file($file);
			$this->changes = true;
			return;
		}
	}

	/**
	 * Add new file to the $files array, so it will can checked next time.
	 */
	private function add_update_file(string $file): void {
		$this->files[$file] = (object) [
			'modified' => filemtime($file),
		];
	}

	/**
	 * Get all scanned files.
	 */
	public function get_scanned_files(): array {
		return $this->files;
	}
}
