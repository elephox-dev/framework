<?php
declare(strict_types=1);

namespace Elephox\Collection;

use JetBrains\PhpStorm\Pure;
use RuntimeException;
use Throwable;

class AmbiguousMatchException extends RuntimeException
{
	#[Pure]
	public function __construct(int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct('Sequence contains more than one matching element', $code, $previous);
	}
}
