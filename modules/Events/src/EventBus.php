<?php
declare(strict_types=1);

namespace Elephox\Events;

use Closure;
use Elephox\Collection\ArrayList;
use Elephox\Collection\ArrayMap;
use Elephox\Collection\Contract\ReadonlyList;
use JetBrains\PhpStorm\Pure;

class EventBus implements Contract\EventBus
{
	/** @var ArrayMap<non-empty-string, ArrayList<Contract\Subscription>> */
	private readonly ArrayMap $eventSubscriptionsMapping;

	/** @var ArrayMap<non-empty-string, Contract\Subscription> */
	private readonly ArrayMap $subscriptionSubscriberMapping;

	#[Pure] public function __construct()
	{
		$this->eventSubscriptionsMapping = new ArrayMap();
		$this->subscriptionSubscriberMapping = new ArrayMap();
	}

	public function subscribe(string $eventName, callable $callback, int $priority = 0): Contract\Subscription
	{
		if ($this->eventSubscriptionsMapping->has($eventName)) {
			$list = $this->eventSubscriptionsMapping->get($eventName);
		} else {
			/** @var ArrayList<Contract\Subscription> $list */
			$list = new ArrayList();
		}

		$subscription = new Subscription($eventName, $callback(...), $priority);

		$list->add($subscription);

		$this->eventSubscriptionsMapping->put($eventName, $list);
		$this->subscriptionSubscriberMapping->put($subscription->getId(), $subscription);

		return $subscription;
	}

	public function unsubscribe(string $id): void
	{
		if (!$this->subscriptionSubscriberMapping->has($id)) {
			return;
		}

		$eventName = $this->subscriptionSubscriberMapping->get($id)->getEventName();

		$subscriptions = $this->eventSubscriptionsMapping->get($eventName);
		$subscriptions->remove(fn(Contract\Subscription $subscription) => $subscription->getId() === $id);
		if ($subscriptions->isEmpty()) {
			$this->eventSubscriptionsMapping->remove($eventName);
		} else {
			$this->eventSubscriptionsMapping->put($eventName, $subscriptions);
		}

		$this->subscriptionSubscriberMapping->remove($id);
	}

	public function publish(Contract\Event $event): void
	{
		$key = $event->getName();
		if (!$this->eventSubscriptionsMapping->has($key)) {
			return;
		}

		$subscriptions = $this->eventSubscriptionsMapping->get($key)
			->orderBy(fn(Contract\Subscription $a, Contract\Subscription $b) => $b->getPriority() - $a->getPriority());
		foreach ($subscriptions as $subscription) {
			$callback = $subscription->getCallback();

			$callback($event);

			if ($event->isPropagationStopped()) {
				break;
			}
		}
	}

	#[Pure] public function getSubscriptions(): ReadonlyList
	{
		return $this->subscriptionSubscriberMapping->values();
	}
}
