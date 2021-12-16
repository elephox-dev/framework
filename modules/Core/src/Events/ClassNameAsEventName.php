<?php
declare(strict_types=1);

namespace Elephox\Core\Events;

trait ClassNameAsEventName
{
	public function getName(): string
	{
		return static::class;
	}
}
