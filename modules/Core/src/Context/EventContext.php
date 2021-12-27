<?php
declare(strict_types=1);

namespace Elephox\Core\Context;

use Elephox\Core\ActionType;
use Elephox\DI\Contract\Container;
use Elephox\Events\Contract\Event;

class EventContext extends AbstractContext implements Contract\EventContext
{
	public function __construct(
		Container $container,
		private Event     $event,
	)
	{
		parent::__construct(ActionType::Event, $container);

		$container->register(Contract\EventContext::class, $this);
	}

	public function getEvent(): Event
	{
		return $this->event;
	}
}
