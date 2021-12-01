<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Closure;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Handler\Attribute\Contract\HandlerAttribute;

/**
 * @template THandler as Closure(): mixed
 * @template TContext as Context
 *
 * @template-implements Contract\HandlerBinding<THandler, TContext>
 */
class HandlerBinding implements Contract\HandlerBinding
{
	/**
	 * @param THandler $handler
	 * @param HandlerAttribute $attribute
	 */
	public function __construct(
		private Closure $handler,
		private HandlerAttribute $attribute,
	)
	{
	}

	/**
	 * @return THandler
	 */
	public function getHandler(): Closure
	{
		return $this->handler;
	}

	/**
	 * @param TContext $context
	 */
	public function isApplicable(Context $context): bool
	{
		if ($context->getActionType() !== $this->attribute->getType()) {
			return false;
		}

		return $this->attribute->handles($context);
	}

	public function handle(Context $context): void
	{
		$this->attribute->invoke($this->handler, $context);
	}

	public function getWeight(): int
	{
		return $this->attribute->getWeight();
	}
}
