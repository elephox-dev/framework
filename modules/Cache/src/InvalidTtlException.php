<?php
declare(strict_types=1);

namespace Elephox\Cache;

use Exception;
use JetBrains\PhpStorm\Pure;
use Psr\Cache\InvalidArgumentException;
use Throwable;

class InvalidTtlException extends Exception implements InvalidArgumentException
{
	#[Pure]
	public function __construct(int $ttl, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Unable to parse TTL: $ttl", $code, $previous);
	}
}
