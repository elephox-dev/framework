<?php
declare(strict_types=1);

namespace Elephox\Events;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Events\EventBus
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\ArraySet
 * @covers \Elephox\Events\NamedEvent
 * @covers \Elephox\Events\Subscription
 * @covers \Elephox\Collection\DefaultEqualityComparer
 * @covers \Elephox\Collection\KeyedEnumerable
 * @covers \Elephox\Collection\Enumerable
 * @covers \Elephox\Collection\Iterator\OrderedIterator
 * @covers \Elephox\Collection\OrderedEnumerable
 * @uses   \Elephox\Collection\IsEnumerable
 * @uses   \Elephox\Events\ClassNameAsEventName
 */
class EventBusTest extends TestCase
{
	public function testPubSub(): void
	{
		$bus = new EventBus();

		$triggered = false;
		$subscription = $bus->subscribe(TestEvent::class, function (TestEvent $event) use (&$triggered) {
			$triggered = true;

			self::assertEquals(5, $event->data);
		});

		$bus->publish(new TestEvent(5));

		self::assertTrue($triggered);

		$bus->unsubscribe($subscription->getId());

		$triggered = false;

		$bus->publish(new TestEvent(5));

		self::assertFalse($triggered);
	}

	public function testPubSubNamed(): void
	{
		$bus = new EventBus();
		$triggeredA1 = false;
		$triggeredA2 = false;
		$triggeredB = false;

		$bus->subscribe('testA', function (TestNamedEvent $event) use (&$triggeredA1) {
			$triggeredA1 = true;

			self::assertEquals(5, $event->data);
		});

		$subscription = $bus->subscribe('testA', function () use (&$triggeredA2) {
			$triggeredA2 = true;
		});

		$bus->subscribe('testB', function (TestNamedEvent $event) use (&$triggeredB) {
			$triggeredB = true;

			self::assertEquals(6, $event->data);
		});

		$bus->publish(new TestNamedEvent('testA', 5));

		self::assertTrue($triggeredA1);
		self::assertTrue($triggeredA2);
		self::assertFalse($triggeredB);

		$bus->publish(new TestNamedEvent('testB', 6));

		self::assertTrue($triggeredB);

		$bus->unsubscribe($subscription->getId());
		$bus->unsubscribe($subscription->getId());
	}

	public function testEventNameFromClass(): void
	{
		$testEvent = new TestEvent(5);

		self::assertEquals(TestEvent::class, $testEvent->getName());
	}

	public function testGetSubscribers(): void
	{
		$bus = new EventBus();

		self::assertEmpty($bus->getSubscriptions());

		$subscription = $bus->subscribe("test", function () {});

		self::assertCount(1, $bus->getSubscriptions());
		self::assertSame($subscription, $bus->getSubscriptions()->first());
	}

	public function testStopPropagation(): void
	{
		$bus = new EventBus();
		$triggered = [false, false, false];

		$bus->subscribe(TestEvent::class, function (TestEvent $event) use (&$triggered) {
			$triggered[0] = true;
		});

		$bus->subscribe(TestEvent::class, function (TestEvent $event) use (&$triggered) {
			$triggered[1] = true;
			$event->stopPropagation();
		});

		$bus->subscribe(TestEvent::class, function (TestEvent $event) use (&$triggered) {
			$triggered[2] = true;
		});

		$bus->publish(new TestEvent(5));

		self::assertTrue($triggered[0]);
		self::assertTrue($triggered[1]);
		self::assertFalse($triggered[2]);
	}

	public function testPriority(): void
	{
		$bus = new EventBus();
		$triggered = [false, false, false];

		$bus->subscribe(TestEvent::class, function () use (&$triggered) {
			$triggered[0] = true;
		});

		$bus->subscribe(TestEvent::class, function (TestEvent $event) use (&$triggered) {
			$triggered[1] = true;
			$event->stopPropagation();
		}, 1);

		$bus->subscribe(TestEvent::class, function () use (&$triggered) {
			$triggered[2] = true;
		}, 2);

		$bus->publish(new TestEvent(5));

		self::assertFalse($triggered[0]);
		self::assertTrue($triggered[1]);
		self::assertTrue($triggered[2]);
	}
}

class TestEvent extends Event
{
	public function __construct(public readonly int $data)
	{
	}
}

class TestNamedEvent extends NamedEvent
{
	public function __construct(string $name, public readonly int $data)
	{
		parent::__construct($name);
	}
}
