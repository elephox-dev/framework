<?php
declare(strict_types=1);

namespace Elephox\Events;

use Elephox\Collection\ArrayMap;
use Elephox\Collection\ArraySet;
use Elephox\Collection\Contract\GenericEnumerable;
use JetBrains\PhpStorm\Pure;

readonly class EventBus implements Contract\EventBus
{
	/**
	 * @var ArrayMap<non-empty-string, ArraySet<Contract\Subscription>> $eventSubscriptionsMapping
	 */
	private ArrayMap $eventSubscriptionsMapping;

	/**
	 * @var ArrayMap<non-empty-string, Contract\Subscription> $subscriptionSubscriberMapping
	 */
	private ArrayMap $subscriptionSubscriberMapping;

	#[Pure]
	public function __construct()
	{
		/** @var ArrayMap<non-empty-string, ArraySet<Contract\Subscription>> */
		$this->eventSubscriptionsMapping = new ArrayMap();

		/** @var ArrayMap<non-empty-string, Contract\Subscription> */
		$this->subscriptionSubscriberMapping = new ArrayMap();
	}

	public function subscribe(
		string $eventName,
		callable $callback,
		int $priority = 0,
	): Contract\Subscription {
		if ($this->eventSubscriptionsMapping->has($eventName)) {
			$subscriptions = $this->eventSubscriptionsMapping->get($eventName);
		} else {
			/** @var ArraySet<Contract\Subscription> $subscriptions */
			$subscriptions = new ArraySet();
		}

		$subscription =
			new Subscription(
				$eventName,
				$callback(...),
				$priority,
			);

		$subscriptions->add($subscription);

		$this->eventSubscriptionsMapping->put(
			$eventName,
			$subscriptions,
		);
		$this->subscriptionSubscriberMapping->put(
			$subscription->getId(),
			$subscription,
		);

		return $subscription;
	}

	public function unsubscribe(Contract\Subscription|string $id): void
	{
		if ($id instanceof Contract\Subscription) {
			$id = $id->getId();
		}

		if (!$this->subscriptionSubscriberMapping->has($id)) {
			return;
		}

		$eventName = $this->subscriptionSubscriberMapping->get($id)->getEventName();

		$subscriptions = $this->eventSubscriptionsMapping->get($eventName);
		$subscriptions->removeBy(
			static fn (Contract\Subscription $subscription) => $subscription->getId() === $id,
		);
		if ($subscriptions->isEmpty()) {
			$this->eventSubscriptionsMapping->remove($eventName);
		} else {
			$this->eventSubscriptionsMapping->put(
				$eventName,
				$subscriptions,
			);
		}

		$this->subscriptionSubscriberMapping->remove($id);
	}

	public function publish(Contract\Event $event): void
	{
		$eventName = $event->getName();
		if (!$this->eventSubscriptionsMapping->has($eventName)) {
			return;
		}

		$subscriptions = $this->eventSubscriptionsMapping->get($eventName)->orderByDescending(
			static fn (Contract\Subscription $s): int => $s->getPriority(),
		);

		foreach ($subscriptions as $subscription) {
			$callback = $subscription->getCallback();

			$callback($event);

			if ($event->isPropagationStopped()) {
				break;
			}
		}
	}

	public function getSubscriptions(?string $eventName = null): GenericEnumerable
	{
		/** @var GenericEnumerable<Contract\Subscription> $subscriptions */
		$subscriptions = $this->subscriptionSubscriberMapping->values();

		if ($eventName !== null) {
			return $subscriptions->where(
				static fn (Contract\Subscription $subscription) => $subscription->getEventName() ===
					$eventName,
			);
		}

		return $subscriptions;
	}
}
