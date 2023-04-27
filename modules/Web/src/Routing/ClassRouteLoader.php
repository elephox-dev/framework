<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Collection\Enumerable;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Web\Contract\WebMiddleware;
use Elephox\Web\Contract\WebMiddlewareAttribute;
use Elephox\Web\Routing\Attribute\Contract\ActionAttribute;
use Elephox\Web\Routing\Attribute\Contract\ControllerAttribute;
use Elephox\Web\Routing\Contract\RouteData;
use Elephox\Web\Routing\Contract\RouteLoader;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;

readonly class ClassRouteLoader implements RouteLoader
{
	/**
	 * @param ReflectionClass $class
	 *
	 * @return GenericKeyedEnumerable<int, ControllerAttribute>
	 */
	private static function getClassControllerAttributes(
		ReflectionClass $class,
	): GenericKeyedEnumerable {
		/** @var GenericKeyedEnumerable<int, ControllerAttribute> */
		return ArrayList::from($class->getAttributes(
			ControllerAttribute::class,
			ReflectionAttribute::IS_INSTANCEOF,
		))->select(static fn (
			ReflectionAttribute $attribute,
		): ControllerAttribute => /** @var ControllerAttribute */ $attribute->newInstance());
	}

	/**
	 * @param ReflectionClass $class
	 *
	 * @return GenericKeyedEnumerable<int, ActionAttribute>
	 */
	private static function getClassActionAttributes(
		ReflectionClass $class,
	): GenericKeyedEnumerable {
		/** @var GenericKeyedEnumerable<int, ActionAttribute> */
		return ArrayList::from($class->getAttributes(
			ActionAttribute::class,
			ReflectionAttribute::IS_INSTANCEOF,
		))->select(static fn (
			ReflectionAttribute $attribute,
		): ActionAttribute => /** @var ActionAttribute */ $attribute->newInstance());
	}

	/**
	 * @param ReflectionMethod $method
	 *
	 * @return GenericKeyedEnumerable<int, ActionAttribute>
	 */
	private static function getMethodActionAttributes(ReflectionMethod $method): GenericKeyedEnumerable
	{
		/** @var GenericKeyedEnumerable<int, ActionAttribute> */
		return ArrayList::from($method->getAttributes(
			ActionAttribute::class,
			ReflectionAttribute::IS_INSTANCEOF,
		))->select(static fn (
			ReflectionAttribute $attribute,
		): ActionAttribute => /** @var ActionAttribute */ $attribute->newInstance());
	}

	/**
	 * @param ReflectionClass|ReflectionMethod $reflection
	 *
	 * @return GenericKeyedEnumerable<int, WebMiddlewareAttribute>
	 */
	private static function getMiddlewareAttributes(
		ReflectionClass|ReflectionMethod $reflection,
	): GenericKeyedEnumerable {
		/** @var GenericKeyedEnumerable<int, WebMiddlewareAttribute> */
		return ArrayList::from($reflection->getAttributes(
			WebMiddlewareAttribute::class,
			ReflectionAttribute::IS_INSTANCEOF,
		))->select(static fn (
			ReflectionAttribute $attribute,
		): WebMiddlewareAttribute => /** @var WebMiddlewareAttribute */ $attribute->newInstance());
	}

	/**
	 * @param class-string $className
	 *
	 * @throws ReflectionException
	 */
	public function __construct(
		public string $className,
	) {
		$this->classReflection = new ReflectionClass($this->className);
		$this->classMiddlewares = self::getMiddlewareAttributes($this->classReflection)->toArrayList();
		$this->classControllers = self::getClassControllerAttributes($this->classReflection)->toArrayList();
		$this->classActions = self::getClassActionAttributes($this->classReflection)->toArrayList();
		$this->hasClassControllers = $this->classControllers->isNotEmpty();
	}

	private ReflectionClass $classReflection;

	/**
	 * @var ArrayList<WebMiddlewareAttribute> $classMiddlewares
	 */
	private ArrayList $classMiddlewares;

	private ArrayList $classControllers;
	private ArrayList $classActions;
	private bool $hasClassControllers;

	public function getRoutes(): GenericEnumerable
	{
		/** @var Enumerable<RouteData> */
		return new Enumerable(function () {
			$publicMethods = $this->classReflection->getMethods(ReflectionMethod::IS_PUBLIC);

			foreach ($publicMethods as $methodReflection) {
				yield from $this->getRoutesFromMethod($methodReflection);
			}
		});
	}

	private function validateReturnType(ReflectionMethod $methodReflection): void
	{
		$returnType = $methodReflection->getReturnType();
		if (!$returnType instanceof ReflectionNamedType ||
			$returnType->getName() !== ResponseBuilder::class) {
			throw new InvalidRequestHandlerReturnType($this->className, $methodReflection->getName());
		}
	}

	private function getRoutesFromMethod(ReflectionMethod $methodReflection): iterable
	{
		if ($methodReflection->getName() === '__invoke') {
			yield from $this->getRoutesFromInvoke($methodReflection);

			return;
		}

		$methodActions = self::getMethodActionAttributes($methodReflection)->toArrayList();
		if ($methodActions->isEmpty()) {
			return;
		}

		/** @var ArrayList<WebMiddleware> $methodMiddlewares */
		$methodMiddlewares = $this->classMiddlewares
			->concat(self::getMiddlewareAttributes($methodReflection))
			->toArrayList()
		;

		$this->validateReturnType($methodReflection);

		foreach ($methodActions as $methodAction) {
			if ($this->hasClassControllers) {
				yield from $this->getRoutesFromControllerAndAttribute($methodAction, $methodMiddlewares, $methodReflection);
			} else {
				yield $this->getRouteFromAttribute($methodAction, $methodMiddlewares, $methodReflection);
			}
		}
	}

	/**
	 * @param ArrayList<WebMiddleware> $methodMiddlewares
	 */
	private function getRouteFromAttribute(ActionAttribute $methodAction, ArrayList $methodMiddlewares, ReflectionMethod $methodReflection): ClassMethodRouteData
	{
		$routePath = $methodAction->getPath() ?? '';
		$actionTemplate = RouteTemplate::parse($routePath);

		return new ClassMethodRouteData(
			$this,
			$actionTemplate,
			$methodMiddlewares,
			$methodAction->getRequestMethods(),
			$methodReflection,
		);
	}

	/**
	 * @param ArrayList<WebMiddleware> $methodMiddlewares
	 */
	private function getRoutesFromControllerAndAttribute(ActionAttribute $methodAction, ArrayList $methodMiddlewares, ReflectionMethod $methodReflection): iterable
	{
		$routePath = $methodAction->getPath() ?? '';

		/** @var ControllerAttribute $controllerAttribute */
		foreach ($this->classControllers as $controllerAttribute) {
			$controllerPath = $controllerAttribute->getPath() ?? '';
			$controllerTemplate = RouteTemplate::parse($controllerPath);
			$actionTemplate = RouteTemplate::parse($routePath, $controllerTemplate);

			yield new ClassMethodRouteData(
				$this,
				$actionTemplate,
				$methodMiddlewares,
				$methodAction->getRequestMethods(),
				$methodReflection,
			);
		}
	}

	private function getRoutesFromInvoke(ReflectionMethod $methodReflection): iterable
	{
		/** @var ActionAttribute $classAction */
		foreach ($this->classActions as $classAction) {
			$routePath = $classAction->getPath() ?? '';
			$actionTemplate = RouteTemplate::parse($routePath);

			yield new ClassMethodRouteData(
				$this,
				$actionTemplate,
				$this->classMiddlewares,
				$classAction->getRequestMethods(),
				$methodReflection,
			);
		}
	}
}
