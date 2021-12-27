<?php
declare(strict_types=1);

namespace Elephox\Events;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Events\EventBus
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Events\NamedEvent
 * @uses   \Elephox\Events\ClassNameAsEventName
 */
class EventBusTest extends TestCase
{
	public function testPubSub(): void
	{
		$bus = new EventBus();

		$triggered = false;
		$id = $bus->subscribe(TestEvent::class, function (TestEvent $event) use (&$triggered) {
			$triggered = true;

			self::assertEquals(5, $event->data);
		});

		$bus->publish(new TestEvent(5));

		self::assertTrue($triggered);

		$bus->unsubscribe($id);

		$triggered = false;

		$bus->publish(new TestEvent(5));

		self::assertFalse($triggered);
	}

	public function testPubSubNamed(): void
	{
		$bus = new EventBus();
		$triggeredA1 = false;
		$triggeredB = false;

		$bus->subscribe('testA', function (TestNamedEvent $event) use (&$triggeredA1) {
			$triggeredA1 = true;

			self::assertEquals(5, $event->data);
		});

		$idA2 = $bus->subscribe('testA', function () use (&$triggeredA2) {
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

		$bus->unsubscribe($idA2);
		$bus->unsubscribe($idA2);
	}

	public function testEventNameFromClass(): void
	{
		$testEvent = new TestEvent(5);

		self::assertEquals(TestEvent::class, $testEvent->getName());
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
