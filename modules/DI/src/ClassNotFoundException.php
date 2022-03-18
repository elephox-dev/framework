<?php
declare(strict_types=1);

namespace Elephox\DI;

use RuntimeException;
use Throwable;

class ClassNotFoundException extends RuntimeException
{
	public function __construct(string $className, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Class not found: $className", $code, $previous);
	}
}
