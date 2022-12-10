<?php

$finder = PhpCsFixer\Finder::create()
	->in(__DIR__ . '/src');
// ->in(__DIR__ . '/tests');

$config = new PhpCsFixer\Config();
return $config->setRules([
	'@PSR12' => true,
	'array_syntax' => ['syntax' => 'short'],
	'braces' => [
		'allow_single_line_closure' => true,
		'position_after_functions_and_oop_constructs' => 'same',
	],
])
	->setIndent("\t")
	->setLineEnding("\n")
	->setFinder($finder);
