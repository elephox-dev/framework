<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Closure;
use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Collection\Contract\GenericList;
use Elephox\Collection\Contract\Grouping;
use Elephox\Collection\ObjectSet;
use Elephox\Core\Handler\Contract\ComposerAutoloaderInit;
use Elephox\Core\Handler\Contract\ComposerClassLoader;
use Elephox\Core\Handler\InvalidClassCallableHandlerException;
use Elephox\DI\Contract\Resolver;
use Elephox\Files\Contract\Directory as DirectoryContract;
use Elephox\Files\Directory;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\Response;
use Elephox\OOR\Arr;
use Elephox\OOR\Regex;
use Elephox\Web\AmbiguousRouteHandlerException;
use Elephox\Web\Contract\RequestPipelineEndpoint;
use Elephox\Web\Contract\Router;
use Elephox\Web\Contract\WebMiddleware;
use Elephox\Web\Contract\WebMiddlewareAttribute;
use Elephox\Web\Contract\WebServiceCollection;
use Elephox\Web\RouteNotFoundException;
use Elephox\Web\Routing\Attribute\Contract\RouteAttribute;
use Elephox\Web\Routing\Attribute\Controller;
use Elephox\Web\Routing\Contract\RouteHandler as RouteHandlerContract;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use RuntimeException;

class RequestRouter implements RequestPipelineEndpoint, Router
{
	/**
	 * @return ComposerClassLoader
	 */
	private static function getClassLoader(): object
	{
		/** @var null|class-string<ComposerAutoloaderInit> $autoloaderClassName */
		$autoloaderClassName = null;
		foreach (get_declared_classes() as $class) {
			if (!str_starts_with($class, 'ComposerAutoloaderInit')) {
				continue;
			}

			$autoloaderClassName = $class;

			break;
		}

		if ($autoloaderClassName === null) {
			throw new RuntimeException('Could not find ComposerAutoloaderInit class. Did you install the dependencies using composer?');
		}

		/** @var ComposerClassLoader */
		return call_user_func([$autoloaderClassName, 'getLoader']);
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
		$matchingHandlers = $this->handlers
			->groupBy(fn(RouteHandlerContract $handler): float => $handler->getMatchScore($request))
			->orderByDescending(fn(Grouping $grouping): mixed => $grouping->groupKey())
			->firstOrDefault(null)
			?->toList()
		;

		if ($matchingHandlers === null) {
			throw new RouteNotFoundException($request);
		}

		if (count($matchingHandlers) === 1) {
			return $matchingHandlers[0];
		}

		throw new AmbiguousRouteHandlerException($request);
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
			return Response::build()->exception($e);
		}
	}

	/**
	 * @param class-string $className
	 * @throws ReflectionException
	 */
	public function loadFromClass(string $className): static
	{
		$classReflection = new ReflectionClass($className);
		$middlewareAttributes = $this->getMiddlewares($classReflection)->toList();
		$classInstance = $this->services->get($className) ?? $this->services->resolver()->instantiate($className);

		if ($classReflection->hasMethod('__invoke')) {
			$methodReflection = $classReflection->getMethod('__invoke');
			$returnType = $methodReflection->getReturnType();
			if (!$returnType instanceof ReflectionNamedType || $returnType->getName() !== ResponseBuilder::class) {
				throw new InvalidInvokableController($className);
			}

			foreach ($this->getControllers($classReflection) as $controllerAttribute) {
				// TODO: make this tidier
				$callback = Closure::fromCallable($classInstance);
				$handler = fn (Request $request): ResponseBuilder => /** @var ResponseBuilder */ $this->services->resolver()->callback($callback, ['request' => $request]);
				$routeHandler = new RouteHandler($controllerAttribute, $className . "__invoke", $middlewareAttributes, $handler);
				$this->add($routeHandler);
			}
		}

//		if (!empty($classAttributes)) {
//			if (!method_exists($className, "__invoke")) {
//				throw new InvalidClassCallableHandlerException($className);
//			}
//
//			$classInstance = $this->services->resolver()->instantiate($className);
//
//			/** @noinspection PhpClosureCanBeConvertedToFirstClassCallableInspection */
//			$closure = Closure::fromCallable($classInstance);
//			$middlewareAttributes = $classReflection->getAttributes(WebMiddlewareAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
//			$this->registerAttributes($className . '::__invoke', $closure, $classAttributes, $middlewareAttributes);
//		}
//
//		$methods = $classReflection->getMethods(ReflectionMethod::IS_PUBLIC);
//		foreach ($methods as $methodReflection) {
//			$methodAttributes = $methodReflection->getAttributes(Controller::class, ReflectionAttribute::IS_INSTANCEOF);
//			if (empty($methodAttributes)) {
//				continue;
//			}
//
//			$classInstance ??= $this->services->resolver()->instantiate($className);
//
//			/** @var Closure $closure */
//			$closure = $methodReflection->getClosure($classInstance);
//
//			$methodMiddlewareAttributes = $methodReflection->getAttributes(WebMiddlewareAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
//			$classMiddlewareAttributes = $classReflection->getAttributes(WebMiddlewareAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
//			$middlewareAttributes = [...$methodMiddlewareAttributes, ...$classMiddlewareAttributes];
//			$this->registerAttributes($className . "::" . $methodReflection->getName(), $closure, $methodAttributes, $middlewareAttributes);
//		}

		return $this;
	}

	/**
	 * @param non-empty-string $functionName
	 * @param Closure $closure
	 * @param array<array-key, ReflectionAttribute<Controller>> $requestHandlerAttributes
	 * @param array<array-key, ReflectionAttribute<WebMiddlewareAttribute>> $middlewareAttributes
	 */
	private function registerAttributes(string $functionName, Closure $closure, array $requestHandlerAttributes, array $middlewareAttributes): void
	{
		foreach ($requestHandlerAttributes as $attribute) {
			$requestHandler = $attribute->newInstance();

			/** @var GenericKeyedEnumerable<int, WebMiddleware> $middlewares */
			$middlewares = ArrayList::from($middlewareAttributes)
				->select(
					static fn(ReflectionAttribute $middlewareAttribute): WebMiddleware => /** @var WebMiddlewareAttribute */ $middlewareAttribute->newInstance()
				);

			$binding = new RouteHandler($functionName, $middlewares, $requestHandler, $closure);

			$this->handlers->add($binding);
		}
	}

	/**
	 * @throws ReflectionException
	 */
	public function loadFromNamespace(string $namespace): static
	{
		$classLoader = self::getClassLoader();
		foreach ($classLoader->getPrefixesPsr4() as $nsPrefix => $dirs) {
			if (!str_starts_with($namespace, $nsPrefix) && !str_starts_with($nsPrefix, $namespace)) {
				continue;
			}

			$parts = Regex::split('/\\\\/', rtrim($namespace, '\\') . '\\');
			// remove first element since it is the alias for the directories we are iterating
			$root = $parts->shift();

			foreach ($dirs as $dir) {
				$directory = new Directory($dir);

				/** @var ArrayList<string> $partsUsed */
				$partsUsed = new ArrayList();
				$this->loadClassesRecursive($root, $parts, $partsUsed, $directory, $classLoader);

				//assert($partsUsed->isEmpty());
			}
		}

		return $this;
	}

	/**
	 * @param string $rootNs
	 * @param ArrayList<string> $nsParts
	 * @param ArrayList<string> $nsPartsUsed
	 * @param DirectoryContract $directory
	 * @param ComposerClassLoader $classLoader
	 * @param int $depth
	 * @throws ReflectionException
	 *
	 * @noinspection PhpDocSignatureInspection
	 */
	private function loadClassesRecursive(string $rootNs, ArrayList $nsParts, ArrayList $nsPartsUsed, DirectoryContract $directory, object $classLoader, int $depth = 0): void
	{
		if ($depth > 10) {
			throw new RuntimeException("Recursion limit exceeded. Please choose a more specific namespace.");
		}

		$lastPart = $nsParts->shift();
		$nsPartsUsed->add($lastPart);
		foreach ($directory->getDirectories() as $dir) {
			if ($dir->getName() !== $lastPart) {

				continue;
			}

			self::loadClassesRecursive($rootNs, $nsParts, $nsPartsUsed, $dir, $classLoader, $depth + 1);
		}

		if ($lastPart === '') {
			foreach ($directory->getFiles() as $file) {
				$filename = $file->getName();
				if (!str_ends_with($filename, '.php')) {
					continue;
				}

				$className = substr($filename, 0, -4);

				/**
				 * @var class-string $fqcn
				 * @noinspection PhpRedundantVariableDocTypeInspection
				 */
				$fqcn = $rootNs . "\\" . implode("\\", $nsPartsUsed->toList()) . $className;

				$classLoader->loadClass($fqcn);

				$this->loadFromClass($fqcn);
			}
		}

		$nsParts->unshift($nsPartsUsed->pop());
	}

	/**
	 * @param ReflectionClass $class
	 * @return GenericList<Controller>
	 */
	private function getControllers(ReflectionClass $class): GenericList
	{
		/** @var GenericList<Controller> */
		return ArrayList::from($class->getAttributes(Controller::class, ReflectionAttribute::IS_INSTANCEOF))
			->select(fn(ReflectionAttribute $attribute): Controller => /** @var Controller */ $attribute->newInstance());
	}

	/**
	 * @param ReflectionClass $class
	 * @return GenericList<WebMiddlewareAttribute>
	 */
	private function getMiddlewares(ReflectionClass $class): GenericList
	{
		/** @var GenericList<WebMiddlewareAttribute> */
		return ArrayList::from($class->getAttributes(WebMiddlewareAttribute::class, ReflectionAttribute::IS_INSTANCEOF))
			->select(fn(ReflectionAttribute $attribute): WebMiddlewareAttribute => /** @var WebMiddlewareAttribute */ $attribute->newInstance());
	}
}
