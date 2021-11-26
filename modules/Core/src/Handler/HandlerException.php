<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Core\CoreException;
use JetBrains\PhpStorm\Pure;
use Throwable;

class HandlerException extends CoreException
{
	#[Pure] public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
