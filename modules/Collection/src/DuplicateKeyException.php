<?php

namespace Elephox\Collection;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Throwable;

class DuplicateKeyException extends InvalidArgumentException
{
	#[Pure] public function __construct(mixed $key, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Duplicate key: $key", $code, $previous);
	}
}
