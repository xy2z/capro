<?php

$finder = PhpCsFixer\Finder::create()
	->in(__DIR__ . '/src')
	->append([
		__DIR__ . '/capro'
	]);

$config = new PhpCsFixer\Config();
return $config->setRules([
	'@PSR12' => true,

	// Short arrays `[]` instead of `array()`
	'array_syntax' => ['syntax' => 'short'],

	// Place braces on same line
	'braces' => [
		'allow_single_line_closure' => true,
		'position_after_functions_and_oop_constructs' => 'same',
	],

	// Fix indetation to tabs
	'indentation_type' => true,

	// PHP code must use the long <?php tags or short-echo <?= tags and not other tag variations.
	'full_opening_tag' => true,

	// The PHP closing tag MUST be omitted from files containing only PHP.
	'no_closing_tag' => true,

	// A PHP file without end tag must always end with a single empty line feed.
	'single_blank_line_at_eof' => true,

	// Ensure there is no code on the same line as the PHP open tag and it is followed by a blank line.
	'blank_line_after_opening_tag' => true,

	// Each element of an array must be indented exactly once.
	'array_indentation' => true,

	// Remove extra spaces in a nullable typehint.
	'compact_nullable_typehint' => true,

	// Removes extra blank lines and/or blank lines following configuration.
	'no_extra_blank_lines' => true,

	// Strings must be single quote
	'single_quote' => true,

	// Class static references self, static and parent MUST be in lower case.
	'lowercase_static_reference' => true,

	// A single space or none should be between cast and variable.
	'cast_spaces' => true,

	// Removes extra spaces between colon and case value.
	'switch_case_space' => true,

	// Function defined by PHP should be called using the correct casing.
	'native_function_casing' => true,

	// PHP code MUST use only UTF-8 without BOM (remove BOM).
	'encoding' => true,

	// PHP keywords MUST be in lower case.
	'lowercase_keywords' => true,

	// The PHP constants true, false, and null MUST be written using the correct casing.
	'constant_case' => true,

	// Magic constants should be referred to using the correct casing. (eg. `__DIR__`)
	'magic_constant_casing' => true,

	// Magic method definitions and calls must be using the correct casing.
	'magic_method_casing' => true,

	// Multi-line arrays, arguments list, parameters list and match expressions must have a trailing comma.
	'trailing_comma_in_multiline' => true,
])
->setIndent("\t")
->setLineEnding("\n") // "LF"
->setFinder($finder);
