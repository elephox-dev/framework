<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Handler\Attribute\AbstractHandler;

/**
 * @template THandler as object
 * @template TContext as Context
 *
 * @template-implements Contract\HandlerBinding<THandler, TContext>
 */
class HandlerBinding implements Contract\HandlerBinding
{
	/**
	 * @param THandler $handler
	 * @param string $method
	 * @param AbstractHandler $attribute
	 */
	public function __construct(
		private object          $handler,
		private string          $method,
		private AbstractHandler $attribute,
	)
	{
	}

	public function getMethodName(): string
	{
		return $this->method;
	}

	/**
	 * @return THandler
	 */
	public function getHandler(): object
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

	/**
	 * @param TContext $context
	 */
	public function handle(Context $context): void
	{
		$this->attribute->invoke($this->handler, $this->method, $context);
	}
}
