<?php
declare(strict_types=1);

namespace Elephox\Core\Events;

class Event implements Contract\Event
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
