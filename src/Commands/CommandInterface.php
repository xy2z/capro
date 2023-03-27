<?php

namespace xy2z\Capro\Commands;

interface CommandInterface {
	/**
	 * Constructor
	 *
	 * @param array<mixed> $argv
	 */
	public function __construct(array $argv);

	public function run(): void;
}
