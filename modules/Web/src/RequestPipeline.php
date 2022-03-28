<?php
declare(strict_types=1);

namespace Elephox\Web;

use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericList;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder as ResponseBuilderContract;
use Elephox\Http\Response;
use Elephox\Web\Contract\RequestPipelineEndpoint;
use Elephox\Web\Contract\WebMiddleware;
use Throwable;

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
		$previous = function (Request $r): ResponseBuilderContract {
			try {
				return $this->endpoint->handle($r);
			} catch (Throwable $e) {
				return Response::build()->exception($e);
			}
		};

		/** @var WebMiddleware $middleware */
		foreach ($this->getMiddlewares()->reverse() as $middleware) {
			$previous = static function (Request $r) use ($middleware, $previous): ResponseBuilderContract {
				try {
					return $middleware->handle($r, $previous);
				} catch (Throwable $e) {
					return Response::build()->exception($e);
				}
			};
		}

		return $previous($request);
	}
}