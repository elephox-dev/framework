<?php
declare(strict_types=1);

namespace Elephox\Support;

/**
 * @see \Elephox\Support\Contract\StringConvertible
 */
trait ToStringCompatible
{
	abstract public function asString(): string;

	public function __toString(): string
	{
		return $this->asString();
	}
}
