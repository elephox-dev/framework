<?php
declare(strict_types=1);

namespace Elephox\DI\Data;

class TestServiceClass implements TestServiceInterface
{
	public function returnsString(string $testString): string {
		return $testString;
	}

	public function returnsTestServiceInterface(TestServiceInterface $service): TestServiceInterface {
		return $service;
	}
}
