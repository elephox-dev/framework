<?php
declare(strict_types=1);

namespace Elephox\Events;

use Closure;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
class Subscription implements Contract\Subscription
{
	/**
	 * @var non-empty-string $id
	 */
	private readonly string $id;

	/**
	 * @param non-empty-string $eventName
	 * @param Closure(Contract\Event): void $callback
	 * @param int $priority
	 */
	public function __construct(
		private readonly string $eventName,
		private readonly Closure $callback,
		private readonly int $priority = 0,
	) {
		/** @var non-empty-string */
		$this->id = spl_object_hash($this);
	}

	#[Pure]
	public function getId(): string
	{
		return $this->id;
	}

	#[Pure]
	public function getEventName(): string
	{
		return $this->eventName;
	}

	#[Pure]
	public function getCallback(): callable
	{
		return $this->callback;
	}

	#[Pure]
	public function getPriority(): int
	{
		return $this->priority;
	}
}
