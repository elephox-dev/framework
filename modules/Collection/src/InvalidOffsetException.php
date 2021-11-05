<?php

namespace Philly\Collection;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Throwable;

abstract class InvalidOffsetException extends InvalidArgumentException
{
	#[Pure] public function __construct(mixed $offset, string $format, int $code = 0, ?Throwable $previous = null)
	{
		$message_offset = is_object($offset) ? get_class($offset) : (string)$offset;

		parent::__construct(sprintf($format, $message_offset), $code, $previous);
	}
}