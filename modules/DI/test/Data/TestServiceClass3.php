<?php
declare(strict_types=1);

namespace Elephox\DI\Data;

class TestServiceClass3 implements TestServiceInterface
{
	public function returnsString(string $testString): string
	{
		return $testString;
	}

	public function returnsTestServiceInterface(TestServiceInterface $service): TestServiceInterface
	{
		return $service;
	}

	private function privateReturnsString(string $testString): string
	{
		return $testString;
	}

	public static function returnsStringStatic(string $testString): string
	{
		return $testString;
	}
}
