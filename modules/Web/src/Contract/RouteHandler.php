<?php

namespace Elephox\Web\Contract;

use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Stringable;

interface RouteHandler extends Stringable
{
	public function getMatchScore(Request $request): float;

	/**
	 * @return GenericKeyedEnumerable<int, WebMiddleware>
	 */
	public function getMiddlewares(): GenericKeyedEnumerable;

	public function handle(Request $request): ResponseBuilder;
}
