<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Core\Context\Contract\Context;
use JetBrains\PhpStorm\Pure;
use Throwable;

class InvalidContextException extends HandlerException
{
	#[Pure] public function __construct(Context $given, string $expected, int $code = 0, Throwable $previous = null)
	{
		parent::__construct("Invalid context for handler. Expected: $expected, given: " . $given::class, $code, $previous);
	}
}
