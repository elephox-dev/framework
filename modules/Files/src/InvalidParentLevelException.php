<?php
declare(strict_types=1);

namespace Elephox\Files;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Throwable;

class InvalidParentLevelException extends InvalidArgumentException
{
	#[Pure] public function __construct(int $levels, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Levels must be greater than 0, got: $levels", $code, $previous);
	}
}
