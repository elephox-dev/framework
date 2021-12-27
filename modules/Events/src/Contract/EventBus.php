<?php
declare(strict_types=1);

namespace Elephox\Events\Contract;

use Elephox\Core\Context\Contract\EventContext;

interface EventBus
{
	/**
	 * @param non-empty-string $eventName
	 * @param callable(EventContext): void $callback
	 */
	public function subscribe(string $eventName, callable $callback): void;

	public function publish(Event $eventContext): void;
}
