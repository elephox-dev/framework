<?php

namespace Elephox\Web\Contract;

use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;

interface RouteHandler
{
	public function getMatchScore(Request $request): float;

	public function handle(Request $request): ResponseBuilder;
}
