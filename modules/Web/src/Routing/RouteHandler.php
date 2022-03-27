<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Closure;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\OOR\Regex;
use Elephox\Web\Contract\WebMiddleware;
use Elephox\Web\Routing\Attribute\Contract\ControllerAttribute;
use Elephox\Web\Routing\Attribute\Contract\RouteAttribute;

class RouteHandler implements Contract\RouteHandler
{
	private readonly string $pathRegex;

	/**
	 * @param ControllerAttribute $controllerAttribute
	 * @param null|RouteAttribute $routeAttribute
	 * @param non-empty-string $attributeLocation
	 * @param iterable<int, WebMiddleware> $middlewares
	 * @param Closure(Request): ResponseBuilder $handler
	 */
	public function __construct(
		private readonly ControllerAttribute $controllerAttribute,
		private readonly ?RouteAttribute $routeAttribute,
		private readonly string $attributeLocation,
		private readonly iterable $middlewares,
		private readonly Closure $handler,
	)
	{
		$controllerPath = $this->controllerAttribute->getPath();
		$routePath = $this->routeAttribute?->getPath() ?? "";

		if (!str_starts_with($controllerPath, 'regex:')) {
			$controllerPath = Regex::escape(trim($controllerPath, '/'));
		} else {
			$controllerPath = substr($controllerPath, 6);
		}

		if (!str_starts_with($routePath, 'regex:')) {
			$routePath = trim($routePath, '/');
			if (strlen($controllerPath) > 0 && strlen($routePath) > 0) {
				$routePath = "/$routePath";
			}
			$routePath = Regex::escape($routePath);
		} else {
			$routePath = substr($routePath, 6);
		}

		$this->pathRegex = sprintf("/^%s%s$/i", $controllerPath, $routePath);
	}

	public function __toString(): string
	{
		$attributeName = array_slice(explode('\\', $this->routeAttribute::class ?? $this->controllerAttribute::class), -1, 1)[0];

		return "[$attributeName] $this->attributeLocation";
	}

	public function getMatchScore(Request $request): float
	{
		return Regex::specificity($this->pathRegex, $this->getNormalizedRequestRoute($request));
	}

	public function matches(Request $request): bool
	{
		return Regex::matches($this->pathRegex, $this->getNormalizedRequestRoute($request));
	}

	private function getNormalizedRequestRoute(Request $request): string
	{
		return ltrim($request->getUrl()->path, '/');
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
