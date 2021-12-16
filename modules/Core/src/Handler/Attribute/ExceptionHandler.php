<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Attribute;

use Attribute;
use Elephox\Core\ActionType;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Context\Contract\ExceptionContext;
use JetBrains\PhpStorm\Pure;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ExceptionHandler extends AbstractHandlerAttribute
{
	#[Pure] public function __construct(
		private ?string $exceptionClass = null,
		int $weight = 0,
	)
	{
		parent::__construct(ActionType::Exception, $weight);
	}

	public function getExceptionClass(): ?string
	{
		return $this->exceptionClass;
	}

	public function handles(Context $context): bool
	{
		if (!$context instanceof ExceptionContext) {
			return false;
		}

		if ($this->exceptionClass === null) {
			return true;
		}

		return get_class($context->getException()) instanceof $this->exceptionClass;
	}
}
