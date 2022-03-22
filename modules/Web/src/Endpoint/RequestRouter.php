<?php
declare(strict_types=1);

namespace Elephox\Web\Endpoint;

use Elephox\Collection\Contract\Grouping;
use Elephox\Collection\ObjectSet;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\Response;
use Elephox\Http\ResponseCode;
use Elephox\Web\AmbiguousRouteHandlerException;
use Elephox\Web\Contract\RequestPipelineEndpoint;
use Elephox\Web\Contract\RouteHandler;
use Elephox\Web\Contract\Router;
use Elephox\Web\RouteNotFoundException;

class RequestRouter implements RequestPipelineEndpoint, Router
{
	/** @var ObjectSet<RouteHandler> $handlers */
	private readonly ObjectSet $handlers;

	public function __construct()
	{
		/** @var ObjectSet<RouteHandler> */
		$this->handlers = new ObjectSet();
	}

	public function getHandler(Request $request): RouteHandler
	{
		$matchingHandlers = $this->handlers
			->groupBy(fn(RouteHandler $handler) => $handler->getMatchScore($request))
			->orderByDescending(fn(Grouping $grouping) => $grouping->groupKey())
			->firstOrDefault(null)
			?->toList()
		;

		if ($matchingHandlers === null) {
			throw new RouteNotFoundException($request);
		}

		if (count($matchingHandlers) === 1) {
			return $matchingHandlers[0];
		}

		throw new AmbiguousRouteHandlerException($request);
	}

	public function handle(Request $request): ResponseBuilder
	{
		try {
			return $this->getHandler($request)->handle($request);
		} catch (RouteNotFoundException|AmbiguousRouteHandlerException $e) {
			return Response::build()
				->exception($e)
				->responseCode(ResponseCode::InternalServerError);
		}
	}
}
