<?php
declare(strict_types=1);

namespace Elephox\Collection;

use JetBrains\PhpStorm\Pure;
use OutOfRangeException;
use Throwable;

class IndexOutOfRangeException extends OutOfRangeException
{
	#[Pure] public function __construct(int $index, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct('Requested index out of range: ' . $index, $code, $previous);
	}
}
