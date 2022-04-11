<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Contract;

use Elephox\DI\Contract\ServiceCollection;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Web\Contract\WebMiddleware;
use Elephox\Web\Routing\Attribute\Contract\ControllerAttribute;
use Stringable;

interface RouteHandler extends Stringable
{
	public function getMatchScore(Request $request): float;

	public function matches(Request $request): bool;

	public function handle(ServiceCollection $services): ResponseBuilder;

	/**
	 * @return class-string
	 */
	public function getAttributeClass(): string;

	public function getAttributeMethod(): string;

	public function getPathRegex(): string;

	public function getSourceAttribute(): ControllerAttribute;

	/**
	 * @return iterable<int, WebMiddleware>
	 */
	public function getMiddlewares(): iterable;
}
