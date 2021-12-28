<?php
declare(strict_types=1);

namespace Elephox\Events\Contract;

use Elephox\Collection\Contract\ReadonlyList;

interface EventBus
{
	/**
	 * @param non-empty-string $eventName
	 * @param callable(Event): void $callback
	 *
	 * @return Subscription
	 */
	public function subscribe(string $eventName, callable $callback): Subscription;

	/**
	 * @param non-empty-string $id
	 */
	public function unsubscribe(string $id): void;

	/**
	 * @return ReadonlyList<Subscription>
	 */
	public function getSubscriptions(): ReadonlyList;

	public function publish(Event $event): void;
}
