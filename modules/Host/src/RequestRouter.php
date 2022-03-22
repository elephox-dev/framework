<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Collection\Contract\Grouping;
use Elephox\Collection\ObjectSet;
use Elephox\Host\Contract\RequestPipelineEndpoint;
use Elephox\Host\Contract\RouteHandler;
use Elephox\Host\Contract\Router;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\Response;
use Elephox\Http\ResponseCode;

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
