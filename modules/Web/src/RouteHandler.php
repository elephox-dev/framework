<?php
declare(strict_types=1);

namespace Elephox\Web;

use Closure;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Core\Handler\Attribute\RequestHandler;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Web\Contract\WebMiddleware;

class RouteHandler implements Contract\RouteHandler
{
	/**
	 * @param non-empty-string $functionName
	 * @param GenericKeyedEnumerable<int, WebMiddleware> $middlewares
	 * @param Closure $handler
	 */
	public function __construct(
		private string $functionName,
		private GenericKeyedEnumerable $middlewares,
		private RequestHandler $handlerAttribute,
		private Closure $handler,
	)
	{
	}

	public function __toString(): string
	{
		return $this->functionName;
	}

	public function getMiddlewares(): GenericKeyedEnumerable
	{
		return $this->middlewares;
	}

	public function getMatchScore(Request $request): float
	{
		// TODO: Implement getMatchScore() method.
	}

	public function handle(Request $request): ResponseBuilder
	{
		// TODO: Implement handle() method.
	}
}
