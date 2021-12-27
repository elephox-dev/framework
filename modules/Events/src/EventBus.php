<?php
declare(strict_types=1);

namespace Elephox\Events;

use Elephox\Collection\ArrayList;
use Elephox\Collection\ArrayMap;
use Elephox\Events\Contract\Event;
use JetBrains\PhpStorm\Pure;

class EventBus implements Contract\EventBus
{
	/** @var ArrayMap<non-empty-string, ArrayList<non-empty-string>> */
	private ArrayMap $eventSubscriptionsMapping;

	/** @var ArrayMap<non-empty-string, array{non-empty-string, callable(Event): void}> */
	private ArrayMap $subscriptionSubscriberMapping;

	#[Pure] public function __construct()
	{
		$this->eventSubscriptionsMapping = new ArrayMap();
		$this->subscriptionSubscriberMapping = new ArrayMap();
	}

	public function subscribe(string $eventName, callable $callback): string
	{
		if ($this->eventSubscriptionsMapping->has($eventName)) {
			$list = $this->eventSubscriptionsMapping->get($eventName);
		} else {
			/** @var ArrayList<non-empty-string> $list */
			$list = new ArrayList();
		}

		/** @var non-empty-string $id */
		$id = spl_object_hash((object)$callback);

		$list->add($id);

		$this->eventSubscriptionsMapping->put($eventName, $list);
		$this->subscriptionSubscriberMapping->put($id, [$eventName, $callback]);

		return $id;
	}

	public function unsubscribe(string $id): void
	{
		if (!$this->subscriptionSubscriberMapping->has($id)) {
			return;
		}

		$eventName = $this->subscriptionSubscriberMapping->get($id)[0];

		$subscriptions = $this->eventSubscriptionsMapping->get($eventName);
		$subscriptions->remove(fn(string $subscription) => $subscription === $id);
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

		$subscriberIds = $this->eventSubscriptionsMapping->get($key);
		foreach ($subscriberIds as $id) {
			$subscriber = $this->subscriptionSubscriberMapping->get($id)[1];

			$subscriber($event);
		}
	}
}
