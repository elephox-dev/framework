<?php
declare(strict_types=1);

namespace Elephox\PIE;

use JetBrains\PhpStorm\Pure;
use RuntimeException;
use Throwable;

class EmptySequenceException extends RuntimeException
{
	#[Pure] public function __construct(int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("The sequence contains no elements", $code, $previous);
	}
}
