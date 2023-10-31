<?php
declare(strict_types=1);

namespace Elephox\DI\Data;

class TestServiceClassWithConstructor2
{
	public function __construct(public TestServiceClassWithConstructor $testService)
	{
	}
}
