<?php
declare(strict_types=1);

namespace Elephox\Events;

abstract class NamedEvent implements Contract\Event
{
	use StopsPropagation;

	/**
	 * @param non-empty-string $name
	 */
	public function __construct(
		private readonly string $name,
	) {
	}

	public function getName(): string
	{
		return $this->name;
	}
}
