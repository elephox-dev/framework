<?php
declare(strict_types=1);

namespace Elephox\DI\Data;

class TestServiceClassWithConstructor implements TestServiceInterface
{
	public function __construct(public TestServiceInterface $testService)
	{
	}
}
