<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Contract;

use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Web\Contract\WebMiddleware;
use Elephox\Web\Routing\Attribute\Contract\ControllerAttribute;
use Stringable;

interface RouteHandler extends Stringable
{
	public function getMatchScore(Request $request): float;

	public function matches(Request $request): bool;

	public function handle(Request $request): ResponseBuilder;

	public function getSourceAttribute(): ControllerAttribute;

	/**
	 * @return iterable<int, WebMiddleware>
	 */
	public function getMiddlewares(): iterable;
}
