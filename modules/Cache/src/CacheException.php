<?php
declare(strict_types=1);

namespace Elephox\Cache;

use Exception;
use JetBrains\PhpStorm\Pure;
use Psr\Cache\InvalidArgumentException;
use Throwable;

class CacheException extends Exception implements InvalidArgumentException
{
	#[Pure]
	public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
