<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use RuntimeException;
use Throwable;

class RequiredArgumentMissingException extends RuntimeException
{
	public function __construct(string $argumentName, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Required argument '$argumentName' is missing", $code, $previous);
	}
}
