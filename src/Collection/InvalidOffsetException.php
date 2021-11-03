<?php

namespace Philly\Collection;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Throwable;

class InvalidOffsetException extends InvalidArgumentException
{
	#[Pure] public function __construct(mixed $offset)
	{
		$message_offset = is_object($offset) ? get_class($offset) : (string)$offset;

		parent::__construct("Offset '$message_offset' does not exist.");
	}
}
