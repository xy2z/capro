<?php

namespace xy2z\Capro;

use xy2z\LiteConfig\LiteConfig;
use Symfony\Component\Yaml\Yaml;

/**
 * Capro config class
 * Add custom hadnler to LiteConfig package to support YAML files.
 */
class Config extends LiteConfig {
	protected static function custom_handler(string $extension, string $path) { /*: mixed*/
		if ($extension === 'yml' || $extension === 'yaml') {
			return Yaml::parseFile($path);
		}

		return null;
	}
}
