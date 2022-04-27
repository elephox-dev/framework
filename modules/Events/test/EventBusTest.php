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
 * @covers \Elephox\Collection\IteratorProvider
 *
 * @uses   \Elephox\Collection\IsEnumerable
 * @uses   \Elephox\Events\ClassNameAsEventName
 *
 * @internal
 */
class EventBusTest extends TestCase
{
	public function testPubSub(): void
	{
		$bus = new EventBus();

		$triggered = false;
		$subscription = $bus->subscribe(TestEvent::class, static function (TestEvent $event) use (&$triggered): void {
			$triggered = true;

			self::assertEquals(5, $event->data);
		});

		$bus->publish(new TestEvent(5));

		static::assertTrue($triggered);

		$bus->unsubscribe($subscription);

		$triggered = false;

		$bus->publish(new TestEvent(5));

		static::assertFalse($triggered);
	}

	public function testPubSubNamed(): void
	{
		$bus = new EventBus();
		$triggeredA1 = false;
		$triggeredA2 = false;
		$triggeredB = false;

		$bus->subscribe('testA', static function (TestNamedEvent $event) use (&$triggeredA1): void {
			$triggeredA1 = true;

			self::assertEquals(5, $event->data);
		});

		$subscription = $bus->subscribe('testA', static function () use (&$triggeredA2): void {
			$triggeredA2 = true;
		});

		$bus->subscribe('testB', static function (TestNamedEvent $event) use (&$triggeredB): void {
			$triggeredB = true;

			self::assertEquals(6, $event->data);
		});

		$bus->publish(new TestNamedEvent('testA', 5));

		static::assertTrue($triggeredA1);
		static::assertTrue($triggeredA2);
		static::assertFalse($triggeredB);

		$bus->publish(new TestNamedEvent('testB', 6));

		static::assertTrue($triggeredB);

		$bus->unsubscribe($subscription->getId());
		$bus->unsubscribe($subscription->getId());
	}

	public function testEventNameFromClass(): void
	{
		$testEvent = new TestEvent(5);

		static::assertEquals(TestEvent::class, $testEvent->getName());
	}

	public function testGetSubscribers(): void
	{
		$bus = new EventBus();

		static::assertEmpty($bus->getSubscriptions());

		$subscription = $bus->subscribe('test', static function (): void {});

		static::assertCount(1, $bus->getSubscriptions());
		static::assertSame($subscription, $bus->getSubscriptions()->first());
	}

	public function testStopPropagation(): void
	{
		$bus = new EventBus();
		$triggered = [false, false, false];

		$bus->subscribe(TestEvent::class, static function (TestEvent $event) use (&$triggered): void {
			$triggered[0] = true;
		});

		$bus->subscribe(TestEvent::class, static function (TestEvent $event) use (&$triggered): void {
			$triggered[1] = true;
			$event->stopPropagation();
		});

		$bus->subscribe(TestEvent::class, static function (TestEvent $event) use (&$triggered): void {
			$triggered[2] = true;
		});

		$bus->publish(new TestEvent(5));

		static::assertTrue($triggered[0]);
		static::assertTrue($triggered[1]);
		static::assertFalse($triggered[2]);
	}

	public function testPriority(): void
	{
		$bus = new EventBus();
		$triggered = [false, false, false];

		$bus->subscribe(TestEvent::class, static function () use (&$triggered): void {
			$triggered[0] = true;
		});

		$bus->subscribe(TestEvent::class, static function (TestEvent $event) use (&$triggered): void {
			$triggered[1] = true;
			$event->stopPropagation();
		}, 1);

		$bus->subscribe(TestEvent::class, static function () use (&$triggered): void {
			$triggered[2] = true;
		}, 2);

		$bus->publish(new TestEvent(5));

		static::assertFalse($triggered[0]);
		static::assertTrue($triggered[1]);
		static::assertTrue($triggered[2]);
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
