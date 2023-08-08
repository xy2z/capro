<?php

namespace xy2z\Capro;

// A "view" is every page and collection. I's not assets like layouts, etc.
// This is so each "page" (or collection which is also rendered as a page) - gets their own class.
// This should also replace "Items" class - since all views and variables are collected here instead.

use Exception;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Jenssegers\Blade\Blade;
use xy2z\Capro\PublicView;
use Spatie\YamlFrontMatter\Document;
use Throwable;

class View {
	protected string $path;
	protected string $dir;
	protected string $relative_path; // needed for cli messages (errors).
	protected string $basename;
	protected string $label;
	protected string $save_path; // full path to where the build view file (html) will be saved.
	protected string $href;
	protected int $type; // True = is page. False = is collection item.
	protected Document $yaml_front_matter;

	public const TYPE_PAGE = 0;
	public const TYPE_COLLECTION = 1;
	public const TYPE_TEMPLATE = 2;

	/**
	 * Data for the view so it can access variables.
	 * @var array<mixed>
	 */
	protected array $view_data = [];

	protected static Blade $blade;

	/**
	 * Construct a new view (page or collection item)
	 */
	public function __construct(string $path, int $type, string $label = '', string $save_path = '') {
		$this->path = $path;
		$this->type = $type;
		$this->dir = dirname($this->path);
		if (!empty($save_path)) {
			$this->save_path = $save_path;
		}

		$this->label = $label; // "collection" label.
		$this->basename = str_replace('.blade.php', '', basename($this->path));
		$this->relative_path = str_replace(SITE_ROOT_DIR, '', $this->path);

		$file_content = @file_get_contents($this->path) ?: '';

		if ($file_content) {
			// Replace YAML placeholders (variables).
			// Only do replacement on the yaml section.
			$first = strpos($file_content, '---' . PHP_EOL);
			$find_yaml_end_string = PHP_EOL . '---' . PHP_EOL;
			$second = strpos($file_content, $find_yaml_end_string, $first + 3);
			if (($first !== false) && ($second !== false)) {
				$yaml_section = substr($file_content, 0, $second + strlen($find_yaml_end_string));
				$yaml_section = replace_yaml_placeholders($yaml_section);

				// Merge file-content back together with the replaced yaml section.
				$blade_section = substr($file_content, $second + strlen($find_yaml_end_string));
				$file_content = $yaml_section . $blade_section;
			}
		}

		try {
			$this->yaml_front_matter = YamlFrontMatter::parse($file_content);
		} catch (Throwable $e) {
			tell('Error: Invalid yaml in ' . $this->relative_path . '. ' . $e->getMessage());
			exit;
		}

		// Set save_path
		$this->set_save_path();
	}

	private function set_save_path(): void {
		if ($this->type === self::TYPE_TEMPLATE) {
			return;
		}

		if ($this->type === self::TYPE_PAGE) {
			$relative_dir = str_replace(VIEWS_DIR . DIRECTORY_SEPARATOR . 'pages', '', $this->dir);
		} elseif ($this->type === self::TYPE_COLLECTION) {
			$relative_dir = str_replace(VIEWS_DIR . DIRECTORY_SEPARATOR . 'collections', '', $this->dir);
		} else {
			throw new Exception('Unknown View type.');
		}

		if (!empty($this->get_view_data('core.save_as'))) {
			$save_as = $this->get_view_data('core.save_as');
			$this->href = str_replace('\\', '/', $relative_dir) . '/' . $save_as;
			$this->save_path = PUBLIC_DIR . $relative_dir . DIRECTORY_SEPARATOR . $save_as;
			return;
		}

		if (($this->basename == 'index') && empty($relative_dir)) {
			// Root index.html
			$this->href = '/';
			$this->save_path = PUBLIC_DIR . DIRECTORY_SEPARATOR . 'index.html';
		} else {
			if ($this->basename == 'index') {
				// Place the file as index.html
				$this->href = str_replace('\\', '/', $relative_dir) . '/';
				$this->save_path = PUBLIC_DIR . $relative_dir . DIRECTORY_SEPARATOR . 'index.html';
			} else {
				// Not "index" file, so make a dir for the basename.
				$this->href = str_replace('\\', '/', $relative_dir) . '/' . $this->basename . '/';
				$this->save_path = PUBLIC_DIR . $relative_dir . DIRECTORY_SEPARATOR . $this->basename . DIRECTORY_SEPARATOR . 'index.html';
			}
		}
	}

	/** @param array<mixed> $data */
	public function set_view_data(array $data): void {
		$this->view_data = $data;
	}

	public function set_href(string $href): void {
		$this->href = $href;
	}

	public function get_view_data(mixed $key = null): mixed {
		$data =  array_merge($this->view_data, $this->yaml_front_matter->matter(), [
			'self' => $this->get_public_view(),
		]);

		if (isset($key)) {
			// Return only the specific key.
			return $data[$key] ?? null;
		}

		// Return all data.
		return $data;
	}

	/**
	 * Get property or yaml-front-matter value.
	 */
	public function get(string $param): mixed {
		if (isset($this->$param)) {
			return $this->$param;
		} else {
			// Try look in the yaml_front_matter.
			return $this->get_view_data($param);
		}
	}

	public function build(): bool {
		// Prepare blade.
		self::load_blade();

		// Make directory for the file in the PUBLIC dir, since it will always be saved as 'index.html'.
		$save_path_dir = dirname($this->save_path);
		if (!is_dir($save_path_dir)) {
			mkdir($save_path_dir, 0775, true);
		}

		// Save the file without the yaml-front-matter in a temp location.
		// This is needed because Blade cannot make from a string, it needs to be an actual file.
		// TODO: Refactor when possible.
		$saved = file_put_contents(VIEWS_DIR . '/__tmp.blade.php', $this->yaml_front_matter->body());

		if ($saved === false) {
			throw new Exception('Could not build view.'); // TODO: If this is in CommandServe, just retry in a few microseconds...
		}

		// Build (make view)
		$build_content = null;
		try {
			$make = self::$blade->make('__tmp', $this->get_view_data());
			// $build_content = $make->__toString(); // Do this here so it can throw exceptions.
			$build_content = strval($make); // Do this here so it can throw exceptions.
		} catch (Throwable $e) {
			tell_error('Error in ' . $this->relative_path . ': ' . $e->getMessage());
		}

		if (is_null($build_content)) {
			// Error.
			return false;
		}

		// Save file
		return $this->save_build_file($build_content);
	}

	protected static function load_blade(): void {
		if (isset(self::$blade)) {
			// Blade is already loaded.
			return;
		}

		self::$blade = new Blade(VIEWS_DIR, VIEWS_CACHE_DIR);
		class_alias('xy2z\\Capro\\Config', 'Config');

		self::$blade->directive('markdown', function () {
			return '<?php ob_start(); ?>';
		});

		self::$blade->directive('endmarkdown', function () {
			// See https://commonmark.thephpleague.com/2.0/configuration/
			return '<?php
			$converter = new \League\CommonMark\GithubFlavoredMarkdownConverter([
				"html_input" => "allow", // To allow "<br>" tags, etc.
				"allow_unsafe_links" => false,
			]);
			echo $converter->convertToHtml(ob_get_clean());
			?>';
		});
	}

	protected function save_build_file(string $build_content): bool {
		$saved = @file_put_contents($this->save_path, $build_content);

		// file_put_contents() may return a non-Boolean value which evaluates to false.
		return ($saved === false) ? false : true;
	}

	public function get_public_view(): PublicView {
		// Get the Public View object of this class, to be used in frontend (Blade) code.
		// to only allow specific methods and properties to be public.
		return new PublicView($this);
	}

	public function is_build_disabled(): bool {
		// Check if "core.disable_build" is set in the view.
		return (bool) ($this->get_view_data('core.disable_build'));
	}
}
