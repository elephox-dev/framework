<?php
declare(strict_types=1);

namespace Elephox\Core\Events;

trait ClassNameAsEventName
{
	/**
	 * @return class-string<self>
	 */
	public function getName(): string
	{
		return static::class;
	}
}
