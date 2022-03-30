<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Closure;
use Elephox\Autoloading\Composer\NamespaceLoader;
use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Collection\Contract\Grouping;
use Elephox\Collection\ObjectSet;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\Response;
use Elephox\Http\ResponseCode;
use Elephox\Support\Composer\Contract\ComposerAutoloaderInit;
use Elephox\Support\Composer\Contract\ComposerClassLoader;
use Elephox\Web\Contract\RequestPipelineEndpoint;
use Elephox\Web\Contract\Router;
use Elephox\Web\Contract\WebMiddlewareAttribute;
use Elephox\Web\Contract\WebServiceCollection;
use Elephox\Web\RouteNotFoundException;
use Elephox\Web\Routing\Attribute\Contract\ControllerAttribute;
use Elephox\Web\Routing\Attribute\Contract\RouteAttribute;
use Elephox\Web\Routing\Contract\RouteHandler as RouteHandlerContract;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;

class RequestRouter implements RequestPipelineEndpoint, Router
{

	/**
	 * @param ReflectionClass $class
	 * @return GenericKeyedEnumerable<int, ControllerAttribute>
	 */
	private static function getControllers(ReflectionClass $class): GenericKeyedEnumerable
	{
		/** @var GenericKeyedEnumerable<int, ControllerAttribute> */
		return ArrayList::from($class->getAttributes(ControllerAttribute::class, ReflectionAttribute::IS_INSTANCEOF))
			->select(fn(ReflectionAttribute $attribute): ControllerAttribute => /** @var ControllerAttribute */ $attribute->newInstance());
	}

	/**
	 * @param ReflectionMethod $method
	 * @return GenericKeyedEnumerable<int, RouteAttribute>
	 */
	private static function getRoutes(ReflectionMethod $method): GenericKeyedEnumerable
	{
		/** @var GenericKeyedEnumerable<int, RouteAttribute> */
		return ArrayList::from($method->getAttributes(RouteAttribute::class, ReflectionAttribute::IS_INSTANCEOF))
			->select(fn(ReflectionAttribute $attribute): RouteAttribute => /** @var RouteAttribute */ $attribute->newInstance());
	}

	/**
	 * @param ReflectionClass|ReflectionMethod $reflection
	 * @return GenericKeyedEnumerable<int, WebMiddlewareAttribute>
	 */
	private static function getMiddlewares(ReflectionClass|ReflectionMethod $reflection): GenericKeyedEnumerable
	{
		/** @var GenericKeyedEnumerable<int, WebMiddlewareAttribute> */
		return ArrayList::from($reflection->getAttributes(WebMiddlewareAttribute::class, ReflectionAttribute::IS_INSTANCEOF))
			->select(fn(ReflectionAttribute $attribute): WebMiddlewareAttribute => /** @var WebMiddlewareAttribute */ $attribute->newInstance());
	}

	/** @var ObjectSet<RouteHandlerContract> $handlers */
	private readonly ObjectSet $handlers;

	public function __construct(
		private readonly WebServiceCollection $services,
	)
	{
		/** @var ObjectSet<RouteHandlerContract> */
		$this->handlers = new ObjectSet();
	}

	public function getRouteHandler(Request $request): RouteHandlerContract
	{
		$matchedHandlers = $this->handlers
			->where(fn(RouteHandlerContract $handler): bool => $handler->matches($request))
			->groupBy(fn(RouteHandlerContract $handler): float => $handler->getMatchScore($request))
			->orderBy(fn(Grouping $grouping): mixed => $grouping->groupKey())
			->firstOrDefault(null)
			?->toList()
		;

		if ($matchedHandlers === null) {
			throw new RouteNotFoundException($request);
		}

		if (count($matchedHandlers) === 1) {
			return $matchedHandlers[0];
		}

		throw new AmbiguousRouteHandlerException($request, $matchedHandlers);
	}

	public function add(RouteHandlerContract $handler): static
	{
		$this->handlers->add($handler);

		return $this;
	}

	public function handle(Request $request): ResponseBuilder
	{
		try {
			return $this->getRouteHandler($request)->handle($request);
		} catch (RouteNotFoundException|AmbiguousRouteHandlerException $e) {
			return Response::build()->exception($e, ResponseCode::NotFound);
		}
	}

	/**
	 * @throws InvalidRequestController
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
	 * @throws InvalidRequestController
	 */
	public function loadFromClass(string $className): static
	{
		try {
			$classReflection = new ReflectionClass($className);
			$classMiddleware = self::getMiddlewares($classReflection)->toList();
			$classInstance = $this->services->get($className) ?? $this->services->resolver()->instantiate($className);
			$classControllers = self::getControllers($classReflection)->toList();

			if ($classReflection->hasMethod('__invoke')) {
				$methodReflection = $classReflection->getMethod('__invoke');
				$returnType = $methodReflection->getReturnType();
				if (!$returnType instanceof ReflectionNamedType || $returnType->getName() !== ResponseBuilder::class) {
					throw new InvalidRequestController($className);
				}

				foreach ($classControllers as $controllerAttribute) {
					// TODO: make this tidier
					$callback = Closure::fromCallable($classInstance);
					$handler = fn(Request $request): ResponseBuilder => /** @var ResponseBuilder */ $this->services->resolver()->callback($callback, ['request' => $request]);
					$routeHandler = new RouteHandler($controllerAttribute, null, $className, "__invoke", $classMiddleware, $handler);
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
					$handler = fn(Request $request): ResponseBuilder => /** @var ResponseBuilder */ $this->services->resolver()->callback($callback, ['request' => $request]);
					foreach ($classControllers as $controllerAttribute) {
						$routeHandler = new RouteHandler($controllerAttribute, $routeAttribute, $className, $methodReflection->getName(), $methodMiddleware, $handler);
						$this->add($routeHandler);
					}
				}
			}

			return $this;
		} catch (ReflectionException $e) {
			throw new InvalidRequestController($className, previous: $e);
		}
	}
}
