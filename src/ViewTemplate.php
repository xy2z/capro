<?php

namespace xy2z\Capro;

use Generator;

/**
 * ViewTemplate
 *
 * This can be used for content fetched from an API that needs to be rendered in a view template (blade file).
 * This will create a view (page) for each row in $items, based on a blade template view ($template_view).
 */
class ViewTemplate {
	protected string $label;
	protected string $template_view;
	protected string $result_path;

	/** @var array<mixed> */
	protected array $items;

	/** @param array<mixed> $items */
	public function __construct(string $label, string $template_view, string $result_path, array $items) {
		$this->label = $label;
		$this->template_view = $template_view;
		$this->result_path = $result_path;
		$this->items = $items;
	}

	/** @param array<mixed> $item */
	protected function get_item_path(array $item): string {
		// Replace slugs in result_path with variables from the item array.
		// Eg. if the result_path is "news/{slug}" - "{slug}" will be replaced by the "slug" value in the item array.
		// so the result will be"news/my-first-post".
		$path = $this->result_path;
		foreach ($item as $key => $value) {
			if (!is_string($value) && !is_float($value) && !is_integer($value)) {
				continue;
			}
			$path = str_replace('{' . $key . '}', $value, $path);
		}
		return $path;
	}

	/** @param array<mixed> $item */
	protected function get_public_path(array $item): string {
		$path = $this->get_item_path($item);
		return PUBLIC_DIR . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . 'index.html';
	}

	public function load_views(): Generator {
		$this->template_view = VIEWS_DIR . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $this->template_view . '.blade.php';

		// Make a View for each row in $items.
		foreach ($this->items as $item) {
			/* // PHP 8+
			$view = new View(
				path: $this->template_view,
				type: View::TYPE_TEMPLATE,
				label: $this->label,
				save_path: $this->get_public_path($item),
			); */
			$view = new View(
				$this->template_view,
				View::TYPE_TEMPLATE,
				$this->label,
				$this->get_public_path($item),
			);
			$view->set_view_data($item);

			$href = $this->get_item_path($item);
			$view->set_href($href);

			yield $view;
		}
	}
}
