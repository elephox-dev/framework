<?php
declare(strict_types=1);

namespace Elephox\Core;

use Exception;
use JetBrains\PhpStorm\Pure;
use Throwable;

class CoreException extends Exception
{
	#[Pure]
	public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
