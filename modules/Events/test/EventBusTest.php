<?php
declare(strict_types=1);

namespace Elephox\Events;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Events\EventBus
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\ArrayMap
 * @uses \Elephox\Events\ClassNameAsEventName
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
}

class TestEvent extends Event
{
	public function __construct(public readonly int $data)
	{
	}
}
