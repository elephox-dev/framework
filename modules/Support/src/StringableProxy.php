<?php
declare(strict_types=1);

namespace Elephox\Support;

/**
 * @see Contract\StringConvertible
 * @see Stringable
 */
trait StringableProxy
{
	abstract public function toString(): string;

	public function __toString(): string
	{
		return $this->toString();
	}
}
