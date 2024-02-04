<?php

namespace xy2z\Capro;

use xy2z\Capro\View;

/**
 * PublicView class
 *
 * "Proxy" class for a "View" to only allow a few methods to be public in the Blade templates.
 * Meaning instead of sending the View class via Capro::pages(), we give send this PublicView class.
 *
 * Can be called in a blade view, eg. using the Capro class:
 * `$post = Capro::news()->first();
 * Get the post title: `$post->title` (will get the "title" variable from the post's yaml-front-matter)
 *
 * Magic properties of the View class
 * @property string $path
 * @property string $dir
 * @property string $relative_path; // needed for cli messages (errors)
 * @property string $basename
 * @property string $label
 * @property string $save_path; // full path to where the build view file (html) will be saved
 * @property string $href
 * @property int $type
 */
class PublicView {
	protected View $view;

	public function __construct(View $view) {
		$this->view = $view;
	}

	public function __get(string $name): mixed {
		return $this->view->get($name);
	}

	public function get(string $name): mixed {
		return $this->view->get($name);
	}

	public function get_all_view_data(): mixed {
		return $this->view->get_view_data();
	}

	public function is_page(): bool {
		return $this->view->get('type') === View::TYPE_PAGE;
	}

	public function is_collection(string $label = ''): bool {
		return (($this->view->get('type') === View::TYPE_COLLECTION) && (empty($label) || $this->view->get('label') === $label));
	}

	public function is_template(): bool {
		return $this->view->get('type') === View::TYPE_TEMPLATE;
	}
}
