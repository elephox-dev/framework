<?php
declare(strict_types=1);

namespace Elephox\Events\Contract;

use Elephox\Collection\Contract\GenericEnumerable;

interface EventBus
{
	/**
	 * @param non-empty-string $eventName
	 * @param callable(Event): void $callback
	 */
	public function subscribe(string $eventName, callable $callback): Subscription;

	/**
	 * @param Subscription|non-empty-string $id
	 */
	public function unsubscribe(Subscription|string $id): void;

	/**
	 * @param null|non-empty-string $eventName
	 *
	 * @return GenericEnumerable<Subscription>
	 */
	public function getSubscriptions(?string $eventName = null): GenericEnumerable;

	public function publish(Event $event): void;
}
