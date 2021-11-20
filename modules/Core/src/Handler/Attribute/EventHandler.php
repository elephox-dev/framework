<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Attribute;

use Attribute;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Context\Contract\EventContext;
use Elephox\Core\Handler\ActionType;
use Exception;
use JetBrains\PhpStorm\Pure;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class EventHandler extends AbstractHandlerAttribute
{
	#[Pure] public function __construct(
		private ?string $eventName = null,
	)
	{
		parent::__construct(ActionType::Event);
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

	/**
	 * @throws Exception
	 */
	public function invoke(object $handler, string $method, Context $context): void
	{
		if (!$context instanceof EventContext) {
			throw new Exception('Invalid context type');
		}

		$context->getContainer()->call($handler, $method, ['context' => $context]);
	}
}
