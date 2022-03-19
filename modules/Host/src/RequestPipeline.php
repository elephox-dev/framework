<?php
declare(strict_types=1);

namespace Elephox\Host;

use Closure;
use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericList;
use Elephox\Host\Contract\WebMiddleware;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\Response;
use Elephox\Http\Contract\ResponseBuilder as ResponseBuilderContract;

class RequestPipeline
{
	/**
	 * @param ArrayList<WebMiddleware> $middlewares
	 */
	public function __construct(
		private readonly ArrayList $middlewares
	)
	{
	}

	public function getMiddlewares(): GenericList
	{
		return $this->middlewares;
	}

	/**
	 * @param Closure(Request): ResponseBuilderContract $previous
	 * @param Request $request
	 *
	 * @return ResponseBuilderContract
	 */
	public function process(Closure $previous, Request $request): ResponseBuilderContract
	{
		/** @var WebMiddleware $middleware */
		foreach ($this->getMiddlewares()->reverse() as $middleware) {
			$previous = static fn (Request $r): ResponseBuilderContract => $middleware->handle($r, $previous);
		}

		return $previous($request);
	}
}
