<?php
declare(strict_types=1);

namespace Elephox\Events;

class Event implements Contract\Event
{
	use ClassNameAsEventName;
}
