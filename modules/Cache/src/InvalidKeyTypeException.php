<?php
declare(strict_types=1);

namespace Elephox\Cache;

use Exception;
use JetBrains\PhpStorm\Pure;
use Psr\Cache\InvalidArgumentException;
use Throwable;

class InvalidKeyTypeException extends Exception implements InvalidArgumentException
{
	#[Pure]
	public function __construct(mixed $value, int $code = 0, ?Throwable $previous = null) {
		$type = get_debug_type($value);

		parent::__construct(sprintf('The key of type "%s" is not a string.', $type), $code, $previous);
	}
}
