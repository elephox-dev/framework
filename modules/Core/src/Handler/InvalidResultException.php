<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use JetBrains\PhpStorm\Pure;
use Throwable;

class InvalidResultException extends HandlerException
{
	#[Pure]
	public function __construct(mixed $given, string $expected, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Handler returned an invalid result. Expected: $expected, given: ". $given::class, $code, $previous);
	}
}
