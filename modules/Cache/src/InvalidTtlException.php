<?php
declare(strict_types=1);

namespace Elephox\Cache;

use JetBrains\PhpStorm\Pure;
use Throwable;

class InvalidTtlException extends CacheException
{
	#[Pure]
	public function __construct(int $ttl, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Unable to parse TTL: $ttl", $code, $previous);
	}
}
