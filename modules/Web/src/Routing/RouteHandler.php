<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Closure;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\DI\Contract\ServiceCollection;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\RequestMethod;
use Elephox\OOR\Regex;
use Elephox\Web\Contract\WebMiddleware;
use Elephox\Web\Routing\Attribute\Contract\ControllerAttribute;
use Elephox\Web\Routing\Attribute\Contract\RouteAttribute;
use Elephox\Web\Routing\Attribute\Controller;

class RouteHandler implements Contract\RouteHandler
{
	/**
	 * @param Closure(): ResponseBuilder $callback
	 * @param non-empty-string|RequestMethod|iterable<non-empty-string|RequestMethod> $methods
	 * @param string $path
	 * @param int $weight
	 */
	public static function fromCallback(string $path, Closure $callback, int $weight = Controller::DEFAULT_WEIGHT, string|RequestMethod|iterable $methods = []): self
	{
		return new self(
			new Controller($path, $weight, $methods),
			null,
			(new class {
			})::class,
			'__invoke',
			[],
			$callback,
		);
	}

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

		$patternWrapper = '#';

		if (!str_starts_with($controllerPath, 'regex:')) {
			if (str_ends_with($controllerPath, 'Controller')) {
				$controllerPath = substr($controllerPath, 0, -10);
			}

			$controllerPath = Regex::escape(trim($controllerPath, $patternWrapper));
		} else {
			$controllerPath = substr($controllerPath, 6);
		}

		if (!str_starts_with($routePath, 'regex:')) {
			if ($this->attributeMethod === 'index' || $this->attributeMethod === '__invoke') {
				$routePath = '';
			} else {
				$routePath = trim($routePath, $patternWrapper);
			}

			if ($controllerPath !== '' && $routePath !== '') {
				$routePath = "/$routePath";
			}

			$routePath = Regex::escape($routePath);
		} else {
			$routePath = substr($routePath, 6);
		}

		$this->pathRegex = sprintf('%s^%s%s$%si', $patternWrapper, $controllerPath, $routePath, $patternWrapper);
	}

	public function __toString(): string
	{
		if ($this->routeAttribute !== null) {
			$className = $this->routeAttribute::class;
		} else {
			$className = $this->controllerAttribute::class;
		}

		$attributeName = array_slice(explode('\\', $className), -1, 1)[0];

		return "[$attributeName $this->pathRegex] $this->attributeClass::$this->attributeMethod";
	}

	public function getMatchScore(Request $request): float
	{
		return Regex::specificity($this->pathRegex, $this->getNormalizedRequestRoute($request));
	}

	public function matches(Request $request): bool
	{
		$method = $request->getMethod();
		$handledMethods = $this->getHandledRequestMethods();
		if (!$handledMethods->isEmpty() && !$handledMethods->contains($method)) {
			return false;
		}

		$normalizedRoute = $this->getNormalizedRequestRoute($request);

		return Regex::matches($this->pathRegex, $normalizedRoute);
	}

	private function getHandledRequestMethods(): GenericKeyedEnumerable
	{
		return $this->controllerAttribute->getRequestMethods()->appendAll($this->routeAttribute?->getRequestMethods() ?? []);
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
