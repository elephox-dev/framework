<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Collection\ObjectSet;
use Elephox\Host\Contract\RouteHandler;
use Elephox\Http\Contract\Request;

class Router implements Contract\Router
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
			->toList()
		;
	}
}
