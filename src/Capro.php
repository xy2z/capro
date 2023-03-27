<?php

namespace xy2z\Capro;

use xy2z\Capro\Commands\CommandBuild;

/**
 * Capro - static lcass
 * To be used in views to access pages, collections, etc.
 */
abstract class Capro {
	public static CommandBuild $CommandBuild;

	/**
	 * Magic method for any method call on this class. Eg. "Capro::pages()" or "Capro::posts()".
	 * @param array<mixed> $arguments
	 *
	 * @return mixed
	 */
	public static function __callStatic(string $name, array $arguments): mixed {
		$views = self::$CommandBuild->get_views();

		// Check if call matches "pages()".
		if ($name === 'pages') {
			$data_pages = [];
			foreach ($views as $view) {
				if ($view->get('type') === View::TYPE_PAGE) {
					$data_pages[] = $view->get_public_view();
				}
			}
			return new Collector($data_pages);
		}

		// Check if call matches the name of a collection.
		$data = [];
		foreach ($views as $view) {
			if (strtolower($view->get('label')) === strtolower($name)) {
				$data[] = $view->get_public_view();
			}
		}

		if (empty($data)) {
			// Possible error. Empty $data for method. Notice/warning.
			self::$CommandBuild->add_error('Warning: No views found for collection "' . $name . '".');
		}

		// Return collector, even if $data is empty, so methods doesnt throw fatal errors.
		return new Collector($data);
	}
}
