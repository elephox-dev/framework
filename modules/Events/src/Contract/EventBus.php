<?php
declare(strict_types=1);

namespace Elephox\Events\Contract;

interface EventBus
{
	/**
	 * @param non-empty-string $eventName
	 * @param callable(Event): void $callback
	 *
	 * @return non-empty-string
	 */
	public function subscribe(string $eventName, callable $callback): string;

	/**
	 * @param non-empty-string $id
	 */
	public function unsubscribe(string $id): void;

	public function publish(Event $event): void;
}
