<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Attribute;

use Attribute;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Context\Contract\ExceptionContext;
use Elephox\Core\Handler\ActionType;
use Exception;
use JetBrains\PhpStorm\Pure;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class ExceptionHandler extends AbstractHandlerAttribute
{
	#[Pure] public function __construct(
		private ?string $exceptionClass = null,
	)
	{
		parent::__construct(ActionType::Exception);
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

	/**
	 * @throws Exception
	 */
	public function invoke(object $handler, string $method, Context $context): void
	{
		if (!$context instanceof ExceptionContext) {
			// TODO: do something else with this
			throw new Exception('Invalid context type');
		}

		$context->getContainer()->call($handler, $method, ['context' => $context]);
	}
}
