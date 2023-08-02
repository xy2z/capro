<?php

namespace xy2z\Capro;

use xy2z\Capro\Collector as CaproCollector;

// use xy2z\Capro\Collector as CaproCollector;

// Collector class
// Is only being used by Capro class, to filter, limit and sort views like Pages and Collections.

class Collector {
	/** @var array<PublicView> */
	protected array $original_data;

	/** @var array<PublicView> */
	protected array $data;

	/** @param array<PublicView> $data */
	public function __construct(array $data) {
		$this->original_data = $data;
		$this->data = $data;
	}

	/** @return array<PublicView> */
	public function get(): array {
		return $this->data;
	}

	public function count(): int {
		return count($this->data);
	}

	public function first(): ?PublicView {
		return $this->data[array_key_first($this->data)] ?? null;
	}

	public function last(): ?PublicView {
		return $this->data[array_key_last($this->data)] ?? null;
	}

	public function limit(int $limit, int $offset = 0): self {
		$this->data = array_slice($this->data, $offset, $limit);
		return $this;
	}

	public function reverse(): self {
		$this->data = array_reverse($this->data);
		return $this;
	}

	public function reset(): self {
		$this->data = $this->original_data;
		return $this;
	}

	public function where(string $where_key, mixed $where_val): self {
		foreach ($this->data as $key => $view) {
			if ($view->$where_key !== $where_val) {
				// Remove from array.
				unset($this->data[$key]);
			}
		}
		return $this;
	}

	public function whereNot(string $where_key, mixed $where_val): self {
		foreach ($this->data as $key => $view) {
			if ($view->$where_key === $where_val) {
				// Remove from array.
				unset($this->data[$key]);
			}
		}
		return $this;
	}

	public function whereBetween(string $where_key, float $min, float $max): self {
		foreach ($this->data as $key => $view) {
			if (is_null($view->$where_key ?? null) || ($view->$where_key < $min) || ($view->$where_key > $max)) {
				// Remove from array.
				unset($this->data[$key]);
			}
		}
		return $this;
	}

	public function whereNotBetween(string $where_key, float $min, float $max): self {
		foreach ($this->data as $key => $view) {
			if (($view->$where_key >= $min) && ($view->$where_key <= $max)) {
				// Remove from array.
				unset($this->data[$key]);
			}
		}
		return $this;
	}

	/**
	 * @param array<mixed> $options
	 */
	public function whereIn(string $where_key, array $options): self {
		foreach ($this->data as $key => $view) {
			if (is_array($view->$where_key)) {
				if (empty(array_intersect($view->$where_key, $options))) {
					unset($this->data[$key]);
				}
			} elseif (!in_array($view->$where_key, $options)) {
				// Remove from array.
				unset($this->data[$key]);
			}
		}
		return $this;
	}

	/**
	 * @param array<mixed> $options
	 */
	public function whereNotIn(string $where_key, array $options): self {
		foreach ($this->data as $key => $view) {
			if (is_array($view->$where_key)) {
				if (!empty(array_intersect($view->$where_key, $options))) {
					unset($this->data[$key]);
				}
			} elseif (in_array($view->$where_key, $options)) {
				// Remove from array.
				unset($this->data[$key]);
			}
		}
		return $this;
	}

	public function orderBy(string $sort_key, bool $case_insensitive = true): self {
		usort($this->data, function ($a, $b) use ($sort_key, $case_insensitive) {
			if ($case_insensitive) {
				return strtolower($a->$sort_key) <=> strtolower($b->$sort_key);
			}
			return $a->$sort_key <=> $b->$sort_key;
		});

		return $this;
	}

	public function orderByDesc(string $sort_key, bool $case_insensitive = true): self {
		$this->orderBy($sort_key, $case_insensitive)->reverse();
		return $this;
	}

	public function whereHas(string $key): self {
		foreach ($this->data as $i => $view) {
			if (is_null($view->$key ?? null)) {
				// Remove from array.
				unset($this->data[$i]);
			}
		}
		return $this;
	}

	public function whereHasNot(string $key): self {
		foreach ($this->data as $i => $view) {
			if (!is_null($view->$key ?? null)) {
				// Remove from array.
				unset($this->data[$i]);
			}
		}
		return $this;
	}

	// Exclude views
	/** @param array<PublicView> $exclude_views */
	public function exclude(PublicView|array $exclude_views): self {
		if (!is_array($exclude_views)) {
			$exclude_views = [$exclude_views];
		}

		foreach ($exclude_views as $view) {
			if (!$view instanceof PublicView) {
				throw new \Exception('exclude() expects an array of PublicView objects.');
			}
		}

		foreach ($this->data as $i => $view) {
			if (in_array($view, $exclude_views)) {
				unset($this->data[$i]); // Remove from array.
			}
		}

		return $this;
	}

	public function shuffle(): self {
		shuffle($this->data);
		return $this;
	}

	public function debug(): void {
		$debug_data = [];
		foreach ($this->data as $key => $view) {
			$debug_data[$key] = [
				'path' => $view->path,
				'relative_path' => $view->relative_path,
				'dir' => $view->dir,
				'basename' => $view->basename,
				'href' => $view->href,
				'view_data' => $view->get_all_view_data(),
			];
		}

		echo '<pre style="text-align: left;">';
		var_dump($debug_data);
		echo '</pre>';
	}
}
