<?php

namespace xy2z\Capro\Commands;

interface CommandInterface {
	public function __construct(array $argv);

	public function run(): void;
}
