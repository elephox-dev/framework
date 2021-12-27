<?php
declare(strict_types=1);

namespace Elephox\Events;

trait ClassNameAsEventName
{
	/**
	 * @return class-string<static>
	 */
	public function getName(): string
	{
		return static::class;
	}
}
