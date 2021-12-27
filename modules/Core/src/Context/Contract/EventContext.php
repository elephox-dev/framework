<?php
declare(strict_types=1);

namespace Elephox\Core\Context\Contract;

use Elephox\Events\Contract\Event;

interface EventContext extends Context
{
	public function getEvent(): Event;
}
