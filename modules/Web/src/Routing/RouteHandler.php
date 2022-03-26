<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Closure;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Web\Contract\WebMiddleware;
use Elephox\Web\Routing\Attribute\Contract\ControllerAttribute;

class RouteHandler implements Contract\RouteHandler
{
	/**
	 * @param ControllerAttribute $attribute
	 * @param non-empty-string $attributeLocation
	 * @param iterable<int, WebMiddleware> $middlewares
	 * @param Closure(Request): ResponseBuilder $handler
	 */
	public function __construct(
		private readonly ControllerAttribute $attribute,
		private readonly string $attributeLocation,
		private readonly iterable $middlewares,
		private readonly Closure $handler,
	)
	{
	}

	public function __toString(): string
	{
		$attributeName = array_slice(explode('\\', $this->attribute::class), -1, 1)[0];

		return "[$attributeName] $this->attributeLocation";
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

	public function getMiddlewares(): iterable
	{
		return $this->middlewares;
	}
}
