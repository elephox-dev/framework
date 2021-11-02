<?php

namespace Philly\Collection;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Throwable;

class InvalidOffsetException extends InvalidArgumentException
{
	#[Pure] public function __construct(string|int $offset)
	{
		parent::__construct("Offset '$offset' does not exist.");
	}
}
