<?php
declare(strict_types=1);

namespace Elephox\Events;

use Elephox\Events\Contract\Event;

class NamedEvent implements Event
{
	/**
	 * @param non-empty-string $name
	 */
	public function __construct(
		private string $name,
	)
	{
	}

	public function getName(): string
	{
		return $this->name;
	}
}
