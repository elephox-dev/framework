<?php
declare(strict_types=1);

namespace Elephox\DI\Data;

class TestServiceClassUninstantiable
{
	private function __construct()
	{
	}

	public function returnsString(string $testString): string
	{
		return $testString;
	}
}
