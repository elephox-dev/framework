<?php
declare(strict_types=1);

namespace Elephox\Events;

use Closure;
use JetBrains\PhpStorm\Pure;

class Subscription implements Contract\Subscription
{
	private readonly string $id;

	/**
	 * @param non-empty-string $eventName
	 * @param Closure(Contract\Event): void $callback
	 */
	public function __construct(
		private readonly string $eventName,
		private readonly Closure $callback
	) {
		$this->id = spl_object_hash((object)$this);
	}

	#[Pure] public function getId(): string
	{
		return $this->id;
	}

	#[Pure] public function getEventName(): string
	{
		return $this->eventName;
	}

	#[Pure] public function getCallback(): callable
	{
		return $this->callback;
	}
}
