<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Closure;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Handler\Contract\HandlerMeta;

class HandlerBinding implements Contract\HandlerBinding
{
	/**
	 * @param Closure $closure
	 * @param HandlerMeta $handlerMeta
	 */
	public function __construct(
		private Closure     $closure,
		private HandlerMeta $handlerMeta,
	)
	{
	}

	public function getHandlerMeta(): HandlerMeta
	{
		return $this->handlerMeta;
	}

	public function handle(Context $context): mixed
	{
		$parameters = $this->getHandlerMeta()->getHandlerParams($context);

		return $context->getContainer()->callback($this->closure, ['context' => $context, ...$parameters]);
	}
}
