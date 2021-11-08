<?php
declare(strict_types=1);

namespace Philly\Collection;

use JetBrains\PhpStorm\Pure;
use Throwable;

class OffsetNotAllowedException extends InvalidOffsetException
{
	#[Pure] public function __construct(mixed $offset, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($offset, "Offset '%s' is not allowed.", $code, $previous);
	}
}
