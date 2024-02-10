<?php

// Blade Helpers
// This file is loaded in all blade views to make sure global functions are available.
// This can probably be done smarter at some point.

use xy2z\Capro\Config;

// ====================
// Classes
// ====================
if (!class_exists('Config')) {
	class_alias('\xy2z\Capro\Config', 'Config');
}
if (!class_exists('Capro')) {
	class_alias('\xy2z\Capro\Capro', 'Capro');
}

// ====================
// Functions
// ====================
if (!function_exists('config')) {
	function config(string $key, mixed $default = null): mixed {
		return Config::get($key, $default);
	}
}
