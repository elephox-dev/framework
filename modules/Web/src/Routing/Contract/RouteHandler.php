<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Contract;

use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Collection\Contract\GenericList;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Web\Contract\WebMiddleware;
use Stringable;

interface RouteHandler extends Stringable
{
	public function getMatchScore(Request $request): float;

	public function matches(Request $request): bool;

	public function handle(Request $request): ResponseBuilder;

	/**
	 * @return iterable<int, WebMiddleware>
	 */
	public function getMiddlewares(): iterable;
}
