<?php

namespace xy2z\Capro\Commands;

use Generator;
use xy2z\Capro\View;
use xy2z\Capro\Capro;
use xy2z\Capro\ViewTemplate;
use xy2z\Capro\Config;
use xy2z\Capro\Commands\CommandInterface;
use xy2z\Capro\Commands\CommandServe;
use xy2z\Capro\Helpers;

/**
 * CommandBuild
 * Manages everything related to building - calls other classes.
 * When this is done I dont need: FileBuilder, Builder, PageBuilder, PostBuilder anymore !!
 */
class CommandBuild implements CommandInterface {
	/** @var array<View> */
	private array $views; // views are ALL "pages" (pages, posts, collections, etc.) but NOT layouts etc...

	private int $count_view_build_success = 0;

	private int $count_view_build_errors = 0;

	/** @var array<string> */
	protected array $tell_errors = [];

	public function __construct(array $argv) {
		Capro::$CommandBuild = $this;
	}

	public function run(): void {
		// Validate before build.
		Helpers::validate_in_capro_dir();

		// Validate dirs exists
		if (!is_dir(CAPRO_VIEWS_DIR)) {
			Helpers::tell_error('Views dir not found: ' . CAPRO_VIEWS_DIR);
		}

		// Start
		// Helpers::tell('Building site...');
		$this->rm_public_dir();
		$this->check_cache_dir();
		$this->copy_static_dir_to_public();

		// Get information about pages and collections.
		$this->load_views();

		// Build pages and collections.
		$this->build_views();

		// Done building.
		$this->finish();
	}

	private function rm_public_dir(): void {
		// Remove all content in the public dir (to keep chmod and chown)
		try {
			Helpers::rm_dir_content(CAPRO_PUBLIC_DIR);
		} catch (\Exception $e) {
			Helpers::tell_error('Error: ' . $e->getMessage() . ' - is the file/dir open?');
		}
		// Helpers::tell('✔ Cleared: public dir.');
	}

	private function check_cache_dir(): void {
		// Make cache dir if not exists.
		if (!is_dir(CAPRO_VIEWS_CACHE_DIR)) {
			if (mkdir(CAPRO_VIEWS_CACHE_DIR, 0775, true)) {
				Helpers::tell('✔ Created cache dir: ' . realpath(CAPRO_VIEWS_CACHE_DIR));
			} else {
				Helpers::tell_error('Error: Could not create cache dir: ' . CAPRO_VIEWS_CACHE_DIR);
			}
			return;
		}

		// Deprecated below: Why remove the cache dir on every build? then why have a cache dir at all?...
		// ---------------
		// Remove all content in the cache dir.
		/*
		try {
			Helpers::rm_dir_content(CAPRO_VIEWS_CACHE_DIR);
		} catch (\Exception $e) {
			Helpers::tell_error('Error: ' . $e->getMessage() . ' - is the file/dir open?');
		}
		Helpers::tell('✔ Cleared: cache dir.');
		*/
		// ---------------
	}

	private function copy_static_dir_to_public(): void {
		if (!is_dir(CAPRO_STATIC_DIR)) {
			// Helpers::tell('Notice: Static dir not found: ' . STATIC_DIR); // verbose. (but never for "capro new" commands)
			return;
		}

		Helpers::rcopy(CAPRO_STATIC_DIR, CAPRO_PUBLIC_DIR);
		// Helpers::tell('✔ Copied static content in to public directory.'); // verbose.
	}

	private static function is_filename_ignored(string $filename): bool {
		if (($filename === '.') || ($filename === '..')) {
			return true;
		}

		if (substr($filename, 0, 1) === '.') {
			// Ignore filename starting with "."
			return true;
		}

		return false;
	}

	private function load_views(): void {
		// Load Pages (dont build)
		$page_dir = CAPRO_VIEWS_DIR . DIRECTORY_SEPARATOR . 'pages'; // No trailing slashes!
		foreach ($this->get_all_pages($page_dir) as $file) {
			$view = new View(
				path: $file->path,
				type: View::TYPE_PAGE,
			);

			if ($view->is_build_disabled()) {
				Helpers::tell('Build disabled for: ' . $view->get('relative_path'));
				continue;
			}

			$this->views[strtolower($file->path)] = $view;
		}

		// Load Collections (dont build)
		$collections_dir = CAPRO_VIEWS_DIR . DIRECTORY_SEPARATOR . 'collections';
		foreach ($this->get_all_collections_views($collections_dir) as $file) {
			$view = new View(
				path: $file->path,
				type: View::TYPE_COLLECTION,
				label: $file->collection_dir
			);

			if ($view->is_build_disabled()) {
				Helpers::tell('Build disabled for: ' . $view->get('relative_path'));
				continue;
			}

			$this->views[strtolower($file->path)] = $view;
		}

		// Load view templates.
		$this->load_view_templates();
	}

	protected function get_all_pages(string $dir): Generator {
		foreach (scandir($dir) as $item) {
			if (self::is_filename_ignored($item)) {
				continue;
			}

			$fullpath = $dir . DIRECTORY_SEPARATOR . $item;
			if (is_dir($fullpath)) {
				yield from $this->get_all_pages($fullpath);
			} else {
				yield (object) [
					'path' => $fullpath,
				];
			}
		}
	}

	protected function get_all_collections_views(string $dir): Generator {
		if (!is_dir($dir)) {
			return;
		}

		foreach (scandir($dir) as $collection_dir) {
			if (($collection_dir === '.') || ($collection_dir === '..')) {
				continue;
			}

			$fullpath = $dir . DIRECTORY_SEPARATOR . $collection_dir;
			foreach ($this->get_all_pages($fullpath) as $file) {
				$file->collection_dir = $collection_dir;
				yield $file;
			}
		}
	}

	/**
	 * Build and save each view from blade to html.
	 */
	protected function build_views(): void {
		foreach ($this->views as $path => $view) {
			if ($view->build()) {
				// Helpers::tell('✔ Successfully build: ' . $view->get('relative_path'));
				$this->count_view_build_success++;
			} else {
				Helpers::tell('Warning: Could not build: ' . $view->get('relative_path'));
				$this->count_view_build_errors++;
			}
		}

		// Delete temporary file build.
		$tmp_path = CAPRO_VIEWS_DIR . '/__tmp.blade.php';
		@unlink($tmp_path);
	}

	protected function load_view_templates(): void {
		$templates = Config::get('core.templates', []);
		if (!is_array($templates)) {
			$this->add_error('Config core.templates is not an array.');
			return;
		}

		foreach ($templates as $template) {
			if (!$template instanceof ViewTemplate) {
				$this->add_error('Template is not a ViewTemplate instance. See docs.');
				continue;
			}

			foreach ($template->load_views() as $view) {
				$this->views[strtolower($view->get('save_path'))] = $view;
			}
		}
	}

	protected function finish(): void {
		// Stop timer
		if (CommandServe::$time_start_build > 0) {
			// Timer was started by "serve", so it will reset for every build.
			$build_time = number_format(microtime(true) - CommandServe::$time_start_build, 2);
		} else {
			$build_time = number_format(microtime(true) - CAPRO_START_TIME, 2);
		}

		// Print success / errors.
		if ($this->count_view_build_errors) {
			if ($this->tell_errors) {
				foreach ($this->tell_errors as $msg) {
					Helpers::tell($msg);
				}
			}
			Helpers::tell('❌ Done with ' . $this->count_view_build_errors . ' errors. (in ' . $build_time . ' seconds)');
		} else {
			Helpers::tell('✔ Done in ' . $build_time . ' seconds. ' . $this->count_view_build_success . ' views build.');
		}
	}

	/** @return array<View> */
	public function get_views(): array {
		return $this->views;
	}

	public function add_error(string $message): void {
		$this->count_view_build_errors++;
		$this->tell_errors[] = $message;
	}
}
