<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Closure;
use Elephox\Collection\Contract\GenericList;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Web\Contract\WebMiddleware;
use Elephox\Web\Routing\Attribute\Contract\RouteAttribute;

class RouteHandler implements Contract\RouteHandler
{
	/**
	 * @param RouteAttribute $attribute
	 * @param non-empty-string $attributeLocation
	 * @param GenericList<WebMiddleware> $middlewares
	 * @param Closure(Request): ResponseBuilder $handler
	 */
	public function __construct(
		private readonly RouteAttribute $attribute,
		private readonly string $attributeLocation,
		private readonly GenericList $middlewares,
		private readonly Closure $handler,
	)
	{
	}

	public function __toString(): string
	{
		return array_slice(explode('\\', $this->attribute::class), -1, 1)[0] . '@' . $this->attributeLocation;
	}

	public function getMatchScore(Request $request): float
	{
		// FIXME: replace with real implementation once $this->matches() is implemented
		return $this->matches($request) ? 1.0 : 0.0;
	}

	public function matches(Request $request): bool
	{
		// FIXME: this is a temporary solution (need to check request method, specificity of regex, etc.)
		return $this->attribute->getPath() === $request->getUrl()->path;
	}

	public function handle(Request $request): ResponseBuilder
	{
		return ($this->handler)($request);
	}

	public function getMiddlewares(): GenericList
	{
		return $this->middlewares;
	}
}
