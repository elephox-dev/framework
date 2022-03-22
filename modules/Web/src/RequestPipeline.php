<?php
declare(strict_types=1);

namespace Elephox\Web;

use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericList;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder as ResponseBuilderContract;
use Elephox\Web\Contract\RequestPipelineEndpoint;
use Elephox\Web\Contract\WebMiddleware;

class RequestPipeline
{
	/**
	 * @param ArrayList<WebMiddleware> $middlewares
	 */
	public function __construct(
		private readonly RequestPipelineEndpoint $endpoint,
		private readonly ArrayList $middlewares,
	)
	{
	}

	public function getMiddlewares(): GenericList
	{
		return $this->middlewares;
	}

	/**
	 * @param Request $request
	 *
	 * @return ResponseBuilderContract
	 */
	public function process(Request $request): ResponseBuilderContract
	{
		$previous = fn (Request $r): ResponseBuilderContract => $this->endpoint->handle($r);

		/** @var WebMiddleware $middleware */
		foreach ($this->getMiddlewares()->reverse() as $middleware) {
			$previous = static fn (Request $r): ResponseBuilderContract => $middleware->handle($r, $previous);
		}

		return $previous($request);
	}
}
