<?php
declare(strict_types=1);

namespace Elephox\Host\Middleware;

use Closure;
use Elephox\Collection\ObjectSet;
use Elephox\Host\Contract\RouteHandler;
use Elephox\Host\Contract\Router;
use Elephox\Host\Contract\WebMiddleware;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;

class RequestRouter implements WebMiddleware, Router
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
		$this->handlers
			->orderByDescending(fn(RouteHandler $handler) => $handler->getMatchScore($request))
			->toList();
	}

	public function handle(Request $request, Closure $next): ResponseBuilder
	{
		$url = (string)$request->getUrl();

	}
}
