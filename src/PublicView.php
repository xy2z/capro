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
}
