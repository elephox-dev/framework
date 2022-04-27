<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Closure;
use Elephox\Autoloading\Composer\NamespaceLoader;
use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Collection\Contract\Grouping;
use Elephox\Collection\ObjectSet;
use Elephox\DI\Contract\ServiceCollection;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\Response;
use Elephox\Http\ResponseCode;
use Elephox\Web\Contract\RequestPipelineEndpoint;
use Elephox\Web\Contract\WebMiddlewareAttribute;
use Elephox\Web\RouteNotFoundException;
use Elephox\Web\Routing\Attribute\Contract\ControllerAttribute;
use Elephox\Web\Routing\Attribute\Contract\RouteAttribute;
use Elephox\Web\Routing\Contract\RouteHandler as RouteHandlerContract;
use Elephox\Web\Routing\Contract\Router;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;

class RequestRouter implements RequestPipelineEndpoint, Router
{
	/**
	 * @return GenericKeyedEnumerable<int, ControllerAttribute>
	 *
	 * @param ReflectionClass $class
	 */
	private static function getControllers(ReflectionClass $class): GenericKeyedEnumerable
	{
		/** @var GenericKeyedEnumerable<int, ControllerAttribute> */
		return ArrayList::from($class->getAttributes(ControllerAttribute::class, ReflectionAttribute::IS_INSTANCEOF))
			->select(static fn (ReflectionAttribute $attribute): ControllerAttribute => /** @var ControllerAttribute */ $attribute->newInstance())
		;
	}

	/**
	 * @return GenericKeyedEnumerable<int, RouteAttribute>
	 *
	 * @param ReflectionMethod $method
	 */
	private static function getRoutes(ReflectionMethod $method): GenericKeyedEnumerable
	{
		/** @var GenericKeyedEnumerable<int, RouteAttribute> */
		return ArrayList::from($method->getAttributes(RouteAttribute::class, ReflectionAttribute::IS_INSTANCEOF))
			->select(static fn (ReflectionAttribute $attribute): RouteAttribute => /** @var RouteAttribute */ $attribute->newInstance())
		;
	}

	/**
	 * @return GenericKeyedEnumerable<int, WebMiddlewareAttribute>
	 *
	 * @param ReflectionClass|ReflectionMethod $reflection
	 */
	private static function getMiddlewares(ReflectionClass|ReflectionMethod $reflection): GenericKeyedEnumerable
	{
		/** @var GenericKeyedEnumerable<int, WebMiddlewareAttribute> */
		return ArrayList::from($reflection->getAttributes(WebMiddlewareAttribute::class, ReflectionAttribute::IS_INSTANCEOF))
			->select(static fn (ReflectionAttribute $attribute): WebMiddlewareAttribute => /** @var WebMiddlewareAttribute */ $attribute->newInstance())
		;
	}

	/**
	 * @var ObjectSet<RouteHandlerContract> $handlers
	 */
	private readonly ObjectSet $handlers;

	public function __construct(
		private readonly ServiceCollection $services,
	) {
		/** @var ObjectSet<RouteHandlerContract> */
		$this->handlers = new ObjectSet();
	}

	public function getRouteHandlers(): iterable
	{
		return $this->handlers;
	}

	public function getRouteHandler(Request $request): RouteHandlerContract
	{
		$matchedHandlersGroup = $this->handlers
			->where(static fn (RouteHandlerContract $handler): bool => $handler->matches($request))
			->groupBy(static fn (RouteHandlerContract $handler): float => $handler->getMatchScore($request))
			->orderByDescending(static fn (Grouping $grouping): mixed => $grouping->groupKey())
			->firstOrDefault(null)
		;

		if ($matchedHandlersGroup === null) {
			throw new RouteNotFoundException($request);
		}

		/** @var list<RouteHandlerContract> $orderedHandlers */
		$orderedHandlers = $matchedHandlersGroup
			->orderByDescending(static fn (RouteHandlerContract $handler): int => $handler->getSourceAttribute()->getWeight())
			->toList()
		;

		if (count($orderedHandlers) > 1 && $orderedHandlers[0]->getSourceAttribute()->getWeight() === $orderedHandlers[1]->getSourceAttribute()->getWeight()) {
			throw new AmbiguousRouteHandlerException($request, $orderedHandlers);
		}

		return $orderedHandlers[0];
	}

	public function add(RouteHandlerContract $handler): static
	{
		$this->handlers->add($handler);

		return $this;
	}

	public function handle(Request $request): ResponseBuilder
	{
		try {
			return $this->getRouteHandler($request)->handle($this->services);
		} catch (RouteNotFoundException $e) {
			return Response::build()->exception($e, ResponseCode::NotFound);
		} catch (AmbiguousRouteHandlerException $e) {
			return Response::build()->exception($e);
		}
	}

	/**
	 * @throws InvalidRequestController
	 *
	 * @param string $namespace
	 */
	public function loadFromNamespace(string $namespace): static
	{
		NamespaceLoader::iterateNamespace($namespace, function (string $className): void {
			$this->loadFromClass($className);
		});

		return $this;
	}

	/**
	 * @param class-string $className
	 *
	 * @throws InvalidRequestController
	 */
	public function loadFromClass(string $className): static
	{
		try {
			$classReflection = new ReflectionClass($className);
			$classMiddleware = self::getMiddlewares($classReflection)->toList();
			$classInstance = $this->services->getService($className) ?? $this->services->resolver()->instantiate($className);
			$classControllers = self::getControllers($classReflection)->toList();

			if ($classReflection->hasMethod('__invoke')) {
				$methodReflection = $classReflection->getMethod('__invoke');
				$returnType = $methodReflection->getReturnType();
				if (!$returnType instanceof ReflectionNamedType || $returnType->getName() !== ResponseBuilder::class) {
					throw new InvalidRequestController($className);
				}

				foreach ($classControllers as $controllerAttribute) {
					$callback = Closure::fromCallable($classInstance);
					$routeHandler = new RouteHandler($controllerAttribute, null, $className, '__invoke', $classMiddleware, $callback);
					$this->add($routeHandler);
				}
			}

			foreach ($classReflection->getMethods(ReflectionMethod::IS_PUBLIC) as $methodReflection) {
				if ($methodReflection->getName() === '__invoke') {
					continue;
				}

				$returnType = $methodReflection->getReturnType();
				if (!$returnType instanceof ReflectionNamedType || $returnType->getName() !== ResponseBuilder::class) {
					throw new InvalidRequestHandler($className, $methodReflection->getName());
				}

				$methodMiddleware = [...$classMiddleware, ...self::getMiddlewares($methodReflection)->toList()];
				foreach (self::getRoutes($methodReflection) as $routeAttribute) {
					$callback = $methodReflection->getClosure($classInstance) ?? throw new InvalidRequestHandler($className, $methodReflection->getName());
					if (empty($classControllers)) {
						$routeHandler = new RouteHandler($routeAttribute, null, $className, $methodReflection->getName(), $methodMiddleware, $callback);
						$this->add($routeHandler);
					} else {
						foreach ($classControllers as $controllerAttribute) {
							$routeHandler = new RouteHandler($controllerAttribute, $routeAttribute, $className, $methodReflection->getName(), $methodMiddleware, $callback);
							$this->add($routeHandler);
						}
					}
				}
			}

			return $this;
		} catch (ReflectionException $e) {
			throw new InvalidRequestController($className, previous: $e);
		}
	}
}
