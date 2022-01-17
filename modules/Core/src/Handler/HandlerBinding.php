<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Closure;
use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Collection\Contract\GenericList;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Handler\Contract\HandlerMeta;
use Elephox\Core\Middleware\Contract\Middleware;
use JetBrains\PhpStorm\Pure;

class HandlerBinding implements Contract\HandlerBinding
{
	/**
	 * @var GenericKeyedEnumerable<int, Middleware> $middlewares
	 */
	private GenericKeyedEnumerable $middlewares;

	/**
	 * @param Closure $closure
	 * @param HandlerMeta $handlerMeta
	 * @param GenericKeyedEnumerable<int, Middleware> $middlewares
	 */
	public function __construct(
		private Closure     $closure,
		private HandlerMeta $handlerMeta,
		GenericKeyedEnumerable $middlewares,
	) {
		$this->middlewares = $middlewares->orderBy(static fn(Middleware $m): int => $m->getWeight());
	}

	#[Pure]
	public function getHandlerMeta(): HandlerMeta
	{
		return $this->handlerMeta;
	}

	#[Pure]
	public function getMiddlewares(): GenericKeyedEnumerable
	{
		return $this->middlewares;
	}

	public function handle(Context $context): mixed
	{
		$contextHandler = function (Context $ctx): mixed {
			$parameters = $this->getHandlerMeta()->getHandlerParams($ctx);

			return $ctx->getContainer()->callback($this->closure, ['context' => $ctx, ...$parameters]);
		};

		foreach ($this->getMiddlewares() as $middleware) {
			$contextHandler = static fn(Context $ctx): mixed => $middleware->handle($ctx, $contextHandler);
		}

		return $contextHandler($context);
	}
}
