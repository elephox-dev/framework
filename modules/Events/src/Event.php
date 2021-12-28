<?php
declare(strict_types=1);

namespace Elephox\Events;

abstract class Event implements Contract\Event
{
	use ClassNameAsEventName, StopsPropagation;
}
