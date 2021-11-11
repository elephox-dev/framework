<?php
declare(strict_types=1);

namespace Elephox\Collection;

use JetBrains\PhpStorm\Pure;
use Throwable;

class OffsetNotFoundException extends InvalidOffsetException
{
	#[Pure] public function __construct(mixed $offset, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($offset, "Offset '%s' does not exist.", $code, $previous);
	}
}
