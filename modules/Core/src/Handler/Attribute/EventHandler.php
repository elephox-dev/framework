<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Attribute;

use Attribute;
use Elephox\Core\ActionType;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Context\Contract\EventContext;
use JetBrains\PhpStorm\Pure;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class EventHandler extends AbstractHandlerAttribute
{
	#[Pure]
	public function __construct(
		private ?string $eventName = null,
		int $weight = 0,
	)
	{
		parent::__construct(ActionType::Event, $weight);
	}

	public function handles(Context $context): bool
	{
		if (!$context instanceof EventContext) {
			return false;
		}

		if ($this->eventName === null) {
			return true;
		}

		return $this->eventName === $context->getEvent()->getName();
	}
}
