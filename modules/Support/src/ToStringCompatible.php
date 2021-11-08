<?php
declare(strict_types=1);

namespace Philly\Support;

/**
 * @see \Philly\Support\Contract\StringConvertible
 */
trait ToStringCompatible
{
	abstract public function asString(): string;

	public function __toString(): string
	{
		return $this->asString();
	}
}
