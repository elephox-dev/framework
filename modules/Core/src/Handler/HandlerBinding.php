<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Closure;
use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericList;
use Elephox\Collection\Contract\GenericList;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Handler\Contract\HandlerMeta;
use Elephox\Core\Middleware\Contract\Middleware;
use JetBrains\PhpStorm\Pure;

class HandlerBinding implements Contract\HandlerBinding
{
	/**
	 * @var GenericList<Middleware> $middlewares
	 */
	private GenericList $middlewares;

	/**
	 * @param Closure $closure
	 * @param HandlerMeta $handlerMeta
	 * @param GenericList<Middleware> $middlewares
	 */
	public function __construct(
        private Closure     $closure,
        private HandlerMeta $handlerMeta,
        GenericList         $middlewares,
	) {
		$this->middlewares = ArrayList::fromArray($middlewares)
			->orderBy(static fn(Middleware $a, Middleware $b): int => $b->getWeight() - $a->getWeight());
	}

	#[Pure] public function getHandlerMeta(): HandlerMeta
	{
		return $this->handlerMeta;
	}

	#[Pure] public function getMiddlewares(): GenericList
	{
		return $this->middlewares->asReadonly();
	}

	public function handle(Context $context): mixed
	{
		$contextHandler = function (Context $ctx): mixed {
			$parameters = $this->getHandlerMeta()->getHandlerParams($ctx);

			return $ctx->getContainer()->callback($this->closure, ['context' => $ctx, ...$parameters]);
		};

		$middlewares = $this
			->getMiddlewares();
		foreach ($middlewares as $middleware) {
			$contextHandler = static fn (Context $ctx): mixed => $middleware->handle($ctx, $contextHandler);
		}

		return $contextHandler($context);
	}
}
