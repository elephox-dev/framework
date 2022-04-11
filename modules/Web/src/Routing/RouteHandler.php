<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Closure;
use Elephox\DI\Contract\ServiceCollection;
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
	 * @param class-string $attributeClass
	 * @param string $attributeMethod
	 * @param iterable<int, WebMiddleware> $middlewares
	 * @param Closure $handler
	 */
	public function __construct(
		private readonly ControllerAttribute $controllerAttribute,
		private readonly ?RouteAttribute $routeAttribute,
		private readonly string $attributeClass,
		private readonly string $attributeMethod,
		private readonly iterable $middlewares,
		private readonly Closure $handler,
	) {
		$controllerPath = $this->controllerAttribute->getPath() ?? array_slice(explode('\\', $this->attributeClass), -1, 1)[0];
		$routePath = $this->routeAttribute?->getPath() ?? $this->attributeMethod;

		if (!str_starts_with($controllerPath, 'regex:')) {
			if (str_ends_with($controllerPath, 'Controller')) {
				$controllerPath = substr($controllerPath, 0, -10);
			}

			$controllerPath = Regex::escape(trim($controllerPath, '/'));
		} else {
			$controllerPath = substr($controllerPath, 6);
		}

		if (!str_starts_with($routePath, 'regex:')) {
			if ($this->attributeMethod === 'index' || $this->attributeMethod === '__invoke') {
				$routePath = '';
			} else {
				$routePath = trim($routePath, '/');
			}

			if ($controllerPath !== '' && $routePath !== '') {
				$routePath = "/$routePath";
			}

			$routePath = Regex::escape($routePath);
		} else {
			$routePath = substr($routePath, 6);
		}

		$this->pathRegex = sprintf('/^%s%s$/i', $controllerPath, $routePath);
	}

	public function __toString(): string
	{
		$attributeName = array_slice(explode('\\', $this->routeAttribute::class ?? $this->controllerAttribute::class), -1, 1)[0];

		return "[$attributeName] $this->attributeClass::$this->attributeMethod";
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

	public function handle(ServiceCollection $services): ResponseBuilder
	{
		$request = $services->requireService(Request::class);

		$matchedParametersMap = MatchedUrlParametersMap::fromRegex($this->getNormalizedRequestRoute($request), $this->pathRegex);
		$services->addSingleton(MatchedUrlParametersMap::class, implementation: $matchedParametersMap, replace: true);

		/** @var ResponseBuilder */
		return $services->resolver()->callback($this->handler, [
			...$matchedParametersMap,
			'request' => $request,
		]);
	}

	public function getSourceAttribute(): ControllerAttribute
	{
		return $this->routeAttribute ?? $this->controllerAttribute;
	}

	public function getMiddlewares(): iterable
	{
		return $this->middlewares;
	}

	public function getAttributeClass(): string
	{
		return $this->attributeClass;
	}

	public function getAttributeMethod(): string
	{
		return $this->attributeMethod;
	}

	public function getPathRegex(): string
	{
		return $this->pathRegex;
	}
}
