<?php
declare(strict_types=1);

namespace Philly\Http;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Throwable;

class InvalidHeaderTypeException extends InvalidArgumentException
{
	#[Pure] public function __construct(mixed $actual, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Header value must be an array or string, " . gettype($actual) . " given", $code, $previous);
	}
}
