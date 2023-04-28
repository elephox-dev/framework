<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Elephox\Collection\ArraySet;
use Elephox\Collection\ObjectSet;
use Elephox\Http\Contract\RequestMethod;
use Elephox\Web\Routing\Contract\RouteData;
use Elephox\Web\Routing\Contract\RouteLoader;
use Elephox\Web\Routing\Contract\Router;
use Elephox\Web\Routing\Contract\RouterBuilder as RouterBuilderContract;
use ReflectionException;

readonly class RouterBuilder implements RouterBuilderContract
{
	/**
	 * @var ObjectSet<RouteLoader> $loaders
	 */
	private ObjectSet $loaders;

	public function __construct()
	{
		/** @var ObjectSet<RouteLoader> */
		$this->loaders = new ObjectSet();
	}

	public function addLoader(RouteLoader $loader): void
	{
		$this->loaders->add($loader);
	}

	/**
	 * @param class-string $className
	 *
	 * @throws ReflectionException
	 */
	public function addRoutesFromClass(string $className): void
	{
		$loader = new ClassRouteLoader($className);

		$this->addLoader($loader);
	}

	public function addRoutesFromNamespace(string $namespace): void
	{
		$loader = new NamespaceRouteLoader($namespace);

		$this->addLoader($loader);
	}

	/**
	 * @param RequestMethod|non-empty-string|iterable<mixed, RequestMethod|non-empty-string> $method
	 */
	public function addRoute(RequestMethod|string|iterable $method, string $template, callable $handler): void
	{
		$loader = new ClosureRouteLoader($method, $template, $handler(...));

		$this->addLoader($loader);
	}

	public function build(): Router
	{
		/** @var ArraySet<RouteData> $routes */
		$routes = new ArraySet();

		/** @var RouteLoader $loader */
		foreach ($this->loaders as $loader) {
			$routes->addAll($loader->getRoutes());
		}

		return new RegexRouter($routes);
	}
}
