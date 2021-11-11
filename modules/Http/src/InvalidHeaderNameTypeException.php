<?php
declare(strict_types=1);

namespace Elephox\Http;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Throwable;

class InvalidHeaderNameTypeException extends InvalidArgumentException
{
	#[Pure] public function __construct(mixed $actual, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Header name must be a string, " . gettype($actual) . " given", $code, $previous);
	}
}
