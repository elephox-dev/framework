<?php
declare(strict_types=1);

namespace Elephox\Collection;

use InvalidArgumentException;
use Throwable;

abstract class InvalidOffsetException extends InvalidArgumentException
{
	/**
	 * @param ?Throwable $previous
	 * @param mixed $offset
	 * @param string $format
	 * @param int $code
	 */
	public function __construct(mixed $offset, string $format, int $code = 0, ?Throwable $previous = null)
	{
		$message_offset = is_object($offset) ? $offset::class : (string) $offset;

		parent::__construct(sprintf($format, $message_offset), $code, $previous);
	}
}
