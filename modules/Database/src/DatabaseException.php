<?php
declare(strict_types=1);

namespace Elephox\Database;

use JetBrains\PhpStorm\Pure;
use RuntimeException;
use Throwable;

class DatabaseException extends RuntimeException
{
	#[Pure] public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
